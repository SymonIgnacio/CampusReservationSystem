<?php
// Turn off output buffering
ob_end_clean();

// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get all users from the table
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "users" => $users
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in get_users.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>