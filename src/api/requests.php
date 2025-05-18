<?php
// requests.php - Fetch pending event requests

// Disable error display in output
error_reporting(0);
ini_set('display_errors', 0);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json");

// Function to return error response
function returnError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit;
}

try {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        returnError("Connection failed: " . $conn->connect_error, 500);
    }

    // Check if database exists
    $dbExists = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($dbExists->num_rows == 0) {
        // Create database if it doesn't exist
        if (!$conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
            returnError("Failed to create database: " . $conn->error, 500);
        }
        $conn->select_db($dbname);
    }

    // Check which table exists
    $reservationsTableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;
    $eventsTableExists = $conn->query("SHOW TABLES LIKE 'events'")->num_rows > 0;

    // If neither table exists, create one
    if (!$reservationsTableExists && !$eventsTableExists) {
        $createTableSQL = "CREATE TABLE events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            time_start TIME,
            time_end TIME,
            place VARCHAR(255),
            status VARCHAR(50) DEFAULT 'pending',
            organizer VARCHAR(255)
        )";
        
        if ($conn->query($createTableSQL)) {
            $eventsTableExists = true;
        } else {
            returnError("Failed to create events table: " . $conn->error, 500);
        }
    }

    $tableName = $reservationsTableExists ? 'reservations' : 'events';

    // Check if the table has a status column
    $hasStatusColumn = false;
    $columnsResult = $conn->query("DESCRIBE $tableName");
    while ($column = $columnsResult->fetch_assoc()) {
        if ($column['Field'] === 'status') {
            $hasStatusColumn = true;
            break;
        }
    }

    // Add status column if it doesn't exist
    if (!$hasStatusColumn) {
        $alterSql = "ALTER TABLE $tableName ADD COLUMN status VARCHAR(50) DEFAULT 'pending'";
        if (!$conn->query($alterSql)) {
            returnError("Failed to add status column: " . $conn->error, 500);
        }
    }

    // Get filter parameter if provided
    $status = isset($_GET['status']) ? $_GET['status'] : 'pending';

    // Prepare SQL query based on filter
    if ($status === 'all') {
        $sql = "SELECT * FROM $tableName";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT * FROM $tableName WHERE status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $status);
    }

    if (!$stmt) {
        returnError("Prepare statement failed: " . $conn->error, 500);
    }

    // Execute query
    if (!$stmt->execute()) {
        returnError("Query execution failed: " . $stmt->error, 500);
    }
    
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        // Add some basic formatting
        if (isset($row['date_from'])) {
            $row['date'] = $row['date_from'];
        }
        
        if (isset($row['time_start']) && isset($row['time_end'])) {
            $row['time'] = $row['time_start'] . ' - ' . $row['time_end'];
        }
        
        if (isset($row['activity'])) {
            $row['name'] = $row['activity'];
            $row['title'] = $row['activity'];
        }
        
        if (isset($row['venue'])) {
            $row['location'] = $row['venue'];
            $row['place'] = $row['venue'];
        }
        
        if (isset($row['requestor_name'])) {
            $row['organizer'] = $row['requestor_name'];
            $row['requestedBy'] = $row['requestor_name'];
        }
        
        // Ensure status field exists
        if (!isset($row['status'])) {
            // Default to pending for requests
            $row['status'] = 'pending';
        }
        
        // Ensure ID field exists
        if (!isset($row['id']) && isset($row['reservation_id'])) {
            $row['id'] = $row['reservation_id'];
        } else if (!isset($row['id']) && isset($row['event_id'])) {
            $row['id'] = $row['event_id'];
        } else if (!isset($row['id'])) {
            // Generate a unique ID if none exists
            $row['id'] = uniqid();
        }
        
        $requests[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "requests" => $requests,
        "count" => count($requests),
        "table" => $tableName
    ]);

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    returnError("Server error: " . $e->getMessage(), 500);
}
?>