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
if (!isset($_POST['application_id']) || !isset($_FILES['document_file']) || !isset($_POST['document_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$applicationId = intval($_POST['application_id']);
$documentName = trim($_POST['document_name']);
$userId = $_SESSION['user_id'];

// Verify that the application belongs to the user and get current status
$stmt = $conn->prepare("SELECT id, status FROM applications WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $applicationId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Application not found or access denied']);
    exit();
}

$application = $result->fetch_assoc();

// Get document info
$file = $_FILES['document_file'];
$originalName = basename($file['name']);
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];
$fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Sanitize the original filename
$sanitizedOriginalName = preg_replace("/[^a-zA-Z0-9_.]/", "_", $originalName);
$sanitizedDocumentName = preg_replace("/[^a-zA-Z0-9_\- ]/", "_", $documentName);

// Validate file
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        $logger->error("Failed to create upload directory: $uploadDir");
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit();
    }
}

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
            $errorMessage = 'The uploaded file is too large. Maximum size: 5MB';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errorMessage = 'The uploaded file was only partially uploaded';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errorMessage = 'No file was selected for upload';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $errorMessage = 'Missing temporary folder';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $errorMessage = 'Failed to write file to disk';
            break;
        case UPLOAD_ERR_EXTENSION:
            $errorMessage = 'File upload stopped by PHP extension';
            break;
    }
    
    $logger->error("File upload error ($fileError): $errorMessage");
    echo json_encode(['success' => false, 'message' => $errorMessage]);
    exit();
}

// Generate unique filename with original extension
$uniqueId = uniqid('', true);
$newFileName = "app_{$applicationId}_" . substr(md5($sanitizedDocumentName . $uniqueId), 0, 8) . ".$fileExt";
$destination = $uploadDir . $newFileName;

// Ensure the filename is unique
$counter = 1;
while (file_exists($destination)) {
    $newFileName = "app_{$applicationId}_" . substr(md5($sanitizedDocumentName . $uniqueId . $counter), 0, 8) . ".$fileExt";
    $destination = $uploadDir . $newFileName;
    $counter++;
}

// Move the uploaded file
if (!move_uploaded_file($fileTmpName, $destination)) {
    $logger->error("Failed to move uploaded file to $destination");
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
    exit();
}

// Set file permissions
chmod($destination, 0644);

// Begin transaction
$conn->begin_transaction();

try {
    // Check if document already exists for this application and document name
    $stmt = $conn->prepare("SELECT id, file_path FROM files WHERE model_type = 'application' AND model_id = ? AND document_name = ?");
    $stmt->bind_param('is', $applicationId, $documentName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing document
        $row = $result->fetch_assoc();
        $oldFilePath = $uploadDir . $row['file_path'];
        
        // Delete old file
        if (file_exists($oldFilePath)) {
            if (!unlink($oldFilePath)) {
                throw new Exception('Failed to remove old file');
            }
        }
        
        $stmt = $conn->prepare("UPDATE files SET file_path = ?, original_name = ?, uploaded_at = NOW() WHERE id = ?");
        $stmt->bind_param('ssi', $newFileName, $sanitizedOriginalName, $row['id']);
    } else {
        // Insert new document
        $stmt = $conn->prepare("INSERT INTO files (model_type, model_id, document_name, file_path, original_name, uploaded_at) VALUES ('application', ?, ?, ?, ?, NOW())");
        $stmt->bind_param('isss', $applicationId, $documentName, $newFileName, $sanitizedOriginalName);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to save document information to database');
    }

    // Update application status if it was rejected or missing documents
    if (in_array($application['status'], ['rejected', 'missing_document'])) {
        $updateStmt = $conn->prepare("UPDATE applications SET status = 'pending', updated_at = NOW() WHERE id = ?");
        $updateStmt->bind_param('i', $applicationId);
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update application status');
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Log the successful upload
    $logger->info("Document '$documentName' uploaded for application #$applicationId");
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Document uploaded successfully',
        'document' => [
            'name' => $documentName,
            'original_name' => $sanitizedOriginalName,
            'file_path' => $newFileName,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Delete the uploaded file if it exists
    if (file_exists($destination)) {
        @unlink($destination);
    }
    
    // Log the error
    $errorMsg = $e->getMessage();
    $logger->error("Document upload failed: $errorMsg");
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to process document upload: ' . $errorMsg
    ]);
}

// Close database connection
$conn->close();
?>
