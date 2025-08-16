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
function uploadDocument($file, $uploadDir, $docName, $fileTitle)
{
    // wrap the complete function in try catch and log the error
    try {
        global $conn;
        global $logger;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            // Create a custom error message based on the error code
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = "The uploaded file was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = "No file was uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = "Missing a temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = "Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = "A PHP extension stopped the file upload.";
                    break;
                default:
                    $message = "Unknown upload error.";
                    break;
            }
            throw new Exception("Failed to upload $docName: $message");
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

        // Create new filename: original_name + unix_timestamp + extension
        $timestamp = time();
        $originalNameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        $newFilename = $originalNameWithoutExt . '_' . $timestamp . '.' . $fileExt;
        $targetPath = $uploadDir . '/' . $newFilename;

        // define fileDetails here
        $fileDetails = [];
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Normalize path for consistent storage (convert backslashes to forward slashes)
            $normalizedPath = str_replace('\\', '/', $targetPath);
            
            // insert file details into the array
            $fileDetails = [
                'original_name' => $originalName,
                'filename' => $newFilename,
                'size' => $file['size'],
                'file_path' => $normalizedPath,
                'file_type' => mime_content_type($targetPath),
                'file_title' => $fileTitle
            ];

            // here now log the file upload details
            $logger->info("File uploaded successfully: " . json_encode($fileDetails));

            // SQL query to insert the details in the files table (assuming you have file_title column)
            $sql = "INSERT INTO files (original_name, file_name, file_size, file_path, file_title, model_type, model_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            // Check if prepare was successful
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }

            // Extract variables for bind_param (required for pass by reference)
            $originalName = $fileDetails['original_name'];
            $filename = $fileDetails['filename'];
            $fileSize = $fileDetails['size'];
            $filePath = $fileDetails['file_path'];
            $fileTitle = $fileDetails['file_title'];
            $modelType = 'application';
            $modelId = $_SESSION['temp_application']['id'];

            $result = $stmt->bind_param(
                "ssisssi", 
                $originalName,  // string
                $filename,      // string
                $fileSize,      // int
                $filePath,      // string
                $fileTitle,     // string
                $modelType,     // string
                $modelId        // int
            );

            if (!$result) {
                throw new Exception("Failed to bind parameters: " . $stmt->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert file details: " . $stmt->error);
            }

            $insertId = $stmt->insert_id;
            $stmt->close();

            // Log the database insert result
            $logger->info("File details inserted into database with ID: " . $insertId);

            return $fileDetails;
        } else {
            throw new Exception("Failed to move uploaded file to destination");
        }

    } catch (Exception $e) {
        $logger->info("Error occurred: " . $e->getMessage());
        return false;
    }
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

        // Generate new application number
        $sql = "SELECT application_number FROM applications ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);

        if (!$result) {
            // Query failed, log the error
            $logger->error("Query failed: " . $conn->error);
            throw new Exception("Database query failed: " . $conn->error);
        }

        $row = $result->fetch_assoc();
        $lastApplication = $row['application_number'] ?? null;

        if ($lastApplication) {
            $newApplicationNumber = str_pad((int) $lastApplication + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newApplicationNumber = '000001';
        }

        // Store form data in session for later use after payment
        $_SESSION['temp_application'] = [
            'user_id' => $_SESSION['user_id'],
            'application_type' => $_POST['application_type'],
            'amount' => $servicePrices[$_POST['application_type']],
            'application_number' => $newApplicationNumber
        ];

        $logger->info("Application data stored in session: " . json_encode($_SESSION['temp_application']));

        // Create application
        $sql = "INSERT INTO applications (user_id, application_number, service_type) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $_SESSION['temp_application']['user_id'], $_SESSION['temp_application']['application_number'], $_SESSION['temp_application']['application_type']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to create application: " . $stmt->error);
        }

        // Log the actual result that i get from the db in the stmt object
        $logger->info("Application created successfully: " . json_encode($stmt->insert_id));

        $_SESSION['temp_application']['id'] = $stmt->insert_id;

        // Create upload directory
        $tempUploadDir = '../uploads/applications';

        $logger->info("Temporary upload directory created: " . $tempUploadDir);

        if (!file_exists($tempUploadDir)) {
            if (!mkdir($tempUploadDir, 0755, recursive: true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Handle file uploads
        $uploadedFiles = [];
        $requiredDocs = $requiredDocuments[$_POST['application_type']];
        $logger->info("Required documents for application type: " . json_encode($requiredDocs));
        $docNames = $_POST['document_names'] ?? [];
        $fileTitles = $_POST['file_titles'] ?? [];
        $logger->info("Document names from form: " . json_encode($docNames));
        $logger->info("File titles from form: " . json_encode($fileTitles));

        $logger->info("Uploaded files array: " . json_encode($_FILES['documents']));

        if (isset($_FILES['documents'])) {
            $logger->info("Uploaded files array: " . json_encode($_FILES['documents']));

            // Re-structure the $_FILES array to be more intuitive
            $files = [];
            foreach ($_FILES['documents'] as $key => $all) {
                foreach ($all as $i => $val) {
                    $files[$i][$key] = $val;
                }
            }

            if (count($files) !== count($requiredDocs)) {
                throw new Exception('Please upload all required documents.');
            }

            foreach ($files as $index => $file) {
                $docName = $docNames[$index] ?? 'document'; // Fallback name
                $fileTitle = $fileTitles[$index] ?? $docName; // Use file title or fallback to docName

                $logger->info("Processing file for document: " . $docName . " with title: " . $fileTitle);

                if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                    throw new Exception("Please upload the required file for: $docName");
                }

                // Log file, tempupload dir and docname
                $logger->info("File details: " . json_encode($file));
                $logger->info("Temporary upload directory: " . $tempUploadDir);
                $logger->info("Document name: " . $docName);

                $uploadResult = uploadDocument($file, $tempUploadDir, $docName, $fileTitle);

                if (!$uploadResult) {
                    throw new Exception("Failed to upload $docName");
                }

                // The key of the $uploadedFiles array is the name of the required document
                $uploadedFiles[$docName] = $uploadResult;

                $logger->info("Uploaded file for $docName: " . json_encode($uploadResult));
            }
        } else {
            throw new Exception('No files were uploaded.');
        }

        // Store uploaded files info in session
        $_SESSION['temp_application']['uploaded_files'] = $uploadedFiles;
        $_SESSION['temp_application']['service_type'] = $_POST['service_type'] ?? '';

        $logger->info("Successfully processed and stored uploaded files in session: " . json_encode($uploadedFiles));

        // // Create order in database with unpaid status
        // $sql = "INSERT INTO orders (user_id, application_id, amount, order_id, payment_status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        // $stmt = $conn->prepare($sql);
        // $orderIdDb = 'order_' . uniqid();
        // $paymentStatus = 'unpaid';
        
        // $stmt->bind_param("iidss", $_SESSION['user_id'], $_SESSION['temp_application']['id'], $_SESSION['temp_application']['amount'], $orderIdDb, $paymentStatus);
        
        // if (!$stmt->execute()) {
        //     throw new Exception("Failed to create order: " . $stmt->error);
        // }
        
        // $orderId = $stmt->insert_id;
        // $_SESSION['temp_application']['order_id'] = $orderId;
        // $_SESSION['temp_application']['order_id_db'] = $orderIdDb;
        
        // $logger->info("Order created successfully with ID: " . $orderId);

        // Use JavaScript redirect instead of header redirect to avoid "headers already sent" error
        echo "<script>window.location.href = 'payment_handler.php';</script>";
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
        $logger->info("Error occurred: " . $e->getMessage());
    }
}

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Application Form</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="applicationForm" method="post" enctype="multipart/form-data">
                <div id="step1">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Please fill in your application details and upload the
                        required documents.
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Full Name</label>
                            <input disabled value="<?php echo htmlspecialchars($_SESSION['name']); ?>" type="text"
                                class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email Address</label>
                            <input disabled value="<?php echo htmlspecialchars($_SESSION['email']); ?>" type="email"
                                class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone Number</label>
                            <input disabled value="<?php echo htmlspecialchars($_SESSION['mobile']); ?>" type="text"
                                class="form-control">
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
                                            <?php echo htmlspecialchars($service); ?>
                                            (₹<?php echo number_format($price); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="documentFields" class="mt-4">
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i> Please select an application type to see
                                    required documents.
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
                                        <input class="form-check-input" type="radio" name="payment_method" id="razorpay"
                                            value="razorpay" checked>
                                        <label class="form-check-label" for="razorpay">
                                            <img src="../assets/img/razorpay.png" alt="Razorpay"
                                                style="height: 24px; margin-left: 10px;">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Footer Buttons -->
                <div class="sticky-bottom bg-white py-3 border-top mt-4"
                    style="position: sticky; bottom: 0; z-index: 1000;">
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
                            <button type="submit" name="proceed_payment" class="btn btn-success" id="payBtn"
                                style="display:none;" disabled>
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
    document.addEventListener('DOMContentLoaded', function () {
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
                        <input type="hidden" name="document_names[]" value="${doc}">
                        <input type="hidden" name="file_titles[]" value="${doc}">
                        <input type="file" name="documents[]" class="form-control doc-input" id="doc-${safeName}" accept=".pdf,.jpg,.jpeg,.png" required>
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
        appTypeSelect.addEventListener('change', function () {
            const appType = this.value;
            selectedAmount = servicePrices[appType] || 0;
            updateDocumentFields(appType);
        });

        // Next button click handler
        nextBtn.addEventListener('click', function () {
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
        prevBtn.addEventListener('click', function () {
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