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
        $sql = "SELECT * FROM request WHERE id = ?";
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
            reference_number,
            request_by,
            department_organization,
            activity,
            purpose,
            nature_of_activity,
            date_need_from,
            date_need_until,
            start_time,
            end_time,
            participants,
            total_male_attendees,
            total_female_attendees,
            venue,
            equipments_needed,
            approved_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssssssssssiisss", 
            $request['reference_number'],
            $request['request_by'],
            $request['department_organization'],
            $request['activity'],
            $request['purpose'],
            $request['nature_of_activity'],
            $request['date_need_from'],
            $request['date_need_until'],
            $request['start_time'],
            $request['end_time'],
            $request['participants'],
            $request['total_male_attendees'],
            $request['total_female_attendees'],
            $request['venue'],
            $request['equipments_needed'],
            $approved_by
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        // Update status based on current status
        $currentStatus = $request['status'] ?: 'pending_gso';
        error_log("Current status: " . $currentStatus);
        
        if ($currentStatus === 'pending_gso' || $currentStatus === '' || $currentStatus === null) {
            // GSO approval - move to VPO pending (DO NOT move to approved_request yet)
            $sql = "UPDATE request SET status = 'pending_vpo' WHERE id = ?";
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
            
            echo json_encode([
                "success" => true,
                "message" => "Request approved by GSO. Sent to VPO for final approval.",
                "next_status" => "pending_vpo"
            ]);
            
        } else if ($currentStatus === 'pending_vpo') {
            // VPO approval - NOW move to approved_request table
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Delete from request table after successful insert
            $sql = "DELETE FROM request WHERE id = ?";
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
            
            echo json_encode([
                "success" => true,
                "message" => "Request fully approved by VPO. Now available as upcoming event.",
                "final_approval" => true
            ]);
        } else {
            throw new Exception("Invalid request status for approval: " . $currentStatus);
        }
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

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