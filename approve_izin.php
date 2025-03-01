<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id_izin = $_GET['id'];

    $sql = "UPDATE izin SET status = 'Disetujui' WHERE id = '$id_izin'";

    if ($conn->query($sql) === TRUE) {
        echo "Izin disetujui!";
        header("Location: admin_dashboard.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
