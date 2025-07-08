<?php
// Direct CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if user already exists in database
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE firebase_uid = ? OR email = ?");
    $stmt->bind_param("ss", $data['firebaseUid'], $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, just return success
        echo json_encode([
            "success" => true,
            "message" => "Registration successful. Please verify your email."
        ]);
        $conn->close();
        exit;
    }

    // Insert user with Firebase UID
    $stmt = $conn->prepare("INSERT INTO users (username, email, firstname, lastname, department, role, firebase_uid) VALUES (?, ?, ?, ?, ?, 'user', ?)");
    $stmt->bind_param("ssssss", $data['username'], $data['email'], $data['firstName'], $data['lastName'], $data['department'], $data['firebaseUid']);
    
    if (!$stmt->execute()) {
        throw new Exception("Registration failed: " . $stmt->error);
    }

    echo json_encode([
        "success" => true,
        "message" => "Registration successful. Please verify your email."
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}