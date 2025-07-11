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

if (!$data || !isset($data['user_id'])) {
    echo json_encode(["success" => false, "message" => "Missing user ID"]);
    exit();
}

try {
    $conn = new mysqli("localhost", "root", "", "campus_db");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Update firstname and lastname if provided, otherwise keep existing values
    if (isset($data['firstname']) && isset($data['lastname'])) {
        $sql = "UPDATE users SET firstname = ?, lastname = ?, username = ?, email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", 
            $data['firstname'],
            $data['lastname'], 
            $data['username'], 
            $data['email'], 
            $data['user_id']
        );
    } else {
        $sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", 
            $data['username'], 
            $data['email'], 
            $data['user_id']
        );
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "User updated successfully"
        ]);
    } else {
        throw new Exception("Failed to update user: " . $stmt->error);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>