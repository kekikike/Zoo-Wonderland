<?php
// Pago con QR para compra individual de tickets
// Variables esperadas: $_SESSION['ultima_compra_datos']
$datos = $_SESSION['ultima_compra_datos'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagar Compra - Zoo Wonderland</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0; }
.container { max-width:600px; margin:40px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); text-align:center; }
h1 { color:#2d5016; }
.qr { margin:20px 0; }
button, a.button { background:#2d5016; color:#fff; padding:10px 20px; border:none; border-radius:4px; text-decoration:none; display:inline-block; }
button:hover, a.button:hover { background:#234010; }
</style>
</head>
<body>
<div class="container">
    <h1>Completa tu pago</h1>
    <?php if (!empty($datos)): ?>
        <p>Escanea el código QR para pagar <strong>Bs. <?php echo number_format($datos['monto_total'],2); ?></strong></p>
        <div class="qr">
            <img src="img/qr.jpeg" alt="QR de pago" width="250">
        </div>
        <p>Recorrido: <?php echo htmlspecialchars($datos['recorrido']); ?></p>
        <p>Fecha: <?php echo htmlspecialchars($datos['fecha']); ?> &nbsp;Hora: <?php echo htmlspecialchars($datos['hora']); ?></p>
        <a class="button" href="index.php?r=compras/historial">Ver historial</a>
    <?php else: ?>
        <p>No hay datos de compra disponibles.</p>
        <a class="button" href="index.php">Volver al inicio</a>
    <?php endif; ?>
</div>
</body>
</html>
