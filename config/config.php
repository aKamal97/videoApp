
<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "videoapp";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set UTF8 encoding
mysqli_set_charset($conn, "utf8");
?>
