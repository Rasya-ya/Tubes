<?php
session_start();
require 'koneksi.php';

$error          = '';
$nama_lengkap   = '';
$email          = '';
$no_hp          = '';
$tanggal_lahir  = '';
$golongan_darah = '';
$rhesus         = '';
$berat_badan    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap   = trim($_POST['nama'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $password       = trim($_POST['password'] ?? '');
    $no_hp          = trim($_POST['no_hp'] ?? '');
    $tanggal_lahir  = trim($_POST['tanggal_lahir'] ?? '');
    $golongan_darah = trim($_POST['golongan_darah'] ?? '');
    $rhesus         = trim($_POST['rhesus'] ?? '');
    $berat_badan    = trim($_POST['berat_badan'] ?? '');

    // Validasi
    if ($nama_lengkap === '' || $email === '' || $password === '' ||
        $no_hp === '' || $tanggal_lahir === '' || $golongan_darah === '' ||
        $rhesus === '' || $berat_badan === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/^(\+62|62|0)8[0-9]{8,12}$/', $no_hp)) {
        $error = 'Nomor HP tidak valid';
    } elseif (!in_array($golongan_darah, ['A', 'B', 'AB', 'O'], true)) {
        $error = 'Golongan darah tidak valid.';
    } elseif (!in_array($rhesus, ['+', '-'], true)) {
        $error = 'Rhesus tidak valid.';
    } elseif (!is_numeric($berat_badan) || $berat_badan <= 0 || $berat_badan > 300) {
        $error = 'Berat badan tidak valid.';
    } else {
        $tgl = DateTime::createFromFormat('Y-m-d', $tanggal_lahir);

        if (!$tgl || $tgl->format('Y-m-d') != $tanggal_lahir || $tgl > new DateTime('today')) {
            $error = 'Tanggal lahir tidak valid.';
        }
    }

    // Cek email yang sudah terdaftar (WAJIB ===)
    if ($error === '') {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Email sudah terdaftar.';
        }
        mysqli_stmt_close($stmt);
    }

    // Simpan ke database (WAJIB ===)
    if ($error === '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $berat = (float) $berat_badan;

        $stmt = mysqli_prepare($conn, 'INSERT INTO users (nama_lengkap, email, password, no_hp, tanggal_lahir, golongan_darah, rhesus, berat_badan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssssssd', $nama_lengkap, $email, $hash, $no_hp, $tanggal_lahir, $golongan_darah, $rhesus, $berat);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $_SESSION['success'] = 'Pendaftaran berhasil! Silahkan login.';
            header('Location: login.xhtml');
            exit;
        } else {
            $error = 'Terjadi kesalahan saat menyimpan data: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>