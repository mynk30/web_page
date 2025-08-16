<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

require_once '../vendor/autoload.php';


function sendMail(string $mailTemplate, string $subject, array $data, string $toEmail, string $toName = ''): bool {
    $mail = new PHPMailer(true);
    try {
        // Setup Twig
        $loader = new FilesystemLoader('../templates/mail/');
        $twig = new Environment($loader);

        // Render the email HTML with dynamic data
        $fullTemplateName = $mailTemplate . '.html.twig';
        $emailBody = $twig->render($fullTemplateName, $data);

        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.ethereal.email'; // 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cletus44@ethereal.email'; // replace with your SMTP username
        $mail->Password   = 'WMEtXeAXkcQ5E7vswj';           // replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email setup
        $mail->setFrom('cletus44@ethereal.email', 'Admin');  // replace as needed
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $emailBody;

        $mail->send();
        $_SESSION['recipientEmail'] = $toEmail;

        return true;
    } catch (Exception $e) {
        // optionally log or handle $mail->ErrorInfo
        return false;
    }
}
