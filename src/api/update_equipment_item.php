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
    if (!isset($data['id']) || empty($data['id'])) {
        throw new Exception("Equipment ID is required");
    }

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

    // Check if equipment exists
    $checkStmt = $conn->prepare("SELECT * FROM equipments WHERE equipment_id = ?");
    $checkStmt->bind_param("i", $data['id']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Equipment not found");
    }
    $checkStmt->close();

    // Set default values for optional fields
    $stock = isset($data['stock']) && !empty($data['stock']) ? (int)$data['stock'] : 0;

    // Update equipment
    $stmt = $conn->prepare("UPDATE equipments SET name = ?, stock = ? WHERE equipment_id = ?");
    $stmt->bind_param("sii", $data['name'], $stock, $data['id']);

    if (!$stmt->execute()) {
        throw new Exception("Error updating equipment: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Equipment updated successfully"
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in update_equipment_item.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>