<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "elassssss"; 

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener datos de pandillas
$sql = "SELECT * FROM Pandillas WHERE latitud_reunion IS NOT NULL AND longitud_reunion IS NOT NULL";
$result = $conn->query($sql);

$gangs = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $gangs[] = $row;
    }
}

$conn->close();

// Enviar como JSON
header('Content-Type: application/json');
echo json_encode($gangs);
?>