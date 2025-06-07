<?php
// Simplified version of create_event_with_range.php for testing

// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file
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

    // Extract basic data
    $userId = $data['userId'] ?? 1;
    $eventName = $data['activity'] ?? '';
    $venue = $data['venue'] ?? '';
    $status = 'pending';
    
    // Simple insert query
    $query = "INSERT INTO reservations (user_id, event_name, status, date_created) VALUES (?, ?, ?, CURRENT_DATE())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $userId, $eventName, $status);
    
    if (!$stmt->execute()) {
        throw new Exception("Error creating reservation: " . $stmt->error);
    }
    
    $reservationId = $conn->insert_id;
    $stmt->close();
    
    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Reservation created successfully",
        "reservation_id" => $reservationId
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in simple_create_event.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>