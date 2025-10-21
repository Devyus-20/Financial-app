<?php
// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect ke URL tertentu
 * @param string $url URL tujuan redirect
 */
function redirect_to($url = '')
{
    // Tambahkan validasi URL untuk keamanan
    if (empty($url)) {
        $url = 'index.php';
    }
    
    // Pastikan tidak ada output sebelum header
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit();
    } else {
        // Fallback jika header sudah dikirim
        echo "<script>window.location.href = '$url';</script>";
        exit();
    }
}

/**
 * Cek login user dan validasi role
 * @param array $allowed_roles Array role yang diizinkan mengakses halaman
 */
function cek_login($allowed_roles = array())
{
    // Pastikan parameter adalah array
    if (!is_array($allowed_roles)) {
        $allowed_roles = array($allowed_roles);
    }
    
    // Cek apakah user sudah login
    if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
        redirect_to("index.php");
        return false;
    }
    
    // Jika tidak ada role yang dispesifikasi, hanya cek login
    if (empty($allowed_roles)) {
        return true;
    }
    
    // Cek apakah role user ada dalam daftar yang diizinkan
    $user_role = strtolower(trim($_SESSION['role']));
    $allowed_roles = array_map('strtolower', array_map('trim', $allowed_roles));
    
    if (!in_array($user_role, $allowed_roles)) {
        // User tidak memiliki akses, redirect dengan pesan error
        $_SESSION['error_message'] = 'Anda tidak memiliki akses ke halaman ini.';
        redirect_to("dashboard.php");
        return false;
    }
    
    return true;
}

/**
 * Mendapatkan role user yang sedang login
 * @return string|false Role user atau false jika tidak login
 */
function get_role()
{
    // Cek apakah user sudah login
    if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
        return false;
    }
    
    // Daftar role yang valid - sesuaikan dengan sistem Anda
    $valid_roles = [
        'admin', 
        'dashboard_manager', 
        'manager' // untuk backward compatibility
    ];
    
    $user_role = strtolower(trim($_SESSION['role']));
    
    // Cek apakah role valid
    if (in_array($user_role, $valid_roles)) {
        return $user_role;
    } else {
        return false; // Role tidak dikenali
    }
}

/**
 * Cek apakah user adalah admin
 * @return bool
 */
function is_admin()
{
    $role = get_role();
    return ($role === 'admin');
}

/**
 * Cek apakah user adalah dashboard manager
 * @return bool
 */
function is_dashboard_manager()
{
    $role = get_role();
    return ($role === 'dashboard_manager' || $role === 'manager');
}

/**
 * Mendapatkan nama user yang sedang login
 * @return string|false Nama user atau false jika tidak login
 */
function get_username()
{
    if (isset($_SESSION['username'])) {
        return $_SESSION['username'];
    } elseif (isset($_SESSION['first_name'])) {
        return $_SESSION['first_name'];
    } else {
        return false;
    }
}

/**
 * Mendapatkan ID user yang sedang login
 * @return int|false ID user atau false jika tidak login
 */
function get_user_id()
{
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    } elseif (isset($_SESSION['id'])) {
        return $_SESSION['id'];
    } else {
        return false;
    }
}

/**
 * Logout user dan hapus semua session
 */
function logout_user()
{
    // Hancurkan semua session
    session_unset();
    session_destroy();
    
    // Redirect ke halaman login
    redirect_to('index.php');
}

/**
 * Cek apakah user sudah login
 * @return bool
 */
function is_logged_in()
{
    return (isset($_SESSION['id']) && isset($_SESSION['role']));
}

/**
 * Set pesan flash untuk ditampilkan di halaman berikutnya
 * @param string $message Pesan yang akan ditampilkan
 * @param string $type Tipe pesan (success, error, warning, info)
 */
function set_flash_message($message, $type = 'info')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Mendapatkan dan menghapus pesan flash
 * @return array|false Array dengan message dan type, atau false jika tidak ada
 */
function get_flash_message()
{
    if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        
        // Hapus pesan setelah diambil
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return array('message' => $message, 'type' => $type);
    }
    
    return false;
}

/**
 * Fungsi untuk menampilkan pesan flash di HTML
 */
function display_flash_message()
{
    $flash = get_flash_message();
    if ($flash) {
        $alert_class = '';
        switch ($flash['type']) {
            case 'success':
                $alert_class = 'alert-success';
                break;
            case 'error':
                $alert_class = 'alert-danger';
                break;
            case 'warning':
                $alert_class = 'alert-warning';
                break;
            default:
                $alert_class = 'alert-info';
                break;
        }
        
        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}
?>