<?php
session_start();
require 'database/db.php';
require_once 'funct_bukubesar.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Function untuk mendapatkan total berdasarkan kelompok
function getTotalByKelompok($koneksi, $kelompok, $start_date = null, $end_date = null) {
    $sql = "SELECT 
                SUM(CASE WHEN kelompok_debit = ? THEN debit ELSE 0 END) as total_debit,
                SUM(CASE WHEN kelompok_kredit = ? THEN kredit ELSE 0 END) as total_kredit
            FROM buku_besar 
            WHERE 1=1";
    
    $params = [$kelompok, $kelompok];
    $types = "ss";
    
    if ($start_date && $end_date) {
        $sql .= " AND date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }
    
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    return ($row['total_debit'] ?? 0) + ($row['total_kredit'] ?? 0);
}

// Function untuk mendapatkan detail akun berdasarkan kelompok
function getAkunByKelompok($koneksi, $kelompok, $start_date = null, $end_date = null) {
    $sql = "SELECT 
                akun,
                perkiraan,
                SUM(CASE WHEN kelompok_debit = ? THEN debit ELSE 0 END) as total_debit,
                SUM(CASE WHEN kelompok_kredit = ? THEN kredit ELSE 0 END) as total_kredit
            FROM buku_besar 
            WHERE (kelompok_debit = ? OR kelompok_kredit = ?)";
    
    $params = [$kelompok, $kelompok, $kelompok, $kelompok];
    $types = "ssss";
    
    if ($start_date && $end_date) {
        $sql .= " AND date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }
    
    $sql .= " GROUP BY akun, perkiraan ORDER BY akun";
    
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $akun_list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $total = $row['total_debit'] + $row['total_kredit'];
        if ($total > 0) {
            $akun_list[] = [
                'akun' => $row['akun'],
                'perkiraan' => $row['perkiraan'],
                'total' => $total
            ];
        }
    }
    
    return $akun_list;
}

// Function untuk mendapatkan semua kelompok yang ada di database
function getAllKelompokNeraca($koneksi) {
    $sql = "SELECT DISTINCT 
                CASE 
                    WHEN kelompok_debit IS NOT NULL AND kelompok_debit != '' THEN kelompok_debit 
                    WHEN kelompok_kredit IS NOT NULL AND kelompok_kredit != '' THEN kelompok_kredit 
                END as kelompok
            FROM buku_besar 
            WHERE (kelompok_debit IS NOT NULL AND kelompok_debit != '') 
               OR (kelompok_kredit IS NOT NULL AND kelompok_kredit != '')";
    
    $result = mysqli_query($koneksi, $sql);
    $kelompok_list = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['kelompok']) {
            $kelompok_list[] = $row['kelompok'];
        }
    }
    
    return array_unique($kelompok_list);
}

// Function untuk menentukan apakah kelompok termasuk neraca atau laba rugi
function isKelompokNeraca($kelompok) {
    $kelompok_labarugi = ['PENERIMAAN', 'PENDAPATAN', 'BEBAN', 'BIAYA'];
    return !in_array(strtoupper($kelompok), $kelompok_labarugi);
}

// Function untuk mendapatkan semua kelompok neraca dengan detail akun
function getNeracaData($koneksi, $start_date = null, $end_date = null) {
    $all_kelompok = getAllKelompokNeraca($koneksi);
    $neraca_data = [];
    
    foreach ($all_kelompok as $kelompok) {
        if (isKelompokNeraca($kelompok)) {
            $total = getTotalByKelompok($koneksi, $kelompok, $start_date, $end_date);
            $akun_list = getAkunByKelompok($koneksi, $kelompok, $start_date, $end_date);
            
            if ($total > 0 || !empty($akun_list)) {
                $neraca_data[$kelompok] = [
                    'total' => $total,
                    'akun_list' => $akun_list
                ];
            }
        }
    }
    
    return $neraca_data;
}

// Get date range from URL parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Hitung Laba/Rugi
$penerimaan = getTotalByKelompok($koneksi, 'PENERIMAAN', $start_date, $end_date);
$beban = getTotalByKelompok($koneksi, 'BEBAN', $start_date, $end_date);
$laba_rugi = $penerimaan - $beban;

// Get data Neraca
$neraca_data = getNeracaData($koneksi, $start_date, $end_date);

// Hitung total Neraca
$total_neraca = 0;
foreach ($neraca_data as $kelompok => $data) {
    $total_neraca += $data['total'];
}

// Get detail data
$penerimaan_detail = getAkunByKelompok($koneksi, 'PENERIMAAN', $start_date, $end_date);
$beban_detail = getAkunByKelompok($koneksi, 'BEBAN', $start_date, $end_date);
?>

<html>
<head>
    <title>Laporan Keuangan - My Finance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .header-container h2 {
            margin: 0 10px;
            text-align: center;
        }
        .header-container img {
            height: 48px;
        }
        .periode {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 8px;
            font-weight: bold;
            border: 1px solid #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .bg-info {
            background-color: #d1ecf1;
        }
        .bg-success {
            background-color: #d4edda;
        }
        .bg-danger {
            background-color: #f8d7da;
        }
        .indent {
            padding-left: 20px;
        }
        .total-row {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }
        .summary-table {
            margin-top: 20px;
        }
        .summary-table td {
            padding: 8px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print();">

<div style="width:100%;margin:0 auto;">
    <!-- Header -->
    <div class="header-container">
        <h2>LAPORAN KEUANGAN<br>MY FINANCE</h2>
    </div>
    
    <div class="periode">
        Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?>
    </div>

    <!-- Laporan Laba Rugi -->
    <div class="section">
        <div class="section-title">LAPORAN LABA RUGI</div>
        
        <table>
            <thead>
                <tr>
                    <th width="60%">Keterangan</th>
                    <th width="40%">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Penerimaan -->
                <tr class="bg-success">
                    <td class="font-bold">PENERIMAAN</td>
                    <td class="text-right font-bold"><?= number_format($penerimaan, 0, ',', '.') ?></td>
                </tr>
                <?php if (!empty($penerimaan_detail)): ?>
                    <?php foreach ($penerimaan_detail as $akun): ?>
                    <tr>
                        <td class="indent"><?= htmlspecialchars($akun['akun']) ?> - <?= htmlspecialchars($akun['perkiraan']) ?></td>
                        <td class="text-right"><?= number_format($akun['total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="indent text-center" colspan="2">Tidak ada data penerimaan</td>
                    </tr>
                <?php endif; ?>
                
                <!-- Beban -->
                <tr class="bg-danger">
                    <td class="font-bold">BEBAN</td>
                    <td class="text-right font-bold"><?= number_format($beban, 0, ',', '.') ?></td>
                </tr>
                <?php if (!empty($beban_detail)): ?>
                    <?php foreach ($beban_detail as $akun): ?>
                    <tr>
                        <td class="indent"><?= htmlspecialchars($akun['akun']) ?> - <?= htmlspecialchars($akun['perkiraan']) ?></td>
                        <td class="text-right"><?= number_format($akun['total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="indent text-center" colspan="2">Tidak ada data beban</td>
                    </tr>
                <?php endif; ?>
                
                <!-- Total Laba/Rugi -->
                <tr class="total-row">
                    <td class="font-bold">
                        <?= $laba_rugi >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' ?>
                    </td>
                    <td class="text-right font-bold"><?= number_format(abs($laba_rugi), 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Laporan Neraca -->
    <div class="section">
        <div class="section-title">LAPORAN NERACA</div>
        
        <table>
            <thead>
                <tr>
                    <th width="60%">Kelompok / Akun</th>
                    <th width="40%">Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($neraca_data)): ?>
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data neraca yang tersedia</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($neraca_data as $kelompok => $data): ?>
                    <!-- Header Kelompok -->
                    <tr class="bg-info">
                        <td class="font-bold"><?= strtoupper(htmlspecialchars($kelompok)) ?></td>
                        <td class="text-right font-bold"><?= number_format($data['total'], 0, ',', '.') ?></td>
                    </tr>
                    
                    <!-- Detail Akun dalam Kelompok -->
                    <?php foreach ($data['akun_list'] as $akun): ?>
                    <tr>
                        <td class="indent">
                            <?= htmlspecialchars($akun['akun']) ?> - <?= htmlspecialchars($akun['perkiraan']) ?>
                        </td>
                        <td class="text-right"><?= number_format($akun['total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Total Neraca -->
                <tr class="total-row">
                    <td class="font-bold">TOTAL NERACA</td>
                    <td class="text-right font-bold"><?= number_format($total_neraca, 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Summary Kelompok -->
    <div class="section">
        <div class="section-title">RINGKASAN KELOMPOK AKUN</div>
        
        <table class="summary-table">
            <thead>
                <tr>
                    <th width="40%">Kelompok</th>
                    <th width="30%">Kategori</th>
                    <th width="30%">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $all_kelompok = getAllKelompokNeraca($koneksi);
                foreach ($all_kelompok as $kelompok):
                    $total_kelompok = getTotalByKelompok($koneksi, $kelompok, $start_date, $end_date);
                    if ($total_kelompok > 0):
                ?>
                <tr>
                    <td class="font-bold"><?= strtoupper(htmlspecialchars($kelompok)) ?></td>
                    <td class="text-center">
                        <span style="padding: 2px 8px; background-color: <?= isKelompokNeraca($kelompok) ? '#d4edda' : '#fff3cd' ?>; border-radius: 3px;">
                            <?= isKelompokNeraca($kelompok) ? 'Neraca' : 'Laba/Rugi' ?>
                        </span>
                    </td>
                    <td class="text-right"><?= number_format($total_kelompok, 0, ',', '.') ?></td>
                </tr>
                <?php 
                    endif;
                endforeach; 
                ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div style="margin-top: 40px; text-align: right;">
        <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
        <div style="margin-top: 60px;">
            <p>_________________________</p>
            <p>Penanggung Jawab</p>
        </div>
    </div>
</div>

</body>
</html>