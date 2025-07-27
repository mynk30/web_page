<?php
// Start output buffering to handle redirects
ob_start();

// Include header first (this starts the session)
include '../include/header.php';
require_once '../php/db.php';
require_once '../php/config.php';
global $logger;

$user_id = $_SESSION['user_id'];

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? '';

    $hasError = false;

    if (empty($name) || empty($email)) {
        $_SESSION['error'] = 'Name and email are required.';
        $hasError = true;
    }

    // log all the variables from the logger
    $logger->info("Name: " . $name);
    $logger->info("Email: " . $email);
    $logger->info("Phone: " . $phone);
    $logger->info("Has Error: " . ($hasError ? 'Yes' : 'No'));

    $file_uploaded = isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE;
    $logger->info("File uploaded: " . ($file_uploaded ? 'Yes' : 'No'));

    $new_image_path = null; // Track new image path

    if ($file_uploaded && !$hasError) {
        $file = $_FILES['profile_picture'];
        $allowed_types = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $file_type = $file['type'];
        $max_file_size = 5 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'File upload error: ' . $file['error'];
            $hasError = true;
        } elseif (!in_array($file_type, array_keys($allowed_types))) {
            $_SESSION['error'] = 'Invalid file type.';
            $hasError = true;
        } elseif ($file['size'] > $max_file_size) {
            $_SESSION['error'] = 'File too large.';
            $hasError = true;
        }
    }

    if (!$hasError) {
        try {
            $conn->begin_transaction();

            // Handle file upload first if needed
            if ($file_uploaded) {
                $upload_dir = '../uploads/profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_name = uniqid() . '.' . $allowed_types[$file_type];
                $file_path_for_db = 'uploads/profiles/' . $file_name;
                $full_file_path = $upload_dir . $file_name;

                if (move_uploaded_file($file['tmp_name'], $full_file_path)) {
                    // Get old file info first
                    $deleteStmt = $conn->prepare("SELECT file_path FROM files WHERE model_type = 'user' AND model_id = ?");
                    if ($deleteStmt === false) {
                        throw new Exception('Prepare failed for file select: ' . $conn->error);
                    }
                    $deleteStmt->bind_param("i", $user_id);
                    if (!$deleteStmt->execute()) {
                        throw new Exception('Execute failed for file select: ' . $deleteStmt->error);
                    }
                    $oldFile = $deleteStmt->get_result()->fetch_assoc();
                    $deleteStmt->close();

                    // Delete old file record
                    $deleteFileStmt = $conn->prepare("DELETE FROM files WHERE model_type = 'user' AND model_id = ?");
                    if ($deleteFileStmt === false) {
                        throw new Exception('Prepare failed for file delete: ' . $conn->error);
                    }
                    $deleteFileStmt->bind_param("i", $user_id);
                    if (!$deleteFileStmt->execute()) {
                        throw new Exception('Execute failed for file delete: ' . $deleteFileStmt->error);
                    }
                    $deleteFileStmt->close();

                    // Insert new file record
                    $original_name = $file['name'];
                    $file_size = $file['size'];
                    $model_type = 'user';

                    $insertFileStmt = $conn->prepare("INSERT INTO files (original_name, file_name, file_path, file_size, model_type, model_id) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($insertFileStmt === false) {
                        throw new Exception('Prepare failed for file insert: ' . $conn->error);
                    }
                    $insertFileStmt->bind_param("sssssi", $original_name, $file_name, $file_path_for_db, $file_size, $model_type, $user_id);
                    if (!$insertFileStmt->execute()) {
                        throw new Exception('Execute failed for file insert: ' . $insertFileStmt->error);
                    }
                    $insertFileStmt->close();
                    $logger->info("File inserted successfully.");

                    // Delete old physical file
                    if ($oldFile && file_exists('../' . $oldFile['file_path'])) {
                        unlink('../' . $oldFile['file_path']);
                    }

                    $new_image_path = $file_path_for_db;
                } else {
                    throw new Exception('File upload failed.');
                }
            }

            // Update user name and featured_image (always update, even if no new file)
            if ($new_image_path) {
                // Update with new image path
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, featured_image = ? WHERE id = ?");    
                if ($stmt === false) {
                    throw new Exception('Prepare failed for user update: ' . $conn->error);
                }
                $stmt->bind_param("sssi", $name, $email, $new_image_path, $user_id);
            } else {
                // Update only name
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");                                                        
                if ($stmt === false) {
                    throw new Exception('Prepare failed for user update: ' . $conn->error);
                }
                $stmt->bind_param("ssi", $name, $email, $user_id);
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed for user update: ' . $stmt->error);
            }
            $stmt->close();

            // Update application data with upsert (ALWAYS update, even if empty)
            $appStmt = $conn->prepare("
                INSERT INTO applications (user_id, phone) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE 
                phone = VALUES(phone)   
            ");
            if ($appStmt === false) {
                throw new Exception('Prepare failed for application update: ' . $conn->error);
            }
            $appStmt->bind_param("is", $user_id, $phone);
            if (!$appStmt->execute()) {
                throw new Exception('Execute failed for application update: ' . $appStmt->error);
            }
            $appStmt->close();

            $conn->commit();
            
            // Update session variables AFTER successful commit
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            if ($new_image_path) {
                $_SESSION['featured_image'] = $new_image_path;
            }
            
            $logger->info("Session image path updated successfully.");
            $logger->info("Complete session information after update profile: " . json_encode($_SESSION));
            
            $_SESSION['message'] = 'Profile updated successfully.';

        } catch (Exception $e) {
            if ($conn->in_transaction) {
                $conn->rollback();
            }
            $_SESSION['error'] = $e->getMessage();
            $logger->error("Profile update error: " . $e->getMessage());
        }

        // Clear output buffer and redirect
        ob_end_clean();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
if ($stmt === false) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get profile picture
$stmt = $conn->prepare("SELECT * FROM files WHERE model_type = 'user' AND model_id = ? ORDER BY uploaded_at DESC LIMIT 1");
if ($stmt === false) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile_picture = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get application data
$appStmt = $conn->prepare("SELECT phone FROM applications WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
if ($appStmt === false) {
    die('Prepare failed: ' . $conn->error);
}
$appStmt->bind_param("i", $user_id);
$appStmt->execute();
$appData = $appStmt->get_result()->fetch_assoc();
$appStmt->close();

$phone = $appData['phone'] ?? '';

$baseUrl = 'http://localhost/web_page/';
$imagePath = $profile_picture ? $profile_picture['file_path'] : 'uploads/profiles/default.png';
$imageSrc = $baseUrl . $imagePath;
?>

<div class="container mt-4">
    <h2>User Profile</h2>

    <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="mb-3">
                            <img id="preview" src="<?php echo $imageSrc; ?>"
                            class="img-fluid rounded-circle mb-3"
                            style="width: 200px; height: 200px; object-fit: cover;">
                            <!-- /upload/prole/prile_34567 -->
                            <div>
                                <label for="profile_picture" class="btn btn-outline-primary btn-sm">Change Photo</label>
                                <input type="file" class="d-none" id="profile_picture" name="profile_picture"
                                       accept=".jpg,.jpeg,.png,.webp" onchange="previewImage(event)">
                            </div>
                            <div class="small text-muted mt-2">Allowed JPG, JPEG, PNG, WEBP. Max 5MB</div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Profile Information</h4>

                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="name"
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone"
                                        value="<?php echo htmlspecialchars($phone); ?>">
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</div>

<?php 
// End output buffering
include '../include/footer.php';
ob_end_flush();
?>