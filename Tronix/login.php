<?php
// auth/login.php
require_once __DIR__.'/conexion.php';
require_once __DIR__.'/csrf.php';
session_start();
csrf_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=account');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
    header('Location: ../index.php?err=cred');
    exit;
}

$stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    header('Location: ../index.php');
    exit;
} else {
    header('Location: ../index.php?err=cred');
    exit;
}
