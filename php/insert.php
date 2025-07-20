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
        
        // sql query to create tabel contact_form
        // CREATE TABLE contact_form (
        //     id INT(11) AUTO_INCREMENT PRIMARY KEY,
        //     name VARCHAR(100) NOT NULL,
        //     email VARCHAR(100) NOT NULL,
        //     mobile VARCHAR(15) NOT NULL,
        //     subject VARCHAR(100) NOT NULL,
        //     message TEXT NOT NULL
        // );
        
        //sql query to create tabel service_form
        // CREATE TABLE service_form (      
        //     id INT(11) AUTO_INCREMENT PRIMARY KEY,
        //     name VARCHAR(100) NOT NULL,      
        //     email VARCHAR(100) NOT NULL,
        //     mobile VARCHAR(15) NOT NULL,
        //     bname VARCHAR(100) NOT NULL,
        //     service VARCHAR(100) NOT NULL,
        //     message TEXT NOT NULL
        // );

        
        
    } 
    elseif($_POST['forms']=='mca'){
        $mobile = $_POST['phone'];
        $bname = $_POST['business'];
        $service = $_POST['service'];

        $sql = "INSERT INTO service_form(`name`,`email`,`mobile`,`bname`,`service`,`message`) VALUES ('$name', '$email', '$mobile', '$bname', '$service', '$message')";
    }

    else {
        $sql = "INSERT INTO feedback_form(`name`,`email`,`message`) VALUES ('$name', '$email', '$message')";
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