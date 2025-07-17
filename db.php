<?php
$host = "localhost";
$user = "root";
$pass = ""; // Leave empty for XAMPP
$dbname = "nextntern_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
