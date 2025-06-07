<?php
// Disable error display in response to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/htdocs/CampusReservationSystem/php_errors.log');

// Ensure clean output
ob_clean();

// Start output buffering to catch any unwanted output
ob_start();

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

    // Extract date range
    $dateFrom = isset($data['dateFrom']) ? $data['dateFrom'] : null;
    $dateTo = isset($data['dateTo']) ? $data['dateTo'] : $dateFrom; // Default to dateFrom if dateTo not provided
    $timeStart = isset($data['timeStart']) ? $data['timeStart'] : null;
    $timeEnd = isset($data['timeEnd']) ? $data['timeEnd'] : null;
    
    // Store only the time part without date
    $startTimeOnly = $timeStart . ':00';
    $endTimeOnly = $timeEnd . ':00';
    
    // Get resource_id from venue name
    $venueName = $data['venue'];
    $resourceQuery = "SELECT resource_id FROM resources WHERE name = ?";
    $resourceStmt = $conn->prepare($resourceQuery);
    $resourceStmt->bind_param("s", $venueName);
    $resourceStmt->execute();
    $resourceResult = $resourceStmt->get_result();
    
    if ($resourceResult->num_rows === 0) {
        // If venue doesn't exist, create it
        $insertVenueQuery = "INSERT INTO resources (name, type, location, capacity, requires_approval) 
                            VALUES (?, 'event_hall', 'Campus', 100, 1)";
        $insertVenueStmt = $conn->prepare($insertVenueQuery);
        $insertVenueStmt->bind_param("s", $venueName);
        $insertVenueStmt->execute();
        $resourceId = $conn->insert_id;
        $insertVenueStmt->close();
    } else {
        $resourceRow = $resourceResult->fetch_assoc();
        $resourceId = $resourceRow['resource_id'];
    }
    $resourceStmt->close();
    
    // Debug received data
    error_log("Received data: " . print_r($data, true));
    
    // Extract common data
    $userId = $data['userId'] ?? 1;
    $eventName = $data['activity'] ?? $data['eventName'] ?? '';
    $purpose = $data['purpose'] ?? '';
    $referenceNumber = $data['referenceNumber'] ?? '';
    $status = $data['status'] ?? 'pending';
    $department = $data['department'] ?? '';
    $organizer = $data['requestorName'] ?? '';
    
    // Extract additional fields
    $activityNature = $data['activityNature'] ?? '';
    if ($activityNature === 'others') {
        $activityNature = $data['otherNature'] ?? 'Other';
    }
    $participants = $data['participants'] ?? '';
    $malePax = intval($data['malePax'] ?? 0);
    $femalePax = intval($data['femalePax'] ?? 0);
    $totalPax = intval($data['totalPax'] ?? 0);
    
    // Debug extracted data
    error_log("Extracted data: userId=$userId, eventName=$eventName, purpose=$purpose, referenceNumber=$referenceNumber, status=$status, department=$department, organizer=$organizer, activityNature=$activityNature, participants=$participants, malePax=$malePax, femalePax=$femalePax, totalPax=$totalPax");
    
    // Create DateTime objects for start and end dates
    $startDate = new DateTime($dateFrom);
    $endDate = new DateTime($dateTo);
    $endDate->modify('+1 day'); // Include the end date
    
    // Check if start_date and end_date columns exist
    $checkColumn = $conn->query("SHOW COLUMNS FROM reservations LIKE 'start_date'");
    $hasDateColumns = $checkColumn->num_rows > 0;
    
    // Prepare the insert statement based on table structure
    if ($hasDateColumns) {
        $insertQuery = "INSERT INTO reservations (user_id, resource_id, event_name, start_time, end_time, start_date, end_date, status, purpose, reference_number, department, organizer, activity_nature, participants, male_pax, female_pax, total_pax, date_created) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE())";
    } else {
        $insertQuery = "INSERT INTO reservations (user_id, resource_id, event_name, start_time, end_time, status, purpose, reference_number, department, organizer, activity_nature, participants, male_pax, female_pax, total_pax, date_created) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE())";
    }
    
    $insertStmt = $conn->prepare($insertQuery);
    
    // Loop through each day in the date range
    $interval = new DateInterval('P1D'); // 1 day interval
    $dateRange = new DatePeriod($startDate, $interval, $endDate);
    
    $createdReservations = [];
    
    foreach ($dateRange as $date) {
        $currentDate = $date->format('Y-m-d');
        
        // Create start and end times for this day
        $dayStartTime = $currentDate . ' ' . $startTimeOnly;
        $dayEndTime = $currentDate . ' ' . $endTimeOnly;
        
        // Debug information
        error_log("SQL Query: " . $insertQuery);
        
        // Bind parameters based on table structure
        try {
            if ($hasDateColumns) {
                $insertStmt->bind_param("iisssssssssssiiii", 
                    $userId, 
                    $resourceId, 
                    $eventName, 
                    $dayStartTime,
                    $dayEndTime,
                    $dateFrom,  // Original start date for reference
                    $dateTo,    // Original end date for reference
                    $status, 
                    $purpose, 
                    $referenceNumber,
                    $department,
                    $organizer,
                    $activityNature,
                    $participants,
                    $malePax,
                    $femalePax,
                    $totalPax
                );
            } else {
                $insertStmt->bind_param("iissssssssssiiii", 
                    $userId, 
                    $resourceId, 
                    $eventName, 
                    $dayStartTime,
                    $dayEndTime,
                    $status, 
                    $purpose, 
                    $referenceNumber,
                    $department,
                    $organizer,
                    $activityNature,
                    $participants,
                    $malePax,
                    $femalePax,
                    $totalPax
                );
            }
        } catch (Exception $e) {
            error_log("Bind param error: " . $e->getMessage());
            throw new Exception("Error binding parameters: " . $e->getMessage());
        }
        
        if (!$insertStmt->execute()) {
            throw new Exception("Error creating reservation for date $currentDate: " . $insertStmt->error);
        }
        
        $createdReservations[] = [
            'date' => $currentDate,
            'reservation_id' => $conn->insert_id
        ];
    }
    
    $insertStmt->close();
    
    // Handle equipment data if present
    if (isset($data['equipmentNeeded']) && is_array($data['equipmentNeeded']) && !empty($data['equipmentNeeded'])) {
        try {
            // After successful reservation creation, save equipment data
            $equipmentQuery = "INSERT INTO reservation_equipment (reservation_id, equipment_id, quantity) VALUES (?, ?, ?)";
            $equipmentStmt = $conn->prepare($equipmentQuery);
            
            foreach ($createdReservations as $reservation) {
                $reservationId = $reservation['reservation_id'];
                
                foreach ($data['equipmentNeeded'] as $eqId) {
                    $quantity = $data['equipmentQuantities'][$eqId] ?? 1;
                    
                    $equipmentStmt->bind_param("iii", $reservationId, $eqId, $quantity);
                    $equipmentStmt->execute();
                }
            }
            
            $equipmentStmt->close();
        } catch (Exception $e) {
            // Just log the error but don't fail the whole request
            error_log("Error saving equipment data: " . $e->getMessage());
        }
    }

    // Clear any previous output
    ob_clean();
    
    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Reservations created successfully for all dates in range",
        "reservations" => $createdReservations
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in create_event_with_range.php: " . $e->getMessage());
    
    // Clear any previous output
    ob_clean();
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>