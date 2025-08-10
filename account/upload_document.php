<?php
session_start();
require_once '../php/db.php';
require_once '../php/config.php';
global $logger, $browserLogger;

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['application_id']) || !isset($_FILES['document_file']['name']) || !is_array($_FILES['document_file']['name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}



$applicationId = intval($_POST['application_id']);
$logger->info("Application ID: in the upload document" . $applicationId);


$targetDir = "../uploads/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

try {
    //code...

if (isset($_FILES['document_file']['name']) && is_array($_FILES['document_file']['name'])) {
    foreach ($_FILES['document_file']['name'] as $key => $originalName) {
        if (empty($originalName)) continue;

        $timestamp = date('YmdHis');
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $uniqueName = $base . '_' . $timestamp . '.' . $ext;
        $targetPath = $targetDir . $uniqueName;
 
        //  log timestamp, ext, base, uniqueName, targetPath
        $logger->info("Timestamp: " . $timestamp);
        $logger->info("Extension: " . $ext);
        $logger->info("Base: " . $base);
        $logger->info("Original Name: " . $originalName);
        $logger->info("Unique Name: " . $uniqueName);
        $logger->info("Target Path: " . $targetPath);

        
        if (!move_uploaded_file($_FILES['document_file']['tmp_name'][$key], $targetPath)) {
            throw new Exception('Failed to upload file: ' . $originalName);
        }
        
        $fileSize = filesize($targetPath);
        $modelType = 'application';

        $logger->info("File size: " . $fileSize);
        $logger->info("Model type: " . $modelType);

        $stmt = $conn->prepare("INSERT INTO files (original_name, file_name, file_path, file_size, model_type, model_id) VALUES (?, ?, ?, ?, ?, ?)");
        // FIXED: Changed "ssssssi" to "sssisi" to match 6 parameters: string, string, string, integer, string, integer
        if (!$stmt || !$stmt->bind_param("sssisi", $originalName, $uniqueName, $targetPath, $fileSize, $modelType, $applicationId) || !$stmt->execute()) {
            throw new Exception('Failed to save file: ' . $originalName);
        }

        $uploadedFiles[] = $uniqueName;
    }

    $stmt = $conn->prepare("UPDATE applications SET status = 'pending', required_documents = NULL WHERE id = ?");
    // FIXED: Changed "
    if (!$stmt || !$stmt->bind_param("i", $applicationId) || !$stmt->execute()) {
        throw new Exception('Failed to update application status');
    }

    header("Location: view_application.php?id=" . $applicationId);
    exit();
}


// Close database connection
$conn->close();

} catch (\Throwable $th) {
    //throw $th;
    $logger->info("Error aaa gahi hia: " . $th->getMessage());
    header("Location: view_application.php?id=" . $applicationId);
}
?>
