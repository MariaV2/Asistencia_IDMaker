<?php
date_default_timezone_set('America/El_Salvador');
$conn = new mysqli("localhost", "root", "", "AsistenciaSteamPassion");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$mensaje = "";

// Registrar entrada
if (isset($_POST['accion']) && $_POST['accion'] == "entrada") {
    $nombre = $_POST['nombre'];
    $institucion = $_POST['institucion'];
    $cargo = $_POST['cargo'];
    $telefono = $_POST['telefono'];
    $fechaHora = date("Y-m-d H:i:s");
    $codigoUnico = substr(md5($nombre . $telefono . time()), 0, 8);

    $sql = "INSERT INTO asistencias (nombre, institucion, cargo, telefono, entrada, codigo) 
            VALUES ('$nombre', '$institucion', '$cargo', '$telefono', '$fechaHora', '$codigoUnico')";
    if ($conn->query($sql) === TRUE) {
        $mensaje = "<div class='alert alert-success'>Entrada registrada ✅ | Código: <strong>$codigoUnico</strong></div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Registrar salida
if (isset($_POST['accion']) && $_POST['accion'] == "salida") {
    $codigo = $_POST['codigo'];
    $fechaHora = date("Y-m-d H:i:s");

    $sql = "UPDATE asistencias SET salida='$fechaHora' WHERE codigo='$codigo' AND salida IS NULL";
    if ($conn->query($sql) && $conn->affected_rows > 0) {
        // Redirigir a página de agradecimiento
        header("Location: gracias.php");
        exit;
    } else {
        $mensaje = "<div class='alert alert-warning'>⚠️ Código no válido o ya tiene salida.</div>";
    }
}

// --- Buscador ---
$resultados = null;
$busqueda = "";
if (isset($_GET['q']) && $_GET['q'] != "") {
    $busqueda = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT * FROM asistencias 
            WHERE (nombre LIKE '%$busqueda%' OR telefono LIKE '%$busqueda%') 
            ORDER BY entrada DESC";
    $resultados = $conn->query($sql);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protocolo - Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../AsistenciaSteamPassion/assets/css/styles.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="container py-4">
    <?php include 'header.php'; ?>
    <p>
    <h2 align = "center" class="mb-4">Panel Protocolo</h2>
    <?php echo $mensaje; ?>

    <!-- Registro de Entrada -->
    <div class="card mb-3">
        <div class="card-header">Registrar Entrada</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="accion" value="entrada">
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
                <button type="submit" class="btn btn-success">Registrar Entrada</button>
            </form>
        </div>
    </div>

    <!-- Buscador para salida -->
    <div class="card mb-4">
        <div class="card-header">Buscar participante para registrar salida</div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o teléfono" value="<?php echo htmlspecialchars($busqueda); ?>">
            </form>

            <?php if ($resultados && $resultados->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Entrada</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['nombre']; ?></td>
                                <td><?php echo $row['telefono']; ?></td>
                                <td><?php echo $row['entrada']; ?></td>
                                <td>
                                    <?php if (!$row['salida']): ?>
                                        <form method="POST">
                                            <input type="hidden" name="accion" value="salida">
                                            <input type="hidden" name="codigo" value="<?php echo $row['codigo']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Registrar salida</button>
                                        </form>
                                    <?php else: ?>
                                        ✅ Ya salió
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php elseif (isset($_GET['q'])): ?>
                <div class="alert alert-info">No se encontraron resultados.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Registro de Salida por Código -->
    <div class="card mb-4">
        <div class="card-header">Registrar Salida (código manual)</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="accion" value="salida">
                <div class="mb-2">
                    <label>Código único:</label>
                    <input type="text" name="codigo" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-danger">Registrar Salida</button>
            </form>
        </div>
    </div>

    <!-- Registro de Salida con QR -->
    <div class="card mb-4">
        <div class="card-header">Registrar Salida (escaneo QR)</div>
        <div class="card-body">
            <div id="lectorQR" style="width:100%;"></div>
            <form method="POST" id="formQR">
                <input type="hidden" name="accion" value="salida">
                <input type="hidden" name="codigo" id="codigoQR">
            </form>
        </div>
    </div>

    <script>
    function onScanSuccess(decodedText, decodedResult) {
        document.getElementById("codigoQR").value = decodedText;
        document.getElementById("formQR").submit();
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "lectorQR",
        { fps: 10, qrbox: 250 },
        /* verbose= */ false);

    html5QrcodeScanner.render(onScanSuccess);
    </script>
</body>
</html>
