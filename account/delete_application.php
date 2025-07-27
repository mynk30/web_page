<?php
require_once '../php/db.php';
require_once '../php/config.php';
// require_once '../php/logger.php';
global $logger, $browserLogger;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to perform this action.';
    header('Location: login.php');
    exit();
}

// Check if application ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid application ID.';
    header('Location: my_application.php');
    exit();
}

$applicationId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];


// Log the deletion attempt
$logger->info("Attempting to delete application ID: $applicationId by user ID: $userId");

try {
    // Begin transaction
    $conn->begin_transaction();

    // 1. First, verify the application belongs to the user
    $checkStmt = $conn->prepare("SELECT id, user_id FROM applications WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $applicationId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $logger->info("Result: " . $result->num_rows);
    // return;
    
    if ($result->num_rows === 0) {
        throw new Exception("Application not found or you don't have permission to delete it.");
    }
    
    $checkStmt->close();
    
    // 2. Get document filenames before deleting records
    $docStmt = $conn->prepare("SELECT file_name FROM files WHERE model_id = ? AND model_type = 'application'");
    $docStmt->bind_param("i", $applicationId);
    $docStmt->execute();
    $docResult = $docStmt->get_result();
    $docStmt->close();
    
    // 3. Delete from files table
    $deleteDocsStmt = $conn->prepare("DELETE FROM files WHERE model_id = ? AND model_type = 'application'");
    $deleteDocsStmt->bind_param("i", $applicationId);
    $deleteDocsStmt->execute();
    $deleteDocsStmt->close();
    
    // 4. Delete from applications table
    $deleteAppStmt = $conn->prepare("DELETE FROM applications WHERE id = ? AND user_id = ?");
    $deleteAppStmt->bind_param("ii", $applicationId, $userId);
    $deleteAppStmt->execute();
    $rowsAffected = $deleteAppStmt->affected_rows;
    $deleteAppStmt->close();
    
    if ($rowsAffected === 0) {
        throw new Exception("Failed to delete application or application not found.");
    }
    
    // 5. Delete document files from server
    $uploadDir = '../uploads/applications/';
    while ($doc = $docResult->fetch_assoc()) {
        $filePath = $uploadDir . $doc['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    $logger->info("Successfully deleted application ID: $applicationId by user ID: $userId");
    
    $_SESSION['success'] = 'Application deleted successfully.';
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn) {
        $conn->rollback();
    }
    
    $errorMsg = 'Error deleting application: ' . $e->getMessage();
    $logger->info($errorMsg);
    $_SESSION['error'] = $errorMsg;
}

// Redirect back to the applications page
header('Location: my_application.php');
exit();
