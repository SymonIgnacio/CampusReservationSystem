<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log errors to file
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/htdocs/CampusReservationSystem/php_errors.log');

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get request ID from URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid request ID");
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get the request data
    $query = "SELECT * FROM request WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Request not found");
    }
    
    $request = $result->fetch_assoc();
    $stmt->close();
    
    echo "<h2>Original Request Data:</h2>";
    echo "<pre>";
    print_r($request);
    echo "</pre>";
    
    // 2. Insert into approved_request table with explicit fields
    $approvedBy = "Admin";
    $status = "approved";
    $venue = $request['venue'];
    
    echo "<p>Venue value: <strong>" . htmlspecialchars($venue) . "</strong></p>";
    
    $insertQuery = "INSERT INTO approved_request (
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
        status,
        approved_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertQuery);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param(
        "sssssssssssiiisss", 
        $request['reference_number'],
        $request['request_by'],
        $request['department_organization'],
        $request['activity'],
        $request['purpose'],
        $request['nature_of_activity'],
        $request['date_need_from'],
        $request['date_need_until'],
        $request['start_time'],
        $request['end_time'],
        $request['participants'],
        $request['total_male_attendees'],
        $request['total_female_attendees'],
        $venue,
        $request['equipments_needed'],
        $status,
        $approvedBy
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $insertId = $conn->insert_id;
    $stmt->close();
    
    echo "<p style='color:green'>Successfully inserted into approved_request with ID: $insertId</p>";
    
    // 3. Verify the inserted data
    $query = "SELECT * FROM approved_request WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $insertId);
    $stmt->execute();
    $result = $stmt->get_result();
    $approvedRequest = $result->fetch_assoc();
    $stmt->close();
    
    echo "<h2>Inserted Approved Request Data:</h2>";
    echo "<pre>";
    print_r($approvedRequest);
    echo "</pre>";
    
    // Check if venue was properly transferred
    if ($approvedRequest['venue'] === $request['venue']) {
        echo "<p style='color:green'>SUCCESS: Venue was correctly transferred!</p>";
    } else {
        echo "<p style='color:red'>ERROR: Venue was not correctly transferred!</p>";
        echo "<p>Original venue: " . htmlspecialchars($request['venue']) . "</p>";
        echo "<p>Approved venue: " . htmlspecialchars($approvedRequest['venue']) . "</p>";
    }
    
    // 4. Delete from request table
    $query = "DELETE FROM request WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error deleting from request: " . $stmt->error);
    }
    $stmt->close();
    
    echo "<p style='color:green'>Successfully deleted original request</p>";
    
    // Commit transaction
    $conn->commit();
    
    echo "<p style='color:green'>Transaction committed successfully</p>";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>