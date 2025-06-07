<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

try {
    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Add start_date and end_date columns if they don't exist
    $checkColumnsQuery = "SHOW COLUMNS FROM reservations LIKE 'start_date'";
    $result = $conn->query($checkColumnsQuery);
    
    if ($result->num_rows == 0) {
        // Add the columns
        $alterQuery = "ALTER TABLE reservations 
                      ADD COLUMN start_date DATE AFTER start_time,
                      ADD COLUMN end_date DATE AFTER end_time";
        
        if (!$conn->query($alterQuery)) {
            throw new Exception("Error adding columns: " . $conn->error);
        }
        
        // Update existing records
        $updateQuery = "UPDATE reservations 
                       SET start_date = DATE(start_time),
                           end_date = DATE(end_time)";
        
        if (!$conn->query($updateQuery)) {
            throw new Exception("Error updating records: " . $conn->error);
        }
        
        echo "Columns added and data updated successfully";
    } else {
        echo "Columns already exist";
    }

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>