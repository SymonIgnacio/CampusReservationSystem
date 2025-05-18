<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first - must be before any output
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed");
    }

    // Get JSON data from request
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['userId'])) {
        throw new Exception("Missing user ID");
    }

    $userId = $data['userId'];

    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->num_rows == 0) {
        throw new Exception("Users table does not exist");
    }

    // Check if user exists
    $checkStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }

    // Check if user is an admin
    $user = $result->fetch_assoc();
    if ($user['role'] === 'admin') {
        throw new Exception("Cannot delete admin user");
    }
    $checkStmt->close();

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        throw new Exception("Error deleting user: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "User deleted successfully"
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in delete_user.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>