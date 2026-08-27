<?php
$host = 'atlas-mysql';
$username = 'atlas_user';
$password = 'Fob123';
$dbname = 'atlas_ai';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
