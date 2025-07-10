<?php
// Allow from any origin
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection details
$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['venue']) || !isset($data['date']) || !isset($data['start_time']) || !isset($data['end_time'])) {
        throw new Exception("Missing required parameters");
    }
    
    $venue = $data['venue'];
    $date = $data['date'];
    $start_time = $data['start_time'];
    $end_time = $data['end_time'];
    
    // Connect to database
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check for conflicts in approved_request table
    $stmt = $conn->prepare("SELECT COUNT(*) as conflicts FROM approved_request 
                           WHERE venue = :venue 
                           AND date_need_from = :date 
                           AND (
                               (start_time <= :start_time AND end_time > :start_time) OR
                               (start_time < :end_time AND end_time >= :end_time) OR
                               (start_time >= :start_time AND end_time <= :end_time)
                           )");
    
    $stmt->bindParam(':venue', $venue);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $conflicts = $result['conflicts'];
    
    echo json_encode([
        "success" => true,
        "available" => $conflicts == 0,
        "conflicts" => $conflicts
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>