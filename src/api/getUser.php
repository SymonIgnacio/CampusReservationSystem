<?php
// Set headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Include session configuration
require_once 'session_config.php';

// Enable error logging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log for debugging
error_log("getUser.php called. Session data: " . json_encode($_SESSION));

// Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
    
    // Log connection status for debugging
    error_log("Database connection status: " . ($conn->connect_error ? "Failed: " . $conn->connect_error : "Success"));

    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        echo json_encode([
            "success" => false,
            "message" => "Connection failed"
        ]);
        exit;
    }

    // Get user data from database using the correct column name (user_id)
    $stmt = $conn->prepare("SELECT user_id, username, firstname, lastname, email, role, department FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Log query execution for debugging
    error_log("User query executed. User ID: " . $_SESSION['user_id'] . ", Result rows: " . $result->num_rows);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "user" => $user
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "No user logged in"
    ]);
}
?>