<?php
// Disable error display in response to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file instead
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

// Function to generate dates between two dates
function getDatesFromRange($start, $end) {
    $dates = [];
    $current = strtotime($start);
    $end = strtotime($end);

    while ($current <= $end) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }

    return $dates;
}

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed");
    }

    // Get JSON data from request
    $jsonInput = file_get_contents("php://input");
    error_log("Raw input: " . $jsonInput);
    
    $data = json_decode($jsonInput, true);

    if (!$data) {
        throw new Exception("Invalid JSON data: " . json_last_error_msg());
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

    // Extract data
    $venue = $data['venue'] ?? '';
    $dateFrom = $data['dateFrom'] ?? '';
    $dateTo = $data['dateTo'] ?? '';
    $timeStart = $data['timeStart'] ?? '';
    $timeEnd = $data['timeEnd'] ?? '';
    $requestId = $data['requestId'] ?? null; // Optional, to exclude current request when editing
    
    if (!$venue || !$dateFrom || !$dateTo || !$timeStart || !$timeEnd) {
        throw new Exception("Missing required fields");
    }

    // Get all dates in the range
    $dates = getDatesFromRange($dateFrom, $dateTo);
    $conflicts = [];
    
    foreach ($dates as $date) {
        // Check for conflicts in approved_request table
        $query = "SELECT * FROM approved_request 
                 WHERE venue = ? 
                 AND date_need_from = ? 
                 AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssss", $venue, $date, $timeEnd, $timeStart, $timeEnd, $timeStart, $timeStart, $timeEnd);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $conflicts[] = [
                    'date' => $date,
                    'time' => $row['start_time'] . ' - ' . $row['end_time'],
                    'venue' => $row['venue'],
                    'activity' => $row['activity'],
                    'department' => $row['department_organization']
                ];
            }
        }
        $stmt->close();
        
        // Also check pending requests
        $query = "SELECT * FROM request 
                 WHERE venue = ? 
                 AND date_need_from = ? 
                 AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))";
        
        if ($requestId) {
            $query .= " AND id != ?";
        }
        
        $stmt = $conn->prepare($query);
        
        if ($requestId) {
            $stmt->bind_param("ssssssssi", $venue, $date, $timeEnd, $timeStart, $timeEnd, $timeStart, $timeStart, $timeEnd, $requestId);
        } else {
            $stmt->bind_param("ssssssss", $venue, $date, $timeEnd, $timeStart, $timeEnd, $timeStart, $timeStart, $timeEnd);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $conflicts[] = [
                    'date' => $date,
                    'time' => $row['start_time'] . ' - ' . $row['end_time'],
                    'venue' => $row['venue'],
                    'activity' => $row['activity'],
                    'department' => $row['department_organization'],
                    'status' => 'pending'
                ];
            }
        }
        $stmt->close();
    }
    
    // Return results
    if (count($conflicts) > 0) {
        echo json_encode([
            "available" => false,
            "conflicts" => $conflicts
        ]);
    } else {
        echo json_encode([
            "available" => true
        ]);
    }
    
    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in check_venue_availability.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>