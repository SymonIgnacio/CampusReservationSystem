<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include CORS fix
require_once 'cors_fix.php';

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

    // Get all events from approved_request table for calendar
    $sql = "SELECT 
                ar.request_id as reservation_id, 
                ar.event_name, 
                ar.start_time, 
                ar.end_time, 
                ar.purpose,
                ar.reference_number,
                ar.date_created,
                ar.department,
                ar.organizer,
                res.resource_id,
                res.name as location,
                u.firstname, 
                u.lastname, 
                u.department as user_department
            FROM 
                approved_request ar
            JOIN 
                resources res ON ar.resource_id = res.resource_id
            JOIN 
                users u ON ar.user_id = u.user_id
            ORDER BY 
                ar.start_time ASC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates and times
        $startTime = new DateTime($row['start_time']);
        $endTime = new DateTime($row['end_time']);
        
        $events[] = [
            'id' => $row['reservation_id'],
            'name' => $row['event_name'],
            'date' => $startTime->format('Y-m-d'),
            'time' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'location' => $row['location'],
            'resource_id' => $row['resource_id'],
            'reference_number' => $row['reference_number'],
            'date_created' => $row['date_created'],
            'purpose' => $row['purpose'],
            'organizer' => $row['organizer'] ? $row['organizer'] : $row['firstname'] . ' ' . $row['lastname'],
            'department' => $row['department']
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
    error_log("Error in calendar_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}