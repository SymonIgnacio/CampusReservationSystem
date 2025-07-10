<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['name']) || !isset($data['stock'])) {
    echo json_encode(["success" => false, "message" => "Missing required data"]);
    exit();
}

try {
    $conn = new mysqli("localhost", "root", "", "campus_db");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $sql = "UPDATE equipment SET name = ?, quantity_available = ? WHERE equipment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $data['name'], $data['stock'], $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Equipment updated successfully"
        ]);
    } else {
        throw new Exception("Failed to update equipment: " . $stmt->error);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>