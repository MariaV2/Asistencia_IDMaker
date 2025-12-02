<?php
session_start();
date_default_timezone_set('America/El_Salvador');
$conn = new mysqli("localhost", "root", "", "asistenciasteampassion");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Login simple
if (!isset($_SESSION['admin'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
        if ($_POST['password'] === "1234majo") {
            $_SESSION['admin'] = true;
            header("Location: admin.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="../AsistenciaSteamPassion/assets/css/styles.css">
    </head>
    <body class="container py-4">
        <?php include 'header.php'; ?>
        <h3>Acceso Administrador</h3>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Contraseña">
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </body>
    </html>
    <?php
    exit();
}

// Registrar salida
if (isset($_GET['salida'])) {
    $id = intval($_GET['salida']);
    $fechaHora = date("Y-m-d H:i:s");
    $conn->query("UPDATE asistencias SET salida='$fechaHora' WHERE id=$id");
    header("Location: admin.php");
    exit();
}

// Buscador
$busqueda = "";
if (isset($_GET['q'])) {
    $busqueda = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT * FROM asistencias WHERE nombre LIKE '%$busqueda%' OR telefono LIKE '%$busqueda%' ORDER BY entrada DESC";
} else {
    $sql = "SELECT * FROM asistencias ORDER BY entrada DESC";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../AsistenciaSteamPassion/assets/css/styles.css">
</head>
<body class="container py-4">
    <h2>Panel de Administración</h2>
    <form method="GET" class="mb-3">
        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o teléfono" value="<?php echo $busqueda; ?>">
    </form>
    <form method="post" action="export_csv.php">
    <button type="submit" class="btn-export">Exportar a CSV</button>
    </form>
    <p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Institución</th>
                <th>Cargo</th>
                <th>Teléfono</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Código</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['institucion']; ?></td>
                    <td><?php echo $row['cargo']; ?></td>
                    <td><?php echo $row['telefono']; ?></td>
                    <td><?php echo $row['entrada']; ?></td>
                    <td><?php echo $row['salida'] ?: '-'; ?></td>
                    <td><?php echo $row['codigo']; ?></td>
                    <td>
                        <?php if (!$row['salida']): ?>
                            <a href="admin.php?salida=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger">Registrar salida</a>
                        <?php else: ?>
                            ✅
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
