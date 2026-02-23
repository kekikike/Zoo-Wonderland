<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use App\Repositories\UsuarioRepository;
use App\Services\Register;

$repo = new UsuarioRepository();
$register = new Register($repo);

$error = null;
$success = null;

// Preparar valores para rellenar el formulario después de error
$formData = $_POST ?? [];  // Si vino por POST, usamos esos valores

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $resultado = $register->create($_POST);

    if ($resultado['success']) {
        $_SESSION['success'] = $resultado['message'];
        header('Location: index.php');
        exit;
    } else {
        $error = $resultado['message'];
        // ¡Importante! NO limpiamos $formData aquí → se mantiene con $_POST
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro - Zoo Wonderland</title>

<style>
    /* tu CSS anterior sin cambios */
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #fee2a0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .container { background-color: #967b5d; padding: 30px; border-radius: 12px; width: 400px; }
    h2 { color: #68672e; text-align: center; margin-top: 0; }
    input { width: 100%; padding: 8px; margin-bottom: 10px; border: 2px solid #a3712a; border-radius: 5px; font-size: 14px; }
    input:focus { outline: none; border-color: #68672e; }
    button { width: 100%; padding: 10px; background-color: #68672e; color: white; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background-color: #a3712a; }
    .message { min-height: 22px; margin-bottom: 10px; }
    .error { color: red; text-align: center; }
    .success { color: green; text-align: center; }
    .footer { margin-top: 15px; text-align: center; font-size: 13px; color: #333; }
</style>

<script>
function validarFormulario() {
    const username = document.getElementById("username").value;
    if (username.includes(" ")) {
        alert("El usuario no puede tener espacios.");
        return false;
    }
    if (document.getElementById("password").value.length < 4) {
        alert("La contraseña debe tener mínimo 4 caracteres.");
        return false;
    }
    return true;
}
</script>
</head>
<body>

<div class="container">
    <h2>Registro Cliente <br><span>Zoo Wonderland</span></h2>

    <div class="message">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </div>

    <form method="POST" onsubmit="return validarFormulario();">

        <input type="text" name="nombre1" placeholder="Primer Nombre" required
               value="<?= htmlspecialchars($formData['nombre1'] ?? '') ?>">

        <input type="text" name="nombre2" placeholder="Segundo Nombre"
               value="<?= htmlspecialchars($formData['nombre2'] ?? '') ?>">

        <input type="text" name="apellido1" placeholder="Primer Apellido" required
               value="<?= htmlspecialchars($formData['apellido1'] ?? '') ?>">

        <input type="text" name="apellido2" placeholder="Segundo Apellido"
               value="<?= htmlspecialchars($formData['apellido2'] ?? '') ?>">

        <input type="email" name="correo" placeholder="Correo" required
               value="<?= htmlspecialchars($formData['correo'] ?? '') ?>">

        <input type="text" name="telefono" placeholder="Teléfono" required
               value="<?= htmlspecialchars($formData['telefono'] ?? '') ?>">

        <input type="text" name="username" id="username" placeholder="Nombre de Usuario" required
               value="<?= htmlspecialchars($formData['username'] ?? '') ?>">

        <input type="password" name="password" id="password" placeholder="Contraseña" required
               value=""> <!-- ← contraseña NO se rellena por seguridad -->

        <input type="number" name="nit" placeholder="NIT" required
               value="<?= htmlspecialchars($formData['nit'] ?? '') ?>">

        <button type="submit">Registrar 🐾</button>
    </form>

    <div class="footer">
        Bienvenido al reino salvaje 🐘🌿
    </div>
</div>

</body>
</html>