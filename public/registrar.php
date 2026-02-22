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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $resultado = $register->create($_POST);

    if ($resultado['success']) {
        $_SESSION['success'] = $resultado['message'];
        header('Location: index.php');
        exit;
    } else {
        $error = $resultado['message'];
        
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro - Zoo Wonderland</title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background-color: #fee2a0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background-color: #967b5d;
    padding: 30px;
    border-radius: 12px;
    width: 400px;
}
h2 {
    color: #68672e;
    text-align: center;
}
input {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 2px solid #a3712a;
    border-radius: 5px;
}
button {
    width: 100%;
    padding: 10px;
    background-color: #68672e;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background-color: #a3712a;
}
.error {
    color: red;
    text-align: center;
}
.success {
    color: green;
    text-align: center;
}
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
    <h2>🦁 Registro Cliente <br><span>Zoo Wonderland</span></h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validarFormulario();">
        <input type="text" name="nombre1" placeholder="Primer Nombre" required>
        <input type="text" name="nombre2" placeholder="Segundo Nombre">
        <input type="text" name="apellido1" placeholder="Primer Apellido" required>
        <input type="text" name="apellido2" placeholder="Segundo Apellido">
        <input type="email" name="correo" placeholder="Correo" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <input type="text" name="username" id="username" placeholder="Nombre de Usuario" required>
        <input type="password" name="password" id="password" placeholder="Contraseña" required>
        <input type="number" name="nit" placeholder="NIT" required>
        <button type="submit">Registrar 🐾</button>
    </form>

    <div class="footer">
        Bienvenido al reino salvaje 🐘🌿
    </div>
</div>

</body>
</html>