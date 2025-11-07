<?php
// edit_post.php
require_once 'includes/functions.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$id) { header('Location: home.php'); exit; }
    $stmt = $mysqli->prepare("SELECT id, user_id, content, image FROM posts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) { header('Location: home.php'); exit; }
    $post = $res->fetch_assoc();
    if ($post['user_id'] != $_SESSION['user_id']) { header('Location: home.php'); exit; }
    // show edit form below
} else {
    // POST - update
    $id = intval($_POST['id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    if (!$content) { $_SESSION['flash']['error'] = "Content can't be empty."; header("Location: edit_post.php?id=$id"); exit; }

    $stmt = $mysqli->prepare("SELECT image, user_id FROM posts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) { header('Location: home.php'); exit; }
    $post = $res->fetch_assoc();
    if ($post['user_id'] != $_SESSION['user_id']) { header('Location: home.php'); exit; }

    $image_name = $post['image'];

    if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // same checks as create_post
        $allowed = ['jpg','jpeg','png','gif'];
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash']['error'] = "Image upload error.";
            header("Location: edit_post.php?id=$id"); exit;
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            $_SESSION['flash']['error'] = "Image too large (max 2MB).";
            header("Location: edit_post.php?id=$id"); exit;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $_SESSION['flash']['error'] = "Invalid image format.";
            header("Location: edit_post.php?id=$id"); exit;
        }

        // delete old image if exists
        if ($image_name) {
            $old = __DIR__ . '/uploads/' . $image_name;
            if (file_exists($old)) unlink($old);
        }

        $image_name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = __DIR__ . '/uploads/' . $image_name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $_SESSION['flash']['error'] = "Failed to move uploaded file.";
            header("Location: edit_post.php?id=$id"); exit;
        }
    }

    $stmt = $mysqli->prepare("UPDATE posts SET content = ?, image = ? WHERE id = ?");
    $stmt->bind_param('ssi', $content, $image_name, $id);
    $stmt->execute();

    header('Location: home.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Post</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h2>Edit Post</h2>
    <form method="post" action="edit_post.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= esc($post['id']) ?>">
        <textarea name="content" required><?= esc($post['content']) ?></textarea>
        <?php if ($post['image']): ?>
            <div>Current image:<br><img src="uploads/<?= esc($post['image']) ?>" style="max-width:200px"></div>
        <?php endif; ?>
        <label>Change image (optional)</label>
        <input type="file" name="image" accept="image/*">
        <button type="submit">Save</button>
        <a href="home.php">Cancel</a>
    </form>
</div>
</body>
</html>
