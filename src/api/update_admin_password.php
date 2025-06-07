<?php
// This script updates an existing admin account's password to use proper hashing
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Admin account details
$adminUsername = "symon"; // Your admin username
$newPassword = "admin123"; // Your desired admin password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the admin account password
$sql = "UPDATE users SET password = ? WHERE username = ? AND role = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashedPassword, $adminUsername);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    echo json_encode([
      "success" => true,
      "message" => "Admin password updated successfully. You can now log in with username: $adminUsername and password: $newPassword"
    ]);
  } else {
    echo json_encode([
      "success" => false,
      "message" => "No admin account found with username: $adminUsername"
    ]);
  }
} else {
  echo json_encode([
    "success" => false,
    "message" => "Error updating password: " . $stmt->error
  ]);
}

$stmt->close();
$conn->close();
?>