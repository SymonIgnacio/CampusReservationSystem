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

    // Get upcoming events from approved_request table
    $sql = "SELECT * FROM approved_request 
            WHERE date_need_from >= CURDATE() 
            ORDER BY date_need_from ASC, start_time ASC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates and times
        $startDate = new DateTime($row['date_need_from']);
        $endDate = new DateTime($row['date_need_until']);
        
        $events[] = [
            'id' => $row['id'],
            'name' => $row['activity'],
            'date' => $startDate->format('Y-m-d'),
            'time' => $row['start_time'] . ' - ' . $row['end_time'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'location' => $row['venue'],
            'reference_number' => $row['reference_number'],
            'date_created' => $row['approved_at'],
            'purpose' => $row['purpose'],
            'organizer' => $row['request_by'],
            'department' => $row['department_organization']
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
    error_log("Error in upcoming_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}