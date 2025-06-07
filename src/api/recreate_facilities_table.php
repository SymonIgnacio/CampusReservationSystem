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
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // First, delete notifications
        $conn->query("DELETE FROM notifications");
        
        // Delete all reservations
        $conn->query("DELETE FROM reservations");
        
        // Drop the resources table
        $conn->query("DROP TABLE IF EXISTS resources");
        
        // Create new facilities table
        $createTable = "CREATE TABLE facilities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            venue VARCHAR(255) NOT NULL,
            campus VARCHAR(255) NOT NULL,
            capacity INT,
            description TEXT
        )";
        
        if (!$conn->query($createTable)) {
            throw new Exception("Error creating facilities table: " . $conn->error);
        }
        
        // Insert some sample data
        $insertData = "INSERT INTO facilities (venue, campus, capacity, description) VALUES
            ('Main Hall', 'Main Campus', 500, 'Large auditorium for events'),
            ('Conference Room A', 'Main Campus', 50, 'Medium-sized conference room'),
            ('Lecture Hall 101', 'East Campus', 200, 'Lecture hall with projector'),
            ('Sports Field', 'West Campus', 1000, 'Outdoor sports field'),
            ('Computer Lab', 'North Campus', 30, 'Computer lab with 30 workstations')";
            
        if (!$conn->query($insertData)) {
            throw new Exception("Error inserting sample data: " . $conn->error);
        }
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Facilities table recreated successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        // Re-enable foreign key checks even on error
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        throw $e;
    }

    $conn->close();
    
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in recreate_facilities_table.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>