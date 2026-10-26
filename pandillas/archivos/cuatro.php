<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: dos.php"); exit(); }
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombrePandilla'];
    $alias = $_POST['alias'] ?? '';
    $descripcion = $_POST['descripcion'];
    $num = $_POST['numIntegrantes'];
    $edades = $_POST['edades'];
    $horarios = $_POST['horarios'];
    $rivalidades = $_POST['rivalidades'];
    $redes_sociales = $_POST['redes_sociales'] ?? '';
    $zona = $_POST['zona'] ?? '';
    
    $lugar = $_POST['lugarReunion'];
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];
    $peligrosidad = $_POST['peligrosidad'];
    
    $id_lider_actual = !empty($_POST['id_lider_actual']) ? $_POST['id_lider_actual'] : NULL;
    $delitos_pandillas = isset($_POST['delitos']) ? implode(', ', $_POST['delitos']) : '';
    $faltas_pandillas = isset($_POST['faltas']) ? implode(', ', $_POST['faltas']) : '';
    
    // Variables para riñas
    $fecha_rina = $_POST['fecha_rina'] ?? NULL;
    $hora_rina = $_POST['hora_rina'] ?? NULL;
    $ubicacion_rina = $_POST['ubicacion_rina'] ?? '';
    $descripcion_hechos = $_POST['descripcion_hechos'] ?? '';
    $latitud_rina = $_POST['latitud_rina'] ?? NULL;
    $longitud_rina = $_POST['longitud_rina'] ?? NULL;
    $id_usuario_registro = $_SESSION['usuario_id'];
    
    $sql = "INSERT INTO Pandillas (nombre, alias, descripcion, id_lider_actual, num_integrantes_aproximado, rango_edades, horario_reunion, peligrosidad, redes_sociales, lugar_reunion, latitud_reunion, longitud_reunion, pandillas_rivales_texto, delitos_pandillas, faltas_pandillas, zona) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssissssssssssss", $nombre, $alias, $descripcion, $id_lider_actual, $num, $edades, $horarios, $peligrosidad, $redes_sociales, $lugar, $latitud, $longitud, $rivalidades, $delitos_pandillas, $faltas_pandillas, $zona);
    
    if ($stmt->execute()) {
        $id_pandilla = $stmt->insert_id;
        
        // Insertar datos de riña si existen
        if (!empty($fecha_rina) && !empty($ubicacion_rina)) {
            $sql_rina = "INSERT INTO rinas_pandilleriles (id_rina, fecha_rina, hora_rina, ubicacion_rina, descripcion_hechos, latitud_rina, longitud_rina, id_usuario_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_rina = $conn->prepare($sql_rina);
            $stmt_rina->bind_param("issssssi", $id_pandilla, $fecha_rina, $hora_rina, $ubicacion_rina, $descripcion_hechos, $latitud_rina, $longitud_rina, $id_usuario_registro);
            
            if ($stmt_rina->execute()) {
                echo "<script>alert('Pandilla registrada exitosamente con información de riña');</script>";
            } else {
                echo "<script>alert('Pandilla registrada, pero hubo un error al guardar la información de riña: " . $stmt_rina->error . "');</script>";
            }
            $stmt_rina->close();
        } else {
            echo "<script>alert('Pandilla registrada exitosamente');</script>";
        }
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Captura Pandillas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .top-nav { background-color: #0d47a1; color: white; padding: 10px 0; display: flex; }
        .nav-item { padding: 10px 20px; cursor: pointer; text-decoration: none; color: white; border-right: 1px solid #1976d2; }
        .nav-item.active { background-color: white; color: #0d47a1; font-weight: bold; }
        .main-content { padding: 20px; flex-grow: 1; }
        .form-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 1100px; margin: 0 auto; }
        
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .span-3 { grid-column: span 3; }
        
        /* ESTILOS DEL MAPA SELECTOR */
        .location-section {
            grid-column: span 3;
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #90caf9;
            display: flex;
            gap: 20px;
        }
        .location-inputs { flex: 1; display: flex; flex-direction: column; gap: 15px; }
        .location-map-container { flex: 1; height: 300px; background-color: #eee; border-radius: 4px; border: 1px solid #ccc; }
        #mapPicker, #mapPickerRina { width: 100%; height: 100%; border-radius: 4px; }
        .help-text { font-size: 12px; color: #666; font-style: italic; margin-top: 5px; }

        .section-title { font-size: 18px; margin-top: 20px; border-bottom: 1px solid #ddd; color: #0d47a1; }
        .checkbox-group { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        
        /* Estilos específicos para la sección de riñas */
        .rina-section {
            grid-column: span 3;
            background-color: #fff3e0;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ffb74d;
            margin-top: 10px;
        }
        .rina-section .section-title {
            color: #e65100;
            border-bottom: 1px solid #ffb74d;
        }
        .rina-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .footer { background-color: #333; color: white; padding: 20px; margin-top: auto; }
        
        /* Estilos para los botones de mapa */
        .map-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .map-toggle-btn {
            padding: 8px 15px;
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .map-toggle-btn.active {
            background-color: #0d47a1;
            color: white;
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <a class="nav-item active" href="cuatro.php">Captura pandillas</a>
        <a class="nav-item" href="cinco.php">Captura integrantes</a>
        <a class="nav-item" href="seis.php">Reportes consultas</a>
        <a class="nav-item" href="siete.php">Sistema mapa</a>
        <a class="nav-item" href="diez.php">Modificaciones/Eliminar</a>
        <a class="nav-item" href="ocho.php">Ayuda soporte</a>
        <a class="nav-item" href="nueve.php">Agregar usuario</a>
        <a class="nav-item" href="tres.php">Inicio</a>
    </div>

    <div class="main-content">
        <h2 style="color: #0d47a1; border-bottom: 2px solid #ccc;">Captura de datos pandillas</h2>
        
        <div class="form-container">
            <form id="pandillaForm" method="POST">
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>Nombre de la pandilla *</label>
                        <input type="text" name="nombrePandilla" required>
                    </div>
                    <div class="form-group">
                        <label>Alias</label>
                        <input type="text" name="alias">
                    </div>
                    <div class="form-group">
                        <label>Líder Actual </label>
                        <input type="text" name="id_lider_actual">
                    </div>
                    
                    <div class="form-group">
                        <label>Peligrosidad *</label>
                        <select name="peligrosidad" required>
                            <option value="Baja">Baja</option>
                            <option value="Media">Media</option>
                            <option value="Alta">Alta</option>
                            <option value="Extrema">Extrema</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Zona *</label>
                        <input type="text" name="zona" placeholder="Ej: Norte" required>
                    </div>
                    <div class="form-group">
                        <label>Num. Integrantes</label>
                        <input type="number" name="numIntegrantes">
                    </div>

                    <div class="location-section">
                        <div class="location-inputs">
                            <div class="map-toggle">
                                <button type="button" class="map-toggle-btn active" onclick="showMap('pandilla')">Mapa Pandilla</button>
                                <button type="button" class="map-toggle-btn" onclick="showMap('rina')">Mapa Riña</button>
                            </div>
                            
                            <h3 style="margin-top:0; color:#0d47a1;" id="mapTitle">Ubicación Geográfica de la Pandilla</h3>
                            <p class="help-text">Haz clic en el mapa O escribe las coordenadas manualmente.</p>
                            
                            <div id="pandillaLocationInputs">
                                <div class="form-group">
                                    <label>Dirección / Lugar *</label>
                                    <input type="text" name="lugarReunion" placeholder="Calle, Colonia o referencia" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Latitud *</label>
                                    <input type="text" name="latitud" id="latInput" placeholder="Ej: 22.14..." required>
                                </div>
                                <div class="form-group">
                                    <label>Longitud *</label>
                                    <input type="text" name="longitud" id="lngInput" placeholder="Ej: -100.98..." required>
                                </div>
                            </div>
                            
                            <div id="rinaLocationInputs" style="display: none;">
                                <div class="form-group">
                                    <label>Ubicación de la Riña</label>
                                    <input type="text" name="ubicacion_rina" id="ubicacionRinaInput" placeholder="Ej: Estadio Alfonso Lastras">
                                </div>
                                
                                <div class="form-group">
                                    <label>Latitud Riña</label>
                                    <input type="text" name="latitud_rina" id="latRinaInput" placeholder="Ej: 22.14...">
                                </div>
                                <div class="form-group">
                                    <label>Longitud Riña</label>
                                    <input type="text" name="longitud_rina" id="lngRinaInput" placeholder="Ej: -100.98...">
                                </div>
                            </div>
                        </div>
                        <div class="location-map-container">
                            <div id="mapPicker">Cargando mapa...</div>
                            <div id="mapPickerRina" style="display: none;">Cargando mapa...</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Edades (Rango)</label>
                        <input type="text" name="edades" placeholder="Ej: 15-25">
                    </div>
                    <div class="form-group">
                        <label>Horarios Reunión</label>
                        <input type="text" name="horarios">
                    </div>
                    <div class="form-group">
                        <label>Redes Sociales</label>
                        <input type="text" name="redes_sociales">
                    </div>
                    <div class="form-group span-3">
                        <label>Rivalidades</label>
                        <input type="text" name="rivalidades">
                    </div>

                    <div class="form-group span-3">
                        <h3 class="section-title">Delitos asociados</h3>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="delitos[]" value="Robo a persona"> Robo persona</label>
                            <label><input type="checkbox" name="delitos[]" value="Robo a vehículo"> Robo vehículo</label>
                            <label><input type="checkbox" name="delitos[]" value="Robo a casa habitación"> Robo a casa habitación</label>
                            <label><input type="checkbox" name="delitos[]" value="Robo a comercio"> Robo a comercio</label>
                            <label><input type="checkbox" name="delitos[]" value="Robo a repartidores"> Robo a repartidores</label>
                            <label><input type="checkbox" name="delitos[]" value="Narcomenudeo"> Narcomenudeo</label>
                            <label><input type="checkbox" name="delitos[]" value="Homicidio"> Homicidio</label>
                            <label><input type="checkbox" name="delitos[]" value="Extorsiones"> Extorsiones</label>
                            <label><input type="checkbox" name="delitos[]" value="Daños"> Daños</label>
                            <label><input type="checkbox" name="delitos[]" value="Otros"> Otros Delitos</label>
                        </div>
                    </div>

                    <div class="form-group span-3">
                        <h3 class="section-title">Faltas cometidas</h3>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="faltas[]" value="Tomar en vía pública"> Tomar en la vía pública</label>
                            <label><input type="checkbox" name="faltas[]" value="Riñas"> Riñas</label>
                            <label><input type="checkbox" name="faltas[]" value="violencia familiar"> violencia familiar</label>
                            <label><input type="checkbox" name="faltas[]" value="Consumo de Drogas"> Consumo de Drogas</label>
                            <label><input type="checkbox" name="faltas[]" value="Daños"> Daños</label>
                            <label><input type="checkbox" name="faltas[]" value="Escandalizar"> Escandalizar</label>
                            
                            
                            <label><input type="checkbox" name="faltas[]" value="Otras Faltas"> Otras Faltas</label>
                        </div>
                    </div>

                    <div class="form-group span-3">
                        <label>Descripción General</label>
                        <textarea name="descripcion" rows="4"></textarea>
                    </div>
                    
                    <!-- SECCIÓN DE RIÑAS -->
                    <div class="rina-section">
                        <h3 class="section-title">Información de Riñas (Opcional)</h3>
                        
                        <div class="rina-grid">
                            <div class="form-group">
                                <label>Fecha de Riña</label>
                                <input type="date" name="fecha_rina">
                            </div>
                            
                            <div class="form-group">
                                <label>Hora de Riña</label>
                                <input type="time" name="hora_rina">
                            </div>
                            
                          
                        </div>
                        
                        <div class="form-group span-3">
                            <label>Descripción de los hechos</label>
                            <textarea name="descripcion_hechos" rows="3" placeholder="Describa detalladamente los hechos ocurridos en la riña..."></textarea>
                            <p class="help-text">Ej: Pelea de pandillas, uso de armas, número aproximado de involucrados, etc.</p>
                        </div>
                    </div>
                    
                    <div class="form-group span-3">
                        <button type="submit" style="background:#0d47a1; color:white; padding:15px; border:none; cursor:pointer; font-size:18px;">Guardar Pandilla</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let mapPandilla, mapRina, markerPandilla, markerRina;
        let currentMap = 'pandilla';
        
        function initMap() {
            const slp = { lat: 22.148236, lng: -100.983523 };
            
            // Mapa para pandilla
            mapPandilla = new google.maps.Map(document.getElementById("mapPicker"), {
                zoom: 12,
                center: slp,
                mapTypeId: "roadmap",
                streetViewControl: false
            });

            mapPandilla.addListener("click", (e) => {
                if (currentMap === 'pandilla') {
                    placeMarkerAndPanTo(e.latLng, 'pandilla');
                }
            });

            // Mapa para riña
            mapRina = new google.maps.Map(document.getElementById("mapPickerRina"), {
                zoom: 12,
                center: slp,
                mapTypeId: "roadmap",
                streetViewControl: false
            });

            mapRina.addListener("click", (e) => {
                if (currentMap === 'rina') {
                    placeMarkerAndPanTo(e.latLng, 'rina');
                }
            });
        }

        function placeMarkerAndPanTo(latLng, type) {
            if (type === 'pandilla') {
                if (markerPandilla) markerPandilla.setMap(null);
                markerPandilla = new google.maps.Marker({
                    position: latLng,
                    map: mapPandilla,
                    animation: google.maps.Animation.DROP
                });
                document.getElementById("latInput").value = latLng.lat().toFixed(6);
                document.getElementById("lngInput").value = latLng.lng().toFixed(6);
            } else {
                if (markerRina) markerRina.setMap(null);
                markerRina = new google.maps.Marker({
                    position: latLng,
                    map: mapRina,
                    animation: google.maps.Animation.DROP,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                });
                document.getElementById("latRinaInput").value = latLng.lat().toFixed(6);
                document.getElementById("lngRinaInput").value = latLng.lng().toFixed(6);
            }
        }

        function showMap(type) {
            currentMap = type;
            
            // Actualizar botones
            document.querySelectorAll('.map-toggle-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Actualizar título
            if (type === 'pandilla') {
                document.getElementById('mapTitle').textContent = 'Ubicación Geográfica de la Pandilla';
                document.getElementById('pandillaLocationInputs').style.display = 'flex';
                document.getElementById('rinaLocationInputs').style.display = 'none';
                document.getElementById('mapPicker').style.display = 'block';
                document.getElementById('mapPickerRina').style.display = 'none';
            } else {
                document.getElementById('mapTitle').textContent = 'Ubicación Geográfica de la Riña';
                document.getElementById('pandillaLocationInputs').style.display = 'none';
                document.getElementById('rinaLocationInputs').style.display = 'flex';
                document.getElementById('mapPicker').style.display = 'none';
                document.getElementById('mapPickerRina').style.display = 'block';
            }
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2hmJyVpimhxu1OsTt9eUIz-9NUnj2qYw&callback=initMap"></script>
</body>
</html>