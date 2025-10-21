<?php
// Start session to check user role
session_start();

require_once('database/init.php');

// Get user role
$user_role = $_SESSION['role'];

// Check if user has access to buku besar
if (!in_array($user_role, ['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit();
}

require 'database/db.php';
require_once 'funct_bukubesar.php';  // Include the functions file

$filter = []; // jika Anda juga menggunakan filter, pastikan ini diisi sesuai kebutuhan
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id'; // default 'id'
$orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'ASC'; // default 'ASC'

// Get data using the function from funct_bukubesar.php
$result_data = get_buku_besar($koneksi, $filter, $orderBy, $orderDir);

// Check if the print button was clicked
if(isset($_GET['print']) && $_GET['print'] == 'true') {
    // Debug: Check if data exists
    error_log("Print function called. Data count: " . count($result_data));
    
    // Re-fetch data to ensure we have fresh data for printing
    $result_data = get_buku_besar($koneksi, $filter, $orderBy, $orderDir);
    
    // Start output buffering to capture HTML content
    ob_start();
    
    // Create complete HTML structure for PDF with proper header
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Laporan Buku Besar</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                font-size: 12px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #333;
                padding-bottom: 15px;
            }
            .company-name {
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .company-address {
                font-size: 12px;
                color: #666;
                margin-bottom: 10px;
            }
            .report-title {
                font-size: 16px;
                font-weight: bold;
                margin-top: 15px;
            }
            .report-date {
                font-size: 11px;
                color: #666;
                margin-top: 5px;
            }
            .container {
                width: 100%;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                font-size: 10px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 6px 4px;
                text-align: left;
                word-wrap: break-word;
            }
            th {
                background-color: #f8f9fa;
                font-weight: bold;
                text-align: center;
            }
            .text-end {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }
            .page-break {
                page-break-before: always;
            }
            /* Adjust column widths for better fit */
            .col-id { width: 5%; }
            .col-date { width: 8%; }
            .col-notes { width: 15%; }
            .col-type { width: 8%; }
            .col-account { width: 12%; }
            .col-amount { width: 10%; }
            .col-status { width: 8%; }
            
            @page {
                margin: 15mm;
                size: A4 landscape;
            }
            
            @media print {
                body { margin: 0; }
                .no-print { display: none !important; }
                .page-break { page-break-before: always; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="company-name">NAMA PERUSAHAAN ANDA</div>
            <div class="company-address">
                Alamat Perusahaan<br>
                Kota, Kode Pos<br>
                Telp: (021) 123-4567
            </div>
            <div class="report-title">LAPORAN BUKU BESAR</div>
            <div class="report-date">
                Periode: Semua Data | Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
        
        <div class="container">     
            <table>
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-id">ID Jurnal</th>
                        <th class="col-date">Tanggal</th>
                        <th class="col-notes">Catatan</th>
                        <th class="col-type">Tipe</th>
                        <th class="col-account">Akun</th>
                        <th class="col-account">Perkiraan</th>
                        <th class="col-account">Akun Perkiraan</th>
                        <th class="col-amount">Debit</th>
                        <th class="col-amount">Kredit</th>
                        <th class="col-amount">Nilai</th>
                        <th class="col-status">Status</th>
                        <th class="col-account">Kel. Kredit</th>
                        <th class="col-account">Kel. Debit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Check if data exists and is array
                    if (!empty($result_data) && is_array($result_data)) {
                        foreach ($result_data as $row) { 
                            // Ensure $row is array and has required keys
                            if (!is_array($row)) continue;
                            
                            // Get kelompok for this akun - dengan null coalescing operator
                            $kelompok_kredit = $row['kelompok_kredit'] ?? '';
                            $kelompok_debit = $row['kelompok_debit'] ?? '';
                    ?>
                    <tr>
                        <td class="text-center"><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['id_jurnal'] ?? ''); ?></td>
                        <td class="text-center"><?php echo isset($row['date']) ? date('d-m-Y', strtotime($row['date'])) : ''; ?></td>
                        <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['tipe'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['akun'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['perkiraan'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['akun_perkiraan'] ?? ''); ?></td>
                        <td class="text-end"><?php echo number_format($row['debit'] ?? 0, 2, ',', '.'); ?></td>
                        <td class="text-end"><?php echo number_format($row['kredit'] ?? 0, 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['nilai'] ?? ''); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['status'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($kelompok_kredit); ?></td>
                        <td><?php echo htmlspecialchars($kelompok_debit); ?></td>
                    </tr>
                    <?php 
                        } 
                    } else {
                    ?>
                    <tr>
                        <td colspan="14" class="text-center">Tidak ada data untuk ditampilkan</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <!-- Summary atau tanda tangan -->
            <div style="margin-top: 40px;">
                <table style="border: none; width: 100%;">
                    <tr style="border: none;">
                        <td style="border: none; width: 50%;"></td>
                        <td style="border: none; width: 50%; text-align: center;">
                            <div style="margin-bottom: 60px;">
                                <?php echo date('d F Y'); ?><br>
                                Mengetahui,
                            </div>
                            <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                            <div style="margin-top: 5px;">Nama Pejabat</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Auto print and close script -->
        <script>
            window.onload = function() {
                // Auto print when page loads
                window.print();
                
                // Close window after printing (with delay to ensure printing starts)
                setTimeout(function() {
                    window.close();
                }, 1000);
            };
        </script>
    </body>
    </html>
    <?php
    
    // Get the content for PDF generation
    $content = ob_get_clean();
    
    // Check if HTML2PDF library exists
    if (file_exists('vendor/autoload.php')) {
        // Generate PDF using HTML2PDF
        require_once 'vendor/autoload.php';
        try {
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('L', 'A4', 'id', true, 'UTF-8', array(8, 8, 8, 8));
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($content);
            $html2pdf->Output('bukubesar_laporan_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
            exit;
        } catch (\Spipu\Html2Pdf\Exception\Html2PdfException $e) {
            error_log("PDF Generation Error: " . $e->getMessage());
            // Fallback to HTML print if PDF fails
            echo $content;
            exit;
        } catch (Exception $e) {
            error_log("General PDF Error: " . $e->getMessage());
            // Fallback to HTML print if PDF fails
            echo $content;
            exit;
        }
    } else {
        // Fallback: Just display HTML for printing
        echo $content;
        exit;
    }
}

include 'layout/header.php';
?>

    <div id="layoutSidenav">
        <?php include 'layout/sidebar.php'; ?>
        </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Laporan Jurnal</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">
                                <a href="dashboard.php">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active">Laporan Jurnal</li>
                        </ol>
                        
                        <!-- Role indicator for clarity -->
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Logged in as: <strong><?php echo ucfirst($user_role); ?></strong>
                            <?php if ($user_role == 'dashboard_manager'): ?>
                            - You have access to view Buku Besar and generate reports.
                            <?php endif; ?>
                        </div>
                        
                        <!-- Debug info (remove in production) -->
                        <?php if (isset($_GET['debug'])): ?>
                        <div class="alert alert-warning">
                            <strong>Debug Info:</strong><br>
                            Data Count: <?php echo count($result_data); ?><br>
                            User Role: <?php echo $user_role; ?><br>
                            Vendor Autoload: <?php echo file_exists('vendor/autoload.php') ? 'EXISTS' : 'NOT FOUND'; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-table me-1"></i>
                                    Data Buku Besar
                                </div>
                                <div>
                                <button class="btn btn-sm btn-info float-end" onclick="printBukuBesar()">
                                    <i class="fas fa-print"></i> Print Laporan
                                </button>
                                    <button class="btn btn-sm btn-success" onclick="exportBukuBesar()">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($result_data)): ?>
                                <div class="table-responsive">
                                <table id="datatablesSimple" class="table table-bordered table-striped">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID</th>
                                            <th>ID Jurnal</th>
                                            <th>Tanggal</th>
                                            <th>Catatan</th>
                                            <th>Tipe</th>
                                            <th>Akun</th>
                                            <th>Perkiraan</th>
                                            <th>Akun Perkiraan</th>
                                            <th>Debit</th>
                                            <th>Kredit</th>
                                            <th>Nilai</th>
                                            <th>Status</th>
                                            <th>Kelompok Debit</th>
                                            <th>Kelompok Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($result_data as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['id_jurnal'] ?? ''); ?></td>
                                            <td><?php echo isset($row['date']) ? date('d-m-Y', strtotime($row['date'])) : ''; ?></td>
                                            <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['tipe'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['akun'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars(preg_replace('/^\d+\s*-\s*/', '', $row['perkiraan'] ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars($row['akun_perkiraan'] ?? ''); ?></td>
                                            <td class="text-end"><?php echo number_format($row['debit'] ?? 0, 2, ',', '.'); ?></td>
                                            <td class="text-end"><?php echo number_format($row['kredit'] ?? 0, 2, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($row['nilai'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['status'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['kelompok_debit'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['kelompok_kredit'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Tidak ada data Buku Besar</h5>
                                    <p class="text-muted">Belum ada transaksi yang tercatat dalam sistem.</p>
                                    <?php if ($user_role == 'admin'): ?>
                                    <a href="jurnal_transaksi.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Tambah Transaksi
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </main>
                <?php include 'layout/footer.php'; ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Initialize datatable only if table exists
                const table = document.getElementById("datatablesSimple");
                if (table) {
                    new simpleDatatables.DataTable("#datatablesSimple", {
                        searchable: true,
                        sortable: true,
                        fixedHeight: true,
                        perPage: 10,
                        perPageSelect: [5, 10, 15, 20, 25, 50, 100],
                        labels: {
                            placeholder: "Cari data...",
                            perPage: "data per halaman",
                            noRows: "Tidak ada data",
                            info: "Menampilkan {start} sampai {end} dari {rows} data",
                        }
                    });
                }
                
                // Initialize date pickers
                flatpickr(".datepicker", {
                    dateFormat: "Y-m-d",
                });
            });

            function printBukuBesar() {
        // Get current filter parameters if any
        const urlParams = new URLSearchParams(window.location.search);
        
        // Build print URL with current parameters
        let printUrl = 'buku_besar_print.php';
        const params = [];
        
        // Add existing URL parameters to print URL
        for (const [key, value] of urlParams.entries()) {
            if (['start_date', 'end_date', 'akun', 'tipe', 'kelompok', 'orderBy', 'orderDir'].includes(key)) {
                params.push(`${key}=${encodeURIComponent(value)}`);
            }
        }
        
        if (params.length > 0) {
            printUrl += '?' + params.join('&');
        }
        
        // Open print window
        window.open(printUrl, '_blank', 'width=1200,height=800');
    }

            function exportBukuBesar() {
                // Simple CSV export function
                const table = document.getElementById('datatablesSimple');
                if (!table) {
                    alert('No data to export');
                    return;
                }
                
                let csv = '';
                const rows = table.querySelectorAll('tr');
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const cols = row.querySelectorAll('td, th');
                    const csvRow = [];
                    
                    for (let j = 0; j < cols.length; j++) {
                        csvRow.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                    }
                    
                    csv += csvRow.join(',') + '\n';
                }
                
                // Download CSV
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'bukubesar_' + new Date().toISOString().slice(0, 10) + '.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }
        </script>