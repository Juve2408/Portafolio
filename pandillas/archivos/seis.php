<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header("Location: dos.php"); 
    exit(); 
}
include 'conexion.php';


$pandillas_query = $conn->query("SELECT id_pandilla, nombre FROM Pandillas");
$pandillas = [];
while($row = $pandillas_query->fetch_assoc()) {
    $pandillas[] = $row;
}


$pandilla_seleccionada = isset($_GET['pandilla_id']) ? intval($_GET['pandilla_id']) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Consultas</title>
    // jsPDF y autoTable 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .top-nav { background-color: #0d47a1; color: white; padding: 10px 0; display: flex; }
        .nav-item { padding: 10px 20px; cursor: pointer; text-decoration: none; color: white; border-right: 1px solid #1976d2; }
        .nav-item.active { background-color: white; color: #0d47a1; font-weight: bold; }
        .main-content { padding: 20px; flex-grow: 1; }
        .report-section { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .section-title { font-size: 24px; margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .controls-row { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; min-width: 200px; }
        .form-group label { margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .footer { background-color: #333; color: white; padding: 20px; margin-top: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #0d47a1; color: white; }
        .hidden { display: none; }
        .export-btn { background-color: #0d47a1; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; margin-bottom: 15px; margin-right: 10px; }
        .map-btn { background-color: #4CAF50; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; margin-bottom: 15px; }
        .alert { background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #ffc107; }
        .map-alert { background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #17a2b8; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #f8f9fa; border-left: 4px solid #0d47a1; padding: 15px; border-radius: 5px; }
        .stat-value { font-size: 24px; font-weight: bold; color: #0d47a1; }
        .stat-label { font-size: 14px; color: #666; }
        .pandilla-header { background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #0d47a1; }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { font-weight: bold; min-width: 150px; }
        .info-value { color: #333; }
        .delito-falta-item { display: inline-block; background: #e9ecef; padding: 3px 8px; margin: 2px; border-radius: 3px; font-size: 12px; }
        .delito-item { background: #ffebee; border: 1px solid #ffcdd2; }
        .falta-item { background: #fff3e0; border: 1px solid #ffe0b2; }
        .frecuencia-badge { background: #0d47a1; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 5px; }
           table img {
       max-width: 60px;
     max-height: 60px;
     border-radius: 4px;
        border: 1px solid #ddd;
    }

    table td {
        vertical-align: middle;
    }

    .delito-falta-item {
        display: inline-block;
        font-size: 11px;
         padding: 2px 6px;
      margin: 1px;
      border-radius: 3px;
      white-space: nowrap;
    }
        </style>
    
</head>
<body>

    <div class="top-nav" id="adminNav">
        <a class="nav-item" href="cuatro.php">Captura pandillas</a>
        <a class="nav-item" href="cinco.php">Captura integrantes</a>
        <a class="nav-item active" href="seis.php">Reportes consultas</a>
        <a class="nav-item" href="siete.php">Sistema mapa</a>
        <a class="nav-item" href="diez.php">Modificaciones/Eliminar</a>
        <a class="nav-item" href="ocho.php">Ayuda soporte</a>
        <a class="nav-item" href="nueve.php">Agregar usuario</a>
        <a class="nav-item" href="tres.php">Inicio</a>
    </div>

    <div class="main-content">

        <div class="controls-row">
            <div class="form-group">
                <label>Elige tu tipo de reporte</label>
                <select id="tipoReporte" onchange="mostrarReporte()">
                    <option value="rinas">Riñas Pandilleriles</option>
                    <option value="peligrosidad">Grado de Peligrosidad</option>
                    <option value="faltas">Faltas Cometidas</option>
                    <option value="delitos">Delitos Cometidos</option>
                    <option value="general">Reporte general</option>
                </select>
            </div>
        </div>

        // Controles de filtro de fecha (para Riñas) 
        <div class="controls-row" id="filtroFecha" style="display: none;">
            <div class="form-group">
                <label>Fecha desde</label>
                <input type="date" id="fechaDesde" value="<?php echo date('Y-m-d', strtotime('-1 month')); ?>">
            </div>
            <div class="form-group">
                <label>Fecha hasta</label>
                <input type="date" id="fechaHasta" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <button class="export-btn" onclick="filtrarRiñas()">Filtrar Riñas</button>
            <button class="export-btn" onclick="exportPDF()">Exportar a PDF</button>
        </div>

        // Alerta para reportes que van al mapa 
        <div class="map-alert hidden" id="alertaMapa">
            <strong>Este reporte se visualiza en el mapa:</strong> 
            <span id="textoMapa"></span>
            <button class="map-btn" onclick="irAlMapa()" style="margin-left: 20px;">Ver en Mapa</button>
        </div>

        //REPORTE RIÑAS PANDILLERILES 
        <div class="report-section" id="reporteRinas">
            <div class="section-title">Reporte de Riñas Pandilleriles</div>
            <table id="tableRinas">
                <tr>
                    <th>Pandillas Involucradas</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Ubicación</th>
                    <th>Descripción</th>
                    <th>Coordenadas</th>
                </tr>
                <?php
                $sql = "SELECT pandillas_involucradas, fecha_rina, hora_rina, ubicacion_rina, 
                               descripcion_hechos, latitud_rina, longitud_rina 
                        FROM rinas_pandilleriles 
                        WHERE fecha_rina >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                        ORDER BY fecha_rina DESC";
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()) {
                    $coordenadas = "Lat: " . $row['latitud_rina'] . "<br>Lon: " . $row['longitud_rina'];
                    echo "<tr>
                        <td>".$row['pandillas_involucradas']."</td>
                        <td>".$row['fecha_rina']."</td>
                        <td>".$row['hora_rina']."</td>
                        <td>".$row['ubicacion_rina']."</td>
                        <td>".$row['descripcion_hechos']."</td>
                        <td>".$coordenadas."</td>
                    </tr>";
                }
                ?>
            </table>
        </div>

        // REPORTE GRADO DE PELIGROSIDAD 
        <div class="report-section hidden" id="reportePeligrosidad">
            <div class="section-title">Grado de Peligrosidad</div>
            <div class="alert">
                <p><strong>Información disponible para visualización en mapa:</strong></p>
                <ul>
                    <li>Pandillas con peligrosidad ALTA (mostradas en rojo)</li>
                    <li>Pandillas con peligrosidad MEDIA (mostradas en amarillo)</li>
                    <li>Pandillas con peligrosidad BAJA (mostradas en verde)</li>
                </ul>
            </div>
            <table id="tablePeligrosidad">
                <tr>
                    <th>Pandilla</th>
                    <th>Peligrosidad</th>
                    <th>Integrantes Aprox.</th>
                    <th>Lugar Reunión</th>
                    <th>Coordenadas Reunión</th>
                </tr>
                <?php
                $sql = "SELECT nombre, peligrosidad, num_integrantes_aproximado, 
                               lugar_reunion, latitud_reunion, longitud_reunion 
                        FROM Pandillas 
                        ORDER BY 
                            CASE peligrosidad 
                                WHEN 'Alta' THEN 1
                                WHEN 'Media' THEN 2
                                WHEN 'Baja' THEN 3
                                ELSE 4
                            END, nombre";
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()) {
                    $coordenadas = "Lat: " . $row['latitud_reunion'] . "<br>Lon: " . $row['longitud_reunion'];
                    echo "<tr>
                        <td>".$row['nombre']."</td>
                        <td><strong>".$row['peligrosidad']."</strong></td>
                        <td>".$row['num_integrantes_aproximado']."</td>
                        <td>".$row['lugar_reunion']."</td>
                        <td>".$coordenadas."</td>
                    </tr>";
                }
                ?>
            </table>
        </div>

        // REPORTE FALTAS COMETIDAS 
        <div class="report-section hidden" id="reporteFaltas">
            <div class="section-title">Faltas Cometidas</div>
            <table id="tableFaltas">
                <tr>
                    <th>Integrante</th>
                    <th>Pandilla</th>
                    <th>Faltas Cometidas</th>
                    <th>Fecha Nacimiento</th>
                    <th>Dirección</th>
                    <th>Redes Sociales</th>
                </tr>
                <?php
                // Obtener integrantes que tienen faltas cometidas
                $sql = "SELECT i.nombres, i.apellido_paterno, i.apellido_materno, i.alias,
                               i.faltas_cometidas, i.fecha_nacimiento, i.direccion_vivienda,
                               i.redes_sociales, p.nombre as nombre_pandilla
                        FROM integrantes i
                        LEFT JOIN Pandillas p ON i.id_pandilla = p.id_pandilla
                        WHERE i.faltas_cometidas IS NOT NULL 
                        AND i.faltas_cometidas != ''
                        ORDER BY i.nombres, i.apellido_paterno";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $nombre_completo = $row['nombres'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'];
                        if (!empty($row['alias'])) {
                            $nombre_completo .= " (" . $row['alias'] . ")";
                        }
                        
                        
                        $faltas_html = '';
                        if (!empty($row['faltas_cometidas'])) {
                            $faltas_array = explode(', ', $row['faltas_cometidas']);
                            foreach ($faltas_array as $falta) {
                                $faltas_html .= '<span class="delito-falta-item falta-item">' . htmlspecialchars(trim($falta)) . '</span> ';
                            }
                        }
                        
                        $fecha_nac = !empty($row['fecha_nacimiento']) ? date('d/m/Y', strtotime($row['fecha_nacimiento'])) : '';
                        
                        echo "<tr>
                            <td>" . $nombre_completo . "</td>
                            <td>" . $row['nombre_pandilla'] . "</td>
                            <td>" . $faltas_html . "</td>
                            <td>" . $fecha_nac . "</td>
                            <td>" . $row['direccion_vivienda'] . "</td>
                            <td>" . $row['redes_sociales'] . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>No hay integrantes con faltas registradas</td></tr>";
                }
                ?>
            </table>
        </div>

        // REPORTE DELITOS COMETIDOS 
        <div class="report-section hidden" id="reporteDelitos">
            <div class="section-title">Delitos Cometidos</div>
            <div class="alert">
                <p><strong>Delitos registrados en el sistema:</strong></p>
                <p>Esta información se visualiza georreferenciada en el mapa del sistema.</p>
            </div>
            <table id="tableDelitos">
                <tr>
                    <th>Integrante</th>
                    <th>Pandilla</th>
                    <th>Delitos Asociados</th>
                    <th>Fecha Nacimiento</th>
                    <th>Dirección</th>
                    <th>Redes Sociales</th>
                </tr>
                <?php
                // Obtener integrantes que tienen delitos asociados
                $sql = "SELECT i.nombres, i.apellido_paterno, i.apellido_materno, i.alias,
                               i.delitos_asociados, i.fecha_nacimiento, i.direccion_vivienda,
                               i.redes_sociales, p.nombre as nombre_pandilla
                        FROM integrantes i
                        LEFT JOIN Pandillas p ON i.id_pandilla = p.id_pandilla
                        WHERE i.delitos_asociados IS NOT NULL 
                        AND i.delitos_asociados != ''
                        ORDER BY i.nombres, i.apellido_paterno";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $nombre_completo = $row['nombres'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'];
                        if (!empty($row['alias'])) {
                            $nombre_completo .= " (" . $row['alias'] . ")";
                        }
                        
                        
                        $delitos_html = '';
                        if (!empty($row['delitos_asociados'])) {
                            $delitos_array = explode(', ', $row['delitos_asociados']);
                            foreach ($delitos_array as $delito) {
                                $delitos_html .= '<span class="delito-falta-item delito-item">' . htmlspecialchars(trim($delito)) . '</span> ';
                            }
                        }
                        
                        $fecha_nac = !empty($row['fecha_nacimiento']) ? date('d/m/Y', strtotime($row['fecha_nacimiento'])) : '';
                        
                        echo "<tr>
                            <td>" . $nombre_completo . "</td>
                            <td>" . $row['nombre_pandilla'] . "</td>
                            <td>" . $delitos_html . "</td>
                            <td>" . $fecha_nac . "</td>
                            <td>" . $row['direccion_vivienda'] . "</td>
                            <td>" . $row['redes_sociales'] . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>No hay integrantes con delitos registrados</td></tr>";
                }
                ?>
            </table>
        </div>

// REPORTE GENERAL
<div class="report-section hidden" id="reporteGeneral">
    <div class="section-title">Reporte general</div>
    
    // Selector de pandilla 
    <div class="controls-row">
        <div class="form-group">
            <label>Seleccionar Pandilla</label>
            <select id="selectPandilla" onchange="cargarReportePandilla()">
                <option value="">-- Seleccione una pandilla --</option>
                <?php foreach($pandillas as $p): ?>
                    <option value="<?php echo $p['id_pandilla']; ?>" 
                        <?php echo ($pandilla_seleccionada == $p['id_pandilla']) ? 'selected' : ''; ?>>
                        <?php echo $p['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="export-btn" onclick="exportPDFPandilla()">Exportar a PDF</button>
    </div>
    
    // Contenido del reporte de pandilla 
    <div id="contenidoReportePandilla">
        <?php
        if ($pandilla_seleccionada) {
            // Obtener información de la pandilla seleccionada
            $stmt = $conn->prepare("SELECT * FROM Pandillas WHERE id_pandilla = ?");
            $stmt->bind_param("i", $pandilla_seleccionada);
            $stmt->execute();
            $result_pandilla = $stmt->get_result();
            
            if ($result_pandilla->num_rows > 0) {
                $pandilla = $result_pandilla->fetch_assoc();
                
                // Estadísticas de la pandilla
                $total_integrantes = $conn->query("SELECT COUNT(*) as total FROM Integrantes WHERE id_pandilla = $pandilla_seleccionada")->fetch_assoc()['total'];
                
                // Riñas donde participa esta pandilla
                $sql_rinas = "SELECT COUNT(*) as total FROM rinas_pandilleriles 
                             WHERE pandillas_involucradas LIKE '%".$conn->real_escape_string($pandilla['nombre'])."%'";
                $result_rinas = $conn->query($sql_rinas);
                $total_rinas = $result_rinas->fetch_assoc()['total'];
                
                // Delitos cometidos por integrantes de esta pandilla 
                $sql_delitos_count = "SELECT COUNT(*) as total FROM Integrantes 
                                    WHERE id_pandilla = $pandilla_seleccionada 
                                    AND delitos_asociados IS NOT NULL 
                                    AND delitos_asociados != ''";
                $result_delitos_count = $conn->query($sql_delitos_count);
                $total_delitos_integrantes = $result_delitos_count->fetch_assoc()['total'];
                
                // Faltas cometidas por integrantes de esta pandilla 
                $sql_faltas_count = "SELECT COUNT(*) as total FROM Integrantes 
                                   WHERE id_pandilla = $pandilla_seleccionada 
                                   AND faltas_cometidas IS NOT NULL 
                                   AND faltas_cometidas != ''";
                $result_faltas_count = $conn->query($sql_faltas_count);
                $total_faltas_integrantes = $result_faltas_count->fetch_assoc()['total'];
                
                // 1. DELITOS DE LA PANDILLA 
                $delitos_pandilla = [];
                if (!empty($pandilla['delitos_pandillas'])) {
                    $delitos_pandilla = explode(', ', $pandilla['delitos_pandillas']);
                    // Limpiar y contar
                    $delitos_pandilla = array_map('trim', $delitos_pandilla);
                    $delitos_pandilla = array_filter($delitos_pandilla); // Eliminar vacíos
                }
                
                // 2. FALTAS DE LA PANDILLA 
                $faltas_pandilla = [];
                if (!empty($pandilla['faltas_pandillas'])) {
                    $faltas_pandilla = explode(', ', $pandilla['faltas_pandillas']);
                    // Limpiar y contar
                    $faltas_pandilla = array_map('trim', $faltas_pandilla);
                    $faltas_pandilla = array_filter($faltas_pandilla); // Eliminar vacíos
                }
                
                // 3. DELITOS DE LOS INTEGRANTES 
                $sql_delitos_integrantes = "SELECT delitos_asociados FROM Integrantes 
                                          WHERE id_pandilla = $pandilla_seleccionada 
                                          AND delitos_asociados IS NOT NULL 
                                          AND delitos_asociados != ''";
                $result_delitos_integrantes = $conn->query($sql_delitos_integrantes);
                
                $delitos_integrantes_array = [];
                $delitos_integrantes_frecuencia = [];
                $total_delitos_integrantes_count = 0;
                
                while($row_delito = $result_delitos_integrantes->fetch_assoc()) {
                    if (!empty($row_delito['delitos_asociados'])) {
                        $delitos_individuales = explode(', ', $row_delito['delitos_asociados']);
                        foreach ($delitos_individuales as $delito) {
                            $delito = trim($delito);
                            if (!empty($delito)) {
                                $delitos_integrantes_array[] = $delito;
                                $total_delitos_integrantes_count++;
                                if (!isset($delitos_integrantes_frecuencia[$delito])) {
                                    $delitos_integrantes_frecuencia[$delito] = 0;
                                }
                                $delitos_integrantes_frecuencia[$delito]++;
                            }
                        }
                    }
                }
                
                // Ordenar por frecuencia
                arsort($delitos_integrantes_frecuencia);
                
                //  FALTAS DE LOS INTEGRANTES 
                $sql_faltas_integrantes = "SELECT faltas_cometidas FROM Integrantes 
                                         WHERE id_pandilla = $pandilla_seleccionada 
                                         AND faltas_cometidas IS NOT NULL 
                                         AND faltas_cometidas != ''";
                $result_faltas_integrantes = $conn->query($sql_faltas_integrantes);
                
                $faltas_integrantes_array = [];
                $faltas_integrantes_frecuencia = [];
                $total_faltas_integrantes_count = 0;
                
                while($row_falta = $result_faltas_integrantes->fetch_assoc()) {
                    if (!empty($row_falta['faltas_cometidas'])) {
                        $faltas_individuales = explode(', ', $row_falta['faltas_cometidas']);
                        foreach ($faltas_individuales as $falta) {
                            $falta = trim($falta);
                            if (!empty($falta)) {
                                $faltas_integrantes_array[] = $falta;
                                $total_faltas_integrantes_count++;
                                if (!isset($faltas_integrantes_frecuencia[$falta])) {
                                    $faltas_integrantes_frecuencia[$falta] = 0;
                                }
                                $faltas_integrantes_frecuencia[$falta]++;
                            }
                        }
                    }
                }
                
                // Ordenar por frecuencia
                arsort($faltas_integrantes_frecuencia);
                
                // Información del líder (si existe)
                $lider_info = "";
                if (!empty($pandilla['id_lider_actual'])) {
                    $sql_lider = "SELECT nombres, apellido_paterno, apellido_materno, alias 
                                 FROM Integrantes 
                                 WHERE id_integrante = ?";
                    $stmt_lider = $conn->prepare($sql_lider);
                    $stmt_lider->bind_param("i", $pandilla['id_lider_actual']);
                    $stmt_lider->execute();
                    $result_lider = $stmt_lider->get_result();
                    
                    if ($result_lider->num_rows > 0) {
                        $lider = $result_lider->fetch_assoc();
                        $lider_info = $lider['nombres'] . " " . $lider['apellido_paterno'] . " " . 
                                      $lider['apellido_materno'] . " (" . $lider['alias'] . ")";
                    }
                }
                
                echo '<div class="pandilla-header">';
                echo '<h3>' . htmlspecialchars($pandilla['nombre']) . '</h3>';
                if (!empty($pandilla['alias'])) {
                    echo '<p><strong>Alias:</strong> ' . htmlspecialchars($pandilla['alias']) . '</p>';
                }
                echo '</div>';
                
                // Estadísticas de la pandilla
                echo '<div class="stats-grid">';
                echo '<div class="stat-card">';
                echo '<div class="stat-value">' . $total_integrantes . '</div>';
                echo '<div class="stat-label">Integrantes</div>';
                echo '</div>';
                
                echo '<div class="stat-card">';
                echo '<div class="stat-value">' . $pandilla['num_integrantes_aproximado'] . '</div>';
                echo '<div class="stat-label">Integrantes Aprox.</div>';
                echo '</div>';
                
                echo '<div class="stat-card">';
                echo '<div class="stat-value">' . $total_rinas . '</div>';
                echo '<div class="stat-label">Riñas Involucradas</div>';
                echo '</div>';
                
                echo '<div class="stat-card">';
                echo '<div class="stat-value">' . count($delitos_pandilla) . '</div>';
                echo '<div class="stat-label">Delitos Pandilla</div>';
                echo '</div>';
                
                echo '<div class="stat-card">';
                echo '<div class="stat-value">' . count($faltas_pandilla) . '</div>';
                echo '<div class="stat-label">Faltas Pandilla</div>';
                echo '</div>';
                echo '</div>';
                
                // Información detallada
                echo '<div class="info-row"><div class="info-label">Nivel de Peligrosidad:</div>';
                echo '<div class="info-value"><strong>' . $pandilla['peligrosidad'] . '</strong></div></div>';
                
                if (!empty($pandilla['rango_edades'])) {
                    echo '<div class="info-row"><div class="info-label">Rango de Edades:</div>';
                    echo '<div class="info-value">' . $pandilla['rango_edades'] . '</div></div>';
                }
                
                if (!empty($lider_info)) {
                    echo '<div class="info-row"><div class="info-label">Líder Actual:</div>';
                    echo '<div class="info-value">' . $lider_info . '</div></div>';
                }
                
                if (!empty($pandilla['lugar_reunion'])) {
                    echo '<div class="info-row"><div class="info-label">Lugar de Reunión:</div>';
                    echo '<div class="info-value">' . $pandilla['lugar_reunion'] . '</div></div>';
                    
                    if (!empty($pandilla['latitud_reunion']) && !empty($pandilla['longitud_reunion'])) {
                        echo '<div class="info-row"><div class="info-label">Coordenadas:</div>';
                        echo '<div class="info-value">Lat: ' . $pandilla['latitud_reunion'] . ', Lon: ' . $pandilla['longitud_reunion'] . '</div></div>';
                    }
                }
                
                if (!empty($pandilla['horario_reunion'])) {
                    echo '<div class="info-row"><div class="info-label">Horario de Reunión:</div>';
                    echo '<div class="info-value">' . $pandilla['horario_reunion'] . '</div></div>';
                }
                
                if (!empty($pandilla['pandillas_rivales_texto'])) {
                    echo '<div class="info-row"><div class="info-label">Pandillas Rivales:</div>';
                    echo '<div class="info-value">' . $pandilla['pandillas_rivales_texto'] . '</div></div>';
                }
                
                if (!empty($pandilla['redes_sociales'])) {
                    echo '<div class="info-row"><div class="info-label">Redes Sociales:</div>';
                    echo '<div class="info-value">' . $pandilla['redes_sociales'] . '</div></div>';
                }
                
                // SECCIÓN 1: DELITOS DE LA PANDILLA (DIRECTOS - de la columna delitos_pandillas)
                if (!empty($delitos_pandilla)) {
                    echo '<h3 style="margin-top: 30px;">Delitos Asociados a la Pandilla</h3>';
                    
                    echo '<div style="margin-bottom: 20px; padding: 15px; background-color: #ffebee; border-radius: 5px;">';
                    echo '<p><strong>Delitos registrados directamente a la pandilla:</strong> ' . count($delitos_pandilla) . ' delitos</p>';
                    
                    foreach ($delitos_pandilla as $delito) {
                        echo '<span class="delito-falta-item" style="margin-right: 5px; margin-bottom: 5px; background-color: #ffcdd2; border: 1px solid #ef9a9a;">';
                        echo htmlspecialchars($delito);
                        echo '</span> ';
                    }
                    echo '</div>';
                }
                
                // SECCIÓN 2: FALTAS DE LA PANDILLA ( de la columna faltas_pandillas)
                if (!empty($faltas_pandilla)) {
                    echo '<h3 style="margin-top: 30px;">Faltas Cometidas por la Pandilla</h3>';
                    
                    echo '<div style="margin-bottom: 20px; padding: 15px; background-color: #fff3e0; border-radius: 5px;">';
                    echo '<p><strong>Faltas registradas directamente a la pandilla:</strong> ' . count($faltas_pandilla) . ' faltas</p>';
                    
                    foreach ($faltas_pandilla as $falta) {
                        echo '<span class="delito-falta-item" style="margin-right: 5px; margin-bottom: 5px; background-color: #ffe0b2; border: 1px solid #ffcc80;">';
                        echo htmlspecialchars($falta);
                        echo '</span> ';
                    }
                    echo '</div>';
                }
                
                // SECCIÓN 3: DELITOS DE LOS INTEGRANTES (ACUMULADOS)
                if (!empty($delitos_integrantes_frecuencia)) {
                    echo '<h3 style="margin-top: 30px;">Delitos de los Integrantes</h3>';
                    
                    echo '<div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">';
                    echo '<p><strong>Total de delitos de integrantes:</strong> ' . $total_delitos_integrantes_count . ' en ' . count($delitos_integrantes_frecuencia) . ' tipos diferentes</p>';
                    echo '<p><strong>Delitos más frecuentes:</strong></p>';
                    
                    $counter = 0;
                    foreach ($delitos_integrantes_frecuencia as $delito => $frecuencia) {
                        if ($counter < 5) { // Mostrar solo los 5 más frecuentes
                            $porcentaje = ($total_delitos_integrantes_count > 0) ? round(($frecuencia / $total_delitos_integrantes_count) * 100, 1) : 0;
                            echo '<span class="delito-falta-item" style="margin-right: 5px; margin-bottom: 5px; background-color: #ffebee; border: 1px solid #ffcdd2;">';
                            echo htmlspecialchars($delito) . ' (' . $frecuencia . ' veces - ' . $porcentaje . '%)';
                            echo '</span> ';
                            $counter++;
                        }
                    }
                    
                    if (count($delitos_integrantes_frecuencia) > 5) {
                        echo '<br><em>... y ' . (count($delitos_integrantes_frecuencia) - 5) . ' delitos más</em>';
                    }
                    echo '</div>';
                } else {
                    echo '<div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">';
                    echo '<p><strong>Delitos de integrantes:</strong> No se han registrado delitos para los integrantes de esta pandilla.</p>';
                    echo '</div>';
                }
                
                //  FALTAS DE LOS INTEGRANTES 
                if (!empty($faltas_integrantes_frecuencia)) {
                    echo '<h3 style="margin-top: 30px;">Faltas de los Integrantes</h3>';
                    
                    echo '<div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">';
                    echo '<p><strong>Total de faltas de integrantes:</strong> ' . $total_faltas_integrantes_count . ' en ' . count($faltas_integrantes_frecuencia) . ' tipos diferentes</p>';
                    echo '<p><strong>Faltas más frecuentes:</strong></p>';
                    
                    $counter = 0;
                    foreach ($faltas_integrantes_frecuencia as $falta => $frecuencia) {
                        if ($counter < 5) { // Mostrar solo las 5 más frecuentes
                            $porcentaje = ($total_faltas_integrantes_count > 0) ? round(($frecuencia / $total_faltas_integrantes_count) * 100, 1) : 0;
                            echo '<span class="delito-falta-item" style="margin-right: 5px; margin-bottom: 5px; background-color: #fff3e0; border: 1px solid #ffe0b2;">';
                            echo htmlspecialchars($falta) . ' (' . $frecuencia . ' veces - ' . $porcentaje . '%)';
                            echo '</span> ';
                            $counter++;
                        }
                    }
                    
                    if (count($faltas_integrantes_frecuencia) > 5) {
                        echo '<br><em>... y ' . (count($faltas_integrantes_frecuencia) - 5) . ' faltas más</em>';
                    }
                    echo '</div>';
                } else {
                    echo '<div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">';
                    echo '<p><strong>Faltas de integrantes:</strong> No se han registrado faltas para los integrantes de esta pandilla.</p>';
                    echo '</div>';
                }
                
                if (!empty($pandilla['descripcion'])) {
                    echo '<div class="info-row" style="flex-direction: column; margin-top: 20px;">';
                    echo '<div class="info-label">Descripción:</div>';
                    echo '<div class="info-value" style="margin-top: 10px; background: #f8f9fa; padding: 15px; border-radius: 5px;">';
                    echo nl2br(htmlspecialchars($pandilla['descripcion']));
                    echo '</div></div>';
                }
                
                // Integrantes de la pandilla
echo '<h3 style="margin-top: 30px;">Integrantes (' . $total_integrantes . ')</h3>';
if ($total_integrantes > 0) {
    $sql_integrantes = "SELECT nombres, apellido_paterno, apellido_materno, alias, 
                       fecha_nacimiento, redes_sociales, direccion_vivienda,
                       delitos_asociados, faltas_cometidas, fotografia
                FROM Integrantes 
                WHERE id_pandilla = $pandilla_seleccionada 
                ORDER BY apellido_paterno, nombres";
    $result_integrantes = $conn->query($sql_integrantes);
    
    echo '<table>';
    echo '<tr><th>Foto</th><th>Nombre</th><th>Alias</th><th>Delitos</th><th>Faltas</th><th>Dirección</th></tr>';
    while($integrante = $result_integrantes->fetch_assoc()) {
        $nombre_completo = $integrante['nombres'] . ' ' . $integrante['apellido_paterno'] . ' ' . $integrante['apellido_materno'];
        
        // Mostrar foto si existe
        $foto_html = '';
        if (!empty($integrante['fotografia'])) {
            $ruta_foto = 'uploads/fotos_integrantes/' . $integrante['fotografia'];
            if (file_exists($ruta_foto)) {
                $foto_html = '<img src="' . $ruta_foto . '" alt="Foto" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">';
            } else {
                $foto_html = '<div style="width: 60px; height: 60px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #ddd; color: #888; font-size: 11px;">Sin foto</div>';
            }
        } else {
            $foto_html = '<div style="width: 60px; height: 60px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #ddd; color: #888; font-size: 11px;">Sin foto</div>';
        }
        
        // Delitos como elementos visuales
        $delitos_html = '';
        if (!empty($integrante['delitos_asociados'])) {
            $delitos_array = explode(', ', $integrante['delitos_asociados']);
            foreach ($delitos_array as $delito) {
                $delitos_html .= '<span class="delito-falta-item" style="background-color: #ffebee; border: 1px solid #ffcdd2; font-size: 11px;">' . htmlspecialchars(trim($delito)) . '</span> ';
            }
        } else {
            $delitos_html = '<span style="color: #888; font-style: italic; font-size: 11px;">Sin delitos</span>';
        }
        
        // Faltas como elementos visuales
        $faltas_html = '';
        if (!empty($integrante['faltas_cometidas'])) {
            $faltas_array = explode(', ', $integrante['faltas_cometidas']);
            foreach ($faltas_array as $falta) {
                $faltas_html .= '<span class="delito-falta-item" style="background-color: #fff3e0; border: 1px solid #ffe0b2; font-size: 11px;">' . htmlspecialchars(trim($falta)) . '</span> ';
            }
        } else {
            $faltas_html = '<span style="color: #888; font-style: italic; font-size: 11px;">Sin faltas</span>';
        }
        
        echo '<tr>';
        echo '<td style="text-align: center; padding: 8px;">' . $foto_html . '</td>';
        echo '<td>' . $nombre_completo . '</td>';
        echo '<td>' . $integrante['alias'] . '</td>';
        echo '<td style="max-width: 200px;">' . $delitos_html . '</td>';
        echo '<td style="max-width: 200px;">' . $faltas_html . '</td>';
        echo '<td>' . $integrante['direccion_vivienda'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>No hay integrantes registrados para esta pandilla.</p>';
}
                
                // Riñas donde participa esta pandilla
                if ($total_rinas > 0) {
                    echo '<h3 style="margin-top: 30px;">Riñas donde participa</h3>';
                    $sql_rinas_detalle = "SELECT * FROM rinas_pandilleriles 
                                        WHERE pandillas_involucradas LIKE '%".$conn->real_escape_string($pandilla['nombre'])."%'
                                        ORDER BY fecha_rina DESC 
                                        LIMIT 10";
                    $result_rinas_detalle = $conn->query($sql_rinas_detalle);
                    
                    echo '<table>';
                    echo '<tr><th>Fecha</th><th>Hora</th><th>Ubicación</th><th>Pandillas Involucradas</th><th>Descripción</th></tr>';
                    while($rina = $result_rinas_detalle->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $rina['fecha_rina'] . '</td>';
                        echo '<td>' . $rina['hora_rina'] . '</td>';
                        echo '<td>' . $rina['ubicacion_rina'] . '</td>';
                        echo '<td>' . $rina['pandillas_involucradas'] . '</td>';
                        echo '<td>' . $rina['descripcion_hechos'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                
            } else {
                echo '<div class="alert">No se encontró información de la pandilla seleccionada.</div>';
            }
        } else {
            echo '<div class="alert">Seleccione una pandilla para ver su reporte detallado.</div>';
        }
        ?>
    </div>
</div>
    </div>

    <div class="footer">
        <div class="footer-contact-info"><strong>Correos de contacto</strong><p>186115@upslp.edu.mx</p></div>
    </div>

    <script>
        function mostrarReporte() {
            let tipo = document.getElementById("tipoReporte").value;
            
            // Ocultar todos los reportes
            document.getElementById("reporteRinas").classList.add("hidden");
            document.getElementById("reportePeligrosidad").classList.add("hidden");
            document.getElementById("reporteFaltas").classList.add("hidden");
            document.getElementById("reporteDelitos").classList.add("hidden");
            document.getElementById("reporteGeneral").classList.add("hidden");
            
            // Ocultar/mostrar filtros
            document.getElementById("filtroFecha").style.display = "none";
            document.getElementById("alertaMapa").classList.add("hidden");
            
            // Configurar según el tipo de reporte
            if (tipo === "rinas") {
                document.getElementById("reporteRinas").classList.remove("hidden");
                document.getElementById("filtroFecha").style.display = "flex";
            } else if (tipo === "peligrosidad") {
                document.getElementById("reportePeligrosidad").classList.remove("hidden");
                mostrarAlertaMapa("Grado de Peligrosidad - Visualización por niveles en el mapa");
            } else if (tipo === "faltas") {
                document.getElementById("reporteFaltas").classList.remove("hidden");
            } else if (tipo === "delitos") {
                document.getElementById("reporteDelitos").classList.remove("hidden");
                mostrarAlertaMapa("Delitos Cometidos - Visualización georreferenciada en el mapa");
            } else if (tipo === "general") {
                document.getElementById("reporteGeneral").classList.remove("hidden");
            }
        }

        function mostrarAlertaMapa(texto) {
            document.getElementById("textoMapa").textContent = texto;
            document.getElementById("alertaMapa").classList.remove("hidden");
        }

        function filtrarRiñas() {
            let fechaDesde = document.getElementById("fechaDesde").value;
            let fechaHasta = document.getElementById("fechaHasta").value;
            
            // Enviar petición AJAX para filtrar riñas
            fetch('filtrar_rinas.php?desde=' + fechaDesde + '&hasta=' + fechaHasta)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("tableRinas").innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        }

        function cargarReportePandilla() {
            let pandillaId = document.getElementById("selectPandilla").value;
            
            if (pandillaId) {
                // Recargar la página con el ID de la pandilla seleccionada
                window.location.href = 'seis.php?pandilla_id=' + pandillaId + '&tipo=general';
            }
        }

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'pt', 'a4');
            let tipo = document.getElementById("tipoReporte").value;
            let tableId, title;

            if(tipo === "rinas") {
                tableId = "tableRinas";
                title = "Reporte de Riñas Pandilleriles";
            } else if(tipo === "peligrosidad") {
                tableId = "tablePeligrosidad";
                title = "Reporte de Grado de Peligrosidad";
            } else if(tipo === "faltas") {
                tableId = "tableFaltas";
                title = "Reporte de Faltas Cometidas";
            } else if(tipo === "delitos") {
                tableId = "tableDelitos";
                title = "Reporte de Delitos Cometidos";
            }

            const table = document.getElementById(tableId);
            const headers = [];
            table.querySelectorAll("th").forEach(th => headers.push(th.innerText));

            const data = [];
            table.querySelectorAll("tr").forEach((tr, index) => {
                if(index === 0) return;
                const row = [];
                tr.querySelectorAll("td").forEach(td => {
                    // Eliminar etiquetas HTML de los delitos/faltas
                    let text = td.innerText || td.textContent;
                    row.push(text);
                });
                data.push(row);
            });

            doc.text(title, 20, 20);
            doc.autoTable({
                head: [headers],
                body: data,
                startY: 40,
                styles: { fontSize: 9 },
                columnStyles: {
                    0: { cellWidth: 100 },
                    1: { cellWidth: 80 },
                    2: { cellWidth: 150 },
                    3: { cellWidth: 60 },
                    4: { cellWidth: 100 },
                    5: { cellWidth: 100 }
                }
            });

            doc.save(tipo + "_reporte.pdf");
        }

        function exportPDFPandilla() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');
            
            let pandillaId = document.getElementById("selectPandilla").value;
            let pandillaNombre = document.getElementById("selectPandilla").options[document.getElementById("selectPandilla").selectedIndex].text;
            
            // Título
            doc.setFontSize(20);
            doc.text("Reporte de Pandilla: " + pandillaNombre, 20, 40);
            doc.setFontSize(10);
            doc.text("Generado el: " + new Date().toLocaleDateString('es-MX'), 20, 60);
            
            let y = 80;
            
            // Obtener datos via AJAX para el PDF
            fetch('obtener_datos_pandilla_pdf.php?pandilla_id=' + pandillaId)
                .then(response => response.json())
                .then(data => {
                    // Continuar con la generación del PDF con los datos obtenidos
                    generarPDFPandilla(doc, data, pandillaNombre, y);
                })
                .catch(error => {
                    // Si hay error, usar los datos visibles en la página
                    generarPDFPandillaFromHTML(doc, pandillaNombre);
                });
        }

        function generarPDFPandillaFromHTML(doc, pandillaNombre) {
            // Obtener contenido visible de la página
            const contenido = document.getElementById("contenidoReportePandilla");
            
            // Convertir el contenido a texto para el PDF
            let texto = contenido.innerText;
            let lineas = texto.split('\n');
            
            doc.setFontSize(12);
            let y = 80;
            
            lineas.forEach(linea => {
                if (linea.trim() !== '') {
                    if (y > 750) {
                        doc.addPage();
                        y = 40;
                    }
                    
                    // Formato especial para encabezados
                    if (linea.includes('Integrantes (')) {
                        doc.setFontSize(14);
                        doc.text(linea, 20, y);
                        doc.setFontSize(12);
                    } else if (linea.includes('Riñas donde participa') || linea.includes('Delitos Asociados') || linea.includes('Faltas Cometidas')) {
                        doc.setFontSize(14);
                        y += 10;
                        doc.text(linea, 20, y);
                        doc.setFontSize(12);
                    } else {
                        doc.text(linea, 20, y);
                    }
                    
                    y += 20;
                }
            });
            
            doc.save("reporte_pandilla_" + pandillaNombre.replace(/[^a-zA-Z0-9]/g, '_') + ".pdf");
        }

        function generarPDFPandilla(doc, data, pandillaNombre, y) {
            // Esta función sería llamada si tenemos un endpoint específico para obtener datos para PDF
            // Por ahora usamos la versión HTML
            generarPDFPandillaFromHTML(doc, pandillaNombre);
        }

        function irAlMapa() {
            let tipo = document.getElementById("tipoReporte").value;
            let url = "siete.php?reporte=" + tipo;
            window.location.href = url;
        }

        // Inicializar mostrando el reporte correcto
        document.addEventListener('DOMContentLoaded', function() {
            // Si hay una pandilla seleccionada en la URL, mostrar reporte general
            const urlParams = new URLSearchParams(window.location.search);
            const pandillaId = urlParams.get('pandilla_id');
            
            if (pandillaId) {
                // Seleccionar "Reporte por Pandilla" en el dropdown
                document.getElementById("tipoReporte").value = "general";
                mostrarReporte();
                
                // Seleccionar la pandilla en el select
                document.getElementById("selectPandilla").value = pandillaId;
            } else {
                mostrarReporte();
            }
        });
    </script>

</body>
</html>