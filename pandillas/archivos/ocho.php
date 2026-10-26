<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header("Location: dos.php"); 
    exit(); 
}

$mensaje_status = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo_usuario = $_POST['email'];
    $info = $_POST['info'];
    
    // Crear carpeta logs si no existe
    if (!file_exists('logs')) {
        mkdir('logs', 0777, true);
    }
    
    // Guardar en archivo local 
    $log_data = "Fecha: " . date("Y-m-d H:i:s") . "\n";
    $log_data .= "Email: " . $correo_usuario . "\n";
    $log_data .= "Mensaje: " . $info . "\n";
    $log_data .= "----------------------------------------\n";
    
    file_put_contents("logs/solicitudes_" . date("Y-m-d") . ".log", $log_data, FILE_APPEND);
    
    header("Location: mailto:186115@upslp.edu.mx?subject=Solicitud%20de%20Soporte&body=Correo%20del%20usuario:%20" . 
           urlencode($correo_usuario) . "%0A%0AMensaje:%20" . urlencode($info));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ayuda Soporte</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .top-nav { background-color: #0d47a1; color: white; padding: 10px 0; display: flex; }
        .nav-item { padding: 10px 20px; cursor: pointer; text-decoration: none; color: white; border-right: 1px solid #1976d2; }
        .nav-item.active { background-color: white; color: #0d47a1; font-weight: bold; }
        .main-content { padding: 20px; flex-grow: 1; display: flex; justify-content: center; }
        .content-box { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 600px; }
        .contact-section { margin-bottom: 40px; }
        .contact-section h2 { font-size: 24px; margin-bottom: 15px; }
        
        /* ESTILOS PARA EL FORMULARIO */
        .contact-input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 10px; }
        .contact-textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; height: 100px; resize: none; margin-bottom: 10px; }
        .btn-enviar { background-color: #0d47a1; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        .btn-enviar:hover { background-color: #1565c0; }
        
        .faq-section h2 { font-size: 24px; margin-bottom: 15px; }
        .faq-button { width: 100%; background-color: #333; color: white; border: none; padding: 15px; cursor: pointer; border-radius: 4px; font-size: 16px; margin-bottom: 5px; text-align: left; display: flex; justify-content: space-between; align-items: center; }
        .faq-button:hover { background-color: #555; }
        .faq-button::after { content: '+'; font-size: 20px; font-weight: bold; }
        .faq-button.active::after { content: '−'; }
        .faq-content { background-color: #f9f9f9; padding: 0; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, padding 0.3s ease-out; border-left: 3px solid #0d47a1; margin-bottom: 10px; }
        .faq-content.active { padding: 15px; max-height: 300px; }
        .faq-answer { line-height: 1.6; color: #333; }
        .faq-answer ul { margin: 10px 0 10px 20px; }
        .footer { background-color: #333; color: white; padding: 20px; margin-top: auto; }
        .footer-contact-info { margin-bottom: 10px; }
        
        /* Estilo para mensajes de estado */
        .status-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php echo $mensaje_status; ?>

    <div class="top-nav" id="adminNav">
        <a class="nav-item" href="cuatro.php">Captura pandillas</a>
        <a class="nav-item" href="cinco.php">Captura integrantes</a>
        <a class="nav-item" href="seis.php">Reportes consultas</a>
        <a class="nav-item" href="siete.php">Sistema mapa</a>
        <a class="nav-item" href="diez.php">Modificaciones/Eliminar</a>
        <a class="nav-item" href="ocho.php">Ayuda soporte</a>
        <a class="nav-item" href="nueve.php">Agregar usuario</a>
        <a class="nav-item" href="tres.php">Inicio</a>
    </div>

    <div class="main-content">
        <div class="content-box">
            
            <div class="contact-section">
                <h2>Contacto y Soporte</h2>
                <form method="POST" action="">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Tu Correo Electrónico:</label>
                    <input type="email" name="email" class="contact-input" placeholder="ejemplo@correo.com" required>
                    
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Descripción del Problema:</label>
                    <textarea name="info" class="contact-textarea" placeholder="Describe tu problema o duda aquí..." required></textarea>
                    
                    <button type="submit" class="btn-enviar">Enviar Mensaje</button>
                    <p style="font-size: 12px; color: #666; margin-top: 10px;">
                        * El mensaje será enviado al administrador: 186115@upslp.edu.mx
                    </p>
                </form>
            </div>
            
            <div class="faq-section">
                <h2>Preguntas frecuentes</h2>
                
                <button class="faq-button">¿Cómo registrar una nueva pandilla/integrante?</button>
                <div class="faq-content">
                    <div class="faq-answer">
                        <p>Para registrar una nueva pandilla:</p>
                        <ul>
                            <li>Accede a la sección "Captura pandillas o integrante"</li>
                            <li>Completa todos los campos obligatorios</li>
                            <li>Haz clic en "Guardar"</li>
                        </ul>
                    </div>
                </div>
                
                <button class="faq-button">¿Cómo elimino un dato incorrecto?</button>
                <div class="faq-content">
                    <div class="faq-answer">
                        <p>Ve a la nueva sección <strong>"Modificaciones"</strong> (diez.php) en el menú. Allí podrás buscar, editar y eliminar registros de forma permanente.</p>
                    </div>
                </div>
                
                <button class="faq-button">¿Cómo generar reportes de una pandilla?</button>
                <div class="faq-content">
                    <div class="faq-answer">
                        <p>Para generar reporte de una pandilla:</p>
                        <ul>
                            <li>Accede a la sección "Reporte cosnultas"</li>
                            <li>Despliegue la barra de selección</li>
                            <li>Selecciona el tipo de reporte que desea generar</li>
                            <li>Haz clic en "Exportar a PDF"</li>
                        </ul>
                    </div>
                </div>

                <button class="faq-button">¿Cómo usar el mapa?</button>
                <div class="faq-content">
                    <div class="faq-answer">
                        <p>Accede al sistema de mapa. Selecciona los filtros para ver pandillas, integrantes o zonas de calor. Requiere conexión a internet.</p>
                    </div>
                </div>
                
                <button class="faq-button">¿Cómo registrar un nuevo administrador?</button>
                <div class="faq-content">
                    <div class="faq-answer">
                        <p>Para registrar un nuevo usuario:</p>
                        <ul>
                            <li>Accede a la sección "Agregar usuario" desde el menú principal</li>
                            <li>Completa todos los campos obligatorios del formulario</li>
                            <li>Verifica que la información del usuario antes de guardar</li>
                            <li>Haz clic en el botón "Registrar usuario"</li>
                            <li>Confirma el mensaje de éxito en pantalla</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="footer-contact-info">
            <strong>Correos de contacto</strong>
            <p>186115@upslp.edu.mx</p>
            <p style="font-size: 12px; margin-top: 5px;">Para soporte técnico, utiliza el formulario de arriba</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqButtons = document.querySelectorAll('.faq-button');
            faqButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('active');
                    const content = this.nextElementSibling;
                    content.classList.toggle('active');
                });
            });
            
            // Validación del formulario
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const email = form.querySelector('input[name="email"]').value;
                    const info = form.querySelector('textarea[name="info"]').value;
                    
                    if (!email || !info) {
                        e.preventDefault();
                        alert('Por favor, completa todos los campos obligatorios.');
                    }
                });
            }
        });
    </script>
</body>
</html>