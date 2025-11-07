<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_field() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'">';
}

function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Error: token CSRF inválido');
        }
    }
}
?>
