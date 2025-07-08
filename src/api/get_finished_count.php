<?php
require_once 'cors_fix.php';
header("Content-Type: application/json");

// Database connection details
$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    // Connect to database
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get finished events count (approved events with past dates)
    $finished_query = "SELECT COUNT(*) as finished_count FROM approved_request 
                      WHERE date_need_from < CURDATE()";
    $finished_stmt = $conn->prepare($finished_query);
    $finished_stmt->execute();
    $finished_result = $finished_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "finished_count" => $finished_result['finished_count']
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>