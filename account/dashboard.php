<?php 
include '../include/header.php';
require_once '../php/db.php';
require_once '../php/config.php';
global $logger, $browserLogger;

// Add dashboard CSS
$additionalCSS = '<link rel="stylesheet" href="' . $baseURL . 'assests/css/dashboard.css">';


$logger->info("Dashboard accessed");
$browserLogger->log("Dashboard accessed");

// Fetch user id from session
$userId = $_SESSION['user_id'];
$logger->info("User ID: " . $userId);
$browserLogger->log("User ID: " . $userId);

// Initialize stats
$stats = [
    'total_applications' => 0,
    'pending_applications' => 0,
    'approved_applications' => 0,
    'missing_document_applications' => 0,
    'rejected_applications' => 0,
];

// Fetch application statistics for the logged-in user
$stmt = $conn->prepare("
    SELECT COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'missing_document' THEN 1 ELSE 0 END) as missing_docs,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications
    WHERE user_id = ?
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['total_applications'] = $row['total'];
    $stats['pending_applications'] = $row['pending'];
    $stats['approved_applications'] = $row['approved'];
    $stats['missing_document_applications'] = $row['missing_docs'];
    $stats['rejected_applications'] = $row['rejected'];

    $logger->info("Application statistics fetched: " . json_encode($stats));
    $browserLogger->log("Application statistics fetched: " . json_encode($stats));
}

// Fetch recent applications for this user
$stmt = $conn->prepare("
    SELECT * FROM applications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$recentApplications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$logger->info("Recent applications fetched: " . json_encode($recentApplications));
$browserLogger->log("Recent applications fetched: " . json_encode($recentApplications));
?>

<!-- Main container with sidebar and content -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <?php include 'sidebar.php'; ?>
        </div>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="my_application.php" class="btn btn-sm custom-btn">View All Applications</a>
                        <button type="button" class="btn btn-sm custom-btn" data-bs-toggle="modal" data-bs-target="#applicationFormModal">
                        Application Form
                        </button>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>!</p>
            </div>
   

    <div class="row">
        <div class="col-md-6 col-lg-2 mb-4">
            <div class="card stat-card h-100 text-center">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-count"><?php echo $stats['total_applications']; ?></div>
                    <div class="stat-title">Total Applications</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-2 mb-4">
            <div class="card stat-card h-100 text-center">
                <div class="card-body">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-count"><?php echo $stats['pending_applications']; ?></div>
                    <div class="stat-title">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-2 mb-4">
            <div class="card stat-card h-100 text-center">
                <div class="card-body">
                    <div class="stat-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-count"><?php echo $stats['approved_applications']; ?></div>
                    <div class="stat-title">Approved</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-2 mb-4">
            <div class="card stat-card h-100 text-center">
                <div class="card-body">
                    <div class="stat-icon text-info">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-count"><?php echo $stats['missing_document_applications']; ?></div>
                    <div class="stat-title">Missing Docs</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-2 mb-4">
            <div class="card stat-card h-100 text-center">
                <div class="card-body">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-count"><?php echo $stats['rejected_applications']; ?></div>
                    <div class="stat-title">Rejected</div>
                </div>
            </div>
        </div>
    </div>


    <div class="card mt-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Applications</h5>
            <a href="my_application.php" class="btn btn-sm btn-outline-light">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentApplications)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No applications found</td>
                        </tr>
                        <?php else: ?>
                        <?php $srNo = 1; ?>
                        <?php foreach ($recentApplications as $app): ?>
                        <tr>
                            <td><?php echo $srNo++; ?></td>
                            <td><?php echo htmlspecialchars($app['service_type']); ?></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                switch ($app['status']) {
                                    case 'pending': $statusClass = 'bg-warning'; break;
                                    case 'approved': $statusClass = 'bg-success'; break;
                                    case 'missing_document': $statusClass = 'bg-info'; break;
                                    case 'rejected': $statusClass = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo $app['status'] === 'missing_document' ? 'Missing Document' : ucfirst(str_replace('_', ' ', $app['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                            <td>
                                <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </main>
    </div>
</div>


<!-- application form modal -->
<div class="modal fade" id="applicationFormModal" tabindex="-1" aria-labelledby="applicationFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger m-3"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form action="./submit_application.php" method="POST" enctype="multipart/form-data" id="applicationForm">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Application Form</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input disabled value="<?php echo htmlspecialchars($_SESSION['name']); ?>" type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input disabled value="<?php echo htmlspecialchars($_SESSION['email']); ?>" type="email" name="email" class="form-control" required>
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input disabled  value="<?php echo htmlspecialchars($_SESSION['mobile']); ?>" type="text" name="phone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Application Type</label>
                        <select name="application_type" class="form-select" required>
                            <option disabled selected>-- Select Application Type --</option>
                            <option value="GST Registration">GST Registration</option>
                            <option value="Digital Signature">Digital Signature</option>
                            <option value="MSME Registration">MSME Registration</option>
                            <option value="Income Tax Filing">Income Tax Filing</option>
                            <option value="Trademark Registration">Trademark Registration</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Documents (PDF, JPG, PNG)</label>
                        <input type="file" name="document[]" class="form-control" multiple >
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn custom-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn custom-btn">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include '../include/footer.php'; ?>
