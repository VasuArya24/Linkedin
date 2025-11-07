<?php
session_start();
include("includes/config.php");

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

// Handle Login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: home.php");
        exit();
    } else {
        echo "<script>alert('Invalid email or password!');</script>";
    }
}

// Handle Signup
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Email already registered. Please log in.');</script>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");
        if ($insert) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['user_name'] = $name;
            header("Location: home.php");
            exit();
        } else {
            echo "<script>alert('Error during signup. Try again!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LinkedIn Clone | Login or Signup</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/174/174857.png">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ===== Animated Background ===== -->
<div class="bg-animation">
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>
  <div class="circle circle3"></div>
  <div class="circle circle4"></div>
</div>

<!-- ===== Centered Glass Box ===== -->
<div class="container-fluid d-flex justify-content-center align-items-center min-vh-100">
  <div class="login-card shadow-lg p-4 rounded-4 text-center">

    <h1 class="fw-bold mb-3 text-primary"><i class="bi bi-linkedin"></i> LinkedIn Clone</h1>
    <p class="text-muted mb-4">Connect. Share. Grow.</p>

    <div class="d-flex justify-content-center mb-4">
      <button class="btn-switch me-2 active" id="loginTab" onclick="showLogin()">Login</button>
      <button class="btn-switch" id="registerTab" onclick="showRegister()">Signup</button>
    </div>

    <!-- LOGIN FORM -->
    <form id="loginForm" method="POST">
      <input type="hidden" name="login" value="1">
      <div class="mb-3 text-start">
        <label class="form-label text-white">Email Address</label>
        <input type="email" name="email" class="form-control form-glass" placeholder="Enter email" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label text-white">Password</label>
        <input type="password" name="password" class="form-control form-glass" placeholder="Enter password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100 rounded-pill mt-2">Login</button>
    </form>

    <!-- SIGNUP FORM -->
    <form id="registerForm" class="d-none" method="POST">
      <input type="hidden" name="signup" value="1">
      <div class="mb-3 text-start">
        <label class="form-label text-white">Full Name</label>
        <input type="text" name="name" class="form-control form-glass" placeholder="Your full name" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label text-white">Email Address</label>
        <input type="email" name="email" class="form-control form-glass" placeholder="Enter email" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label text-white">Password</label>
        <input type="password" name="password" class="form-control form-glass" placeholder="Choose password" required>
      </div>
      <button type="submit" class="btn btn-success w-100 rounded-pill mt-2">Sign Up</button>
    </form>

    <footer class="text-white-50 mt-4 small">© 2025 LinkedIn Clone Project by Vasu Arya</footer>
  </div>
</div>

<!-- Bootstrap + JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>

</body>
</html>
