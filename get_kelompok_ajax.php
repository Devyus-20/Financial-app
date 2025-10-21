<?php
// Ajax handler for getting kelompok by akun
require 'database/db.php';
require_once 'get_kelompok_by_akun.php';

// Check if the akun parameter is set
if (isset($_GET['akun'])) {
    $akun = $_GET['akun'];
    
    // Get kelompok data based on selected akun
    $kelompok = get_kelompok_by_akun($koneksi, $akun);
    
    // Return the kelompok as JSON
    header('Content-Type: application/json');
    echo json_encode(['kelompok' => $kelompok]);
} else {
    // Return error if akun parameter is not set
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing akun parameter']);
}
?>