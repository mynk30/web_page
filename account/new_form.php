<?php
require_once '../include/header.php';
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

// Clear the messages
unset($_SESSION['success']);
unset($_SESSION['error']);

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start output buffering to prevent any accidental output
    ob_start();
    
    // Set default response array
    $response = [
        'success' => false,
        'message' => '',
        'redirect' => ''
    ];
    
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Basic validation
        $required = ['application_type', 'payment_status'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate payment status
        if ($_POST['payment_status'] !== 'completed') {
            throw new Exception('Payment not completed');
        }

        // Start transaction
        $conn->begin_transaction();

        // Generate application number
        $appNumber = 'APP' . date('Ymd') . strtoupper(uniqid());
        
        // Insert application into database - removed 'notes' column as it doesn't exist
        $stmt = $conn->prepare("INSERT INTO applications 
            (user_id, application_number, service_type, status, payment_status) 
            VALUES (?, ?, ?, 'pending', 'completed')");
        
        $stmt->bind_param('iss', 
            $_SESSION['user_id'], 
            $appNumber,
            $_POST['application_type']
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to save application: ' . $stmt->error);
        }

        $applicationId = $conn->insert_id;
        $uploadDir = '../uploads/applications/' . $applicationId . '/';
        
        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Handle file uploads
        if (!empty($_FILES['document'])) {
            foreach ($_FILES['document']['name'] as $field => $filename) {
                if ($_FILES['document']['error'][$field] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['document']['tmp_name'][$field];
                    $originalName = basename($filename);
                    $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $newFilename = uniqid() . '.' . $fileExt;
                    $targetPath = $uploadDir . $newFilename;

                    // Validate file type
                    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
                    if (!in_array($fileExt, $allowedTypes)) {
                        throw new Exception("Invalid file type: $originalName. Only PDF, JPG, and PNG files are allowed.");
                    }

                    // Validate file size (5MB max)
                    if ($_FILES['document']['size'][$field] > 5 * 1024 * 1024) {
                        throw new Exception("File too large: $originalName. Maximum size is 5MB.");
                    }

                    // Move uploaded file
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        // Save file info to database
                        $docStmt = $conn->prepare("INSERT INTO application_documents 
                            (application_id, document_name, file_path, file_type) 
                            VALUES (?, ?, ?, ?)");
                        $docStmt->bind_param('isss', 
                            $applicationId, 
                            $field,
                            $targetPath,
                            mime_content_type($targetPath)
                        );
                        if (!$docStmt->execute()) {
                            throw new Exception('Failed to save document info: ' . $docStmt->error);
                        }
                        $docStmt->close();
                    } else {
                        throw new Exception("Failed to upload file: $originalName");
                    }
                }
            }
        }

        // Commit transaction
        $conn->commit();
        
        // Prepare success response
        $response['success'] = true;
        $response['message'] = 'Your application has been submitted successfully! Application ID: ' . $appNumber;
        $response['redirect'] = 'my_application.php?id=' . $applicationId;
        
        // If not AJAX, use session and redirect
        if (!$isAjax) {
            $_SESSION['success'] = $response['message'];
            header('Location: ' . $response['redirect']);
            exit();
        }

    } catch (Exception $e) {
        // Rollback transaction on error
        if (isset($conn) && $conn->ping()) {
            $conn->rollback();
        }
        
        $errorMsg = 'Application submission failed: ' . $e->getMessage();
        $logger->error($errorMsg);
        
        $response['message'] = 'Error: ' . $e->getMessage();
        
        // If not AJAX, show error and redirect back
        if (!$isAjax) {
            $_SESSION['error'] = $response['message'];
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'dashboard.php');
            exit();
        }
    }
    
    // For AJAX requests, send JSON response
    if ($isAjax) {
        // Clear any previous output
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
            
                <form id="applicationForm" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="payment_status" id="paymentStatusField" value="pending">
                    
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
                                    <div class="invalid-feedback">
                                        Please select an application type
                                    </div>
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
                                                <img src="../assests/img/razorpay.png" alt="Razorpay" style="height: 24px; margin-left: 10px;">
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="paytm" value="paytm">
                                            <label class="form-check-label" for="paytm">
                                                <img src="../assests/img/paytm.png" alt="Paytm" style="height: 24px; margin-left: 10px;">
                                            </label>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-success" id="payBtn">
                                                <i class="fas fa-credit-card me-2"></i>Pay Now
                                            </button>
                                            <div id="paymentStatus" class="text-center mt-2">
                                                <span class="badge bg-warning">Payment Pending</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                    </label>
                                    <div class="invalid-feedback">
                                        You must agree to the terms and conditions
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
                                <button type="submit" form="applicationForm" class="btn custom-btn" id="submitBtn" style="display:none;" disabled>
                                    <i class="fas fa-paper-plane me-1"></i> Submit Application
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
    const submitBtn = document.getElementById('submitBtn');
    const paymentStatus = document.getElementById('paymentStatus');
    const paymentStatusField = document.getElementById('paymentStatusField');
    const summaryService = document.getElementById('summaryService');
    const summaryAmount = document.getElementById('summaryAmount');
    const summaryTotal = document.getElementById('summaryTotal');
    const payBtn = document.getElementById('payBtn');

    let selectedAmount = 0;
    let paymentDone = false;
    
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

    // Helper function to validate file input
    function validateFileInput(input) {
        const file = input.files[0];
        if (!file) return false;
        
        const validTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        const fileExt = file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(file.type) && !['pdf', 'jpg', 'jpeg', 'png'].includes(fileExt)) {
            input.setCustomValidity('Invalid file type. Only PDF, JPG, and PNG are allowed.');
            return false;
        }
        
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            input.setCustomValidity('File is too large. Maximum size is 5MB.');
            return false;
        }
        
        input.setCustomValidity('');
        return true;
    }

    // Helper function to check if all required documents are uploaded
    function checkDocsFilled() {
        const requiredInputs = document.querySelectorAll('#step1 .doc-input[required]');
        let allFilled = true;
        
        if (requiredInputs.length === 0) {
            nextBtn.disabled = false;
            return true;
        }

        requiredInputs.forEach(input => {
            if (!input.files || input.files.length === 0 || !validateFileInput(input)) {
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
            const safeName = doc.replace(/\s+/g, '_').toLowerCase();
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
                    <div class="invalid-feedback">Please upload a valid ${doc.toLowerCase()} document</div>
                </div>`;
        });
        docContainer.innerHTML = html;
        
        document.querySelectorAll('.doc-input').forEach(input => {
            input.addEventListener('change', checkDocsFilled);
        });
        checkDocsFilled();
    }
    
    // Main event listener for service selection
    appTypeSelect.addEventListener('change', function() {
        const appType = this.value;
        selectedAmount = servicePrices[appType] || 0;
        updateDocumentFields(appType);
    });

    // Next button click handler
    nextBtn.addEventListener('click', function() {
        if (!checkDocsFilled()) {
            form.classList.add('was-validated');
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
            return;
        }

        // Update order summary
        const selectedService = appTypeSelect.options[appTypeSelect.selectedIndex].text;
        summaryService.textContent = selectedService;
        summaryAmount.textContent = '₹' + selectedAmount.toLocaleString('en-IN');
        summaryTotal.textContent = '₹' + selectedAmount.toLocaleString('en-IN');
        
        // Show next step
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        
        nextBtn.style.display = 'none';
        prevBtn.style.display = 'inline-block';
        submitBtn.style.display = 'inline-block';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Previous button click handler
    prevBtn.addEventListener('click', function() {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
        
        nextBtn.style.display = 'inline-block';
        prevBtn.style.display = 'none';
        submitBtn.style.display = 'none';
        
        checkDocsFilled(); // Re-validate step 1 on returning
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Pay Now button handler
    payBtn.addEventListener('click', function() {
        if (!document.getElementById('terms').checked) {
            document.getElementById('terms').focus();
            return;
        }
        
        const originalText = payBtn.innerHTML;
        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
        
        setTimeout(() => {
            paymentDone = true;
            paymentStatusField.value = 'completed';
            paymentStatus.innerHTML = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Payment Successful</span>';
            submitBtn.disabled = false;
            payBtn.disabled = true;
            payBtn.innerHTML = originalText;
            
            const toast = new bootstrap.Toast(document.getElementById('paymentSuccessToast'));
            toast.show();
        }, 2000);
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!paymentDone) {
            alert('Please complete the payment before submitting the form.');
            document.getElementById('step2').scrollIntoView({ behavior: 'smooth' });
            return;
        }

        // Show loading state
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Submitting...';

        // Submit the form via AJAX
        const formData = new FormData(form);
        
        fetch('', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(() => {
            // Show success message in a clean way
            const successHtml = `
                <div class="alert alert-success text-center py-4" role="alert" style="margin-top: 20px;">
                    <div class="d-flex flex-column align-items-center">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                        <h3 class="alert-heading">Application Submitted Successfully!</h3>
                        <p class="mb-0">Your application has been received. The page will refresh shortly.</p>
                    </div>
                </div>
            `;
            
            // Clear the form and show only the success message
            const formContainer = document.querySelector('.col-md-9');
            formContainer.innerHTML = successHtml;
            
            // Reload the page after 3 seconds to show a fresh form
            setTimeout(() => {
                window.location.reload();
            }, 3000);
            
            // Reset file inputs
            document.querySelectorAll('.doc-input').forEach(input => {
                input.value = '';
                input.dispatchEvent(new Event('change'));
            });
            
            // Reinitialize document fields if application type is selected
            if (appTypeSelect.value) {
                updateDocumentFields(appTypeSelect.value);
            }
            
            // Enable/disable next button based on validation
            checkDocsFilled();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the form. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Initial state check
    checkDocsFilled();
});
</script>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="paymentSuccessToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto"><i class="fas fa-check-circle me-2"></i> Payment Successful</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Your payment has been processed successfully! You can now submit your application.
        </div>
    </div>
</div>

<?php include '../include/footer.php'; ?>