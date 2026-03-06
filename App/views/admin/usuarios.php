<?php
// app/Views/admin/usuarios.php
declare(strict_types=1);

// Mapa colores por recorrido
$coloresRecorrido = [
    'felinos'      => ['bg' => '#fff3e0', 'text' => '#e65100', 'border' => '#ffcc80'],
    'osos'         => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'border' => '#a5d6a7'],
    'cóndor'       => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'border' => '#90caf9'],
    'condor'       => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'border' => '#90caf9'],
    'acuario'      => ['bg' => '#e0f7fa', 'text' => '#006064', 'border' => '#80deea'],
    'general'      => ['bg' => '#f3e5f5', 'text' => '#6a1b9a', 'border' => '#ce93d8'],
    'interactivo'  => ['bg' => '#fce4ec', 'text' => '#880e4f', 'border' => '#f48fb1'],
];

function getColorRecorrido(string $nombre, array $map): array {
    $n = strtolower($nombre);
    foreach ($map as $key => $col) {
        if (strpos($n, $key) !== false) return $col;
    }
    return ['bg' => '#f5f5f5', 'text' => '#424242', 'border' => '#bdbdbd'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - ZooWonderland</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --selva-dark:    #0a3d1f;
            --selva-med:     #1b5e20;
            --selva-light:   #2e7d32;
            --jungle-gold:   #ffb300;
            --jungle-orange: #ff8c00;
            --sky-blue:      #1e88e5;
            --gris-bg:       #f0f7f4;
            --blanco:        #ffffff;
            --shadow-lg:     0 20px 60px rgba(0,0,0,0.15);
            --shadow-md:     0 10px 30px rgba(0,0,0,0.1);
            --trans:         all 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg,#f0f7f4 0%,#e8f5e9 50%,#f1f8e9 100%);
            color: #333;
            min-height: 100vh;
        }
        h1,h2,h3 { font-family:'Playfair Display',serif; }

        /* ── HEADER ── */
        header {
            background: linear-gradient(135deg,var(--selva-dark) 0%,var(--selva-med) 50%,var(--jungle-gold) 100%);
            padding: 1.5rem 5%;
            box-shadow: var(--shadow-lg);
            border-bottom: 4px solid var(--jungle-gold);
            position: sticky; top:0; z-index:1000;
        }
        nav {
            display:flex; justify-content:space-between; align-items:center;
            max-width:1400px; margin:0 auto; gap:3rem;
        }
        .logo {
            font-size:1.6rem; font-weight:800; color:var(--jungle-gold);
            text-shadow:2px 2px 4px rgba(0,0,0,.3);
            text-decoration:none; letter-spacing:-1px; white-space:nowrap;
        }
        .menu { display:flex; gap:2.5rem; align-items:center; flex-grow:1; }
        .menu a {
            color:white; text-decoration:none; font-weight:600; font-size:.95rem;
            transition:var(--trans); display:flex; align-items:center; gap:.6rem;
        }
        .menu a:hover, .menu a.active { color:var(--jungle-gold); transform:translateY(-2px); }
        .user-area { display:flex; align-items:center; gap:1.5rem; }
        .user-name {
            background:rgba(255,255,255,.15); padding:.6rem 1.2rem;
            border-radius:25px; color:white; font-weight:600; font-size:.9rem;
        }
        .logout-btn {
            background:var(--jungle-orange); color:white;
            padding:.7rem 1.5rem; border-radius:25px; border:none;
            cursor:pointer; font-weight:600; transition:var(--trans);
            box-shadow:0 4px 15px rgba(255,140,0,.3);
            text-decoration:none; display:inline-flex; align-items:center; gap:.5rem;
        }
        .logout-btn:hover { background:#e67e00; transform:translateY(-2px); }

        /* ── MAIN ── */
        main { max-width:1400px; margin:0 auto; padding:3rem 5%; }

        /* ── PAGE HEADER ── */
        .page-header { margin-bottom:2rem; animation: slideInDown .6s ease; }
        .page-header h1 {
            font-size:2.5rem;
            background: linear-gradient(135deg,var(--selva-dark),var(--jungle-gold));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text; font-weight:800;
        }
        .page-header p { color:#666; font-size:1rem; font-weight:300; margin-top:.3rem; }

        /* ── FLASH MSG ── */
        .flash {
            padding:1rem 1.5rem; border-radius:12px; margin-bottom:1.5rem;
            font-weight:600; display:flex; align-items:center; gap:.8rem;
            animation: fadeInUp .4s ease;
        }
        .flash.ok    { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
        .flash.error { background:#fdecea; color:#c62828; border:1px solid #ef9a9a; }

        /* ── FILTROS ── */
        .filtros-card {
            background:var(--blanco); border-radius:16px;
            padding:1.8rem 2rem; margin-bottom:2rem;
            box-shadow:var(--shadow-md);
            animation: fadeInUp .7s ease;
        }
        .filtros-card h3 {
            font-size:1.1rem; color:var(--selva-dark);
            margin-bottom:1.2rem; display:flex; align-items:center; gap:.6rem;
        }
        .filtros-grid {
            display:grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap:1rem; align-items:end;
        }
        @media(max-width:900px){ .filtros-grid{ grid-template-columns:1fr 1fr; } }
        @media(max-width:560px){ .filtros-grid{ grid-template-columns:1fr; } }

        .form-group { display:flex; flex-direction:column; gap:.4rem; }
        .form-group label { font-size:.8rem; font-weight:700; color:#666; text-transform:uppercase; letter-spacing:.5px; }
        .form-control {
            padding:.7rem 1rem; border:2px solid #e0e0e0; border-radius:10px;
            font-family:'Poppins',sans-serif; font-size:.9rem;
            transition:border-color .2s; outline:none;
            background:white;
        }
        .form-control:focus { border-color:var(--jungle-gold); }

        .btn-filtrar {
            background:linear-gradient(135deg,var(--selva-light),var(--jungle-gold));
            color:white; padding:.75rem 1.5rem; border-radius:10px;
            border:none; cursor:pointer; font-weight:700;
            display:inline-flex; align-items:center; gap:.5rem;
            font-family:'Poppins',sans-serif; font-size:.9rem;
            transition:var(--trans); white-space:nowrap;
            box-shadow:0 4px 15px rgba(46,125,50,.3);
        }
        .btn-filtrar:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(255,179,0,.4); }

        .btn-limpiar {
            background:var(--gris-bg); color:var(--selva-dark);
            padding:.75rem 1.2rem; border-radius:10px;
            border:1px solid #ccc; cursor:pointer; font-weight:600;
            font-family:'Poppins',sans-serif; font-size:.9rem;
            text-decoration:none; display:inline-flex; align-items:center; gap:.5rem;
            transition:var(--trans); white-space:nowrap;
        }
        .btn-limpiar:hover { background:#ddd; }

        /* ── TABLA CARD ── */
        .tabla-card {
            background:var(--blanco); border-radius:16px;
            box-shadow:var(--shadow-md); overflow:hidden;
            animation: fadeInUp .9s ease;
        }
        .tabla-header {
            padding:1.5rem 2rem; border-bottom:3px solid var(--gris-bg);
            display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;
        }
        .tabla-header h2 {
            font-size:1.6rem; color:var(--selva-dark);
            display:flex; align-items:center; gap:.7rem;
        }
        .tabla-header h2 i { color:var(--jungle-gold); }
        .contador-badge {
            background:var(--gris-bg); color:var(--selva-dark);
            padding:.35rem .9rem; border-radius:999px; font-size:.85rem; font-weight:700;
        }

        .tabla-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        thead {
            background:linear-gradient(90deg,#f5f5f5,#efefef);
            border-bottom:3px solid var(--jungle-gold);
        }
        th {
            padding:1.1rem 1.2rem; text-align:left;
            font-weight:700; color:var(--selva-dark);
            text-transform:uppercase; font-size:.8rem; letter-spacing:1px;
        }
        td { padding:1rem 1.2rem; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
        tbody tr { transition:background .2s; }
        tbody tr:hover { background:linear-gradient(90deg,transparent,rgba(255,179,0,.04),transparent); }

        /* Fila desactivada */
        tbody tr.inactivo td { opacity:.55; }
        tbody tr.inactivo { background:#fafafa; }

        /* ── BADGES ── */
        .badge {
            display:inline-flex; align-items:center; gap:.3rem;
            padding:.3rem .8rem; border-radius:999px;
            font-size:.75rem; font-weight:700; letter-spacing:.3px;
        }
        .badge-admin    { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
        .badge-guia     { background:#e3f2fd; color:#1565c0; border:1px solid #90caf9; }
        .badge-cliente  { background:#fff3e0; color:#e65100; border:1px solid #ffcc80; }
        .badge-activo   { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
        .badge-inactivo { background:#fdecea; color:#c62828; border:1px solid #ef9a9a; }

        /* Tags de recorridos */
        .recorrido-tags { display:flex; flex-wrap:wrap; gap:.3rem; }
        .rtag {
            padding:.2rem .6rem; border-radius:999px;
            font-size:.7rem; font-weight:700;
            border:1px solid;
        }

        /* ── ACCIONES ── */
        .acciones { display:flex; gap:.6rem; align-items:center; flex-wrap:wrap; }
        .btn-editar {
            color:var(--selva-light); background:rgba(46,125,50,.1);
            padding:.45rem .9rem; border-radius:8px;
            text-decoration:none; font-weight:600; font-size:.82rem;
            display:inline-flex; align-items:center; gap:.4rem;
            transition:var(--trans);
        }
        .btn-editar:hover { background:rgba(46,125,50,.25); transform:translateX(2px); }

        .btn-toggle {
            padding:.45rem .9rem; border-radius:8px;
            border:none; cursor:pointer; font-weight:600; font-size:.82rem;
            display:inline-flex; align-items:center; gap:.4rem;
            font-family:'Poppins',sans-serif; transition:var(--trans);
        }
        .btn-desactivar { color:#ff6f6f; background:rgba(255,111,111,.1); }
        .btn-desactivar:hover { background:rgba(255,111,111,.25); transform:translateX(2px); }
        .btn-activar { color:#2e7d32; background:rgba(46,125,50,.1); }
        .btn-activar:hover { background:rgba(46,125,50,.25); transform:translateX(2px); }

        /* ── EMPTY STATE ── */
        .empty-state { text-align:center; padding:4rem 2rem; color:#bbb; }
        .empty-state i { font-size:4rem; margin-bottom:1rem; opacity:.4; }
        .empty-state p { font-size:1.05rem; color:#999; }

        /* ── FOOTER ── */
        footer {
            background:var(--selva-dark); color:white;
            text-align:center; padding:2rem; margin-top:3rem; font-size:.9rem;
        }

        /* ── ANIMACIONES ── */
        @keyframes slideInDown {
            from { opacity:0; transform:translateY(-20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <nav>
        <a href="index.php?r=admin/dashboard" class="logo">
            <i class="fas fa-leaf"></i> ZooWonderland
        </a>
        <div class="menu">
            <a href="index.php?r=admin/dashboard"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="index.php?r=admin/recorridos"><i class="fas fa-route"></i> Recorridos</a>
            <a href="index.php?r=admin/areas"><i class="fas fa-map"></i> Áreas</a>
            <a href="index.php?r=admin/animales"><i class="fas fa-paw"></i> Animales</a>
            <a href="index.php?r=admin/reservas"><i class="fas fa-calendar-alt"></i> Reservas</a>
            <a href="index.php?r=admin/usuarios" class="active"><i class="fas fa-user-group"></i> Usuarios</a>
            <a href="index.php?r=admin/reportes"><i class="fas fa-file-chart-line"></i> Reportes</a>
        </div>
        <div class="user-area">
            <span class="user-name">👋 <?= htmlspecialchars($user->getNombreParaMostrar()) ?></span>
            <a href="index.php?r=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </nav>
</header>

<main>
    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1><i class="fas fa-users-gear"></i> Gestión de Usuarios</h1>
        <p>Administra cuentas, roles y permisos del sistema • <?= date('d/m/Y H:i') ?></p>
    </div>

    <!-- FLASH -->
    <?php if ($mensaje): ?>
        <div class="flash <?= htmlspecialchars($tipoMsg) ?>">
            <i class="fas fa-<?= $tipoMsg === 'ok' ? 'circle-check' : 'circle-xmark' ?>"></i>
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- FILTROS -->
    <div class="filtros-card">
        <h3><i class="fas fa-filter" style="color:var(--jungle-gold)"></i> Buscar y Filtrar</h3>
        <form method="GET" action="index.php">
            <input type="hidden" name="r" value="admin/usuarios">
            <div class="filtros-grid">
                <!-- Búsqueda -->
                <div class="form-group">
                    <label><i class="fas fa-search"></i> Buscar por CI, nombre o apellido</label>
                    <input type="text" name="busqueda" class="form-control"
                           placeholder="Ej: 800007 o Juan Mendoza"
                           value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                </div>
                <!-- Filtro rol -->
                <div class="form-group">
                    <label><i class="fas fa-id-badge"></i> Rol</label>
                    <select name="rol" class="form-control">
                        <option value="">Todos los roles</option>
                        <option value="administrador" <?= ($_GET['rol'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                        <option value="guia"          <?= ($_GET['rol'] ?? '') === 'guia'          ? 'selected' : '' ?>>Guía</option>
                        <option value="cliente"       <?= ($_GET['rol'] ?? '') === 'cliente'       ? 'selected' : '' ?>>Cliente</option>
                    </select>
                </div>
                <!-- Filtro recorrido -->
                <div class="form-group">
                    <label><i class="fas fa-route"></i> Recorrido asignado</label>
                    <select name="recorrido" class="form-control">
                        <option value="0">Todos los recorridos</option>
                        <?php foreach ($recorridos as $rec): ?>
                            <option value="<?= $rec['id_recorrido'] ?>"
                                <?= (int)($_GET['recorrido'] ?? 0) === (int)$rec['id_recorrido'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rec['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Filtro estado -->
                <div class="form-group">
                    <label><i class="fas fa-toggle-on"></i> Estado</label>
                    <select name="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="1" <?= ($_GET['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
                        <option value="0" <?= ($_GET['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                <!-- Botones -->
                <div style="display:flex; gap:.6rem; flex-wrap:wrap;">
                    <button type="submit" class="btn-filtrar">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="index.php?r=admin/usuarios" class="btn-limpiar">
                        <i class="fas fa-xmark"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- TABLA -->
    <div class="tabla-card">
        <div class="tabla-header">
            <h2><i class="fas fa-table-list"></i> Usuarios</h2>
            <span class="contador-badge">
                <i class="fas fa-users"></i> <?= count($usuarios) ?> resultado<?= count($usuarios) !== 1 ? 's' : '' ?>
            </span>
        </div>

        <?php if (empty($usuarios)): ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <p>No se encontraron usuarios con los filtros aplicados.</p>
            </div>
        <?php else: ?>
            <div class="tabla-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre completo</th>
                            <th>CI</th>
                            <th>Correo / Tel.</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Recorridos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u):
                            $inactivo   = (int)$u['estado'] === 0;
                            $nombreFull = trim(
                                htmlspecialchars($u['nombre1']) . ' ' .
                                htmlspecialchars($u['nombre2'] ?? '') . ' ' .
                                htmlspecialchars($u['apellido1']) . ' ' .
                                htmlspecialchars($u['apellido2'] ?? '')
                            );
                            // Badge rol
                            $rolBadge = match($u['rol']) {
                                'administrador' => ['class' => 'badge-admin',   'icon' => 'fa-crown',        'label' => 'Admin'],
                                'guia'          => ['class' => 'badge-guia',    'icon' => 'fa-person-hiking','label' => 'Guía'],
                                default         => ['class' => 'badge-cliente', 'icon' => 'fa-user',         'label' => 'Cliente'],
                            };
                        ?>
                        <tr class="<?= $inactivo ? 'inactivo' : '' ?>">
                            <td style="color:#aaa; font-size:.85rem;"><?= $u['id_usuario'] ?></td>
                            <td>
                                <strong><?= $nombreFull ?></strong>
                                <?php if ($u['rol'] === 'guia' && !empty($u['guia_horarios'])): ?>
                                    <br><small style="color:#888;">
                                        <i class="fas fa-clock"></i> <?= htmlspecialchars($u['guia_horarios']) ?>
                                        &nbsp;·&nbsp; <?= htmlspecialchars($u['guia_dias'] ?? '') ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:700; color:var(--selva-dark);"><?= $u['ci'] ?></td>
                            <td>
                                <small><?= htmlspecialchars($u['correo']) ?></small><br>
                                <small style="color:#888;">
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($u['telefono'] ?? '—') ?>
                                </small>
                            </td>
                            <td>
                                <code style="background:#f5f5f5; padding:.2rem .5rem; border-radius:5px; font-size:.82rem;">
                                    <?= htmlspecialchars($u['nombre_usuario']) ?>
                                </code>
                            </td>
                            <td>
                                <span class="badge <?= $rolBadge['class'] ?>">
                                    <i class="fas <?= $rolBadge['icon'] ?>"></i>
                                    <?= $rolBadge['label'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($u['recorridos_asignados'])): ?>
                                    <div class="recorrido-tags">
                                        <?php foreach (explode(', ', $u['recorridos_asignados']) as $rNombre):
                                            $col = getColorRecorrido($rNombre, $coloresRecorrido);
                                        ?>
                                        <span class="rtag" style="
                                            background:<?= $col['bg'] ?>;
                                            color:<?= $col['text'] ?>;
                                            border-color:<?= $col['border'] ?>;">
                                            <?= htmlspecialchars($rNombre) ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#ccc; font-size:.82rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $inactivo ? 'badge-inactivo' : 'badge-activo' ?>">
                                    <i class="fas <?= $inactivo ? 'fa-ban' : 'fa-circle-check' ?>"></i>
                                    <?= $inactivo ? 'Inactivo' : 'Activo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="acciones">
                                    <!-- Editar -->
                                    <a href="index.php?r=admin/usuario-editar&id=<?= $u['id_usuario'] ?>"
                                       class="btn-editar" title="Editar usuario">
                                        <i class="fas fa-pen-to-square"></i> Editar
                                    </a>
                                    <!-- Toggle estado -->
                                    <form method="POST" action="index.php?r=admin/usuario-toggle"
                                          onsubmit="return confirm('¿Confirmas el cambio de estado?')">
                                        <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                                        <input type="hidden" name="estado"     value="<?= $u['estado'] ?>">
                                        <button type="submit"
                                            class="btn-toggle <?= $inactivo ? 'btn-activar' : 'btn-desactivar' ?>"
                                            title="<?= $inactivo ? 'Activar' : 'Desactivar' ?> cuenta">
                                            <i class="fas <?= $inactivo ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                            <?= $inactivo ? 'Activar' : 'Desactivar' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> ZooWonderland - Panel de Administración. Educación y Conservación.</p>
</footer>

</body>
</html>