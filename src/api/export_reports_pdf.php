<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get data from either POST body or GET parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
} else {
    $data = isset($_GET['data']) ? json_decode(urldecode($_GET['data']), true) : [];
}

// Set content type for PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="event_reports.pdf"');

// Create a simple text-based PDF (for demonstration)
$pdf = "Event Reports Summary\n\n";
$pdf .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
$pdf .= "Total Events: " . ($data['totalEvents'] ?? 0) . "\n";
$pdf .= "Approved Events: " . ($data['approvedEvents'] ?? 0) . "\n";
$pdf .= "Pending Events: " . ($data['pendingEvents'] ?? 0) . "\n";
$pdf .= "Declined Events: " . ($data['declinedEvents'] ?? 0) . "\n";
$pdf .= "Upcoming Events: " . ($data['upcomingEvents'] ?? 0) . "\n";
$pdf .= "Most Used Venue: " . ($data['mostUsedVenue'] ?? 'N/A') . "\n";
$pdf .= "Most Active Month: " . ($data['mostActiveMonth'] ?? 'N/A') . "\n";

// Output the PDF content
echo $pdf;
?>