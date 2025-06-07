<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first - must be before any output
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get all approved events (both upcoming and finished)
    $sql = "SELECT ar.id, ar.reference_number, ar.event_name, ar.purpose, 
                   ar.date_need_from, ar.date_need_until, ar.time_need_from, ar.time_need_until,
                   ar.status, ar.approved_by, ar.approved_at, ar.department,
                   ar.venue_name as venue, ar.venue_location as location,
                   ar.requestor_name as organizer
            FROM approved_request ar
            WHERE ar.status = 'approved'
            ORDER BY ar.date_need_from DESC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates and times
        $startDate = new DateTime($row['date_need_from']);
        $endDate = new DateTime($row['date_need_until']);
        $startTime = $row['time_need_from'];
        $endTime = $row['time_need_until'];
        
        $row['date'] = $startDate->format('Y-m-d');
        $row['time'] = $startTime . ' - ' . $endTime;
        
        // Add a flag to indicate if the event is finished
        $currentTime = new DateTime();
        $row['is_finished'] = ($endDate < $currentTime) ? true : false;
        
        $events[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "events" => $events
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in approved_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>