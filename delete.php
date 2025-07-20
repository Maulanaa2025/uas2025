<?php
// Koneksi ke database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'yuzril';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses hapus data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM mahasiswa WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        header("Location: index.php?status=deleted");
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>