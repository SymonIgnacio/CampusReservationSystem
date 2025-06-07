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

    // Extract data from request
    $referenceNumber = $data['referenceNumber'] ?? '';
    $requestBy = $data['organizer'] ?? '';
    $departmentOrganization = $data['department'] ?? '';
    $activity = $data['eventName'] ?? '';
    $purpose = $data['purpose'] ?? '';
    
    // Handle nature of activity
    $natureOfActivity = $data['activityNature'] ?? 'curricular';
    if ($natureOfActivity === 'others') {
        // If using 'others', we need to handle this differently since our DB only accepts 'curricular' or 'co-curricular'
        // For now, we'll default to 'co-curricular' if it's not one of the two accepted values
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
    
    // Admin created events are auto-approved
    $status = 'approved';
    
    // Insert into the new request table
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
        $dateNeedFrom,
        $dateNeedUntil,
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
        throw new Exception("Error creating request: " . $insertStmt->error);
    }
    
    $requestId = $conn->insert_id;
    $insertStmt->close();

    // Clear any previous output
    ob_clean();
    
    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Event created successfully",
        "requestId" => $requestId
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in admin_create_request.php: " . $e->getMessage());
    
    // Clear any previous output
    ob_clean();
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>