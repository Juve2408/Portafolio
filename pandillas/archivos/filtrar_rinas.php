<?php
include 'conexion.php';

$fecha_desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-1 month'));
$fecha_hasta = $_GET['hasta'] ?? date('Y-m-d');

$sql = "SELECT pandillas_involucradas, fecha_rina, hora_rina, ubicacion_rina, descripcion_hechos 
        FROM rinas_pandilleriles 
        WHERE fecha_rina BETWEEN ? AND ?
        ORDER BY fecha_rina DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
$stmt->execute();
$result = $stmt->get_result();

echo '<tr>
    <th>Pandillas Involucradas</th>
    <th>Fecha</th>
    <th>Hora</th>
    <th>Ubicación</th>
    <th>Descripción</th>
</tr>';

while($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>".$row['pandillas_involucradas']."</td>
        <td>".$row['fecha_rina']."</td>
        <td>".$row['hora_rina']."</td>
        <td>".$row['ubicacion_rina']."</td>
        <td>".$row['descripcion_hechos']."</td>
    </tr>";
}
?>