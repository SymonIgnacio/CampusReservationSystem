<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['user_id']) || !isset($data['new_password'])) {
    echo json_encode(["success" => false, "message" => "Missing required data"]);
    exit();
}

try {
    $conn = new mysqli("localhost", "root", "", "campus_db");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get user info
    $sql = "SELECT firebase_uid, password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("User not found");
    }
    
    // If user has firebase_uid, they use Firebase auth (no password check needed)
    if ($user['firebase_uid']) {
        // For Firebase users, we don't store passwords in database
        // The password change should be handled on the frontend with Firebase
        echo json_encode([
            "success" => true,
            "message" => "Password updated successfully",
            "firebase_user" => true
        ]);
    } else {
        // For local users, verify current password if provided
        if (isset($data['current_password'])) {
            if (!password_verify($data['current_password'], $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
        }
        
        // Update password in database
        $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $data['user_id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Password updated successfully",
                "firebase_user" => false
            ]);
        } else {
            throw new Exception("Failed to update password: " . $stmt->error);
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>