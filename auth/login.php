<?php 
require_once "../include/header.php"?>
<?php
// session_start();

require_once __DIR__ . '../../php/config.php';
global $logger, $browserLogger;

$logger->info('Login page accessed');
// $logger->info('Session data: ' . json_encode($_SESSION));
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error){
    return !empty($error) ? "<p class='error'>$error</p>" : '';
}
function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 auth-form-container">
            <!-- Login Form -->
            <div class="auth-form-box <?= isActiveForm('login', $activeForm) ?>" id="login-form">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4 custom-heading">Login</h2>
                        <?= showError($errors['login']); ?>
                        <form action="login_register.php" method="post">
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                            </div>
                            <button type="submit" name="login" class="btn custom-btn w-100">Login</button>
                            <p class="text-center mt-3">Don't have an account? <a href="#" data-auth-form-toggle="register-form" class="text-decoration-none">Register</a></p>
                            <p class="text-center">Forgot Password? <a href="forgotPassword.php" class="text-decoration-none">Reset</a></p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Register Form -->
            <div class="auth-form-box <?= isActiveForm('register', $activeForm) ?>" id="register-form">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4 custom-heading">Register</h2>
                        <?= showError($errors['register']); ?>
                        <form action="login_register.php" method="post">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" id="reg-email" placeholder="Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" class="form-control" name="mobile" id="mobile" placeholder="Mobile" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" name="password" id="reg-password" placeholder="Password" required>
                            </div>
                            <button type="submit" name="register" class="btn custom-btn w-100">Register</button>
                            <p class="text-center mt-3">Already have an account? <a href="#" data-auth-form-toggle="login-form">Login</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php require_once "../include/footer.php"?>