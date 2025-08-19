<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
require_once '../config/config.php';
session_start();

$username = $_POST['myusername'] ?? '';
$password = $_POST['mypassword'] ?? '';

$ip1 = $_SERVER['REMOTE_ADDR'] ?? '';
$ip2 = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';

$username = mysqli_real_escape_string($conn, stripslashes($username));
$password = mysqli_real_escape_string($conn, stripslashes($password));

$sql    = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 1) {
    $db_record = mysqli_fetch_assoc($result);
    $checked   = $db_record['checked'];

    if ($checked == 1) {
        $user_id  = $db_record['user_id'];
        $firstname = $db_record['firstname'];
        $am        = $db_record['am'];

        $sql2    = "SELECT sessionid FROM sessions ORDER BY sessionid DESC LIMIT 1";
        $result2 = mysqli_query($conn, $sql2);

        if (mysqli_num_rows($result2) == 0) {
            $sid = 1;
        } else {
            $row = mysqli_fetch_assoc($result2);
            $sid = $row['sessionid'] + 1;
        }

        
        $sql3 = "INSERT INTO sessions (sessionid, user_id, ipaddr_1, ipaddr_2, startdate, starttime) 
                 VALUES ('$sid', '$user_id', '$ip1', '$ip2', CURDATE(), CURTIME())";

        if (!mysqli_query($conn, $sql3)) {
            die("Error inserting session: " . mysqli_error($conn));
        }

        $_SESSION['mysid']   = $sid;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $firstname;
        $_SESSION['am'] = $am;

        if (!file_exists("img/user_{$user_id}_{$am}")) {
            mkdir("img/user_{$user_id}_{$am}", 0777, true);
        }

        // توجيه
        header("Location: start.php");
        exit;
    } else {
        echo "الحساب غير مفعل، برجاء مراجعة الإدارة.";
    }
} else {
    header("Location: main_login-2.php");
    exit;
}
?>
</head>
<body>
</body>
</html>
