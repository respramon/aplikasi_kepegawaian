<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id_izin = $_GET['id'];

    $sql = "UPDATE izin SET status = 'Ditolak' WHERE id = '$id_izin'";

    if ($conn->query($sql) === TRUE) {
        echo "Izin ditolak!";
        header("Location: admin_dashboard.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
