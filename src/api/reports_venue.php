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

    $sql = "SELECT 
                f.venue as venue_name,
                COUNT(ar.id) as total_bookings,
                MAX(ar.date_created) as last_booking,
                COALESCE(MIN(ar.date_created), NOW()) as created_date,
                'Active' as status
            FROM facilities f
            LEFT JOIN approved_request ar ON f.venue = ar.venue
            GROUP BY f.venue
            ORDER BY total_bookings DESC";
    
    $result = $conn->query($sql);
    $venues = [];
    
    while ($row = $result->fetch_assoc()) {
        $venues[] = $row;
    }

    echo json_encode([
        "success" => true,
        "venues" => $venues
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}