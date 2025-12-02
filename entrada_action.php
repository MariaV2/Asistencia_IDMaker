<?php
// entrada_action.php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$institucion = trim($_POST['institucion'] ?? '');
$cargo = trim($_POST['cargo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

if ($nombre === '') {
    echo json_encode(['ok' => false, 'msg' => 'El nombre es requerido']);
    exit;
}

// generar código único seguro
$codigo = bin2hex(random_bytes(6)); // 12 chars hex
$entrada = date('Y-m-d H:i:s');

$stmt = $mysqli->prepare("INSERT INTO asistencias (codigo, nombre, institucion, cargo, telefono, entrada) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssss', $codigo, $nombre, $institucion, $cargo, $telefono, $entrada);

if ($stmt->execute()) {
    echo json_encode(['ok' => true, 'codigo' => $codigo, 'entrada' => $entrada]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error al guardar: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
