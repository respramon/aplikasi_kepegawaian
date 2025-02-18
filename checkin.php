<?php
session_start();
include 'db.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('Asia/Jakarta'); // Waktu Indonesia Barat (WIB)
// date_default_timezone_set('Asia/Makassar'); // Waktu Indonesia Tengah (WITA)
// date_default_timezone_set('Asia/Jayapura'); // Waktu Indonesia Timur (WIT)

$id_pegawai = $_SESSION['id_pegawai'];
$tanggal = date('Y-m-d');
$waktu_masuk = date('H:i:s');

// Cek apakah pegawai sudah melakukan check-in hari ini
$sql_check = "SELECT * FROM kehadiran WHERE id_pegawai = '$id_pegawai' AND tanggal = '$tanggal'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows == 0) {
    // Jika belum check-in, insert data kehadiran
    $sql_checkin = "INSERT INTO kehadiran (id_pegawai, tanggal, waktu_masuk) VALUES ('$id_pegawai', '$tanggal', '$waktu_masuk')";

    if ($conn->query($sql_checkin) === TRUE) {
        echo "Check-in berhasil pada $waktu_masuk!";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Anda sudah melakukan check-in hari ini.";
}
?>
