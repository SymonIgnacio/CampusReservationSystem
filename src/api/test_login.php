<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Prevent PHP from showing errors in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Get username and password from query parameters
$username = isset($_GET['username']) ? $_GET['username'] : '';
$password = isset($_GET['password']) ? $_GET['password'] : '';

if (empty($username) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Please provide both username and password parameters"
    ]);
    exit();
}

// Database connection
$host = "localhost";
$dbuser = "root";
$dbpass = "";
$database = "campus_db";

$conn = new mysqli($host, $dbuser, $dbpass, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Check password
    $passwordMatches = ($user['password'] === $password);
    $passwordMatchesIgnoreCase = (strtolower($user['password']) === strtolower($password));
    
    echo json_encode([
        "success" => true,
        "user_found" => true,
        "password_matches" => $passwordMatches,
        "password_matches_ignore_case" => $passwordMatchesIgnoreCase,
        "stored_password" => $user['password'],
        "provided_password" => $password,
        "user_data" => [
            "user_id" => $user['user_id'],
            "username" => $user['username'],
            "role" => $user['role']
        ]
    ]);
} else {
    echo json_encode([
        "success" => true,
        "user_found" => false,
        "message" => "No user found with username: " . $username
    ]);
}

$stmt->close();
$conn->close();
?>