<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: dos.php");
    exit();
}

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'control_pandillas'; // Cambia por el nombre de tu BD
$username = 'root'; // Cambia por tu usuario
$password = ''; // Cambia por tu contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Inicializar variables
$mensaje = '';
$error = '';
$mostrarCambioPassword = false;

// Obtener información del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT nombre, email, password, rol FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['rol'];
    
    // Verificar si el usuario tiene rol de consultor
    if ($usuario['rol'] === 'admin') {
        $mostrarCambioPassword = true;
        
        // Procesar cambio de contraseña si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
            $passwordActual = $_POST['password_actual'] ?? '';
            $nuevoPassword = $_POST['nuevo_password'] ?? '';
            $confirmarPassword = $_POST['confirmar_password'] ?? '';
            
            // Validaciones
            if (empty($passwordActual) || empty($nuevoPassword) || empty($confirmarPassword)) {
                $error = 'Todos los campos son obligatorios';
            } elseif ($nuevoPassword !== $confirmarPassword) {
                $error = 'Las nuevas contraseñas no coinciden';
            } elseif (strlen($nuevoPassword) < 4) {
                $error = 'La nueva contraseña debe tener al menos 4 caracteres';
            } elseif ($passwordActual !== $usuario['password']) {
                // En tu tabla las contraseñas están en texto plano, si las hasheas deberías usar password_verify()
                $error = 'La contraseña actual es incorrecta';
            } else {
                try {
                    // Actualizar contraseña en la base de datos
                    $updateStmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                    $updateStmt->execute([$nuevoPassword, $usuario_id]);
                    
                    $mensaje = 'Contraseña cambiada exitosamente';
                    
                    // Actualizar la información del usuario en sesión
                    $usuario['password'] = $nuevoPassword;
                    
                } catch(PDOException $e) {
                    $error = 'Error al actualizar la contraseña: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .top-nav {
            background-color: #0d47a1;
            color: white;
            padding: 10px 0;
            display: flex;
        }
        .nav-item {
            padding: 10px 20px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            border-right: 1px solid #1976d2;
        }
        .nav-item:hover {
            background-color: #1565c0;
        }
        .nav-item.logout {
            background-color: #d32f2f;
            border-right: none;
            margin-left: auto;
        }

        .main-content {
            padding: 40px 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .greeting {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
        }

        .frequent-people-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin-bottom: 30px;
        }

        .password-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .section-title {
            font-size: 18px;
            color: #0d47a1;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .person-input, .password-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 16px;
            text-align: center;
            box-sizing: border-box;
        }

        .password-input:focus {
            background-color: #fff;
            border-color: #0d47a1;
            outline: none;
        }

        .btn {
            background-color: #0d47a1;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #1565c0;
        }

        .mensaje-exito {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }

        .mensaje-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }

        .user-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .info-item {
            margin: 10px 0;
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .footer {
            background-color: #333;
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: auto;
        }
    </style>
</head>
<body>

   
    <div class="top-nav" id="adminNav">
    <a class="nav-item" href="cuatro.php">Captura pandillas</a>
    <a class="nav-item" href="cinco.php">Captura integrantes</a>
    <a class="nav-item" href="seis.php">Reportes consultas</a>
    <a class="nav-item" href="siete.php">Sistema mapa</a>
    <a class="nav-item" href="diez.php">Modificaciones/Eliminar</a>
    <a class="nav-item" href="ocho.php">Ayuda soporte</a>
    <a class="nav-item" href="nueve.php">Agregar usuario</a>
    <a class="nav-item" href="tres.php">Inicio</a>
  <a class="nav-item logout" href="uno.php">Salir</a>
</div>

    <div class="main-content">
        <div class="greeting">Hola usuario: <?php echo htmlspecialchars($usuario['nombre'] ?? 'Invitado'); ?></div>
        
        <div class="frequent-people-section">
            <div class="section-title">Personas más frecuentes</div>
            <input type="text" class="person-input" value="-Luis" readonly>
            <input type="text" class="person-input" value="Eduardo" readonly>
            <input type="text" class="person-input" value="Edwin" readonly>
        </div>

        <?php if ($mostrarCambioPassword): ?>
        <div class="password-section">
            <div class="section-title">Cambiar Contraseña</div>
            
            <?php if ($mensaje): ?>
                <div class="mensaje-exito"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="password" class="password-input" name="password_actual" 
                       placeholder="Contraseña actual" required>
                
                <input type="password" class="password-input" name="nuevo_password" 
                       placeholder="Nueva contraseña (mínimo 4 caracteres)" required>
                
                <input type="password" class="password-input" name="confirmar_password" 
                       placeholder="Confirmar nueva contraseña" required>
                
                <button type="submit" name="cambiar_password" class="btn">
                    Cambiar Contraseña
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div class="footer-contact-info">
            <strong>Correos de contacto</strong>
            <p>186115@upslp.edu.mx</p>
        </div>
    </div>

</body>
</html>