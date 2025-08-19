<?php
require_once '../config/config.php'; // include DB connection

// Create mysqli connection (make sure config.php defines DB credentials correctly)
$conn = new mysqli("localhost", "root", "", "videoapp");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get video ID safely from GET
$videoid = isset($_GET['videoid']) ? intval($_GET['videoid']) : 0;

// Prepare data arrays
$data = array();
$data2 = array();

// Define a class to store quiz data
class quiz_store { 
    public $qid;
    public $start; 
    public $endl;
    public $Question;
    public $Answer1;
    public $Answer2;
    public $Answer3;
    public $Answer4;
    public $Answer5;
    public $Submitted;
} 

// Use prepared statement for safety
$stmt = $conn->prepare("SELECT * FROM video_quiz WHERE videoid = ? ORDER BY qid");
$stmt->bind_param("i", $videoid);
$stmt->execute();
$result = $stmt->get_result();

// Count number of rows
$num_rows = $result->num_rows;

// Loop through results
while ($row = $result->fetch_assoc()) {   
    $quizobj = new quiz_store();
    $quizobj->qid       = $row['qid'];
    $quizobj->start     = $row['start'];
    $quizobj->endl      = $row['end'];
    $quizobj->Question  = $row['question'];
    $quizobj->Answer1   = $row['answer1'];
    $quizobj->Answer2   = $row['answer2'];
    $quizobj->Answer3   = $row['answer3'];
    $quizobj->Answer4   = $row['answer4'];
    $quizobj->Answer5   = $row['answer5'];
    $quizobj->Submitted = 0; // default value

    array_push($data2, $quizobj);

    // Build final data array
    $data = array(
        "videoid"            => $videoid,
        "num_quiz_questions" => $num_rows,
        "quiz_questions"     => $data2
    );
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);

// Close DB connection
$stmt->close();
$conn->close();
?>
