<?php 
session_start();
if(!isset($_SESSION['mysid'])){
    header("location:main_login.php");
    exit();
}

require_once '../config/config.php'; // make sure it connects with mysqli
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Interactive Videos</title>
<script type="text/javascript">
// Begin
function fullScreen(theURL) {
    window.open(theURL, '', 'fullscreen=yes, scrollbars=yes');
}
// End
</script>
</head>

<body bgcolor="#FFFFFF" text="#FFFFFF" link="#FFFFFF">

<table width="92%" border="1" align="center" bgcolor="#0099FF">
  <tr>
    <td width="12%">--</td>
    <td width="62%">
      <b><font size="5">
        <div align="center">
          <h4>Interactive Video Examples</h4>
        </div>
      </font></b>
    </td>
    <td width="8%">--</td>
    <td width="11%">--</td>
    <td width="7%">--</td>
  </tr>
  <tr>
    <td>--</td>
    <td>--</td>
    <td>--</td>
    <td>--</td>
    <td>--</td>
  </tr>

<?php
// Fetch videos dynamically
$sql = "SELECT * FROM videos";
$result = mysqli_query($conn, $sql);

$counter = 1;
while($row = mysqli_fetch_assoc($result)) {
    $videoid   = $row['videoid'];
    $videotitle = htmlspecialchars($row['videotitle']);
    echo "<tr>";
    echo "<td align='center'>{$counter}</td>";
    echo "<td><strong>{$videotitle}</strong></td>";

    // Same design, but dynamic links
    echo "<td><font face='Arial, Helvetica, sans-serif'>
            <a href='display_video_questions_table_of_contents.php?videoid={$videoid}'>Quiz & TOC</a>
          </font></td>";

    echo "<td><font face='Arial, Helvetica, sans-serif'>
            <a href='video-subtitles.php?videoid={$videoid}'>Subtitles</a>
          </font></td>";

    echo "<td><font face='Arial, Helvetica, sans-serif'>
            <a href='display_video_urls.php?videoid={$videoid}'>URLs</a> | 
            <a href='display_video_hotpotatoes.php?videoid={$videoid}'>HotPotatoes</a>
          </font></td>";

    echo "</tr>";
    $counter++;
}
?>

</table>
<br><br>

</body>
</html>
