<?php
// Vista del dashboard del administrador - Estructura estándar
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard ZooWonderland</title>
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
            --trans:         all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f7f4 0%, #e8f5e9 50%, #f1f8e9 100%);
            color: #333;
            min-height: 100vh;
        }

        h1, h2, h3 { font-family: 'Playfair Display', serif; }

        /* HEADER ESTÁNDAR */
        header {
            background: linear-gradient(135deg, var(--selva-dark) 0%, var(--selva-med) 50%, var(--jungle-gold) 100%);
            padding: 1.5rem 5%;
            box-shadow: var(--shadow-lg);
            border-bottom: 4px solid var(--jungle-gold);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            gap: 3rem;
        }

        .logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--jungle-gold);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            text-decoration: none;
            letter-spacing: -1px;
            white-space: nowrap;
        }

        .menu {
            display: flex;
            gap: 2.5rem;
            align-items: center;
            flex-grow: 1;
        }

        .menu a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--trans);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .menu a:hover {
            color: var(--jungle-gold);
            transform: translateY(-2px);
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-name {
            background: rgba(255, 255, 255, 0.15);
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: var(--jungle-orange);
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: var(--trans);
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: #e67e00;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 0, 0.5);
        }

        /* MAIN CONTENT */
        main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 5%;
        }

        /* PAGE HEADER */
        .page-header {
            margin-bottom: 3rem;
            animation: slideInDown 0.6s ease;
        }

        .page-header h1 {
            font-size: 2.8rem;
            background: linear-gradient(135deg, var(--selva-dark), var(--jungle-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .page-header p {
            color: #666;
            font-size: 1.05rem;
            font-weight: 300;
        }

        /* GRID ESTADÍSTICAS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 2rem;
            margin-bottom: 3.5rem;
            animation: fadeInUp 0.8s ease;
        }

        .stat-card {
            background: var(--blanco);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border-top: 5px solid var(--jungle-gold);
            transition: var(--trans);
            overflow: hidden;
            position: relative;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, var(--jungle-gold) 0%, transparent 70%);
            opacity: 0.03;
            border-radius: 50%;
        }

        .stat-card:nth-child(1) { border-top-color: #ff7043; }
        .stat-card:nth-child(2) { border-top-color: var(--sky-blue); }
        .stat-card:nth-child(3) { border-top-color: #7cb342; }
        .stat-card:nth-child(4) { border-top-color: var(--jungle-gold); }
        .stat-card:nth-child(5) { border-top-color: #ab47bc; }
        .stat-card:nth-child(6) { border-top-color: #0288d1; }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .stat-card:nth-child(1) .stat-icon { color: #ff7043; }
        .stat-card:nth-child(2) .stat-icon { color: var(--sky-blue); }
        .stat-card:nth-child(3) .stat-icon { color: #7cb342; }
        .stat-card:nth-child(4) .stat-icon { color: var(--jungle-gold); }
        .stat-card:nth-child(5) .stat-icon { color: #ab47bc; }
        .stat-card:nth-child(6) .stat-icon { color: #0288d1; }

        .stat-label {
            color: #888;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--selva-dark), var(--selva-med));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
        }

        /* SECCIÓN RECORRIDOS */
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

        .section-header h2 i {
            color: var(--jungle-gold);
            font-size: 2rem;
        }

        .section-note {
            margin-top: -1rem;
            margin-bottom: 1.5rem;
            color: #555;
            font-size: 0.95rem;
        }

        .section-footer {
            margin-top: 1rem;
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

        .btn-secondary {
            background: var(--gris-bg);
            color: var(--selva-dark);
            padding: 0.8rem 1.8rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: var(--trans);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* TABLA */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(90deg, #f5f5f5 0%, #efefef 100%);
            border-bottom: 3px solid var(--jungle-gold);
        }

        th {
            padding: 1.2rem;
            text-align: left;
            font-weight: 700;
            color: var(--selva-dark);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        td {
            padding: 1.2rem;
            border-bottom: 1px solid #f0f0f0;
        }

        tbody tr {
            transition: var(--trans);
        }

        tbody tr:hover {
            background: linear-gradient(90deg, transparent, rgba(255, 179, 0, 0.05), transparent);
        }

        /* ACCIONES */
        .actions {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
        }

        .actions a {
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: var(--trans);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .actions .edit {
            color: var(--selva-light);
            background: rgba(46, 125, 50, 0.1);
        }

        .actions .edit:hover {
            background: rgba(46, 125, 50, 0.25);
            transform: translateX(3px);
        }

        .actions .delete {
            color: #ff6f6f;
            background: rgba(255, 111, 111, 0.1);
        }

        .actions .delete:hover {
            background: rgba(255, 111, 111, 0.25);
            transform: translateX(3px);
        }

        /* TARJETAS DE RECORRIDOS (INFORMACIÓN GENERAL) */
        .recorridos-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .recorridos-cards .card {
            background: var(--blanco);
            border-radius: 15px;
            padding: 1.8rem;
            box-shadow: var(--shadow-sm);
            transition: var(--trans);
        }
        .recorridos-cards .card h3 {
            margin-top: 0;
            margin-bottom: 0.8rem;
            color: var(--selva-dark);
        }
        .recorridos-cards .card p {
            margin: 0.3rem 0;
            color: #555;
            font-size: 0.95rem;
        }


        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #bbb;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ddd;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1.1rem;
            color: #999;
        }

        /* FOOTER */
        footer {
            background: var(--selva-dark);
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
            font-size: 0.9rem;
        }

        /* ANIMACIONES */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .menu { gap: 1rem; font-size: 0.85rem; }
            .page-header h1 { font-size: 1.8rem; }
            .stats-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
            .section-header { flex-direction: column; align-items: flex-start; }
            table { font-size: 0.85rem; }
            th, td { padding: 0.8rem; }
        }
    </style>
</head>
<body>
    <!-- HEADER ESTÁNDAR -->
    <header>
        <nav>
            <a href="index.php?r=admin/dashboard" class="logo">
                <i class="fas fa-leaf"></i> ZooWonderland
            </a>
            <div class="menu">
                <a href="index.php?r=admin/dashboard" title="Dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="index.php?r=admin/recorridos" title="Gestionar Recorridos">
                    <i class="fas fa-route"></i> Recorridos
                </a>
                <a href="index.php?r=admin/areas" title="Gestionar Áreas">
                    <i class="fas fa-map"></i> Áreas
                </a>
                <a href="index.php?r=admin/animales" title="Gestionar Animales">
                    <i class="fas fa-paw"></i> Animales
                </a>
                <a href="index.php?r=admin/reservas" title="Ver Reservas">
                    <i class="fas fa-calendar-alt"></i> Reservas
                </a>
                <a href="index.php?r=admin/usuarios" title="Gestionar Usuarios">
                    <i class="fas fa-user-group"></i> Usuarios
                </a>
                <a href="index.php?r=admin/reportes" title="Ver Reportes">
                    <i class="fas fa-file-chart-line"></i> Reportes
                </a>
            </div>
            <div class="user-area">
                <span class="user-name">👋 <?php echo htmlspecialchars($user->getNombreParaMostrar()); ?></span>
                <a href="index.php?r=logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </nav>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main>
        <div class="page-header">
            <h1><i class="fas fa-crown"></i> Panel de Control</h1>
            <p>Gestiona ZooWonderland desde aquí • Última actualización: <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <!-- TARJETAS DE ESTADÍSTICAS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-map-location-dot"></i></div>
                <div class="stat-label">Recorridos</div>
                <div class="stat-value"><?php echo $totalRecorridos; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shapes"></i></div>
                <div class="stat-label">Áreas</div>
                <div class="stat-value"><?php echo $totalAreas; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-paw"></i></div>
                <div class="stat-label">Animales</div>
                <div class="stat-value"><?php echo $totalAnimales; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-label">Reservas Activas</div>
                <div class="stat-value"><?php echo $totalReservas; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-label">Ingresos Totales</div>
                <div class="stat-value">Bs. <?php echo number_format($totalIngresos, 0); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-person-hiking"></i></div>
                <div class="stat-label">Guías</div>
                <div class="stat-value"><?php echo $totalGuias; ?></div>
            </div>
        </div>

        <!-- INFORMACIÓN GENERAL DE RECORRIDOS -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-info-circle"></i> Información de Recorridos</h2>
            </div>
            <p class="section-note">Total de recorridos: <strong><?php echo $totalRecorridos; ?></strong></p>

            <?php if (empty($recorridos)): ?>
                <div class="empty-state">
                    <i class="fas fa-compass"></i>
                    <p>No hay recorridos registrados aún.</p>
                </div>
            <?php else: ?>
                <div class="recorridos-cards">
                    <?php foreach ($recorridos as $rec): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($rec['nombre'] ?? ''); ?></h3>
                            <p>Tipo: <?php echo htmlspecialchars($rec['tipo'] ?? '—'); ?></p>
                            <p>Precio: Bs. <?php echo number_format($rec['precio'] ?? 0, 2); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="section-footer">
                    <a href="index.php?r=admin/recorridos" class="btn-secondary">Ver tabla completa</a>
                </div>
            <?php endif; ?>
    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> ZooWonderland - Panel de Administración. Educación y Conservación.</p>
    </footer>
</body>
</html>
