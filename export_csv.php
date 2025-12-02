<?php
// genera CSV descargable
require 'db.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=asistencias_export_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID','Codigo','Nombre','Institucion','Cargo','Telefono','Entrada','Salida','CreadoEn']);

$res = $mysqli->query("SELECT id, codigo, nombre, institucion, cargo, telefono, entrada, salida, creado_en FROM asistencias ORDER BY creado_en DESC");
while ($row = $res->fetch_assoc()) {
    fputcsv($out, [
        $row['id'],
        $row['codigo'],
        $row['nombre'],
        $row['institucion'],
        $row['cargo'],
        $row['telefono'],
        $row['entrada'],
        $row['salida'],
        $row['creado_en']
    ]);
}
$res->free();
$mysqli->close();
fclose($out);
exit;
