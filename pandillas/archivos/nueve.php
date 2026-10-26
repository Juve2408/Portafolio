<?php
session_start();
// SEGURIDAD: Verificar que el usuario sea 'admin'. Si no, lo manda al dashboard.
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: tres.php");
    exit();
}
include 'conexion.php';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    // Verificar si el correo ya existe para evitar duplicados
    $check = $conn->query("SELECT id_usuario FROM usuarios WHERE email = '$email'");
    
    if ($check->num_rows > 0) {
        echo "<script>alert('Error: El correo electrónico ya está registrado.');</script>";
    } else {
        // Insertar el nuevo usuario en la base de datos
        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // "ssss" significa que son 4 strings
        $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
        
        if ($stmt->execute()) {
            echo "<script>alert('Usuario registrado exitosamente.'); window.location.href='nueve.php';</script>";
        } else {
            echo "<script>alert('Error al registrar: " . $stmt->error . "');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <style>
        /* Estilo original simple */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        
        .top-nav { background-color: #0d47a1; color: white; padding: 10px 20px; display: flex; align-items: center; }
        .nav-link { color: white; text-decoration: none; font-weight: bold; margin-right: 20px; }
        .nav-link:hover { text-decoration: underline; }
        
        .main-content { padding: 30px; flex-grow: 1; display: flex; flex-direction: column; align-items: center; }
        
        /* Caja del formulario */
        .form-box { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; margin-bottom: 30px; }
        
        .form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        /* Botón verde para acciones de guardado */
        button { background-color: #4CAF50; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; }
        button:hover { background-color: #45a049; }

        /* Estilos de la Tabla */
        table { width: 100%; max-width: 800px; border-collapse: collapse; background-color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #0d47a1; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .footer { background-color: #333; color: white; padding: 20px; text-align: center; margin-top: auto; }
    </style>
</head>
<body>

    <div class="top-nav">
        <a href="tres.php" class="nav-link">← Volver al Panel de Control</a>
        <span style="margin-left: auto;">Gestión de Usuarios (Admin)</span>
    </div>

    <div class="main-content">
        
        <div class="form-box">
            <h2 style="color: #0d47a1; text-align: center; margin-top: 0;">Agregar Nuevo Usuario</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" required>
                </div>
                <div class="form-group">
                    <label>Correo Electrónico (Login)</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Rol / Permisos</label>
                    <select name="rol">
                        <option value="consultor">Consultor (Solo lectura)</option>
                        <option value="admin">Administrador (Acceso total)</option>
                    </select>
                </div>
                <button type="submit">Registrar Usuario</button>
            </form>
        </div>

        <h3 style="color: #333;">Usuarios Registrados en el Sistema</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consultar usuarios existentes
                $sql = "SELECT nombre, email, rol FROM usuarios ORDER BY id_usuario DESC";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['nombre'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . ucfirst($row['rol']) . "</td>"; // ucfirst pone mayúscula inicial
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align:center;'>No hay usuarios registrados</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>

    <div class="footer">
        <strong>Correos de contacto</strong>
        <p>186115@upslp.edu.mx</p>
    </div>

</body>
</html>