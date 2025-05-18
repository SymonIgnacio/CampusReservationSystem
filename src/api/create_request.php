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

    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Format dates and times for database
    $dateFrom = isset($data['dateFrom']) ? $data['dateFrom'] : null;
    $dateTo = isset($data['dateTo']) ? $data['dateTo'] : null;
    $timeStart = isset($data['timeStart']) ? $data['timeStart'] : null;
    $timeEnd = isset($data['timeEnd']) ? $data['timeEnd'] : null;
    
    // Combine date and time for start_time and end_time
    $startTime = $dateFrom . ' ' . $timeStart . ':00';
    $endTime = $dateTo . ' ' . $timeEnd . ':00';
    
    // Get resource_id from venue name
    $venueName = $data['venue'];
    $resourceQuery = "SELECT resource_id FROM resources WHERE name = ?";
    $resourceStmt = $conn->prepare($resourceQuery);
    $resourceStmt->bind_param("s", $venueName);
    $resourceStmt->execute();
    $resourceResult = $resourceStmt->get_result();
    
    if ($resourceResult->num_rows === 0) {
        // If venue doesn't exist, create it
        $insertVenueQuery = "INSERT INTO resources (name, type, location, capacity, requires_approval) 
                            VALUES (?, 'event_hall', 'Campus', 100, 1)";
        $insertVenueStmt = $conn->prepare($insertVenueQuery);
        $insertVenueStmt->bind_param("s", $venueName);
        $insertVenueStmt->execute();
        $resourceId = $conn->insert_id;
        $insertVenueStmt->close();
    } else {
        $resourceRow = $resourceResult->fetch_assoc();
        $resourceId = $resourceRow['resource_id'];
    }
    $resourceStmt->close();
    
    // Insert into reservations table
    $insertQuery = "INSERT INTO reservations (user_id, resource_id, event_name, start_time, end_time, status, purpose) 
                   VALUES (?, ?, ?, ?, ?, 'pending', ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $userId = $data['userId'];
    $eventName = $data['activity'];
    $purpose = $data['purpose'];
    
    $insertStmt->bind_param("iissss", $userId, $resourceId, $eventName, $startTime, $endTime, $purpose);
    
    if (!$insertStmt->execute()) {
        throw new Exception("Error creating reservation: " . $insertStmt->error);
    }
    
    $reservationId = $conn->insert_id;
    $insertStmt->close();
    
    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Reservation request created successfully",
        "reservation_id" => $reservationId
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in create_request.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>