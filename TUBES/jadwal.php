<?php
session_start();
require 'koneksi.php';

//cek id kosong ap ga, klo user blm login nanti dialihin ke halaman login.
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit; //hentiin program biar kode bawanya ga ikut jalan klo emg masih kosong.
}

    //tampilin menu & judul halaman
    echo '<p><a href="jadwal.php">Jadwal</a></p>';
    echo '<p><a href="pendaftaran.php">Pendaftaran saya</a></p>';
    echo '<p><a href="logout.php">Logout</a></p>';
    echo '<h1>Jadwal Donor Darah</h1>';

    //jadwal dari database
    $result = mysqli_query($conn, "SELECT j.*, j.kuota - (SELECT COUNT(*) 
    FROM pendaftaran_donor p WHERE p.id_jadwal = j.id_jadwal AND p.status <> 'batal') AS sisa  
    FROM jadwal_donor j ORDER BY tanggal");

    //kerangka tabel & baris judul kolom
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>No</th><th>Lokasi</th><th>Alamat</th><th>Tanggal</th><th>Sisa Kuota</th><th>Aksi</th></tr>';


    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        echo '<td>' . $no . '</td>';

        //htmlspecialchars ini dipake biar karakter htmlnya jdi aman
        echo '<td>' . htmlspecialchars($row['nama_lokasi']) . '</td>'; 
        echo '<td>' . htmlspecialchars($row['alamat']) . '</td>';
        echo '<td>' . htmlspecialchars($row['tanggal']) . '</td>';
        echo '<td>' . $row['sisa'] . ' dari ' . $row['kuota'] . '</td>';
        echo '<td>';
        echo '<form action="daftar.php" method="POST">';

        //input tersembunyi supaya "daftar.php" tahu jadwal mana yang dipilih
        echo '<input type="hidden" name="id_jadwal" value="' . $row['id_jadwal'] . '" />';

        //input catatan user kalau ada
        echo '<input type="text" name="catatan" /> ';
        echo '<button type="submit">Daftar</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
        $no++; //buat no urutnya +1 terus
    }

    //tutup tabel
    echo '</table>';

    //tampil jumlah data yang ditemukan
    echo '<p>Jumlah data: ' . mysqli_num_rows($result) . '</p>';
?>