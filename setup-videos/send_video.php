<?php
require_once '../config/config.php';

$videoid                = $_POST['videoid'];
$videourl               = $_POST['videourl'];
$videotitle             = $_POST['videotitle'];
$video_sections_bool    = $_POST['video_sections_bool'];
$videolength            = $_POST['duration'];
$time_section_threshold = $_POST['time_sections_threshold'];

$videolength = round($videolength);

// ✅ Use prepared statement for safety
$stmt = $conn->prepare("INSERT INTO videos 
    (videoid, videotitle, videolength, videourl, video_sections_bool, time_section_threshold) 
    VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssisis", $videoid, $videotitle, $videolength, $videourl, $video_sections_bool, $time_section_threshold);

if (!$stmt->execute()) {
    die("Insert failed: " . $stmt->error);
}

// Debugging log
$myfile = fopen("C://sql.txt", "w") or die("Unable to open file!");
fwrite($myfile, $stmt->get_result());
fclose($myfile);

echo "✅ Video saved successfully!";
?>

<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8" />
    <script src="../build/jquery.js"></script>
    <script src="../build/mediaelement-and-player.min.js"></script>
    <script src="testforfiles.js"></script>

    <link rel="stylesheet" href="jquery-3.css" type="text/css" />
    <link rel="stylesheet" href="../build/mediaelementplayer.min.css" />

    <style>
    .style1,
    .style2 {
        font-size: 14px;
    }
    </style>

    <title>Graphic Design</title>
</head>

<body>
    <div id="video" style="float:left; width:50%;">
        <video id="player1" width="500" height="380">
            <source src="<?php echo htmlspecialchars($videourl); ?>" type="video/youtube">
        </video>

        <br>
        <input type="button" id="button1" value="Preview" />
        <input type="button" id="button2" value="Store in DB" />
        <span id="time"></span>
        <br><br>

        <div class="container1">
            <span id="label"></span>
        </div>

        <br><br>
        <br><span id="examplecomframe"></span>
    </div>

    <div id="respond" style="float:right; width:50%;">
        <h3>Set Sections here. Use the video to check the exact time when the section starts</h3>
        <form name="commentform">
            <label for="FROM" class="required">TimePoint</label><br>
            <input type="number" name="from" id="from" required><br>
            Section Title <input type="text" size="80" name="Section" id="Section"><br><br>
            <input name="Submit" type="button" value="Submit" onClick="JavaScript:handleSubmit()">
        </form>

        <div>Sections here: <span id="commentSection"></span></div>
    </div>

    <script>
    var count = 0;
    var timestamps = [];
    var all = 0;
    var gvideoid = <?php echo json_encode($videoid); ?>;
    var gme;
    var gi;
    var gSection = "";

    $('#commentSection').html('.....<br>');

    function getComments() {
        timestamps = [];
        $('article').each(function() {
            if ($(this).attr('data-start')) {
                var lcommenttext = $(this).text();
                timestamps.push({
                    start: +$(this).attr('data-start'),
                    Section: lcommenttext
                });
            }
        });
        all = timestamps.length;
    }

    function storeComments() {
        $.post("send_sections.php", {
            timestamps: JSON.stringify(timestamps),
            num: all,
            videoid: gvideoid
        }, function(data) {
            alert("Server response: " + data);
        });
    }

    function createComment(data) {
        count++;
        var html = '' +
            '<div><article id="' + count + '" data-start="' + data.from + '">' +
            '<br>Section:<span id="dataQuestion">' + data.Section + ' [Q1] </span>' +
            '</article> <a href="#" class="remove_field">Remove</a></div>';
        return html;
    }

    function displayComment(data) {
        $('#commentSection').append(createComment(data));
        $('#from').val('');
        $('#Section').val('');
    }

    function handleSubmit() {
        var data = {
            "from": $('#from').val(),
            "Section": $('#Section').val(),
        };

        if (data.from === '' || data.Section === '') {
            alert('Fill in the fields');
            return false;
        }
        displayComment(data);
        return false;
    }

    // Remove section event (bind once, not inside showsection)
    $('#commentSection').on("click", ".remove_field", function(e) {
        e.preventDefault();
        $(this).parent('div').remove();
    });

    function showsection(t) {
        for (var i = 0; i < all; i++) {
            if (t >= timestamps[i].start && t <= timestamps[i].end) {
                var Section = timestamps[i].Section;
                if (Section !== gSection) {
                    document.getElementById('label').innerHTML = Section;
                    gSection = Section;
                }
            }
        }
    }

    // Button handlers
    document.getElementById('button1').onclick = getComments;
    document.getElementById('button2').onclick = storeComments;

    // MediaElement init
    $('video').mediaelementplayer({
        features: ['playpause', 'progress', 'current', 'duration', 'tracks', 'volume'],
        success: function(me) {
            gme = me;
            me.addEventListener('timeupdate', function() {
                document.getElementById('time').innerHTML = 'Time : ' + me.currentTime.toFixed(2);
                showsection(parseInt(me.currentTime));
            }, false);
        }
    });
    </script>
</body>

</html>