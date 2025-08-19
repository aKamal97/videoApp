<?php
require_once '../config/config.php';
session_start();

// Check if session exists
if (!isset($_SESSION['mysid'])) {
    header("Location: main_login.php");
    exit();
}

// Check if videoid exists in GET
if (!isset($_GET['videoid'])) {
    header("Location: main_login.php");
    exit();
}

// Connect to database using MySQLi
$conn = new mysqli("localhost", "root", "", "videoapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sessionid = intval($_SESSION['mysid']);
$videoid   = intval($_GET['videoid']);

// Fetch video details
$stmt = $conn->prepare("SELECT * FROM videos WHERE videoid = ?");
$stmt->bind_param("i", $videoid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$videotitle = $row['videotitle'];
$videourl   = $row['videourl'];

// Fetch user id from sessions
$stmt = $conn->prepare("SELECT * FROM sessions WHERE sessionid = ?");
$stmt->bind_param("i", $sessionid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$userid = $row['user_id'];

// Prepare subtitles data
$data = array();
$data2 = array();

// Class for storing subtitles
class subtitles_store { 
    public $subtitleid;
    public $start; 
    public $endl;
    public $text;
}

// Fetch subtitles
$stmt = $conn->prepare("SELECT * FROM video_subtitles WHERE videoid = ? ORDER BY subtitleid");
$stmt->bind_param("i", $videoid);
$stmt->execute();
$result = $stmt->get_result();
$num_rows = $result->num_rows;

while ($row = $result->fetch_assoc()) {
    $subtitleobj = new subtitles_store();
    $subtitleobj->subtitleid = $row['subtitleid'];
    $subtitleobj->start      = $row['start'];
    $subtitleobj->endl       = $row['end'];
    $subtitleobj->text       = $row['text'];

    array_push($data2, $subtitleobj);

    $data = array(
        "videoid"      => $videoid,
        "numsubtitles" => $num_rows,
        "subtitles"    => $data2
    );
}

// Close DB connection
$conn->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <script src="../build/jquery.js"></script>	
    <script src="../build/mediaelement-and-player.min.js"></script>
    <script src="testforfiles.js"></script>
    <link rel="stylesheet" href="jquery-3.css" type="text/css" />
    <link rel="stylesheet" href="../build/mediaelementplayer.min.css" />
  
    <title><?php echo htmlspecialchars($videotitle); ?></title>
    <meta charset="utf-8" />
</head>
<body>

<div id="video">
    <h2>Video Title: <?php echo htmlspecialchars($videotitle); ?></h2><br>
 
    <video id="player1" width="500" height="380">
        <source src="<?php echo htmlspecialchars($videourl); ?>" type="video/youtube">
    </video>

    <span id="time"></span>
    <span id="percent"></span>

    <br><br>

    <div class="container">
        <span id="label"></span>
        <br><br>
        <br><span id="examplecomframe"></span>
    </div>
</div>

<script>
// Load subtitles data from PHP
var timestamps = <?php echo json_encode($data); ?>;

var last = 0, now, old;

// Function to format time
function showtime(ltime){
    var timetmp = ltime;
    var hrs = Math.floor(timetmp/3600);
    timetmp -= 3600 * hrs;
    var mins = Math.floor(timetmp/60);
    timetmp -= 60 * mins;
    var secs = Math.floor(timetmp);
    return (hrs < 10 ? "0" : "" ) + hrs + ":" 
         + (mins < 10 ? "0" : "" ) + mins + ":" 
         + (secs < 10 ? "0" : "" ) + secs;
}

// Function to show subtitles according to time
function showsection(t){
    var text = '';
    for (i = 0; i < timestamps.numsubtitles; i++) {
        if (t >= timestamps.subtitles[i].start && t <= timestamps.subtitles[i].endl) {
            text = timestamps.subtitles[i].text;
            document.getElementById('label').innerHTML = text;
        }
    }
    if (text == '') {
        document.getElementById('label').innerHTML = ' ';
    }
}

// MediaElement.js player init
$('video').mediaelementplayer({
    features: ['playpause','progress','current','duration','tracks','volume'],
    success: function(me, node, player) {
        old=0;
        now=0;

        me.addEventListener('timeupdate', function() {
            now = parseInt(me.currentTime);
            timetext = showtime(me.currentTime);
            document.getElementById('time').innerHTML = 'Time : ' + timetext;
            percent = parseInt(me.currentTime / me.duration * 100);
            document.getElementById('percent').innerHTML = 'percent : ' + percent;

            if (now != old) {
                old = now;
                showsection(now);
            }
        }, false);
    }
});
</script>
</body>
</html>
