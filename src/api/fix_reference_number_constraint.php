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

    // Check if the unique constraint exists
    $checkQuery = "SHOW CREATE TABLE request";
    $result = $conn->query($checkQuery);
    $row = $result->fetch_assoc();
    $createTableStatement = $row['Create Table'];
    
    // Output the current table structure
    echo "Current table structure:<br>";
    echo nl2br(htmlspecialchars($createTableStatement)) . "<br><br>";
    
    // Try to drop the unique constraint if it exists
    $alterQuery = "ALTER TABLE `request` DROP INDEX `reference_number`";
    if ($conn->query($alterQuery)) {
        echo "Successfully removed unique constraint on reference_number.<br>";
    } else {
        // If the first attempt fails, try with a different index name
        $alterQuery = "ALTER TABLE `request` DROP INDEX `idx_reference_number`";
        if ($conn->query($alterQuery)) {
            echo "Successfully removed unique constraint on reference_number (using idx_reference_number).<br>";
        } else {
            echo "No unique constraint found with standard names. Checking for other constraints...<br>";
            
            // Extract all indexes from the CREATE TABLE statement
            preg_match_all('/KEY\s+`([^`]+)`\s+\(`reference_number`\)/', $createTableStatement, $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $indexName) {
                    $alterQuery = "ALTER TABLE `request` DROP INDEX `$indexName`";
                    if ($conn->query($alterQuery)) {
                        echo "Successfully removed constraint named `$indexName` on reference_number.<br>";
                        break;
                    }
                }
            } else {
                echo "Could not find any index on reference_number column.<br>";
            }
        }
    }
    
    // Add a non-unique index for performance
    $addIndexQuery = "ALTER TABLE `request` ADD INDEX `idx_reference_number` (`reference_number`)";
    if ($conn->query($addIndexQuery)) {
        echo "Successfully added non-unique index on reference_number.<br>";
    } else {
        echo "Failed to add non-unique index: " . $conn->error . "<br>";
    }
    
    // Show the updated table structure
    $result = $conn->query($checkQuery);
    $row = $result->fetch_assoc();
    $updatedCreateTableStatement = $row['Create Table'];
    
    echo "<br>Updated table structure:<br>";
    echo nl2br(htmlspecialchars($updatedCreateTableStatement));

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>