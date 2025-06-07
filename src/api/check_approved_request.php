<?php
// Disable error display in response
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

    // Check if approved_request table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'approved_request'");
    
    if ($tableCheck->num_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Table 'approved_request' exists",
            "exists" => true
        ]);
    } else {
        // Create the table if it doesn't exist
        $createTable = "CREATE TABLE approved_request (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reference_number VARCHAR(255) NOT NULL,
            event_name VARCHAR(255) NOT NULL,
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
        
        if ($conn->query($createTable)) {
            echo json_encode([
                "success" => true,
                "message" => "Table 'approved_request' created successfully",
                "exists" => false,
                "created" => true
            ]);
        } else {
            throw new Exception("Error creating table: " . $conn->error);
        }
    }

    // Insert sample data if table was just created
    if ($tableCheck->num_rows == 0) {
        $currentDate = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        
        $sampleData = [
            [
                'reference_number' => 'REF-001',
                'event_name' => 'Department Meeting',
                'purpose' => 'Monthly department meeting',
                'date_need_from' => $tomorrow,
                'date_need_until' => $tomorrow,
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
                'event_name' => 'Student Council Meeting',
                'purpose' => 'Weekly planning session',
                'date_need_from' => $nextWeek,
                'date_need_until' => $nextWeek,
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
                    (reference_number, event_name, purpose, date_need_from, date_need_until, 
                     time_need_from, time_need_until, department, venue_name, venue_location, 
                     requestor_name, approved_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssss", 
                $data['reference_number'], 
                $data['event_name'], 
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
    }

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>