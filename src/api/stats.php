<?php
// Include CORS fix
require_once 'cors_fix.php';

// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

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

    // Get total events count
    $total_query = "SELECT COUNT(*) as total FROM (
                    SELECT id FROM approved_request
                    UNION
                    SELECT id FROM declined_request
                    UNION
                    SELECT request_id FROM request
                    ) as all_events";
    $result = $conn->query($total_query);
    $total_events = $result->fetch_assoc()['total'];

    // Get approved events count
    $approved_query = "SELECT COUNT(*) as approved FROM approved_request";
    $result = $conn->query($approved_query);
    $approved_events = $result->fetch_assoc()['approved'];

    // Get pending events count
    $pending_query = "SELECT COUNT(*) as pending FROM request WHERE status = 'pending'";
    $result = $conn->query($pending_query);
    $pending_events = $result->fetch_assoc()['pending'];

    // Get declined events count
    $declined_query = "SELECT COUNT(*) as declined FROM declined_request";
    $result = $conn->query($declined_query);
    $declined_events = $result->fetch_assoc()['declined'];

    // Get upcoming events count
    $upcoming_query = "SELECT COUNT(*) as upcoming FROM approved_request WHERE date_need_from >= CURDATE()";
    $result = $conn->query($upcoming_query);
    $upcoming_events = $result->fetch_assoc()['upcoming'];

    // Get most used venue
    $venue_query = "SELECT venue, COUNT(*) as count FROM approved_request GROUP BY venue ORDER BY count DESC LIMIT 1";
    $result = $conn->query($venue_query);
    $most_used_venue = $result->num_rows > 0 ? $result->fetch_assoc()['venue'] : 'N/A';

    // Get most active month
    $month_query = "SELECT MONTHNAME(date_need_from) as month, COUNT(*) as count 
                   FROM approved_request 
                   GROUP BY month 
                   ORDER BY count DESC LIMIT 1";
    $result = $conn->query($month_query);
    $most_active_month = $result->num_rows > 0 ? $result->fetch_assoc()['month'] : 'N/A';

    // Return results
    echo json_encode([
        "status" => "success",
        "total_events" => $total_events,
        "approved_events" => $approved_events,
        "pending_events" => $pending_events,
        "declined_events" => $declined_events,
        "upcoming_events" => $upcoming_events,
        "most_used_venue" => $most_used_venue,
        "most_active_month" => $most_active_month
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in stats.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}