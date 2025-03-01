<?php
session_start();
include 'db.php';

$id_pegawai = $_SESSION['id_pegawai'];
$sql = "SELECT * FROM kehadiran WHERE id_pegawai = '$id_pegawai' ORDER BY tanggal DESC";
$result = $conn->query($sql);

echo "<table border='1'>
        <tr>
            <th>Tanggal</th>
            <th>Status Kehadiran</th>
            <th>Waktu Masuk</th>
            <th>Waktu Keluar</th>
        </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $row['tanggal'] . "</td>
            <td>" . $row['status_izin'] . "</td>
            <td>" . $row['waktu_masuk'] . "</td>
            <td>" . $row['waktu_keluar'] . "</td>
          </tr>";
}

echo "</table>";
?>
