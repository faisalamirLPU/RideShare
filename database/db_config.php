<?php
// database/db_config.php

$servername = "localhost";
$username = "root";  // Change this if you have a different DB user
$password = "";      // Set your database password
$dbname = "caps_rideshare";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
