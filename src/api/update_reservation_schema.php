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

    // Check if reservations table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'reservations'");
    if ($tableCheck->num_rows == 0) {
        throw new Exception("Reservations table does not exist");
    }

    // Check current status column definition
    $columnCheck = $conn->query("SHOW COLUMNS FROM reservations LIKE 'status'");
    $columnInfo = $columnCheck->fetch_assoc();
    
    // Update status column to support all three states if needed
    if ($columnInfo && strpos($columnInfo['Type'], 'enum') !== false) {
        // If it's already an enum, check if it has all three values
        if (strpos($columnInfo['Type'], 'pending') === false || 
            strpos($columnInfo['Type'], 'declined') === false) {
            
            // Modify the column to include all three states
            $alterQuery = "ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'approved', 'declined') DEFAULT 'pending'";
            if (!$conn->query($alterQuery)) {
                throw new Exception("Failed to update status column: " . $conn->error);
            }
        }
    } else {
        // If it's not an enum or doesn't exist, create it
        $alterQuery = "ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'approved', 'declined') DEFAULT 'pending'";
        if (!$conn->query($alterQuery)) {
            throw new Exception("Failed to update status column: " . $conn->error);
        }
    }

    // Add some test data with different statuses
    // First check if we have any pending or declined reservations
    $checkQuery = "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending' OR status = 'declined'";
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
                
                // Add a pending reservation - using proper date format
                $startDate1 = date('Y-m-d H:i:s', strtotime('+1 day'));
                $endDate1 = date('Y-m-d H:i:s', strtotime('+1 day +2 hours'));
                
                $insertPending = "INSERT INTO reservations (user_id, resource_id, event_name, start_time, end_time, status, purpose) 
                                 VALUES ($userId, $resourceId, 'Pending Test Event', 
                                 '$startDate1', '$endDate1', 
                                 'pending', 'Testing pending status')";
                $conn->query($insertPending);
                
                // Add a declined reservation - using proper date format
                $startDate2 = date('Y-m-d H:i:s', strtotime('+2 days'));
                $endDate2 = date('Y-m-d H:i:s', strtotime('+2 days +3 hours'));
                
                $insertDeclined = "INSERT INTO reservations (user_id, resource_id, event_name, start_time, end_time, status, purpose) 
                                  VALUES ($userId, $resourceId, 'Declined Test Event', 
                                  '$startDate2', '$endDate2', 
                                  'declined', 'Testing declined status')";
                $conn->query($insertDeclined);
            }
        }
    }

    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Reservation schema updated successfully to support pending, approved, and declined states"
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in update_reservation_schema.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>