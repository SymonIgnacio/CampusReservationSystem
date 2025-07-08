<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

if (!isset($_GET['status']) || !isset($_GET['firebase_uid'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters'
    ]);
    exit();
}

$status = $_GET['status'];
$firebase_uid = $_GET['firebase_uid'];

$valid_statuses = ['pending', 'approved', 'declined'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status parameter'
    ]);
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=campus_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user full name
    $user_query = "SELECT CONCAT(firstname, ' ', lastname) as full_name FROM users WHERE firebase_uid = :firebase_uid";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bindParam(':firebase_uid', $firebase_uid);
    $user_stmt->execute();
    $user_row = $user_stmt->fetch(PDO::FETCH_ASSOC);
    $full_name = $user_row ? $user_row['full_name'] : 'Unknown User';
    
    $requests = [];
    
    if ($status === 'pending') {
        $query = "SELECT *, status FROM request 
                 WHERE request_by = :full_name AND status LIKE 'pending%' 
                 ORDER BY date_created DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug log
        error_log('User: ' . $full_name);
        error_log('Pending requests found: ' . count($requests));
    } 
    else if ($status === 'approved') {
        $query = "SELECT *, 'approved' as status FROM approved_request 
                 WHERE request_by = :full_name 
                 ORDER BY date_created DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    else if ($status === 'declined') {
        $query = "SELECT *, 'declined' as status FROM declined_request 
                 WHERE request_by = :full_name 
                 ORDER BY date_created DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'status' => 'success',
        'requests' => $requests
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
}
?>