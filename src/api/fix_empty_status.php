<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    $conn = new mysqli("localhost", "root", "", "campus_db");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Update all empty/null status to pending_gso
    $sql = "UPDATE request SET status = 'pending_gso' WHERE status = '' OR status IS NULL";
    $result = $conn->query($sql);
    
    if ($result) {
        $affected = $conn->affected_rows;
        echo json_encode([
            "success" => true,
            "message" => "Fixed $affected requests with empty status",
            "affected_rows" => $affected
        ]);
    } else {
        throw new Exception("Update failed: " . $conn->error);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>