<?php
// Enable error display for debugging
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
    $hasActivity = in_array('activity', $columns);
    $hasEventName = in_array('event_name', $columns);
    $hasDateNeedFrom = in_array('date_need_from', $columns);
    $hasDateNeedUntil = in_array('date_need_until', $columns);
    $hasStartTime = in_array('start_time', $columns);
    $hasEndTime = in_array('end_time', $columns);
    $hasVenue = in_array('venue', $columns);
    
    // Construct select part of query
    $select = "SELECT ar." . ($hasRequestId ? "request_id" : "id") . " as id";
    
    // Add event name/activity
    if ($hasActivity) {
        $select .= ", ar.activity";
    } else if ($hasEventName) {
        $select .= ", ar.event_name as activity";
    } else {
        $select .= ", 'Unknown Event' as activity";
    }
    
    // Add date fields
    if ($hasDateNeedFrom) {
        $select .= ", ar.date_need_from";
    } else if ($hasStartTime) {
        $select .= ", DATE(ar.start_time) as date_need_from";
    } else {
        $select .= ", CURRENT_DATE as date_need_from";
    }
    
    if ($hasDateNeedUntil) {
        $select .= ", ar.date_need_until";
    } else if ($hasEndTime) {
        $select .= ", DATE(ar.end_time) as date_need_until";
    } else if ($hasDateNeedFrom) {
        $select .= ", ar.date_need_from as date_need_until";
    } else {
        $select .= ", CURRENT_DATE as date_need_until";
    }
    
    // Add time fields
    if ($hasStartTime) {
        $select .= ", ar.start_time";
    } else {
        $select .= ", '00:00:00' as start_time";
    }
    
    if ($hasEndTime) {
        $select .= ", ar.end_time";
    } else if ($hasStartTime) {
        $select .= ", ar.start_time as end_time";
    } else {
        $select .= ", '23:59:59' as end_time";
    }
    
    // Add venue field
    if ($hasVenue) {
        $select .= ", ar.venue";
    } else {
        $select .= ", 'Unknown Location' as venue";
    }
    
    // Add reference number and approved_at
    $select .= ", ar.reference_number, ar.approved_at";
    
    // Complete query - only get future events
    $sql = $select . " FROM approved_request ar";
    
    // Add WHERE clause to filter only upcoming events
    if ($hasDateNeedFrom) {
        $sql .= " WHERE ar.date_need_from >= CURDATE()";
    } else {
        $sql .= " WHERE DATE(ar.approved_at) >= CURDATE()";
    }
    
    $sql .= " ORDER BY " . ($hasDateNeedFrom ? "ar.date_need_from" : "ar.approved_at") . " ASC";
    
    // Execute query
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error . " SQL: " . $sql);
    }
    
    $events = [];
    $processedRefs = [];
    
    while ($row = $result->fetch_assoc()) {
        // Skip if we've already processed this reference number
        if (!empty($row['reference_number']) && in_array($row['reference_number'], $processedRefs)) {
            continue;
        }
        
        // Add to processed list
        if (!empty($row['reference_number'])) {
            $processedRefs[] = $row['reference_number'];
        }
        
        // Format date range to "Month DD, YYYY"
        $startDate = date('F j, Y', strtotime($row['date_need_from']));
        $endDate = date('F j, Y', strtotime($row['date_need_until']));
        $dateRange = $startDate;
        if ($row['date_need_from'] != $row['date_need_until']) {
            $dateRange .= ' - ' . $endDate;
        }
        
        // Format time range
        $startTime = new DateTime($row['start_time']);
        $endTime = new DateTime($row['end_time']);
        $timeRange = $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A');
        
        $events[] = [
            'id' => $row['id'],
            'activity' => $row['activity'],
            'date' => $dateRange,
            'time' => $timeRange,
            'venue' => $row['venue'] ?? 'Unknown',
            'reference_number' => $row['reference_number'] ?? 'N/A',
            'date_created' => $row['approved_at'] ?? date('Y-m-d H:i:s')
        ];
    }

    // Return results
    echo json_encode([
        "success" => true,
        "events" => $events,
        "debug" => [
            "query" => $sql,
            "columns" => $columns,
            "count" => count($events)
        ]
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in admin_dashboard_approved_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>