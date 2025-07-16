<?php

include './db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    if($_POST['forms']=='contact'){
        $mobile = $_POST['phone'];
        $subject = $_POST['subject'];
       
        $sql = "INSERT INTO contact_form(`name`,`email`,`mobile`,`subject`,`message`) VALUES ('$name', '$email', '$mobile', '$subject', '$message')";
        
    } 
    elseif($_POST['forms']=='mca'){
        $mobile = $_POST['phone'];
        $bname = $_POST['business'];
        $service = $_POST['service'];

        $sql = "INSERT INTO service_form(`name`,`email`,`mobile`,`bname`,`service`,`message`) VALUES ('$name', '$email', '$mobile', '$bname', '$service', '$message')";
    }
    if ($conn->query($sql) === true){
        if (isset($_POST['ajax_submit']) && $_POST['ajax_submit'] == '1') {
        echo "success";
    } else {
        echo "<script>alert('Form submitted successfully!');history.back();</script>";
    }
    } else {
        echo "Error: " . $conn->error;
    }
    
    $conn->close();
}


?>