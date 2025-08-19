<?php
require_once '../config/config.php'; // include DB connection

// Create a mysqli connection (make sure config.php has correct DB credentials)
$conn = new mysqli("localhost", "root", "", "videoapp");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect POST data safely
$sessionid          = isset($_POST['sessionid']) ? intval($_POST['sessionid']) : 0;
$vid                = isset($_POST['vid']) ? intval($_POST['vid']) : 0;
$eventtype          = isset($_POST['eventtype']) ? $conn->real_escape_string($_POST['eventtype']) : '';
$sectionid          = isset($_POST['sectionid']) ? intval($_POST['sectionid']) : 0;
$videotime          = isset($_POST['videotime']) ? $conn->real_escape_string($_POST['videotime']) : '';
$percent            = isset($_POST['percent']) ? floatval($_POST['percent']) : 0;
$content_table_bool = isset($_POST['content_table_bool']) ? intval($_POST['content_table_bool']) : 0;

// Prepare SQL query using prepared statements
$stmt = $conn->prepare("
    INSERT INTO session_events 
    (sessionid, vid, sectionid, eventtype, videotime, percent, content_table_bool, date, time) 
    VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), CURTIME())
");

// Bind parameters (i = int, d = double, s = string)
$stmt->bind_param("iiissdi", $sessionid, $vid, $sectionid, $eventtype, $videotime, $percent, $content_table_bool);

// Execute query
if ($stmt->execute()) {
    $log = "Inserted successfully: EventType=$eventtype, SectionID=$sectionid" . PHP_EOL;
} else {
    $log = "Error: " . $stmt->error . PHP_EOL;
}

// Save log to file (append instead of overwrite)
file_put_contents("test.txt", $log, FILE_APPEND | LOCK_EX);

// Close connections
$stmt->close();
$conn->close();
?>
