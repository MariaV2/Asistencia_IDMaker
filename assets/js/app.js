document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('entradaForm');
  const msg = document.getElementById('msg');
  const qrArea = document.getElementById('qrArea');
  const downloadBtn = document.getElementById('downloadQrBtn');

  let currentCode = null;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.innerHTML = '';
    const data = new URLSearchParams();
    data.append('nombre', document.getElementById('nombre').value);
    data.append('institucion', document.getElementById('institucion').value);
    data.append('cargo', document.getElementById('cargo').value);
    data.append('telefono', document.getElementById('telefono').value);

    try {
      const res = await fetch('entrada_action.php', { method: 'POST', body: data });
      const json = await res.json();
      if (!json.ok) {
        msg.innerHTML = `<div class="alert alert-danger">${json.msg || 'Error'}</div>`;
        return;
      }
      currentCode = json.codigo;
      msg.innerHTML = `<div class="alert alert-success">Entrada registrada: ${json.entrada}</div>`;
      // generar QR
      qrArea.innerHTML = '';
      // QRCode library will create an <img> or <canvas> inside qrArea
      new QRCode(qrArea, {
        text: currentCode,
        width: 200,
        height: 200
      });
      downloadBtn.style.display = 'inline-block';
    } catch (err) {
      console.error(err);
      msg.innerHTML = `<div class="alert alert-danger">Error de conexión</div>`;
    }
  });

  // Descargar QR como PNG
  downloadBtn.addEventListener('click', () => {
    // QRCode lib genera un <img> o un <canvas> como primer hijo
    const qrEl = qrArea.querySelector('img') || qrArea.querySelector('canvas');
    if (!qrEl) return alert('QR no encontrado');
    if (qrEl.tagName.toLowerCase() === 'img') {
      // crear link para descargar
      const link = document.createElement('a');
      link.href = qrEl.src;
      link.download = `qr_${currentCode}.png`;
      link.click();
    } else {
      const url = qrEl.toDataURL('image/png');
      const link = document.createElement('a');
      link.href = url;
      link.download = `qr_${currentCode}.png`;
      link.click();
    }
  });
});
