<?php 
require_once "../include/header.php";
require_once "../php/mailConfig.php";

$email = $_SESSION['recipientEmail'] ?? '';

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


$stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
$stmt->bind_param("iss", $otp, $otp_expiry, $email);
$stmt->execute();

        $emailResponse = sendMail('forgotPassword', 'OTP Verification', $user, $email);
        
        if ($emailResponse) {
            $_SESSION['success'] = 'A password reset link has been sent to your email.';
            header('Location: ' . $baseURL . 'auth/forgotPassword.php');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp']) && isset($_SESSION['recipientEmail'])) {
    $otp = $_POST['otp'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND otp = ? AND otp_expiry > ?");
    $currentTime = date('Y-m-d H:i:s');
    $stmt->bind_param("sss", $email, $otp, $currentTime);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $logger->info("OTP VERIFIED User found: with email " . $email . " and " . json_encode($user));
    unset($_SESSION['recipientEmail']);
    
    return;
    if ($user !== null) {
        $logger->info("OTP verified successfully for email: " . $email);
        header('Location: ' . $baseURL . 'auth/resetPassword.php?email=' . $email);
        exit;
    }
    
}
?>

<form action="" method="post">
    <div class="mb-3">
        <input type="email" class="form-control" name="email" value="<?php echo $email; ?>" id="email" placeholder="Email" required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<?php if (isset($_SESSION['recipientEmail'])): ?>
<form action="" method="post">
    <div class="mb-3">
        <input type="text" class="form-control" name="otp" id="otp" placeholder="OTP" required>
    </div>
    <button type="submit" class="btn btn-primary">Verify</button>
</form>

<?php endif; ?>

<?php 
require_once "../include/footer.php";
?>