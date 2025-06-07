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

    if (!$data || !isset($data['id']) || !isset($data['status'])) {
        throw new Exception("Missing required fields: id and status");
    }

    $eventId = $data['id'];
    $newStatus = $data['status'];
    $reason = isset($data['reason']) ? $data['reason'] : null;

    // Validate status
    $validStatuses = ['pending', 'approved', 'declined'];
    if (!in_array($newStatus, $validStatuses)) {
        throw new Exception("Invalid status value. Must be one of: " . implode(", ", $validStatuses));
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

    // Check if rejection_reason column exists, if not add it
    $checkColumn = $conn->query("SHOW COLUMNS FROM reservations LIKE 'rejection_reason'");
    if ($checkColumn->num_rows === 0) {
        $alterTable = "ALTER TABLE reservations ADD COLUMN rejection_reason TEXT NULL";
        if (!$conn->query($alterTable)) {
            throw new Exception("Error adding rejection_reason column: " . $conn->error);
        }
    }

    // Update event status in reservations table
    if ($newStatus === 'declined' && $reason !== null) {
        $stmt = $conn->prepare("UPDATE reservations SET status = ?, rejection_reason = ? WHERE reservation_id = ?");
        $stmt->bind_param("ssi", $newStatus, $reason, $eventId);
    } else {
        $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $stmt->bind_param("si", $newStatus, $eventId);
    }

    if (!$stmt->execute()) {
        throw new Exception("Error updating event status: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("No event found with ID: $eventId");
    }

    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Event status updated successfully to $newStatus"
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in update_event_status.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>