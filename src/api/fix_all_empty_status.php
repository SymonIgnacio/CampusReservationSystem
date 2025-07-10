<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    $conn = new mysqli("localhost", "root", "", "campus_db");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Update all empty/null status to pending_gso
    $sql = "UPDATE request SET status = 'pending_gso' WHERE status = '' OR status IS NULL OR status = 'pending'";
    $result = $conn->query($sql);
    
    $affected = $conn->affected_rows;
    
    // Get current status distribution
    $statusQuery = "SELECT status, COUNT(*) as count FROM request GROUP BY status";
    $statusResult = $conn->query($statusQuery);
    $statusCounts = [];
    while ($row = $statusResult->fetch_assoc()) {
        $statusCounts[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "message" => "Fixed $affected requests with empty/pending status",
        "affected_rows" => $affected,
        "current_status_distribution" => $statusCounts
    ]);
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>