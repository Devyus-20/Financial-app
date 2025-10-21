<?php
session_start();
require 'database/db.php';
require_once 'funct_bukubesar.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Get filter parameters from URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$akun_filter = isset($_GET['akun']) ? $_GET['akun'] : '';
$tipe_filter = isset($_GET['tipe']) ? $_GET['tipe'] : '';
$kelompok_filter = isset($_GET['kelompok']) ? $_GET['kelompok'] : '';

// Build filter array
$filter = [];
if ($start_date && $end_date) {
    $filter['date_range'] = ['start' => $start_date, 'end' => $end_date];
}
if ($akun_filter) {
    $filter['akun'] = $akun_filter;
}
if ($tipe_filter) {
    $filter['tipe'] = $tipe_filter;
}
if ($kelompok_filter) {
    $filter['kelompok'] = $kelompok_filter;
}

$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'date';
$orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'ASC';

// Get data using the function from funct_bukubesar.php
$result_data = get_buku_besar($koneksi, $filter, $orderBy, $orderDir);

// Calculate totals
$total_debit = 0;
$total_kredit = 0;
$total_records = count($result_data);

foreach ($result_data as $row) {
    $total_debit += $row['debit'];
    $total_kredit += $row['kredit'];
}

// Build filter description for display
$filter_desc = [];
if ($start_date && $end_date) {
    $filter_desc[] = "Periode: " . date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
}
if ($akun_filter) {
    $filter_desc[] = "Akun: " . htmlspecialchars($akun_filter);
}
if ($tipe_filter) {
    $filter_desc[] = "Tipe: " . htmlspecialchars($tipe_filter);
}
if ($kelompok_filter) {
    $filter_desc[] = "Kelompok: " . htmlspecialchars($kelompok_filter);
}

$filter_text = !empty($filter_desc) ? implode(" | ", $filter_desc) : "Semua Data";
?>

<html>
<head>
    <title>Laporan Jurnal - My Finance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 15px;
            line-height: 1.3;
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
        .filter-info {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        .summary-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            background-color: #e9ecef;
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        .summary-info div {
            text-align: center;
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
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
        .total-row {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }
        .even-row {
            background-color: #f8f9fa;
        }
        .debit-col {
            background-color: #d4edda;
        }
        .kredit-col {
            background-color: #f8d7da;
        }
        .footer-info {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: end;
        }
        .signature-area {
            text-align: center;
            margin-top: 40px;
        }
        @media print {
            body { 
                margin: 0; 
                font-size: 8px;
            }
            table {
                font-size: 7px;
            }
            th {
                font-size: 7px;
            }
            .no-print { 
                display: none; 
            }
        }
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
    </style>
</head>
<body onload="window.print();">

<div style="width:100%;margin:0 auto;">
    <!-- Header -->
    <div class="header-container">
        <h2>LAPORAN JURNAL<br>MY FINANCE</h2>
    </div>
    
    <!-- Filter Information -->
    <div class="filter-info">
        Filter: <?= $filter_text ?>
    </div>

    <!-- Summary Information -->
    <div class="summary-info">
        <div>
            <strong>Total Records:</strong><br>
            <?= number_format($total_records, 0, ',', '.') ?> transaksi
        </div>
        <div>
            <strong>Total Debit:</strong><br>
            Rp <?= number_format($total_debit, 2, ',', '.') ?>
        </div>
        <div>
            <strong>Total Kredit:</strong><br>
            Rp <?= number_format($total_kredit, 2, ',', '.') ?>
        </div>
        <div>
            <strong>Selisih:</strong><br>
            Rp <?= number_format(abs($total_debit - $total_kredit), 2, ',', '.') ?>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="4%">ID</th>
                <th width="5%">ID Jurnal</th>
                <th width="7%">Tanggal</th>
                <th width="12%">Catatan</th>
                <th width="6%">Tipe</th>
                <th width="8%">Akun</th>
                <th width="12%">Perkiraan</th>
                <th width="10%">Akun Perkiraan</th>
                <th width="8%" class="debit-col">Debit</th>
                <th width="8%" class="kredit-col">Kredit</th>
                <th width="6%">Status</th>
                <th width="8%">Kel. Debit</th>
                <th width="8%">Kel. Kredit</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($result_data)): ?>
            <tr>
                <td colspan="14" class="text-center">Tidak ada data yang sesuai dengan filter</td>
            </tr>
            <?php else: ?>
                <?php 
                $no = 1;
                foreach ($result_data as $row): 
                    $row_class = ($no % 2 == 0) ? 'even-row' : '';
                ?>
                <tr class="<?= $row_class ?>">
                    <td class="text-center"><?= $no ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['id']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['id_jurnal']) ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])) ?></td>
                    <td><?= htmlspecialchars($row['notes']) ?></td>
                    <td><?= htmlspecialchars($row['tipe']) ?></td>
                    <td><?= htmlspecialchars($row['akun']) ?></td>
                    <td><?= htmlspecialchars(preg_replace('/^\d+\s*-\s*/', '', $row['perkiraan'])) ?></td>
                    <td><?= htmlspecialchars($row['akun_perkiraan']) ?></td>
                    <td class="text-right debit-col">
                        <?= $row['debit'] > 0 ? number_format($row['debit'], 2, ',', '.') : '-' ?>
                    </td>
                    <td class="text-right kredit-col">
                        <?= $row['kredit'] > 0 ? number_format($row['kredit'], 2, ',', '.') : '-' ?>
                    </td>
                    <td class="text-center"><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['kelompok_debit'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['kelompok_kredit'] ?? '') ?></td>
                </tr>
                <?php 
                $no++;
                endforeach; 
                ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="9" class="text-center font-bold">TOTAL</td>
                <td class="text-right font-bold">Rp <?= number_format($total_debit, 2, ',', '.') ?></td>
                <td class="text-right font-bold">Rp <?= number_format($total_kredit, 2, ',', '.') ?></td>
                <td colspan="3" class="text-center font-bold">
                    Balance: Rp <?= number_format(abs($total_debit - $total_kredit), 2, ',', '.') ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Additional Summary by Kelompok -->
    <?php if (!empty($result_data)): ?>
    <div style="margin-top: 20px;">
        <h4 style="text-align: center; background-color: #f8f9fa; padding: 8px; margin-bottom: 10px;">
            RINGKASAN PER KELOMPOK
        </h4>
        
        <?php
        // Group data by kelompok
        $kelompok_summary = [];
        foreach ($result_data as $row) {
            $kelompok_debit = $row['kelompok_debit'] ?? '';
            $kelompok_kredit = $row['kelompok_kredit'] ?? '';
            
            if ($kelompok_debit && $row['debit'] > 0) {
                if (!isset($kelompok_summary[$kelompok_debit])) {
                    $kelompok_summary[$kelompok_debit] = ['debit' => 0, 'kredit' => 0];
                }
                $kelompok_summary[$kelompok_debit]['debit'] += $row['debit'];
            }
            
            if ($kelompok_kredit && $row['kredit'] > 0) {
                if (!isset($kelompok_summary[$kelompok_kredit])) {
                    $kelompok_summary[$kelompok_kredit] = ['debit' => 0, 'kredit' => 0];
                }
                $kelompok_summary[$kelompok_kredit]['kredit'] += $row['kredit'];
            }
        }
        ?>
        
        <table style="width: 60%; margin: 0 auto;">
            <thead>
                <tr>
                    <th>Kelompok</th>
                    <th>Total Debit</th>
                    <th>Total Kredit</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kelompok_summary as $kelompok => $totals): ?>
                <tr>
                    <td class="font-bold"><?= htmlspecialchars($kelompok) ?></td>
                    <td class="text-right"><?= number_format($totals['debit'], 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($totals['kredit'], 2, ',', '.') ?></td>
                    <td class="text-right font-bold">
                        <?= number_format(abs($totals['debit'] - $totals['kredit']), 2, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer-info">
        <div>
            <p><strong>Dicetak pada:</strong> <?= date('d/m/Y H:i:s') ?></p>
            <p><strong>User:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></p>
        </div>
        <div class="signature-area">
            <p>_________________________</p>
            <p>Penanggung Jawab</p>
        </div>
    </div>
</div>

</body>
</html>