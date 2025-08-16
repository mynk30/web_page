<?php
session_start();
require_once '../php/config.php';
require_once '../php/db.php';
global $logger;

// header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

function generateNewApplicationNumber($conn): string {
// <<<<<<< HEAD
    $query = "SELECT application_number FROM applications ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $lastNumber = (int)$row['application_number'];
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    return str_pad((string)$newNumber, 6, '0', STR_PAD_LEFT);
}



// =======
    try {
        $query = "SELECT application_number FROM applications ORDER BY id DESC LIMIT 1";
        $result = $conn->query($query);

        if ($result && $row = $result->fetch_assoc()) {
            $lastNumber = (int)$row['application_number'];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return str_pad((string)$newNumber, 6, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        return '000101'; 
    }


// >>>>>>> upstream/main
try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get user ID from session
    $userId = $_SESSION['user_id'];
    
    // Validate required fields
    $requiredFields = ['application_type'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missingFields));
    }
    
    // Generate application number (format: APP-YYYYMMDD-XXXXXX)
    $appNumber = 'APP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Get form data
    $applicationType = $_POST['application_type'];
    $status = 'pending';
    $paymentStatus = 'pending'; // Default payment status
    
    // Start database transaction
    $conn->begin_transaction();
    
    try {
        // Insert application record
        $stmt = $conn->prepare("INSERT INTO applications (user_id, application_number, service_type, status, payment_status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $userId, $appNumber, $applicationType, $status, $paymentStatus);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create application: ' . $stmt->error);
        }
        
        $applicationId = $conn->insert_id;
        
        // Handle file uploads if any
        if (!empty($_FILES['document'])) {
            $uploadDir = __DIR__ . '/../uploads';
            
            // Create upload directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('Failed to create upload directory');
                }
            }
            
            // Check if documents were uploaded
            if (is_array($_FILES['document']['name'])) {
                // Process each uploaded file
                foreach ($_FILES['document']['name'] as $index => $name) {
                    if ($_FILES['document']['error'][$index] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['document']['tmp_name'][$index];
                        $fileType = $_FILES['document']['type'][$index];
                        $fileSize = $_FILES['document']['size'][$index];
                        $error = $_FILES['document']['error'][$index];
                        
                        // Basic file validation
                        if ($error !== UPLOAD_ERR_OK) {
                            throw new Exception('File upload error: ' . $error);
                        }
                        
                        // Generate secure filename
                        $fileName = generateSecureFilename($name);
                        $filePath = $uploadDir . $fileName;
                        
                        // Verify file is an actual file
                        if (!is_uploaded_file($tmpName)) {
                            throw new Exception('Possible file upload attack');
                        }
                        
                        // Verify file size (max 5MB)
                        $maxFileSize = 5 * 1024 * 1024; // 5MB
                        if ($fileSize > $maxFileSize) {
                            throw new Exception('File size exceeds maximum allowed size of 5MB');
                        }
                        
                        // Allow only specific file types
                        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                        if (!in_array($fileType, $allowedTypes)) {
                            throw new Exception('Invalid file type. Only PDF, JPG, and PNG files are allowed.');
                        }
                        
                        // Move uploaded file
                        if (move_uploaded_file($tmpName, $filePath)) {
                            // Insert file record into database
                            $stmt = $conn->prepare("INSERT INTO files (application_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
                            $relativePath = 'uploads/applications/' . $applicationId . '/' . $fileName;
                            $stmt->bind_param('isssi', $applicationId, $name, $relativePath, $fileType, $fileSize);
                            
                            if (!$stmt->execute()) {
                                throw new Exception('Failed to save file information: ' . $stmt->error);
                            }
                        } else {
                            throw new Exception('Failed to move uploaded file');
                        }
                    }
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => 'Application submitted successfully',
            'application_id' => $applicationId,
            'application_number' => $appNumber,
            'redirect' => 'my_application.php'
        ];
        
        // Log successful submission
        $logger->info("Application submitted successfully", [
            'application_id' => $applicationId,
            'user_id' => $userId,
            'application_number' => $appNumber
        ]);
        
        // Set success message in session
        $_SESSION['success'] = 'Application submitted successfully!';
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ];
    
    // Log error
    $logger->error("Application submission failed", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'post_data' => $_POST,
        'files' => $_FILES
    ]);
    
    // Set error message in session
    $_SESSION['error'] = 'Failed to submit application: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);