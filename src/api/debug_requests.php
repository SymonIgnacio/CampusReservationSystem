<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Direct CORS headers first
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Return debug information
echo json_encode([
    "success" => true,
    "requests" => [
        [
            "reservation_id" => 1,
            "name" => "Department Meeting",
            "date" => date("Y-m-d"),
            "time" => "10:00AM - 12:00PM",
            "venue" => "Conference Room A",
            "status" => "pending",
            "organizer" => "Department of IT"
        ],
        [
            "reservation_id" => 2,
            "name" => "Student Council Meeting",
            "date" => date("Y-m-d", strtotime("+1 day")),
            "time" => "2:00PM - 4:00PM",
            "venue" => "Meeting Room 101",
            "status" => "pending",
            "organizer" => "Student Council"
        ],
        [
            "reservation_id" => 3,
            "name" => "Faculty Workshop",
            "date" => date("Y-m-d", strtotime("+2 days")),
            "time" => "9:00AM - 3:00PM",
            "venue" => "Auditorium",
            "status" => "approved",
            "organizer" => "Faculty Development"
        ],
        [
            "reservation_id" => 4,
            "name" => "Graduation Ceremony",
            "date" => date("Y-m-d", strtotime("+5 days")),
            "time" => "1:00PM - 5:00PM",
            "venue" => "Main Hall",
            "status" => "approved",
            "organizer" => "Student Affairs"
        ],
        [
            "reservation_id" => 5,
            "name" => "Tech Conference",
            "date" => date("Y-m-d", strtotime("+10 days")),
            "time" => "8:00AM - 6:00PM",
            "venue" => "Main Auditorium",
            "status" => "declined",
            "organizer" => "IT Department"
        ]
    ]
]);
?>