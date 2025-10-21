<?php
// Start session to check user role
session_start();

require_once('database/init.php');

// Get user role
$user_role = $_SESSION['role'];

// Check if user has access to add akun
if (!in_array($user_role, ['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit();
}

require 'database/db.php';

if (!isset($_SESSION['id'])) {
  header("Location: index.php");
  exit();
}

// Get all akun data
$koneksi = mysqli_connect($servername, $username, $password, $database);
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM add_akun ORDER BY id ASC";
$result = mysqli_query($koneksi, $sql);
$akun_data = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $akun_data[] = $row;
    }
}

// Check if the print button was clicked
if(isset($_GET['print']) && $_GET['print'] == 'true') {
    // Debug: Check if data exists
    error_log("Print function called. Data count: " . count($akun_data));
    
    // Start output buffering to capture HTML content
    ob_start();
    
    // Create complete HTML structure for PDF with proper header
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Laporan Data Akun</title>
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
                font-size: 11px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px 6px;
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
            .col-id { width: 15%; }
            .col-nama { width: 35%; }
            .col-pembayaran { width: 20%; }
            .col-kelompok { width: 30%; }
            
            @page {
                margin: 15mm;
                size: A4 portrait;
            }
            
            @media print {
                body { margin: 0; }
                .no-print { display: none !important; }
                .page-break { page-break-before: always; }
            }
            
            .summary-section {
                margin-top: 20px;
                padding: 15px;
                background-color: #f8f9fa;
                border: 1px solid #ddd;
            }
            
            .summary-row {
                display: flex;
                justify-content: space-between;
                margin: 5px 0;
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
            <div class="report-title">LAPORAN DATA AKUN</div>
            <div class="report-date">
                Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
        
        <div class="container">     
            <table>
                <thead>
                    <tr>
                        <th class="col-id">ID Akun</th>
                        <th class="col-nama">Nama Akun</th>
                        <th class="col-pembayaran">Tipe Pembayaran</th>
                        <th class="col-kelompok">Kelompok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Counters for summary
                    $total_akun = 0;
                    $count_by_kelompok = [];
                    $count_by_pembayaran = [];
                    
                    // Check if data exists and is array
                    if (!empty($akun_data) && is_array($akun_data)) {
                        foreach ($akun_data as $row) { 
                            // Ensure $row is array and has required keys
                            if (!is_array($row)) continue;
                            
                            $total_akun++;
                            
                            // Count by kelompok
                            $kelompok = $row['kelompok'] ?? 'Tidak Diketahui';
                            $count_by_kelompok[$kelompok] = ($count_by_kelompok[$kelompok] ?? 0) + 1;
                            
                            // Count by pembayaran
                            $pembayaran = $row['pembayaran'] ?? 'Tidak Diketahui';
                            $count_by_pembayaran[$pembayaran] = ($count_by_pembayaran[$pembayaran] ?? 0) + 1;
                    ?>
                    <tr>
                        <td class="text-center"><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_akun'] ?? ''); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['pembayaran'] ?? ''); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['kelompok'] ?? ''); ?></td>
                    </tr>
                    <?php 
                        } 
                    } else {
                    ?>
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data untuk ditampilkan</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <!-- Summary Section -->
            <?php if ($total_akun > 0): ?>
            <div class="summary-section">
                <h4 style="margin-top: 0; margin-bottom: 15px;">RINGKASAN DATA</h4>
                
                <div class="summary-row">
                    <strong>Total Akun:</strong>
                    <strong><?php echo $total_akun; ?> Akun</strong>
                </div>
                
                <hr style="margin: 10px 0;">
                
                <div style="margin-bottom: 10px;"><strong>Berdasarkan Kelompok:</strong></div>
                <?php foreach ($count_by_kelompok as $kelompok => $count): ?>
                <div class="summary-row">
                    <span><?php echo htmlspecialchars($kelompok); ?>:</span>
                    <span><?php echo $count; ?> Akun</span>
                </div>
                <?php endforeach; ?>
                
                <hr style="margin: 10px 0;">
                
                <div style="margin-bottom: 10px;"><strong>Berdasarkan Tipe Pembayaran:</strong></div>
                <?php foreach ($count_by_pembayaran as $pembayaran => $count): ?>
                <div class="summary-row">
                    <span><?php echo htmlspecialchars($pembayaran); ?>:</span>
                    <span><?php echo $count; ?> Akun</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Signature section -->
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
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'id', true, 'UTF-8', array(8, 8, 8, 8));
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($content);
            $html2pdf->Output('akun_laporan_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
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

// If not printing, redirect back to main page
header('Location: add_admin.php');  // PERUBAHAN: Redirect ke add_admin.php
exit;

mysqli_close($koneksi);
?>