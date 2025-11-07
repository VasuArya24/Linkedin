<?php
// create_post.php
require_once 'includes/functions.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$content = trim($_POST['content'] ?? '');
if (!$content) {
    $_SESSION['flash']['error'] = "Post can't be empty.";
    header('Location: home.php'); exit;
}

$image_name = null;
if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $allowed = ['jpg','jpeg','png','gif'];
    $file = $_FILES['image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash']['error'] = "Image upload error.";
        header('Location: home.php'); exit;
    }
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB
        $_SESSION['flash']['error'] = "Image too large (max 2MB).";
        header('Location: home.php'); exit;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        $_SESSION['flash']['error'] = "Invalid image format.";
        header('Location: home.php'); exit;
    }
    $image_name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = __DIR__ . '/uploads/' . $image_name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $_SESSION['flash']['error'] = "Failed to move uploaded file.";
        header('Location: home.php'); exit;
    }
}

$stmt = $mysqli->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $_SESSION['user_id'], $content, $image_name);
$stmt->execute();

header('Location: home.php'); exit;


