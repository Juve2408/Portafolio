<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header("Location: dos.php"); 
    exit(); 
}
include 'conexion.php';

// Obtener delitos de la base de datos
$delitos = [];
$resD = $conn->query("SELECT * FROM delitos");
while($r = $resD->fetch_assoc()) $delitos[] = $r;

// Obtener faltas de la base de datos
$faltas = [];
$resF = $conn->query("SELECT * FROM faltas");
while($r = $resF->fetch_assoc()) $faltas[] = $r;

// Para ELIMINAR 
if (isset($_GET['delete_pandilla'])) {
    $id = $_GET['delete_pandilla'];
    $conn->query("DELETE FROM Pandillas WHERE id_pandilla = $id");
    header("Location: diez.php?msg=Pandilla eliminada");
    exit();
}
if (isset($_GET['delete_integrante'])) {
    $id = $_GET['delete_integrante'];
    $conn->query("DELETE FROM integrantes WHERE id_integrante = $id");
    header("Location: diez.php?msg=Integrante eliminado");
    exit();
}

// GUARDAR CAMBIOS (PANDILLA)
if (isset($_POST['update_pandilla'])) {
    $id = $_POST['id_pandilla'];
    $nombre = $_POST['nombrePandilla'];
    $alias = $_POST['alias'];
    $zona = $_POST['zona'];
    $desc = $_POST['descripcion'];
    $num = $_POST['numIntegrantes'];
    $pelig = $_POST['peligrosidad'];
    $lat = $_POST['latitud'];
    $lon = $_POST['longitud'];
    
    // Convertir delitos seleccionados a texto
    $delitos_pandillas = '';
    if (isset($_POST['delitos_pandillas']) && !empty($_POST['delitos_pandillas'])) {
        $delitos_seleccionados = [];
        foreach ($_POST['delitos_pandillas'] as $id_delito) {
            foreach ($delitos as $delito) {
                if ($delito['id_delito'] == $id_delito) {
                    $delitos_seleccionados[] = $delito['nombre_delito'];
                    break;
                }
            }
        }
        $delitos_pandillas = implode(', ', $delitos_seleccionados);
    }

    // Convertir faltas seleccionadas a texto
    $faltas_pandillas = '';
    if (isset($_POST['faltas_pandillas']) && !empty($_POST['faltas_pandillas'])) {
        $faltas_seleccionadas = [];
        foreach ($_POST['faltas_pandillas'] as $id_falta) {
            foreach ($faltas as $falta) {
                if ($falta['id_falta'] == $id_falta) {
                    $faltas_seleccionadas[] = $falta['nombre_falta'];
                    break;
                }
            }
        }
        $faltas_pandillas = implode(', ', $faltas_seleccionadas);
    }

    $sql = "UPDATE Pandillas SET nombre=?, alias=?, zona=?, descripcion=?, num_integrantes_aproximado=?, peligrosidad=?, latitud_reunion=?, longitud_reunion=?, delitos_pandillas=?, faltas_pandillas=? WHERE id_pandilla=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisssssi", $nombre, $alias, $zona, $desc, $num, $pelig, $lat, $lon, $delitos_pandillas, $faltas_pandillas, $id);
    $stmt->execute();
    echo "<script>alert('Pandilla actualizada'); window.location='diez.php';</script>";
}

//  GUARDAR CAMBIOS (INTEGRANTE) 
if (isset($_POST['update_integrante'])) {
    $id = $_POST['id_integrante'];
    $nom = $_POST['nombres'];
    $ap = $_POST['apellido_paterno'];
    $am = $_POST['apellido_materno'] ?? '';
    $al = $_POST['alias'];
    $id_pan = $_POST['pandilla'];
    $carac = $_POST['caracteristicasFisicas'] ?? '';
    $redes = $_POST['redes_sociales'] ?? '';
    $dir = $_POST['direccion'] ?? '';
    $lat = $_POST['latitud'] ?? '';
    $lon = $_POST['longitud'] ?? '';
    $nac = $_POST['fecha_nacimiento'] ?? '';
    
    // Convertir delitos seleccionados a texto
    $delitos_txt = '';
    if (isset($_POST['delitos']) && !empty($_POST['delitos'])) {
        $delitos_seleccionados = [];
        foreach ($_POST['delitos'] as $id_delito) {
            foreach ($delitos as $delito) {
                if ($delito['id_delito'] == $id_delito) {
                    $delitos_seleccionados[] = $delito['nombre_delito'];
                    break;
                }
            }
        }
        $delitos_txt = implode(', ', $delitos_seleccionados);
    }

    // Convertir faltas seleccionadas a texto
    $faltas_txt = '';
    if (isset($_POST['faltas']) && !empty($_POST['faltas'])) {
        $faltas_seleccionadas = [];
        foreach ($_POST['faltas'] as $id_falta) {
            foreach ($faltas as $falta) {
                if ($falta['id_falta'] == $id_falta) {
                    $faltas_seleccionadas[] = $falta['nombre_falta'];
                    break;
                }
            }
        }
        $faltas_txt = implode(', ', $faltas_seleccionadas);
    }
    
    // Foto
    $foto = $_POST['foto_actual'];
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid() . '.' . $ext;
        if (!is_dir('uploads/fotos_integrantes/')) mkdir('uploads/fotos_integrantes/', 0777, true);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/fotos_integrantes/' . $foto);
    }

    // Actualización completa del integrante con delitos y faltas
    $sql = "UPDATE integrantes SET nombres=?, apellido_paterno=?, apellido_materno=?, alias=?, id_pandilla=?, fotografia=?, informacion_adicional=?, delitos_asociados=?, faltas_cometidas=?, redes_sociales=?, direccion_vivienda=?, latitud_vivienda=?, longitud_vivienda=?, fecha_nacimiento=? WHERE id_integrante=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisssssssssi", $nom, $ap, $am, $al, $id_pan, $foto, $carac, $delitos_txt, $faltas_txt, $redes, $dir, $lat, $lon, $nac, $id);
    $stmt->execute();
    echo "<script>alert('Integrante actualizado'); window.location='diez.php';</script>";
}

// --- DETERMINAR VISTA ACTUAL ---
$vista = 'lista'; // por defecto
$data_editar = null;
$delitos_act = []; // Para delitos activos
$faltas_act = []; // Para faltas activas

if (isset($_GET['edit_pandilla'])) {
    $vista = 'edit_pandilla';
    $id = $_GET['edit_pandilla'];
    $data_editar = $conn->query("SELECT * FROM Pandillas WHERE id_pandilla=$id")->fetch_assoc();
    
    // Obtener delitos actuales de la pandilla
    $delitos_string = $data_editar['delitos_pandillas'] ?? ''; 
    $delitos_act = !empty($delitos_string) ? explode(', ', $delitos_string) : [];
    
    // Obtener faltas actuales de la pandilla
    $faltas_string = $data_editar['faltas_pandillas'] ?? ''; 
    $faltas_act = !empty($faltas_string) ? explode(', ', $faltas_string) : [];
}

if (isset($_GET['edit_integrante'])) {
    $vista = 'edit_integrante';
    $id = $_GET['edit_integrante'];
    $data_editar = $conn->query("SELECT * FROM integrantes WHERE id_integrante=$id")->fetch_assoc();
    
    // Obtener delitos actuales del integrante
    $delitos_string = $data_editar['delitos_asociados'] ?? '';
    $delitos_act = !empty($delitos_string) ? explode(', ', $delitos_string) : [];
    
    // Obtener faltas actuales del integrante
    $faltas_string = $data_editar['faltas_cometidas'] ?? '';
    $faltas_act = !empty($faltas_string) ? explode(', ', $faltas_string) : [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Unificada</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .top-nav { background-color: #0d47a1; color: white; padding: 10px 0; display: flex; }
        .nav-item { padding: 10px 20px; cursor: pointer; text-decoration: none; color: white; border-right: 1px solid #1976d2; }
        .nav-item.active { background-color: white; color: #0d47a1; font-weight: bold; }
        .main-content { padding: 20px; flex-grow: 1; }
        .form-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 1000px; margin: 0 auto; }
        
        /* Tablas */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #0d47a1; color: white; }
        
        /* Botones */
        .btn { padding: 5px 10px; text-decoration: none; color: white; border-radius: 4px; font-size: 12px; margin-right: 5px; }
        .btn-edit { background-color: #FF9800; }
        .btn-del { background-color: #F44336; }
        .btn-cancel { background-color: #777; padding: 10px; display:inline-block; margin-top:10px;}

        /* Formularios */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .form-group { display: flex; flex-direction: column; }
        input, select, textarea { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .full-width { grid-column: span 2; }
        .checkbox-group { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; margin-bottom: 15px; }
        .section-title { background-color: #f0f0f0; padding: 10px; border-radius: 5px; margin-top: 20px; font-weight: bold; color: #0d47a1; }
        
        /* Estilos para checkboxes */
        .checkbox-item { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; }
        .checkbox-item input[type="checkbox"] { width: auto; }
        
        /* Estilos específicos para secciones de delitos y faltas */
        .checkbox-section { margin: 15px 0; padding: 15px; background-color: #f9f9f9; border-radius: 8px; border: 1px solid #ddd; }
        .checkbox-section h4 { margin: 0 0 10px 0; color: #333; }
        .checkbox-section-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; margin-bottom: 15px; }
        .select-all { margin-top: 10px; padding: 5px 10px; background: #e3f2fd; border: 1px solid #bbdefb; border-radius: 4px; cursor: pointer; display: inline-block; }
        .select-all:hover { background: #bbdefb; }
    </style>
    <script>
        function toggleAll(section, checkAllId) {
            const checkAll = document.getElementById(checkAllId);
            const checkboxes = document.querySelectorAll(`input[name="${section}[]"]`);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = checkAll.checked;
            });
        }
        
        function previsualizarImagen(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview');
                if (output) {
                    output.src = reader.result;
                    output.style.display = 'block';
                }
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>

    <div class="top-nav" id="adminNav">
        <a class="nav-item" href="cuatro.php">Captura pandillas</a>
        <a class="nav-item" href="cinco.php">Captura integrantes</a>
        <a class="nav-item" href="seis.php">Reportes consultas</a>
        <a class="nav-item" href="siete.php">Sistema mapa</a>
        <a class="nav-item active" href="diez.php">Modificaciones/Eliminar</a>
        <a class="nav-item" href="ocho.php">Ayuda soporte</a>
        <a class="nav-item" href="nueve.php">Agregar usuario</a>
        <a class="nav-item" href="tres.php">Inicio</a>
    </div>

    <div class="main-content">

        <?php if ($vista == 'lista'): ?>
            <h2 style="color: #0d47a1; border-bottom: 2px solid #ccc;">Panel de Gestión</h2>
            
            <div class="form-container" style="margin-bottom: 30px;">
                <h3>Pandillas Registradas</h3>
                <table>
                    <thead><tr><th>Nombre</th><th>Zona</th><th>Peligrosidad</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT * FROM Pandillas LIMIT 50");
                        while($r = $res->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$r['nombre']}</td>
                                    <td>{$r['zona']}</td>
                                    <td>{$r['peligrosidad']}</td>
                                    <td>
                                        <a href='diez.php?edit_pandilla={$r['id_pandilla']}' class='btn btn-edit'>Editar</a>
                                        <a href='diez.php?delete_pandilla={$r['id_pandilla']}' class='btn btn-del' onclick='return confirm(\"¿Borrar esta pandilla?\")'>Eliminar</a>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="form-container">
                <h3>Integrantes Registrados</h3>
                <table>
                    <thead><tr><th>Nombre</th><th>Alias</th><th>Pandilla ID</th><th>Delitos</th><th>Faltas</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT * FROM integrantes LIMIT 50");
                        while($r = $res->fetch_assoc()) {
                           
                            $delitos_display = strlen($r['delitos_asociados']) > 30 ? substr($r['delitos_asociados'], 0, 30) . '...' : $r['delitos_asociados'];
                            $faltas_display = strlen($r['faltas_cometidas']) > 30 ? substr($r['faltas_cometidas'], 0, 30) . '...' : $r['faltas_cometidas'];
                            
                            echo "<tr>
                                    <td>{$r['nombres']} {$r['apellido_paterno']}</td>
                                    <td>{$r['alias']}</td>
                                    <td>{$r['id_pandilla']}</td>
                                    <td>{$delitos_display}</td>
                                    <td>{$faltas_display}</td>
                                    <td>
                                        <a href='diez.php?edit_integrante={$r['id_integrante']}' class='btn btn-edit'>Editar</a>
                                        <a href='diez.php?delete_integrante={$r['id_integrante']}' class='btn btn-del' onclick='return confirm(\"¿Borrar este integrante?\")'>Eliminar</a>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>


        <?php if ($vista == 'edit_pandilla'): ?>
            <div class="form-container">
                <h2 style="color:#0d47a1">Editar Pandilla: <?php echo $data_editar['nombre']; ?></h2>
                <form method="POST">
                    <input type="hidden" name="id_pandilla" value="<?php echo $data_editar['id_pandilla']; ?>">
                    <input type="hidden" name="update_pandilla" value="1">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre:</label>
                            <input type="text" name="nombrePandilla" value="<?php echo $data_editar['nombre']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Alias:</label>
                            <input type="text" name="alias" value="<?php echo $data_editar['alias']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Zona:</label>
                            <input type="text" name="zona" value="<?php echo $data_editar['zona']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Peligrosidad:</label>
                            <select name="peligrosidad">
                                <option value="Baja" <?php if($data_editar['peligrosidad']=='Baja') echo 'selected'; ?>>Baja</option>
                                <option value="Media" <?php if($data_editar['peligrosidad']=='Media') echo 'selected'; ?>>Media</option>
                                <option value="Alta" <?php if($data_editar['peligrosidad']=='Alta') echo 'selected'; ?>>Alta</option>
                                <option value="Extrema" <?php if($data_editar['peligrosidad']=='Extrema') echo 'selected'; ?>>Extrema</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Latitud:</label>
                            <input type="text" name="latitud" value="<?php echo $data_editar['latitud_reunion']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Longitud:</label>
                            <input type="text" name="longitud" value="<?php echo $data_editar['longitud_reunion']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Num Integrantes:</label>
                            <input type="number" name="numIntegrantes" value="<?php echo $data_editar['num_integrantes_aproximado']; ?>">
                        </div>
                        <div class="form-group full-width">
                            <label>Descripción:</label>
                            <textarea name="descripcion"><?php echo $data_editar['descripcion']; ?></textarea>
                        </div>
                        
                        <!-- Sección de Delitos para Pandillas -->
                        <div class="form-group full-width">
                            <div class="section-title">Delitos Asociados a la Pandilla</div>
                            <div class="checkbox-section">
                                <div class="checkbox-section-grid">
                                    <?php foreach($delitos as $d): 
                                        // Verificar si este delito está en los delitos actuales de la pandilla
                                        $delito_nombre = $d['nombre_delito'];
                                        $checked = in_array($delito_nombre, $delitos_act) ? 'checked' : '';
                                    ?>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="delitos_pandillas[]" value="<?php echo $d['id_delito']; ?>" <?php echo $checked; ?>> 
                                            <?php echo htmlspecialchars($delito_nombre); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="select-all" onclick="toggleAll('delitos_pandillas', 'toggleAllDelitosPandilla')">
                                    <input type="checkbox" id="toggleAllDelitosPandilla"> Seleccionar todos los delitos
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección de Faltas para Pandillas -->
                        <div class="form-group full-width">
                            <div class="section-title">Faltas de la Pandilla</div>
                            <div class="checkbox-section">
                                <div class="checkbox-section-grid">
                                    <?php foreach($faltas as $f): 
                                        // Verificar si esta falta está en las faltas actuales de la pandilla
                                        $falta_nombre = $f['nombre_falta'];
                                        $checked = in_array($falta_nombre, $faltas_act) ? 'checked' : '';
                                    ?>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="faltas_pandillas[]" value="<?php echo $f['id_falta']; ?>" <?php echo $checked; ?>> 
                                            <?php echo htmlspecialchars($falta_nombre); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="select-all" onclick="toggleAll('faltas_pandillas', 'toggleAllFaltasPandilla')">
                                    <input type="checkbox" id="toggleAllFaltasPandilla"> Seleccionar todas las faltas
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" style="background:#0d47a1; color:white; padding:15px; border:none; width:100%; margin-top:20px; font-size:16px;">Guardar Cambios Pandilla</button>
                    <a href="diez.php" class="btn btn-cancel text-center" style="display:block; text-align:center; color:white; text-decoration:none;">Cancelar</a>
                </form>
            </div>
        <?php endif; ?>


        <?php if ($vista == 'edit_integrante'): ?>
            <div class="form-container">
                <h2 style="color:#0d47a1">Editar Integrante</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_integrante" value="<?php echo $data_editar['id_integrante']; ?>">
                    <input type="hidden" name="update_integrante" value="1">
                    <input type="hidden" name="foto_actual" value="<?php echo $data_editar['fotografia']; ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Pandilla *</label>
                            <select name="pandilla" required>
                                <option value="">-- Seleccionar --</option>
                                <?php 
                                $res_p = $conn->query("SELECT id_pandilla, nombre FROM Pandillas ORDER BY nombre");
                                while($pan = $res_p->fetch_assoc()) {
                                    $selected = ($pan['id_pandilla'] == $data_editar['id_pandilla']) ? 'selected' : '';
                                    echo "<option value='{$pan['id_pandilla']}' $selected>{$pan['nombre']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Nombres *</label>
                            <input type="text" name="nombres" value="<?php echo $data_editar['nombres']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" value="<?php echo $data_editar['apellido_paterno']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Apellido Materno</label>
                            <input type="text" name="apellido_materno" value="<?php echo $data_editar['apellido_materno']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Alias</label>
                            <input type="text" name="alias" value="<?php echo $data_editar['alias']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Fecha Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" value="<?php echo $data_editar['fecha_nacimiento']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Dirección</label>
                            <input type="text" name="direccion" value="<?php echo $data_editar['direccion_vivienda'] ?? ''; ?>" placeholder="Calle y número">
                        </div>
                        
                        <div class="form-group">
                            <label>Latitud</label>
                            <input type="text" name="latitud" value="<?php echo $data_editar['latitud_vivienda'] ?? ''; ?>" placeholder="Clic en mapa o escribir">
                        </div>
                        
                        <div class="form-group">
                            <label>Longitud</label>
                            <input type="text" name="longitud" value="<?php echo $data_editar['longitud_vivienda'] ?? ''; ?>" placeholder="Clic en mapa o escribir">
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Características / Información Adicional</label>
                            <textarea name="caracteristicasFisicas" rows="3"><?php echo $data_editar['informacion_adicional'] ?? ''; ?></textarea>
                        </div>
                        
                        <!-- SECCIÓN DE DELITOS ASOCIADOS (IGUAL QUE EN CINCO.PHP) -->
                        <div class="form-group full-width">
                            <div class="checkbox-section">
                                <h4>Delitos Asociados</h4>
                                <div class="checkbox-section-grid">
                                    <?php foreach($delitos as $d): 
                                        // Verificar si este delito está en los delitos actuales del integrante
                                        $delito_nombre = $d['nombre_delito'];
                                        $checked = in_array($delito_nombre, $delitos_act) ? 'checked' : '';
                                    ?>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="delitos[]" value="<?php echo $d['id_delito']; ?>" <?php echo $checked; ?>> 
                                            <?php echo htmlspecialchars($delito_nombre); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="select-all" onclick="toggleAll('delitos', 'toggleAllDelitosInt')">
                                    <input type="checkbox" id="toggleAllDelitosInt"> Seleccionar todos los delitos
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN DE FALTAS COMETIDAS (IGUAL QUE EN CINCO.PHP) -->
                        <div class="form-group full-width">
                            <div class="checkbox-section">
                                <h4>Faltas Cometidas</h4>
                                <div class="checkbox-section-grid">
                                    <?php foreach($faltas as $f): 
                                        // Verificar si esta falta está en las faltas actuales del integrante
                                        $falta_nombre = $f['nombre_falta'];
                                        $checked = in_array($falta_nombre, $faltas_act) ? 'checked' : '';
                                    ?>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="faltas[]" value="<?php echo $f['id_falta']; ?>" <?php echo $checked; ?>> 
                                            <?php echo htmlspecialchars($falta_nombre); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="select-all" onclick="toggleAll('faltas', 'toggleAllFaltasInt')">
                                    <input type="checkbox" id="toggleAllFaltasInt"> Seleccionar todas las faltas
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Redes Sociales</label>
                            <input type="text" name="redes_sociales" value="<?php echo $data_editar['redes_sociales'] ?? ''; ?>" placeholder="Separar con comas">
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Fotografía</label>
                            <input type="file" name="foto" accept="image/*" onchange="previsualizarImagen(event)">
                            <?php if (!empty($data_editar['fotografia'])): ?>
                                <div style="margin-top: 10px; text-align: center;">
                                    <p style="font-size:12px; color:#666;">Foto actual: <?php echo $data_editar['fotografia']; ?></p>
                                    <div style="width: 200px; height: 150px; background: #eee; display: inline-block; border: 1px solid #ccc; overflow: hidden;">
                                        <img src="uploads/fotos_integrantes/<?php echo $data_editar['fotografia']; ?>" alt="Foto actual" style="max-width: 100%; max-height: 100%;">
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" style="background:#0d47a1; color:white; padding:15px; border:none; width:100%; margin-top:20px; font-size:16px;">Guardar Cambios Integrante</button>
                    <a href="diez.php" class="btn btn-cancel" style="display:block; text-align:center; color:white; text-decoration:none; margin-top: 10px;">Cancelar</a>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>