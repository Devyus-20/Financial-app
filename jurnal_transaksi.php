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

// Query untuk mengambil daftar akun dari tabel add_akun
$query_akun = "SELECT * FROM add_akun";
$result_akun = $koneksi->query($query_akun);

if (!$result_akun) {
    die("Query gagal: " . $koneksi->error);
}

// Query untuk mengambil data dari jurnal_transaksi (tanpa JOIN karena akun sudah dalam format lengkap)
$query_jurnal = "SELECT * FROM jurnal_transaksi";
$result_jurnal = $koneksi->query($query_jurnal);

if (!$result_jurnal) {
    die("Query gagal: " . $koneksi->error);
}

// Generate ID Jurnal Otomatis berdasarkan AUTO_INCREMENT
function getNextJurnalId($koneksi) {
    // Ambil AUTO_INCREMENT value berikutnya
    $query = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jurnal_transaksi'";
    $result = $koneksi->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nextId = $row['AUTO_INCREMENT'];
    } else {
        // Fallback: ambil ID terakhir + 1
        $query2 = "SELECT MAX(id_jurnal) as max_id FROM jurnal_transaksi";
        $result2 = $koneksi->query($query2);
        if ($result2 && $result2->num_rows > 0) {
            $row2 = $result2->fetch_assoc();
            $nextId = ($row2['max_id'] ?? 0) + 1;
        } else {
            $nextId = 1;
        }
    }
    
    return $nextId;
}

$autoJurnalId = getNextJurnalId($koneksi);

// Ambil data jurnal berdasarkan ID jika ada parameter GET
$editData = null;
if (isset($_GET['id_jurnal'])) {
    $id_jurnal = $_GET['id_jurnal'];
    $query_edit = "SELECT * FROM jurnal_transaksi WHERE id_jurnal = '$id_jurnal'";
    $result_edit = $koneksi->query($query_edit);
    
    if ($result_edit && $result_edit->num_rows > 0) {
        $editData = $result_edit->fetch_assoc();
    }
}

// Query untuk mengambil data dengan status posting
$query_jurnal_posting = "SELECT * FROM jurnal_transaksi WHERE status = 'posting'";
$result_jurnal_posting = $koneksi->query($query_jurnal_posting);

if (!$result_jurnal_posting) {
    die("Query gagal: " . $koneksi->error);
}

// Query untuk mengambil data dengan status pending
$query_jurnal_pending = "SELECT * FROM jurnal_transaksi WHERE status = 'pending'";
$result_jurnal_pending = $koneksi->query($query_jurnal_pending);

if (!$result_jurnal_pending) {
    die("Query gagal: " . $koneksi->error);
}

// Query untuk mengambil semua data jurnal untuk buku besar
$query_buku_besar = "SELECT * FROM jurnal_transaksi ORDER BY tgl_trans, id_jurnal";
$result_buku_besar = $koneksi->query($query_buku_besar);

if (!$result_buku_besar) {
    die("Query gagal: " . $koneksi->error);
}

include 'layout/header.php';
?>

<style>
    .searchable-dropdown {
        position: relative;
    }
    
    .dropdown-search-wrapper {
        position: relative;
        width: 100%;
    }
    
    .dropdown-search-input {
        width: 100%;
        padding: 8px 30px 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
        cursor: pointer;
    }
    
    .dropdown-search-input:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .dropdown-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        font-size: 12px;
        color: #6c757d;
    }
    
    .dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    
    .dropdown-option {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    
    .dropdown-option:hover {
        background-color: #f8f9fa;
    }
    
    .dropdown-option:last-child {
        border-bottom: none;
    }
    
    .dropdown-option.selected {
        background-color: #0d6efd;
        color: white;
    }
    
    .no-options {
        padding: 10px 12px;
        color: #6c757d;
        font-style: italic;
    }
</style>

<div id="layoutSidenav">
    <?php include 'layout/sidebar.php'; ?>
    </div>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Jurnal Transaksi</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Semangat Bekerja</li>
                </ol>
                <!-- card table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                                Tambahkan
                            </button>
                    </div>

                    <!-- The Modal -->
                    <div class="modal fade" id="myModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">Jurnal Form</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <!-- Modal body -->
                                <form method="post" action="funct_jurnal.php">
                                    <div class="modal-body">
                                        <label for="id_jurnal">ID Jurnal</label>
                                        <input type="text" name="id_jurnal" id="id_jurnal" value="<?php echo $autoJurnalId; ?>" class="form-control" readonly style="background-color: #f8f9fa; font-weight: bold;"><br>
                                        <input type="date" name="tgl_trans" placeholder="Tanggal Transaksi" class="form-control" required><br>
                                        <input type="text" name="referensi" placeholder="Referensi" class="form-control" required><br>
                                        <input type="text" name="deskripsi" placeholder="Deskripsi" class="form-control" required><br>
                                        
                                       <!-- Dropdown untuk Akun Debit dengan Pencarian -->
                                        <label for="akun_debit">Akun Debit</label>
                                        <div class="searchable-dropdown">
                                            <div class="dropdown-search-wrapper">
                                                <input type="text" 
                                                       id="akun_debit_search" 
                                                       class="dropdown-search-input" 
                                                       placeholder="Ketik untuk mencari akun debit..."
                                                       autocomplete="off">
                                                <span class="dropdown-arrow">▼</span>
                                                <div class="dropdown-options" id="akun_debit_options">
                                                    <?php 
                                                    $result_akun->data_seek(0);
                                                    while ($row = $result_akun->fetch_assoc()): 
                                                    ?>
                                                        <div class="dropdown-option" 
                                                             data-value="<?= $row['id'] ?> - <?= $row['nama_akun'] ?>" 
                                                             data-kelompok="<?= $row['kelompok'] ?>">
                                                            <?= $row['id'] ?> - <?= $row['nama_akun'] ?>
                                                        </div>
                                                    <?php endwhile; ?>
                                                </div>
                                            </div>
                                            <input type="hidden" name="akun_debit" id="akun_debit" required>
                                        </div>
                                        <br>

                                        <!-- Dropdown untuk Akun Kredit dengan Pencarian -->
                                        <label for="akun_kredit">Akun Kredit</label>
                                        <div class="searchable-dropdown">
                                            <div class="dropdown-search-wrapper">
                                                <input type="text" 
                                                       id="akun_kredit_search" 
                                                       class="dropdown-search-input" 
                                                       placeholder="Ketik untuk mencari akun kredit..."
                                                       autocomplete="off">
                                                <span class="dropdown-arrow">▼</span>
                                                <div class="dropdown-options" id="akun_kredit_options">
                                                    <?php 
                                                    $result_akun->data_seek(0);
                                                    while ($row = $result_akun->fetch_assoc()): 
                                                    ?>
                                                        <div class="dropdown-option" 
                                                             data-value="<?= $row['id'] ?> - <?= $row['nama_akun'] ?>" 
                                                             data-kelompok="<?= $row['kelompok'] ?>">
                                                            <?= $row['id'] ?> - <?= $row['nama_akun'] ?>
                                                        </div>
                                                    <?php endwhile; ?>
                                                </div>
                                            </div>
                                            <input type="hidden" name="akun_kredit" id="akun_kredit" required>
                                        </div>
                                        <br>

                                        <!-- Input untuk Kelompok Debit (Auto-filled) -->
                                        <label for="kelompok_debit">Kelompok Debit</label>
                                        <input type="text" name="kelompok_debit" id="kelompok_debit" class="form-control" readonly>
                                        <br>
                                        
                                        <!-- Input untuk Kelompok Kredit (Auto-filled) -->
                                        <label for="kelompok_kredit">Kelompok Kredit</label>
                                        <input type="text" name="kelompok_kredit" id="kelompok_kredit" class="form-control" readonly>
                                        <br>

                                        <input type="number" name="jumlah" placeholder="Jumlah" class="form-control" required><br>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" name="status" value="posting">Posting</button>
                                        <button type="submit" class="btn btn-success" name="status" value="pending">Pending</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <!-- Modal Edit -->
                    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="funct_jurnal.php">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edit Jurnal</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_jurnal" id="edit-id-jurnal">

                                        <label for="edit-tgl-trans">Tanggal Transaksi</label>
                                        <input type="date" name="tgl_trans" id="edit-tgl-trans" class="form-control" required><br>

                                        <label for="edit-referensi">Referensi</label>
                                        <input type="text" name="referensi" id="edit-referensi" class="form-control" required><br>

                                        <label for="edit-deskripsi">Deskripsi</label>
                                        <input type="text" name="deskripsi" id="edit-deskripsi" class="form-control" required><br>

                                        <!-- Edit Akun Debit dengan Pencarian -->
                                        <label for="edit-akun-debit">Akun Debit</label>
                                        <div class="searchable-dropdown">
                                            <div class="dropdown-search-wrapper">
                                                <input type="text" 
                                                       id="edit_akun_debit_search" 
                                                       class="dropdown-search-input" 
                                                       placeholder="Ketik untuk mencari akun debit..."
                                                       autocomplete="off">
                                                <span class="dropdown-arrow">▼</span>
                                                <div class="dropdown-options" id="edit_akun_debit_options">
                                                    <?php 
                                                    $result_akun->data_seek(0);
                                                    while ($row = $result_akun->fetch_assoc()): 
                                                    ?>
                                                        <div class="dropdown-option" 
                                                             data-value="<?= $row['id'] ?> - <?= $row['nama_akun'] ?>" 
                                                             data-kelompok="<?= $row['kelompok'] ?>">
                                                            <?= $row['id'] ?> - <?= $row['nama_akun'] ?>
                                                        </div>
                                                    <?php endwhile; ?>
                                                </div>
                                            </div>
                                            <input type="hidden" name="akun_debit" id="edit-akun-debit" required>
                                        </div>
                                        <br>

                                        <!-- Edit Akun Kredit dengan Pencarian -->
                                        <label for="edit-akun-kredit">Akun Kredit</label>
                                        <div class="searchable-dropdown">
                                            <div class="dropdown-search-wrapper">
                                                <input type="text" 
                                                       id="edit_akun_kredit_search" 
                                                       class="dropdown-search-input" 
                                                       placeholder="Ketik untuk mencari akun kredit..."
                                                       autocomplete="off">
                                                <span class="dropdown-arrow">▼</span>
                                                <div class="dropdown-options" id="edit_akun_kredit_options">
                                                    <?php 
                                                    $result_akun->data_seek(0);
                                                    while ($row = $result_akun->fetch_assoc()): 
                                                    ?>
                                                        <div class="dropdown-option" 
                                                             data-value="<?= $row['id'] ?> - <?= $row['nama_akun'] ?>" 
                                                             data-kelompok="<?= $row['kelompok'] ?>">
                                                            <?= $row['id'] ?> - <?= $row['nama_akun'] ?>
                                                        </div>
                                                    <?php endwhile; ?>
                                                </div>
                                            </div>
                                            <input type="hidden" name="akun_kredit" id="edit-akun-kredit" required>
                                        </div>
                                        <br>

                                        <label for="edit-kelompok-debit">Kelompok Debit</label>
                                        <input type="text" name="kelompok_debit" id="edit-kelompok-debit" class="form-control" readonly><br>

                                        <label for="edit-kelompok-kredit">Kelompok Kredit</label>
                                        <input type="text" name="kelompok_kredit" id="edit-kelompok-kredit" class="form-control" readonly><br>

                                        <label for="edit-jumlah">Jumlah</label>
                                        <input type="number" name="jumlah" id="edit-jumlah" class="form-control" required><br>

                                        <label for="edit-status">Status</label>
                                        <select name="status" id="edit-status" class="form-control" required>
                                            <option value="posting">Posting</option>
                                            <option value="pending">Pending</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Data Posting</h5>
                        </div>
                        <div class="card-body">
                            <table id="datatablesPosting" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Id Jurnal</th>
                                        <th>Tanggal Transaksi</th>
                                        <th>Deskripsi</th>
                                        <th>Referensi</th>
                                        <th>Akun Debit</th>
                                        <th>Akun Kredit</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Kelompok Kredit</th>
                                        <th>Kelompok Debit</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($d = $result_jurnal_posting->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $d['id_jurnal']; ?></td>
                                        <td><?php echo $d['tgl_trans']; ?></td>
                                        <td><?php echo $d['deskripsi']; ?></td>
                                        <td><?php echo $d['referensi']; ?></td>
                                        <td><?php echo $d['akun_debit']; ?></td>
                                        <td><?php echo $d['akun_kredit']; ?></td>
                                        <td><?php echo $d['jumlah']; ?></td>
                                        <td><?php echo $d['status']; ?></td>
                                        <td><?php echo $d['kelompok_kredit'];?></td>
                                        <td><?php echo $d['kelompok_debit'];?></td>
                                        <td>
                                            <form action="funct_jurnal.php" method="post" style="margin: 0;">
                                                <input type="hidden" name="delete_id" value="<?php echo $d['id_jurnal']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Data Pending</h5>
                        </div>
                        <div class="card-body">
                            <table id="datatablesPending" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Id Jurnal</th>
                                        <th>Tanggal Transaksi</th>
                                        <th>Deskripsi</th>
                                        <th>Referensi</th>
                                        <th>Akun Debit</th>
                                        <th>Akun Kredit</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Kelompok Kredit</th>
                                        <th>Kelompok Debit</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($d = $result_jurnal_pending->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $d['id_jurnal']; ?></td>
                                        <td><?php echo $d['tgl_trans']; ?></td>
                                        <td><?php echo $d['deskripsi']; ?></td>
                                        <td><?php echo $d['referensi']; ?></td>
                                        <td><?php echo $d['akun_debit']; ?></td>
                                        <td><?php echo $d['akun_kredit']; ?></td>
                                        <td><?php echo $d['jumlah']; ?></td>
                                        <td><?php echo $d['status']; ?></td>
                                        <td><?php echo $d['kelompok_kredit'];?></td>
                                        <td><?php echo $d['kelompok_debit'];?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form action="funct_jurnal.php" method="post" style="margin: 0;">
                                                    <input type="hidden" name="delete_id" value="<?php echo $d['id_jurnal']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                                <button type="button" class="btn btn-warning btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="<?php echo $d['id_jurnal']; ?>"
                                                    data-tgl_trans="<?php echo $d['tgl_trans']; ?>"
                                                    data-referensi="<?php echo $d['referensi']; ?>"
                                                    data-deskripsi="<?php echo $d['deskripsi']; ?>"
                                                    data-akun_debit="<?php echo $d['akun_debit']; ?>"
                                                    data-akun_kredit="<?php echo $d['akun_kredit']; ?>"
                                                    data-jumlah="<?php echo $d['jumlah']; ?>"
                                                    data-status="<?php echo $d['status']; ?>"
                                                    data-kelompok-debit="<?php echo $d['kelompok_debit']; ?>"
                                                    data-kelompok-kredit="<?php echo $d['kelompok_kredit']; ?>"
                                                    >Edit
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabel Data jurnal -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Data Jurnal</h5>
                        </div>
                        <div class="card-body">
                            <table id="datatablesBukuBesar" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal Transaksi</th>
                                        <th>No Perkiraan</th>
                                        <th>Nama Perkiraan</th>
                                        <th>Debit</th>
                                        <th>Kredit</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Reset pointer untuk membaca ulang data
                                    $result_buku_besar->data_seek(0);
                                    while ($row = $result_buku_besar->fetch_assoc()): 
                                        // Parsing akun debit untuk mendapatkan ID dan nama
                                        $akun_debit_parts = explode(' - ', $row['akun_debit'], 2);
                                        $id_debit = $akun_debit_parts[0];
                                        $nama_debit = isset($akun_debit_parts[1]) ? $akun_debit_parts[1] : '';
                                        
                                        // Parsing akun kredit untuk mendapatkan ID dan nama
                                        $akun_kredit_parts = explode(' - ', $row['akun_kredit'], 2);
                                        $id_kredit = $akun_kredit_parts[0];
                                        $nama_kredit = isset($akun_kredit_parts[1]) ? $akun_kredit_parts[1] : '';
                                    ?>
                                        <!-- Baris untuk akun DEBIT -->
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($row['tgl_trans'])); ?></td>
                                            <td><?php echo $id_debit; ?></td>
                                            <td><?php echo $nama_debit; ?></td>
                                            <td style="text-align: right; font-weight: bold;">
                                                Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?>
                                            </td>
                                            <td style="text-align: right;">-</td>
                                            <td><?php echo $row['deskripsi']; ?></td>
                                        </tr>
                                        
                                        <!-- Baris untuk akun KREDIT -->
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($row['tgl_trans'])); ?></td>
                                            <td><?php echo $id_kredit; ?></td>
                                            <td><?php echo $nama_kredit; ?></td>
                                            <td style="text-align: right;">-</td>
                                            <td style="text-align: right; font-weight: bold;">
                                                Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?>
                                            </td>
                                            <td><?php echo $row['deskripsi']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'layout/footer.php'; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script>
    // Fungsi untuk membuat dropdown searchable
    function createSearchableDropdown(searchInputId, optionsId, hiddenInputId, kelompokInputId) {
        const searchInput = document.getElementById(searchInputId);
        const optionsContainer = document.getElementById(optionsId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const kelompokInput = document.getElementById(kelompokInputId);
        const options = Array.from(optionsContainer.children);
        
        // Event untuk menampilkan dropdown saat diklik
        searchInput.addEventListener('click', function() {
            optionsContainer.style.display = 'block';
            filterOptions('');
        });
        
        // Event untuk pencarian
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterOptions(searchTerm);
            optionsContainer.style.display = 'block';
        });
        
        // Event untuk memilih option
        optionsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('dropdown-option')) {
                const value = e.target.getAttribute('data-value');
                const kelompok = e.target.getAttribute('data-kelompok');
                
                searchInput.value = value;
                hiddenInput.value = value;
                if (kelompokInput) {
                    kelompokInput.value = kelompok;
                }
                
                optionsContainer.style.display = 'none';
                
                // Remove selected class from all options
                options.forEach(opt => opt.classList.remove('selected'));
                e.target.classList.add('selected');
            }
        });
        
        // Fungsi untuk filter options
        function filterOptions(searchTerm) {
            let hasVisibleOptions = false;
            
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    option.style.display = 'block';
                    hasVisibleOptions = true;
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Tampilkan pesan jika tidak ada opsi yang cocok
            let noOptionsDiv = optionsContainer.querySelector('.no-options');
            if (!hasVisibleOptions) {
                if (!noOptionsDiv) {
                    noOptionsDiv = document.createElement('div');
                    noOptionsDiv.className = 'no-options';
                    noOptionsDiv.textContent = 'Tidak ada data yang cocok';
                    optionsContainer.appendChild(noOptionsDiv);
                }
                noOptionsDiv.style.display = 'block';
            } else {
                if (noOptionsDiv) {
                    noOptionsDiv.style.display = 'none';
                }
            }
        }
        
        // Event untuk menutup dropdown saat klik di luar
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !optionsContainer.contains(e.target)) {
                optionsContainer.style.display = 'none';
            }
        });
        
        // Event keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            const visibleOptions = Array.from(optionsContainer.children).filter(
                opt => opt.style.display !== 'none' && opt.classList.contains('dropdown-option')
            );
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                optionsContainer.style.display = 'block';
                const currentSelected = optionsContainer.querySelector('.selected');
                if (currentSelected) {
                    currentSelected.classList.remove('selected');
                    const nextOption = visibleOptions[visibleOptions.indexOf(currentSelected) + 1] || visibleOptions[0];
                    nextOption.classList.add('selected');
                } else if (visibleOptions.length > 0) {
                    visibleOptions[0].classList.add('selected');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const currentSelected = optionsContainer.querySelector('.selected');
                if (currentSelected) {
                    currentSelected.classList.remove('selected');
                    const prevOption = visibleOptions[visibleOptions.indexOf(currentSelected) - 1] || visibleOptions[visibleOptions.length - 1];
                    prevOption.classList.add('selected');
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const selectedOption = optionsContainer.querySelector('.selected');
                if (selectedOption) {
                    selectedOption.click();
                }
            } else if (e.key === 'Escape') {
                optionsContainer.style.display = 'none';
            }
        });
    }
    
    // Inisialisasi dropdown searchable untuk form tambah
    document.addEventListener('DOMContentLoaded', function() {
        // Dropdown untuk akun debit (form tambah)
        createSearchableDropdown(
            'akun_debit_search',
            'akun_debit_options', 
            'akun_debit',
            'kelompok_debit'
        );
        
        // Dropdown untuk akun kredit (form tambah)
        createSearchableDropdown(
            'akun_kredit_search',
            'akun_kredit_options',
            'akun_kredit', 
            'kelompok_kredit'
        );
        
        // Dropdown untuk akun debit (form edit)
        createSearchableDropdown(
            'edit_akun_debit_search',
            'edit_akun_debit_options',
            'edit-akun-debit',
            'edit-kelompok-debit'
        );
        
        // Dropdown untuk akun kredit (form edit)
        createSearchableDropdown(
            'edit_akun_kredit_search',
            'edit_akun_kredit_options',
            'edit-akun-kredit',
            'edit-kelompok-kredit'
        );
        
        // Event listener untuk modal edit
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Ambil data dari atribut data-*
            const id = button.getAttribute('data-id');
            const tglTrans = button.getAttribute('data-tgl_trans');
            const referensi = button.getAttribute('data-referensi');
            const deskripsi = button.getAttribute('data-deskripsi');
            const akunDebit = button.getAttribute('data-akun_debit');
            const akunKredit = button.getAttribute('data-akun_kredit');
            const jumlah = button.getAttribute('data-jumlah');
            const status = button.getAttribute('data-status');
            const kelompokDebit = button.getAttribute('data-kelompok-debit');
            const kelompokKredit = button.getAttribute('data-kelompok-kredit');
            
            // Isi form dengan data
            document.getElementById('edit-id-jurnal').value = id;
            document.getElementById('edit-tgl-trans').value = tglTrans;
            document.getElementById('edit-referensi').value = referensi;
            document.getElementById('edit-deskripsi').value = deskripsi;
            document.getElementById('edit-jumlah').value = jumlah;
            document.getElementById('edit-status').value = status;
            
            // Set nilai untuk dropdown akun debit
            document.getElementById('edit_akun_debit_search').value = akunDebit;
            document.getElementById('edit-akun-debit').value = akunDebit;
            document.getElementById('edit-kelompok-debit').value = kelompokDebit;
            
            // Set nilai untuk dropdown akun kredit
            document.getElementById('edit_akun_kredit_search').value = akunKredit;
            document.getElementById('edit-akun-kredit').value = akunKredit;
            document.getElementById('edit-kelompok-kredit').value = kelompokKredit;
        });
        
        // Inisialisasi DataTables
        if (document.getElementById('datatablesPosting')) {
            const dataTablePosting = new simpleDatatables.DataTable('#datatablesPosting', {
                searchable: true,
                fixedHeight: true,
                perPage: 10,
                labels: {
                    placeholder: "Cari data posting...",
                    perPage: "Data per halaman",
                    noRows: "Tidak ada data posting",
                    info: "Menampilkan {start} sampai {end} dari {rows} data",
                }
            });
        }
        
        if (document.getElementById('datatablesPending')) {
            const dataTablePending = new simpleDatatables.DataTable('#datatablesPending', {
                searchable: true,
                fixedHeight: true,
                perPage: 10,
                labels: {
                    placeholder: "Cari data pending...",
                    perPage: "Data per halaman", 
                    noRows: "Tidak ada data pending",
                    info: "Menampilkan {start} sampai {end} dari {rows} data",
                }
            });
        }
        
        if (document.getElementById('datatablesBukuBesar')) {
            const dataTableBukuBesar = new simpleDatatables.DataTable('#datatablesBukuBesar', {
                searchable: true,
                fixedHeight: true,
                perPage: 15,
                labels: {
                    placeholder: "Cari data jurnal...",
                    perPage: "Data per halaman",
                    noRows: "Tidak ada data buku besar", 
                    info: "Menampilkan {start} sampai {end} dari {rows} data",
                }
            });
        }
        
        // Reset form saat modal ditutup
        const modals = ['myModal', 'editModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function() {
                    // Reset form
                    const form = modal.querySelector('form');
                    if (form) {
                        form.reset();
                    }
                    
                    // Reset dropdown values
                    const searchInputs = modal.querySelectorAll('.dropdown-search-input');
                    const hiddenInputs = modal.querySelectorAll('input[type="hidden"]');
                    const kelompokInputs = modal.querySelectorAll('input[readonly]');
                    
                    searchInputs.forEach(input => input.value = '');
                    hiddenInputs.forEach(input => input.value = '');
                    kelompokInputs.forEach(input => {
                        if (input.name && (input.name.includes('kelompok') || input.id.includes('kelompok'))) {
                            input.value = '';
                        }
                    });
                    
                    // Hide all dropdown options
                    const optionsContainers = modal.querySelectorAll('.dropdown-options');
                    optionsContainers.forEach(container => {
                        container.style.display = 'none';
                        // Remove selected class from all options
                        const options = container.querySelectorAll('.dropdown-option');
                        options.forEach(opt => opt.classList.remove('selected'));
                    });
                });
            }
        });
        
        // Validasi form sebelum submit
        const forms = document.querySelectorAll('form[action="funct_jurnal.php"]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const akunDebit = form.querySelector('input[name="akun_debit"]');
                const akunKredit = form.querySelector('input[name="akun_kredit"]');
                
                if (akunDebit && !akunDebit.value) {
                    e.preventDefault();
                    alert('Silakan pilih akun debit');
                    return false;
                }
                
                if (akunKredit && !akunKredit.value) {
                    e.preventDefault();
                    alert('Silakan pilih akun kredit');
                    return false;
                }
                
                // Validasi akun debit dan kredit tidak boleh sama
                if (akunDebit && akunKredit && akunDebit.value === akunKredit.value) {
                    e.preventDefault();
                    alert('Akun debit dan kredit tidak boleh sama');
                    return false;
                }
            });
        });
        
        // Konfirmasi hapus
        const deleteButtons = document.querySelectorAll('button[type="submit"].btn-danger');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.preventDefault();
                }
            });
        });
    });
    
    // Fungsi untuk format angka (opsional, bisa digunakan untuk format input jumlah)
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Event listener untuk format input jumlah (opsional)
    document.addEventListener('DOMContentLoaded', function() {
        const jumlahInputs = document.querySelectorAll('input[name="jumlah"]');
        jumlahInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value) {
                    // Format angka saat kehilangan fokus
                    const value = this.value.replace(/\./g, '');
                    if (!isNaN(value)) {
                        this.setAttribute('data-original-value', value);
                        // Note: Tidak mengubah nilai karena type="number" memerlukan format standar
                    }
                }
            });
        });
    });
</script>