<?php 
    include 'database/db.php';
    $id = $_POST['id'];
    $akun = $_POST['akun'];

    $koneksi = mysqli_connect($servername, $username, $password, $database);
    // Check the connection
    if (!$koneksi) {
            die("Connection failed: " . mysqli_connect_error());
        }
 
    $sql = "INSERT INTO akun (id , akun) VALUES('$id','$akun')";
    if (mysqli_query($koneksi, $sql)) {
     echo "New record created successfully";
    }
    else {
     echo "Error: " . $sql . "<br>" . mysqli_error($koneksi);}
    
    mysqli_close($koneksi);

    header("location:add_admin.php");

?>