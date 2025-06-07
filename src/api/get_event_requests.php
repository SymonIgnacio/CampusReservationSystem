<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first - must be before any output
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

    // Check if event_requests table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'event_requests'");
    if ($tableCheck->num_rows == 0) {
        // Create sample data for demonstration
        $sampleRequests = [
            [
                'referenceNumber' => 'A123456',
                'requestDate' => '2023-11-15',
                'requestorName' => 'John Doe',
                'department' => 'College of Computer Studies',
                'activity' => 'Tech Conference',
                'purpose' => 'Educational',
                'activityNature' => 'academic',
                'dateFrom' => '2023-12-10',
                'dateTo' => '2023-12-12',
                'timeStart' => '09:00',
                'timeEnd' => '17:00',
                'participants' => 'Students and Faculty',
                'malePax' => 45,
                'femalePax' => 35,
                'totalPax' => 80,
                'venueName' => 'Main Auditorium',
                'equipmentList' => [
                    ['name' => 'Projector', 'quantity' => 2],
                    ['name' => 'Microphone', 'quantity' => 4],
                    ['name' => 'Laptop', 'quantity' => 1]
                ],
                'status' => 'pending'
            ],
            [
                'referenceNumber' => 'B789012',
                'requestDate' => '2023-11-20',
                'requestorName' => 'Jane Smith',
                'department' => 'College of Arts And Science',
                'activity' => 'Art Exhibition',
                'purpose' => 'Cultural',
                'activityNature' => 'cultural',
                'dateFrom' => '2023-12-15',
                'dateTo' => '2023-12-15',
                'timeStart' => '13:00',
                'timeEnd' => '20:00',
                'participants' => 'Students and Public',
                'malePax' => 30,
                'femalePax' => 50,
                'totalPax' => 80,
                'venueName' => 'Exhibition Hall',
                'equipmentList' => [
                    ['name' => 'Display Panels', 'quantity' => 10],
                    ['name' => 'Spotlights', 'quantity' => 8]
                ],
                'status' => 'approved'
            ]
        ];

        echo json_encode([
            "success" => true,
            "message" => "Sample data returned (table doesn't exist yet)",
            "requests" => $sampleRequests
        ]);
    } else {
        // Fetch actual event requests from database
        $sql = "SELECT * FROM event_requests ORDER BY request_date DESC";
        $result = $conn->query($sql);

        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            // Format the data to match the expected structure
            $request = [
                'referenceNumber' => $row['reference_number'],
                'requestDate' => $row['request_date'],
                'requestorName' => $row['requestor_name'],
                'department' => $row['department'],
                'activity' => $row['activity_name'],
                'purpose' => $row['purpose'],
                'activityNature' => $row['activity_nature'],
                'otherNature' => $row['other_nature'] ?? '',
                'dateFrom' => $row['date_from'],
                'dateTo' => $row['date_to'],
                'timeStart' => $row['time_start'],
                'timeEnd' => $row['time_end'],
                'participants' => $row['participants_description'],
                'malePax' => $row['male_pax'],
                'femalePax' => $row['female_pax'],
                'totalPax' => $row['total_pax'],
                'venueName' => $row['venue_name'],
                'status' => $row['status']
            ];

            // Get equipment for this request
            $equipmentSql = "SELECT name, quantity FROM request_equipment WHERE request_id = ?";
            $stmt = $conn->prepare($equipmentSql);
            $stmt->bind_param("i", $row['request_id']);
            $stmt->execute();
            $equipmentResult = $stmt->get_result();
            
            $equipment = [];
            while ($eqRow = $equipmentResult->fetch_assoc()) {
                $equipment[] = [
                    'name' => $eqRow['name'],
                    'quantity' => $eqRow['quantity']
                ];
            }
            
            $request['equipmentList'] = $equipment;
            $requests[] = $request;
        }

        echo json_encode([
            "success" => true,
            "requests" => $requests
        ]);
    }

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in get_event_requests.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>