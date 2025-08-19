<?php
require_once '../config/config.php'; // Include DB connection

// Create a connection using mysqli (make sure config.php defines $conn properly)
$conn = new mysqli("localhost", "root", "", "videoapp");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debug log file
$file = 'results_2.txt';
// Example debug message
$res = 'hello there' . PHP_EOL;
file_put_contents($file, $res, FILE_APPEND | LOCK_EX);

// Collect POST data safely
$UserId        = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
$Exercise_Name = isset($_POST['exercisename']) ? $conn->real_escape_string($_POST['exercisename']) : '';
$Exercise_Id   = isset($_POST['exerciseid']) ? intval($_POST['exerciseid']) : 0;
$Score         = isset($_POST['score']) ? intval($_POST['score']) : 0;
$Start_Time    = isset($_POST['starttime']) ? $_POST['starttime'] : '';
$End_Time      = isset($_POST['endtime']) ? $_POST['endtime'] : '';
$All_Done      = isset($_POST['alldone']) ? intval($_POST['alldone']) : 0;
$vid           = isset($_POST['vid']) ? intval($_POST['vid']) : 0;
$sessionid     = isset($_POST['sessionid']) ? intval($_POST['sessionid']) : 0;

// Clean up date strings (remove commas)
$Start_Time = str_replace(",", "", $Start_Time);
$End_Time   = str_replace(",", "", $End_Time);

// Prepare SQL query securely using prepared statements
$stmt = $conn->prepare("
    INSERT INTO session_potatoes 
    (userid, sessionid, vid, exerciseid, exercisename, score, starttime, endtime, alldone, date, time) 
    VALUES (?, ?, ?, ?, ?, ?, STR_TO_DATE(?, '%d/%m/%Y %H:%i:%s'), STR_TO_DATE(?, '%d/%m/%Y %H:%i:%s'), ?, CURDATE(), CURTIME())
");

$stmt->bind_param("iiiisissi", 
    $UserId, $sessionid, $vid, $Exercise_Id, $Exercise_Name, $Score, $Start_Time, $End_Time, $All_Done
);

if ($stmt->execute()) {
    // Log query for debugging
    $log = "Query executed successfully for exercise: " . $Exercise_Name . PHP_EOL;
} else {
    $log = "Error: " . $stmt->error . PHP_EOL;
}

// Write query/result log to file
file_put_contents("results.txt", $log, FILE_APPEND | LOCK_EX);

// Close connection
$stmt->close();
$conn->close();
?>
