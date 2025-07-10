<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the received data for debugging
error_log("Received data: " . $input);
error_log("Parsed data: " . print_r($data, true));

if (!$data || !isset($data['firebase_uid'])) {
    error_log("Invalid data or missing firebase_uid");
    echo json_encode(["status" => "error", "message" => "Invalid data or missing firebase_uid"]);
    exit();
}

$conn = @new mysqli("localhost", "root", "", "campus_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Get user full name
$stmt = $conn->prepare("SELECT CONCAT(firstname, ' ', lastname) as full_name FROM users WHERE firebase_uid = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Query preparation failed"]);
    exit();
}

$stmt->bind_param("s", $data['firebase_uid']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    error_log("User not found for firebase_uid: " . $data['firebase_uid']);
    echo json_encode(["status" => "error", "message" => "User not found for firebase_uid: " . $data['firebase_uid']]);
    exit();
}

// Extract date/time
$date_parts = explode(' ', $data['date_need_from']);
$date_from = $date_parts[0];
$time_from = isset($date_parts[1]) ? $date_parts[1] : '00:00:00';

$date_parts2 = explode(' ', $data['date_need_until']);
$date_to = $date_parts2[0];
$time_to = isset($date_parts2[1]) ? $date_parts2[1] : '00:00:00';

// Prepare data
$ref = 'REQ-' . rand(100000, 999999);
$nature = (isset($data['activityNature']) && $data['activityNature'] === 'co-curricular') ? 'co-curricular' : 'curricular';
$equipment = isset($data['equipment']) ? $data['equipment'] : '';
$participants = isset($data['participants']) ? $data['participants'] : '';
$malePax = isset($data['total_male_attendees']) ? (int)$data['total_male_attendees'] : (isset($data['malePax']) ? (int)$data['malePax'] : 0);
$femalePax = isset($data['total_female_attendees']) ? (int)$data['total_female_attendees'] : (isset($data['femalePax']) ? (int)$data['femalePax'] : 0);

// Insert request
$sql = "INSERT INTO request (reference_number, request_by, department_organization, activity, purpose, nature_of_activity, date_need_from, date_need_until, start_time, end_time, participants, total_male_attendees, total_female_attendees, venue, equipments_needed, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Insert preparation failed"]);
    exit();
}

$status = 'pending_gso'; // Start with GSO approval first
error_log("Setting status to: " . $status);
$stmt->bind_param("sssssssssssiisss", 
    $ref,
    $user['full_name'],
    $data['department'],
    $data['activity'],
    $data['purpose'],
    $nature,
    $date_from,
    $date_to,
    $time_from,
    $time_to,
    $participants,
    $malePax,
    $femalePax,
    $data['venue'],
    $equipment,
    $status
);

error_log("Binding parameters: ref=$ref, user={$user['full_name']}, dept={$data['department']}, activity={$data['activity']}, status=$status");

if ($stmt->execute()) {
    error_log("Request created successfully with reference: " . $ref);
    echo json_encode(["status" => "success", "message" => "Request created successfully", "reference" => $ref]);
} else {
    error_log("Failed to execute insert: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Failed to execute insert: " . $stmt->error]);
}

$conn->close();
?>