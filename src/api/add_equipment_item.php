<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first - must be before any output
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed");
    }

    // Get JSON data from request
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    if (!isset($data['name']) || empty($data['name'])) {
        throw new Exception("Equipment name is required");
    }

    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set default values for optional fields
    $stock = isset($data['stock']) && !empty($data['stock']) ? (int)$data['stock'] : 0;

    // Insert new equipment
    $stmt = $conn->prepare("INSERT INTO equipments (name, stock) VALUES (?, ?)");
    $stmt->bind_param("si", $data['name'], $stock);

    if (!$stmt->execute()) {
        throw new Exception("Error adding equipment: " . $stmt->error);
    }

    $equipmentId = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Equipment added successfully",
        "equipment_id" => $equipmentId
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in add_equipment_item.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>