<?php
    $conn = mysqli_connect('localhost', 'root', '', 'donor');
    
    if (!$conn) {
        die('Koneksi gagal: ' . mysqli_connect_error());
    }
?>