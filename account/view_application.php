<?php
include '../include/header.php';
require_once '../php/db.php';
require_once '../php/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_register.php');
    exit();
}

$applicationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = $_SESSION['user_id'];

if ($applicationId <= 0) {
    $_SESSION['error'] = 'Invalid application ID';
    header('Location: dashboard.php');
    exit();
}

// Fetch application details
$stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->bind_param('i', $applicationId);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

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
            <h5 class="mb-0">Application #<?php echo htmlspecialchars($application['id']); ?></h5>
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
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($application['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($application['phone']); ?></p>
                    <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($application['address'])); ?></p>
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
                                        <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" download class="btn btn-sm btn-outline-secondary">
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

    <!-- Action Buttons -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0 text-white">Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2">
                <?php if ($application['status'] === 'pending' || $application['status'] === 'missing_document'): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitForReviewModal">
                        <i class="fas fa-paper-plane me-1"></i> Submit for Review
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

<!-- Submit Modal -->
<div class="modal fade" id="submitForReviewModal" tabindex="-1" aria-labelledby="submitForReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="submit_application.php" method="post" class="modal-content">
            <input type="hidden" name="application_id" value="<?php echo $applicationId; ?>">
            <div class="modal-header">
                <h5 class="modal-title">Submit for Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit this application for review?</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmSubmit" required>
                    <label class="form-check-label" for="confirmSubmit">
                        I confirm that all information provided is accurate to the best of my knowledge.
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Submit for Review</button>
            </div>
        </form>
    </div>
</div>

<?php include '../include/footer.php'; ?>
