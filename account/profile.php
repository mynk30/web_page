
<?php 
include '../include/header.php';
require_once '../php/db.php';

global $logger, $browserLogger;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$user = null;

try {
    // Fetch user data from users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Fetch latest application data for phone and address
        $appStmt = $conn->prepare("SELECT phone, address FROM applications WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $appStmt->bind_param("i", $userId);
        $appStmt->execute();
        $appResult = $appStmt->get_result();
        
        if ($appResult->num_rows > 0) {
            $appData = $appResult->fetch_assoc();
            // Merge application data with user data
            $user['phone'] = $appData['phone'];
            $user['address'] = $appData['address'];
        }
        
        $appStmt->close();
    } else {
        throw new Exception("User not found");
    }
    
    $stmt->close();
} catch (Exception $e) {
    $logger->error("Error fetching user profile: " . $e->getMessage());
    $browserLogger->log("Error fetching user profile: " . $e->getMessage());
    $_SESSION['error'] = "Error loading profile. Please try again later.";
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <?php include 'sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Profile</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if ($user): ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header  text-white">
                            <h5 class="mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Full Name:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user['name'] ?? 'Not set'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Email:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Phone:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Address:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user['address'] ?? 'Not set'); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <a href="edit_profile.php" class="btn custom-btn form-btn ">
                                        <i class="fas fa-edit me-1 text-white"></i> Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
             
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../include/footer.php'; ?>