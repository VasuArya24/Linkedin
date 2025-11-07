<?php
session_start();
include("includes/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Default description for new users
    $description = "Aspiring professional | Tech Enthusiast";

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Email already registered! Please log in.'); window.location='index.php';</script>";
        exit;
    }

    // Insert new user
    $query = "INSERT INTO users (name, email, password, description) VALUES ('$name', '$email', '$password', '$description')";
    if (mysqli_query($conn, $query)) {
        // Get user ID and start session
        $user_id = mysqli_insert_id($conn);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;

        header("Location: home.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
