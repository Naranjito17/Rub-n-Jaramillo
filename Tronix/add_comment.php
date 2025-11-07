<?php
require_once 'conexion.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$post_id = intval($_POST['post_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($post_id <= 0 || $comment === '') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
    exit;
}

// ✅ Insertar comentario
$stmt = $mysqli->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param('iis', $user_id, $post_id, $comment);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "message" => "Comentario agregado"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error al guardar comentario"]);
}



