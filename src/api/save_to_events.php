<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

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
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['id'])) {
        throw new Exception("Invalid JSON data or missing ID");
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

    // Get reservation data
    $reservationId = $data['id'];
    $query = "SELECT 
                r.reservation_id, 
                r.event_name, 
                r.start_time, 
                r.end_time, 
                r.purpose,
                r.reference_number,
                r.date_created,
                res.name as location,
                u.firstname, 
                u.lastname, 
                u.department
            FROM 
                reservations r
            JOIN 
                resources res ON r.resource_id = res.resource_id
            JOIN 
                users u ON r.user_id = u.user_id
            WHERE 
                r.reservation_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $reservationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Reservation not found");
    }
    
    $reservation = $result->fetch_assoc();
    
    // Insert into events table
    $insertQuery = "INSERT INTO events (
                    reservation_id, 
                    title, 
                    description, 
                    start_date, 
                    end_date, 
                    location, 
                    organizer, 
                    department,
                    reference_number,
                    date_created
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $startTime = new DateTime($reservation['start_time']);
    $endTime = new DateTime($reservation['end_time']);
    $organizer = $reservation['firstname'] . ' ' . $reservation['lastname'];
    
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param(
        "isssssssss", 
        $reservation['reservation_id'],
        $reservation['event_name'],
        $reservation['purpose'],
        $reservation['start_time'],
        $reservation['end_time'],
        $reservation['location'],
        $organizer,
        $reservation['department'],
        $reservation['reference_number'],
        $reservation['date_created']
    );
    
    if (!$insertStmt->execute()) {
        // Check if it's a duplicate entry error
        if ($insertStmt->errno == 1062) {
            // Event already exists, return success
            echo json_encode([
                "success" => true,
                "message" => "Event already exists in events table"
            ]);
            exit();
        }
        throw new Exception("Error inserting event: " . $insertStmt->error);
    }
    
    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Event saved to events table successfully"
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in save_to_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>