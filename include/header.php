<?php
session_start();
// IF THERE IS SESSION['user'] present then set the $_SESSION['user_id'] and $_SESSION['name'] AND $_SESSION['featured_image'] AND $_SESSION['email'] AND SESSION['phone'] FROM THE SESSION['user'] else set the $_SESSION['user_id'] and $_SESSION['name'] AND $_SESSION['featured_image'] AND $_SESSION['email'] AND SESSION['phone'] to null
if (isset($_SESSION['user'])) {
    $_SESSION['user_id'] = $_SESSION['user']['user_id'];
    $_SESSION['name'] = $_SESSION['user']['name'];
    $_SESSION['featured_image'] = $_SESSION['user']['featured_image'];
    $_SESSION['email'] = $_SESSION['user']['email'];
    $_SESSION['phone'] = $_SESSION['user']['phone'];
}
global $baseURL;
$baseURL = "http://" . $_SERVER['HTTP_HOST'] . "/web_page/";



require_once __DIR__ . '/../php/config.php';
global $logger, $browserLogger;

$logger->info("base usr is in header: ". $baseURL);
$imageSrc;

if(isset($_SESSION["user_id"]) && isset($_SESSION["featured_image"])){
    $imageSrc  = $baseURL . $_SESSION["featured_image"];
}else{
    $imageSrc = $baseURL . "uploads/profiles/user-avatar.png";
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="<?php echo $baseURL; ?>assests/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo $baseURL; ?>assests/fontawesome-free-6.7.2-web/css/all.min.css"/>
    <link rel="stylesheet" href="<?php echo $baseURL; ?>assests/css/style.css" />

    <title>PRAKASH JANGID & ASSOCIATES</title>
  </head>
  <body>
    
 
<!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="<?php echo $baseURL; ?>assests/img/logo.png" alt="">
                <h1 class="brand-title mb-0">PRAKASH JANGID & ASSOCIATES</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo $baseURL; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown  ">
                        <a class="nav-link dropdown-toggle  text-white" href="#services" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Services
                        </a>
                        <ul class="dropdown-menu no-radius" aria-labelledby="servicesDropdown">
                            <li><a class="dropdown-item" href="<?php echo $baseURL; ?>TDS.php">Tax Deducted Source</a></li>
                            <li><a class="dropdown-item" href="<?php echo $baseURL; ?>ITD.php">Income Tax Department</a></li>
                            <li><a class="dropdown-item" href="<?php echo $baseURL; ?>GST.php">Goods and Services Tax</a></li>
                            <li><a class="dropdown-item" href="<?php echo $baseURL; ?>MCA.php">Ministry of Corporate Affairs</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  text-white" href="<?php echo $baseURL; ?>about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  text-white" href="<?php echo $baseURL; ?>Contact.php">Contact</a>
                    </li>
                </ul>
                <?php if(isset($_SESSION['user_id'])): ?>   
                    <!-- User Profile Dropdown -->
                    <div class="dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex text-white align-items-center" href="#" id="userProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          
                                <img src="<?php echo htmlspecialchars ($imageSrc); ?>" 
                                     alt="Profile" 
                                     class="rounded-circle me-2" 
                                     style="width: 32px; height: 32px; object-fit: cover;">
                          
                            <span class="text-white"><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'User'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end no-radius" aria-labelledby="userProfileDropdown">
                            <li><a class="dropdown-item" href="<?php echo $baseURL; ?>account/dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $baseURL; ?>account/profile.php">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $baseURL; ?>account/my_application.php">
                                <i class="fas fa-file-alt me-2"></i>My Applications
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $baseURL; ?>auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $baseURL; ?>auth/login.php" class="btn btn-outline-light ms-lg-3">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

