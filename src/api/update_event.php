<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include CORS fix
require_once 'cors_fix.php';

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception("Invalid JSON data");
    }
    
    // Validate required fields
    if (!isset($data['id']) || !isset($data['activity']) || !isset($data['venue'])) {
        throw new Exception("Missing required fields");
    }

    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Update event in approved_request table
    $sql = "UPDATE approved_request SET 
            activity = ?, 
            purpose = ?,
            date_need_from = ?,
            date_need_until = ?,
            start_time = ?,
            end_time = ?,
            venue = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sssssssi", 
        $data['activity'],
        $data['purpose'],
        $data['date_need_from'],
        $data['date_need_until'],
        $data['start_time'],
        $data['end_time'],
        $data['venue'],
        $data['id']
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        // Try updating the events table as fallback
        $stmt->close();
        
        $fallbackSql = "UPDATE events SET 
                name = ?, 
                description = ?,
                start_time = ?,
                end_time = ?,
                location = ?
                WHERE id = ?";
        
        $fallbackStmt = $conn->prepare($fallbackSql);
        
        if (!$fallbackStmt) {
            throw new Exception("Fallback prepare failed: " . $conn->error);
        }
        
        // Combine date and time for start_time and end_time
        $startDateTime = $data['date_need_from'] . ' ' . $data['start_time'];
        $endDateTime = $data['date_need_until'] . ' ' . $data['end_time'];
        
        $fallbackStmt->bind_param("sssssi", 
            $data['activity'],
            $data['purpose'],
            $startDateTime,
            $endDateTime,
            $data['venue'],
            $data['id']
        );
        
        $fallbackResult = $fallbackStmt->execute();
        
        if (!$fallbackResult) {
            throw new Exception("Fallback execute failed: " . $fallbackStmt->error);
        }
        
        if ($fallbackStmt->affected_rows === 0) {
            throw new Exception("No event found with ID: " . $data['id']);
        }
        
        $fallbackStmt->close();
    } else {
        $stmt->close();
    }

    // Return success
    echo json_encode([
        "success" => true,
        "message" => "Event updated successfully"
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in update_event.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}