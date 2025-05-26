<?php
// db.php - MySQLi connection for the 'laravel' database
$host = 'localhost';
$user = 'root'; // Change if your MySQL user is different
$pass = '';
$db = 'laravel';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
