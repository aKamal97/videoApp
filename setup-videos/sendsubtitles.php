<?php
require_once '../config/config.php';

$timestamps = json_decode($_POST['timestamps']); 
$num        = $_POST['num'];
$videoid    = $_POST['videoid'];

echo $num;

for ($i = 0; $i < $num; $i++) {
    $start = $timestamps[$i]->start;
    $end   = $timestamps[$i]->end;
    $text  = $timestamps[$i]->comment;

    // Get the max subtitleid for this video
    $sql    = "SELECT MAX(subtitleid) AS maxid FROM video_subtitles WHERE videoid='$videoid'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $row           = mysqli_fetch_assoc($result);
    $maxsubtitleid = ($row && $row['maxid'] !== null) ? $row['maxid'] : 0;

    $subtitleid = $maxsubtitleid + 1;

    // Insert new subtitle
    $sql = "INSERT INTO video_subtitles (subtitleid, videoid, start, end, text) 
            VALUES ('$subtitleid', '$videoid', '$start', '$end', '$text')";

    if (!mysqli_query($conn, $sql)) {
        die("Insert failed: " . mysqli_error($conn));
    }
}

echo "✅ Subtitles saved successfully!";
?>