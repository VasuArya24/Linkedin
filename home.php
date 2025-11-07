<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current user
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// ---------- Profile Update ----------
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $photo = $user['photo'];

    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $file_name;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
        $photo = $target_file;
    }

    $update = $conn->prepare("UPDATE users SET name=?, description=?, photo=? WHERE id=?");
    $update->bind_param("sssi", $name, $desc, $photo, $user_id);
    $update->execute();
    header("Location: home.php");
    exit();
}

// ---------- Create New Post ----------
if (isset($_POST['post_content'])) {
    $content = trim($_POST['post_content']);
    $image_path = null;

    if (!empty($_FILES['post_image']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $img_name = time() . "_" . basename($_FILES["post_image"]["name"]);
        $target_file = $target_dir . $img_name;
        move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file);
        $image_path = $target_file;
    }

    if (!empty($content) || !empty($image_path)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $content, $image_path);
        $stmt->execute();
    }

    header("Location: home.php");
    exit();
}

// ---------- Edit Post ----------
if (isset($_POST['edit_post_id'])) {
    $post_id = intval($_POST['edit_post_id']);
    $new_content = trim($_POST['edit_post_content']);
    $post_img = $_POST['old_image'];

    if (!empty($_FILES['edit_post_image']['name'])) {
        $target_dir = "uploads/";
        $img_name = time() . "_" . basename($_FILES["edit_post_image"]["name"]);
        $target_file = $target_dir . $img_name;
        move_uploaded_file($_FILES["edit_post_image"]["tmp_name"], $target_file);
        $post_img = $target_file;
    }

    $stmt = $conn->prepare("UPDATE posts SET content=?, image=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssii", $new_content, $post_img, $post_id, $user_id);
    $stmt->execute();
    header("Location: home.php");
    exit();
}

// ---------- Handle Like ----------
if (isset($_GET['like_post'])) {
    $post_id = intval($_GET['like_post']);
    $check = $conn->prepare("SELECT * FROM likes WHERE post_id=? AND user_id=?");
    $check->bind_param("ii", $post_id, $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $conn->query("DELETE FROM likes WHERE post_id=$post_id AND user_id=$user_id");
    } else {
        $conn->query("INSERT INTO likes (post_id, user_id) VALUES ($post_id, $user_id)");
    }

    header("Location: home.php");
    exit();
}

// ---------- Handle Comments ----------
if (isset($_POST['comment_text']) && isset($_POST['comment_post_id'])) {
    $comment_text = trim($_POST['comment_text']);
    $post_id = intval($_POST['comment_post_id']);

    if (!empty($comment_text)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user_id, $comment_text);
        $stmt->execute();
    }

    header("Location: home.php");
    exit();
}

// ---------- Fetch Posts ----------
$posts = $conn->query("
    SELECT posts.*, users.name, users.photo,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id=posts.id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id=posts.id AND likes.user_id=$user_id) AS user_liked
    FROM posts
    JOIN users ON posts.user_id=users.id
    ORDER BY posts.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LinkedIn Clone | Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f3f2ef; font-family: 'Poppins', sans-serif; }
    .navbar { background: #0077b5; color: white; }
    .navbar-brand { color: white; font-weight: 700; }
    .profile-card {
      background: white; border-radius: 12px; padding: 20px; text-align: center;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    .profile-card img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; }
    .post-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 3px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .like-btn { color: #0077b5; text-decoration: none; font-weight: 600; }
    .comment-box textarea { resize: none; }
    .post-img { width: 100%; border-radius: 10px; margin-top: 10px; max-height: 400px; object-fit: cover; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg p-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">LinkedIn Clone</a>
    <div class="ms-auto">
      <span class="me-3">👋 Hi, <?php echo htmlspecialchars($user['name']); ?></span>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="row">
    <!-- Profile -->
    <div class="col-md-3">
      <div class="profile-card">
        <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="">
        <h5 class="mt-2"><?php echo htmlspecialchars($user['name']); ?></h5>
        <p class="text-muted small"><?php echo htmlspecialchars($user['description']); ?></p>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
      </div>
    </div>

    <!-- Feed -->
    <div class="col-md-6">
      <div class="post-card mb-3">
        <form method="POST" enctype="multipart/form-data">
          <textarea name="post_content" class="form-control mb-2" rows="3" placeholder="Share your thoughts..."></textarea>
          <input type="file" name="post_image" class="form-control mb-2">
          <button type="submit" class="btn btn-primary w-100">Post</button>
        </form>
      </div>

      <?php while ($post = $posts->fetch_assoc()): ?>
      <div class="post-card">
        <div class="d-flex align-items-center mb-2">
          <img src="<?php echo htmlspecialchars($post['photo']); ?>" width="45" height="45" class="rounded-circle me-2">
          <div>
            <strong><?php echo htmlspecialchars($post['name']); ?></strong><br>
            <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($post['created_at'])); ?></small>
          </div>
        </div>

        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
        <?php if ($post['image']): ?>
          <img src="<?php echo htmlspecialchars($post['image']); ?>" class="post-img">
        <?php endif; ?>

        <div class="mt-2">
          <a href="?like_post=<?php echo $post['id']; ?>" class="like-btn">
            ❤️ <?php echo ($post['user_liked'] > 0) ? 'Unlike' : 'Like'; ?>
          </a>
          <span class="ms-2 badge bg-primary"><?php echo $post['like_count']; ?> Likes</span>

          <?php if ($post['user_id'] == $user_id): ?>
            <button class="btn btn-sm btn-outline-secondary float-end" data-bs-toggle="modal" data-bs-target="#editPostModal<?php echo $post['id']; ?>">Edit</button>
          <?php endif; ?>
        </div>

        <!-- Comments -->
        <hr>
        <?php
        $post_id = $post['id'];
        $comments = $conn->query("
          SELECT comments.*, users.name FROM comments
          JOIN users ON comments.user_id=users.id
          WHERE comments.post_id=$post_id
          ORDER BY comments.created_at ASC
        ");
        ?>
        <div class="comments-section">
          <?php while ($comment = $comments->fetch_assoc()): ?>
            <div class="small mb-1">
              <strong><?php echo htmlspecialchars($comment['name']); ?>:</strong>
              <?php echo htmlspecialchars($comment['comment_text']); ?>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- Add Comment -->
        <form method="POST" class="comment-box mt-2">
          <input type="hidden" name="comment_post_id" value="<?php echo $post['id']; ?>">
          <textarea name="comment_text" class="form-control mb-1" rows="1" placeholder="Add a comment..."></textarea>
          <button type="submit" class="btn btn-sm btn-outline-primary">Comment</button>
        </form>
      </div>

      <!-- Edit Post Modal -->
      <div class="modal fade" id="editPostModal<?php echo $post['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="edit_post_id" value="<?php echo $post['id']; ?>">
                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($post['image']); ?>">
                <textarea name="edit_post_content" class="form-control mb-2" rows="3"><?php echo htmlspecialchars($post['content']); ?></textarea>
                <input type="file" name="edit_post_image" class="form-control mb-2">
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php endwhile; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-md-3">
      <div class="profile-card">
        <h6 class="text-primary mb-2">Trending Topics</h6>
        <ul class="list-unstyled text-start small">
          <li>💡 Artificial Intelligence</li>
          <li>💼 Career Tips</li>
          <li>🌐 Web Development</li>
          <li>📊 Data Science</li>
          <li>🚀 Entrepreneurship</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="update_profile" value="1">
          <label>Name</label>
          <input type="text" name="name" class="form-control mb-2" value="<?php echo htmlspecialchars($user['name']); ?>">
          <label>About You</label>
          <textarea name="description" class="form-control mb-2"><?php echo htmlspecialchars($user['description']); ?></textarea>
          <label>Profile Photo</label>
          <input type="file" name="photo" class="form-control">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
