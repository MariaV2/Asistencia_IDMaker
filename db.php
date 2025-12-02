<?php
// db.php
$host = "localhost";
$db   = "asistenciasteampassion";
$user = "root";
$pass = ""; // en XAMPP por defecto está vacío
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    http_response_code(500);
    die("Falló la conexión a MySQL: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
?>
