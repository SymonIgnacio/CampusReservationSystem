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

    if (!$data || !isset($data['resource_id']) || !isset($data['name']) || !isset($data['location'])) {
        throw new Exception("Missing required fields");
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

    // Update facility
    $stmt = $conn->prepare("UPDATE facilities SET venue = ?, campus = ?, capacity = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssisi", $data['name'], $data['location'], $data['capacity'], $data['description'], $data['resource_id']);

    if (!$stmt->execute()) {
        throw new Exception("Error updating facility: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("No facility found with ID: " . $data['resource_id']);
    }

    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Facility updated successfully"
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in update_facility.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>