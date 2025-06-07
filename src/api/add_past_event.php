<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Direct CORS headers first
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

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

    // Check if we have any past events
    $checkQuery = "SELECT COUNT(*) as count FROM reservations WHERE status = 'approved' AND start_time < NOW()";
    $checkResult = $conn->query($checkQuery);
    $row = $checkResult->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Get a resource ID to use
        $resourceQuery = "SELECT resource_id FROM resources LIMIT 1";
        $resourceResult = $conn->query($resourceQuery);
        if ($resourceResult->num_rows > 0) {
            $resourceRow = $resourceResult->fetch_assoc();
            $resourceId = $resourceRow['resource_id'];
            
            // Get a user ID to use
            $userQuery = "SELECT user_id FROM users LIMIT 1";
            $userResult = $conn->query($userQuery);
            if ($userResult->num_rows > 0) {
                $userRow = $userResult->fetch_assoc();
                $userId = $userRow['user_id'];
                
                // Add past events
                // Event 1: Yesterday
                $startDate1 = date('Y-m-d H:i:s', strtotime('-1 day'));
                $endDate1 = date('Y-m-d H:i:s', strtotime('-1 day +2 hours'));
                
                $insertPast1 = "INSERT INTO reservations (user_id, resource_id, event_name, start_time, end_time, status, purpose) 
                               VALUES ($userId, $resourceId, 'Past Event 1', 
                               '$startDate1', '$endDate1', 
                               'approved', 'Past event for testing')";
                $conn->query($insertPast1);
                
                // Event 2: Last week
                $startDate2 = date('Y-m-d H:i:s', strtotime('-7 days'));
                $endDate2 = date('Y-m-d H:i:s', strtotime('-7 days +3 hours'));
                
                $insertPast2 = "INSERT INTO reservations (user_id, resource_id, event_name, start_time, end_time, status, purpose) 
                               VALUES ($userId, $resourceId, 'Past Event 2', 
                               '$startDate2', '$endDate2', 
                               'approved', 'Another past event for testing')";
                $conn->query($insertPast2);
            }
        }
    }

    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Past events added successfully"
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in add_past_event.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>