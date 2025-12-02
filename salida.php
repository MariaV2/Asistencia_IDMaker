<?php
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registrar Salida - Asistencia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
      <span class="navbar-brand">Asistencia - Registrar Salida</span>
      <a class="btn btn-outline-light" href="admin.php">Panel Admin</a>
    </div>
  </nav>

  <main class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5>Escanear QR</h5>
            <div id="reader" style="width:100%"></div>
            <div id="scanResult" class="mt-3"></div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5>Ingresar código manualmente</h5>
            <div class="mb-3">
              <input type="text" id="codigoManual" class="form-control" placeholder="Pegue o escriba el código">
            </div>
            <button id="btnManual" class="btn btn-primary">Registrar Salida</button>
            <div id="manualMsg" class="mt-3"></div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
  <script>
    const scanResult = document.getElementById('scanResult');
    const manualMsg = document.getElementById('manualMsg');

    function registrarSalida(codigo) {
      const data = new URLSearchParams();
      data.append('codigo', codigo);
      fetch('salida_action.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(j => {
          if (j.ok) {
            scanResult.innerHTML = `<div class="alert alert-success">${j.msg}</div>`;
            manualMsg.innerHTML = `<div class="alert alert-success">${j.msg}</div>`;
          } else {
            scanResult.innerHTML = `<div class="alert alert-danger">${j.msg}</div>`;
            manualMsg.innerHTML = `<div class="alert alert-danger">${j.msg}</div>`;
          }
        })
        .catch(err => {
          console.error(err);
          scanResult.innerHTML = `<div class="alert alert-danger">Error de conexión</div>`;
        });
    }

    // html5-qrcode scanner
    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: 250 };

    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        // por defecto usa la cámara trasera si existe
        const cameraId = cameras[0].id;
        html5QrCode.start(cameraId, config, qrCodeMessage => {
          registrarSalida(qrCodeMessage);
        }, errorMessage => {
          // console.log("Scan error", errorMessage);
        }).catch(err => {
          scanResult.innerHTML = `<div class="alert alert-warning">No se pudo iniciar la cámara: ${err}</div>`;
        });
      } else {
        scanResult.innerHTML = `<div class="alert alert-warning">No se detectaron cámaras</div>`;
      }
    }).catch(err => {
      scanResult.innerHTML = `<div class="alert alert-warning">Error obteniendo cámaras: ${err}</div>`;
    });

    // manual
    document.getElementById('btnManual').addEventListener('click', () => {
      const code = document.getElementById('codigoManual').value.trim();
      if (!code) return manualMsg.innerHTML = `<div class="alert alert-warning">Ingresa un código</div>`;
      registrarSalida(code);
    });
  </script>
</body>
</html>
