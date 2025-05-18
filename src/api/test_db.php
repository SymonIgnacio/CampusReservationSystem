<?php
// test_db.php - Test database connection and display users table structure
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

// Test database connection
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Get users table structure
$tableStructure = [];
$structureResult = $conn->query("DESCRIBE users");
if ($structureResult) {
    while ($row = $structureResult->fetch_assoc()) {
        $tableStructure[] = $row;
    }
}

// Count users in the database
$countResult = $conn->query("SELECT COUNT(*) as count FROM users");
$userCount = 0;
if ($countResult) {
    $row = $countResult->fetch_assoc();
    $userCount = $row['count'];
}

// Get a sample of users (limit to 5 for security)
$users = [];
$result = $conn->query("SELECT user_id, username, firstname, lastname, email, role FROM users LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Return the results
echo json_encode([
    "success" => true,
    "connection" => "Connected successfully to database: " . $dbname,
    "table_structure" => $tableStructure,
    "user_count" => $userCount,
    "sample_users" => $users
]);

$conn->close();
?>