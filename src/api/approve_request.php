<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include CORS fix
require_once 'cors_fix.php';

// Include session configuration
require_once 'session_config.php';

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception("Invalid JSON data");
    }
    
    // Validate required fields
    if (!isset($data['request_id'])) {
        throw new Exception("Missing request ID");
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

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get request data
        $sql = "SELECT * FROM request WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $data['request_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Request not found");
        }
        
        $request = $result->fetch_assoc();
        $stmt->close();
        
        // Get admin username from session
        $approved_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'admin';
        
        // Insert into approved_request table
        $sql = "INSERT INTO approved_request (
            request_id,
            reference_number,
            user_id,
            request_by,
            department_organization,
            activity,
            purpose,
            date_need_from,
            date_need_until,
            start_time,
            end_time,
            venue,
            resource_id,
            approved_by,
            approved_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("isisssssssssss", 
            $request['request_id'],
            $request['reference_number'],
            $request['user_id'],
            $request['request_by'],
            $request['department_organization'],
            $request['activity'],
            $request['purpose'],
            $request['date_need_from'],
            $request['date_need_until'],
            $request['time_need_from'],
            $request['time_need_until'],
            $request['venue'],
            $request['resource_id'],
            $approved_by
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        // Update request status
        $sql = "UPDATE request SET status = 'approved' WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $data['request_id']);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Return success
        echo json_encode([
            "success" => true,
            "message" => "Request approved successfully"
        ]);
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in approve_request.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}