<?php
require_once '../include/header.php';
// session_start();
global $logger;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to access this page';
    header('Location: login.php');
    exit();
}

// Initialize success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Service types and their prices
$servicePrices = [
    'GST Registration' => 1999,
    'Company Registration' => 5999,
    'MSME Registration' => 1499,
    'Income Tax Filing' => 999,
    'Trademark Registration' => 1999
];

// Required documents for each service
$requiredDocuments = [
    'GST Registration' => ['PAN Card', 'Aadhaar Card', 'Business Address Proof', 'Bank Account Details', 'Photograph'],
    'Company Registration' => ['Director PAN', 'Director Aadhaar', 'Address Proof', 'Passport Photo', 'Business Address Proof'],
    'MSME Registration' => ['Aadhaar Card', 'PAN Card', 'Business Address Proof', 'Bank Account Details'],
    'Income Tax Filing' => ['PAN Card', 'Aadhaar Card', 'Form 16', 'Bank Statements', 'Investment Proofs'],
    'Trademark Registration' => ['Trademark Logo', 'Applicant Details', 'Goods/Services List', 'Power of Attorney']
];

// Common function to handle file upload
function uploadDocument($file, $uploadDir, $docName) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $originalName = basename($file['name']);
    $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
    // Validate file type
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileExt, $allowedTypes)) {
        throw new Exception("Invalid file type for $docName. Only PDF, JPG, and PNG files are allowed.");
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("File too large for $docName. Maximum size is 5MB.");
    }
    
    // Create unique filename
    $newFilename = $docName . '_' . uniqid() . '.' . $fileExt;
    $targetPath = $uploadDir . $newFilename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'original_name' => $originalName,
            'file_path' => $targetPath,
            'file_type' => mime_content_type($targetPath)
        ];
    }
    
    return false;
}

// Handle form submission for Step 2 (Payment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_payment'])) {
    try {
        if (empty($_POST) && empty($_FILES)) {
            throw new Exception('No data received. This might be due to server file size limits. Please check your server configuration (post_max_size, upload_max_filesize).');
        }
        // Validate required fields
        if (empty($_POST['application_type'])) {
            throw new Exception('Please select an application type');
        }
        
        // Store form data in session for later use after payment
        $_SESSION['temp_application'] = [
            'user_id' => $_SESSION['user_id'],
            'name' => $_SESSION['name'],
            'email' => $_SESSION['email'],
            'mobile' => $_SESSION['mobile'],
            'application_type' => $_POST['application_type'],
            'amount' => $servicePrices[$_POST['application_type']]
        ];
        
        // Create upload directory
        $tempUploadDir = '../uploads' . session_id() . '/';
        if (!file_exists($tempUploadDir)) {
            if (!mkdir($tempUploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        // Handle file uploads
        $uploadedFiles = [];
        $requiredDocs = $requiredDocuments[$_POST['application_type']];
        
        foreach ($requiredDocs as $doc) {
            $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $doc));
            
            if (!isset($_FILES['document'][$safeName]) || $_FILES['document'][$safeName]['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception("Please upload $doc");
            }
            
            $uploadResult = uploadDocument($_FILES['document'][$safeName], $tempUploadDir, $safeName);
            if (!$uploadResult) {
                throw new Exception("Failed to upload $doc");
            }
            
            $uploadedFiles[$doc] = $uploadResult;
        }
        
        // Store uploaded files info in session
        $_SESSION['temp_application']['uploaded_files'] = $uploadedFiles;
        
        // Redirect to Razorpay payment page
        header('Location: razorpay_payment.php');
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Application Form</h1>
            </div>

            <?php if ($success): ?>`
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            
            <form id="applicationForm" method="post" enctype="multipart/form-data">
                <div id="step1">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Please fill in your application details and upload the required documents.
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Full Name</label>
                            <input disabled value="<?php echo htmlspecialchars($_SESSION['name']); ?>" type="text" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email Address</label> 
                            <input disabled value="<?php echo htmlspecialchars($_SESSION['email']); ?>" type="email" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone Number</label>
                            <input disabled value="<?php echo htmlspecialchars($_SESSION['mobile']); ?>" type="text" class="form-control">
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header text-white">
                            <h6 class="mb-0">Service Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Application Type <span class="text-danger">*</span></label>
                                <select name="application_type" id="application_type" class="form-select" required>
                                    <option value="" disabled selected>-- Select Application Type --</option>
                                    <?php foreach ($servicePrices as $service => $price): ?>
                                        <option value="<?php echo htmlspecialchars($service); ?>">
                                            <?php echo htmlspecialchars($service); ?> (₹<?php echo number_format($price); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="documentFields" class="mt-4">
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i> Please select an application type to see required documents.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="step2" style="display:none;">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Order Summary</h6>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Service:</span>
                                        <span id="summaryService"></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Amount:</span>
                                        <span id="summaryAmount"></span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span id="summaryTotal"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Payment Method</h6>
                                    <hr>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="razorpay" value="razorpay" checked>
                                        <label class="form-check-label" for="razorpay">
                                            <img src="../assets/img/razorpay.png" alt="Razorpay" style="height: 24px; margin-left: 10px;">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <!-- Sticky Footer Buttons -->
                <div class="sticky-bottom bg-white py-3 border-top mt-4" style="position: sticky; bottom: 0; z-index: 1000;">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display:none;">
                                <i class="fas fa-arrow-left me-1"></i> Previous
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn custom-btn me-2" id="nextBtn" disabled>
                                Next <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                            <button type="submit" name="proceed_payment" class="btn btn-success" id="payBtn" style="display:none;" disabled>
                                <i class="fas fa-credit-card me-2"></i> Proceed to Payment
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('applicationForm');
    const appTypeSelect = document.getElementById('application_type');
    const docContainer = document.getElementById('documentFields');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const payBtn = document.getElementById('payBtn');
    const summaryService = document.getElementById('summaryService');
    const summaryAmount = document.getElementById('summaryAmount');
    const summaryTotal = document.getElementById('summaryTotal');

    let selectedAmount = 0;
    
    const servicePrices = <?php echo json_encode($servicePrices); ?>;
    const requiredDocuments = <?php echo json_encode($requiredDocuments); ?>;

    // Helper function to get document help text
    function getDocumentHelpText(docType) {
        const helpTexts = {
            'PAN Card': 'Clear scan of both sides of your PAN card',
            'Aadhaar Card': 'Front and back of your Aadhaar card',
            'Bank Account Details': 'Latest 3 months statement with IFSC code visible',
            'Photograph': 'White background, 35x45mm, 80% face coverage',
            'Business Address Proof': 'Utility bill or bank statement not older than 3 months',
            'Director PAN': 'Director PAN Card',
            'Director Aadhaar': 'Director Aadhaar Card',
            'Address Proof': 'Utility bill or bank statement not older than 3 months',
            'Form 16': 'Latest Form 16 from your employer',
            'Bank Statements': 'Latest 3 months statement with IFSC code visible',
            'Investment Proofs': 'Sections 80C, 80D, etc. as applicable',
            'Trademark Logo': 'High resolution logo file (min 400x400px)',
            'Applicant Details': 'Applicant Details',
            'Goods/Services List': 'Goods/Services List',
            'Power of Attorney': 'Power of Attorney'
        };
        return helpTexts[docType] || 'Please upload a clear, legible copy of this document';
    }

    // Helper function to check if all required documents are uploaded
    function checkAllDocumentsUploaded() {
        if (!appTypeSelect.value) {
            nextBtn.disabled = true;
            return false;
        }
        
        const requiredInputs = document.querySelectorAll('.doc-input[required]');
        let allFilled = requiredInputs.length > 0;
        
        requiredInputs.forEach(input => {
            if (!input.files || input.files.length === 0) {
                allFilled = false;
            }
        });
        
        nextBtn.disabled = !allFilled;
        return allFilled;
    }
  
    // Function to update document fields based on selected service
    function updateDocumentFields(serviceType) {
        const docs = requiredDocuments[serviceType] || [];
        
        if (docs.length === 0) {
            docContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No additional documents required for this service.
                </div>`;
            nextBtn.disabled = false;
            return;
        }
        
        let html = `<h6 class="mb-3">Required Documents</h6>`;
        docs.forEach((doc, index) => {
            const safeName = doc.toLowerCase().replace(/[^a-z0-9]+/g, '');
            html += `
                <div class="mb-4 document-upload">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">${doc} <span class="text-danger">*</span></label>
                        <span class="badge bg-light text-dark">${index + 1} of ${docs.length}</span>
                    </div>
                    <div class="input-group">
                        <input type="file" name="document[${safeName}]" class="form-control doc-input" id="doc-${safeName}" accept=".pdf,.jpg,.jpeg,.png" required>
                        <label class="input-group-text" for="doc-${safeName}"><i class="fas fa-upload"></i></label>
                    </div>
                    <div class="form-text small"><i class="fas fa-info-circle me-1"></i> ${getDocumentHelpText(doc)}</div>
                </div>`;
        });
        docContainer.innerHTML = html;
        
        // Add event listeners to new file inputs
        document.querySelectorAll('.doc-input').forEach(input => {
            input.addEventListener('change', checkAllDocumentsUploaded);
        });
        
        checkAllDocumentsUploaded();
    }
    
    // Application type selection handler
    appTypeSelect.addEventListener('change', function() {
        const appType = this.value;
        selectedAmount = servicePrices[appType] || 0;
        updateDocumentFields(appType);
    });

    // Next button click handler
    nextBtn.addEventListener('click', function() {
        if (!checkAllDocumentsUploaded()) {
            alert('Please select an application type and upload all required documents.');
            return;
        }

        // Update order summary
        const selectedService = appTypeSelect.options[appTypeSelect.selectedIndex].text;
        summaryService.textContent = selectedService;
        summaryAmount.textContent = '₹' + selectedAmount.toLocaleString('en-IN');
        summaryTotal.textContent = '₹' + selectedAmount.toLocaleString('en-IN');
        
        // Show payment step
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        
        nextBtn.style.display = 'none';
        prevBtn.style.display = 'inline-block';
        payBtn.style.display = 'inline-block';
        payBtn.disabled = false;
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Previous button click handler
    prevBtn.addEventListener('click', function() {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
        
        nextBtn.style.display = 'inline-block';
        prevBtn.style.display = 'none';
        payBtn.style.display = 'none';
        
        checkAllDocumentsUploaded();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Initial state check
    checkAllDocumentsUploaded();
});
</script>

<?php include '../include/footer.php'; ?>