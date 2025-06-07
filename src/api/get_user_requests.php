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

// Check if required parameters are provided
if (!isset($_GET['status']) || !isset($_GET['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters'
    ]);
    exit();
}

$status = $_GET['status'];
$user_id = $_GET['user_id'];

// Validate status parameter
$valid_statuses = ['pending', 'approved', 'declined'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status parameter'
    ]);
    exit();
}

try {
    // Connect to database
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user name
    $user_query = "SELECT CONCAT(firstname, ' ', lastname) as full_name FROM users WHERE user_id = :user_id";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bindParam(':user_id', $user_id);
    $user_stmt->execute();
    $user_row = $user_stmt->fetch(PDO::FETCH_ASSOC);
    $full_name = $user_row['full_name'];
    
    $requests = [];
    
    // Query based on status
    if ($status === 'pending') {
        // Pending requests are in the main request table
        $query = "SELECT *, 'pending' as status FROM request 
                 WHERE request_by = :full_name AND status = 'pending' 
                 ORDER BY date_created DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    else if ($status === 'approved') {
        // Check if approved_request table exists
        $query = "SELECT *, 'approved' as status FROM approved_request 
                 WHERE request_by = :full_name 
                 ORDER BY date_created DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    else if ($status === 'declined') {
        // Check if declined_request table exists
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
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>