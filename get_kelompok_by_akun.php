<?php
/**
 * Function to get kelompok data based on selected akun
 * @param mysqli $koneksi Database connection object
 * @param string $akun The selected akun name
 * @return array|null Returns array of kelompok data or null if error
 */
function get_kelompok_by_akun($koneksi, $akun) {
    // Sanitize input
    $akun = mysqli_real_escape_string($koneksi, $akun);
    
    // Query to get kelompok data based on akun
    $query = "SELECT kelompok FROM add_akun WHERE nama_akun = '$akun'";
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        return null; // Return null if query failed
    }
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['kelompok'];
    } else {
        return null; // Return null if no data found
    }
}

/**
 * Function to get all kelompok options
 * @param mysqli $koneksi Database connection object
 * @return array Returns array of all kelompok data
 */
function get_all_kelompok($koneksi) {
    $query = "SELECT DISTINCT kelompok FROM add_akun ORDER BY kelompok";
    $result = mysqli_query($koneksi, $query);
    
    $kelompok_list = array();
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $kelompok_list[] = $row['kelompok'];
        }
    }
    
    return $kelompok_list;
}
?>