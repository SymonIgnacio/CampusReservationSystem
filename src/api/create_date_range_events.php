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

    // Extract data from request
    $referenceNumber = $data['referenceNumber'] ?? '';
    $requestBy = isset($data['requestorName']) ? $data['requestorName'] : $data['organizer'];
    $departmentOrganization = $data['department'] ?? '';
    $activity = isset($data['activity']) ? $data['activity'] : $data['eventName'];
    $purpose = $data['purpose'] ?? '';
    
    // Handle nature of activity
    $natureOfActivity = $data['activityNature'] ?? 'curricular';
    if ($natureOfActivity === 'others') {
        $natureOfActivity = 'co-curricular';
    }
    
    $dateNeedFrom = $data['dateFrom'] ?? '';
    $dateNeedUntil = $data['dateTo'] ?? '';
    $startTime = $data['timeStart'] ?? '';
    $endTime = $data['timeEnd'] ?? '';
    $participants = $data['participants'] ?? '';
    $totalMaleAttendees = intval($data['malePax'] ?? 0);
    $totalFemaleAttendees = intval($data['femalePax'] ?? 0);
    $venue = $data['venue'] ?? '';
    
    // Process equipment data
    $equipmentsNeeded = '';
    if (isset($data['equipmentNeeded']) && is_array($data['equipmentNeeded']) && !empty($data['equipmentNeeded'])) {
        $equipmentList = [];
        foreach ($data['equipmentNeeded'] as $eqId) {
            $quantity = $data['equipmentQuantities'][$eqId] ?? 1;
            
            // Get equipment name from ID
            $eqQuery = "SELECT name FROM equipment WHERE equipment_id = ?";
            $eqStmt = $conn->prepare($eqQuery);
            $eqStmt->bind_param("i", $eqId);
            $eqStmt->execute();
            $eqResult = $eqStmt->get_result();
            
            if ($eqResult->num_rows > 0) {
                $eqRow = $eqResult->fetch_assoc();
                $equipmentList[] = $eqRow['name'] . ' (' . $quantity . ')';
            }
            $eqStmt->close();
        }
        $equipmentsNeeded = implode(', ', $equipmentList);
    }
    
    // Status depends on whether this is an admin request or user request
    $status = isset($data['status']) ? $data['status'] : 'pending';
    
    // Get all dates between dateFrom and dateTo
    $dates = getDatesFromRange($dateNeedFrom, $dateNeedUntil);
    $createdRequests = [];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        foreach ($dates as $index => $date) {
            // Use the same reference number for all dates
            
            // Insert into the request table for each date
            $insertQuery = "INSERT INTO request (
                reference_number, 
                request_by, 
                department_organization, 
                activity, 
                purpose, 
                nature_of_activity, 
                date_need_from, 
                date_need_until, 
                start_time, 
                end_time, 
                participants, 
                total_male_attendees, 
                total_female_attendees, 
                venue, 
                equipments_needed,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param(
                "sssssssssssiisss", 
                $referenceNumber,
                $requestBy,
                $departmentOrganization,
                $activity,
                $purpose,
                $natureOfActivity,
                $date,  // Use the current date in the loop
                $date,  // Same date for both from and until
                $startTime,
                $endTime,
                $participants,
                $totalMaleAttendees,
                $totalFemaleAttendees,
                $venue,
                $equipmentsNeeded,
                $status
            );
            
            if (!$insertStmt->execute()) {
                throw new Exception("Error creating request for date $date: " . $insertStmt->error);
            }
            
            $requestId = $conn->insert_id;
            $createdRequests[] = [
                'id' => $requestId,
                'reference_number' => $referenceNumber,
                'date' => $date
            ];
            
            $insertStmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        echo json_encode([
            "success" => true,
            "message" => "Requests created successfully for all dates",
            "requests" => $createdRequests
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in create_date_range_events.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>