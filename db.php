<?php
$host = 'localhost';
$username = 'root';
$password = 'hombing';
$dbname = 'kepegawaian';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
