<?php
// conexion.php
// Conexión central a la base de datos con mysqli.
// Edita las credenciales si tu MySQL usa contraseña.

$DB_HOST = 'localhost';
$DB_NAME = 'temixco';
$DB_USER = 'root';
$DB_PASS = ''; // si tienes contraseña en root, ponla aquí

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    // En producción nunca muestres detalles. Para desarrollo puede usarse:
    die("Error: no se puede conectar a la DB (".$mysqli->connect_error.")");
}
$mysqli->set_charset("utf8mb4");
