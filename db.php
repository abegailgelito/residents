<?php
$host = 'localhost';
$db = 'fews';
$user = 'root'; // Default XAMPP user
$pass = '';     // Leave blank if no password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
