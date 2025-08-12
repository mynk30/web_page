<?php 
require_once "../include/header.php";
require_once "../php/mailConfig.php";

$email = $_SESSION['recipientEmail'] ?? '';
$error = '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);


// Process email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $logger->info("User found: with email " . $email . " and " . json_encode($user));

    if ($user !== null) {
        $otp = rand(1000, 9999);
        $otp_expiry_timestamp = time() + 600; // 10 min as timestamp
        $otp_expiry = date('Y-m-d H:i:s', $otp_expiry_timestamp); // Convert for MySQL

        $logger->info("OTP generated for email: " . $email . " and OTP is: " . $otp);
        // print the otp expiry 
        $logger->info("OTP expiry for email: " . $email . " and OTP expiry is: " . $otp_expiry);


        // yaha pr apan ne user ko update kar diya isliye purana otp show ho raha hai

        $stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
        $stmt->bind_param("iss", $otp, $otp_expiry, $email);
        $stmt->execute();


        $data = [
            "name" => $user['name'],
            "otp" => $otp,
            
        ];

        // yaha par mainse sirf name aur otp pass kr diya hai latest wala  ek new var bana ke
        $emailResponse = sendMail('forgotPassword', 'OTP Verification', $data, $email);



        if ($emailResponse) {
            $_SESSION['recipientEmail'] = $email;
            $_SESSION['success'] = 'An OTP has been sent to your email.';
            header('Location: ' . $baseURL . 'auth/forgotPassword.php');
            exit;
        } else {
             $error = 'Failed to send OTP. Please try again.';
        }
    } else {
        $error = 'Email not found.';
    }
}

// Process OTP submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp']) && isset($_SESSION['recipientEmail'])) {
    $email = $_SESSION['recipientEmail'];
    $otp = $_POST['otp'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND otp = ? AND otp_expiry > ?");
    $currentTime = date('Y-m-d H:i:s');
    $stmt->bind_param("sss", $email, $otp, $currentTime);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user !== null) {
        $_SESSION['otp_verified'] = true;
        
        $logger->info("OTP verified successfully for email================: " . $email);
        header('Location: ' . $baseURL . 'auth/resetPassword.php?email=' . $email);
        exit;
    } else {
        $error = 'Invalid or expired OTP.';
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 auth-form-container">
            <div class="auth-form-box auth-form-active">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4 custom-heading">Forgot Password</h2>
                         <?php if ($error): ?>
                            <p class='error-message'><?= htmlspecialchars($error); ?></p>
                        <?php endif; ?>
                         <?php if ($success): ?>
                            <p class='success-message'><?= htmlspecialchars($success); ?></p>
                        <?php endif; ?>
                        
                        <!-- Email submission form -->
                        <form action="" method="post">
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" id="email" placeholder="Enter your email" required>
                            </div>
                            <button type="submit" class="btn custom-btn w-100">Send OTP</button>
                        </form>

                        <!-- OTP verification form, shown only after email is submitted -->
                        <?php if (isset($_SESSION['recipientEmail'])): ?>
                            <hr class="my-4">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="otp" id="otp" placeholder="Enter OTP" required>
                                </div>
                                <button type="submit" class="btn custom-btn w-100">Verify OTP</button>
                            </form>
                        <?php endif; ?>
                        
                        <p class="text-center mt-3"><a href="login.php" class="text-decoration-none">Back to Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once "../include/footer.php";
?>
