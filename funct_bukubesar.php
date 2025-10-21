<?php
// funct_bukubesar.php

require 'database/db.php';

// Fungsi untuk mengambil data buku besar dengan status "posting" dan menampilkan nama akun
function get_buku_besar_posting($koneksi) {
    $query = "
        SELECT 
            bb.*, 
            a1.nama_akun AS nama_perkiraan,
            CONCAT(bb.akun_perkiraan, ' - ', a2.nama_akun) AS akun_perkiraan_lengkap
        FROM buku_besar bb
        LEFT JOIN add_akun a1 ON bb.perkiraan = a1.id_akun
        LEFT JOIN add_akun a2 ON bb.akun_perkiraan = a2.id_akun
        WHERE bb.status = 'posting'
        ORDER BY bb.date DESC
    ";

    $result = mysqli_query($koneksi, $query);
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}


// Fungsi untuk mengambil semua data buku besar
// Fungsi untuk mengambil data buku besar dengan status "posting"
function get_buku_besar($koneksi) {
    $query = "SELECT * FROM buku_besar WHERE status = 'posting' ORDER BY date DESC";
    $result = mysqli_query($koneksi, $query);
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}


// Fungsi untuk menghapus entri buku besar berdasarkan id_jurnal
function deletebuku_besar($koneksi, $id_jurnal) {
    $query = "DELETE FROM buku_besar WHERE id_jurnal = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_jurnal);
    return $stmt->execute();
}

function updatebuku_besar($koneksi, $id_jurnal) {
    // Ambil data jurnal berdasarkan id
    $query = "SELECT * FROM jurnal_transaksi WHERE id_jurnal = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_jurnal);
    $stmt->execute();
    $result = $stmt->get_result();
    $jurnal = $result->fetch_assoc();

    if (!$jurnal || $jurnal['status'] !== 'posting') {
        return false; // Jangan lanjutkan kalau status bukan 'posting'
    }

    $tgl = date('Y-m-d', strtotime($jurnal['tgl_trans']));
    $deskripsi = $jurnal['deskripsi'];
    $jumlah = $jurnal['jumlah'];
    $status = $jurnal['status'];
    $kelompok_debit = $jurnal['kelompok_debit'];
    $kelompok_kredit = $jurnal['kelompok_kredit'];

    $query_insert = "INSERT INTO buku_besar 
        (id_jurnal, date, notes, tipe, akun, perkiraan, akun_perkiraan, debit, kredit, nilai, status, kelompok_debit, kelompok_kredit)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $koneksi->prepare($query_insert);

    // Debit entry
    $tipe = "Debit";
    $akun = $jurnal['akun_debit'];
    $perkiraan = preg_replace('/^\d+\s*-\s*/', '', $jurnal['akun_debit']); // hanya nama akun
    $akun_perkiraan = $jurnal['akun_debit']; // ID - Nama lengkap
    $debit = $jumlah;
    $kredit = 0;
    $nilai = $jumlah;

    $stmt_insert->bind_param(
        "issssssiiisss", 
        $id_jurnal, $tgl, $deskripsi, $tipe, $akun, $perkiraan, $akun_perkiraan,
        $debit, $kredit, $nilai, $status, $kelompok_debit, $kelompok_kredit
    );
    $stmt_insert->execute();

    // Kredit entry
    $tipe = "Kredit";
    $akun = $jurnal['akun_kredit'];
    $perkiraan = preg_replace('/^\d+\s*-\s*/', '', $jurnal['akun_kredit']); // hanya nama akun
    $akun_perkiraan = $jurnal['akun_kredit']; // ID - Nama lengkap
    $debit = 0;
    $kredit = $jumlah;
    $nilai = $jumlah;

    $stmt_insert->bind_param(
        "issssssiiisss", 
        $id_jurnal, $tgl, $deskripsi, $tipe, $akun, $perkiraan, $akun_perkiraan,
        $debit, $kredit, $nilai, $status, $kelompok_debit, $kelompok_kredit
    );
    return $stmt_insert->execute();
}

?>
