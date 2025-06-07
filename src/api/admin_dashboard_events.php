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

    // Get only approved events from today onwards, sorted by date
    // Use a simpler approach with subquery to get only one entry per reference_number
    $today = date('Y-m-d');
    $sql = "SELECT 
                r.reservation_id, 
                r.event_name, 
                r.start_time, 
                r.end_time, 
                res.name as location,
                r.reference_number,
                r.date_created
            FROM 
                reservations r
            JOIN 
                resources res ON r.resource_id = res.resource_id
            JOIN (
                SELECT 
                    reference_number, 
                    MIN(reservation_id) as min_id
                FROM 
                    reservations
                WHERE 
                    reference_number IS NOT NULL
                    AND reference_number != ''
                GROUP BY 
                    reference_number
            ) as min_res ON r.reservation_id = min_res.min_id
            WHERE 
                r.status = 'approved' 
                AND DATE(r.start_time) >= '$today'
            ORDER BY 
                r.start_time ASC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
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
        
        // Get date range for this event
        if (!empty($row['reference_number'])) {
            $dateRangeQuery = "SELECT MIN(DATE(start_time)) as min_date, MAX(DATE(end_time)) as max_date 
                              FROM reservations 
                              WHERE reference_number = '{$row['reference_number']}'";
            $dateResult = $conn->query($dateRangeQuery);
            $dateRow = $dateResult->fetch_assoc();
            
            $startDate = $dateRow['min_date'];
            $endDate = $dateRow['max_date'];
        } else {
            $startTime = new DateTime($row['start_time']);
            $endTime = new DateTime($row['end_time']);
            $startDate = $startTime->format('Y-m-d');
            $endDate = $endTime->format('Y-m-d');
        }
        
        // Format time
        $startTime = new DateTime($row['start_time']);
        
        $events[] = [
            'id' => $row['reservation_id'],
            'event_name' => $row['event_name'],
            'date' => $startDate . ($startDate != $endDate ? ' - ' . $endDate : ''),
            'time' => $startTime->format('h:i A'),
            'location' => $row['location'],
            'reference_number' => $row['reference_number'],
            'date_created' => $row['date_created']
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
    error_log("Error in admin_dashboard_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>