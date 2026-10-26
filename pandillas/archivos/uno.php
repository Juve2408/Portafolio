<?php
//  INCLUIR LA CONEXIÓN
include 'conexion.php'; 

//  CONSULTAR LA BASE DE DATOS
$sql = "SELECT * FROM pandillas";
$result = $conn->query($sql);

$pandillas_array = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Convertimos las coordenadas a números decimales
        $row['latitud_reunion'] = floatval($row['latitud_reunion']);
        $row['longitud_reunion'] = floatval($row['longitud_reunion']);
        $row['num_integrantes_aproximado'] = intval($row['num_integrantes_aproximado']);
        
       
        $pandillas_array[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ela's - Mapa de Situación</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .header { background-color: #0d47a1; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header .logo { font-size: 24px; font-weight: bold; }
        .header .log-in-btn { background-color: #333; color: white; border: none; padding: 8px 15px; cursor: pointer; border-radius: 4px; transition: background 0.3s; }
        .header .log-in-btn:hover { background-color: #555; }
        
        .main-content { padding: 20px; flex-grow: 1; text-align: center; }
        .map-container { max-width: 1200px; margin: 0 auto; background-color: white; border: 1px solid #ccc; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        #map { height: 600px; width: 100%; border-radius: 8px; margin-bottom: 20px; background-color: #eee; }
        
        .controls { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; justify-content: center; align-items: center; background: #f9f9f9; padding: 15px; border-radius: 8px; }
        .control-group { display: flex; flex-direction: column; gap: 5px; }
        .control-label { font-size: 12px; font-weight: bold; color: #555; text-align: left; }
        
        select, button { padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; cursor: pointer; }
        button { background-color: #0d47a1; color: white; border: none; font-weight: bold; }
        button:hover { background-color: #1565c0; }
        button.active { background-color: #4CAF50; }
        button.secondary { background-color: #757575; }
        
        .zone-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        .zone-btn { padding: 8px 12px; background-color: #e0e0e0; border: none; border-radius: 4px; color: #333; font-weight: normal; }
        .zone-btn:hover { background-color: #d5d5d5; }
        .zone-btn.active { background-color: #0d47a1; color: white; font-weight: bold; }
        
        .loading { text-align: center; padding: 20px; color: #666; font-style: italic; }
        .footer { background-color: #333; color: white; padding: 20px; margin-top: auto; text-align: center; }
        
        /* Estilos para el InfoWindow */
        .info-window-content {
            max-width: 300px;
            font-family: Arial, sans-serif;
        }
        .info-window-title {
            margin: 0 0 10px 0;
            color: #0d47a1;
            font-size: 16px;
            font-weight: bold;
        }
        .info-window-detail {
            margin: 5px 0;
            font-size: 14px;
        }
        .info-window-danger {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            font-size: 11px;
            margin-left: 5px;
        }
        .info-window-section {
            margin-top: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #0d47a1;
        }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        @media (max-width: 768px) {
            .controls { flex-direction: column; align-items: stretch; }
            #map { height: 400px; }
            .zone-buttons { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Ela's Control</div>
        <button class="log-in-btn" onclick="location.href='dos.php'">Iniciar Sesión</button>
    </div>

    <div class="main-content">
        <h1>Mapa de Situación de Pandillas</h1>
        
        <div class="map-container">
            <div class="controls">
                <div class="control-group">
                    <span class="control-label">Filtrar por Peligrosidad:</span>
                    <select id="dangerFilter">
                        <option value="all">Todas las Pandillas</option>
                        <option value="Baja">Baja</option>
                        <option value="Media">Media</option>
                        <option value="Alta">Alta</option>
                        <option value="Extrema">Extrema</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <span class="control-label">Zonas de la Ciudad:</span>
                    <div class="zone-buttons">
                        <button class="zone-btn" data-zone="centro">Centro</button>
                        <button class="zone-btn" data-zone="norte">Norte</button>
                        <button class="zone-btn" data-zone="sur">Sur</button>
                        <button class="zone-btn" data-zone="este">Este</button>
                        <button class="zone-btn" data-zone="oeste">Oeste</button>
                    </div>
                </div>
                
                <div class="control-group" style="flex-direction: row; align-items: flex-end; gap: 10px;">
                    <button id="toggleHeatmap">🔥 Activar Mapa de Calor</button>
                    <button id="resetView" class="secondary">🔄 Restablecer</button>
                </div>
            </div>
            
            <div id="map">
                <div class="loading">Cargando mapa...</div>
            </div>
            
            <!-- SE ELIMINÓ LA TABLA QUE APARECÍA AQUÍ -->
        </div>
    </div>

    <div class="footer">
        <div class="footer-contact-info"><strong>Correos de contacto</strong><p>186115@upslp.edu.mx</p></div>
    </div>

    <script>
        //  PHP A JAVASCRIPT 
        const gangsData = <?php echo json_encode($pandillas_array); ?>;

        let map, heatmap, markers = [], infoWindows = [];
        let heatmapActive = false;

        // Configuración de las zonas (coordenadas centrales)
        const zones = {
            centro: { center: { lat: 22.148236, lng: -100.983523 } },
            norte: { center: { lat: 22.180000, lng: -100.993523 } },
            sur: { center: { lat: 22.110000, lng: -100.990000 } },
            este: { center: { lat: 22.148236, lng: -100.930000 } },
            oeste: { center: { lat: 22.148236, lng: -101.030000 } }
        };

        function initMap() {
            // Inicializar mapa
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: { lat: 22.148236, lng: -100.983523 },
                mapTypeId: "roadmap"
            });

            // Si PHP encontró datos, los cargamos
            if (gangsData && gangsData.length > 0) {
                initMarkers();
                initHeatmap();
                setupEventListeners();
            } else {
                document.getElementById('map').innerHTML = '<div class="loading">No hay datos en la base de datos para mostrar.</div>';
                console.log("PHP Data:", gangsData);
            }
        }

        // Función para obtener el color del marcador basado en peligrosidad
        function getGangMarkerColor(peligrosidad) {
            if (!peligrosidad) return "#9E9E9E";
            
            const peli = peligrosidad.toLowerCase();
            if(peli === "baja") return "#4CAF50";
            else if(peli === "media") return "#FFC107";
            else if(peli === "alta") return "#FF9800";
            else if(peli === "extrema") return "#B71C1C";
            else return "#9E9E9E";
        }

        // Función para crear contenido del InfoWindow
        function createInfoWindowContent(gang) {
            const dangerColor = getGangMarkerColor(gang.peligrosidad);
            const dangerText = gang.peligrosidad || "Desconocida";
            
            // Crear etiqueta de peligrosidad con color
            let dangerBadge = `<span class="info-window-danger" style="background-color: ${dangerColor}">${dangerText}</span>`;
            
            return `
                <div class="info-window-content">
                    <h3 class="info-window-title">${gang.nombre || "Sin nombre"}</h3>
                    
                    <div class="info-window-detail">
                        <strong>Alias:</strong> ${gang.alias || "No especificado"}
                    </div>
                    
                    <div class="info-window-detail">
                        <strong>Peligrosidad:</strong> ${dangerBadge}
                    </div>
                    
                    <div class="info-window-detail">
                        <strong>Zona:</strong> ${gang.zona || "No asignada"}
                    </div>
                    
                    <div class="info-window-detail">
                        <strong>Integrantes:</strong> ${gang.num_integrantes_aproximado || "Desconocido"}
                    </div>
                    
                    <div class="info-window-detail">
                        <strong>Lugar de reunión:</strong> ${gang.lugar_reunion || "No especificado"}
                    </div>
                    
                    ${gang.horario_reunion ? `
                    <div class="info-window-detail">
                        <strong>Horario:</strong> ${gang.horario_reunion}
                    </div>
                    ` : ''}
                    
                    ${gang.descripcion ? `
                    <div class="info-window-section">
                        <strong>Descripción:</strong><br>
                        ${gang.descripcion}
                    </div>
                    ` : ''}
                    
                    ${gang.delitos_asociados ? `
                    <div class="info-window-section" style="border-left-color: #dc3545;">
                        <strong>Delitos asociados:</strong><br>
                        ${gang.delitos_asociados}
                    </div>
                    ` : ''}
                    
                    <div style="margin-top: 10px; font-size: 12px; color: #666;">
                        <em>Haz clic fuera para cerrar</em>
                    </div>
                </div>
            `;
        }

        function initMarkers() {
            // Limpiar marcadores anteriores
            markers.forEach(marker => marker.setMap(null));
            markers = [];
            
            // Cerrar todos los InfoWindows
            infoWindows.forEach(infoWindow => infoWindow.close());
            infoWindows = [];
            
            gangsData.forEach(gang => {
                const lat = parseFloat(gang.latitud_reunion);
                const lng = parseFloat(gang.longitud_reunion);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const markerColor = getGangMarkerColor(gang.peligrosidad);
                    
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: map,
                        title: gang.nombre || "Pandilla",
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 10,
                            fillColor: markerColor,
                            fillOpacity: 1,
                            strokeColor: "white",
                            strokeWeight: 2
                        }
                    });

                    // Crear InfoWindow para este marcador
                    const infoWindow = new google.maps.InfoWindow({
                        content: createInfoWindowContent(gang)
                    });

                    // Asignar evento de clic al marcador
                    marker.addListener('click', () => {
                        // Cerrar todos los InfoWindows antes de abrir uno nuevo
                        infoWindows.forEach(iw => iw.close());
                        
                        // Abrir el InfoWindow
                        infoWindow.open(map, marker);
                        
                        // Centrar el mapa en el marcador
                        map.panTo(marker.getPosition());
                    });

                    // Guardar referencia al marcador 
                    marker.gangData = gang;
                    marker.infoWindow = infoWindow;
                    markers.push(marker);
                    infoWindows.push(infoWindow);
                }
            });
        }

        function initHeatmap() {
            let heatmapPoints = [];
            gangsData.forEach(gang => {
                const lat = parseFloat(gang.latitud_reunion);
                const lng = parseFloat(gang.longitud_reunion);
                if (!isNaN(lat) && !isNaN(lng)) {
                    let weight = 1;
                    const peli = (gang.peligrosidad || "").toLowerCase();
                    if(peli === "alta") weight = 3;
                    if(peli === "extrema") weight = 5;

                    heatmapPoints.push({
                        location: new google.maps.LatLng(lat, lng),
                        weight: weight
                    });
                }
            });

            heatmap = new google.maps.visualization.HeatmapLayer({
                data: heatmapPoints,
                radius: 40,
                opacity: 0.6
            });
        }

        function setupEventListeners() {
            document.getElementById('dangerFilter').addEventListener('change', function() {
                const selectedDanger = this.value.toLowerCase(); 
                markers.forEach(marker => {
                    const markerPeli = (marker.gangData.peligrosidad || "").toLowerCase();
                    const visible = (selectedDanger === 'all' || markerPeli === selectedDanger);
                    marker.setVisible(visible);
                });
            });

            document.getElementById('toggleHeatmap').addEventListener('click', function() {
                heatmapActive = !heatmapActive;
                if (heatmapActive) {
                    this.textContent = '❄️ Desactivar Mapa de Calor';
                    this.classList.add('active');
                    markers.forEach(m => m.setVisible(false));
                    // Cerrar todos los InfoWindows cuando se activa el heatmap
                    infoWindows.forEach(iw => iw.close());
                    heatmap.setMap(map);
                } else {
                    this.textContent = '🔥 Activar Mapa de Calor';
                    this.classList.remove('active');
                    markers.forEach(m => m.setVisible(true));
                    heatmap.setMap(null);
                }
            });

            document.querySelectorAll('.zone-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.zone-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const zoneKey = this.getAttribute('data-zone');
                    if (zones[zoneKey]) {
                        map.setCenter(zones[zoneKey].center);
                        map.setZoom(13);
                    }
                    markers.forEach(marker => {
                        const markerZone = (marker.gangData.zona || '').toLowerCase();
                        if (markerZone === zoneKey) {
                            marker.setVisible(true);
                            marker.setAnimation(google.maps.Animation.DROP);
                        } else {
                            marker.setVisible(false);
                        }
                    });
                    // Cerrar todos los InfoWindows cuando se cambia de zona
                    infoWindows.forEach(iw => iw.close());
                });
            });

            document.getElementById('resetView').addEventListener('click', function() {
                map.setCenter({ lat: 22.148236, lng: -100.983523 });
                map.setZoom(12);
                document.getElementById('dangerFilter').value = 'all';
                document.querySelectorAll('.zone-btn').forEach(b => b.classList.remove('active'));
                markers.forEach(marker => marker.setVisible(true));
                // Cerrar todos los InfoWindows
                infoWindows.forEach(iw => iw.close());
            });
            
           
            map.addListener('click', () => {
                infoWindows.forEach(iw => iw.close());
            });
        }
        
        // Función para manejar errores de Google Maps
        function gm_authFailure() {
            const mapDiv = document.getElementById("map");
            mapDiv.innerHTML = `
                <div style="text-align: center; padding: 50px; color: red; font-weight: bold;">
                     ERROR AL CARGAR EL MAPA <br><br>
                    Por favor, contacte al administrador del sistema.
                </div>
            `;
            mapDiv.style.backgroundColor = '#ffeeee';
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2hmJyVpimhxu1OsTt9eUIz-9NUnj2qYw&libraries=visualization&callback=initMap"></script>
</body>
</html>