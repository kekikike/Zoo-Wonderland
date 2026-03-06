<?php
/** @var \App\Models\Usuario $user */
/** @var int $id_gr */
/** @var array $detalleRecorrido */
/** @var string|null $mensaje */

$error = $_SESSION['error'] ?? null;
if ($error) unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Reporte - ZooWonderland</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --verde-selva:   #2e7d32;
            --verde-oscuro:  #1b5e20;
            --amarillo-sol:  #ffca28;
            --gris-claro:    #f8faf8;
            --oscuro:        #0d3a1f;
            --blanco:        #ffffff;
            --sombra:        0 10px 30px rgba(0,0,0,0.08);
        }
        body { font-family: 'Open Sans', sans-serif; background: var(--gris-claro); padding: 2rem; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 20px; box-shadow: var(--sombra); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        h1 { font-family: 'Montserrat', sans-serif; color: var(--oscuro); margin: 0; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--oscuro); }
        textarea { width: 100%; padding: 1rem; border: 2px solid #eee; border-radius: 12px; font-family: inherit; font-size: 1rem; resize: vertical; min-height: 200px; }
        textarea:focus { border-color: var(--verde-selva); outline: none; box-shadow: 0 0 0 3px rgba(46,125,50,0.1); }
        .btn { display: inline-block; padding: 1rem 2rem; background: var(--verde-selva); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; text-decoration: none; transition: all 0.3s; }
        .btn:hover { background: var(--verde-oscuro); transform: translateY(-2px); }
        .btn-cancel { background: #999; margin-left: 0.5rem; }
        .btn-cancel:hover { background: #777; }
        .alert { padding: 1.2rem; border-radius: 10px; margin-bottom: 1.5rem; font-weight: 600; }
        .alert-error { background: #fee2e2; color: #c41c3b; border-left: 4px solid #d32f2f; }
        .alert-info { background: #e3f2fd; color: #1565c0; border-left: 4px solid #2196f3; }
        .info-box { background: #f0f4f0; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-size: 0.95rem; border-left: 5px solid var(--verde-selva); }
        .info-box i { margin-right: 0.5rem; color: var(--verde-selva); }
        .recorrido-info { 
            background: #fafafa; 
            padding: 1.5rem; 
            border-radius: 12px; 
            margin-bottom: 2rem;
            border: 2px solid #e0e0e0;
        }
        .recorrido-titulo { 
            font-size: 1.3rem; 
            font-weight: 800; 
            color: var(--verde-selva); 
            font-family: 'Montserrat', sans-serif;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .recorrido-detalles {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            font-size: 0.9rem;
        }
        .detalle-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .detalle-item i { color: var(--verde-selva); width: 20px; }
        .char-count { 
            font-size: 0.85rem; 
            color: #999; 
            margin-top: 0.3rem;
            text-align: right;
        }
        .char-count.warning { color: #ff9800; font-weight: 700; }
        .char-count.error { color: #d32f2f; font-weight: 700; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Registrar Reporte</h1>
        <a href="index.php?r=guias/reportes-crear" style="color: #666; text-decoration: none; display: inline-block;">
            <i class="fa-solid fa-arrow-left"></i> Seleccionar otro recorrido
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="recorrido-info">
        <div class="recorrido-titulo">
            <i class="fa-solid fa-map"></i>
            <?= htmlspecialchars($detalleRecorrido['recorrido_nombre']) ?>
        </div>
        <div class="recorrido-detalles">
            <div class="detalle-item">
                <i class="fa-solid fa-calendar-days"></i>
                <strong>Realizado:</strong> <?= (new DateTime($detalleRecorrido['fecha_asignacion']))->format('d/m/Y') ?>
            </div>
            <div class="detalle-item">
                <i class="fa-solid fa-users"></i>
                <strong>Clientes confirmados:</strong> <?= (int)$detalleRecorrido['tickets_confirmados'] ?>
            </div>
            <div class="detalle-item">
                <i class="fa-solid fa-certificate"></i>
                <strong>Tipo:</strong> <?= htmlspecialchars($detalleRecorrido['tipo']) ?>
            </div>
        </div>
    </div>

    <div class="info-box">
        <i class="fa-solid fa-info-circle"></i>
        <strong>IMPORTANTE:</strong> Una vez guardado, este reporte NO PODRA ser modificado. 
        Revisa cuidadosamente antes de guardar.
    </div>

    <form method="POST" action="index.php?r=guias/reportes-guardar" id="formReporte">
        <input type="hidden" name="id_guia_recorrido" value="<?= (int)$detalleRecorrido['id_guia_recorrido'] ?>">
        
        <div class="form-group">
            <label for="observaciones">Observaciones del Recorrido (10 - 1000 caracteres)</label>
            <textarea 
                id="observaciones" 
                name="observaciones" 
                placeholder="Describe detalles importantes del recorrido realizado:
- Comportamiento de los animales
- Incidentes o novedades
- Asistencia de los clientes
- Cualquier otro detalle relevante..."
                required
                minlength="10"
                maxlength="1000"
            ><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
            <div class="char-count" id="charCount">0/1000 caracteres</div>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn" id="submitBtn">
                <i class="fa-solid fa-cloud-arrow-up"></i> Guardar Reporte (Final e Inmutable)
            </button>
            <a href="index.php?r=guias/dashboard" class="btn btn-cancel">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.getElementById('observaciones');
    const charCount = document.getElementById('charCount');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('formReporte');

    // Contador de caracteres
    textarea.addEventListener('input', () => {
        const length = textarea.value.length;
        charCount.textContent = length + '/1000 caracteres';
        
        if (length < 10) {
            charCount.classList.add('error');
            charCount.classList.remove('warning');
        } else if (length < 50) {
            charCount.classList.add('warning');
            charCount.classList.remove('error');
        } else {
            charCount.classList.remove('error', 'warning');
        }
    });

    // Validacion antes de enviar
    form.addEventListener('submit', (e) => {
        const length = textarea.value.trim().length;
        
        if (length < 10) {
            e.preventDefault();
            alert('Las observaciones deben tener minimo 10 caracteres.');
            return;
        }
        
        if (length > 1000) {
            e.preventDefault();
            alert('Las observaciones no pueden exceder 1000 caracteres.');
            return;
        }

        // Confirmacion final
        if (!confirm('Una vez guardado, este reporte no podra ser modificado. Continuar?')) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';
    });

    // Inicializar contador
    textarea.dispatchEvent(new Event('input'));
});
</script>

</body>
</html>
