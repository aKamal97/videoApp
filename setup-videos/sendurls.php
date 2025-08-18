<?php
require_once '../config/config.php';

$timestamps = json_decode($_POST['timestamps']); 
$num        = $_POST['num'];
$videoid    = $_POST['videoid'];

for ($i = 0; $i < $num; $i++) {
    $start       = $timestamps[$i]->start;
    $end         = $timestamps[$i]->end;
    $text        = $timestamps[$i]->comment;
    $isEmbedCode = $timestamps[$i]->isEmbedCode;

    // Get max urlid for this video
    $sql    = "SELECT MAX(urlid) AS maxid FROM video_url_code WHERE videoid='$videoid'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $row       = mysqli_fetch_assoc($result);
    $maxurlid  = ($row && $row['maxid'] !== null) ? $row['maxid'] : 0;
    $urlid     = $maxurlid + 1;

    // Insert new row
    $sql = "INSERT INTO video_url_code (urlid, videoid, start, end, text, isembedcode) 
            VALUES ('$urlid', '$videoid', '$start', '$end', '$text', '$isEmbedCode')";

    if (!mysqli_query($conn, $sql)) {
        die("Insert failed: " . mysqli_error($conn));
    }
}

echo "✅ Video URL codes saved successfully!";
?>