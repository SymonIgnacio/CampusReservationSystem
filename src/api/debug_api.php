<?php
// debug_api.php - Debug API responses
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Debug Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .endpoint {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .response {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
            margin-top: 10px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>API Debug Tool</h1>
    
    <div class="endpoint">
        <h2>Events API</h2>
        <p>Endpoint: <code>/events.php</code></p>
        <?php
        $eventsResponse = file_get_contents("http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api/events.php");
        
        // Check if the response is valid JSON
        $isValidJson = false;
        $jsonData = null;
        
        try {
            $jsonData = json_decode($eventsResponse, true);
            $isValidJson = (json_last_error() === JSON_ERROR_NONE);
        } catch (Exception $e) {
            $isValidJson = false;
        }
        
        if ($isValidJson) {
            echo "<p class='success'>✓ Valid JSON response</p>";
        } else {
            echo "<p class='error'>✗ Invalid JSON response</p>";
            echo "<p class='error'>JSON Error: " . json_last_error_msg() . "</p>";
        }
        
        echo "<div class='response'>" . htmlspecialchars($eventsResponse) . "</div>";
        ?>
    </div>
    
    <div class="endpoint">
        <h2>Users API</h2>
        <p>Endpoint: <code>/get_users.php</code></p>
        <?php
        $usersResponse = file_get_contents("http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api/get_users.php");
        
        // Check if the response is valid JSON
        $isValidJson = false;
        $jsonData = null;
        
        try {
            $jsonData = json_decode($usersResponse, true);
            $isValidJson = (json_last_error() === JSON_ERROR_NONE);
        } catch (Exception $e) {
            $isValidJson = false;
        }
        
        if ($isValidJson) {
            echo "<p class='success'>✓ Valid JSON response</p>";
        } else {
            echo "<p class='error'>✗ Invalid JSON response</p>";
            echo "<p class='error'>JSON Error: " . json_last_error_msg() . "</p>";
        }
        
        echo "<div class='response'>" . htmlspecialchars($usersResponse) . "</div>";
        ?>
    </div>
    
    <div class="endpoint">
        <h2>Requests API</h2>
        <p>Endpoint: <code>/requests.php</code></p>
        <?php
        $requestsResponse = file_get_contents("http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api/requests.php");
        
        // Check if the response is valid JSON
        $isValidJson = false;
        $jsonData = null;
        
        try {
            $jsonData = json_decode($requestsResponse, true);
            $isValidJson = (json_last_error() === JSON_ERROR_NONE);
        } catch (Exception $e) {
            $isValidJson = false;
        }
        
        if ($isValidJson) {
            echo "<p class='success'>✓ Valid JSON response</p>";
        } else {
            echo "<p class='error'>✗ Invalid JSON response</p>";
            echo "<p class='error'>JSON Error: " . json_last_error_msg() . "</p>";
        }
        
        echo "<div class='response'>" . htmlspecialchars($requestsResponse) . "</div>";
        ?>
    </div>
    
    <script>
        function refreshPage() {
            location.reload();
        }
    </script>
    
    <button onclick="refreshPage()">Refresh All</button>
</body>
</html>