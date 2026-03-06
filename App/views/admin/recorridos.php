<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Recorridos - ZooWonderland</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-p1Cm..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --selva-dark: #2E7D32;
            --selva-med: #43A047;
            --selva-light: #66BB6A;
            --jungle-gold: #FFC107;
            --sky-blue: #0288D1;
            --blanco: #ffffff;
            --gris-bg: #f5f5f5;
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-sm: 0 2px 6px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
            --trans: all 0.3s ease;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #fafafa;
            margin: 0;
            color: #333;
        }

        header {
            background: var(--selva-dark);
            color: white;
        }
        header nav {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header .logo {
            font-size: 1.6rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        header .menu a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            font-weight: 600;
        }
        header .menu a:hover { text-decoration: underline; }

        main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 5%;
        }

        .page-header h1 {
            font-size: 2.4rem;
            color: var(--selva-dark);
            margin-bottom: 0.5rem;
        }
        .page-header p { color: #666; }

        .content-section {
            background: var(--blanco);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            animation: fadeInUp 1s ease 0.2s both;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 3px solid var(--gris-bg);
            padding-bottom: 1.5rem;
        }
        .section-header h2 {
            font-size: 1.8rem;
            color: var(--selva-dark);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--selva-light), var(--jungle-gold));
            color: white;
            padding: 0.9rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: var(--trans);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3);
            letter-spacing: 0.5px;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(255, 179, 0, 0.4);
        }

        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(90deg, #f5f5f5 0%, #efefef 100%); border-bottom: 3px solid var(--jungle-gold); }
        th, td { padding: 1.2rem; text-align: left; }
        th { font-weight: 700; color: var(--selva-dark); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        tbody tr { transition: var(--trans); }
        tbody tr:hover { background: linear-gradient(90deg, transparent, rgba(255,179,0,0.05), transparent); }
        .actions { display: flex; gap: 1rem; font-size: 0.85rem; }
        .actions a { text-decoration: none; font-weight: 600; padding: 0.5rem 1rem; border-radius: 8px; transition: var(--trans); display: inline-flex; align-items: center; gap: 0.4rem; }
        .edit { color: var(--selva-light); background: rgba(46, 125, 50, 0.1); }
        .edit:hover { background: rgba(46, 125, 50, 0.25); transform: translateX(3px); }
        .delete { color: #ff6f6f; background: rgba(255, 111, 111, 0.1); }
        .delete:hover { background: rgba(255, 111, 111, 0.25); transform: translateX(3px); }

        .empty-state { text-align: center; padding: 4rem 2rem; color: #bbb; }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; color: #ddd; opacity: 0.5; }
        .empty-state p { font-size: 1.1rem; color: #999; }

        footer { background: var(--selva-dark); color: white; text-align: center; padding: 2rem; margin-top: 3rem; font-size: 0.9rem; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php?r=admin/dashboard" class="logo"><i class="fas fa-leaf"></i> ZooWonderland</a>
            <div class="menu">
                <a href="index.php?r=admin/dashboard" title="Dashboard"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="index.php?r=admin/recorridos" title="Gestionar Recorridos"><i class="fas fa-compass"></i> Recorridos</a>
                <a href="index.php?r=admin/areas" title="Gestionar Áreas"><i class="fas fa-shapes"></i> Áreas</a>
                <a href="index.php?r=admin/reservas" title="Reservas"><i class="fas fa-calendar-check"></i> Reservas</a>
                <a href="index.php?r=logout" title="Salir"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="page-header">
            <h1><i class="fas fa-compass"></i> Gestión de Recorridos</h1>
            <p>Aquí puedes crear, editar y eliminar recorridos del zoo.</p>
        </div>

        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-compass"></i> Recorridos</h2>
                <a href="index.php?r=admin/recorridos" class="btn-primary"><i class="fas fa-plus-circle"></i> Nuevo Recorrido</a>
            </div>

            <?php if (empty($recorridos)): ?>
                <div class="empty-state">
                    <i class="fas fa-compass"></i>
                    <p>No hay recorridos registrados aún. ¡Crea tu primer recorrido!</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>🏷️ Nombre</th>
                            <th>🎯 Tipo</th>
                            <th>💰 Precio</th>
                            <th>⚙️ Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recorridos as $rec): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($rec['nombre'] ?? ''); ?></strong></td>
                            <td><?php echo htmlspecialchars($rec['tipo'] ?? '—'); ?></td>
                            <td><strong>Bs. <?php echo number_format($rec['precio'] ?? 0, 2); ?></strong></td>
                            <td class="actions">
                                <a href="index.php?r=admin/recorridos/editar&id=<?php echo $rec['id_recorrido']; ?>" class="edit" title="Editar"><i class="fas fa-pen"></i> Editar</a>
                                <a href="index.php?r=admin/recorridos/eliminar&id=<?php echo $rec['id_recorrido']; ?>" class="delete" title="Eliminar" onclick="return confirm('¿Confirma eliminar este recorrido? Esta acción no afectará reservas activas.');"><i class="fas fa-trash"></i> Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> ZooWonderland - Panel de Administración. Educación y Conservación.</p>
    </footer>
</body>
</html>