<?php 
include '../include/header.php';

// Check if user came from OTP verification and has an email in the session
// yaha pr ek codiition wrong ho rhi thi isliey wo rediret kar rah tha
if (!isset($_SESSION['recipientEmail']) || !isset($_SESSION['otp_verified']) ) {
    $logger->info("Invalid password reset request. Please request a new OTP.");
    $_SESSION['error'] = 'Invalid password reset request. Please request a new OTP.';
    header('Location: ' . $baseURL . 'auth/forgotPassword.php');
    exit();
}

$email = $_SESSION['recipientEmail'];
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error']);
unset($_SESSION['success']);


// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logger->info("Password reset request initiated for email: " . $email);
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($new_password) || empty($confirm_password)) {

        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Update the password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW(), otp = NULL, otp_expiry = NULL WHERE email = ?");
        $update_stmt->bind_param("ss", $new_password_hash, $email);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = 'Password has been reset successfully. You can now login with your new password.';
            $logger->info("Password reset successfully for email: PASSWORD RESET HO GAYA HAI" . $email);
            
            // Clear the OTP verification session
            unset($_SESSION['otp_verified']);
            unset($_SESSION['recipientEmail']);
            
            $logger->info("yaah pr redirect kar dooo");
            // Redirect to login after 3 seconds
            header('Location: ' . $baseURL . 'auth/login.php');
        } else {
            $error = 'Failed to reset password. Please try again.';
            $logger->error("Failed to reset password for email: " . $email . " - " . $conn->error);
        }
        
        $update_stmt->close();
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 auth-form-container">
            <div class="auth-form-box auth-form-active">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4 custom-heading">Reset Password</h2>

                        <?php if ($error): ?>
                            <p class="error-message"><?= htmlspecialchars($error); ?></p>
                        <?php endif; ?>
                            
                        <?php if ($success): ?>
                            <p class="success-message"><?= htmlspecialchars($success); ?></p>
                        <?php endif; ?>
                            
                        <form method="POST" action="" id="passwordForm">
                            <!-- New Password -->
                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-bold">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                </div>
                            </div>
                            
                            <!-- Confirm New Password -->
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn custom-btn w-100">Update Password</button>
                            </div>
                            <p class="text-center mt-3"><a href="login.php" class="text-decoration-none">Back to Login</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../include/footer.php'; ?>
