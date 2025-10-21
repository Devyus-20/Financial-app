<?php
include 'database/db.php';  

if (isset($_POST['id'], $_POST['namaakun'], $_POST['pembayaran'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $namaakun = mysqli_real_escape_string($koneksi, $_POST['namaakun']);
    $pembayaran = mysqli_real_escape_string($koneksi, $_POST['pembayaran']);
    $kelompok = mysqli_real_escape_string($koneksi, $_POST['kelompok']);

    $koneksi = mysqli_connect($servername, $username, $password, $database);
    
    if (!$koneksi) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Update data berdasarkan ID
    $sql = "UPDATE add_akun SET nama_akun='$namaakun', pembayaran='$pembayaran', kelompok='$kelompok' WHERE id='$id'";

    if (mysqli_query($koneksi, $sql)) {
        header("Location: add_admin.php?message=Data updated successfully");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($koneksi);
    }

    mysqli_close($koneksi);
}
?>
