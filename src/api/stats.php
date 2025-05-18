<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Prevent PHP from showing errors in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "campus_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get reservation statistics
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get total count
    $totalSql = "SELECT COUNT(*) as total FROM reservations";
    $totalResult = $conn->query($totalSql);
    $totalRow = $totalResult->fetch_assoc();
    $total = $totalRow['total'];
    
    // Adjust these queries based on your actual status field in the reservations table
    // You might need to change 'status' to whatever field indicates approval status
    $pendingSql = "SELECT COUNT(*) as pending FROM reservations WHERE status = 'pending'";
    $pendingResult = $conn->query($pendingSql);
    $pendingRow = $pendingResult->fetch_assoc();
    $pending = $pendingRow ? $pendingRow['pending'] : 0;
    
    $approvedSql = "SELECT COUNT(*) as approved FROM reservations WHERE status = 'approved'";
    $approvedResult = $conn->query($approvedSql);
    $approvedRow = $approvedResult->fetch_assoc();
    $approved = $approvedRow ? $approvedRow['approved'] : 0;
    
    $declinedSql = "SELECT COUNT(*) as declined FROM reservations WHERE status = 'declined'";
    $declinedResult = $conn->query($declinedSql);
    $declinedRow = $declinedResult->fetch_assoc();
    $declined = $declinedRow ? $declinedRow['declined'] : 0;
    
    // Return the stats
    echo json_encode([
        "success" => true,
        "stats" => [
            "total" => (int)$total,
            "pending" => (int)$pending,
            "approved" => (int)$approved,
            "declined" => (int)$declined
        ]
    ]);
}

$conn->close();
?>