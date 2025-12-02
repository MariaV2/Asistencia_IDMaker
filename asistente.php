<?php
date_default_timezone_set('America/El_Salvador');
$conn = new mysqli("localhost", "root", "", "AsistenciaSteamPassion");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$qrCode = "";
$codigoUnico = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $institucion = $_POST['institucion'];
    $cargo = $_POST['cargo'];
    $telefono = $_POST['telefono'];
    $fechaHora = date("Y-m-d H:i:s");
    
    // Generar código único (ej: hash con nombre y tiempo)
    $codigoUnico = substr(md5($nombre . $telefono . time()), 0, 8);

    $sql = "INSERT INTO asistencias (nombre, institucion, cargo, telefono, entrada, codigo) 
            VALUES ('$nombre', '$institucion', '$cargo', '$telefono', '$fechaHora', '$codigoUnico')";
    if ($conn->query($sql) === TRUE) {
        $qrCode = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($codigoUnico) . "&size=150x150";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Asistencia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../AsistenciaSteamPassion/assets/css/styles.css">
</head>
<body class="container py-4">
    <?php include 'header.php'; ?>
    <p>
    <h2 align = "center" class="mb-4">Registro de Asistencia</h2>

    <?php if ($qrCode): ?>
        <div class="alert alert-success">
            <p><strong>Registro exitoso ✅</strong></p>
            <p>Tu código único es: <strong><?php echo $codigoUnico; ?></strong></p>
            <img src="<?php echo $qrCode; ?>" alt="QR Code">
        </div>
    <?php endif; ?>

    <div class="card mb-3">
    <form method="POST">
        <div class="mb-3">
            <label>Nombre:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Institución:</label>
            <input type="text" name="institucion" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Cargo:</label>
            <input type="text" name="cargo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Teléfono:</label>
            <input type="text" name="telefono" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Entrada</button>
    </form>
    </div>
</body>
</html>
