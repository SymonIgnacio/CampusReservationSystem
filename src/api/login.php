<?php
// Include CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

require_once 'cors_fix.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log login attempt
error_log("Login attempt: " . json_encode($data));

// Check if we have username and password
if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Username and password are required"
    ]);
    exit;
}

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Get user from database
$stmt = $conn->prepare("SELECT user_id, username, firstname, lastname, email, role, password FROM users WHERE username = ?");
$stmt->bind_param("s", $data['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // For testing purposes, accept any password for admin user
    if ($user['username'] === 'admin' || password_verify($data['password'], $user['password'])) {
        // Remove password from user data before sending to client
        unset($user['password']);
        
        // Set session data
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Log successful login
        error_log("Login successful for user: " . $user['username']);
        
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $user
        ]);
    } else {
        error_log("Invalid password for user: " . $data['username']);
        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password"
        ]);
    }
} else {
    error_log("User not found: " . $data['username']);
    echo json_encode([
        "success" => false,
        "message" => "Invalid username or password"
    ]);
}

$stmt->close();
$conn->close();
?>