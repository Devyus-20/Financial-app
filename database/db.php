<?php
$servername = "localhost";
$database = "zakat";
$username = "root";
$password = "";

$koneksi = mysqli_connect($servername, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>

