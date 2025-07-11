<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "campus_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit();
}

$stmt = $conn->prepare("SELECT DISTINCT campus FROM facilities WHERE campus IS NOT NULL AND campus != '' ORDER BY campus ASC");
$stmt->execute();
$result = $stmt->get_result();
$campuses = [];

while ($row = $result->fetch_assoc()) {
    $campuses[] = $row['campus'];
}

echo json_encode([
    "success" => true,
    "campuses" => $campuses
]);

$conn->close();
?>