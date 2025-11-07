<?php
// files/upload_file.php
require_once __DIR__.'/conexion.php';
require_once __DIR__.'/csrf.php';
session_start();
csrf_check();

if (empty($_SESSION['user_id'])) {
    header('Location: index.php?err=login');
    exit;
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: index.php?err=nofile');
    exit;
}

$allowed = [
    'application/pdf' => 'pdf',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'application/zip' => 'zip'
];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
finfo_close($finfo);

if (!array_key_exists($mime, $allowed)) {
    header('Location: index.php?err=filetype');
    exit;
}

if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
    header('Location: index.php?err=filesize');
    exit;
}

$ext = $allowed[$mime];
$filename = uniqid('file_') . '.' . $ext;
$target = __DIR__ . '/../files_store/' . $filename;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
    header('Location: index.php?err=upload');
    exit;
}

// guardar en BD
$stmt = $mysqli->prepare("INSERT INTO files (filename, original_name, uploaded_by) VALUES (?, ?, ?)");
$original = $_FILES['file']['name'];
$uid = $_SESSION['user_id'];
$stmt->bind_param('ssi', $filename, $original, $uid);
$stmt->execute();
$stmt->close();

header('Location: index.php?page=files');
