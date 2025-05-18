<?php
// Enable error reporting but don't display errors in output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers
if (!headers_sent()) {
 header("Access-Control-Allow-Origin: *");
 header("Access-Control-Allow-Headers: Content-Type");
 header("Access-Control-Allow-Methods: POST, OPTIONS");
 header("Content-Type: application/json");
// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to return error response
}
function returnError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError("Method not allowed", 405);
}

// Get the request body
$requestBody = file_get_contents("php://input");
if (empty($requestBody)) {
    returnError("Empty request body");
}

try {
    $data = json_decode($requestBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        returnError("Invalid JSON: " . json_last_error_msg());
    }
} catch (Exception $e) {
    returnError("Error parsing request: " . $e->getMessage());
}

// Check required fields
$requiredFields = ['name', 'start_time', 'end_time', 'place'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        returnError("Missing required field: $field");
    }
}

// Connect to DB
try {
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        returnError("Connection failed: " . $conn->connect_error, 500);
    }
} catch (Exception $e) {
    returnError("Database connection error: " . $e->getMessage(), 500);
}

try {
    // Check if events table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'events'");
    if ($tableCheck->num_rows == 0) {
        // Create events table if it doesn't exist
        $createTableSQL = "CREATE TABLE events (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            start_time DATETIME NOT NULL,
            end_time DATETIME NOT NULL,
            place VARCHAR(255) NOT NULL,
            organizer VARCHAR(255),
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($createTableSQL)) {
            returnError("Failed to create events table: " . $conn->error, 500);
        }
    }
    
    // Set default status to pending if not provided
    if (!isset($data['status']) || empty($data['status'])) {
        $data['status'] = 'pending';
    }
    
    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO events (name, description, start_time, end_time, place, organizer, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        returnError("Prepare failed: " . $conn->error, 500);
    }
    
    // Set default values for optional fields
    $description = isset($data['description']) ? $data['description'] : '';
    $organizer = isset($data['organizer']) ? $data['organizer'] : '';
    
    $stmt->bind_param("sssssss", 
        $data['name'],
        $description,
        $data['start_time'],
        $data['end_time'],
        $data['place'],
        $organizer,
        $data['status']
    );
    
    if (!$stmt->execute()) {
        returnError("Error creating event: " . $stmt->error, 500);
    }
    
    $eventId = $stmt->insert_id;
    
    echo json_encode([
        "success" => true,
        "message" => "Event created successfully",
        "event_id" => $eventId
    ]);
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    returnError("Error processing request: " . $e->getMessage(), 500);
}
?>