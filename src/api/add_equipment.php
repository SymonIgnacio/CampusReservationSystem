<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first - must be before any output
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed");
    }

    // Get JSON data from request
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    if (!isset($data['name']) || empty($data['name'])) {
        throw new Exception("Equipment name is required");
    }

    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if resources table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'resources'");
    if ($tableCheck->num_rows == 0) {
        // Create resources table if it doesn't exist
        $createTableSQL = "CREATE TABLE `resources` (
            `resource_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `type` enum('classroom','event_hall','lab','equipment') NOT NULL,
            `location` varchar(100) DEFAULT NULL,
            `capacity` int(11) DEFAULT NULL,
            `description` text DEFAULT NULL,
            `requires_approval` tinyint(1) DEFAULT 0,
            PRIMARY KEY (`resource_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if (!$conn->query($createTableSQL)) {
            throw new Exception("Failed to create resources table: " . $conn->error);
        }
    }

    // Set default values for optional fields
    $location = isset($data['location']) ? $data['location'] : null;
    $description = isset($data['description']) ? $data['description'] : null;
    $requiresApproval = isset($data['requires_approval']) ? ($data['requires_approval'] ? 1 : 0) : 0;
    $type = 'equipment'; // Always equipment type

    // Insert new equipment
    $stmt = $conn->prepare("INSERT INTO resources (name, type, location, description, requires_approval) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $data['name'], $type, $location, $description, $requiresApproval);

    if (!$stmt->execute()) {
        throw new Exception("Error adding equipment: " . $stmt->error);
    }

    $equipmentId = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Equipment added successfully",
        "equipment_id" => $equipmentId
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in add_equipment.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>