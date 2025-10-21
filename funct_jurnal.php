<?php
// funct_jurnal.php

require 'database/db.php';
require 'funct_bukubesar.php';

function getJurnalById($koneksi, $id_jurnal) {
    $query = "SELECT * FROM jurnal_transaksi WHERE id_jurnal = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_jurnal);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getBukuBesarById($koneksi, $id_jurnal) {
    $query = "SELECT * FROM buku_besar WHERE id_jurnal = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_jurnal);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // DELETE
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $query = "DELETE FROM jurnal_transaksi WHERE id_jurnal = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("s", $delete_id);

        if ($stmt->execute()) {
            deletebuku_besar($koneksi, $delete_id);
            echo "Data berhasil dihapus!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        header("Location: jurnal_transaksi.php");
        exit();
    }

    // UPDATE
    if (isset($_POST['update'])) {
        $id_jurnal  = mysqli_real_escape_string($koneksi, $_POST['id_jurnal']);
        $tgl_trans  = mysqli_real_escape_string($koneksi, $_POST['tgl_trans']);
        $referensi  = mysqli_real_escape_string($koneksi, $_POST['referensi']);
        $deskripsi  = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
        $akun_debit = mysqli_real_escape_string($koneksi, $_POST['akun_debit']);
        $akun_kredit = mysqli_real_escape_string($koneksi, $_POST['akun_kredit']);
        $jumlah     = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
        $status     = mysqli_real_escape_string($koneksi, $_POST['status']);
        $kelompok_kredit = mysqli_real_escape_string($koneksi, $_POST['kelompok_kredit']);
        $kelompok_debit  = mysqli_real_escape_string($koneksi, $_POST['kelompok_debit']);

        $query_update_jurnal = "UPDATE jurnal_transaksi SET 
            tgl_trans = ?, 
            referensi = ?, 
            deskripsi = ?, 
            akun_debit = ?, 
            akun_kredit = ?, 
            jumlah = ?, 
            status = ?, 
            kelompok_kredit = ?, 
            kelompok_debit = ?
            WHERE id_jurnal = ?";

        $stmt = $koneksi->prepare($query_update_jurnal);
        $stmt->bind_param("sssssdssss", $tgl_trans, $referensi, $deskripsi, $akun_debit, $akun_kredit, $jumlah, $status, $kelompok_kredit, $kelompok_debit, $id_jurnal);

        if ($stmt->execute()) {
            deletebuku_besar($koneksi, $id_jurnal);
        
            if ($status === 'posting') {
                if (updatebuku_besar($koneksi, $id_jurnal)) {
                    echo "<script>alert('Data berhasil diperbarui'); window.location.href='jurnal_transaksi.php';</script>";
                } else {
                    echo "<script>alert('Data jurnal diperbarui, tapi gagal memperbarui buku besar'); window.location.href='jurnal_transaksi.php';</script>";
                }
            } else {
                echo "<script>alert('Data jurnal diperbarui dengan status pending'); window.location.href='jurnal_transaksi.php';</script>";
            }
        }
        

        $stmt->close();
    }

    // INSERT
    if (!isset($_POST['delete_id']) && !isset($_POST['update'])) {
        $id_jurnal = mysqli_real_escape_string($koneksi, $_POST['id_jurnal']);
        $tgl_trans = mysqli_real_escape_string($koneksi, $_POST['tgl_trans']);
        $referensi = mysqli_real_escape_string($koneksi, $_POST['referensi']);
        $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
        $akun_debit = mysqli_real_escape_string($koneksi, $_POST['akun_debit']);
        $akun_kredit = mysqli_real_escape_string($koneksi, $_POST['akun_kredit']);
        $jumlah = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
        $status = mysqli_real_escape_string($koneksi, $_POST['status']);
        $kelompok_kredit = mysqli_real_escape_string($koneksi, $_POST['kelompok_kredit']);
        $kelompok_debit = mysqli_real_escape_string($koneksi, $_POST['kelompok_debit']);

        $query = "INSERT INTO jurnal_transaksi 
            (id_jurnal, tgl_trans, referensi, deskripsi, akun_debit, akun_kredit, jumlah, status, kelompok_kredit, kelompok_debit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("ssssssdsss", $id_jurnal, $tgl_trans, $referensi, $deskripsi, $akun_debit, $akun_kredit, $jumlah, $status, $kelompok_kredit, $kelompok_debit);

        if ($stmt->execute()) {
            deletebuku_besar($koneksi, $id_jurnal);
        
            if ($status === 'posting') {
                if (updatebuku_besar($koneksi, $id_jurnal)) {
                    echo "<script>alert('Data berhasil ditambahkan'); window.location.href='jurnal_transaksi.php';</script>";
                } else {
                    echo "<script>alert('Data jurnal berhasil ditambahkan, tapi gagal menambahkan ke buku besar'); window.location.href='jurnal_transaksi.php';</script>";
                }
            } else {
                echo "<script>alert('Data jurnal berhasil ditambahkan dengan status pending'); window.location.href='jurnal_transaksi.php';</script>";
            }
        }
        
        
        $stmt->close();
        exit();
    }
}
?>
