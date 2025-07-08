<?php
require_once 'cors_fix.php';
header("Content-Type: application/json");

$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $events = [];
    $calendar = [];
    
    // Get approved events
    $approvedStmt = $conn->prepare("SELECT activity, date_need_from, start_time, end_time, venue, 'approved' as status FROM approved_request");
    $approvedStmt->execute();
    $approvedEvents = $approvedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get pending requests
    $pendingStmt = $conn->prepare("SELECT activity, date_need_from, start_time, end_time, venue, status FROM request WHERE status IN ('pending_gso', 'pending_vpo')");
    $pendingStmt->execute();
    $pendingEvents = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine all events
    $allEvents = array_merge($approvedEvents, $pendingEvents);
    
    // Group events by date
    foreach ($allEvents as $event) {
        $date = $event['date_need_from'];
        $time = date('h:i A', strtotime($event['start_time'])) . ' - ' . date('h:i A', strtotime($event['end_time']));
        
        $eventData = [
            'activity' => $event['activity'],
            'time' => $time,
            'venue' => $event['venue'],
            'status' => $event['status']
        ];
        
        if (!isset($calendar[$date])) {
            $calendar[$date] = [];
        }
        $calendar[$date][] = $eventData;
        $events[] = $eventData;
    }
    
    echo json_encode([
        "success" => true,
        "events" => $events,
        "calendar" => $calendar
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>