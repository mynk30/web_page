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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $applicationNumber = generateNewApplicationNumber($conn);

    $logger->info('Application number generated: ' . $applicationNumber);
    $browserLogger->info('Application number generated: ' . $applicationNumber);
    

    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception('Failed to start transaction'); 
    }
    

    // Validate phone and application_type
  
    $serviceType = trim($_POST['application_type'] ?? '');
    $logger->info("Service type: " . $serviceType);

    if (empty($serviceType)) {
        throw new Exception('Application type is required');
    }

    $logger->info("session...." . json_encode($_SESSION));
    // Get data from session
    $userId = $_SESSION['user_id'] ?? null;

   

    $logger->info("User ID: " . $userId);
    // $logger->info("I AM HERE...................");
    if (!$userId) {
        throw new Exception('User not logged in');
    }

    $applicationNumber = generateNewApplicationNumber($conn);
    $logger->info("Application number: " . $applicationNumber);

    $logger->info("AFTER....");
    // Insert application

    $stmt = $conn->prepare("INSERT INTO applications (application_number, user_id, service_type, payment_status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");

    $paymentStatus = 'pending'; // Jab nayi application submit ho to payment pending rahe
    
    if (!$stmt || !$stmt->bind_param("siss", $applicationNumber, $userId, $serviceType, $paymentStatus) || !$stmt->execute()) {
        throw new Exception('Failed to insert application');
    }
    

    $applicationId = $conn->insert_id;

    // Upload files
    $uploadedFiles = [];
    $targetDir = "../uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (isset($_FILES['document']['name']) && is_array($_FILES['document']['name'])) {
        foreach ($_FILES['document']['name'] as $key => $originalName) {
            if (empty($originalName)) continue;

            $timestamp = date('YmdHis');
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $base = pathinfo($originalName, PATHINFO_FILENAME);
            $uniqueName = $base . '_' . $timestamp . '.' . $ext;
            $targetPath = $targetDir . $uniqueName;

            if (!move_uploaded_file($_FILES['document']['tmp_name'][$key], $targetPath)) {
                throw new Exception('Failed to upload file: ' . $originalName);
            }

            $fileSize = filesize($targetPath);
            $modelType = 'application';

            $stmt = $conn->prepare("INSERT INTO files (original_name, file_name, file_path, file_size, model_type, model_id) VALUES (?, ?, ?, ?, ?, ?)");
            // FIXED: Changed "ssssssi" to "sssisi" to match 6 parameters: string, string, string, integer, string, integer
            if (!$stmt || !$stmt->bind_param("sssisi", $originalName, $uniqueName, $targetPath, $fileSize, $modelType, $applicationId) || !$stmt->execute()) {
                throw new Exception('Failed to save file: ' . $originalName);
            }

            $uploadedFiles[] = $uniqueName;
        }
    }

    $conn->commit();

    $response = [
        'success' => true,
        'message' => 'Application submitted successfully!',
        'redirect' => 'my_application.php'
    ];
    header('Location: ' . $response['redirect']);
    exit();
} catch (Exception $e) {
    $conn->rollback();
    $logger->info("This is error : " . $e->getMessage());

    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'redirect' => 'my_application.php' 
    ];

    header('Location: ' . $response['redirect']);
    exit();
}
echo json_encode($response);
?>