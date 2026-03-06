<?php // app/Views/errors/403.php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado · ZooWonderland</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0a3d1f 0%, #1b5e20 50%, #2e7d32 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border-radius: 28px;
            padding: 4rem 3.5rem;
            max-width: 560px;
            width: 100%;
            text-align: center;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            animation: fadeInUp 0.6s ease;
            position: relative;
            overflow: hidden;
        }

        /* Decoración de fondo */
        .error-card::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,179,0,0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .error-card::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 240px; height: 240px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Icono */
        .error-icon-wrap {
            width: 110px; height: 110px;
            background: rgba(255, 179, 0, 0.15);
            border: 2px solid rgba(255, 179, 0, 0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2.5s ease infinite;
        }
        .error-icon-wrap i {
            font-size: 3rem;
            color: #ffb300;
        }

        /* Código de error */
        .error-code {
            font-family: 'Playfair Display', serif;
            font-size: 6.5rem;
            font-weight: 800;
            line-height: 1;
            color: #ffb300;
            text-shadow: 0 4px 20px rgba(255, 179, 0, 0.4);
            margin-bottom: 0.3rem;
            position: relative; z-index: 1;
        }

        .error-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.2rem;
            position: relative; z-index: 1;
        }

        .error-desc {
            color: rgba(255, 255, 255, 0.72);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2.5rem;
            position: relative; z-index: 1;
        }
        .error-desc strong {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Separador */
        .divider {
            width: 60px; height: 3px;
            background: linear-gradient(90deg, #ffb300, transparent);
            border-radius: 999px;
            margin: 0 auto 2rem;
        }

        /* Botones */
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            position: relative; z-index: 1;
        }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 0.6rem;
            background: #ffb300;
            color: #0a3d1f;
            padding: 0.85rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.95rem;
            transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(255, 179, 0, 0.4);
        }
        .btn-primary:hover {
            background: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 255, 255, 0.25);
        }

        .btn-secondary {
            display: inline-flex; align-items: center; gap: 0.6rem;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.85);
            padding: 0.85rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s;
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.18);
            transform: translateY(-3px);
        }

        /* Badge rol info */
        .rol-hint {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(255, 179, 0, 0.1);
            border: 1px solid rgba(255, 179, 0, 0.3);
            color: #ffb300;
            padding: 0.4rem 1rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 179, 0, 0.3); }
            50%       { box-shadow: 0 0 0 14px rgba(255, 179, 0, 0); }
        }
    </style>
</head>
<body>

<div class="error-card">

    <!-- Icono animado -->
    <div class="error-icon-wrap">
        <i class="fas fa-lock"></i>
    </div>

    <!-- Código -->
    <div class="error-code">403</div>
    <h1 class="error-title">Acceso Denegado</h1>

    <div class="divider"></div>

    <!-- Badge -->
    <div class="rol-hint">
        <i class="fas fa-shield-halved"></i>
        Zona restringida · Solo administradores
    </div>

    <!-- Descripción -->
    <p class="error-desc">
        No tienes los permisos necesarios para acceder a esta sección.<br>
        Es posible que necesites <strong>iniciar sesión</strong> con una cuenta
        con el rol adecuado, o que tu sesión haya expirado.
    </p>

    <!-- Botones -->
    <div class="btn-group">
        <a href="index.php" class="btn-primary">
            <i class="fas fa-house"></i> Ir al inicio
        </a>
        <a href="index.php?r=login" class="btn-secondary">
            <i class="fas fa-right-to-bracket"></i> Iniciar sesión
        </a>
    </div>

</div>

</body>
</html>