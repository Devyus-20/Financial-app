<?php
session_start(); 

require_once('database/init.php');

// Check if user is logged in and has proper role
if (!isset($_SESSION['role']) || $_SESSION['role'] == 'guest' || empty($_SESSION['role'])) {
    // Redirect to index.php if not logged in or is guest
    header("Location: index.php");
    exit();
}

// Initialize user_role from session (only admin or manager allowed)
$user_role = $_SESSION['role'];

// Additional security check - only allow admin and manager roles
if (!in_array($user_role, ['admin', 'manager'])) {
    header("Location: index.php");
    exit();
}

// Get some basic statistics (adjust queries based on your database structure)
$stats = [];

// Count total entries in buku_besar
$query_buku_besar = "SELECT COUNT(*) as total FROM buku_besar";
$result_buku_besar = mysqli_query($koneksi, $query_buku_besar);
$stats['buku_besar'] = mysqli_fetch_assoc($result_buku_besar)['total'];

// Initialize default values
$stats['jurnal'] = 0;
$stats['users'] = 0;

// Count total jurnal entries (only for admin)
if ($user_role == 'admin') {
    $query_jurnal = "SELECT COUNT(*) as total FROM buku_besar WHERE id_jurnal IS NOT NULL";
    $result_jurnal = mysqli_query($koneksi, $query_jurnal);
    if ($result_jurnal) {
        $stats['jurnal'] = mysqli_fetch_assoc($result_jurnal)['total'];
    }
    
    // Count total users (only for admin)
    $query_users = "SELECT COUNT(*) as total FROM users";
    $result_users = mysqli_query($koneksi, $query_users);
    if ($result_users) {
        $stats['users'] = mysqli_fetch_assoc($result_users)['total'];
    }
}

// Get recent transactions
$query_recent = "SELECT * FROM buku_besar ORDER BY date DESC LIMIT 5";
$result_recent = mysqli_query($koneksi, $query_recent);
$recent_transactions = [];
if ($result_recent) {
    while ($row = mysqli_fetch_assoc($result_recent)) {
        $recent_transactions[] = $row;
    }
}

include 'layout/header.php';
?>

<div id="layoutSidenav">
    <?php include 'layout/sidebar.php'; ?>
    
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Dashboard</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>

                <!-- Welcome message with role indicator -->
                <div class="alert alert-primary mb-4">
                    <h4 class="alert-heading">Selamat Datang, <?php echo isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'User'; ?></h4>
                    <p>Anda login sebagai <strong><?php echo ucfirst($user_role); ?></strong></p>
                    <?php if ($user_role == 'manager'): ?>
                    <hr>
                    <p class="mb-0">Sebagai Manager, Anda memiliki akses ke Buku Besar dan Laporan.</p>
                    <?php elseif ($user_role == 'admin'): ?>
                    <hr>
                    <p class="mb-0">Sebagai Admin, Anda memiliki akses penuh ke semua fitur sistem.</p>
                    <?php endif; ?>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="h5 mb-0"><?php echo number_format($stats['buku_besar']); ?></div>
                                        <div>Total Laporan Jurnal</div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-book fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="buku_besar.php">Lihat Detail</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($user_role == 'admin'): ?>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="h5 mb-0"><?php echo number_format($stats['jurnal']); ?></div>
                                        <div>Total Jurnal</div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-book-open fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="jurnal_transaksi.php">Lihat Detail</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="h5 mb-0"><?php echo number_format($stats['users']); ?></div>
                                        <div>Total Users</div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="user.php">Lihat Detail</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-danger text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="h5 mb-0">Laporan</div>
                                        <div>Generate</div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="laporan.php">Buat Laporan</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Area dengan 2 kolom sejajar -->
                <div class="row">
                    <!-- Kolom Kiri: Quick Access -->
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-bolt me-1"></i>
                                Quick Access
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="buku_besar.php" class="btn btn-outline-primary">
                                        <i class="fas fa-book me-2"></i>Buka Laporan Jurnal
                                    </a>
                                    <a href="laporan.php" class="btn btn-outline-info">
                                        <i class="fas fa-file-alt me-2"></i>Generate Laporan
                                    </a>
                                    <?php if ($user_role == 'admin'): ?>
                                    <a href="jurnal_transaksi.php" class="btn btn-outline-warning">
                                        <i class="fas fa-book-open me-2"></i>Jurnal Transaksi
                                    </a>
                                    <a href="user.php" class="btn btn-outline-success">
                                        <i class="fas fa-users me-2"></i>Kelola User
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Transaksi Terbaru -->
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-clock me-1"></i>
                                Transaksi Terbaru
                            </div>
                            <div class="card-body">
                                <?php if (count($recent_transactions) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Akun</th>
                                                <th>Debit</th>
                                                <th>Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_transactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($transaction['date'])); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['akun']); ?></td>
                                                <td class="text-end">
                                                    <?php echo $transaction['debit'] > 0 ? number_format($transaction['debit'], 0, ',', '.') : '-'; ?>
                                                </td>
                                                <td class="text-end">
                                                    <?php echo $transaction['kredit'] > 0 ? number_format($transaction['kredit'], 0, ',', '.') : '-'; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="buku_besar.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada transaksi</p>
                                    <?php if ($user_role == 'admin'): ?>
                                    <a href="jurnal_transaksi.php" class="btn btn-sm btn-primary">Tambah Transaksi</a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Akses -->
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-info-circle me-1"></i>
                                Informasi Akses
                            </div>
                            <div class="card-body">
                                <?php if ($user_role == 'admin'): ?>
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-crown me-2"></i>Akses Administrator</h5>
                                    <p>Sebagai Admin, Anda memiliki akses penuh ke:</p>
                                    <ul class="mb-0">
                                        <li>Dashboard dengan statistik lengkap</li>
                                        <li>Manajemen User dan penambahan akun</li>
                                        <li>Buku Besar (view dan print)</li>
                                        <li>Jurnal Transaksi (create, read, update, delete)</li>
                                        <li>Generate dan print semua jenis laporan</li>
                                    </ul>
                                </div>
                                <?php elseif ($user_role == 'manager'): ?>
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-chart-line me-2"></i>Akses Manager</h5>
                                    <p>Sebagai Manager, Anda memiliki akses ke:</p>
                                    <ul class="mb-0">
                                        <li>Dashboard dengan informasi Buku Besar</li>
                                        <li>Buku Besar (view dan print laporan)</li>
                                        <li>Generate dan print laporan keuangan</li>
                                        <li>View transaksi terbaru</li>
                                    </ul>
                                    <hr>
                                    <p class="mb-0"><small class="text-muted">
                                        <i class="fas fa-lock me-1"></i>
                                        Fitur admin seperti manajemen user dan jurnal transaksi tidak tersedia untuk role ini.
                                    </small></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include 'layout/footer.php'; ?>
    </div>
</div>

<style>
/* Pastikan layout sidebar normal */
#layoutSidenav {
    display: flex;
    width : 100%
}

#layoutSidenav_content {
    flex: 1;
    margin-left: 0;
    padding: 0;
}

/* Sidebar tetap fixed dengan lebar standar */
.sb-sidenav {
    width: 225px;
    flex-shrink: 0;
}

/* Container menggunakan padding standar Bootstrap */
.container-fluid {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

/* Card styling untuk konsistensi */
.card {
    border: 1px solid rgba(0,0,0,.125);
    border-radius: 0.375rem;
}

.card-header {
    background-color: rgba(0,0,0,.03);
    border-bottom: 1px solid rgba(0,0,0,.125);
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

@media (max-width: 767.98px) {
    .col-xl-3 {
        margin-bottom: 1rem;
    }
}

/* Statistics cards hover effect */
.card.bg-primary:hover,
.card.bg-warning:hover,
.card.bg-success:hover,
.card.bg-danger:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Button styling dalam Quick Access */
.btn-outline-primary:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover,
.btn-outline-success:hover {
    transform: translateX(3px);
    transition: transform 0.2s ease-in-out;
}
</style>

<?php include 'layout/footer.php'; ?>