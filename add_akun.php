
<?php
include 'database/db.php';

// Periksa apakah data yang dibutuhkan sudah ada atau belum
if (isset($_POST['id'], $_POST['namaakun'], $_POST['pembayaran'], $_POST['kelompok'])) {
    // Periksa apakah semua input tidak kosong
    if (!empty($_POST['id']) && !empty($_POST['namaakun']) && !empty($_POST['pembayaran'] && !empty($_POST['kelompok']))) {
        $koneksi = mysqli_connect($servername, $username, $password, $database);

        // Periksa koneksi database
        if (!$koneksi) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Sanitasi data
        $id = mysqli_real_escape_string($koneksi, $_POST['id']);
        $namaakun = mysqli_real_escape_string($koneksi, $_POST['namaakun']);
        $pembayaran = mysqli_real_escape_string($koneksi, $_POST['pembayaran']);
        $kelompok = mysqli_real_escape_string($koneksi, $_POST['kelompok']);
        

        // Query insert data
        $sql = "INSERT INTO add_akun (id, nama_akun, pembayaran, kelompok) VALUES ('$id', '$namaakun', '$pembayaran', '$kelompok')";

        // Eksekusi query
        if (mysqli_query($koneksi, $sql)) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($koneksi);
        }        
        // Redirect ke halaman lain
        header("Location: add_admin.php");
        exit();
    } else {
        echo "Data tidak lengkap";
    }
} else {
    echo "Data tidak lengkap1";
}

?>
