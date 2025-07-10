<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Debug logging
error_log("Received data: " . json_encode($data));

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit();
}

if (!isset($data['venue']) || empty($data['venue'])) {
    echo json_encode(["success" => false, "message" => "Venue name is required"]);
    exit();
}

if (!isset($data['campus']) || empty($data['campus'])) {
    echo json_encode(["success" => false, "message" => "Campus is required"]);
    exit();
}

try {
    $conn = new mysqli("localhost", "root", "", "campus_db");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $sql = "INSERT INTO facilities (venue, campus, capacity, description) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $capacity = isset($data['capacity']) && $data['capacity'] !== '' ? (int)$data['capacity'] : null;
    $description = isset($data['description']) && $data['description'] !== '' ? $data['description'] : null;
    
    $stmt->bind_param("ssis", 
        $data['venue'], 
        $data['campus'], 
        $capacity, 
        $description
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Facility added successfully",
            "facility_id" => $conn->insert_id
        ]);
    } else {
        throw new Exception("Failed to add facility: " . $stmt->error);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>