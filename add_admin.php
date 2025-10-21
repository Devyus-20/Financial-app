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

// Proses penambahan akun
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['namaakun'])) {
    $koneksi = mysqli_connect($servername, $username, $password, $database);
    
    if ($koneksi) {
        $id = mysqli_real_escape_string($koneksi, $_POST['id']);
        $nama_akun = mysqli_real_escape_string($koneksi, $_POST['namaakun']);
        $pembayaran = mysqli_real_escape_string($koneksi, $_POST['pembayaran']);
        $kelompok = mysqli_real_escape_string($koneksi, $_POST['kelompok']);
        
        // Check if ID already exists
        $check_query = "SELECT id FROM add_akun WHERE id = '$id'";
        $check_result = mysqli_query($koneksi, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "ID Akun sudah ada! Silakan gunakan ID yang berbeda.";
        } else {
            // Insert new account
            $insert_query = "INSERT INTO add_akun (id, nama_akun, pembayaran, kelompok) VALUES ('$id', '$nama_akun', '$pembayaran', '$kelompok')";
            
            if (mysqli_query($koneksi, $insert_query)) {
                $success_message = "Data akun berhasil ditambahkan!";
            } else {
                $error_message = "Gagal menambahkan data: " . mysqli_error($koneksi);
            }
        }
        mysqli_close($koneksi);
    } else {
        $error_message = "Koneksi database gagal!";
    }
}

// Handle print functionality
if(isset($_GET['print']) && $_GET['print'] == 'true') {
    // Get data for printing
    $koneksi = mysqli_connect($servername, $username, $password, $database);
    $data = mysqli_query($koneksi, "SELECT * FROM add_akun ORDER BY id ASC");
    
    // Start output buffering to capture HTML content
    ob_start();
    
    // Create complete HTML structure for printing
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
                font-size: 12px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
                word-wrap: break-word;
            }
            th {
                background-color: #f8f9fa;
                font-weight: bold;
                text-align: center;
            }
            .text-center {
                text-align: center;
            }
            .page-break {
                page-break-before: always;
            }
            
            @page {
                margin: 15mm;
                size: A4;
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
            <div class="report-title">LAPORAN DATA AKUN</div>
            <div class="report-date">
                Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
        
        <div class="container">     
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Default</th>
                        <th>Kelompok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($data) > 0) {
                        while ($d = mysqli_fetch_array($data)) { 
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($d['id'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($d['nama_akun'] ?? ''); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($d['pembayaran'] ?? ''); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($d['kelompok'] ?? ''); ?></td>
                    </tr>
                    <?php 
                        } 
                    } else {
                    ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data untuk ditampilkan</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <!-- Summary dan tanda tangan -->
            <div style="margin-top: 40px;">
                <table style="border: none; width: 100%;">
                    <tr style="border: none;">
                        <td style="border: none; width: 50%;">
                            <div style="text-align: left;">
                                <strong>Total Akun: <?php echo mysqli_num_rows($data); ?></strong>
                            </div>
                        </td>
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
    
    // Get the content and output it
    $content = ob_get_clean();
    echo $content;
    mysqli_close($koneksi);
    exit;
}

include 'layout/header.php';
?>

<div id="layoutSidenav">
  <?php include 'layout/sidebar.php'; ?>

  <div id="layoutSidenav_content">
    <main>
      <div class="container-fluid px-4">
        <h1 class="mt-4">Add Akun</h1>
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <a href="dashboard.php">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">Add Akun</li>
        </ol>
        
        <!-- Notifikasi Success -->
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Berhasil!</strong> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Notifikasi Error -->
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error!</strong> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Role indicator for clarity -->
        <div class="alert alert-info mb-4">
          <i class="fas fa-info-circle me-2"></i>
          Logged in as: <strong><?php echo ucfirst($user_role); ?></strong>
          <?php if ($user_role == 'manager'): ?>
          - You have access to manage accounts.
          <?php endif; ?>
        </div>
        
        <div class="row">
          <div class="col-xl-3 col-md-6">

          </div>
          <div class="card mb-4 --bs-secondary">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-table me-1"></i>
                Data Akun
              </div>
              <div>
                <button class="btn btn-sm btn-info me-2" onclick="printAkun()">
                  <i class="fas fa-print"></i> Print Akun
                </button>
                <button class="btn btn-sm btn-success me-2" onclick="exportAkun()">
                  <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                  <i class="fas fa-plus"></i> Tambahkan
                </button>
              </div>
            </div>

            <!-- The Modal Tambah Akun -->
            <div class="modal fade" id="myModal">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">

                  <!-- Modal Header -->
                  <div class="modal-header">
                    <h4 class="modal-title">Tambahkan Akun</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>

                  <!-- Modal body -->
                  <form action="" method="post" id="formTambahAkun">
                    <div class="modal-body">
                        <br>
                        <!-- Input lainnya -->
                        <div class="mb-3">
                            <label for="id" class="form-label">ID Akun</label>
                            <input type="text" name="id" id="id" placeholder="Masukkan ID Akun" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="namaakun" class="form-label">Nama Akun</label>
                            <input type="text" name="namaakun" id="namaakun" placeholder="Masukkan Nama Akun" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="pembayaran" class="form-label">Default</label>
                            <select class="form-control" name="pembayaran" id="pembayaran" required>
                                <option value="">-- Pilih Default --</option>
                                <option value="Default">Default</option>
                                <option value="Debit">Debit</option>
                                <option value="Kredit">Kredit</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="kelompok" class="form-label">Kelompok</label>
                            <select class="form-control" name="kelompok" id="kelompok" required>
                                <option value="">-- Pilih Kelompok --</option>
                                <option value="Neraca">Neraca</option>
                                <option value="Penerimaan">Penerimaan</option>
                                <option value="Beban">Beban</option>
                            </select>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Submit
                        </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div class="card-body">
              <?php
              $koneksi = mysqli_connect($servername, $username, $password, $database);
              $data = mysqli_query($koneksi, "SELECT * FROM add_akun");
              $has_data = mysqli_num_rows($data) > 0;
              ?>
              
              <?php if ($has_data): ?>
              <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered table-striped">
                  <thead class="table-primary">
                    <tr>
                      <th>Akun</th>
                      <th>Nama Akun</th>
                      <th>Default</th>
                      <th>Kelompok</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Reset data pointer
                    mysqli_data_seek($data, 0);
                    while ($d = mysqli_fetch_array($data)) {
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($d['id'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($d['nama_akun'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($d['pembayaran'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($d['kelompok'] ?? ''); ?></td>
                      <td>
                        <?php if ($user_role == 'admin'): ?>
                        <form action="delete_akun.php" method="post" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')"> 
                              <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo str_replace('.', '_', $d['id']); ?>"> 
                          <i class="fas fa-edit"></i> Edit
                        </button>
                        
                        <!-- Modal untuk Edit Akun -->
                        <div class="modal fade" id="editModal<?php echo str_replace( '.' , '_' , $d['id']); ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Edit Akun</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="edit_akun.php" method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                            <div class="mb-3">
                                                <label for="namaakun" class="form-label">Nama Akun</label>
                                                <input type="text" class="form-control" name="namaakun" value="<?php echo $d['nama_akun']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="pembayaran" class="form-label">Pembayaran</label>
                                                <select class="form-control" name="pembayaran" required>
                                                    <option value="Default" <?php echo ($d['pembayaran'] == 'Default') ? 'selected' : ''; ?>>Default</option>
                                                    <option value="Debit" <?php echo ($d['pembayaran'] == 'Debit') ? 'selected' : ''; ?>>Debit</option>
                                                    <option value="Kredit" <?php echo ($d['pembayaran'] == 'Kredit') ? 'selected' : ''; ?>>Kredit</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="kelompok" class="form-label">Kelompok</label>
                                                <select class="form-control" name="kelompok" required>
                                                    <option value="Neraca" <?php echo ($d['kelompok'] == 'Neraca') ? 'selected' : ''; ?>>Neraca</option>
                                                    <option value="Penerimaan" <?php echo ($d['kelompok'] == 'Penerimaan') ? 'selected' : ''; ?>>Penerimaan</option>
                                                    <option value="Beban" <?php echo ($d['kelompok'] == 'Beban') ? 'selected' : ''; ?>>Beban</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                      </td>
                    </tr>
                    <?php
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada data Akun</h5>
                <p class="text-muted">Belum ada akun yang terdaftar dalam sistem.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                  <i class="fas fa-plus me-2"></i>Tambah Akun
                </button>
              </div>
              <?php endif; ?>
              
              <?php mysqli_close($koneksi); ?>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php include 'layout/footer.php'; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function printAkun() {
    // Get current URL parameters if any
    const urlParams = new URLSearchParams(window.location.search);
    
    // Build print URL - pointing to same file with print parameter
    let printUrl = window.location.pathname + '?print=true';
    
    // Add existing parameters if needed (for future filtering)
    const params = [];
    for (const [key, value] of urlParams.entries()) {
        if (['kelompok', 'pembayaran', 'orderBy', 'orderDir'].includes(key)) {
            params.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
        }
    }

    // Add parameters to URL if any exist
    if (params.length > 0) {
        printUrl += '&' + params.join('&');
    }

    // Open print window
    window.open(printUrl, '_blank', 'width=1200,height=800');
}

function exportAkun() {
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
        
        for (let j = 0; j < cols.length - 1; j++) { // Exclude action column
            let cellText = cols[j].innerText.replace(/"/g, '""');
            // Clean up action buttons text
            cellText = cellText.replace(/Hapus|Edit/g, '').trim();
            csvRow.push('"' + cellText + '"');
        }
        
        csv += csvRow.join(',') + '\n';
    }
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'data_akun_' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Auto hide alerts after 5 seconds
document.addEventListener("DOMContentLoaded", function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                alert.classList.add('fade');
                setTimeout(function() {
                    alert.remove();
                }, 150);
            }
        }, 5000);
    });

    // Reset form when modal is closed
    const modal = document.getElementById('myModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('formTambahAkun').reset();
        });
    }
});
</script>
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Initialize datatable only if table exists
    const table = document.getElementById("datatablesSimple");
    if (table) {
      new simpleDatatables.DataTable("#datatablesSimple", {
        searchable: true,
        sortable: true,
        fixedHeight: true,
        perPage: 5,
        perPageSelect: [5, 10, 15, 20, 25, 50, 100],
        labels: {
          placeholder: "Cari data...",
          perPage: "data per halaman",
          noRows: "Tidak ada data",
          info: "Menampilkan {start} sampai {end} dari {rows} data",
        }
      });
    }
  });
</script>
<style>
    #layoutSidenav {
      display: flex;
      width : 100%;
  }
</style>