<?php
require_once 'conexion.php';
session_start();

// 🚫 Verificar sesión activa
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$user_id = intval($_SESSION['user_id']);
$post_id = intval($_POST['post_id'] ?? 0);

if ($post_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID de publicación inválido"]);
    exit;
}

// ✅ Verificar conexión
if (!$mysqli) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error en conexión"]);
    exit;
}

// 🔍 Verificar si ya existe el like
$check = $mysqli->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$check->bind_param('ii', $user_id, $post_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Quitar el like
    $del = $mysqli->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $del->bind_param('ii', $user_id, $post_id);
    $del->execute();
    echo json_encode(["status" => "ok", "action" => "unliked"]);
} else {
    // Agregar like
    $ins = $mysqli->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $ins->bind_param('ii', $user_id, $post_id);
    $ins->execute();
    echo json_encode(["status" => "ok", "action" => "liked"]);
}
