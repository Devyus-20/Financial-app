<?php
session_start();
require_once('database/init.php');

// Security: Validate session and user permissions
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit();
}

class FinancialReportGenerator {
    private $connection;
    
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    /**
     * Get total amount by group with optional date filtering
     */
    public function getTotalByGroup($group, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    SUM(CASE WHEN kelompok_debit = ? THEN debit ELSE 0 END) as total_debit,
                    SUM(CASE WHEN kelompok_kredit = ? THEN kredit ELSE 0 END) as total_kredit
                FROM buku_besar 
                WHERE 1=1";
        
        $params = [$group, $group];
        $types = "ss";
        
        if ($startDate && $endDate) {
            $sql .= " AND date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }
        
        try {
            $stmt = mysqli_prepare($this->connection, $sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($this->connection));
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            
            return ($row['total_debit'] ?? 0) + ($row['total_kredit'] ?? 0);
        } catch (Exception $e) {
            error_log("Error in getTotalByGroup: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get account details by group
     */
    public function getAccountsByGroup($group, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    akun,
                    perkiraan,
                    SUM(CASE WHEN kelompok_debit = ? THEN debit ELSE 0 END) as total_debit,
                    SUM(CASE WHEN kelompok_kredit = ? THEN kredit ELSE 0 END) as total_kredit
                FROM buku_besar 
                WHERE (kelompok_debit = ? OR kelompok_kredit = ?)";
        
        $params = [$group, $group, $group, $group];
        $types = "ssss";
        
        if ($startDate && $endDate) {
            $sql .= " AND date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }
        
        $sql .= " GROUP BY akun, perkiraan ORDER BY akun";
        
        try {
            $stmt = mysqli_prepare($this->connection, $sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($this->connection));
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $accountList = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $total = $row['total_debit'] + $row['total_kredit'];
                if ($total > 0) {
                    $accountList[] = [
                        'akun' => htmlspecialchars($row['akun']),
                        'perkiraan' => htmlspecialchars($row['perkiraan']),
                        'total' => $total
                    ];
                }
            }
            
            return $accountList;
        } catch (Exception $e) {
            error_log("Error in getAccountsByGroup: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get balance sheet data categorized by account ID prefix
     */
    public function getCategorizedBalanceSheetData($startDate = null, $endDate = null) {
        $sql = "SELECT 
                    akun,
                    perkiraan,
                    SUM(debit) as total_debit,
                    SUM(kredit) as total_kredit,
                    SUBSTRING(akun, 1, 1) as kategori
                FROM buku_besar 
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($startDate && $endDate) {
            $sql .= " AND date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }
        
        $sql .= " GROUP BY akun, perkiraan 
                  HAVING (total_debit > 0 OR total_kredit > 0)
                  ORDER BY akun";
        
        try {
            $stmt = mysqli_prepare($this->connection, $sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($this->connection));
            }
            
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $categorizedData = [
                'aktiva' => ['accounts' => [], 'total' => 0],
                'passiva' => ['accounts' => [], 'total' => 0],
                'modal' => ['accounts' => [], 'total' => 0]
            ];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $total = $row['total_debit'] + $row['total_kredit'];
                $account = [
                    'akun' => htmlspecialchars($row['akun']),
                    'perkiraan' => htmlspecialchars($row['perkiraan']),
                    'total' => $total
                ];
                
                // Kategorisasi berdasarkan digit pertama
                $firstDigit = $row['kategori'];
                if ($firstDigit == '1') {
                    $categorizedData['aktiva']['accounts'][] = $account;
                    $categorizedData['aktiva']['total'] += $total;
                } elseif ($firstDigit == '2') {
                    $categorizedData['passiva']['accounts'][] = $account;
                    $categorizedData['passiva']['total'] += $total;
                } elseif ($firstDigit == '3') {
                    $categorizedData['modal']['accounts'][] = $account;
                    $categorizedData['modal']['total'] += $total;
                }
            }
            
            return $categorizedData;
        } catch (Exception $e) {
            error_log("Error in getCategorizedBalanceSheetData: " . $e->getMessage());
            return [
                'aktiva' => ['accounts' => [], 'total' => 0],
                'passiva' => ['accounts' => [], 'total' => 0],
                'modal' => ['accounts' => [], 'total' => 0]
            ];
        }
    }
    
    /**
     * Get all balance sheet groups
     */
    public function getAllBalanceSheetGroups() {
        $sql = "SELECT DISTINCT 
                    CASE 
                        WHEN kelompok_debit IS NOT NULL AND kelompok_debit != '' THEN kelompok_debit 
                        WHEN kelompok_kredit IS NOT NULL AND kelompok_kredit != '' THEN kelompok_kredit 
                    END as kelompok
                FROM buku_besar 
                WHERE (kelompok_debit IS NOT NULL AND kelompok_debit != '') 
                   OR (kelompok_kredit IS NOT NULL AND kelompok_kredit != '')";
        
        try {
            $result = mysqli_query($this->connection, $sql);
            if (!$result) {
                throw new Exception("Query failed: " . mysqli_error($this->connection));
            }
            
            $groups = [];
            while ($row = mysqli_fetch_assoc($result)) {
                if (!empty($row['kelompok'])) {
                    $groups[] = $row['kelompok'];
                }
            }
            
            return array_unique($groups);
        } catch (Exception $e) {
            error_log("Error in getAllBalanceSheetGroups: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if group belongs to balance sheet (not profit/loss)
     */
    public function isBalanceSheetGroup($group) {
        $profitLossGroups = ['PENERIMAAN', 'PENDAPATAN', 'BEBAN', 'BIAYA'];
        return !in_array(strtoupper($group), $profitLossGroups);
    }
    
    /**
     * Get balance sheet data with account details
     */
    public function getBalanceSheetData($startDate = null, $endDate = null) {
        $allGroups = $this->getAllBalanceSheetGroups();
        $balanceSheetData = [];
        
        foreach ($allGroups as $group) {
            if ($this->isBalanceSheetGroup($group)) {
                $total = $this->getTotalByGroup($group, $startDate, $endDate);
                $accountList = $this->getAccountsByGroup($group, $startDate, $endDate);
                
                if ($total > 0 || !empty($accountList)) {
                    $balanceSheetData[$group] = [
                        'total' => $total,
                        'account_list' => $accountList
                    ];
                }
            }
        }
        
        return $balanceSheetData;
    }
    
    /**
     * Generate profit/loss report
     */
    public function getProfitLossReport($startDate = null, $endDate = null) {
        $revenue = $this->getTotalByGroup('PENERIMAAN', $startDate, $endDate);
        $expenses = $this->getTotalByGroup('BEBAN', $startDate, $endDate);
        $profitLoss = $revenue - $expenses;
        
        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit_loss' => $profitLoss,
            'revenue_details' => $this->getAccountsByGroup('PENERIMAAN', $startDate, $endDate),
            'expense_details' => $this->getAccountsByGroup('BEBAN', $startDate, $endDate)
        ];
    }
    
    /**
     * Format currency for display
     */
    public function formatCurrency($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
    
    /**
     * Validate date format
     */
    public function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

// Initialize the report generator
$reportGenerator = new FinancialReportGenerator($koneksi);

// Validate and sanitize input dates
$startDate = isset($_GET['start_date']) && $reportGenerator->validateDate($_GET['start_date']) 
    ? $_GET['start_date'] 
    : date('Y-m-01');

$endDate = isset($_GET['end_date']) && $reportGenerator->validateDate($_GET['end_date']) 
    ? $_GET['end_date'] 
    : date('Y-m-t');

// Generate reports
$profitLossReport = $reportGenerator->getProfitLossReport($startDate, $endDate);
$balanceSheetData = $reportGenerator->getBalanceSheetData();
$categorizedBalanceSheet = $reportGenerator->getCategorizedBalanceSheetData($startDate, $endDate);

// Calculate totals
$totalBalanceSheet = array_sum(array_column($balanceSheetData, 'total'));
$allGroups = $reportGenerator->getAllBalanceSheetGroups();

include 'layout/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .profit { color: #22c55e; }
        .loss { color: #ef4444; }
        .card-summary { border-left: 4px solid #3b82f6; }
        .table-group-header { background-color: #e0f2fe; font-weight: bold; }
        .account-detail { padding-left: 1.5rem; }
        #layoutSidenav { display: flex; width: 100%; }
        .chart-container { 
            position: relative; 
            height: 350px; 
            margin-bottom: 20px;
        }
        
        /* Custom scrollbar for balance sheet */
        .balance-sheet-scroll::-webkit-scrollbar {
            width: 6px;
        }
        
        .balance-sheet-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .balance-sheet-scroll::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        .balance-sheet-scroll::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Loading animation */
        .chart-loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 350px;
            flex-direction: column;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Neraca Category Styles */
        .neraca-aktiva { border-left: 4px solid #10b981; }
        .neraca-passiva { border-left: 4px solid #f59e0b; }
        .neraca-modal { border-left: 4px solid #8b5cf6; }
        
        .category-header-aktiva { background-color: #d1fae5; color: #065f46; }
        .category-header-passiva { background-color: #fef3c7; color: #92400e; }
        .category-header-modal { background-color: #ede9fe; color: #5b21b6; }
    </style>
</head>
<body>
    <div id="layoutSidenav">
        <?php include 'layout/sidebar.php'; ?>   

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                        <h1>Laporan Keuangan</h1>
                        <button class="btn btn-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Cetak Laporan
                        </button>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Dashboard / Laporan Keuangan</li>
                    </ol>

                    <!-- Date Filter Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="laporan.php" class="row g-3">
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" id="start_date" name="start_date" 
                                           value="<?= htmlspecialchars($startDate) ?>" 
                                           class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                                    <input type="date" id="end_date" name="end_date" 
                                           value="<?= htmlspecialchars($endDate) ?>" 
                                           class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Profit/Loss Report -->
                        <div class="col-md-6">
                            <div class="card mb-4 card-summary">
                                <div class="card-header">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Laporan Laba/Rugi
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Periode: <?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?></strong>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="profit mb-1">Penerimaan:</p>
                                            <h5 class="profit"><?= $reportGenerator->formatCurrency($profitLossReport['revenue']) ?></h5>
                                        </div>
                                        <div class="col-6">
                                            <p class="loss mb-1">Beban:</p>
                                            <h5 class="loss"><?= $reportGenerator->formatCurrency($profitLossReport['expenses']) ?></h5>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="text-center">
                                        <p class="mb-1">
                                            <strong><?= $profitLossReport['profit_loss'] >= 0 ? 'LABA' : 'RUGI' ?></strong>
                                        </p>
                                        <h4 class="<?= $profitLossReport['profit_loss'] >= 0 ? 'profit' : 'loss' ?>">
                                            <?= $reportGenerator->formatCurrency(abs($profitLossReport['profit_loss'])) ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Categorized Balance Sheet -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-balance-scale me-1"></i>
                                    Neraca Berdasarkan Kategori
                                </div>
                                <div class="card-body balance-sheet-scroll" style="max-height: 400px; overflow-y: auto;">
                                    <?php 
                                    $totalAktiva = $categorizedBalanceSheet['aktiva']['total'];
                                    $totalPassiva = $categorizedBalanceSheet['passiva']['total'];
                                    $totalModal = $categorizedBalanceSheet['modal']['total'];
                                    $grandTotal = $totalAktiva + $totalPassiva + $totalModal;
                                    
                                    if ($grandTotal == 0): ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <p>Tidak ada data neraca tersedia</p>
                                        </div>
                                    <?php else: ?>
                                        <table class="table table-sm">
                                            <tbody>
                                                <!-- AKTIVA -->
                                                <?php if ($totalAktiva > 0): ?>
                                                <tr class="category-header-aktiva">
                                                    <td colspan="2"><strong><i class="fas fa-building me-2"></i>AKTIVA</strong></td>
                                                </tr>
                                                <?php foreach ($categorizedBalanceSheet['aktiva']['accounts'] as $account): ?>
                                                <tr>
                                                    <td class="account-detail">
                                                        <?= $account['akun'] ?> - <?= $account['perkiraan'] ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?= $reportGenerator->formatCurrency($account['total']) ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-success">
                                                    <td><strong>TOTAL AKTIVA</strong></td>
                                                    <td class="text-end"><strong><?= $reportGenerator->formatCurrency($totalAktiva) ?></strong></td>
                                                </tr>
                                                <tr><td colspan="2">&nbsp;</td></tr>
                                                <?php endif; ?>

                                                <!-- PASSIVA -->
                                                <?php if ($totalPassiva > 0): ?>
                                                <tr class="category-header-passiva">
                                                    <td colspan="2"><strong><i class="fas fa-hand-holding-usd me-2"></i>PASSIVA</strong></td>
                                                </tr>
                                                <?php foreach ($categorizedBalanceSheet['passiva']['accounts'] as $account): ?>
                                                <tr>
                                                    <td class="account-detail">
                                                        <?= $account['akun'] ?> - <?= $account['perkiraan'] ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?= $reportGenerator->formatCurrency($account['total']) ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-warning">
                                                    <td><strong>TOTAL PASSIVA</strong></td>
                                                    <td class="text-end"><strong><?= $reportGenerator->formatCurrency($totalPassiva) ?></strong></td>
                                                </tr>
                                                <tr><td colspan="2">&nbsp;</td></tr>
                                                <?php endif; ?>

                                                <!-- MODAL -->
                                                <?php if ($totalModal > 0): ?>
                                                <tr class="category-header-modal">
                                                    <td colspan="2"><strong><i class="fas fa-coins me-2"></i>MODAL</strong></td>
                                                </tr>
                                                <?php foreach ($categorizedBalanceSheet['modal']['accounts'] as $account): ?>
                                                <tr>
                                                    <td class="account-detail">
                                                        <?= $account['akun'] ?> - <?= $account['perkiraan'] ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?= $reportGenerator->formatCurrency($account['total']) ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-info">
                                                    <td><strong>TOTAL MODAL</strong></td>
                                                    <td class="text-end"><strong><?= $reportGenerator->formatCurrency($totalModal) ?></strong></td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot class="table-dark">
                                                <tr>
                                                    <th>TOTAL NERACA</th>
                                                    <th class="text-end">
                                                        <?= $reportGenerator->formatCurrency($grandTotal) ?>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Sheet Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card neraca-aktiva h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-2x text-success mb-2"></i>
                                    <h5 class="card-title">Total Aktiva</h5>
                                    <h4 class="text-success"><?= $reportGenerator->formatCurrency($totalAktiva) ?></h4>
                                    <small class="text-muted"><?= count($categorizedBalanceSheet['aktiva']['accounts']) ?> akun</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card neraca-passiva h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-hand-holding-usd fa-2x text-warning mb-2"></i>
                                    <h5 class="card-title">Total Passiva</h5>
                                    <h4 class="text-warning"><?= $reportGenerator->formatCurrency($totalPassiva) ?></h4>
                                    <small class="text-muted"><?= count($categorizedBalanceSheet['passiva']['accounts']) ?> akun</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card neraca-modal h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-coins fa-2x text-info mb-2"></i>
                                    <h5 class="card-title">Total Modal</h5>
                                    <h4 class="text-info"><?= $reportGenerator->formatCurrency($totalModal) ?></h4>
                                    <small class="text-muted"><?= count($categorizedBalanceSheet['modal']['accounts']) ?> akun</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Reports -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Detail Laporan Laba/Rugi
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="profit">Detail Penerimaan</h6>
                                            <?php if (empty($profitLossReport['revenue_details'])): ?>
                                                <p class="text-muted">Tidak ada data penerimaan</p>
                                            <?php else: ?>
                                                <table class="table table-sm">
                                                    <?php foreach ($profitLossReport['revenue_details'] as $account): ?>
                                                    <tr>
                                                        <td><?= $account['akun'] ?></td>
                                                        <td><?= $account['perkiraan'] ?></td>
                                                        <td class="text-end">
                                                            <?= $reportGenerator->formatCurrency($account['total']) ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h6 class="loss">Detail Beban</h6>
                                            <?php if (empty($profitLossReport['expense_details'])): ?>
                                                <p class="text-muted">Tidak ada data beban</p>
                                            <?php else: ?>
                                                <table class="table table-sm">
                                                    <?php foreach ($profitLossReport['expense_details'] as $account): ?>
                                                    <tr>
                                                        <td><?= $account['akun'] ?></td>
                                                        <td><?= $account['perkiraan'] ?></td>
                                                        <td class="text-end">
                                                            <?= $reportGenerator->formatCurrency($account['total']) ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary All Groups -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-pie me-1"></i>
                                    Ringkasan Semua Kelompok Akun
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($allGroups as $group): 
                                            $groupTotal = $reportGenerator->getTotalByGroup($group, $startDate, $endDate);
                                            if ($groupTotal > 0):
                                        ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="card border-primary h-100">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title text-primary">
                                                        <?= strtoupper(htmlspecialchars($group)) ?>
                                                    </h6>
                                                    <h6 class="profit">
                                                        <?= $reportGenerator->formatCurrency($groupTotal) ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?= count($reportGenerator->getAccountsByGroup($group, $startDate, $endDate)) ?> akun
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar me-1"></i>
                                    Grafik Laba/Rugi
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="profitLossChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-pie me-1"></i>
                                    Grafik Komposisi Neraca
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="balanceSheetChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
            
            <?php include 'layout/footer.php'; ?>
        </div>
    </div>

    <script>
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            initializeProfitLossChart();
            initializeBalanceSheetChart();
        });

        function initializeProfitLossChart() {
            const ctx = document.getElementById('profitLossChart').getContext('2d');
            const revenue = <?= $profitLossReport['revenue'] ?>;
            const expenses = <?= $profitLossReport['expenses'] ?>;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Penerimaan', 'Beban'],
                    datasets: [{
                        label: 'Jumlah (Rp)',
                        data: [revenue, expenses],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderColor: [
                            'rgba(34, 197, 94, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Perbandingan Penerimaan vs Beban'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        function initializeBalanceSheetChart() {
            const ctx = document.getElementById('balanceSheetChart').getContext('2d');
            const aktiva = <?= $totalAktiva ?>;
            const passiva = <?= $totalPassiva ?>;
            const modal = <?= $totalModal ?>;
            
            const data = [];
            const labels = [];
            const colors = [];
            
            if (aktiva > 0) {
                data.push(aktiva);
                labels.push('Aktiva');
                colors.push('rgba(16, 185, 129, 0.8)');
            }
            if (passiva > 0) {
                data.push(passiva);
                labels.push('Passiva');
                colors.push('rgba(245, 158, 11, 0.8)');
            }
            if (modal > 0) {
                data.push(modal);
                labels.push('Modal');
                colors.push('rgba(139, 92, 246, 0.8)');
            }
            
            if (data.length === 0) {
                // Show no data message
                document.getElementById('balanceSheetChart').style.display = 'none';
                const container = document.getElementById('balanceSheetChart').parentElement;
                container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-info-circle fa-2x mb-2"></i><p>Tidak ada data untuk ditampilkan</p></div>';
                return;
            }

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Komposisi Neraca'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        function printReport() {
            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Laporan Keuangan</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .period { text-align: center; margin-bottom: 20px; color: #666; }
                        .section { margin-bottom: 30px; }
                        .section h3 { border-bottom: 2px solid #333; padding-bottom: 5px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .text-right { text-align: right; }
                        .profit { color: #22c55e; font-weight: bold; }
                        .loss { color: #ef4444; font-weight: bold; }
                        .total-row { font-weight: bold; background-color: #f8f9fa; }
                        @media print {
                            .no-print { display: none; }
                            body { margin: 0; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>LAPORAN KEUANGAN</h1>
                        <div class="period">
                            Periode: <?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?>
                        </div>
                    </div>

                    <div class="section">
                        <h3>LAPORAN LABA/RUGI</h3>
                        <table>
                            <tr>
                                <td><strong>PENERIMAAN</strong></td>
                                <td class="text-right profit"><?= $reportGenerator->formatCurrency($profitLossReport['revenue']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>BEBAN</strong></td>
                                <td class="text-right loss"><?= $reportGenerator->formatCurrency($profitLossReport['expenses']) ?></td>
                            </tr>
                            <tr class="total-row">
                                <td><strong><?= $profitLossReport['profit_loss'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' ?></strong></td>
                                <td class="text-right <?= $profitLossReport['profit_loss'] >= 0 ? 'profit' : 'loss' ?>">
                                    <?= $reportGenerator->formatCurrency(abs($profitLossReport['profit_loss'])) ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="section">
                        <h3>NERACA</h3>
                        <table>
                            <?php if ($totalAktiva > 0): ?>
                            <tr class="total-row">
                                <td colspan="2"><strong>AKTIVA</strong></td>
                            </tr>
                            <?php foreach ($categorizedBalanceSheet['aktiva']['accounts'] as $account): ?>
                            <tr>
                                <td>&nbsp;&nbsp;<?= $account['akun'] ?> - <?= $account['perkiraan'] ?></td>
                                <td class="text-right"><?= $reportGenerator->formatCurrency($account['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td><strong>TOTAL AKTIVA</strong></td>
                                <td class="text-right"><strong><?= $reportGenerator->formatCurrency($totalAktiva) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            
                            <?php if ($totalPassiva > 0): ?>
                            <tr><td colspan="2">&nbsp;</td></tr>
                            <tr class="total-row">
                                <td colspan="2"><strong>PASSIVA</strong></td>
                            </tr>
                            <?php foreach ($categorizedBalanceSheet['passiva']['accounts'] as $account): ?>
                            <tr>
                                <td>&nbsp;&nbsp;<?= $account['akun'] ?> - <?= $account['perkiraan'] ?></td>
                                <td class="text-right"><?= $reportGenerator->formatCurrency($account['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td><strong>TOTAL PASSIVA</strong></td>
                                <td class="text-right"><strong><?= $reportGenerator->formatCurrency($totalPassiva) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            
                            <?php if ($totalModal > 0): ?>
                            <tr><td colspan="2">&nbsp;</td></tr>
                            <tr class="total-row">
                                <td colspan="2"><strong>MODAL</strong></td>
                            </tr>
                            <?php foreach ($categorizedBalanceSheet['modal']['accounts'] as $account): ?>
                            <tr>
                                <td>&nbsp;&nbsp;<?= $account['akun'] ?> - <?= $account['perkiraan'] ?></td>
                                <td class="text-right"><?= $reportGenerator->formatCurrency($account['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td><strong>TOTAL MODAL</strong></td>
                                <td class="text-right"><strong><?= $reportGenerator->formatCurrency($totalModal) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            
                            <tr style="background-color: #333; color: white;">
                                <td><strong>TOTAL NERACA</strong></td>
                                <td class="text-right"><strong><?= $reportGenerator->formatCurrency($grandTotal) ?></strong></td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-top: 50px; text-align: right;">
                        <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
                    </div>
                </body>
                </html>
            `;
            
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }

        // Form validation
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (startDate > endDate) {
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                this.value = '';
            }
        });

        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(this.value);
            
            if (endDate < startDate) {
                alert('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
                this.value = '';
            }
        });

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            if (confirm('Refresh data laporan keuangan?')) {
                location.reload();
            }
        }, 300000); // 5 minutes

        // Loading animation for charts
        function showChartLoading(chartId) {
            const container = document.getElementById(chartId).parentElement;
            container.innerHTML = `
                <div class="chart-loading">
                    <div class="spinner"></div>
                    <p class="mt-2">Memuat grafik...</p>
                </div>
            `;
        }

        // Export to Excel functionality (optional)
        function exportToExcel() {
            const data = {
                startDate: '<?= $startDate ?>',
                endDate: '<?= $endDate ?>',
                profitLoss: {
                    revenue: <?= $profitLossReport['revenue'] ?>,
                    expenses: <?= $profitLossReport['expenses'] ?>,
                    profit: <?= $profitLossReport['profit_loss'] ?>
                },
                balanceSheet: {
                    aktiva: <?= $totalAktiva ?>,
                    passiva: <?= $totalPassiva ?>,
                    modal: <?= $totalModal ?>,
                    total: <?= $grandTotal ?>
                }
            };
            
            // This would require additional server-side processing
            // For now, we'll just log the data structure
            console.log('Export data:', data);
            alert('Fitur export Excel akan segera tersedia');
        }
    </script>

    <!-- Additional styles for print -->
    <style media="print">
        .no-print, .card-header, .btn, .breadcrumb, form, .chart-container {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
        
        body {
            background: white !important;
        }
        
        .table {
            font-size: 12px;
        }
    </style>

</body>
</html>

<?php
// Clean up any output buffers and close database connection
if (ob_get_level()) {
    ob_end_flush();
}

// Close database connection if it exists
if (isset($koneksi) && $koneksi) {
    mysqli_close($koneksi);
}
?>