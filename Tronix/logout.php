<?php
// logout.php
session_start(); // 🔹 Inicia la sesión si no estaba iniciada
session_unset(); // 🔹 Limpia todas las variables de sesión
session_destroy(); // 🔹 Destruye la sesión completamente

// 🔹 Redirige al inicio
header('Location: index.php');
exit;
?>

