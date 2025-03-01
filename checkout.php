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
$waktu_keluar = date('H:i:s');

// Cek apakah pegawai sudah melakukan check-in hari ini
$sql_check = "SELECT * FROM kehadiran WHERE id_pegawai = '$id_pegawai' AND tanggal = '$tanggal'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    // Jika sudah check-in, lakukan update waktu keluar
    $sql_checkout = "UPDATE kehadiran SET waktu_keluar = '$waktu_keluar' WHERE id_pegawai = '$id_pegawai' AND tanggal = '$tanggal'";

    if ($conn->query($sql_checkout) === TRUE) {
        echo "Check-out berhasil pada $waktu_keluar!";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Anda belum melakukan check-in hari ini.";
}
?>
