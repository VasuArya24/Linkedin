<?php
$host = "localhost";
$user = "root"; // change if needed
$pass = "";
$dbname = "linkedin_clone";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}
?>
