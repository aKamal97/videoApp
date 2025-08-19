<?php
require_once '../config/config.php'; // Include DB connection

// Create a mysqli connection (make sure config.php defines proper connection)
$conn = new mysqli("localhost", "root", "", "videoapp");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect POST data safely
$sessionid   = isset($_POST['sessionid']) ? intval($_POST['sessionid']) : 0;
$vid         = isset($_POST['vid']) ? intval($_POST['vid']) : 0;
$userid      = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
$questionid  = isset($_POST['questionid']) ? intval($_POST['questionid']) : 0;
$answerInt   = isset($_POST['answerInt']) ? intval($_POST['answerInt']) : 0;
$answerText  = isset($_POST['answerText']) ? $conn->real_escape_string($_POST['answerText']) : '';
$questionType= isset($_POST['questionType']) ? $conn->real_escape_string($_POST['questionType']) : '';

// Prepare SQL query using prepared statements (safer against SQL injection)
$stmt = $conn->prepare("
    INSERT INTO session_video_quiz 
    (sessionid, vid, userid, questionid, answerInt, answerText, date, time) 
    VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURTIME())
");

$stmt->bind_param("iiiiis", $sessionid, $vid, $userid, $questionid, $answerInt, $answerText);

// Execute the statement
if ($stmt->execute()) {
    $log = "Query executed successfully for question ID: $questionid" . PHP_EOL;
} else {
    $log = "Error: " . $stmt->error . PHP_EOL;
}

// Log query/result to file (append mode instead of overwrite)
file_put_contents("test2.txt", $log, FILE_APPEND | LOCK_EX);

// Close connection
$stmt->close();
$conn->close();
?>
