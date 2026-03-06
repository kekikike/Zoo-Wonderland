<?php
// app/Views/auth/login.php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a ZooWonderland - Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --verde-selva:   #2e7d32;
            --verde-oscuro:  #1b5e20;
            --amarillo-sol:  #ffca28;
            --naranja-tigre: #f57c00;
            --oscuro:        #0d3a1f;
            --blanco:        #ffffff;
            --sombra:        0 15px 35px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Open Sans', sans-serif;
            /* Fondo con un degradado que recuerda a la selva/naturaleza */
            background: linear-gradient(135deg, #e8f5e9 0%, #fff8e1 100%);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-card {
            background: var(--blanco);
            padding: 3rem 2.5rem;
            border-radius: 30px;
            box-shadow: var(--sombra);
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(46, 125, 50, 0.1);
        }

        /* Detalle decorativo superior */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 8px;
            background: linear-gradient(to right, var(--verde-selva), var(--amarillo-sol));
        }

        .logo-area {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-area i {
            font-size: 3rem;
            color: var(--verde-selva);
            margin-bottom: 10px;
        }

        h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            color: var(--oscuro);
            font-size: 1.8rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .error-box {
            background: #fff3f3;
            color: #d32f2f;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #d32f2f;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--oscuro);
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            transition: 0.3s;
        }

        input {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.3s;
            font-family: inherit;
        }

        input:focus {
            outline: none;
            border-color: var(--verde-selva);
            background: #f1f8f1;
        }

        input:focus + i {
            color: var(--verde-selva);
        }

        button {
            width: 100%;
            padding: 1rem;
            background: var(--verde-selva);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
            text-transform: uppercase;
        }

        button:hover {
            background: var(--oscuro);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        .footer-links {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #666;
        }

        .footer-links a {
            color: var(--verde-selva);
            text-decoration: none;
            font-weight: 700;
        }

        .footer-links a:hover {
            color: var(--naranja-tigre);
            text-decoration: underline;
        }

        .btn-home {
            display: block;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #999;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-area">
        <i class="fa-solid fa-leaf"></i>
        <h1>ZooWonderland</h1>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="error-box">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars((string)$error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?r=login">
        <div class="form-group">
            <label for="login">Usuario</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text" id="login" name="login" required 
                       placeholder="usuario"
                       value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password" name="password" required 
                       placeholder="••••••••">
            </div>
        </div>

        <button type="submit">Ingresar</button>
    </form>

    <div class="footer-links">
        ¿Aún no eres parte de la manada? <br>
        <a href="index.php?r=registro">Crea tu cuenta aquí</a>
    </div>

    <a href="index.php" class="btn-home">
        <i class="fa-solid fa-house"></i> Volver al inicio
    </a>
</div>

</body>
</html>