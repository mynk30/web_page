<?php
session_start();
require_once('../php/db.php');
require_once('../php/config.php');
global $logger, $browserLogger;

$logger->info('Login register page accessed');
// $logger->info('Session data: ' . json_encode($_SESSION));

if (isset($_POST['register'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user'; // Correct role value is 'user', not 'users'


    $checkEmail = $conn->query("SELECT * FROM users WHERE email = '$email'");
    $logger->info("checkMail " . json_encode($checkEmail));
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = "Email already exists!";
        $_SESSION['active_form'] = "register";
        header("Location: login.php");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        $stmt->close();
        header("Location: login.php");
        exit();
    }
}

if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $logger->info("email " . json_encode($email));
    $logger->info("password " . json_encode($password));

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    $logger->info("Executed query: SELECT * FROM users WHERE email = '$email'");

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $logger->info("User Row: " . json_encode($user));

        if (password_verify($password, $user['password'])) {
            $logger->info("password verify success");

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['featured_image'] = $user['featured_image'];
            $_SESSION['role'] = $user['role'];

            $logger->info("This is session after login: " . json_encode($_SESSION));
            // $base_url = "http://" . $_SERVER['HTTP_HOST'] . "/web_page/";
            header("Location: ../");
            exit();
        } else {
            $logger->info("Invalid password.");
            $_SESSION['login_error'] = "Invalid email or password!";
            $_SESSION['active_form'] = "login";
            header("Location: login.php");
            exit();
        }
    } else {
        $logger->info("No user found with email: " . $email);
        $_SESSION['login_error'] = "Invalid email or password!";
        $_SESSION['active_form'] = "login";
        header("Location: login.php");
        exit();
    }
}

?>
