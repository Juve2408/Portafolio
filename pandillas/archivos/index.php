<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inteligencia - Inicio</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .header { background-color: #0d47a1; color: white; padding: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .hero { flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; }
        .hero h2 { color: #333; margin-bottom: 40px; }
        
        .cards-container { display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; }
        .card { background: white; width: 300px; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s; cursor: pointer; }
        .card:hover { transform: translateY(-10px); }
        .icon { font-size: 60px; margin-bottom: 20px; display: block; }
        .card h3 { color: #0d47a1; margin-bottom: 15px; }
        .card p { color: #666; margin-bottom: 25px; line-height: 1.5; }
        
        .btn { display: inline-block; padding: 12px 30px; border-radius: 25px; text-decoration: none; font-weight: bold; transition: background 0.3s; }
        .btn-citizen { background-color: #4CAF50; color: white; }
        .btn-citizen:hover { background-color: #388E3C; }
        .btn-admin { background-color: #333; color: white; }
        .btn-admin:hover { background-color: #555; }
        
        .footer { background-color: #333; color: white; padding: 15px; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Sistema de Control de Pandillas SLP</h1>
    </div>

    <div class="hero">
        <h2>Seleccione su perfil de acceso</h2>
        
        <div class="cards-container">
            <div class="card" onclick="location.href='uno.php'">
                <span class="icon">🗺️</span>
                <h3>Ciudadano</h3>
                <p>Acceso público. Consulte el mapa de zonas de riesgo y ubicación de pandillas en el estado.</p>
                <a href="uno.php" class="btn btn-citizen">Ver Mapa</a>
            </div>

            <div class="card" onclick="location.href='dos.php'">
                <span class="icon">🛡️</span>
                <h3>Administración</h3>
                <p>Acceso restringido. Gestión de pandillas, integrantes, reportes de inteligencia y análisis.</p>
                <a href="dos.php" class="btn btn-admin">Iniciar Sesión</a>
            </div>
        </div>
    </div>

    <div class="footer">
        © 2025 Ela's - Seguridad Pública
    </div>

</body>
</html>