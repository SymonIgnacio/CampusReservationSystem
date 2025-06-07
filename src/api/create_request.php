<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection details
$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception("Invalid JSON data");
    }
    
    // Validate required fields
    if (!isset($data['activity']) || !isset($data['venue']) || !isset($data['date_need_from'])) {
        throw new Exception("Missing required fields");
    }

    // Connect to DB
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Generate reference number
    $reference_number = 'REQ-' . date('YmdHis') . '-' . rand(1000, 9999);
    
    // Get user ID from data
    $user_id = isset($data['user_id']) ? $data['user_id'] : null;
    
    if (!$user_id) {
        throw new Exception("User ID is required");
    }
    
    // Get user name from database
    $user_query = "SELECT CONCAT(firstname, ' ', lastname) as full_name FROM users WHERE user_id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows === 0) {
        throw new Exception("User not found");
    }
    
    $user_row = $user_result->fetch_assoc();
    $request_by = $user_row['full_name'];
    $user_stmt->close();
    
    // Extract date and time from combined fields
    $date_from_parts = explode(' ', $data['date_need_from']);
    $date_to_parts = explode(' ', $data['date_need_until']);
    
    $date_from = $date_from_parts[0];
    $time_from = isset($date_from_parts[1]) ? $date_from_parts[1] : '00:00:00';
    
    $date_to = $date_to_parts[0];
    $time_to = isset($date_to_parts[1]) ? $date_to_parts[1] : '00:00:00';
    
    // Prepare equipment string
    $equipment = isset($data['equipment']) ? $data['equipment'] : '';
    
    // Insert request - using the correct table name 'request'
    $sql = "INSERT INTO request (
        reference_number, 
        user_id,
        request_by,
        department_organization,
        activity,
        purpose,
        date_need_from,
        date_need_until,
        time_need_from,
        time_need_until,
        venue,
        status,
        date_created
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sissssssss", 
        $reference_number,
        $user_id,
        $request_by,
        $data['department'],
        $data['activity'],
        $data['purpose'],
        $date_from,
        $date_to,
        $time_from,
        $time_to,
        $data['venue']
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $request_id = $stmt->insert_id;
    $stmt->close();

    // Return success
    echo json_encode([
        "status" => "success",
        "message" => "Request created successfully",
        "request_id" => $request_id,
        "reference_number" => $reference_number
    ]);

    $conn->close();
} catch (Exception $e) {
    // Return error as JSON
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>