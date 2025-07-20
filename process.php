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

// Proses CREATE
if ($_GET['action'] == 'create') {
    $npm = mysqli_real_escape_string($conn, $_POST['npm']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $program_studi = mysqli_real_escape_string($conn, $_POST['program_studi']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    $query = "INSERT INTO mahasiswa (npm, nama, program_studi, email, alamat) 
              VALUES ('$npm', '$nama', '$program_studi', '$email', '$alamat')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: index.php?status=created");
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}

// Proses UPDATE
if ($_GET['action'] == 'update') {
    $id = $_POST['id'];
    $npm = mysqli_real_escape_string($conn, $_POST['npm']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $program_studi = mysqli_real_escape_string($conn, $_POST['program_studi']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    $query = "UPDATE mahasiswa SET 
              npm = '$npm', 
              nama = '$nama', 
              program_studi = '$program_studi', 
              email = '$email', 
              alamat = '$alamat' 
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        header("Location: index.php?status=updated");
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>