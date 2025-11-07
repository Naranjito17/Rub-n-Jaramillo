<?php
// register.php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/csrf.php';
session_start();
csrf_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); // ✅ antes decía ../index.php
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';

if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
    header('Location: index.php?err=reg'); // ✅ antes decía ../index.php
    exit;
}

// comprobar si ya existe email
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $stmt->close();
    header('Location: index.php?err=exists'); // ✅ antes decía ../index.php
    exit;
}
$stmt->close();

// insertar usuario
$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $name, $email, $hash);
$ok = $stmt->execute();

if ($ok) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $stmt->close();
    header('Location: index.php'); // ✅
    exit;
} else {
    $stmt->close();
    header('Location: login.php'); // ✅ ya está bien
    exit;
}
?>

