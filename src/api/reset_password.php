<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['user_id'])) {
    echo json_encode(["success" => false, "message" => "Missing user ID"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "campus_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit();
}

// Generate new random password
$new_password = 'temp' . rand(1000, 9999);
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$stmt->bind_param("si", $hashed_password, $data['user_id']);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true, 
        "message" => "Password reset successfully",
        "new_password" => $new_password
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to reset password"]);
}

$conn->close();
?>