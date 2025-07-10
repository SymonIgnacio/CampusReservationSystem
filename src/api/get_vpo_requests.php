<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "campus_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit();
}

// Get only VPO pending requests
$stmt = $conn->prepare("SELECT * FROM request WHERE status = 'pending_vpo' ORDER BY date_created DESC");
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "success" => true,
    "requests" => $requests
]);

$conn->close();
?>