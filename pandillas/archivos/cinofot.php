<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: dos.php"); exit(); }
include 'conexion.php';
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pandilla_id = $_POST['pandilla'];
    $nombre = $_POST['nombreCompleto'];
    $caracteristicas = $_POST['caracteristicasFisicas'];
    $direccion = $_POST['direccion'];
   
    // --- LÓGICA DE SUBIDA DE IMAGEN ---
    $ruta_foto = NULL; // Por defecto nulo
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $directorio = "uploads/";
        // Crear carpeta si no existe
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }
        // Nombre único para evitar sobrescribir
        $nombreArchivo = time() . "_" . basename($_FILES["foto"]["name"]);
        $rutaDestino = $directorio . $nombreArchivo;
 
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $rutaDestino)) {
            $ruta_foto = $rutaDestino;
        }
    }
    // ----------------------------------
 
    // Consulta actualizada incluyendo 'fotografia' (según bd.txt tabla Integrantes)
    $sql = "INSERT INTO Integrantes (id_pandilla, nombres, informacion_adicional, direccion_vivienda, fotografia) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $pandilla_id, $nombre, $caracteristicas, $direccion, $ruta_foto);
   
    if ($stmt->execute()) {
        echo "<script>alert('Integrante registrado con fotografía');</script>";
    } else {
        echo "<script>alert('Error al registrar: " . $stmt->error . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captura Integrantes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .top-nav { background-color: #0d47a1; color: white; padding: 10px 0; display: flex; }
        .nav-item { padding: 10px 20px; cursor: pointer; text-decoration: none; color: white; border-right: 1px solid #1976d2; }
        .nav-item.active { background-color: white; color: #0d47a1; font-weight: bold; border-right: 1px solid #0d47a1; }
        .main-content { padding: 20px; flex-grow: 1; }
        .nav-title { font-size: 28px; margin-bottom: 20px; }
        .form-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); display: flex; gap: 30px; }
        .left-column { flex: 2; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .right-column { flex: 1; display: flex; flex-direction: column; align-items: center; }
        /* Estilos actualizados para la imagen */
        .image-preview-container { width: 100%; height: 250px; background-color: #eee; border: 1px solid #ccc; border-radius: 4px; display: flex; justify-content: center; align-items: center; margin-top: 30px; overflow: hidden; }
        .image-preview-container img { width: 100%; height: 100%; object-fit: cover; display: none; }
        .upload-btn { margin-top: 10px; }
        .footer { background-color: #333; color: white; padding: 20px; margin-top: auto; }
    </style>
    <script>
        function previsualizarImagen(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview');
                var placeholder = document.getElementById('placeholder-text');
                output.src = reader.result;
                output.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>
    <div class="top-nav" id="adminNav">
        <a class="nav-item" href="cuatro.php">Captura pandillas</a>
        <a class="nav-item active" href="cinco.php">Captura integrantes</a>
        <a class="nav-item" href="seis.php">Reportes consultas</a>
        <a class="nav-item" href="siete.php">Sistema mapa</a>
        <a class="nav-item" href="ocho.php">ayuda soporte</a>
        <a class="nav-item" href="tres.php">Inicio</a>
    </div>
 
    <div class="main-content">
        <h2 class="nav-title">Captura Integrantes</h2>
       
        <div class="form-container">
            <form id="integranteForm" method="POST" enctype="multipart/form-data" style="display: contents;">
                <div class="left-column">
                    <div class="form-group">
                        <label for="pandilla">Pandilla</label>
                        <select id="pandilla" name="pandilla" class="pandilla-select">
                            <?php
                            $res = $conn->query("SELECT id_pandilla, nombre FROM Pandillas");
                            while($row = $res->fetch_assoc()) {
                                echo "<option value='".$row['id_pandilla']."'>".$row['nombre']."</option>";
                            }
                            ?>
                        </select>
                    </div>
 
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombreCompleto" required>
                    </div>
                   
                    <div class="form-group">
                        <label>Características físicas</label>
                        <textarea name="caracteristicasFisicas" rows="4"></textarea>
                    </div>
 
                    <div class="form-group">
                        <label>Direccion</label>
                        <input type="text" name="direccion">
                    </div>
 
                    <div class="form-group" style="grid-column: span 2;">
                        <button type="submit" style="background-color: #0d47a1; color: white; padding: 15px; border: none; cursor: pointer;">Guardar</button>
                    </div>
                </div>
 
                <div class="right-column">
                    <div class="image-preview-container">
                        <p id="placeholder-text" style="color: gray;">Sin imagen seleccionada</p>
                        <img id="preview" src="#" alt="Tu imagen">
                    </div>
                    <div class="form-group upload-btn">
                        <label>Fotografía</label>
                        <input type="file" name="foto" accept="image/*" onchange="previsualizarImagen(event)">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="footer">
        <div class="footer-contact-info"><strong>Correos de contacto</strong><p>186115@upslp.edu.mx</p></div>
    </div>
</body>
</html>