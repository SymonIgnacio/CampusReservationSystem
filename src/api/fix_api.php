<?php
// fix_api.php - Fix API issues
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");


echo "<h1>API Fix Tool</h1>";

// Connect to DB
$host = "localhost";
$dbuser = "root";
$dbpass = "";

echo "<p>Connecting to MySQL...</p>";

$conn = new mysqli($host, $dbuser, $dbpass);

if ($conn->connect_error) {
    echo "<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color:green'>Connected to MySQL successfully</p>";

// Create database if it doesn't exist
$dbname = "campus_db";
echo "<p>Checking if database '$dbname' exists...</p>";

$result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
if ($result->num_rows == 0) {
    echo "<p>Database does not exist. Creating database...</p>";
    if ($conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
        echo "<p style='color:green'>Database created successfully</p>";
    } else {
        echo "<p style='color:red'>Error creating database: " . $conn->error . "</p>";
        exit;
    }
} else {
    echo "<p style='color:green'>Database already exists</p>";
}

// Select the database
$conn->select_db($dbname);

// Check and create users table
echo "<p>Checking users table...</p>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "<p>Users table does not exist. Creating table...</p>";
    
    $sql = "CREATE TABLE users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(255) NOT NULL,
        middlename VARCHAR(255),
        lastname VARCHAR(255) NOT NULL,
        department VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "<p style='color:green'>Users table created successfully</p>";
        
        // Create default admin user
        echo "<p>Creating default admin user...</p>";
        
        $adminFirstName = "Admin";
        $adminMiddleName = "";
        $adminLastName = "User";
        $adminDepartment = "IT Department";
        $adminEmail = "admin@example.com";
        $adminUsername = "admin";
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $adminRole = "admin";
        
        $sql = "INSERT INTO users (firstname, middlename, lastname, department, email, username, password, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $adminFirstName, 
            $adminMiddleName, 
            $adminLastName, 
            $adminDepartment, 
            $adminEmail, 
            $adminUsername, 
            $adminPassword, 
            $adminRole
        );
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>Default admin user created successfully</p>";
            echo "<p>Username: admin</p>";
            echo "<p>Password: admin123</p>";
        } else {
            echo "<p style='color:red'>Error creating admin user: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    } else {
        echo "<p style='color:red'>Error creating users table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>Users table already exists</p>";
}

// Check and create events table
echo "<p>Checking events table...</p>";
$result = $conn->query("SHOW TABLES LIKE 'events'");
if ($result->num_rows == 0) {
    echo "<p>Events table does not exist. Creating table...</p>";
    
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
    
    if ($conn->query($sql)) {
        echo "<p style='color:green'>Events table created successfully</p>";
        
        // Create sample events
        echo "<p>Creating sample events...</p>";
        
        // Get current date
        $currentDate = date('Y-m-d');
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        $nextMonth = date('Y-m-d', strtotime('+30 days'));
        $lastWeek = date('Y-m-d', strtotime('-7 days'));
        
        $sampleEvents = [
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
        
        $successCount = 0;
        foreach ($sampleEvents as $event) {
            $sql = "INSERT INTO events (name, date, time_start, time_end, place, status, organizer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", 
                $event['name'], 
                $event['date'], 
                $event['time_start'], 
                $event['time_end'], 
                $event['place'], 
                $event['status'], 
                $event['organizer']
            );
            
            if ($stmt->execute()) {
                $successCount++;
            } else {
                echo "<p style='color:red'>Error creating event: " . $stmt->error . "</p>";
            }
            
            $stmt->close();
        }
        
        echo "<p style='color:green'>Created $successCount sample events</p>";
    } else {
        echo "<p style='color:red'>Error creating events table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>Events table already exists</p>";
}

// Fix API files
echo "<h2>Fixing API Files</h2>";

// Fix events.php
echo "<p>Fixing events.php...</p>";
$eventsPhp = <<<'EOD'
<?php
// events.php - Fetch events from the database
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json");

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

// Get all events from the table
$sql = "SELECT * FROM events";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $conn->error
    ]);
    exit;
}

$events = [];
while ($row = $result->fetch_assoc()) {
    // Format time
    if (isset($row['time_start']) && isset($row['time_end'])) {
        $row['time'] = $row['time_start'] . ' - ' . $row['time_end'];
    }
    
    $events[] = $row;
}

// Return results
echo json_encode([
    "success" => true,
    "events" => $events
]);

$conn->close();
?>
EOD;

file_put_contents("events.php", $eventsPhp);
echo "<p style='color:green'>events.php fixed</p>";

// Fix get_users.php
echo "<p>Fixing get_users.php...</p>";
$getUsersPhp = <<<'EOD'
<?php
// get_users.php - Get a list of all users
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

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

// Get filter parameters if provided
$role = isset($_GET['role']) ? $_GET['role'] : '';
$excludeAdmin = isset($_GET['excludeAdmin']) ? filter_var($_GET['excludeAdmin'], FILTER_VALIDATE_BOOLEAN) : false;

// Prepare SQL query based on filter
if (!empty($role) && $excludeAdmin) {
    // Only show non-admin users with the specified role
    $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role = ? AND role != 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
} else if (!empty($role)) {
    // Show users with the specified role including admins
    $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
} else if ($excludeAdmin) {
    // Show all users except admins
    $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role != 'admin'";
    $stmt = $conn->prepare($sql);
} else {
    // Show all users including admins
    $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users";
    $stmt = $conn->prepare($sql);
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Return results
echo json_encode([
    "success" => true,
    "users" => $users,
    "count" => count($users)
]);

$stmt->close();
$conn->close();
?>
EOD;

file_put_contents("get_users.php", $getUsersPhp);
echo "<p style='color:green'>get_users.php fixed</p>";

// Fix requests.php
echo "<p>Fixing requests.php...</p>";
$requestsPhp = <<<'EOD'
<?php
// requests.php - Fetch pending event requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json");

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

// Get filter parameter if provided
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Prepare SQL query based on filter
if ($status === 'all') {
    $sql = "SELECT * FROM events";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM events WHERE status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    // Format time
    if (isset($row['time_start']) && isset($row['time_end'])) {
        $row['time'] = $row['time_start'] . ' - ' . $row['time_end'];
    }
    
    $requests[] = $row;
}

// Return results
echo json_encode([
    "success" => true,
    "requests" => $requests,
    "count" => count($requests)
]);

$stmt->close();
$conn->close();
?>
EOD;

file_put_contents("requests.php", $requestsPhp);
echo "<p style='color:green'>requests.php fixed</p>";

echo "<h2>All fixes applied successfully!</h2>";
echo "<p>Please try accessing your application again.</p>";
echo "<p><a href='debug_api.php'>Click here to check API responses</a></p>";

$conn->close();
?>