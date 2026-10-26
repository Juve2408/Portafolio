<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: dos.php"); exit(); }
// 1. Incluir la conexión a la base de datos
include 'conexion.php'; 

// --- 2. EXTRACCIÓN DE DATOS DE LA BASE DE DATOS PARA JAVASCRIPT ---

// A) Pandillas (para marcarlas y el mapa de rivalidad)
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
$pandillas_json = json_encode($pandillas_data);


// B) Integrantes (para marcarlos individualmente)
// Se asume que Integrantes tiene latitud_vivienda y longitud_vivienda (se corrigió en cinco.php)
$sql_integrantes = "SELECT 
                        i.nombres, i.alias, i.latitud_vivienda, i.longitud_vivienda, p.nombre as pandilla_nombre
                    FROM Integrantes i 
                    JOIN Pandillas p ON i.id_pandilla = p.id_pandilla
                    WHERE i.latitud_vivienda IS NOT NULL AND i.longitud_vivienda IS NOT NULL 
                    LIMIT 200"; // Límite para evitar sobrecargar el mapa

$result_integrantes = $conn->query($sql_integrantes);
$integrantes_data = [];
while ($row = $result_integrantes->fetch_assoc()) {
    $integrantes_data[] = $row;
}
$integrantes_json = json_encode($integrantes_data);


// C) Incidentes/Riñas (para marcarlos)
// Usamos la tabla 'rinas_pandilleriles' del esquema
$sql_incidentes = "SELECT 
                       fecha_rina, ubicacion_rina, descripcion_hechos, 
                       latitud_rina, longitud_rina, pandillas_involucradas
                   FROM rinas_pandilleriles
                   WHERE latitud_rina IS NOT NULL AND longitud_rina IS NOT NULL
                   LIMIT 100";
                   
$result_incidentes = $conn->query($sql_incidentes);
$incidentes_data = [];
while ($row = $result_incidentes->fetch_assoc()) {
    $incidentes_data[] = $row;
}
$incidentes_json = json_encode($incidentes_data);


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
            flex: 1 1 auto; /* Permite que los botones crezcan/encojan */
            min-width: 200px;
        }
        .action-btn:hover { background-color: #555; }
        .action-btn.active { background-color: #4CAF50; }
        .action-btn[data-action="peligrosas"].active { background-color: #B71C1C; } /* Rojo para peligrosas */
        
        .footer { background-color: #333; color: white; padding: 20px; margin-top: auto; }
    </style>
</head>
<body>
    <div class="top-nav" id="adminNav">    
           <a class="nav-item active" href="consultor.php">Sistema mapa</a>
        <a class="nav-item" href="consultor2.php">Reportes consultas</a>
 
        <a class="nav-item" href="consultor3.php">Inicio</a>
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
    </div>
    <div class="footer">
        <div class="footer-contact-info"><strong>Correos de contacto</strong><p>186115@upslp.edu.mx</p></div>
    </div>

    <script>
        // --- DATOS INYECTADOS DESDE PHP ---
        const pandillasData = <?php echo $pandillas_json; ?>;
        const integrantesData = <?php echo $integrantes_json; ?>;
        const incidentesData = <?php echo $incidentes_json; ?>;

        let map, heatmap;
        let gangMarkers = [];
        let memberMarkers = [];
        let incidentMarkers = [];
        let rivalryLines = [];

        // Estado inicial de los toggles
        let mapState = {
            pandillas: false,
            integrantes: false,
            peligrosas: false,
            incidentes: false,
            rivalidad: false
        };

        // Función de inicialización de Google Maps (Callback)
        function initMap() {
            map = new google.maps.Map(document.getElementById("map-admin"), {
                zoom: 12,
                center: { lat: 22.148236, lng: -100.983523 }, // Centro de SLP
                mapTypeId: "roadmap"
            });
            
            // Inicializar todos los datos
            initMarkers(pandillasData, gangMarkers, 'Pandilla', getGangMarkerIcon);
            initMarkers(integrantesData, memberMarkers, 'Integrante', getMemberMarkerIcon);
            initMarkers(incidentesData, incidentMarkers, 'Incidente', getIncidentMarkerIcon);
            initHeatmap(); // Inicializar el heatmap
            
            // Apagar todos por defecto
            toggleMarkers(gangMarkers, false);
            toggleMarkers(memberMarkers, false);
            toggleMarkers(incidentMarkers, false);
            toggleHeatmap(false);

            setupEventListeners();
        }
        
        // --- Funciones de Iconos ---

        function getGangMarkerIcon(data) {
            let color = "#0D47A1"; // Azul
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

        function getMemberMarkerIcon(data) {
            return {
                url: 'https://maps.google.com/mapfiles/ms/icons/man.png', // Icono de persona
                scaledSize: new google.maps.Size(30, 30)
            };
        }

        function getIncidentMarkerIcon(data) {
             return {
                url: 'https://maps.google.com/mapfiles/ms/icons/caution.png', // Icono de peligro/precaución
                scaledSize: new google.maps.Size(30, 30)
            };
        }
        
        // --- Funciones de Inicialización y Toggles ---

        function initMarkers(dataArray, markerArray, type, iconFunc) {
            const infoWindow = new google.maps.InfoWindow();

            dataArray.forEach(data => {
                let lat, lng;
                let title, content;

                if (type === 'Pandilla') {
                    lat = parseFloat(data.latitud_reunion);
                    lng = parseFloat(data.longitud_reunion);
                    title = data.nombre;
                    content = `<h3>${data.nombre}</h3><p>Peligrosidad: ${data.peligrosidad}</p><p>Rivalidad: ${data.pandillas_rivales_texto}</p>`;
                } else if (type === 'Integrante') {
                    lat = parseFloat(data.latitud_vivienda);
                    lng = parseFloat(data.longitud_vivienda);
                    title = data.nombres;
                    content = `<h3>${data.nombres} (${data.alias || 'S/A'})</h3><p>Pandilla: ${data.pandilla_nombre}</p>`;
                } else if (type === 'Incidente') {
                    lat = parseFloat(data.latitud_rina);
                    lng = parseFloat(data.longitud_rina);
                    title = data.descripcion_hechos;
                    content = `<h3>Incidente (${data.fecha_rina})</h3><p>Ubicación: ${data.ubicacion_rina}</p><p>Pandillas: ${data.pandillas_involucradas}</p>`;
                }

                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: null, // Inicialmente invisible
                        title: title,
                        icon: iconFunc(data),
                        data: data // Guardamos la data completa en el marcador
                    });

                    marker.addListener('click', () => {
                        infoWindow.setContent(content);
                        infoWindow.open(map, marker);
                    });

                    markerArray.push(marker);
                }
            });
        }

        function toggleMarkers(markerArray, show) {
            markerArray.forEach(marker => {
                marker.setMap(show ? map : null);
            });
        }
        
        function initHeatmap() {
            const heatmapData = pandillasData.map(gang => {
                const lat = parseFloat(gang.latitud_reunion);
                const lng = parseFloat(gang.longitud_reunion);
                let weight = 1;

                if (gang.peligrosidad === "Alta") weight = 5;
                else if (gang.peligrosidad === "Muy Alta") weight = 8;
                else if (gang.peligrosidad === "Extrema") weight = 10;
                
                return { location: new google.maps.LatLng(lat, lng), weight: weight };
            });

            heatmap = new google.maps.visualization.HeatmapLayer({
                data: heatmapData,
                radius: 40,
                opacity: 0.7
            });
        }
        
        function toggleHeatmap(show) {
            heatmap.setMap(show ? map : null);
        }

        function toggleRivalryLines(show) {
            // 1. Limpiar líneas anteriores
            rivalryLines.forEach(line => line.setMap(null));
            rivalryLines = [];

            if (!show) return;

            // 2. Mapear pandillas para acceso rápido a coordenadas
            const gangMap = {};
            pandillasData.forEach(p => {
                gangMap[p.nombre.trim().toLowerCase()] = {
                    lat: parseFloat(p.latitud_reunion),
                    lng: parseFloat(p.longitud_reunion)
                };
            });

            // 3. Crear líneas de rivalidad
            pandillasData.forEach(gang => {
                const originCoord = gangMap[gang.nombre.trim().toLowerCase()];

                if (!originCoord) return;

                gang.rivales_array.forEach(rivalName => {
                    const rivalCoord = gangMap[rivalName.toLowerCase()];
                    
                    // Solo dibuja la línea si la pandilla rival tiene coordenadas
                    if (rivalCoord) {
                        const line = new google.maps.Polyline({
                            path: [
                                originCoord,
                                rivalCoord
                            ],
                            geodesic: true,
                            strokeColor: '#F44336', // Rojo
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            map: map
                        });
                        rivalryLines.push(line);
                    }
                });
            });
        }

        // --- Event Listeners para los Botones ---

        function setupEventListeners() {
            document.querySelectorAll('.action-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.getAttribute('data-action');
                    mapState[action] = !mapState[action];
                    
                    this.classList.toggle('active', mapState[action]);
                    
                    // Aplicar la acción
                    switch(action) {
                        case 'pandillas':
                            toggleMarkers(gangMarkers, mapState.pandillas);
                            break;
                        case 'integrantes':
                            toggleMarkers(memberMarkers, mapState.integrantes);
                            break;
                        case 'incidentes':
                            toggleMarkers(incidentMarkers, mapState.incidentes);
                            break;
                        case 'peligrosas':
                            toggleHeatmap(mapState.peligrosas);
                            break;
                        case 'rivalidad':
                            toggleRivalryLines(mapState.rivalidad);
                            break;
                    }

                    // Asegurarse de que si el mapa de calor está activo, los marcadores de pandillas se oculten (opcional, pero mejora la visibilidad)
                    if (mapState.peligrosas) {
                        toggleMarkers(gangMarkers, false);
                    } else if (mapState.pandillas) {
                        toggleMarkers(gangMarkers, true);
                    }
                });
            });
        }
        function gm_authFailure() {
        const mapDiv = document.getElementById("map-admin");
        mapDiv.innerHTML = `
            <div style="text-align: center; padding: 50px; color: red; font-weight: bold;">
                ❌ ERROR AL CARGAR EL MAPA ❌<br><br>
                1. Revisa que tu Clave API en Google Maps sea correcta.<br>
                2. Asegúrate de haber habilitado la 'Maps JavaScript API'.
            </div>
        `;
        mapDiv.style.backgroundColor = '#ffeeee';
        }
    </script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2hmJyVpimhxu1OsTt9eUIz-9NUnj2qYw&libraries=visualization,geometry&callback=initMap&callback=initMap"></script></body>
</html>