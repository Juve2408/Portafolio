<?php
session_start();
include 'conexion.php';
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT id_usuario, nombre, password, rol FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($password === $row['password']) {
            $_SESSION['usuario_id'] = $row['id_usuario'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['rol'] = $row['rol']; 
            
            // REDIRECCIÓN SEGÚN ROL
            if ($row['rol'] === 'administrador') {
                header("Location: tres.php");
            } elseif ($row['rol'] === 'consultor') {
                header("Location: consultor.php");
            } else {
                // Si el rol no es reconocido, redirige a una página por defecto
                header("Location: tres.php");
            }
            exit();
        } else { 
            $error = "Contraseña incorrecta."; 
        }
    } else { 
        $error = "Correo no registrado."; 
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f4f4; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-box { 
            background-color: white; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            width: 300px; 
            text-align: center; 
        }
        h2 { 
            color: #0d47a1; 
            margin-bottom: 20px;
        }
        input { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box; 
            font-size: 14px;
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background-color: #0d47a1; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #1565c0;
        }
        .error { 
            color: #d32f2f; 
            font-size: 14px; 
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #ffcdd2;
        }
        .success { 
            color: #388e3c; 
            font-size: 14px; 
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #c8e6c9;
        }
        a { 
            text-decoration: none; 
            color: #666; 
            font-size: 14px; 
            display: block; 
            margin-top: 15px; 
            transition: color 0.3s;
        }
        a:hover {
            color: #0d47a1;
        }
        .role-info {
            font-size: 12px;
            color: #666;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Iniciar Sesión</h2>
        
        <?php 
        // Mostrar mensaje de error si existe
        if($error) echo "<div class='error'>$error</div>"; 
        
        // Mostrar mensaje de éxito si se ha cerrado sesión
        if(isset($_GET['logout']) && $_GET['logout'] == 'success') {
            echo "<div class='success'>Has cerrado sesión correctamente.</div>";
        }
        ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Entrar al Sistema</button>
        </form>
        
        <a href="index.html">← Volver al Inicio</a>
        
        <div class="role-info">
            <strong>Roles del sistema:</strong><br>
            • Administrador: Acceso completo<br>
            • Consultor: Solo consultas
        </div>
    </div>
</body>
</html>