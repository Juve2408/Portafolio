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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pandilla_id = $_POST['pandilla'];
    $nombres = $_POST['nombres'];
    $ap_p = $_POST['apellido_paterno'];
    $ap_m = $_POST['apellido_materno'];
    $alias = $_POST['alias'];
    $nac = $_POST['fecha_nacimiento'];
    $dir = $_POST['direccion'];
    $lat = $_POST['latitud'];
    $lon = $_POST['longitud'];
    $redes = $_POST['redes_sociales'];
    $carac = $_POST['caracteristicasFisicas'];
    
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

    $nombre_foto = NULL;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_foto = uniqid() . '.' . $ext;
        if (!is_dir('uploads/fotos_integrantes/')) mkdir('uploads/fotos_integrantes/', 0777, true);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/fotos_integrantes/' . $nombre_foto);
    }
    
    $sql = "INSERT INTO integrantes (id_pandilla, nombres, apellido_paterno, apellido_materno, alias, informacion_adicional, delitos_asociados, faltas_cometidas, fecha_nacimiento, redes_sociales, direccion_vivienda, latitud_vivienda, longitud_vivienda, fotografia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssssssss", $pandilla_id, $nombres, $ap_p, $ap_m, $alias, $carac, $delitos_txt, $faltas_txt, $nac, $redes, $dir, $lat, $lon, $nombre_foto);
    
    if ($stmt->execute()) {
        echo "<script>alert('Integrante registrado exitosamente');</script>";
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
    <title>Captura Integrantes</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .top-nav { background-color: #0d47a1; color: white; padding: 10px 0; display: flex; }
        .nav-item { padding: 10px 20px; cursor: pointer; text-decoration: none; color: white; border-right: 1px solid #1976d2; }
        .nav-item.active { background-color: white; color: #0d47a1; font-weight: bold; }
        .main-content { padding: 20px; flex-grow: 1; }
        
        .form-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); display: flex; gap: 30px; max-width: 1200px; margin: 0 auto; }
        .left-column { flex: 3; display: flex; flex-direction: column; gap: 15px; }
        .right-column { flex: 1; display: flex; flex-direction: column; align-items: center; }
        
        .form-row { display: flex; gap: 15px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        .form-group label { font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        
        .geo-box {
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #c8e6c9;
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .geo-inputs { flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .geo-map { flex: 1; height: 250px; background: #ddd; border-radius: 4px; border: 1px solid #bbb; }
        #mapPickerInt { width: 100%; height: 100%; border-radius: 4px; }

        .checkbox-section { margin: 15px 0; padding: 15px; background-color: #f9f9f9; border-radius: 8px; border: 1px solid #ddd; }
        .checkbox-section h4 { margin: 0 0 10px 0; color: #333; }
        .checkbox-group { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; margin-bottom: 15px; }
        .select-all { margin-top: 10px; padding: 5px 10px; background: #e3f2fd; border: 1px solid #bbdefb; border-radius: 4px; cursor: pointer; }
        .select-all:hover { background: #bbdefb; }
        
        .image-preview { width: 100%; height: 200px; background: #eee; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; border: 1px solid #ccc;}
        .image-preview img { max-width: 100%; max-height: 100%; display: none; }
    </style>
    <script>
        function previsualizarImagen(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
        
        function toggleAllDelitos() {
            const checkboxes = document.querySelectorAll('input[name="delitos[]"]');
            const toggle = document.getElementById('toggleAllDelitos');
            checkboxes.forEach(cb => cb.checked = toggle.checked);
        }
        
        function toggleAllFaltas() {
            const checkboxes = document.querySelectorAll('input[name="faltas[]"]');
            const toggle = document.getElementById('toggleAllFaltas');
            checkboxes.forEach(cb => cb.checked = toggle.checked);
        }
    </script>
</head>
<body>
    <div class="top-nav">
        <a class="nav-item" href="cuatro.php">Captura pandillas</a>
        <a class="nav-item active" href="cinco.php">Captura integrantes</a>
        <a class="nav-item" href="seis.php">Reportes consultas</a>
        <a class="nav-item" href="siete.php">Sistema mapa</a>
        <a class="nav-item" href="diez.php">Modificaciones/Eliminar</a>
        <a class="nav-item" href="ocho.php">Ayuda soporte</a>
        <a class="nav-item" href="nueve.php">Agregar usuario</a>
        <a class="nav-item" href="tres.php">Inicio</a>
    </div>

    <div class="main-content">
        <h2 style="color: #0d47a1;">Captura de Integrante</h2>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" style="display:contents;">
                
                <div class="left-column">
                    <div class="form-group">
                        <label>Pandilla *</label>
                        <select name="pandilla" required>
                            <option value="">-- Seleccionar --</option>
                            <?php $p = $conn->query("SELECT id_pandilla, nombre FROM Pandillas");
                            while($r=$p->fetch_assoc()) echo "<option value='{$r['id_pandilla']}'>{$r['nombre']}</option>"; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group"><label>Nombres *</label><input type="text" name="nombres" required></div>
                        <div class="form-group"><label>Apellido P.</label><input type="text" name="apellido_paterno"></div>
                        <div class="form-group"><label>Apellido M.</label><input type="text" name="apellido_materno"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group"><label>Alias</label><input type="text" name="alias"></div>
                        <div class="form-group"><label>Fecha Nacimiento</label><input type="date" name="fecha_nacimiento"></div>
                    </div>

                    <div class="geo-box">
                        <div class="geo-inputs">
                            <h4 style="margin:0; color:#2e7d32;">Vivienda / Ubicación</h4>
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" name="direccion" placeholder="Calle y número">
                            </div>
                            <div class="form-group">
                                <label>Latitud</label>
                                <input type="text" name="latitud" id="latInt" placeholder="Clic en mapa o escribir">
                            </div>
                            <div class="form-group">
                                <label>Longitud</label>
                                <input type="text" name="longitud" id="lngInt" placeholder="Clic en mapa o escribir">
                            </div>
                        </div>
                        <div class="geo-map">
                            <div id="mapPickerInt">Cargando mapa...</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Características / Info Adicional</label>
                        <textarea name="caracteristicasFisicas" rows="3"></textarea>
                    </div>

                    <!-- SECCIÓN DE DELITOS -->
                    <div class="checkbox-section">
                        <h4>Delitos Asociados</h4>
                        <div class="checkbox-group">
                            <?php foreach($delitos as $d): ?>
                                <label><input type="checkbox" name="delitos[]" value="<?php echo $d['id_delito']; ?>"> <?php echo htmlspecialchars($d['nombre_delito']); ?></label>
                            <?php endforeach; ?>
                        </div>
                        
                    </div>

                    <!-- SECCIÓN DE FALTAS  -->
                    <div class="checkbox-section">
                        <h4>Faltas Cometidas</h4>
                        <div class="checkbox-group">
                            <?php foreach($faltas as $f): ?>
                                <label><input type="checkbox" name="faltas[]" value="<?php echo $f['id_falta']; ?>"> <?php echo htmlspecialchars($f['nombre_falta']); ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Redes Sociales</label>
                        <input type="text" name="redes_sociales" placeholder="Separar con comas">
                    </div>

                    <button type="submit" style="background:#0d47a1; color:white; padding:15px; border:none; cursor:pointer; font-size:16px;">Guardar Integrante</button>
                </div>

                <div class="right-column">
                    <div class="image-preview">
                        <span style="color:#aaa;">Sin imagen</span>
                        <img id="preview" alt="Vista previa">
                    </div>
                    <div class="form-group" style="width:100%;">
                        <label>Fotografía</label>
                        <input type="file" name="foto" accept="image/*" onchange="previsualizarImagen(event)">
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        let mapInt, markerInt;
        function initMapInt() {
            const slp = { lat: 22.148236, lng: -100.983523 };
            mapInt = new google.maps.Map(document.getElementById("mapPickerInt"), {
                zoom: 12, center: slp, mapTypeId: "roadmap", streetViewControl: false
            });
            mapInt.addListener("click", (e) => {
                if(markerInt) markerInt.setMap(null);
                markerInt = new google.maps.Marker({ position: e.latLng, map: mapInt, animation: google.maps.Animation.DROP });
                document.getElementById("latInt").value = e.latLng.lat().toFixed(6);
                document.getElementById("lngInt").value = e.latLng.lng().toFixed(6);
            });
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2hmJyVpimhxu1OsTt9eUIz-9NUnj2qYw&callback=initMapInt"></script>
</body>
</html>