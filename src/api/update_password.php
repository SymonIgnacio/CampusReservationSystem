<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection details
$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception("Invalid JSON data");
    }
    
    // Validate required fields
    if (!isset($data['user_id']) || !isset($data['current_password']) || !isset($data['new_password'])) {
        throw new Exception("Missing required fields");
    }

    // Connect to DB
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get current password hash
    $get_user = "SELECT password FROM users WHERE user_id = ?";
    $user_stmt = $conn->prepare($get_user);
    $user_stmt->bind_param("i", $data['user_id']);
    $user_stmt->execute();
    $result = $user_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }
    
    $user = $result->fetch_assoc();
    $user_stmt->close();
    
    // Verify current password
    if (!password_verify($data['current_password'], $user['password'])) {
        throw new Exception("Current password is incorrect");
    }
    
    // Hash new password
    $new_password_hash = password_hash($data['new_password'], PASSWORD_DEFAULT);
    
    // Update password
    $sql = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("si", $new_password_hash, $data['user_id']);
    
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();

    // Return success
    echo json_encode([
        "status" => "success",
        "message" => "Password updated successfully"
    ]);

    $conn->close();
} catch (Exception $e) {
    // Return error as JSON
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>