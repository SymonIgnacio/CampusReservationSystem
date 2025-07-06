<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check for equipment requests that exceed available stock
    $sql = "SELECT 
                e.name as equipment_name,
                e.stock as available,
                SUM(re.quantity) as requested
            FROM equipment e
            LEFT JOIN request_equipment re ON e.id = re.equipment_id
            LEFT JOIN request r ON re.request_id = r.id
            WHERE r.status = 'pending' AND r.date_need_from >= CURDATE()
            GROUP BY e.id, e.name, e.stock
            HAVING requested > available";
    
    $result = $conn->query($sql);
    $alerts = [];
    
    while ($row = $result->fetch_assoc()) {
        $alerts[] = $row;
    }

    echo json_encode([
        "success" => true,
        "alerts" => $alerts
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}