<?php
// create_test_data.php - Create test data for the events/reservations table
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

echo "<h1>Creating Test Data</h1>";

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

echo "<p>Connecting to database...</p>";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo "<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color:green'>Connected to database successfully</p>";

// Check which table exists
$reservationsTableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;
$eventsTableExists = $conn->query("SHOW TABLES LIKE 'events'")->num_rows > 0;

echo "<p>Reservations table exists: " . ($reservationsTableExists ? "Yes" : "No") . "</p>";
echo "<p>Events table exists: " . ($eventsTableExists ? "Yes" : "No") . "</p>";

// Create events table if it doesn't exist
if (!$eventsTableExists && !$reservationsTableExists) {
    echo "<p>Creating events table...</p>";
    
    $sql = "CREATE TABLE events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        time_start TIME,
        time_end TIME,
        place VARCHAR(255),
        status VARCHAR(50) DEFAULT 'approved',
        organizer VARCHAR(255)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>Events table created successfully</p>";
        $eventsTableExists = true;
    } else {
        echo "<p style='color:red'>Error creating events table: " . $conn->error . "</p>";
        exit;
    }
}

// Determine which table to use
$tableName = $reservationsTableExists ? 'reservations' : 'events';
echo "<p>Using table: " . $tableName . "</p>";

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
    echo "<p>Adding status column to $tableName table...</p>";
    
    $sql = "ALTER TABLE $tableName ADD COLUMN status VARCHAR(50) DEFAULT 'approved'";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>Status column added successfully</p>";
    } else {
        echo "<p style='color:red'>Error adding status column: " . $conn->error . "</p>";
    }
}

// Clear existing data
echo "<p>Clearing existing data from $tableName table...</p>";
$conn->query("DELETE FROM $tableName");

// Insert test data
echo "<p>Inserting test data...</p>";

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

// Check if we need to adapt field names for reservations table
$nameField = in_array('activity', $columns) ? 'activity' : 'name';
$dateField = in_array('date_from', $columns) ? 'date_from' : 'date';
$placeField = in_array('venue', $columns) ? 'venue' : 'place';
$organizerField = in_array('requestor_name', $columns) ? 'requestor_name' : 'organizer';

// Insert each test event
$successCount = 0;
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
    
    if ($conn->query($sql) === TRUE) {
        $successCount++;
    } else {
        echo "<p style='color:red'>Error inserting event: " . $conn->error . "</p>";
        echo "<p>SQL: " . $sql . "</p>";
    }
}

echo "<p style='color:green'>Successfully inserted $successCount test events</p>";

// Show the inserted data
echo "<h2>Inserted Data:</h2>";
$result = $conn->query("SELECT * FROM $tableName");

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    
    // Get column names
    $firstRow = $result->fetch_assoc();
    echo "<tr>";
    foreach (array_keys($firstRow) as $column) {
        echo "<th>" . $column . "</th>";
    }
    echo "</tr>";
    
    // Reset result pointer
    $result->data_seek(0);
    
    // Show data
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No records found in the table.</p>";
}

$conn->close();
echo "<p>Database connection closed.</p>";
echo "<p><a href='events.php'>Test Events API</a></p>";
?>