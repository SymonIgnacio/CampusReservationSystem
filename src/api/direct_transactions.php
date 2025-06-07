<?php
// Enable error display for debugging
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

    // Check if tables exist and create them if they don't
    $tables = ['approved_request', 'declined_request'];
    foreach ($tables as $table) {
        $tableCheck = $conn->query("SHOW TABLES LIKE '$table'");
        if ($tableCheck->num_rows == 0) {
            $createTable = "CREATE TABLE $table (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reference_number VARCHAR(255) NOT NULL,
                activity VARCHAR(255) NOT NULL,
                purpose TEXT,
                date_need_from DATE NOT NULL,
                date_need_until DATE NOT NULL,
                time_need_from TIME NOT NULL,
                time_need_until TIME NOT NULL,
                status VARCHAR(50) DEFAULT '" . ($table == 'approved_request' ? 'approved' : 'declined') . "',
                department VARCHAR(255),
                venue_name VARCHAR(255),
                venue_location VARCHAR(255),
                requestor_name VARCHAR(255),
                " . ($table == 'approved_request' ? 'approved_by' : 'declined_by') . " VARCHAR(255),
                " . ($table == 'approved_request' ? 'approved_at' : 'declined_at') . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if (!$conn->query($createTable)) {
                throw new Exception("Error creating $table: " . $conn->error);
            }
            
            // Add sample data
            if ($table == 'approved_request') {
                $sampleData = [
                    [
                        'reference_number' => 'REF-001',
                        'activity' => 'Department Meeting',
                        'purpose' => 'Monthly department meeting',
                        'date_need_from' => '2023-11-15',
                        'date_need_until' => '2023-11-15',
                        'time_need_from' => '10:00:00',
                        'time_need_until' => '12:00:00',
                        'department' => 'IT Department',
                        'venue_name' => 'Conference Room A',
                        'venue_location' => 'Main Building',
                        'requestor_name' => 'John Smith',
                        'approved_by' => 'Admin'
                    ],
                    [
                        'reference_number' => 'REF-002',
                        'activity' => 'Student Council Meeting',
                        'purpose' => 'Weekly planning session',
                        'date_need_from' => '2023-11-20',
                        'date_need_until' => '2023-11-20',
                        'time_need_from' => '14:00:00',
                        'time_need_until' => '16:00:00',
                        'department' => 'Student Affairs',
                        'venue_name' => 'Meeting Room 101',
                        'venue_location' => 'Student Center',
                        'requestor_name' => 'Jane Doe',
                        'approved_by' => 'Admin'
                    ]
                ];
                
                foreach ($sampleData as $data) {
                    $sql = "INSERT INTO approved_request 
                            (reference_number, activity, purpose, date_need_from, date_need_until, 
                             time_need_from, time_need_until, department, venue_name, venue_location, 
                             requestor_name, approved_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param(
                        "ssssssssssss", 
                        $data['reference_number'], 
                        $data['activity'], 
                        $data['purpose'], 
                        $data['date_need_from'], 
                        $data['date_need_until'], 
                        $data['time_need_from'], 
                        $data['time_need_until'], 
                        $data['department'], 
                        $data['venue_name'], 
                        $data['venue_location'], 
                        $data['requestor_name'], 
                        $data['approved_by']
                    );
                    
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                $sampleData = [
                    [
                        'reference_number' => 'REF-003',
                        'activity' => 'Faculty Workshop',
                        'purpose' => 'Training session',
                        'date_need_from' => '2023-11-25',
                        'date_need_until' => '2023-11-25',
                        'time_need_from' => '09:00:00',
                        'time_need_until' => '17:00:00',
                        'department' => 'Faculty Development',
                        'venue_name' => 'Auditorium',
                        'venue_location' => 'Main Building',
                        'requestor_name' => 'Robert Johnson',
                        'declined_by' => 'Admin'
                    ],
                    [
                        'reference_number' => 'REF-004',
                        'activity' => 'Club Social Event',
                        'purpose' => 'Social gathering',
                        'date_need_from' => '2023-11-30',
                        'date_need_until' => '2023-11-30',
                        'time_need_from' => '18:00:00',
                        'time_need_until' => '21:00:00',
                        'department' => 'Student Clubs',
                        'venue_name' => 'Student Lounge',
                        'venue_location' => 'Student Center',
                        'requestor_name' => 'Mary Wilson',
                        'declined_by' => 'Admin'
                    ]
                ];
                
                foreach ($sampleData as $data) {
                    $sql = "INSERT INTO declined_request 
                            (reference_number, activity, purpose, date_need_from, date_need_until, 
                             time_need_from, time_need_until, department, venue_name, venue_location, 
                             requestor_name, declined_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param(
                        "ssssssssssss", 
                        $data['reference_number'], 
                        $data['activity'], 
                        $data['purpose'], 
                        $data['date_need_from'], 
                        $data['date_need_until'], 
                        $data['time_need_from'], 
                        $data['time_need_until'], 
                        $data['department'], 
                        $data['venue_name'], 
                        $data['venue_location'], 
                        $data['requestor_name'], 
                        $data['declined_by']
                    );
                    
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    // Get approved requests
    $approvedSql = "SELECT 
                    'approved' as status_type,
                    id, 
                    reference_number, 
                    activity, 
                    purpose, 
                    date_need_from, 
                    date_need_until, 
                    time_need_from, 
                    time_need_until, 
                    status, 
                    approved_by as handled_by, 
                    approved_at as transaction_date, 
                    department, 
                    venue_name, 
                    venue_location, 
                    requestor_name
                FROM approved_request";
    
    $approvedResult = $conn->query($approvedSql);
    
    if (!$approvedResult) {
        throw new Exception("Query failed for approved requests: " . $conn->error);
    }
    
    $approvedTransactions = [];
    while ($row = $approvedResult->fetch_assoc()) {
        $approvedTransactions[] = $row;
    }

    // Get declined requests
    $declinedSql = "SELECT 
                    'declined' as status_type,
                    id, 
                    reference_number, 
                    activity, 
                    purpose, 
                    date_need_from, 
                    date_need_until, 
                    time_need_from, 
                    time_need_until, 
                    status, 
                    declined_by as handled_by, 
                    declined_at as transaction_date, 
                    department, 
                    venue_name, 
                    venue_location, 
                    requestor_name
                FROM declined_request";
    
    $declinedResult = $conn->query($declinedSql);
    
    if (!$declinedResult) {
        throw new Exception("Query failed for declined requests: " . $conn->error);
    }
    
    $declinedTransactions = [];
    while ($row = $declinedResult->fetch_assoc()) {
        $declinedTransactions[] = $row;
    }

    // Combine both results
    $allTransactions = array_merge($approvedTransactions, $declinedTransactions);
    
    // Sort by transaction date (newest first)
    usort($allTransactions, function($a, $b) {
        return strtotime($b['transaction_date']) - strtotime($a['transaction_date']);
    });

    // Return results
    echo json_encode([
        "success" => true,
        "transactions" => $allTransactions
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>