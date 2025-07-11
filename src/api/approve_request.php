<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || (!isset($data['id']) && !isset($data['request_id']))) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit();
}

$request_id = isset($data['id']) ? $data['id'] : $data['request_id'];

$conn = new mysqli("localhost", "root", "", "campus_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit();
}

// Get current request
$stmt = $conn->prepare("SELECT * FROM request WHERE id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    echo json_encode(["success" => false, "message" => "Request not found"]);
    exit();
}

$currentStatus = $request['status'];

if ($currentStatus === 'pending' || $currentStatus === 'pending_gso') {
    // GSO Approval - Change status to pending_vpo
    $stmt = $conn->prepare("UPDATE request SET status = 'pending_vpo' WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Request approved by GSO. Sent to VPO for final approval."
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update status"]);
    }
    
} else if ($currentStatus === 'pending_vpo') {
    // VPO Approval - Move to approved_request table
    $stmt = $conn->prepare("INSERT INTO approved_request (reference_number, request_by, department_organization, activity, purpose, nature_of_activity, date_need_from, date_need_until, start_time, end_time, participants, total_male_attendees, total_female_attendees, venue, equipments_needed, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $approved_by = isset($data['approvedBy']) ? $data['approvedBy'] : 'VPO';
    
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
    
    if ($stmt->execute()) {
        // Delete from request table
        $stmt = $conn->prepare("DELETE FROM request WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        
        echo json_encode([
            "success" => true,
            "message" => "Request fully approved by VPO. Now available as upcoming event."
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to approve request"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request status"]);
}

$conn->close();
?>