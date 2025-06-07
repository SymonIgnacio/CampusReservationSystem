<?php
// Enable error display for debugging
ini_set('display_errors', 1);
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

    // Check if approved_request table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'approved_request'");
    if ($tableCheck->num_rows === 0) {
        throw new Exception("Table 'approved_request' does not exist");
    }
    
    // Get column names from approved_request table
    $columnsResult = $conn->query("SHOW COLUMNS FROM approved_request");
    $columns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $columns[] = $column['Field'];
    }
    
    // Build query based on available columns
    $hasRequestId = in_array('request_id', $columns);
    $hasEventName = in_array('event_name', $columns);
    $hasStartTime = in_array('start_time', $columns);
    $hasEndTime = in_array('end_time', $columns);
    $hasResourceId = in_array('resource_id', $columns);
    $hasReferenceNumber = in_array('reference_number', $columns);
    $hasOrganizer = in_array('organizer', $columns);
    
    // Construct query based on available columns
    $query = "SELECT 
                ar." . ($hasRequestId ? "request_id" : "id") . " as id, 
                ar." . ($hasEventName ? "event_name" : "name") . " as name, 
                ar." . ($hasStartTime ? "start_time" : "date_time") . " as start_time, 
                ar." . ($hasEndTime ? "end_time" : "date_time") . " as end_time,
                DATE(ar." . ($hasStartTime ? "start_time" : "date_time") . ") as event_date";
    
    // Add location if resource_id exists
    if ($hasResourceId) {
        $query .= ",
                res.name as location";
    } else {
        $query .= ",
                'Unknown' as location";
    }
    
    // Add reference number if it exists
    if ($hasReferenceNumber) {
        $query .= ",
                ar.reference_number";
    } else {
        $query .= ",
                'N/A' as reference_number";
    }
    
    // Add organizer if it exists
    if ($hasOrganizer) {
        $query .= ",
                ar.organizer";
    } else {
        $query .= ",
                'N/A' as organizer";
    }
    
    $query .= "
            FROM 
                approved_request ar";
    
    // Join with resources if resource_id exists
    if ($hasResourceId) {
        $query .= "
            LEFT JOIN 
                resources res ON ar.resource_id = res.resource_id";
    }
    
    // Order by start time if it exists
    if ($hasStartTime) {
        $query .= "
            ORDER BY 
                ar.start_time ASC";
    }
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error . " SQL: " . $query);
    }
    
    $events = [];
    $eventsByDate = [];
    
    while ($row = $result->fetch_assoc()) {
        // Handle potential missing fields
        $startTime = isset($row['start_time']) ? new DateTime($row['start_time']) : new DateTime();
        $endTime = isset($row['end_time']) ? new DateTime($row['end_time']) : clone $startTime;
        
        // Format time with AM/PM but without date
        $timeOnly = $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A');
        
        $event = [
            'id' => $row['id'],
            'name' => $row['name'],
            'date' => $row['event_date'],
            'time' => $timeOnly,
            'location' => $row['location'] ?? 'Unknown',
            'reference_number' => $row['reference_number'] ?? 'N/A',
            'organizer' => $row['organizer'] ?? 'N/A'
        ];
        
        $events[] = $event;
        
        // Group events by date
        $dateKey = $row['event_date'];
        if (!isset($eventsByDate[$dateKey])) {
            $eventsByDate[$dateKey] = [];
        }
        $eventsByDate[$dateKey][] = $event;
    }

    // Return results with both flat list and grouped by date
    echo json_encode([
        "success" => true,
        "events" => $events,
        "eventsByDate" => $eventsByDate
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in simple_approved_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>