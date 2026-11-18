
<?php
session_start();
require 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password harus diisi.';
    } else {
        $query = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($query, "s", $email);
        mysqli_stmt_execute($query);

        $result = mysqli_stmt_get_result($query);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>

<h1>Login</h1>

<form method="POST">
    <p>Email:</p>
    <input type="email" name="email">

    <p>Password:</p>
    <input type="password" name="password">

    <button type="submit">Login</button>
</form>
<?php if ($error !== ''): ?>
    <p style="color:red;">
        <?php echo htmlspecialchars($error); ?>
    </p>
<?php endif; ?>

<?php
echo '<a href="register.php">Daftar</a>';
?>
