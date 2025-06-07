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
    if (!isset($data['user_id']) || !isset($data['firstname']) || !isset($data['lastname']) || !isset($data['email'])) {
        throw new Exception("Missing required fields");
    }

    // Connect to DB
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if email already exists for another user
    $check_email = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_email);
    $check_stmt->bind_param("si", $data['email'], $data['user_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception("Email already in use by another account");
    }
    $check_stmt->close();
    
    // Update user information
    $sql = "UPDATE users SET 
            firstname = ?, 
            lastname = ?, 
            email = ?, 
            phone = ?, 
            department = ? 
            WHERE user_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sssssi", 
        $data['firstname'],
        $data['lastname'],
        $data['email'],
        $data['phone'],
        $data['department'],
        $data['user_id']
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();

    // Return success
    echo json_encode([
        "status" => "success",
        "message" => "User information updated successfully"
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