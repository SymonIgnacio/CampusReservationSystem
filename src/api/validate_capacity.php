<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['venue']) || !isset($data['total_attendees'])) {
    echo json_encode(["valid" => false, "message" => "Missing required fields"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "campus_db");

if ($conn->connect_error) {
    echo json_encode(["valid" => false, "message" => "Connection failed"]);
    exit();
}

$stmt = $conn->prepare("SELECT capacity FROM facilities WHERE venue = ?");
$stmt->bind_param("s", $data['venue']);
$stmt->execute();
$result = $stmt->get_result();
$facility = $result->fetch_assoc();

if (!$facility) {
    echo json_encode(["valid" => false, "message" => "Venue not found"]);
    exit();
}

$capacity = $facility['capacity'];
$attendees = $data['total_attendees'];

if ($attendees > $capacity) {
    echo json_encode([
        "valid" => false, 
        "message" => "Total attendees ($attendees) exceeds venue capacity ($capacity)",
        "capacity" => $capacity,
        "attendees" => $attendees
    ]);
} else {
    echo json_encode([
        "valid" => true, 
        "message" => "Capacity validation passed",
        "capacity" => $capacity,
        "attendees" => $attendees
    ]);
}

$conn->close();
?>