<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['venue']) || !isset($data['date_from']) || !isset($data['date_to'])) {
    echo json_encode(["success" => false, "message" => "Missing required data"]);
    exit();
}

try {
    $conn = new mysqli("localhost", "root", "", "campus_db");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check for conflicts in approved_request table
    $sql = "SELECT * FROM approved_request WHERE venue = ? AND (
        (date_need_from <= ? AND date_need_until >= ?) OR
        (date_need_from <= ? AND date_need_until >= ?) OR
        (date_need_from >= ? AND date_need_until <= ?)
    )";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", 
        $data['venue'],
        $data['date_from'], $data['date_from'],
        $data['date_to'], $data['date_to'],
        $data['date_from'], $data['date_to']
    );
    
    $stmt->execute();
    $result = $stmt->get_result();
    $conflicts = $result->fetch_all(MYSQLI_ASSOC);
    
    if (count($conflicts) > 0) {
        echo json_encode([
            "success" => false,
            "has_conflict" => true,
            "message" => "Venue is already booked for the selected dates",
            "conflicts" => $conflicts
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "has_conflict" => false,
            "message" => "Venue is available"
        ]);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>