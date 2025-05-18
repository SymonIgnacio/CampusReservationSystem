<?php
// update_event_status.php - Update event status
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['status'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required parameters: id and status"
    ]);
    exit;
}

$id = intval($data['id']);
$status = $conn->real_escape_string($data['status']);

// Check which table exists
$reservationsTableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;
$eventsTableExists = $conn->query("SHOW TABLES LIKE 'events'")->num_rows > 0;

$tableName = $reservationsTableExists ? 'reservations' : ($eventsTableExists ? 'events' : null);

if (!$tableName) {
    echo json_encode([
        "success" => false,
        "message" => "No events or reservations table found in database"
    ]);
    exit;
}

// Check if the table has a status column
$hasStatusColumn = false;
$columnsResult = $conn->query("DESCRIBE $tableName");
while ($column = $columnsResult->fetch_assoc()) {
    if ($column['Field'] === 'status') {
        $hasStatusColumn = true;
        break;
    }
}

// Add status column if it doesn't exist
if (!$hasStatusColumn) {
    $alterSql = "ALTER TABLE $tableName ADD COLUMN status VARCHAR(50) DEFAULT 'approved'";
    if (!$conn->query($alterSql)) {
        echo json_encode([
            "success" => false,
            "message" => "Failed to add status column: " . $conn->error
        ]);
        exit;
    }
}

// Determine the ID column name
$idColumnName = 'id';
if ($tableName === 'reservations') {
    // Check if reservation_id exists
    $hasReservationId = false;
    $columnsResult = $conn->query("DESCRIBE reservations");
    while ($column = $columnsResult->fetch_assoc()) {
        if ($column['Field'] === 'reservation_id') {
            $hasReservationId = true;
            $idColumnName = 'reservation_id';
            break;
        }
    }
}

// Update the event status
$sql = "UPDATE $tableName SET status = '$status' WHERE $idColumnName = $id";
$result = $conn->query($sql);

if ($result) {
    echo json_encode([
        "success" => true,
        "message" => "Event status updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update event status: " . $conn->error
    ]);
}

$conn->close();
?>