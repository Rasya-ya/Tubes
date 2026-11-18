<?php
require 'koneksi.php';
wajib_login(); //sebagai penanda belum login
$id = $_SESSION['id']; //melihat id akun yang sedang login

//Menyimpan Perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $foto = null;

    //untuk pengguna memilih file foto
    if (!empty($_FILES['foto']['nama'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    //penanda foto yang diunggah harus memiliki format jpg/png dan maksimal 2mb
    if (!in_array($ext, ['jpg', 'png']) || $_FILES['foto']['size'] > 2000000) {
        <out(false, 'Foto harus jpg/png, maksimal 2 MB ');
    }
    q('UPDATE users SET nama=?,no_hp=?,tgl_lahir=?,golongan_darah=?,rhesus=?,berat_badan=?,alamat=?,foto=COALESCE(?,foto) WHERE id_user=?',
    'sssssissi',
    $_POST['nama'], $_POST['no_hp'], $_POST['tgl_lahir'], $_POST['golongan_darah'],
    $_POST['rhesus'], (int)$_POST['berat_badan'], $_POST['alamat'], $foto, $id);

    out(true, '', rows(q('SELECT id_user,nama,email,no_hp,tgl_lahir,golongan_darah,rhesus,berat_badan,alamat,foto,role FROM users WHERE id_user=?', 'i', $id))[0]);
}