<?php
include '../include/header.php';
require_once '../php/db.php';
require_once '../php/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_register.php');
    exit();
}

$userId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get filter and search parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Build the base query with JOIN to get user name
    $query = "SELECT a.*, 
              u.name as customer_name
              FROM applications a 
              LEFT JOIN users u ON a.user_id = u.id
              WHERE a.user_id = ?";
    
    $params = [$userId];
    $types = "i";

    // Add status filter if provided
    if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'approved', 'rejected', 'missing_document'])) {
        $query .= " AND a.status = ?";
        $params[] = $statusFilter;
        $types .= "s";
    }

    // Add search filter if provided
    if (!empty($searchQuery)) {
        $query .= " AND (a.name LIKE ? OR a.service_type LIKE ? OR u.name LIKE ? OR a.email LIKE ?)";
        $searchTerm = "%$searchQuery%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $types .= "ssss";
    }

    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) FROM applications a 
                   LEFT JOIN users u ON a.user_id = u.id
                   WHERE a.user_id = ?";
    $countParams = [$userId];
    $countTypes = "i";
    
    // Add the same filters to count query
    if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'approved', 'rejected', 'missing_document'])) {
        $countQuery .= " AND a.status = ?";
        $countParams[] = $statusFilter;
        $countTypes .= "s";
    }
    
    if (!empty($searchQuery)) {
        $countQuery .= " AND (a.name LIKE ? OR a.service_type LIKE ? OR u.name LIKE ? OR a.email LIKE ?)";
        $searchTerm = "%$searchQuery%";
        $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $countTypes .= "ssss";
    }
    
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $totalApplications = $countStmt->get_result()->fetch_row()[0];
    $totalPages = ceil($totalApplications / $perPage);

    // Add sorting and pagination to the main query
    $query .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
    $params = array_merge($params, [$perPage, $offset]);
    $types .= "ii";

    // Execute the main query
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $logger->error("Error fetching applications: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while fetching your applications. Please try again later.";
    $applications = [];
    $totalPages = 0;
}

?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3 px-0 d-md-block sidebar collapse">
            <?php include 'sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="container">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>My Applications</h2>
                    <button type="button" class="btn btn-sm custom-btn" data-bs-toggle="modal" data-bs-target="#applicationFormModal">
                        Application Form
                        </button>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <!-- Filters and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" action="" class="row g-3">
                            <div class="col-md-5">
                                <label for="search" class="form-label">Search Applications</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($searchQuery); ?>" 
                                           placeholder="Search by name, service type, customer name, or email">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="missing_document" <?php echo $statusFilter === 'missing_document' ? 'selected' : ''; ?>>Missing Documents</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <a href="my_application.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-1"></i> Reset Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Applications List -->
                <div class="card">
                    <div class="card-body p-0">
                        <?php if (empty($applications)): ?>
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-file-alt fa-4x text-muted"></i>
                                </div>
                                <h4>No applications found</h4>
                                <p class="text-muted">You haven't submitted any applications yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Application #</th>
                                            <th>Customer Name</th>
                                            <th>Service Type</th>
                                            <th>Submitted On</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($applications as $app): 
                                            // Get status class
                                            $statusClass = 'bg-secondary';
                                            switch (strtolower($app['status'])) {
                                                case 'pending': $statusClass = 'bg-warning'; break;
                                                case 'approved': $statusClass = 'bg-success'; break;
                                                case 'missing_document': $statusClass = 'bg-info'; break;
                                                case 'rejected': $statusClass = 'bg-danger'; break;
                                            }
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($app['id'])): ?>
                                                        #<?php echo str_pad($app['id'], 6, '0', STR_PAD_LEFT); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($app['customer_name'] ?? $app['customer_name']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($app['service_type']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($app['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?php echo $statusClass; ?>">
                                                        <?php echo ucwords(str_replace('_', ' ', $app['status'])); ?>
                                                    </span>
                                                </td>
                                                <td class="text-nowrap">
                                                    <a href="view_application.php?id=<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary me-1" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger delete-application" 
                                                            data-id="<?php echo $app['id']; ?>"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav class="d-flex justify-content-center mt-4">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


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
                        <input type="text" name="phone" class="form-control" required>
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
                        <input type="file" name="document[]" class="form-control" multiple required>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this application? This action cannot be undone.</p>
                <p class="text-muted small">All associated documents will also be permanently deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Delete Application
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // meet kar lo
// Enhanced delete functionality with fallback
function handleDeleteClick(e) {
    e.preventDefault();
    
    // Get application ID from data attribute
    const applicationId = this.getAttribute('data-id');
    if (!applicationId) {
        console.error('No application ID found');
        return false;
    }
    
    const deleteUrl = 'delete_application.php?id=' + encodeURIComponent(applicationId);
    
    // Try to use Bootstrap modal if available
    try {
        const deleteModalEl = document.getElementById('deleteConfirmationModal');
        if (deleteModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            if (confirmBtn) {
                confirmBtn.href = deleteUrl;
                const deleteModal = new bootstrap.Modal(deleteModalEl);
                deleteModal.show();
                return true;
            }
        }
        
        // Fallback to native confirm dialog if Bootstrap modal fails
        if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
            window.location.href = deleteUrl;
        }
    } catch (error) {
        console.error('Error showing delete confirmation:', error);
        // Fallback to native confirm if there's an error with the modal
        if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
            window.location.href = deleteUrl;
        }
    }
    
    return false;
}

// Initialize delete handlers when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to existing delete buttons
    document.querySelectorAll('.delete-application').forEach(button => {
        button.addEventListener('click', handleDeleteClick);
    });
    
    // Also handle dynamically added delete buttons
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const newButtons = node.matches('.delete-application') ? [node] : 
                                    (node.querySelectorAll ? node.querySelectorAll('.delete-application') : []);
                    newButtons.forEach(button => {
                        button.addEventListener('click', handleDeleteClick);
                    });
                }
            });
        });
    });
    
    // Start observing the document with the configured parameters
    observer.observe(document.body, { childList: true, subtree: true });
});

// Fallback for jQuery if it's loaded after this script
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        $(document).off('click', '.delete-application').on('click', '.delete-application', handleDeleteClick);
    });
}
</script>

<?php include '../include/footer.php'; ?>