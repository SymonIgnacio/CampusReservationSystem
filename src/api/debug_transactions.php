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

    // Check if tables exist
    $tables = ['approved_request', 'declined_request'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        $tableCheck = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $tableCheck->num_rows > 0;
        
        $tableStatus[$table] = [
            'exists' => $exists
        ];
        
        if (!$exists) {
            // Create table
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
            
            if ($conn->query($createSql)) {
                $tableStatus[$table]['created'] = true;
                
                // Add sample data
                if ($table == 'approved_request') {
                    $sampleData = [
                        [
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
                            'requestor_name' => 'John Smith',
                            'approved_by' => 'Admin'
                        ],
                        [
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
                            'requestor_name' => 'Robert Johnson',
                            'declined_by' => 'Admin'
                        ],
                        [
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
                            'requestor_name' => 'Mary Williams',
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
            } else {
                $tableStatus[$table]['error'] = $conn->error;
            }
        } else {
            // Count records
            $countQuery = $conn->query("SELECT COUNT(*) as count FROM $table");
            $tableStatus[$table]['count'] = $countQuery->fetch_assoc()['count'];
        }
    }

    // Return debug info
    echo json_encode([
        "success" => true,
        "tables" => $tableStatus,
        "message" => "Debug information retrieved successfully"
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>