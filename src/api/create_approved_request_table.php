<?php
// Disable error display in response to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/htdocs/CampusReservationSystem/php_errors.log');

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

    // Check if approved_request table already exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'approved_request'");
    if ($tableCheck->num_rows > 0) {
        echo "Table 'approved_request' already exists.";
    } else {
        // Create approved_request table with same structure as request table
        $sql = "CREATE TABLE approved_request LIKE request";
        if (!$conn->query($sql)) {
            throw new Exception("Error creating table structure: " . $conn->error);
        }

        // Add approved_by and approved_at columns
        $sql = "ALTER TABLE approved_request 
                ADD COLUMN approved_by VARCHAR(255) NOT NULL,
                ADD COLUMN approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error adding columns: " . $conn->error);
        }

        echo "Table 'approved_request' created successfully.";
    }

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in create_approved_request_table.php: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
?>