<?php
require_once 'cors_fix.php';
header("Content-Type: application/json");

$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create system_logs table if it doesn't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS system_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        type ENUM('Info', 'Warning', 'Error') NOT NULL,
        message TEXT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert some sample logs if table is empty
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM system_logs");
    $checkStmt->execute();
    $count = $checkStmt->fetchColumn();
    
    if ($count == 0) {
        $conn->exec("INSERT INTO system_logs (type, message) VALUES 
            ('Info', 'System started successfully'),
            ('Info', 'Database connection established'),
            ('Warning', 'High memory usage detected'),
            ('Info', 'User login: admin@example.com')");
    }
    
    $stmt = $conn->prepare("SELECT * FROM system_logs ORDER BY timestamp DESC LIMIT 10");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "logs" => $logs
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>