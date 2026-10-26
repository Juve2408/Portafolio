<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header("Location: dos.php"); 
    exit(); 
}
//  Incluir la conexión a la base de datos
include 'conexion.php'; 

//  EXTRACCIÓN DE DATOS DE LA BASE DE DATOS PARA JAVASCRIPT 

//  Pandillas (para marcarlas y el mapa de rivalidad)
$sql_pandillas = "SELECT 
                    nombre, alias, peligrosidad, 
                    latitud_reunion, longitud_reunion, pandillas_rivales_texto
                  FROM Pandillas 
                  WHERE latitud_reunion IS NOT NULL AND longitud_reunion IS NOT NULL";

$result_pandillas = $conn->query($sql_pandillas);
$pandillas_data = [];
while ($row = $result_pandillas->fetch_assoc()) {
    // Preparamos la lista de rivales para el JS (usando explode)
    $row['rivales_array'] = array_map('trim', explode(',', $row['pandillas_rivales_texto'] ?? ''));
    $pandillas_data[] = $row;
}
$pandillas_json = json_encode($pandillas_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);


//  Integrantes (para marcarlos individualmente) 
//  Integrantes de pandillas tienen latitud_vivienda y longitud_vivienda 
$sql_integrantes = "SELECT 
                        i.id_integrante,
                        i.nombres, 
                        i.apellido_paterno, 
                        i.apellido_materno, 
                        i.alias, 
                        i.latitud_vivienda, 
                        i.longitud_vivienda, 
                        i.direccion_vivienda,
                        i.delitos_asociados,
                        i.faltas_cometidas,
                        p.nombre as pandilla_nombre,
                        p.peligrosidad as peligrosidad_pandilla
                    FROM Integrantes i 
                    JOIN Pandillas p ON i.id_pandilla = p.id_pandilla
                    WHERE i.latitud_vivienda IS NOT NULL 
                    AND i.longitud_vivienda IS NOT NULL 
                    AND TRIM(i.latitud_vivienda) != '' 
                    AND TRIM(i.longitud_vivienda) != ''
                    LIMIT 200"; // Límite para evitar sobrecargar el mapa

$result_integrantes = $conn->query($sql_integrantes);
$integrantes_data = [];
while ($row = $result_integrantes->fetch_assoc()) {
    // Formatear el nombre completo
    $nombre_completo = trim($row['nombres'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
    $row['nombre_completo'] = $nombre_completo;
    
    // Verificar que las coordenadas sean válidas
    $lat = floatval($row['latitud_vivienda']);
    $lng = floatval($row['longitud_vivienda']);
    
    if ($lat != 0 && $lng != 0 && is_numeric($lat) && is_numeric($lng)) {
        $row['lat'] = $lat;
        $row['lng'] = $lng;
        $integrantes_data[] = $row;
    }
}
$integrantes_json = json_encode($integrantes_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);


// Incidentes/Riñas (para marcarlos)
$sql_incidentes = "SELECT 
                       fecha_rina, ubicacion_rina, descripcion_hechos, 
                       latitud_rina, longitud_rina, pandillas_involucradas
                   FROM rinas_pandilleriles
                   WHERE latitud_rina IS NOT NULL AND longitud_rina IS NOT NULL
                   AND TRIM(latitud_rina) != '' AND TRIM(longitud_rina) != ''
                   LIMIT 100";
                   
$result_incidentes = $conn->query($sql_incidentes);
$incidentes_data = [];
while ($row = $result_incidentes->fetch_assoc()) {
    // Verificar que las coordenadas sean válidas
    $lat = floatval($row['latitud_rina']);
    $lng = floatval($row['longitud_rina']);
    
    if ($lat != 0 && $lng != 0 && is_numeric($lat) && is_numeric($lng)) {
        $row['lat'] = $lat;
        $row['lng'] = $lng;
        $incidentes_data[] = $row;
    }
}
$incidentes_json = json_encode($incidentes_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Mapeo (Admin)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .top-nav { background-color: #0d47a1; color: white; padding: 10px 0; display: flex; }
        .nav-item { padding: 10px 20px; cursor: pointer; text-decoration: none; color: white; border-right: 1px solid #1976d2; }
        .nav-item.active { background-color: white; color: #0d47a1; font-weight: bold; }
        .main-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; align-items: center; }
        /* Adaptamos el contenedor para el mapa real */
        .map-container-admin { max-width: 1200px; width: 100%; height: 600px; background-color: white; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 30px; border-radius: 8px; }
        #map-admin { width: 100%; height: 100%; border-radius: 8px; }
        
        .action-buttons { 
            display: flex; 
            flex-wrap: wrap;
            gap: 15px; 
            max-width: 1200px; 
            width: 100%; 
            justify-content: center;
        }
        .action-btn { 
            background-color: #333; 
            color: white; 
            border: none; 
            padding: 12px 20px; 
            cursor: pointer; 
            border-radius: 4px; 
            font-size: 16px; 
            transition: background-color 0.3s; 
            flex: 1 1 auto; 
            min-width: 200px;
        }
        .action-btn:hover { background-color: #555; }
        .action-btn.active { background-color: #4CAF50; }
        .action-btn[data-action="peligrosas"].active { background-color: #B71C1C; } /* Rojo para peligrosas */
        
        .legend-container {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
            max-width: 1200px;
            width: 100%;
        }
        .legend-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #0d47a1;
        }
        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 3px rgba(0,0,0,0.3);
        }
        .legend-text {
            font-size: 14px;
        }
        
        .footer { background-color: #333; color: white; padding: 20px; margin-top: auto; }
    </style>
</head>
<body>
   <div class="top-nav" id="adminNav">
    <a class="nav-item" href="cuatro.php">Captura pandillas</a>
    <a class="nav-item" href="cinco.php">Captura integrantes</a>
    <a class="nav-item" href="seis.php">Reportes consultas</a>
    <a class="nav-item" href="siete.php">Sistema mapa</a>
    <a class="nav-item" href="diez.php">Modificaciones/Eliminar</a>
    <a class="nav-item" href="ocho.php">Ayuda soporte</a>
    <a class="nav-item" href="nueve.php">Agregar usuario</a>
    <a class="nav-item" href="tres.php">Inicio</a>
</div>

    <div class="main-content">
        <h2 style="margin-top: 0;">Sistema de Mapeo de Inteligencia</h2>
        <div class="map-container-admin">
            <div id="map-admin">Cargando Mapa de Google...</div>
        </div>
        <div class="action-buttons">
            <button class="action-btn" data-action="pandillas">Marcar Pandillas</button>
            <button class="action-btn" data-action="integrantes">Marcar Integrantes</button>
            <button class="action-btn" data-action="peligrosas">Mapa de Zonas Peligrosas</button>
            <button class="action-btn" data-action="incidentes">Marcar Incidentes Recientes</button>
            <button class="action-btn" data-action="rivalidad">Líneas de Rivalidad de Pandillas</button>
        </div>
        
        <div class="legend-container">
            <div class="legend-title">Leyenda del Mapa:</div>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #0162f2ff;"></div>
                    <div class="legend-text">Pandilla (Normal)</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #FF9800;"></div>
                    <div class="legend-text">Pandilla (Alta Peligrosidad)</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #B71C1C;"></div>
                    <div class="legend-text">Pandilla (Extrema Peligrosidad)</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color">
                        <img src="https://maps.google.com/mapfiles/ms/icons/man.png" style="width: 20px; height: 20px;">
                    </div>
                    <div class="legend-text">Integrante</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color">
                        <img src="https://maps.google.com/mapfiles/ms/icons/caution.png" style="width: 20px; height: 20px;">
                    </div>
                    <div class="legend-text">Incidente/Riña</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #F44336; width: 20px; height: 4px; border-radius: 0;"></div>
                    <div class="legend-text">Línea de Rivalidad</div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <div class="footer-contact-info"><strong>Correos de contacto</strong><p>186115@upslp.edu.mx</p></div>
    </div>

    <script>
        //  DATOS DESDE PHP 
        const pandillasData = <?php echo $pandillas_json; ?>;
        const integrantesData = <?php echo $integrantes_json; ?>;
        const incidentesData = <?php echo $incidentes_json; ?>;

        console.log('Integrantes cargados:', integrantesData.length);
        console.log('Pandillas cargadas:', pandillasData.length);
        console.log('Incidentes cargados:', incidentesData.length);

        let map, heatmap;
        let gangMarkers = [];
        let memberMarkers = [];
        let incidentMarkers = [];
        let rivalryLines = [];

        let mapState = {
            pandillas: false,
            integrantes: false,
            peligrosas: false,
            incidentes: false,
            rivalidad: false
        };

        // Función de inicialización de Google Maps 
        function initMap() {
            // Intentar centrar el mapa en la primera pandilla con coordenadas
            let initialCenter = { lat: 22.148236, lng: -100.983523 }; // Centro de SLP por defecto
            
            if (pandillasData.length > 0 && pandillasData[0].latitud_reunion) {
                const lat = parseFloat(pandillasData[0].latitud_reunion);
                const lng = parseFloat(pandillasData[0].longitud_reunion);
                if (!isNaN(lat) && !isNaN(lng)) {
                    initialCenter = { lat: lat, lng: lng };
                }
            }
            
            map = new google.maps.Map(document.getElementById("map-admin"), {
                zoom: 12,
                center: initialCenter,
                mapTypeId: "roadmap",
                streetViewControl: false,
                mapTypeControl: true,
                fullscreenControl: true
            });
            
            // Inicializar todos los datos
            initGangMarkers();
            initMemberMarkers();
            initIncidentMarkers();
            initHeatmap();
            
            // Apagar todos por defecto
            toggleMarkers(gangMarkers, false);
            toggleMarkers(memberMarkers, false);
            toggleMarkers(incidentMarkers, false);
            toggleHeatmap(false);

            setupEventListeners();
        }
        
        // 

        function getGangMarkerIcon(data) {
            let color = "#0048ffff"; // Azul - Peligrosidad baja/media
            if (data.peligrosidad === "Extrema") color = "#B71C1C"; // Rojo Oscuro
            else if (data.peligrosidad === "Alta") color = "#FF9800"; // Naranja

            return {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: color,
                fillOpacity: 0.9,
                strokeColor: "#FFFFFF",
                strokeWeight: 2
            };
        }

        function getMemberMarkerIcon(memberData) {
            // Color basado en la peligrosidad de la pandilla
            let color = "#cfef00ff"; 
            if (memberData.peligrosidad_pandilla === "Extrema") color = "#B71C1C";
            else if (memberData.peligrosidad_pandilla === "Alta") color = "#FF9800";
            else if (memberData.peligrosidad_pandilla === "Media") color = "#0202ffff";

            return {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 8,
                fillColor: color,
                fillOpacity: 0.8,
                strokeColor: "#FFFFFF",
                strokeWeight: 2
            };
        }

        function getIncidentMarkerIcon(data) {
            return {
                url: 'https://maps.google.com/mapfiles/ms/icons/caution.png',
                scaledSize: new google.maps.Size(30, 30)
            };
        }
        
    

        function initGangMarkers() {
            const infoWindow = new google.maps.InfoWindow();
            
            pandillasData.forEach(gang => {
                const lat = parseFloat(gang.latitud_reunion);
                const lng = parseFloat(gang.longitud_reunion);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: null,
                        title: gang.nombre,
                        icon: getGangMarkerIcon(gang),
                        data: gang
                    });

                    marker.addListener('click', () => {
                        const content = `
                            <div style="max-width: 300px;">
                                <h3 style="margin: 0 0 10px 0; color: #81a10dff;">${gang.nombre}</h3>
                                <p><strong>Alias:</strong> ${gang.alias || 'No especificado'}</p>
                                <p><strong>Peligrosidad:</strong> <span style="color: ${gang.peligrosidad === 'Alta' ? '#FF9800' : gang.peligrosidad === 'Extrema' ? '#B71C1C' : '#4CAF50'}">${gang.peligrosidad}</span></p>
                                <p><strong>Rivales:</strong> ${gang.pandillas_rivales_texto || 'No especificados'}</p>
                                <p><strong>Coordenadas:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}</p>
                            </div>
                        `;
                        infoWindow.setContent(content);
                        infoWindow.open(map, marker);
                    });

                    gangMarkers.push(marker);
                }
            });
        }

        function initMemberMarkers() {
            const infoWindow = new google.maps.InfoWindow();
            
            integrantesData.forEach(member => {
                // Usamos lat y lng ya convertidos en PHP
                const lat = member.lat;
                const lng = member.lng;
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: null,
                        title: member.nombre_completo || member.nombres,
                        icon: getMemberMarkerIcon(member),
                        data: member
                    });

                    marker.addListener('click', () => {
                        // Crear contenido informativo del integrante
                        const delitos = member.delitos_asociados ? member.delitos_asociados : 'No registrados';
                        const faltas = member.faltas_cometidas ? member.faltas_cometidas : 'No registradas';
                        const direccion = member.direccion_vivienda ? member.direccion_vivienda : 'No especificada';
                        
                        const content = `
                            <div style="max-width: 350px;">
                                <h3 style="margin: 0 0 10px 0; color: #0D47A1;">${member.nombre_completo}</h3>
                                <p><strong>Alias:</strong> ${member.alias || 'No especificado'}</p>
                                <p><strong>Pandilla:</strong> ${member.pandilla_nombre} <span style="color: ${member.peligrosidad_pandilla === 'Alta' ? '#FF9800' : member.peligrosidad_pandilla === 'Extrema' ? '#B71C1C' : '#4CAF50'}">(${member.peligrosidad_pandilla})</span></p>
                                <p><strong>Dirección:</strong> ${direccion}</p>
                                <p><strong>Coordenadas:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}</p>
                                <div style="margin-top: 10px; padding: 8px; background-color: #f8f9fa; border-left: 4px solid #dc3545;">
                                    <p style="margin: 0 0 5px 0;"><strong>Delitos:</strong> ${delitos}</p>
                                    <p style="margin: 0;"><strong>Faltas:</strong> ${faltas}</p>
                                </div>
                            </div>
                        `;
                        infoWindow.setContent(content);
                        infoWindow.open(map, marker);
                    });

                    memberMarkers.push(marker);
                }
            });
        }

        function initIncidentMarkers() {
            const infoWindow = new google.maps.InfoWindow();
            
            incidentesData.forEach(incident => {
                const lat = incident.lat;
                const lng = incident.lng;
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: null,
                        title: `Incidente: ${incident.fecha_rina}`,
                        icon: getIncidentMarkerIcon(incident),
                        data: incident
                    });

                    marker.addListener('click', () => {
                        const content = `
                            <div style="max-width: 300px;">
                                <h3 style="margin: 0 0 10px 0; color: #B71C1C;">Incidente de Riña</h3>
                                <p><strong>Fecha:</strong> ${incident.fecha_rina}</p>
                                <p><strong>Ubicación:</strong> ${incident.ubicacion_rina}</p>
                                <p><strong>Pandillas Involucradas:</strong> ${incident.pandillas_involucradas}</p>
                                <p><strong>Descripción:</strong> ${incident.descripcion_hechos}</p>
                                <p><strong>Coordenadas:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}</p>
                            </div>
                        `;
                        infoWindow.setContent(content);
                        infoWindow.open(map, marker);
                    });

                    incidentMarkers.push(marker);
                }
            });
        }

        function toggleMarkers(markerArray, show) {
            markerArray.forEach(marker => {
                marker.setMap(show ? map : null);
            });
        }
        
        function initHeatmap() {
            const heatmapData = [];
            
            // Agregar pandillas al heatmap
            pandillasData.forEach(gang => {
                const lat = parseFloat(gang.latitud_reunion);
                const lng = parseFloat(gang.longitud_reunion);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    let weight = 1;
                    if (gang.peligrosidad === "Alta") weight = 5;
                    else if (gang.peligrosidad === "Extrema") weight = 8;
                    
                    heatmapData.push({ 
                        location: new google.maps.LatLng(lat, lng), 
                        weight: weight 
                    });
                }
            });
            
            // Agregar incidentes al heatmap
            incidentesData.forEach(incident => {
                const lat = incident.lat;
                const lng = incident.lng;
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    heatmapData.push({ 
                        location: new google.maps.LatLng(lat, lng), 
                        weight: 3 
                    });
                }
            });

            heatmap = new google.maps.visualization.HeatmapLayer({
                data: heatmapData,
                radius: 50,
                opacity: 0.7,
              
            });
        }
        
        function toggleHeatmap(show) {
            heatmap.setMap(show ? map : null);
        }

        function toggleRivalityLines(show) {
            // Limpiar líneas anteriores
            rivalryLines.forEach(line => line.setMap(null));
            rivalryLines = [];

            if (!show) return;

            // Mapear pandillas para acceso rápido a coordenadas
            const gangMap = {};
            pandillasData.forEach(p => {
                const lat = parseFloat(p.latitud_reunion);
                const lng = parseFloat(p.longitud_reunion);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    gangMap[p.nombre.trim().toLowerCase()] = {
                        lat: lat,
                        lng: lng
                    };
                }
            });

            // Crear líneas de rivalidad
            pandillasData.forEach(gang => {
                const originCoord = gangMap[gang.nombre.trim().toLowerCase()];
                if (!originCoord) return;

                gang.rivales_array.forEach(rivalName => {
                    const rivalNameLower = rivalName.toLowerCase().trim();
                    const rivalCoord = gangMap[rivalNameLower];
                    
                    if (rivalCoord) {
                        const line = new google.maps.Polyline({
                            path: [originCoord, rivalCoord],
                            geodesic: true,
                            strokeColor: '#F44336',
                            strokeOpacity: 0.6,
                            strokeWeight: 2,
                            map: map
                        });
                        rivalryLines.push(line);
                    }
                });
            });
        }

        

        function setupEventListeners() {
            document.querySelectorAll('.action-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.getAttribute('data-action');
                    mapState[action] = !mapState[action];
                    
                    // Actualizar estado visual del botón
                    this.classList.toggle('active', mapState[action]);
                    
                    // Aplicar la acción correspondiente
                    switch(action) {
                        case 'pandillas':
                            toggleMarkers(gangMarkers, mapState.pandillas);
                            if (mapState.pandillas && mapState.peligrosas) {
                                toggleHeatmap(false);
                                mapState.peligrosas = false;
                                document.querySelector('[data-action="peligrosas"]').classList.remove('active');
                            }
                            break;
                        case 'integrantes':
                            toggleMarkers(memberMarkers, mapState.integrantes);
                            break;
                        case 'incidentes':
                            toggleMarkers(incidentMarkers, mapState.incidentes);
                            break;
                        case 'peligrosas':
                            toggleHeatmap(mapState.peligrosas);
                            if (mapState.peligrosas && mapState.pandillas) {
                                toggleMarkers(gangMarkers, false);
                                mapState.pandillas = false;
                                document.querySelector('[data-action="pandillas"]').classList.remove('active');
                            }
                            break;
                        case 'rivalidad':
                            toggleRivalityLines(mapState.rivalidad);
                            break;
                    }
                });
            });
        }

        function gm_authFailure() {
            const mapDiv = document.getElementById("map-admin");
            mapDiv.innerHTML = `
                <div style="text-align: center; padding: 50px; color: red; font-weight: bold;">
                     ERROR AL CARGAR EL MAPA <br><br>
                    1. Revisa que tu Clave API en Google Maps sea correcta.<br>
                    2. Asegúrate de haber habilitado la 'Maps JavaScript API'.
                </div>
            `;
            mapDiv.style.backgroundColor = '#ffeeee';
        }

        // Mostrar conteo de datos cargados
        window.onload = function() {
            console.log(`Mapa cargado con ${integrantesData.length} integrantes, ${pandillasData.length} pandillas y ${incidentesData.length} incidentes.`);
        };
    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2hmJyVpimhxu1OsTt9eUIz-9NUnj2qYw&libraries=visualization,geometry&callback=initMap"></script>
</body>
</html>