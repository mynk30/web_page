<?php
session_start();
require_once '../php/db.php';
require_once '../php/config.php';
global $logger, $browserLogger;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['application_id']) || !isset($_FILES['document_file']) || !isset($_POST['document_type'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$applicationId = intval($_POST['application_id']);
$documentType = $_POST['document_type'];
$userId = $_SESSION['user_id'];

// Verify that the application belongs to the user
$stmt = $conn->prepare("SELECT id FROM applications WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $applicationId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Application not found or access denied']);
    exit();
}

// Get document info
$file = $_FILES['document_file'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

if (!in_array($fileExt, $allowedExtensions)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions)]);
    exit();
}

if ($fileSize > $maxFileSize) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size: 5MB']);
    exit();
}

if ($fileError !== UPLOAD_ERR_OK) {
    $errorMessage = 'File upload error';
    switch ($fileError) {
        case UPLOAD_ERR_INI_SIZE:
            $errorMessage = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $errorMessage = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errorMessage = 'The uploaded file was only partially uploaded';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errorMessage = 'No file was uploaded';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $errorMessage = 'Missing a temporary folder';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $errorMessage = 'Failed to write file to disk';
            break;
        case UPLOAD_ERR_EXTENSION:
            $errorMessage = 'A PHP extension stopped the file upload';
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $errorMessage]);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/documents/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$newFileName = uniqid('doc_', true) . '.' . $fileExt;
$destination = $uploadDir . $newFileName;

// Move the uploaded file
if (!move_uploaded_file($fileTmpName, $destination)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
    exit();
}

// Check if document already exists for this application and document type
$stmt = $conn->prepare("SELECT id, file_path FROM application_documents WHERE application_id = ? AND document_type = ?");
$stmt->bind_param('is', $applicationId, $documentType);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing document
    $row = $result->fetch_assoc();
    $oldFilePath = $uploadDir . $row['file_path'];
    
    // Delete old file
    if (file_exists($oldFilePath)) {
        unlink($oldFilePath);
    }
    
    $stmt = $conn->prepare("UPDATE application_documents SET file_path = ?, original_filename = ?, uploaded_at = NOW() WHERE id = ?");
    $stmt->bind_param('ssi', $newFileName, $fileName, $row['id']);
} else {
    // Insert new document
    $stmt = $conn->prepare("INSERT INTO application_documents (application_id, document_type, document_name, file_path, original_filename, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
    
    // Get document name from required_documents
    $docStmt = $conn->prepare("SELECT document_name FROM required_documents WHERE document_type = ? LIMIT 1");
    $docStmt->bind_param('s', $documentType);
    $docStmt->execute();
    $docResult = $docStmt->get_result();
    $docName = $docResult->num_rows > 0 ? $docResult->fetch_assoc()['document_name'] : $documentType;
    
    $stmt->bind_param('issss', $applicationId, $documentType, $docName, $newFileName, $fileName);
}

if ($stmt->execute()) {
    // Update application status if it was rejected or missing documents
    $updateStmt = $conn->prepare("UPDATE applications SET status = 'pending' WHERE id = ? AND (status = 'rejected' OR status = 'missing_document')");
    $updateStmt->bind_param('i', $applicationId);
    $updateStmt->execute();
    
    // Log the upload
    $logger->info("Document uploaded for application #$applicationId: $documentType");
    $browserLogger->log("Document uploaded for application #$applicationId: $documentType");
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Document uploaded successfully']);
} else {
    // Delete the uploaded file if database update fails
    if (file_exists($destination)) {
        unlink($destination);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to save document information to database']);
}

$conn->close();
?>
