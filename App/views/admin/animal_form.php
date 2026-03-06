<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $animal ? 'Editar' : 'Nuevo'; ?> Animal - ZooWonderland</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-p1Cm..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* estilos básicos para formulario, reutilizamos algunos de los anteriores */
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #fafafa; margin: 0; color: #333; }
        header { background: #2E7D32; color: white; }
        header nav { max-width: 1400px; margin: 0 auto; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; }
        header .logo { font-size: 1.6rem; font-weight: bold; text-decoration: none; color: white; }
        header .menu a { color: white; text-decoration: none; margin-left: 1.5rem; font-weight: 600; }
        header .menu a:hover { text-decoration: underline; }
        main { max-width: 800px; margin: 0 auto; padding: 3rem 5%; }
        .form-section { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-section h2 { color: #2E7D32; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; }
        .btn { background: linear-gradient(135deg, #66BB6A, #FFC107); color: white; padding: 0.9rem 2rem; border: none; border-radius: 25px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; }
        .btn:hover { transform: translateY(-2px); }
        .back-link { display: inline-block; margin-top: 1rem; color: #555; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .error { color: #c0392b; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php?r=admin/dashboard" class="logo"><i class="fas fa-leaf"></i> ZooWonderland</a>
            <div class="menu">
                <a href="index.php?r=admin/dashboard" title="Dashboard"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="index.php?r=admin/recorridos" title="Gestionar Recorridos"><i class="fas fa-compass"></i> Recorridos</a>
                <a href="index.php?r=admin/animales" title="Gestionar Animales"><i class="fas fa-paw"></i> Animales</a>
                <a href="index.php?r=admin/areas" title="Gestionar Áreas"><i class="fas fa-shapes"></i> Áreas</a>
                <a href="index.php?r=admin/reservas" title="Reservas"><i class="fas fa-calendar-check"></i> Reservas</a>
                <a href="index.php?r=logout" title="Salir"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="form-section">
            <h2><?php echo $animal ? 'Editar' : 'Nuevo'; ?> Animal</h2>

            <?php if (!empty($_SESSION['form_errors'])): ?>
                <div class="error"><?php echo htmlspecialchars($_SESSION['form_errors']); unset($_SESSION['form_errors']); ?></div>
            <?php endif; ?>

            <form action="index.php?r=admin/animales/<?php echo $action; ?>" method="post">
                <?php
                $vals = ['especie'=>'', 'nombre'=>'', 'habitat'=>'', 'descripcion'=>'', 'estado'=>'Activo', 'areaId'=>0];
                if ($animal) {
                    // convertir estructura BD al formato esperado
                    $animal_data = is_array($animal) ? $animal : $animal->getInfo();
                    $vals = [
                        'especie' => $animal_data['especie'] ?? '',
                        'nombre' => $animal_data['nombre_comun'] ?? '',
                        'habitat' => $animal_data['habitat'] ?? '',
                        'descripcion' => $animal_data['descripcion'] ?? '',
                        'estado' => $animal_data['estado'] ?? 'Activo',
                        'areaId' => $animal_data['id_area'] ?? 0,
                    ];
                }
                ?>

                <div class="form-group">
                    <label for="especie">Especie *</label>
                    <input type="text" id="especie" name="especie" value="<?php echo htmlspecialchars($vals['especie']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre común</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($vals['nombre']); ?>">
                </div>
                <div class="form-group">
                    <label for="habitat">Hábitat *</label>
                    <input type="text" id="habitat" name="habitat" value="<?php echo htmlspecialchars($vals['habitat']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción *</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required><?php echo htmlspecialchars($vals['descripcion']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <?php $opts = ['Activo','Inactivo','En observación'];
                        foreach ($opts as $opt) {
                            $sel = $opt === $vals['estado'] ? 'selected' : '';
                            echo "<option value=\"$opt\" $sel>$opt</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="area_id">Área</label>
                    <select id="area_id" name="area_id">
                        <option value="0">-- Sin asignar --</option>
                        <?php foreach ($areas as $ar):
                            // cada área puede venir como array o como objeto
                            $aid = is_array($ar) ? $ar['id_area'] : $ar->getId();
                            $aname = is_array($ar) ? $ar['nombre'] : $ar->getNombre();
                            $sel = $aid == $vals['areaId'] ? 'selected' : '';
                            echo "<option value=\"$aid\" $sel>" . htmlspecialchars($aname) . "</option>";
                        endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn"><?php echo $animal ? 'Actualizar' : 'Guardar'; ?></button>
            </form>

            <a href="index.php?r=admin/animales" class="back-link"><i class="fas fa-arrow-left"></i> Volver a la lista</a>
        </div>
    </main>

    <footer style="text-align:center; padding:2rem; background:#2E7D32; color:white; font-size:0.9rem;">
        &copy; <?php echo date('Y'); ?> ZooWonderland - Panel de Administración.
    </footer>
</body>
</html>