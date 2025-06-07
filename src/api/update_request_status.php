<?php
// Disable error display in response to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/htdocs/CampusReservationSystem/php_errors.log');

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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
    $jsonInput = file_get_contents("php://input");
    error_log("Raw input: " . $jsonInput);
    
    $data = json_decode($jsonInput, true);

    if (!$data) {
        throw new Exception("Invalid JSON data: " . json_last_error_msg());
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

    // Extract data
    $id = $data['id'] ?? null;
    $status = $data['status'] ?? null;
    
    if (!$id || !$status) {
        throw new Exception("Missing required fields: id and status");
    }

    // Update request status
    $query = "UPDATE request SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating request status: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        "success" => true,
        "message" => "Request status updated successfully"
    ]);
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in update_request_status.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>