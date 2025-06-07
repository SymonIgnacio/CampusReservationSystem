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
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if description column exists
    $result = $conn->query("SHOW COLUMNS FROM resources LIKE 'description'");
    
    if ($result->num_rows == 0) {
        // Add description column if it doesn't exist
        $alterTableSQL = "ALTER TABLE resources ADD COLUMN description TEXT DEFAULT NULL";
        
        if ($conn->query($alterTableSQL)) {
            echo "Description column added successfully!";
        } else {
            echo "Error adding description column: " . $conn->error;
        }
    } else {
        echo "Description column already exists.";
    }

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>