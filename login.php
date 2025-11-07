<?php
session_start();
include 'includes/config.php';

if (isset($_POST['login'])) {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
  if (mysqli_num_rows($res) > 0) {
    $user = mysqli_fetch_assoc($res);
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['name'];
      header("Location: home.php");
      exit;
    } else {
      $error = "Incorrect password!";
    }
  } else {
    $error = "No account found with this email!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LinkedIn Clone - Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="auth-container">
    <h1>LinkedIn Clone</h1>

    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <div class="card">
      <h2>Login</h2>
      <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
      </form>
      <p>Don't have an account? <a href="register.php">Sign Up</a></p>
    </div>
  </div>
  <script src="assets/js/script.js"></script>
</body>
</html>
