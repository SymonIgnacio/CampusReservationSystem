<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['name']) || !isset($data['location']) || !isset($data['capacity'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "campus_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO facilities (venue, campus, capacity, description) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssis", $data['name'], $data['location'], $data['capacity'], $data['description']);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Facility added successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add facility"]);
}

$conn->close();
?>