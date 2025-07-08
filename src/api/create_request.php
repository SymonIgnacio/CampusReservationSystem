<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['firebase_uid'])) {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
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
    echo json_encode(["status" => "error", "message" => "User not found"]);
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
$malePax = isset($data['malePax']) ? (int)$data['malePax'] : 0;
$femalePax = isset($data['femalePax']) ? (int)$data['femalePax'] : 0;

// Insert request
$sql = "INSERT INTO request (reference_number, request_by, department_organization, activity, purpose, nature_of_activity, date_need_from, date_need_until, start_time, end_time, participants, total_male_attendees, total_female_attendees, venue, equipments_needed, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Insert preparation failed"]);
    exit();
}

$status = 'pending';
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

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Request created successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to execute insert"]);
}

$conn->close();
?>