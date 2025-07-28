<?php
include '../include/header.php';
require_once '../php/db.php';
require_once '../php/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_register.php');
    exit();
}


// $applicationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$applicationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$logger->info("this is application id: ", $applicationId);
$userId = $_SESSION['user_id'];

if ($applicationId <= 0) {
    $_SESSION['error'] = 'Invalid application ID';
    header('Location: dashboard.php');
    exit();
}

// Fetch application details
$stmt = $conn->prepare("SELECT a.*, 
              u.name as customer_name , 
              u.email as email , 
              u.mobile as mobile
              FROM applications a 
              LEFT JOIN users u ON a.user_id = u.id
              WHERE a.id = ? AND a.user_id = ?");
$stmt->bind_param('ii', $applicationId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();


$requiredDocs = [];

if (!empty($application['required_documents'])) {
    $requiredDocs = json_decode($application['required_documents'], true);
    if (!is_array($requiredDocs)) {
        $requiredDocs = [];
    }
}
$logger->info("Application details fetched: ", json_encode($requiredDocs));


if (!$application) {
    $_SESSION['error'] = 'Application not found or you do not have permission to view it';
    header('Location: dashboard.php');
    exit();
}

// Fetch uploaded files
$stmt = $conn->prepare("
    SELECT * FROM files
    WHERE model_type = 'application' AND model_id = ?
");
$stmt->bind_param('i', $applicationId);
$stmt->execute();
$uploadedDocs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statusClass = 'bg-secondary';
switch ($application['status']) {
    case 'pending': $statusClass = 'bg-warning'; break;
    case 'approved': $statusClass = 'bg-success'; break;
    case 'missing_document': $statusClass = 'bg-info'; break;
    case 'rejected': $statusClass = 'bg-danger'; break;
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Application Details</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between text-white align-items-center">
            <h5 class="mb-0">Application <?php echo htmlspecialchars($application['application_number']); ?></h5>
            <span class="badge <?php echo $statusClass; ?> p-2">
                <?php echo ucfirst(str_replace('_', ' ', $application['status'])); ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Service Type:</strong> <?php echo htmlspecialchars($application['service_type']); ?></p>
                    <p><strong>Submitted On:</strong> <?php echo date('F j, Y, g:i a', strtotime($application['created_at'])); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($application['customer_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($application['mobile']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Uploaded Documents -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0 text-white">Uploaded Documents</h5>
        </div>
        <div class="card-body">
            <?php if (empty($uploadedDocs)): ?>
                <p class="text-muted">No documents uploaded.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Original Name</th>
                                <th>Uploaded On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uploadedDocs as $doc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['original_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($doc['uploaded_at'])); ?></td>
                                    <td>
                                        <!-- <?php $baseURL . $doc['file_path']; ?> -->
                                        <a href="../uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <a href="../uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" download class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Required Documents from Admin -->
    <?php if (!empty($requiredDocs)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 text-white">Required Documents (as marked by Admin)</h5>
    </div>
    <div class="card-body">
        <p>Please upload the following documents before proceeding the application: <strong><?php echo implode(', ', $requiredDocs); ?></strong></p>
        <form action="upload_document.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                <div class="mb-3">
            <label for="documentFile" class="form-label">Select file to upload</label>
            <input class="form-control" type="file" id="documentFile" name="document_file[]" multiple required>
            <div class="form-text">
                <p id="documentNameText" class="mb-1"></p>
                Accepted file types: PDF, JPG, PNG (Max: 5MB)
            </div>
        </div>

                    <button type="submit" onSubmit="handleSubmit()" class="btn btn-primary">Upload</button>

            </form>
    </div>
</div>
<?php endif; ?>
    </div>

<script>
function handleSubmit() {
    console.log("submit");
    // log the files here
    console.log(document.getElementById("documentFile").files);
}
</script>

<!-- <script>
document.addEventListener('DOMContentLoaded', function() {
    var uploadModal = document.getElementById('uploadDocumentModal');
    var uploadForm = uploadModal.querySelector('form');
    var uploadButton = uploadForm.querySelector('button[type="submit"]');
    var originalUploadButtonText = uploadButton.innerHTML;
    
    // Handle modal show event
    uploadModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var docName = button.getAttribute('data-doc-name');
        
        var modalTitle = uploadModal.querySelector('.modal-title');
        var docNameInput = document.getElementById('documentNameInput');
        var docNameText = document.getElementById('documentNameText');
        
        modalTitle.textContent = 'Upload ' + docName;
        docNameInput.value = docName;
        docNameText.textContent = 'Document: ' + docName;
        
        // Reset form
        uploadForm.reset();
        
        // Remove any previous error/success messages
        var existingAlerts = uploadModal.querySelectorAll('.alert');
        existingAlerts.forEach(function(alert) {
            alert.remove();
        });
        
        // Reset button state
        uploadButton.disabled = false;
        uploadButton.innerHTML = originalUploadButtonText;
    });
    
    // Handle form submission
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(uploadForm);
        var fileInput = uploadForm.querySelector('input[type="file"]');
        
        // Validate file
        if (fileInput.files.length === 0) {
            showAlert('Please select a file to upload', 'danger');
            return;
        }
        
        // Show loading state
        uploadButton.disabled = true;
        uploadButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';
        
        // Submit form via AJAX
        fetch('upload_document.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('Document uploaded successfully!', 'success');
                
                // Close modal after 1.5 seconds
                setTimeout(function() {
                    var modal = bootstrap.Modal.getInstance(uploadModal);
                    modal.hide();
                    
                    // Reload the page to show updated document list
                    window.location.reload();
                }, 1500);
            } else {
                // Show error message
                showAlert(data.message || 'Failed to upload document', 'danger');
                uploadButton.disabled = false;
                uploadButton.innerHTML = originalUploadButtonText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while uploading the document', 'danger');
            uploadButton.disabled = false;
            uploadButton.innerHTML = originalUploadButtonText;
        });
    });
    
    // Helper function to show alert messages
    function showAlert(message, type) {
        // Remove any existing alerts
        var existingAlerts = uploadModal.querySelectorAll('.alert');
        existingAlerts.forEach(function(alert) {
            alert.remove();
        });
        
        // Create new alert
        var alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert alert after the form
        uploadForm.parentNode.insertBefore(alertDiv, uploadForm.nextSibling);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            var bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
});
</script> -->

</div>



<?php include '../include/footer.php'; ?>
