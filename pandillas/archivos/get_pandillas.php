<?php
// archivo de conexión
include 'conexion.php';

header('Content-Type: application/json');

$pandillas_data = [];


$sql = "SELECT 
            id_pandilla, 
            nombre,
            nombre as alias, -- 
            descripcion, 
            num_integrantes_aproximado, 
            rango_edades, 
            horario_reunion, 
            lugar_reunion, 
            latitud_reunion, 
            longitud_reunion, 
            peligrosidad, 
            pandillas_rivales_texto,
            zona
        FROM Pandillas";

$result = $conn->query($sql);

if ($result) {
   
    while ($row = $result->fetch_assoc()) {
        $pandillas_data[] = $row;
    }

    // Devolver los datos codificados como JSON
    echo json_encode($pandillas_data);
    
} else {
    // 5. Manejo de errores
    http_response_code(500);
    echo json_encode(["error" => "Error al ejecutar la consulta: " . $conn->error]);
}

// Cerrar la conexión
$conn->close();
?>