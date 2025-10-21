<?php
// File untuk generate ID jurnal otomatis via AJAX
require_once('database/init.php');

function generateIdJurnal($koneksi) {
    $query = "SELECT id_jurnal FROM jurnal_transaksi ORDER BY id_jurnal DESC LIMIT 1";
    $result = $koneksi->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['id_jurnal'];
        
        // Ekstrak nomor dari ID terakhir (misal: JRN001 -> 1)
        if (preg_match('/JRN(\d+)/', $lastId, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }
    } else {
        $number = 1;
    }
    
    // Format ID dengan padding 3 digit: JRN001, JRN002, dst
    return 'JRN' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Generate dan return ID jurnal baru
echo generateIdJurnal($koneksi);
?>