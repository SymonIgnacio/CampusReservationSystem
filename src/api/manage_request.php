php
<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id']) || !isset($data['status'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing ID or status in request body"
    ]);
    exit();
}

$id = $data['id'];
$status = $data['status'];

// Connect to DB
$host = "localhost";
$dbname = "campus_db";
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Check which table exists
$reservationsTableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;
$eventsTableExists = $conn->query("SHOW TABLES LIKE 'events'")->num_rows > 0;

$tableName = null;
$idColumn = null;

if ($reservationsTableExists) {
    $tableName = 'reservations';
    $idColumn = 'id'; // Assuming 'id' is the primary key in reservations table
} elseif ($eventsTableExists) {
    $tableName = 'events';
    $idColumn = 'id'; // Assuming 'id' is the primary key in events table
}

if (!$tableName) {
    echo json_encode([
        "success" => false,
        "message" => "Neither 'events' nor 'reservations' table found"
    ]);
    $conn->close();
    exit();
}

// Update the status in the determined table
$stmt = $conn->prepare("UPDATE $tableName SET status = ? WHERE $idColumn = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Status updated successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No record found with the given ID in $tableName"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error updating status: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

?>