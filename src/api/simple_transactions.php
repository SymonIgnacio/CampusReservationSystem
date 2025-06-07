<?php
// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

try {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create tables if they don't exist
    $tables = ['approved_request', 'declined_request'];
    foreach ($tables as $table) {
        $tableCheck = $conn->query("SHOW TABLES LIKE '$table'");
        if ($tableCheck->num_rows == 0) {
            $createSql = "CREATE TABLE $table (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reference_number VARCHAR(255),
                activity VARCHAR(255),
                purpose TEXT,
                date_need_from DATE,
                date_need_until DATE,
                time_need_from TIME,
                time_need_until TIME,
                status VARCHAR(50) DEFAULT '" . ($table == 'approved_request' ? 'approved' : 'declined') . "',
                department VARCHAR(255),
                venue_name VARCHAR(255),
                venue_location VARCHAR(255),
                requestor_name VARCHAR(255),
                " . ($table == 'approved_request' ? 'approved_by' : 'declined_by') . " VARCHAR(255),
                " . ($table == 'approved_request' ? 'approved_at' : 'declined_at') . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if (!$conn->query($createSql)) {
                throw new Exception("Error creating $table: " . $conn->error);
            }
            
            // Add sample data
            if ($table == 'approved_request') {
                $conn->query("INSERT INTO approved_request (reference_number, activity, purpose, date_need_from, date_need_until, time_need_from, time_need_until, department, venue_name, venue_location, requestor_name, approved_by) 
                VALUES ('REF001', 'Department Meeting', 'Monthly planning', '2023-11-15', '2023-11-15', '09:00:00', '11:00:00', 'IT Department', 'Conference Room A', 'Main Building', 'John Smith', 'Admin')");
                
                $conn->query("INSERT INTO approved_request (reference_number, activity, purpose, date_need_from, date_need_until, time_need_from, time_need_until, department, venue_name, venue_location, requestor_name, approved_by) 
                VALUES ('REF002', 'Team Building', 'Annual event', '2023-11-20', '2023-11-21', '08:00:00', '17:00:00', 'HR Department', 'Function Hall', 'Annex Building', 'Jane Doe', 'Admin')");
            } else {
                $conn->query("INSERT INTO declined_request (reference_number, activity, purpose, date_need_from, date_need_until, time_need_from, time_need_until, department, venue_name, venue_location, requestor_name, declined_by) 
                VALUES ('REF003', 'Product Launch', 'New product introduction', '2023-11-25', '2023-11-25', '13:00:00', '16:00:00', 'Marketing', 'Auditorium', 'Main Building', 'Robert Johnson', 'Admin')");
                
                $conn->query("INSERT INTO declined_request (reference_number, activity, purpose, date_need_from, date_need_until, time_need_from, time_need_until, department, venue_name, venue_location, requestor_name, declined_by) 
                VALUES ('REF004', 'Training Session', 'Staff development', '2023-11-28', '2023-11-28', '09:00:00', '12:00:00', 'Training Department', 'Training Room B', 'Annex Building', 'Mary Williams', 'Admin')");
            }
        }
    }

    // Simple array of transactions
    $transactions = [
        [
            'type' => 'approved',
            'id' => 1,
            'reference_number' => 'REF001',
            'activity' => 'Department Meeting',
            'purpose' => 'Monthly planning',
            'date_need_from' => '2023-11-15',
            'date_need_until' => '2023-11-15',
            'time_need_from' => '09:00:00',
            'time_need_until' => '11:00:00',
            'department' => 'IT Department',
            'venue_name' => 'Conference Room A',
            'venue_location' => 'Main Building',
            'requestor_name' => 'John Smith'
        ],
        [
            'type' => 'approved',
            'id' => 2,
            'reference_number' => 'REF002',
            'activity' => 'Team Building',
            'purpose' => 'Annual event',
            'date_need_from' => '2023-11-20',
            'date_need_until' => '2023-11-21',
            'time_need_from' => '08:00:00',
            'time_need_until' => '17:00:00',
            'department' => 'HR Department',
            'venue_name' => 'Function Hall',
            'venue_location' => 'Annex Building',
            'requestor_name' => 'Jane Doe'
        ],
        [
            'type' => 'declined',
            'id' => 3,
            'reference_number' => 'REF003',
            'activity' => 'Product Launch',
            'purpose' => 'New product introduction',
            'date_need_from' => '2023-11-25',
            'date_need_until' => '2023-11-25',
            'time_need_from' => '13:00:00',
            'time_need_until' => '16:00:00',
            'department' => 'Marketing',
            'venue_name' => 'Auditorium',
            'venue_location' => 'Main Building',
            'requestor_name' => 'Robert Johnson'
        ],
        [
            'type' => 'declined',
            'id' => 4,
            'reference_number' => 'REF004',
            'activity' => 'Training Session',
            'purpose' => 'Staff development',
            'date_need_from' => '2023-11-28',
            'date_need_until' => '2023-11-28',
            'time_need_from' => '09:00:00',
            'time_need_until' => '12:00:00',
            'department' => 'Training Department',
            'venue_name' => 'Training Room B',
            'venue_location' => 'Annex Building',
            'requestor_name' => 'Mary Williams'
        ]
    ];

    // Return hardcoded data
    echo json_encode([
        "success" => true,
        "transactions" => $transactions
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>