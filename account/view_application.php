<?php
include '../include/header.php';
require_once '../php/db.php';
require_once '../php/config.php';
global $logger, $browserLogger;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_register.php');
    exit();
}

// Get application ID from URL
$applicationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = $_SESSION['user_id'];

if ($applicationId <= 0) {
    $_SESSION['error'] = 'Invalid application ID';
    header('Location: dashboard.php');
    exit();
}

// Fetch application details
$stmt = $conn->prepare("
    SELECT a.* 
    FROM applications a 
    WHERE a.id = ?
");
$stmt->bind_param('i', $applicationId);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

if (!$application) {
    $_SESSION['error'] = 'Application not found or you do not have permission to view it';
    header('Location: dashboard.php');
    exit();
}

// Fetch required documents for this service type
$stmt = $conn->prepare("
    SELECT * FROM required_documents 
    WHERE service_type = ?
    ORDER BY document_name
");
$stmt->bind_param('s', $application['service_type']);
$stmt->execute();
$requiredDocs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch uploaded documents

// $stmt = $conn->prepare("
//     SELECT * FROM application_documents 
//     WHERE application_id = ?
// ");

$stmt->bind_param('i', $applicationId);
$stmt->execute();
$uploadedDocs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$uploadedDocMap = [];
foreach ($uploadedDocs as $doc) {
    $uploadedDocMap[$doc['document_type']] = $doc;
}

// Determine missing documents
$missingDocs = [];
foreach ($requiredDocs as $doc) {
    if (!isset($uploadedDocMap[$doc['document_type']])) {
        $missingDocs[] = $doc;
    }
}

// Get status class for styling
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Application #<?php echo htmlspecialchars($application['id']); ?></h5>
            <span class="badge <?php echo $statusClass; ?> p-2">
                <?php echo ucfirst(str_replace('_', ' ', $application['status'])); ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Service Type:</strong></p>
                    <p><?php echo htmlspecialchars($application['service_type']); ?></p>
                    
                    <p class="mb-1"><strong>Submitted On:</strong></p>
                    <p><?php echo date('F j, Y, g:i a', strtotime($application['created_at'])); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Applicant Name:</strong></p>
                    <p><?php echo htmlspecialchars($application['user_name']); ?></p>
                    
                    <p class="mb-1"><strong>Email:</strong></p>
                    <p><?php echo htmlspecialchars($application['email']); ?></p>
                    
                    <?php if (!empty($application['phone'])): ?>
                        <p class="mb-1"><strong>Phone:</strong></p>
                        <p><?php echo htmlspecialchars($application['phone']); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($application['address'])): ?>
                        <p class="mb-1"><strong>Address:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($application['address'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($application['notes'])): ?>
                <div class="mb-3">
                    <p class="mb-1"><strong>Additional Notes:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($application['notes'])); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($application['status'] === 'rejected' && !empty($application['rejection_reason'])): ?>
                <div class="alert alert-danger">
                    <h6>Rejection Reason:</h6>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($application['rejection_reason'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Documents Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Documents</h5>
        </div>
        <div class="card-body">
            <?php if (empty($requiredDocs)): ?>
                <p class="text-muted">No documents required for this service.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Document Name</th>
                                <th>Status</th>
                                <th>Uploaded On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requiredDocs as $doc): 
                                $uploadedDoc = $uploadedDocMap[$doc['document_type']] ?? null;
                                $isUploaded = $uploadedDoc !== null;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($doc['document_name']); ?></strong>
                                        <?php if (!empty($doc['description'])): ?>
                                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($doc['description']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isUploaded): ?>
                                            <span class="badge bg-success">Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Missing</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $isUploaded ? date('M j, Y', strtotime($uploadedDoc['uploaded_at'])) : 'N/A'; ?>
                                    </td>
                                    <td>
                                        <?php if ($isUploaded): ?>
                                            <a href="../uploads/documents/<?php echo htmlspecialchars($uploadedDoc['file_path']); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                            <a href="../uploads/documents/<?php echo htmlspecialchars($uploadedDoc['file_path']); ?>" 
                                               download 
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#uploadDocumentModal"
                                                    data-doc-type="<?php echo htmlspecialchars($doc['document_type']); ?>"
                                                    data-doc-name="<?php echo htmlspecialchars($doc['document_name']); ?>">
                                                <i class="fas fa-upload me-1"></i> Upload
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($missingDocs)): ?>
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Missing Documents</h6>
                        <p class="mb-0">The following documents are required but not yet uploaded:</p>
                        <ul class="mb-0">
                            <?php foreach ($missingDocs as $doc): ?>
                                <li><?php echo htmlspecialchars($doc['document_name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Application Actions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2">
                <?php if ($application['status'] === 'pending' || $application['status'] === 'missing_document'): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitForReviewModal">
                        <i class="fas fa-paper-plane me-1"></i> Submit for Review
                    </button>
                <?php endif; ?>
                
                <?php if ($application['status'] === 'missing_document'): ?>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="fas fa-upload me-1"></i> Upload Missing Documents
                    </button>
                <?php endif; ?>
                
                <?php if ($application['status'] === 'approved'): ?>
                    <a href="#" class="btn btn-success">
                        <i class="fas fa-file-invoice me-1"></i> Download Certificate
                    </a>
                <?php endif; ?>
                
                <a href="my_application.php" class="btn btn-outline-secondary ms-auto">
                    <i class="fas fa-list me-1"></i> View All Applications
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="upload_document.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="application_id" value="<?php echo $applicationId; ?>">
                <input type="hidden" name="document_type" id="documentType" value="">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="documentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="documentName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="documentFile" class="form-label">Select File</label>
                        <input class="form-control" type="file" id="documentFile" name="document_file" required>
                        <div class="form-text">Accepted formats: PDF, JPG, PNG (Max size: 5MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Submit for Review Modal -->
<div class="modal fade" id="submitForReviewModal" tabindex="-1" aria-labelledby="submitForReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="submit_application.php" method="post">
                <input type="hidden" name="application_id" value="<?php echo $applicationId; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="submitForReviewModalLabel">Submit for Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($missingDocs)): ?>
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Missing Documents</h6>
                            <p>The following documents are required but not yet uploaded. Your application may be rejected or delayed without them.</p>
                            <ul>
                                <?php foreach ($missingDocs as $doc): ?>
                                    <li><?php echo htmlspecialchars($doc['document_name']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <p>Are you sure you want to submit this application for review?</p>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmSubmit" required>
                        <label class="form-check-label" for="confirmSubmit">
                            I confirm that all information provided is accurate to the best of my knowledge.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit for Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize modal with document details
document.addEventListener('DOMContentLoaded', function() {
    const uploadModal = document.getElementById('uploadDocumentModal');
    
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const docType = button.getAttribute('data-doc-type');
            const docName = button.getAttribute('data-doc-name');
            
            document.getElementById('documentType').value = docType || '';
            document.getElementById('documentName').value = docName || 'Document';
        });
    }
});
</script>

<?php include '../include/footer.php'; ?>
