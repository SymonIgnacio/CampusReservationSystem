<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

// Database connection details
$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    // Connect to database
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get total events (all requests)
    $total_query = "SELECT COUNT(*) as total FROM request";
    $total_stmt = $conn->prepare($total_query);
    $total_stmt->execute();
    $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
    $total_events = $total_result['total'];
    
    // Get pending events
    $pending_query = "SELECT COUNT(*) as pending FROM request WHERE status = 'pending'";
    $pending_stmt = $conn->prepare($pending_query);
    $pending_stmt->execute();
    $pending_result = $pending_stmt->fetch(PDO::FETCH_ASSOC);
    $pending_events = $pending_result['pending'];
    
    // Get approved events
    $approved_query = "SELECT COUNT(*) as approved FROM approved_request";
    $approved_stmt = $conn->prepare($approved_query);
    $approved_stmt->execute();
    $approved_result = $approved_stmt->fetch(PDO::FETCH_ASSOC);
    $approved_events = $approved_result['approved'];
    
    // Get declined events
    $declined_query = "SELECT COUNT(*) as declined FROM declined_request";
    $declined_stmt = $conn->prepare($declined_query);
    $declined_stmt->execute();
    $declined_result = $declined_stmt->fetch(PDO::FETCH_ASSOC);
    $declined_events = $declined_result['declined'];
    
    // Get upcoming events (approved events with future dates)
    $upcoming_query = "SELECT COUNT(*) as upcoming FROM approved_request 
                      WHERE STR_TO_DATE(CONCAT(date_need_from, ' ', start_time), '%Y-%m-%d %H:%i:%s') > NOW()";
    $upcoming_stmt = $conn->prepare($upcoming_query);
    $upcoming_stmt->execute();
    $upcoming_result = $upcoming_stmt->fetch(PDO::FETCH_ASSOC);
    $upcoming_events = $upcoming_result['upcoming'];
    
    // Get most used venue
    $venue_query = "SELECT venue, COUNT(*) as count FROM approved_request GROUP BY venue ORDER BY count DESC LIMIT 1";
    $venue_stmt = $conn->prepare($venue_query);
    $venue_stmt->execute();
    $venue_result = $venue_stmt->fetch(PDO::FETCH_ASSOC);
    $most_used_venue = $venue_result ? $venue_result['venue'] : 'N/A';
    
    // Get most active month
    $month_query = "SELECT MONTHNAME(STR_TO_DATE(date_need_from, '%Y-%m-%d')) as month, 
                   COUNT(*) as count FROM request 
                   GROUP BY month ORDER BY count DESC LIMIT 1";
    $month_stmt = $conn->prepare($month_query);
    $month_stmt->execute();
    $month_result = $month_stmt->fetch(PDO::FETCH_ASSOC);
    $most_active_month = $month_result ? $month_result['month'] : 'N/A';
    
    // Get monthly requests data for chart
    $monthly_query = "SELECT MONTHNAME(STR_TO_DATE(date_need_from, '%Y-%m-%d')) as month, 
                     COUNT(*) as count FROM request 
                     GROUP BY month ORDER BY MONTH(STR_TO_DATE(date_need_from, '%Y-%m-%d'))";
    $monthly_stmt = $conn->prepare($monthly_query);
    $monthly_stmt->execute();
    $monthly_requests = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get department requests data for chart
    $dept_query = "SELECT department_organization as department, COUNT(*) as count 
                  FROM request GROUP BY department ORDER BY count DESC";
    $dept_stmt = $conn->prepare($dept_query);
    $dept_stmt->execute();
    $department_requests = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return all stats as JSON
    echo json_encode([
        'status' => 'success',
        'total_events' => $total_events,
        'pending_events' => $pending_events,
        'approved_events' => $approved_events,
        'declined_events' => $declined_events,
        'upcoming_events' => $upcoming_events,
        'most_used_venue' => $most_used_venue,
        'most_active_month' => $most_active_month,
        'monthly_requests' => $monthly_requests,
        'department_requests' => $department_requests
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>