<?php
// appointments/book_appointment.php
require_once __DIR__.'/conexion.php';
require_once __DIR__.'/csrf.php';
session_start();
csrf_check();

if (empty($_SESSION['user_id'])) {
    header('Location: index.php?err=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$date = $_POST['date'] ?? '';
$time_slot = $_POST['time_slot'] ?? '';
$purpose = trim($_POST['purpose'] ?? '');

if (!$date || !$time_slot || !$purpose) {
    header('Location: index.php?err=invalid');
    exit;
}

// check conflict
$stmt = $mysqli->prepare("SELECT id FROM appointments WHERE date = ? AND time_slot = ?");
$stmt->bind_param('ss', $date, $time_slot);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $stmt->close();
    header('Location: index.php?page=nosotros&msg=ocupado');
    exit;
}
$stmt->close();

$stmt = $mysqli->prepare("INSERT INTO appointments (user_id, date, time_slot, purpose) VALUES (?, ?, ?, ?)");
$stmt->bind_param('isss', $user_id, $date, $time_slot, $purpose);
$stmt->execute();
$stmt->close();

header('Location: index.php?page=nosotros&msg=ok');
