<?php
// events.php - Fetch events from the database

// Disable error display in output
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Function to return error response
function returnError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit;
}

try {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        returnError("Connection failed: " . $conn->connect_error, 500);
    }

    // Check if database exists
    $dbExists = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($dbExists->num_rows == 0) {
        // Create database if it doesn't exist
        if (!$conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
            returnError("Failed to create database: " . $conn->error, 500);
        }
        $conn->select_db($dbname);
    }

    // Check which table exists
    $reservationsTableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;
    $eventsTableExists = $conn->query("SHOW TABLES LIKE 'events'")->num_rows > 0;

    // If neither table exists, create one
    if (!$reservationsTableExists && !$eventsTableExists) {
        $createTableSQL = "CREATE TABLE events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            time_start TIME,
            time_end TIME,
            place VARCHAR(255),
            status VARCHAR(50) DEFAULT 'approved',
            organizer VARCHAR(255)
        )";
        
        if ($conn->query($createTableSQL)) {
            $eventsTableExists = true;
        } else {
            returnError("Failed to create events table: " . $conn->error, 500);
        }
    }

    $tableName = $reservationsTableExists ? 'reservations' : 'events';

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
            returnError("Failed to add status column: " . $conn->error, 500);
        }
    }

    // Check if there's any data in the table
    $countResult = $conn->query("SELECT COUNT(*) as count FROM $tableName");
    $countRow = $countResult->fetch_assoc();
    $recordCount = $countRow['count'];

    // If no data exists, create some sample data
    if ($recordCount == 0) {
        // Get current date
        $currentDate = date('Y-m-d');
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        $nextMonth = date('Y-m-d', strtotime('+30 days'));
        $lastWeek = date('Y-m-d', strtotime('-7 days'));
        
        // Create test events
        $testEvents = [
            [
                'name' => 'Department Meeting',
                'date' => $nextWeek,
                'time_start' => '10:00:00',
                'time_end' => '12:00:00',
                'place' => 'Conference Room A',
                'status' => 'approved',
                'organizer' => 'Department of IT'
            ],
            [
                'name' => 'Student Council Meeting',
                'date' => $nextWeek,
                'time_start' => '14:00:00',
                'time_end' => '16:00:00',
                'place' => 'Meeting Room 101',
                'status' => 'pending',
                'organizer' => 'Student Council'
            ],
            [
                'name' => 'Faculty Workshop',
                'date' => $nextMonth,
                'time_start' => '09:00:00',
                'time_end' => '15:00:00',
                'place' => 'Auditorium',
                'status' => 'approved',
                'organizer' => 'Faculty Development'
            ],
            [
                'name' => 'Past Event',
                'date' => $lastWeek,
                'time_start' => '13:00:00',
                'time_end' => '17:00:00',
                'place' => 'Main Hall',
                'status' => 'approved',
                'organizer' => 'Student Affairs'
            ],
            [
                'name' => 'Upcoming Conference',
                'date' => $nextMonth,
                'time_start' => '08:00:00',
                'time_end' => '18:00:00',
                'place' => 'Main Auditorium',
                'status' => 'approved',
                'organizer' => 'Academic Affairs'
            ]
        ];
        
        // Adapt field names based on the table structure
        $columnsResult = $conn->query("DESCRIBE $tableName");
        $columns = [];
        while ($column = $columnsResult->fetch_assoc()) {
            $columns[] = $column['Field'];
        }
        
        // Check if we need to adapt field names
        $nameField = in_array('activity', $columns) ? 'activity' : 'name';
        $dateField = in_array('date_from', $columns) ? 'date_from' : 'date';
        $placeField = in_array('venue', $columns) ? 'venue' : 'place';
        $organizerField = in_array('requestor_name', $columns) ? 'requestor_name' : 'organizer';
        
        // Insert each test event
        foreach ($testEvents as $event) {
            $fields = [];
            $values = [];
            
            // Map fields to the appropriate column names
            if (in_array($nameField, $columns)) {
                $fields[] = $nameField;
                $values[] = "'" . $conn->real_escape_string($event['name']) . "'";
            }
            
            if (in_array($dateField, $columns)) {
                $fields[] = $dateField;
                $values[] = "'" . $conn->real_escape_string($event['date']) . "'";
            }
            
            if (in_array('time_start', $columns)) {
                $fields[] = 'time_start';
                $values[] = "'" . $conn->real_escape_string($event['time_start']) . "'";
            }
            
            if (in_array('time_end', $columns)) {
                $fields[] = 'time_end';
                $values[] = "'" . $conn->real_escape_string($event['time_end']) . "'";
            }
            
            if (in_array($placeField, $columns)) {
                $fields[] = $placeField;
                $values[] = "'" . $conn->real_escape_string($event['place']) . "'";
            }
            
            if (in_array('status', $columns)) {
                $fields[] = 'status';
                $values[] = "'" . $conn->real_escape_string($event['status']) . "'";
            }
            
            if (in_array($organizerField, $columns)) {
                $fields[] = $organizerField;
                $values[] = "'" . $conn->real_escape_string($event['organizer']) . "'";
            }
            
            // Build and execute the SQL query
            $sql = "INSERT INTO $tableName (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";
            
            $conn->query($sql);
        }
    }

    // Get filter parameter if provided
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    // Prepare SQL query based on filter
    if (!empty($status) && $status !== 'all') {
        $sql = "SELECT * FROM $tableName WHERE status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $status);
    } else {
        $sql = "SELECT * FROM $tableName";
        $stmt = $conn->prepare($sql);
    }

    if (!$stmt) {
        returnError("Prepare statement failed: " . $conn->error, 500);
    }

    // Execute query
    if (!$stmt->execute()) {
        returnError("Query execution failed: " . $stmt->error, 500);
    }
    
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Add some basic formatting
        if (isset($row['date_from'])) {
            $row['date'] = $row['date_from'];
        }
        
        if (isset($row['time_start']) && isset($row['time_end'])) {
            $row['time'] = $row['time_start'] . ' - ' . $row['time_end'];
        }
        
        if (isset($row['activity'])) {
            $row['name'] = $row['activity'];
            $row['title'] = $row['activity'];
        }
        
        if (isset($row['venue'])) {
            $row['location'] = $row['venue'];
            $row['place'] = $row['venue'];
        }
        
        if (isset($row['requestor_name'])) {
            $row['organizer'] = $row['requestor_name'];
            $row['requestedBy'] = $row['requestor_name'];
        }
        
        // Ensure status field exists
        if (!isset($row['status'])) {
            // Default all events to 'approved' if status is not present
            $row['status'] = 'approved';
        }
        
        // Ensure ID field exists
        if (!isset($row['id']) && isset($row['reservation_id'])) {
            $row['id'] = $row['reservation_id'];
        } else if (!isset($row['id']) && isset($row['event_id'])) {
            $row['id'] = $row['event_id'];
        } else if (!isset($row['id'])) {
            // Generate a unique ID if none exists
            $row['id'] = uniqid();
        }
        
        $events[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "events" => $events,
        "count" => count($events),
        "table" => $tableName
    ]);

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    returnError("Server error: " . $e->getMessage(), 500);
}
?>
?>