<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log errors to file
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/htdocs/CampusReservationSystem/php_errors.log');

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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

    // Get a sample request for testing
    $query = "SELECT * FROM request LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows === 0) {
        echo "No requests found for testing.";
        exit();
    }
    
    $request = $result->fetch_assoc();
    
    echo "<h2>Request Data:</h2>";
    echo "<pre>";
    print_r($request);
    echo "</pre>";
    
    // Check if venue field exists and has a value
    if (!isset($request['venue'])) {
        echo "<p style='color:red'>ERROR: 'venue' field does not exist in the request table!</p>";
    } elseif (empty($request['venue'])) {
        echo "<p style='color:orange'>WARNING: 'venue' field is empty in this request.</p>";
    } else {
        echo "<p style='color:green'>GOOD: 'venue' field exists and has value: " . $request['venue'] . "</p>";
    }
    
    // Check approved_request table structure
    $query = "DESCRIBE approved_request";
    $result = $conn->query($query);
    
    echo "<h2>Approved Request Table Structure:</h2>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $hasVenueField = false;
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'venue') {
            $hasVenueField = true;
        }
    }
    
    echo "</table>";
    
    if (!$hasVenueField) {
        echo "<p style='color:red'>ERROR: 'venue' field does not exist in the approved_request table!</p>";
    } else {
        echo "<p style='color:green'>GOOD: 'venue' field exists in the approved_request table.</p>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>