<?php
session_start();
include("includes/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    mysqli_query($conn, "UPDATE users SET description='$description' WHERE id='$user_id'");
    header("Location: home.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">

<div class="container" style="max-width: 600px;">
    <div class="card p-4 shadow">
        <h4 class="mb-3">Edit Your Profile</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="description" class="form-label">About You</label>
                <textarea class="form-control" name="description" id="description" rows="4"><?php echo htmlspecialchars($user['description']); ?></textarea>
            </div>
            <button class="btn btn-primary w-100" type="submit">Save Changes</button>
            <a href="home.php" class="btn btn-secondary w-100 mt-2">Back</a>
        </form>
    </div>
</div>

</body>
</html>
