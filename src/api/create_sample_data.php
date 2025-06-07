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

    $results = [];

    // Check if approved_request table exists
    $approvedCheck = $conn->query("SHOW TABLES LIKE 'approved_request'");
    if ($approvedCheck->num_rows == 0) {
        // Create approved_request table
        $createApproved = "CREATE TABLE approved_request (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reference_number VARCHAR(255) NOT NULL,
            activity VARCHAR(255) NOT NULL,
            purpose TEXT,
            date_need_from DATE NOT NULL,
            date_need_until DATE NOT NULL,
            time_need_from TIME NOT NULL,
            time_need_until TIME NOT NULL,
            status VARCHAR(50) DEFAULT 'approved',
            department VARCHAR(255),
            venue_name VARCHAR(255),
            venue_location VARCHAR(255),
            requestor_name VARCHAR(255),
            approved_by VARCHAR(255),
            approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($createApproved)) {
            $results['approved_request'] = "Table created successfully";
        } else {
            $results['approved_request'] = "Error creating table: " . $conn->error;
        }
    } else {
        $results['approved_request'] = "Table already exists";
    }

    // Check if declined_request table exists
    $declinedCheck = $conn->query("SHOW TABLES LIKE 'declined_request'");
    if ($declinedCheck->num_rows == 0) {
        // Create declined_request table
        $createDeclined = "CREATE TABLE declined_request (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reference_number VARCHAR(255) NOT NULL,
            activity VARCHAR(255) NOT NULL,
            purpose TEXT,
            date_need_from DATE NOT NULL,
            date_need_until DATE NOT NULL,
            time_need_from TIME NOT NULL,
            time_need_until TIME NOT NULL,
            status VARCHAR(50) DEFAULT 'declined',
            department VARCHAR(255),
            venue_name VARCHAR(255),
            venue_location VARCHAR(255),
            requestor_name VARCHAR(255),
            declined_by VARCHAR(255),
            declined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($createDeclined)) {
            $results['declined_request'] = "Table created successfully";
        } else {
            $results['declined_request'] = "Error creating table: " . $conn->error;
        }
    } else {
        $results['declined_request'] = "Table already exists";
    }

    // Insert sample data if tables were just created
    $approvedCount = $conn->query("SELECT COUNT(*) as count FROM approved_request")->fetch_assoc()['count'];
    $declinedCount = $conn->query("SELECT COUNT(*) as count FROM declined_request")->fetch_assoc()['count'];

    if ($approvedCount == 0) {
        // Insert sample approved requests
        $sampleApproved = [
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
        
        $approvedInserted = 0;
        foreach ($sampleApproved as $data) {
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
            
            if ($stmt->execute()) {
                $approvedInserted++;
            }
            $stmt->close();
        }
        
        $results['approved_data'] = "Inserted $approvedInserted sample records";
    } else {
        $results['approved_data'] = "Data already exists ($approvedCount records)";
    }

    if ($declinedCount == 0) {
        // Insert sample declined requests
        $sampleDeclined = [
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
        
        $declinedInserted = 0;
        foreach ($sampleDeclined as $data) {
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
            
            if ($stmt->execute()) {
                $declinedInserted++;
            }
            $stmt->close();
        }
        
        $results['declined_data'] = "Inserted $declinedInserted sample records";
    } else {
        $results['declined_data'] = "Data already exists ($declinedCount records)";
    }

    // Return results
    echo json_encode([
        "success" => true,
        "results" => $results
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>