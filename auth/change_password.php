<?php 
include '../include/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $baseURL . 'auth/login.php');
    exit();
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logger->info("Password change request initiated");
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match';
    }
    //  elseif (strlen($new_password) < 8) {
    //     $error = 'New password must be at least 8 characters long';
    // }
     else {
        // Get current password hash from database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $userPassword = $result->fetch_assoc();
        $logger->info("Fetching user data for password verification: " . json_encode($userPassword));

        if(password_verify($current_password, $userPassword['password'])){
            $logger->info("CORRECT PASSWORD HAI ");
        }else{
            $logger->warning("GLAT PASSWORD HAI");
        }


        if ($userPassword && password_verify($current_password, $userPassword['password'])) {


            $logger->info("CORRECT PASSWORD HAI Current password is correct for user ID $user_id");
            // Current password is correct, update to new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            // 1234 -> #$ASDFasDGASDF
            // 4321 -> #$ASDFasDGASDF (1234) === 4321
            $update_stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->bind_param("si", $new_password_hash, $user_id);
            
            if ($update_stmt->execute()) {
                $success = 'Password updated successfully';
                $logger->info("User ID $user_id changed their password successfully");
                $browserLogger->log("Password changed for user ID $user_id");
            } else {
                $error = 'Failed to update password. Please try again.';
                $logger->error("Failed to update password for user ID $user_id: " . $conn->error);
            }
            
            $update_stmt->close();
        } else {
            $logger->warning("Incorrect current password attempt for user ID $user_id");
            $error = 'Current password is incorrect';
        }
        
        $stmt->close();
    }
}
?>



<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <?php include 'sidebar.php'; ?>
        </div>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Change Password</h1>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-key me-2"></i>Change Your Password</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo htmlspecialchars($success); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="passwordForm">
                                <!-- Current Password -->
                                <div class="mb-4">
                                    <label for="current_password" class="form-label fw-bold">
                                        <i class="fas fa-lock me-1"></i>Current Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-unlock"></i></span>
                                        <input type="password" class="form-control form-control-lg" 
                                               id="current_password" name="current_password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye" id="current_password_icon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- New Password -->
                                <div class="mb-4">
                                    <label for="new_password" class="form-label fw-bold">
                                        <i class="fas fa-key me-1"></i>New Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-plus-circle"></i></span>
                                        <input type="password" class="form-control form-control-lg" 
                                               id="new_password" name="new_password" required minlength="3"
                                               oninput="checkPasswordStrength(); checkPasswordMatch();">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye" id="new_password_icon"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="strength-bar"></div>
                                    <div class="form-text text-muted small mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Password must be at least 8 characters long with letters and numbers.
                                    </div>
                                </div>
                                
                                <!-- Confirm New Password -->
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-bold">
                                        <i class="fas fa-check-circle me-1"></i>Confirm New Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                        <input type="password" class="form-control form-control-lg" 
                                               id="confirm_password" name="confirm_password" required
                                               oninput="checkPasswordMatch();">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye" id="confirm_password_icon"></i>
                                        </button>
                                    </div>
                                    <div class="match-indicator" id="match-indicator"></div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="form-btn custom-btn  btn-lg" id="submitBtn">
                                        <i class="fas fa-save me-2 text-white"></i>Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>


<?php include '../include/footer.php'; ?>