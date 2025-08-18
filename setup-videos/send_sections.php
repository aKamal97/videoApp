<?php
require_once '../config/config.php';

$timestamps = json_decode($_POST['timestamps']); 
$num        = $_POST['num'];
$videoid    = $_POST['videoid'];

 
$answers = array();

for ($i = 0; $i < $num; $i++) {
    $start = $timestamps[$i]->start;
    $end   = $timestamps[$i]->end;
    $title = $timestamps[$i]->Section;
	
    $maxsectionid = 0;

    // Get existing sections for this video
    $sql    = "SELECT * FROM video_sections WHERE videoid='$videoid'";
    $result = mysqli_query($conn, $sql);
	

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $num_rows = mysqli_num_rows($result);

    if ($num_rows == 0) {
        $maxsectionid = 0;
    } else {
        $sql    = "SELECT MAX(sectionid) AS maxsectionid FROM video_sections WHERE videoid='$videoid'";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Query failed: " . mysqli_error($conn));
        }

        $row          = mysqli_fetch_assoc($result);
        $maxsectionid = $row['maxsectionid'];
    }

    $sectionid = $maxsectionid + 1;

    $sql = "INSERT INTO video_sections (videoid, sectionid, title, start, end) 
            VALUES ('$videoid', '$sectionid', '$title', '$start', '$end')";

    if (!mysqli_query($conn, $sql)) {
        die("Insert failed: " . mysqli_error($conn));
    }
}
?>