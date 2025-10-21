<?php
// Tambahan untuk memeriksa struktur tabel
// Simpan ini sebagai file check_table.php dan jalankan untuk memeriksa struktur tabel buku_besar

require_once 'database/db.php';

// Fungsi untuk memeriksa struktur tabel
function checkTableStructure($koneksi, $tableName) {
    $query = "DESCRIBE $tableName";
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        echo "Error: " . mysqli_error($koneksi);
        return;
    }
    
    echo "<h3>Struktur Tabel $tableName:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Fungsi untuk memeriksa data dalam tabel
function checkTableData($koneksi, $tableName, $limit = 5) {
    $query = "SELECT * FROM $tableName LIMIT $limit";
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        echo "Error: " . mysqli_error($koneksi);
        return;
    }
    
    echo "<h3>Data Sample $tableName (limit $limit):</h3>";
    echo "<table border='1'>";
    
    // Header
    $first = true;
    $data = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
        
        if ($first) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            $first = false;
        }
    }
    
    // Data
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
}

// Periksa struktur tabel
echo "<h2>Pemeriksaan Struktur Database</h2>";
checkTableStructure($koneksi, "jurnal_transaksi");
checkTableStructure($koneksi, "buku_besar");

// Periksa data
echo "<h2>Pemeriksaan Data</h2>";
checkTableData($koneksi, "jurnal_transaksi");
checkTableData($koneksi, "buku_besar");

// Periksa transformasi data
echo "<h2>Pemeriksaan Transformasi Data</h2>";

$testQuery = "SELECT jt.id_jurnal, jt.kelompok_debit, jt.kelompok_kredit, 
              bb.id, bb.kelompok_debit as bb_kelompok_debit, bb.kelompok_kredit as bb_kelompok_kredit
              FROM jurnal_transaksi jt
              LEFT JOIN buku_besar bb ON jt.id_jurnal = bb.id_jurnal
              LIMIT 5";

$testResult = mysqli_query($koneksi, $testQuery);

if (!$testResult) {
    echo "Error: " . mysqli_error($koneksi);
} else {
    echo "<table border='1'>";
    echo "<tr>
            <th>ID Jurnal</th>
            <th>Kelompok Debit (Jurnal)</th>
            <th>Kelompok Kredit (Jurnal)</th>
            <th>ID (Buku Besar)</th>
            <th>Kelompok Debit (Buku Besar)</th>
            <th>Kelompok Kredit (Buku Besar)</th>
          </tr>";
          
    while ($row = mysqli_fetch_assoc($testResult)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id_jurnal']) . "</td>";
        echo "<td>" . htmlspecialchars($row['kelompok_debit']) . "</td>";
        echo "<td>" . htmlspecialchars($row['kelompok_kredit']) . "</td>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bb_kelompok_debit']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bb_kelompok_kredit']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}
?>