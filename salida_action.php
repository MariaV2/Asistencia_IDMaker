<?php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

$codigo = trim($_POST['codigo'] ?? '');
if ($codigo === '') {
    echo json_encode(['ok' => false, 'msg' => 'Código requerido']);
    exit;
}

// buscar registro
$stmt = $mysqli->prepare("SELECT id, salida FROM asistencias WHERE codigo = ?");
$stmt->bind_param('s', $codigo);
$stmt->execute();
$stmt->bind_result($id, $salida);
if ($stmt->fetch()) {
    $stmt->close();
    if ($salida !== null) {
        echo json_encode(['ok' => false, 'msg' => 'La salida ya fue registrada previamente']);
        exit;
    }
    $salida_now = date('Y-m-d H:i:s');
    $u = $mysqli->prepare("UPDATE asistencias SET salida = ? WHERE id = ?");
    $u->bind_param('si', $salida_now, $id);
    if ($u->execute()) {
        echo json_encode(['ok' => true, 'msg' => "Salida registrada: $salida_now"]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'msg' => 'Error al actualizar salida']);
    }
    $u->close();
} else {
    $stmt->close();
    echo json_encode(['ok' => false, 'msg' => 'Código no encontrado']);
}
$mysqli->close();
