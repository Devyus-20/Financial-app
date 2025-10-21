<?php
// Menggunakan session role yang sama dengan dashboard
if (!isset($user_role)) {
    $user_role = $_SESSION['role'] ?? null;
}
?>

<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark bg-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <a class="nav-link" href="dashboard.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>

                    <?php if ($user_role === 'admin'): ?>
                    <div class="sb-sidenav-menu-heading">Data Management</div>
                    <a class="nav-link" href="add_admin.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                        Add Akun
                    </a>
                    <a class="nav-link" href="user.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-tie"></i></div>
                        User
                    </a>
                    <a class="nav-link" href="jurnal_transaksi.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                        Jurnal Transaksi
                    </a>
                    <?php endif; ?>

                    <?php if ($user_role === 'admin' || $user_role === 'manager'): ?>
                    <div class="sb-sidenav-menu-heading">Financial Reports</div>
                    <a class="nav-link" href="buku_besar.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                        Laporan Jurnal
                    </a>
                    <a class="nav-link" href="laporan.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                        Laporan
                    </a>
                    <?php endif; ?>

                    <div class="sb-sidenav-menu-heading">Account</div>
                    <a class="nav-link" href="logout.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                        Logout
                    </a>
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                <?php echo htmlspecialchars($user_role ?? 'Guest'); ?>
            </div>
        </nav>
    </div>