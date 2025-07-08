<?php
require_once 'cors_fix.php';
header("Content-Type: application/json");

$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['request_id'])) {
        echo json_encode(["success" => false, "message" => "Missing request_id"]);
        exit;
    }
    
    $request_id = $data['request_id'];
    $decline_reason = isset($data['decline_reason']) ? $data['decline_reason'] : 'No reason provided';
    
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->beginTransaction();
    
    // Get request
    $stmt = $conn->prepare("SELECT * FROM request WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(["success" => false, "message" => "Request not found"]);
        exit;
    }
    
    // Insert into declined_request using correct column names
    $stmt = $conn->prepare("INSERT INTO declined_request (
        reference_number, date_created, request_by, department_organization, 
        activity, purpose, nature_of_activity, date_need_from, date_need_until, 
        start_time, end_time, participants, total_male_attendees, 
        total_female_attendees, venue, equipments_needed, 
        status, reason, rejected_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'declined', ?, 'Admin')");
    
    $stmt->execute([
        $request['reference_number'],
        $request['date_created'],
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
        $decline_reason
    ]);
    
    // Delete from request table
    $stmt = $conn->prepare("DELETE FROM request WHERE id = ?");
    $stmt->execute([$request_id]);
    
    $conn->commit();
    
    echo json_encode(["success" => true, "message" => "Request declined successfully"]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>