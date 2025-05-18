<?php
// create_request.php - Updated to work with both reservations and reservations tables
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
// header("Access-Control-Allow-Credentials: true"); // Not needed with '*' origin
header("Content-Type: application/json");

// Create a log file for debugging
$logFile = fopen("request_log.txt", "a");
fwrite($logFile, "Request received at " . date('Y-m-d H:i:s') . "\n");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    fwrite($logFile, "OPTIONS request handled\n");
    fclose($logFile);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fwrite($logFile, "Method not allowed: " . $_SERVER['REQUEST_METHOD'] . "\n");
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    fclose($logFile);
    exit;
}

// Get the request body
$requestBody = file_get_contents("php://input");
fwrite($logFile, "Request body: " . $requestBody . "\n");

if (empty($requestBody)) {
    fwrite($logFile, "Empty request body\n");
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Empty request body"
    ]);
    fclose($logFile);
    exit;
}

try {
    $data = json_decode($requestBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        fwrite($logFile, "JSON parse error: " . json_last_error_msg() . "\n");
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON: " . json_last_error_msg()
        ]);
        fclose($logFile);
        exit;
    }
} catch (Exception $e) {
    fwrite($logFile, "Error parsing request: " . $e->getMessage() . "\n");
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Error parsing request: " . $e->getMessage()
    ]);
    fclose($logFile);
    exit;
}

// Connect to DB
try {
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        fwrite($logFile, "Database connection failed: " . $conn->connect_error . "\n");
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Connection failed: " . $conn->connect_error
        ]);
        fclose($logFile);
        exit;
    }
    
    fwrite($logFile, "Database connection successful\n");
} catch (Exception $e) {
    fwrite($logFile, "Database connection error: " . $e->getMessage() . "\n");
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection error: " . $e->getMessage()
    ]);
    fclose($logFile);
    exit;
}

// Check which table exists
$reservationsTableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;
$reservationsTableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;

$tableName = $reservationsTableExists ? 'reservations' : ($reservationsTableExists ? 'reservations' : null);

if (!$tableName) {
    // Create reservations table if neither exists
    fwrite($logFile, "No reservations or reservations table found. Creating reservations table...\n");
    
    $createTableSQL = "CREATE TABLE reservations (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        reference_number VARCHAR(20) NOT NULL,
        request_date DATE NOT NULL,
        requestor_name VARCHAR(255) NOT NULL,
        department VARCHAR(255) NOT NULL,
        activity VARCHAR(255) NOT NULL,
        purpose TEXT NOT NULL,
        activity_nature VARCHAR(50) NOT NULL,
        other_nature VARCHAR(255),
        date_from DATE NOT NULL,
        date_to DATE NOT NULL,
        time_start TIME NOT NULL,
        time_end TIME NOT NULL,
        participants TEXT,
        male_pax INT DEFAULT 0,
        female_pax INT DEFAULT 0,
        total_pax INT DEFAULT 0,
        venue VARCHAR(255) NOT NULL,
        equipment_needed TEXT,
        user_id INT(11),
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTableSQL)) {
        fwrite($logFile, "Failed to create reservations table: " . $conn->error . "\n");
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to create reservations table: " . $conn->error
        ]);
        fclose($logFile);
        exit;
    }
    
    $tableName = 'reservations';
    fwrite($logFile, "Reservations table created successfully\n");
}

fwrite($logFile, "Using table: " . $tableName . "\n");

try {
    // Prepare equipment data for storage
    $equipmentData = [];
    if (!empty($data['equipmentNeeded']) && is_array($data['equipmentNeeded'])) {
        foreach ($data['equipmentNeeded'] as $eqId) {
            $quantity = isset($data['equipmentQuantities'][$eqId]) ? $data['equipmentQuantities'][$eqId] : 1;
            $equipmentData[] = [
                'id' => $eqId,
                'quantity' => $quantity
            ];
        }
    }
    
    // Convert to JSON for storage
    $equipmentJson = json_encode($equipmentData);
    
    // Format dates for MySQL
    $requestDate = isset($data['requestDate']) ? date('Y-m-d', strtotime($data['requestDate'])) : date('Y-m-d');
    $dateFrom = isset($data['dateFrom']) ? date('Y-m-d', strtotime($data['dateFrom'])) : null;
    $dateTo = isset($data['dateTo']) ? date('Y-m-d', strtotime($data['dateTo'])) : null;
    $timeStart = isset($data['timeStart']) ? date('H:i:s', strtotime($data['timeStart'])) : null;
    $timeEnd = isset($data['timeEnd']) ? date('H:i:s', strtotime($data['timeEnd'])) : null;
    
    // Always set status to pending for faculty requests
    $status = 'pending';
    
    // Log the data we're about to insert
    fwrite($logFile, "Preparing to insert data:\n");
    fwrite($logFile, "Reference Number: " . $data['referenceNumber'] . "\n");
    fwrite($logFile, "Request Date: " . $requestDate . "\n");
    fwrite($logFile, "Requestor Name: " . $data['requestorName'] . "\n");
    fwrite($logFile, "Department: " . $data['department'] . "\n");
    fwrite($logFile, "Activity: " . $data['activity'] . "\n");
    fwrite($logFile, "Date From: " . $dateFrom . "\n");
    fwrite($logFile, "Date To: " . $dateTo . "\n");
    fwrite($logFile, "Time Start: " . $timeStart . "\n");
    fwrite($logFile, "Time End: " . $timeEnd . "\n");
    
    // Use a simpler SQL query for debugging
    $sql = "INSERT INTO $tableName (
        reference_number, request_date, requestor_name, department, 
        activity, purpose, activity_nature, other_nature, 
        date_from, date_to, time_start, time_end, 
        participants, male_pax, female_pax, total_pax, 
        venue, equipment_needed, user_id, status
    ) VALUES (
        '{$data['referenceNumber']}', '$requestDate', '{$data['requestorName']}', '{$data['department']}',
        '{$data['activity']}', '{$data['purpose']}', '{$data['activityNature']}', '{$data['otherNature']}',
        '$dateFrom', '$dateTo', '$timeStart', '$timeEnd',
        '{$data['participants']}', {$data['malePax']}, {$data['femalePax']}, {$data['totalPax']},
        '{$data['venue']}', '$equipmentJson', {$data['userId']}, '$status'
    )";
    
    fwrite($logFile, "SQL Query: " . $sql . "\n");
    
    if (!$conn->query($sql)) {
        fwrite($logFile, "Error executing query: " . $conn->error . "\n");
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error creating request: " . $conn->error
        ]);
        fclose($logFile);
        exit;
    }
    
    $requestId = $conn->insert_id;
    fwrite($logFile, "Request created successfully with ID: " . $requestId . "\n");
    
    echo json_encode([
        "success" => true,
        "message" => "Venue request submitted successfully! Your request is pending approval.",
        "request_id" => $requestId
    ]);
    
    fclose($logFile);
    $conn->close();
} catch (Exception $e) {
    fwrite($logFile, "Error processing request: " . $e->getMessage() . "\n");
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error processing request: " . $e->getMessage()
    ]);
    fclose($logFile);
    exit;
}
?>