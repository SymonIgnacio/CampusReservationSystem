<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// CORS headers
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

    // Get current date
    $today = date('Y-m-d');
    
    // Get events from both tables - first from events table
    $eventsQuery = "SELECT 
                    'event' as source,
                    event_id as id,
                    title as name,
                    description as purpose,
                    start_date as start_time,
                    end_date as end_time,
                    location,
                    organizer,
                    department,
                    reference_number,
                    date_created
                FROM 
                    events
                WHERE
                    DATE(start_date) >= '$today'
                ORDER BY 
                    start_date DESC";
    
    $eventsResult = $conn->query($eventsQuery);
    
    if (!$eventsResult) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    // Then from approved reservations
    $reservationsQuery = "SELECT 
                    'reservation' as source,
                    r.reservation_id as id, 
                    r.event_name as name, 
                    r.purpose,
                    r.start_time, 
                    r.end_time, 
                    res.name as location,
                    CONCAT(u.firstname, ' ', u.lastname) as organizer,
                    u.department,
                    r.reference_number,
                    r.date_created
                FROM 
                    reservations r
                JOIN 
                    resources res ON r.resource_id = res.resource_id
                JOIN 
                    users u ON r.user_id = u.user_id
                WHERE 
                    r.status = 'approved'
                    AND DATE(r.start_time) >= '$today'
                    AND r.reservation_id NOT IN (SELECT reservation_id FROM events)
                ORDER BY 
                    r.start_time DESC";
    
    $reservationsResult = $conn->query($reservationsQuery);
    
    if (!$reservationsResult) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    // Combine results
    $events = [];
    
    // Add events from events table
    while ($row = $eventsResult->fetch_assoc()) {
        $startTime = new DateTime($row['start_time']);
        $endTime = new DateTime($row['end_time']);
        
        $events[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'date' => $startTime->format('Y-m-d'),
            'time' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
            'location' => $row['location'],
            'reference_number' => $row['reference_number'],
            'date_created' => $row['date_created'],
            'purpose' => $row['purpose'],
            'organizer' => $row['organizer'],
            'department' => $row['department'],
            'source' => $row['source']
        ];
    }
    
    // Add events from reservations table
    while ($row = $reservationsResult->fetch_assoc()) {
        $startTime = new DateTime($row['start_time']);
        $endTime = new DateTime($row['end_time']);
        
        $events[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'date' => $startTime->format('Y-m-d'),
            'time' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
            'location' => $row['location'],
            'reference_number' => $row['reference_number'],
            'date_created' => $row['date_created'],
            'purpose' => $row['purpose'],
            'organizer' => $row['organizer'],
            'department' => $row['department'],
            'source' => $row['source']
        ];
    }

    // Return results
    echo json_encode([
        "success" => true,
        "events" => $events
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in get_dashboard_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>