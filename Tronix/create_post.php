<?php
// create_post.php — Guarda publicaciones nuevas
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/csrf.php';

// Verifica sesión
if (empty($_SESSION['user_id'])) {
    header('Location: index.php?err=notlogged');
    exit;
}

// Verifica método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?err=method');
    exit;
}

csrf_check(); // Seguridad CSRF

// Capturar campos del formulario
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$user_id = $_SESSION['user_id'];
$image_name = null;

// Validar contenido
if (strlen($content) < 3) {
    header('Location: index.php?err=empty');
    exit;
}

// 📸 Subir imagen si se incluye
if (!empty($_FILES['image']['name'])) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tmp = $_FILES['image']['tmp_name'];
    $name = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $allowed)) {
        $newName = uniqid('post_', true) . '.' . $ext;
        $target = $uploadDir . $newName;

        if (move_uploaded_file($tmp, $target)) {
            $image_name = $newName;
        }
    }
}

// Guardar publicación
$stmt = $mysqli->prepare("INSERT INTO posts (user_id, title, content, image, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param('isss', $user_id, $title, $content, $image_name);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    header('Location: index.php?msg=posted');
    exit;
} else {
    header('Location: index.php?err=db');
    exit;
}
?>

