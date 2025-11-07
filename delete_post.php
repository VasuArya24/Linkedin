<?php
// delete_post.php
require_once 'includes/functions.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: home.php'); exit; }

// verify owner
$stmt = $mysqli->prepare("SELECT image, user_id FROM posts WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { header('Location: home.php'); exit; }
$post = $res->fetch_assoc();
if ($post['user_id'] != $_SESSION['user_id']) { header('Location: home.php'); exit; }

// delete image file
if ($post['image']) {
    $file = __DIR__ . '/uploads/' . $post['image'];
    if (file_exists($file)) unlink($file);
}

// delete db record
$stmt = $mysqli->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

header('Location: home.php'); exit;
