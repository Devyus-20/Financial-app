<?php
include 'database/db.php';  // Pastikan path ke db.php benar

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    
    $koneksi = mysqli_connect($servername, $username, $password, $database);
    
    if (!$koneksi) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Hapus data berdasarkan ID
    $sql = "DELETE FROM add_akun WHERE id='$id'";

    if (mysqli_query($koneksi, $sql)) {
        header("Location: add_admin.php?message=Data deleted successfully");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($koneksi);
    }

    mysqli_close($koneksi);
}
?>
