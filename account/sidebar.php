<?php

require_once '../php/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 250px; min-height: 100vh;">
    <p class="fw-bold">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></p>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="/web_page/account/dashboard.php" class="nav-link <?php echo ($current_page === 'dashboard.php') ? 'active' : 'link-dark'; ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="/web_page/account/profile.php" class="nav-link <?php echo ($current_page === 'profile.php') ? 'active' : 'link-dark'; ?>">
                <i class="fas fa-user me-2"></i> My Profile
            </a>
        </li>
        <li>
            <a href="/web_page/account/my_application.php" class="nav-link <?php echo ($current_page === 'my_application.php') ? 'active' : 'link-dark'; ?>">
                <i class="fas fa-file-alt me-2"></i> My Application
            </a>
        </li>
        <li>
            <a href="/web_page/account/change_password.php" class="nav-link <?php echo ($current_page === 'change_password.php') ? 'active' : 'link-dark'; ?>">
                <i class="fas fa-key me-2"></i> Change Password
            </a>
        </li>
        <li>
            <a href="/web_page/auth/logout.php" class="nav-link link-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>
