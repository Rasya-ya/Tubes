<?php
session_start();
require 'koneksi.php';

//cek id kosong ap ga, klo user blm login nanti dialihin ke halaman login.
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit; //hentiin program biar kode bawanya ga ikut jalan klo emg masih kosong.
}

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //id_user diambil dari session (siapa yang login), supaya user tdk bisa daftar atas nama org lain.
        $id_user   = $_SESSION['id'];

        //trim() untuk buang spasi di awal/akhir & '' untuk memberi nilai kalau datanya tdk ada
        $id_jadwal = trim($_POST['id_jadwal'] ?? '');
        $catatan   = trim($_POST['catatan'] ?? '');

        //validasi id_jadawal tdk boleh kosong & (ctype_digit) harus berupa angka saja.
        if ($id_jadwal === '' || !ctype_digit($id_jadwal)) {
            echo '<p style="color:red;">!! Jadwal tidak valid. !!</p>';
        } else {
            // pengecekan 1: input data user untuk cek syarat donor
            $stmt = mysqli_prepare($conn, 'SELECT tanggal_lahir, berat_badan FROM users WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $id_user); //i=integer
            mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            //hitung umur: tahun sekarang - tahun lahir
            $umur = date('Y') - date('Y', strtotime($user['tanggal_lahir']));

            // pengecekan 2: cek sisa kuota
            $stmt = mysqli_prepare($conn, "SELECT kuota - (SELECT COUNT(*) FROM pendaftaran_donor WHERE id_jadwal = ? AND status <> 'batal') AS sisa FROM jadwal_donor WHERE id_jadwal = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $id_jadwal, $id_jadwal); //ii karna ada 2 tanda yang keduanya integer
            mysqli_stmt_execute($stmt);
            $kuota = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            // pengecekan 3: cek sudah daftar atau belum
            $stmt = mysqli_prepare($conn, 'SELECT id_pendaftaran FROM pendaftaran_donor WHERE id_user = ? AND id_jadwal = ?');
            mysqli_stmt_bind_param($stmt, 'ii', $id_user, $id_jadwal);
            mysqli_stmt_execute($stmt);
            $sudah = mysqli_num_rows(mysqli_stmt_get_result($stmt)); //hitung jumlah baris: 0=belum, >0 = sudah.

            //validasi 3 pengecekan
            //kalau salah 1 gagal, tampil pesan error merah dan tidak disimpan
            if ($umur < 17 || $umur > 60 || $user['berat_badan'] < 45) {
                echo '<p style="color:red;">!! Syarat donor: umur 17-60 tahun dan berat minimal 45 kg. !!</p>';
            } elseif (!$kuota || $kuota['sisa'] < 1) {
                //"!$kuota" = jadwal tidak ditemukan & "sisa < 1" = kuota sdh penuh
                echo '<p style="color:red;">!! Kuota penuh atau jadwal tidak ada. !!</p>';
            } elseif ($sudah > 0) {
                echo '<p style="color:red;">!! Kamu sudah terdaftar di jadwal ini. !!</p>';
            } else {
                //semua syarat terpenuhi
                $stmt = mysqli_prepare($conn, 'INSERT INTO pendaftaran_donor (id_user, id_jadwal, catatan) VALUES (?, ?, ?)');
                mysqli_stmt_bind_param($stmt, 'iis', $id_user, $id_jadwal, $catatan); //"iis" = integer, integer, string (id_usesr, id_jadwal, catatan)
                mysqli_stmt_execute($stmt);

                //tampilin jumlah baris yang berhasil disimpan
                echo '<p>Pendaftaran donor berhasil (' . mysqli_stmt_affected_rows($stmt) . ' baris).</p>';
            }
        }
    } else {
        //buka halaman tanpa lewat form
        echo '<p>Silakan pilih jadwal terlebih dahulu.</p>';
    }

    //link untuk kembali ke daftar jadwal
    echo '<a href="jadwal.php">Kembali</a>';
?>