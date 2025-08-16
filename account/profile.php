<?php
ob_start();

include '../include/header.php';
require_once '../php/db.php';
require_once '../php/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = basename($_SERVER['PHP_SELF']);

$message = $_SESSION['message'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// ✅ Always fetch latest user data from DB
$userQuery = $conn->prepare("SELECT name, email, mobile, featured_image FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$currentUser = $userResult->fetch_assoc();
$userQuery->close();

// Update session with latest DB values (so form is never stale)
$_SESSION['name']  = $currentUser['name'];
$_SESSION['email'] = $currentUser['email'];
$_SESSION['mobile'] = $currentUser['mobile'];
$_SESSION['featured_image'] = $currentUser['featured_image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name   = $_POST['name'];
    $email  = $_POST['email'];
    $mobile = $_POST['mobile'] ?? '';

    $hasError = false;

    if (empty($name) || empty($email)) {
        $_SESSION['error'] = 'Name and email are required.';
        $hasError = true;
    }

    $file_uploaded = isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE;
    $new_image_path = null;

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

            // Handle file upload
            if ($file_uploaded) {
                $upload_dir = '../uploads/profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_name = uniqid() . '.' . $allowed_types[$file_type];
                $file_path_for_db = 'uploads/profiles/' . $file_name;
                $full_file_path = $upload_dir . $file_name;

                if (move_uploaded_file($file['tmp_name'], $full_file_path)) {
                    // Delete old file record + insert new
                    $deleteStmt = $conn->prepare("SELECT file_path FROM files WHERE model_type = 'user' AND model_id = ?");
                    $deleteStmt->bind_param("i", $user_id);
                    $deleteStmt->execute();
                    $oldFile = $deleteStmt->get_result()->fetch_assoc();
                    $deleteStmt->close();

                    $deleteFileStmt = $conn->prepare("DELETE FROM files WHERE model_type = 'user' AND model_id = ?");
                    $deleteFileStmt->bind_param("i", $user_id);
                    $deleteFileStmt->execute();
                    $deleteFileStmt->close();

                    $original_name = $file['name'];
                    $file_size = $file['size'];
                    $model_type = 'user';

                    $insertFileStmt = $conn->prepare("INSERT INTO files (original_name, file_name, file_path, file_size, model_type, model_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $insertFileStmt->bind_param("sssssi", $original_name, $file_name, $file_path_for_db, $file_size, $model_type, $user_id);
                    $insertFileStmt->execute();
                    $insertFileStmt->close();

                    if ($oldFile && file_exists('../' . $oldFile['file_path'])) {
                        unlink('../' . $oldFile['file_path']);
                    }

                    $new_image_path = $file_path_for_db;
                } else {
                    throw new Exception('File upload failed.');
                }
            }

            // Update user data
            if ($new_image_path) {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, featured_image = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $name, $email, $mobile, $new_image_path, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, mobile = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $mobile, $user_id);
            }

            $stmt->execute();
            $stmt->close();

            $conn->commit();

            // ✅ Update session values
            $_SESSION['name']  = $name;
            $_SESSION['email'] = $email;
            $_SESSION['mobile'] = $mobile; // important fix
            if ($new_image_path) {
                $_SESSION['featured_image'] = $new_image_path;
            }

            $_SESSION['message'] = 'Profile updated successfully.';

        } catch (Exception $e) {
            if ($conn->in_transaction) {
                $conn->rollback();
            }
            $_SESSION['error'] = $e->getMessage();
        }

        ob_end_clean();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

$imageSrc = $_SESSION['featured_image'] ? $baseURL . $_SESSION['featured_image'] : $baseURL . 'uploads/profiles/user-avatar.png';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <?php include 'sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Profile</h1>
            </div>
            
            <div class="container mt-4">
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
                                        <div>
                                            <label for="profile_picture" class="btn custom-btn btn-sm">Change Photo</label>
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
                                                    value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Email Address</label>
                                                <input type="email" name="email" class="form-control"
                                                    value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="text" class="form-control" name="mobile"
                                                    value="<?php echo htmlspecialchars($_SESSION['mobile']); ?>">
                                            </div>

                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                                <button type="submit" name="update_profile" class="btn custom-btn form-btn">
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
        </main>
    </div>
</div>

<?php 
include '../include/footer.php';
ob_end_flush();
?>
