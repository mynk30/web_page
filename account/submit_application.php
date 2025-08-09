<?php
session_start();
require_once '../php/db.php';
require_once '../php/config.php';
global $logger, $browserLogger;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

function generateNewApplicationNumber($conn): string {
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



try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $applicationNumber = generateNewApplicationNumber($conn);

    $logger->info('Application number generated: ' . $applicationNumber);
    $browserLogger->info('Application number generated: ' . $applicationNumber);
    return;

    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception('Failed to start transaction');
    }

    // Validate phone and application_type
    $phone = trim($_POST['phone'] ?? '');
    $serviceType = trim($_POST['application_type'] ?? '');

    if (empty($phone) || empty($serviceType)) {
        throw new Exception('Phone number and application type are required');
    }

    // Get data from session
    $userId = $_SESSION['user_id'] ?? null;
    $userName = $_SESSION['name'] ?? '';
    $email = $_SESSION['email'] ?? '';

    if (!$userId) {
        throw new Exception('User not logged in');
    }

    $assignedTo = 0;
    $status = 'Pending';

    // Insert application
    $stmt = $conn->prepare("INSERT INTO applications (user_id, name, email, phone, service_type, status, assigned_to, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

    if (!$stmt || !$stmt->bind_param("isssssi", $userId, $userName, $email, $phone, $serviceType, $status, $assignedTo) || !$stmt->execute()) {
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
            if (!$stmt || !$stmt->bind_param("sssssi", $originalName, $uniqueName, $targetPath, $fileSize, $modelType, $applicationId) || !$stmt->execute()) {
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
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ];
    header('Location: ' . $response['redirect']);
    exit();
}

echo json_encode($response);
?>
