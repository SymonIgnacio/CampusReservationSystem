<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Prevent PHP from showing errors in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

// Get table structure
$tableStructure = [];
$structureResult = $conn->query("DESCRIBE users");
if ($structureResult) {
    while ($row = $structureResult->fetch_assoc()) {
        $tableStructure[] = $row;
    }
}

// Get all users (without passwords for security)
$users = [];
$result = $conn->query("SELECT user_id, username, firstname, lastname, email, role FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Return the data
echo json_encode([
    "success" => true,
    "table_structure" => $tableStructure,
    "user_count" => count($users),
    "users" => $users
]);

$conn->close();
?>