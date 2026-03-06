<?php
// Formulario de compra de tickets
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprar Tickets - Zoo Wonderland</title>
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
        --transicion:    all 0.3s cubic-bezier(0.4,0,0.2,1);
        --error:         #d32f2f;
    }

    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Open Sans',sans-serif; background:var(--gris-claro); color:#333; }
    h1,h2,h3,.logo { font-family:'Montserrat',sans-serif; }
    header { background:var(--blanco); padding:1rem 5%; position:sticky; top:0; z-index:1000; box-shadow:0 2px 15px rgba(0,0,0,0.05); }
    nav { display:flex; justify-content:space-between; align-items:center; max-width:1400px; margin:0 auto; }
    .logo { font-size:1.6rem; font-weight:800; color:var(--verde-selva); text-decoration:none; }
    .menu { display:flex; gap:2rem; }
    .menu a { color:var(--oscuro); text-decoration:none; font-weight:600; }
    .user-welcome { display:flex; align-items:center; gap:1rem; background:#f0f4f0; padding:0.5rem 1rem; border-radius:50px; font-size:0.9rem; }

    .page-hero {
        background: linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.7)),
                    url('https://images.unsplash.com/photo-1567818871419-e808cde8ad94?q=80&w=2070&auto=format&fit=crop') center/cover;
        height:200px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:white;
        text-align:center;
        margin-bottom:2rem;
    }
    .page-hero h1 { font-size:2.4rem; }

    main { max-width:1200px; margin:0 auto; padding:0 2rem; display:grid; grid-template-columns:350px 1fr; gap:2.5rem; }
    @media(max-width:900px){ main{grid-template-columns:1fr;} }

    .sidebar{display:flex;flex-direction:column;gap:1.5rem;}
    .card-info{background:var(--blanco);border-radius:20px;padding:1.5rem;box-shadow:var(--sombra);border-left:5px solid var(--amarillo-sol);}
    .card-info h3{color:var(--verde-selva);margin-bottom:1rem;display:flex;align-items:center;gap:10px;}
    .card-info ul{list-style:none;}
    .card-info li{margin-bottom:0.8rem;font-size:0.95rem;display:flex;align-items:center;gap:10px;}
    .card-info li i{color:var(--verde-selva);width:20px;}

    .form-container{background:var(--blanco);border-radius:25px;padding:2.5rem;box-shadow:var(--sombra);}
    .form-container h2{margin-bottom:2rem;color:var(--oscuro);text-align:left;}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;}
    @media(max-width:600px){ .form-grid{grid-template-columns:1fr;} }
    .form-group{margin-bottom:1.2rem;}
    .form-group label{display:block;font-weight:700;margin-bottom:0.5rem;color:var(--oscuro);font-size:0.9rem;}
    .form-group input,.form-group select{width:100%;padding:0.8rem 1rem;border:2px solid #eee;border-radius:12px;font-family:inherit;font-size:1rem;transition:var(--transicion);}
    .form-group input:focus,.form-group select:focus{border-color:var(--verde-selva);outline:none;background:#f0fdf4;}
    .full-width{grid-column:span 2;}@media(max-width:600px){.full-width{grid-column:span 1;}}
    .btn-submit{background:var(--verde-selva);color:white;padding:1rem 2rem;border:none;border-radius:12px;font-weight:700;font-size:1.1rem;cursor:pointer;transition:var(--transicion);display:inline-flex;align-items:center;justify-content:center;gap:10px;margin-top:1rem;box-shadow:0 5px 15px rgba(46,125,50,0.2);}
    .btn-submit:hover{background:var(--verde-oscuro);transform:translateY(-3px);}
    .alert{padding:1rem;border-radius:12px;margin-bottom:2rem;display:flex;align-items:center;gap:15px;}
    .alert-error{background:#fee2e2;color:var(--error);border:1px solid #fecaca;}
    .field-error{color:var(--error);font-size:0.8rem;margin-top:0.4rem;font-weight:600;}
    .input-error{border-color:var(--error)!important;}
    .recorridos-grid { display: none; }
    .form-section { background: #f9f9f9; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; }
    .form-section h3 { color: var(--oscuro); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; font-size: 1.2rem; }
    .availability-box { background: #e8f5e9; border-left: 4px solid var(--verde-selva); padding: 1rem; border-radius: 8px; margin-top: 1rem; color: var(--verde-oscuro); font-weight: 600; }
    footer{background:var(--oscuro);color:white;text-align:center;padding:3rem;margin-top:5rem;font-size:0.9rem;opacity:0.9;}
</style>
</head>
<body>
<header>
    <nav>
        <a href="index.php" class="logo">🍃 ZooWonderland</a>
        <div class="menu">
            <a href="index.php">Inicio</a>
            <a href="index.php?r=compras/crear">Comprar Tickets</a>
            <a href="index.php?r=reservas/historial">Mis Reservas</a>
        </div>
        <div class="user-welcome">
            <i class="fa-solid fa-user-circle"></i> 
            <strong><?= htmlspecialchars($usuario->getNombreParaMostrar()) ?></strong>
        </div>
    </nav>
</header>

<div class="page-hero">
    <h1>Tickets Individuales / Familiares</h1>
</div>

<main>
    <aside class="sidebar">
        <div class="card-info">
            <h3><i class="fa-solid fa-info-circle"></i> Datos importantes</h3>
            <ul>
                <li><i class="fa-solid fa-users"></i> Capacidad: Disponible solo de 1 a 10 personas por compra.</li>
                <li><i class="fa-solid fa-clock"></i> Horarios: Ingreso libre de 09:00 a 16:00.</li>
                <li><i class="fa-solid fa-bolt"></i> Disponibilidad: Compra inmediata (sin anticipación requerida).</li>
                <li><i class="fa-solid fa-check-circle"></i> Confirmación: Instantánea al realizar el pago.</li>
            </ul>
        </div>

        <div class="card-info" style="border-left-color: var(--verde-oscuro);">
            <h3><i class="fa-solid fa-map-location-dot"></i> Recorridos</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($recorridos as $rec): 
                    $recId = $rec['id_recorrido'] ?? $rec['id'];
                ?>
                <div style="padding-bottom: 0.8rem; border-bottom: 1px dashed #eee;">
                    <div style="font-weight: 700; color: var(--verde-oscuro); margin-bottom: 0.3rem;">
                        <?php echo htmlspecialchars($rec['nombre']); ?>
                    </div>
                    <div style="font-size: 0.9rem; color: var(--verde-selva); font-weight: 600;">
                        Bs. <?php echo number_format($rec['precio'], 2); ?>/persona
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

    <div class="form-container">
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <h2><i class="fa-solid fa-ticket"></i> Comprar Entradas</h2>
        
        <form method="post" action="index.php?r=compras/crear">
            <div class="form-section">
                <h3><i class="fa-solid fa-map-location-dot"></i> Elige tu Recorrido</h3>
                <div class="form-group full-width">
                    <label for="recorrido_id">Selecciona un recorrido</label>
                    <select name="recorrido_id" id="recorrido_id" class="<?php echo !empty($errores['recorrido_id']) ? 'input-error' : ''; ?>">
                        <option value="">-- Seleccione un recorrido --</option>
                        <?php foreach ($recorridos as $rec): ?>
                            <option value="<?php echo $rec['id_recorrido'] ?? $rec['id']; ?>" <?php echo $form['recorrido_id'] == ($rec['id_recorrido'] ?? $rec['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rec['nombre']); ?> - Bs. <?php echo number_format($rec['precio'], 2); ?>/persona
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errores['recorrido_id'])): ?>
                        <div class="field-error"><?php echo $errores['recorrido_id']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-section">
                <h3><i class="fa-solid fa-ticket"></i> Detalles de tu Compra</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="cantidad">Número de Entradas</label>
                        <input type="number" name="cantidad" id="cantidad" min="1" max="10" 
                               value="<?php echo $form['cantidad']; ?>"
                               class="<?php echo !empty($errores['cantidad']) ? 'input-error' : ''; ?>">
                        <?php if (!empty($errores['cantidad'])): ?>
                            <div class="field-error"><?php echo $errores['cantidad']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha de Visita</label>
                        <input type="date" name="fecha" id="fecha" 
                               value="<?php echo htmlspecialchars($form['fecha']); ?>"
                               class="<?php echo !empty($errores['fecha']) ? 'input-error' : ''; ?>">
                        <?php if (!empty($errores['fecha'])): ?>
                            <div class="field-error"><?php echo $errores['fecha']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="hora">Hora de Ingreso</label>
                        <input type="time" name="hora" id="hora" 
                               value="<?php echo htmlspecialchars($form['hora']); ?>"
                               min="09:00" max="16:00"
                               class="<?php echo !empty($errores['hora']) ? 'input-error' : ''; ?>">
                        <?php if (!empty($errores['hora'])): ?>
                            <div class="field-error"><?php echo $errores['hora']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($disponibles !== null && $disponibles > 0): ?>
                    <div class="availability-box">
                        <i class="fa-solid fa-circle-check"></i> Disponibles: <?php echo $disponibles; ?> cupos
                    </div>
                <?php elseif ($disponibles === 0): ?>
                    <div class="alert alert-error" style="margin-top: 1rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i> No hay disponibilidad para este horario
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group full-width">
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-credit-card"></i> Proceder al Pago
                </button>
            </div>
        </form>
    <p><a href="index.php">Volver al inicio</a></p>
    </div> <!-- .form-container -->
</main>
<footer>
    <p>&copy; <?= date('Y') ?> ZooWonderland - Educación y Conservación</p>
</footer>
</body>
</html>
