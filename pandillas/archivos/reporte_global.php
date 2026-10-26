<?php
include 'conexion.php';

$pandilla_id = $_GET['pandilla_id'] ?? 'todas';

// Función para obtener color según peligrosidad
function getColorPeligrosidad($peligrosidad) {
    switch(strtoupper($peligrosidad)) {
        case 'ALTA': return '#dc3545'; // Rojo
        case 'MEDIA': return '#ffc107'; // Amarillo
        case 'BAJA': return '#28a745'; // Verde
        default: return '#6c757d'; // Gris
    }
}

if($pandilla_id === 'todas') {
    $sql_pandillas = "SELECT * FROM Pandillas ORDER BY nombre";
    $stmt = $conn->prepare($sql_pandillas);
} else {
    $sql_pandillas = "SELECT * FROM Pandillas WHERE id_pandilla = ?";
    $stmt = $conn->prepare($sql_pandillas);
    $stmt->bind_param("i", $pandilla_id);
}

$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    echo '<p style="text-align: center; color: #666;">No hay pandillas registradas</p>';
}

while($pandilla = $result->fetch_assoc()) {
    $color = getColorPeligrosidad($pandilla['peligrosidad']);
    
    echo '<div style="margin-bottom: 30px; border: 2px solid ' . $color . '; padding: 20px; border-radius: 8px; background-color: #f8f9fa;">';
    
    // Cabecera con color según peligrosidad
    echo '<div style="background-color: ' . $color . '; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">';
    echo '<h3 style="margin: 0;">' . $pandilla['nombre'] . ' (' . $pandilla['alias'] . ')</h3>';
    echo '<p style="margin: 5px 0 0 0;">Peligrosidad: <strong>' . $pandilla['peligrosidad'] . '</strong></p>';
    echo '</div>';
    
    // Información básica
    echo '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">';
    echo '<p><strong>Descripción:</strong> ' . $pandilla['descripcion'] . '</p>';
    echo '<p><strong>Lugar de Reunión:</strong> ' . $pandilla['lugar_reunion'] . '</p>';
    echo '<p><strong>Integrantes Aproximados:</strong> ' . $pandilla['num_integrantes_aproximado'] . '</p>';
    echo '<p><strong>Rango de Edades:</strong> ' . $pandilla['rango_edades'] . '</p>';
    echo '<p><strong>Horario Reunión:</strong> ' . $pandilla['horario_reunion'] . '</p>';
    echo '<p><strong>Fecha Registro:</strong> ' . $pandilla['fecha_registro'] . '</p>';
    echo '</div>';
    
    // Coordenadas
    if($pandilla['latitud_reunion'] && $pandilla['longitud_reunion']) {
        echo '<p><strong>Coordenadas Reunión:</strong> Lat: ' . $pandilla['latitud_reunion'] . ', Lon: ' . $pandilla['longitud_reunion'] . '</p>';
    }
    
    // Redes sociales
    if($pandilla['redes_sociales']) {
        echo '<p><strong>Redes Sociales:</strong> ' . $pandilla['redes_sociales'] . '</p>';
    }
    
    // Pandillas rivales
    if($pandilla['pandillas_rivales_texto']) {
        echo '<p><strong>Pandillas Rivales:</strong> ' . $pandilla['pandillas_rivales_texto'] . '</p>';
    }
    
    // Obtener y mostrar integrantes
    $sql_integrantes = "SELECT * FROM Integrantes WHERE id_pandilla = ? ORDER BY nombres";
    $stmt2 = $conn->prepare($sql_integrantes);
    $stmt2->bind_param("i", $pandilla['id_pandilla']);
    $stmt2->execute();
    $integrantes = $stmt2->get_result();
    
    if($integrantes->num_rows > 0) {
        echo '<h4 style="border-bottom: 1px solid #ddd; padding-bottom: 5px;">Integrantes (' . $integrantes->num_rows . ')</h4>';
        echo '<table style="width:100%; border-collapse: collapse; margin-top: 10px;">';
        echo '<tr style="background-color: #e9ecef;">';
        echo '<th style="padding: 8px; text-align: left;">Nombre</th>';
        echo '<th style="padding: 8px; text-align: left;">Alias</th>';
        echo '<th style="padding: 8px; text-align: left;">Dirección</th>';
        echo '<th style="padding: 8px; text-align: left;">Fecha Nac.</th>';
        echo '</tr>';
        
        while($integrante = $integrantes->fetch_assoc()) {
            $nombre_completo = $integrante['nombres'] . ' ' . 
                             $integrante['apellido_paterno'] . ' ' . 
                             $integrante['apellido_materno'];
            
            echo '<tr style="border-bottom: 1px solid #dee2e6;">';
            echo '<td style="padding: 8px;">' . $nombre_completo . '</td>';
            echo '<td style="padding: 8px;">' . ($integrante['alias'] ?: '-') . '</td>';
            echo '<td style="padding: 8px;">' . ($integrante['direccion_vivienda'] ?: '-') . '</td>';
            echo '<td style="padding: 8px;">' . ($integrante['fecha_nacimiento'] ?: '-') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p style="color: #666; font-style: italic;">No hay integrantes registrados para esta pandilla.</p>';
    }
    
    echo '</div>'; // Cierre del div principal
}
?>