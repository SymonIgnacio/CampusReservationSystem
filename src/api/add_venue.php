<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['name'])) {
        throw new Exception("Venue name is required");
    }

    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO facilities (venue, capacity) VALUES (?, ?)");
    $capacity = isset($data['capacity']) ? (int)$data['capacity'] : 0;
    $stmt->bind_param("si", $data['name'], $capacity);
    
    if (!$stmt->execute()) {
        throw new Exception("Error adding venue: " . $stmt->error);
    }

    echo json_encode([
        "success" => true,
        "message" => "Venue added successfully"
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}