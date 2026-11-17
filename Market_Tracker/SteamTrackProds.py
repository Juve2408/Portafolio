import os
import requests
import time
import random
import sqlite3
from datetime import datetime
from urllib.parse import urlparse, unquote, quote

#Convertir la URL pública del mercado de Steam a la URL de la API de Steam para obtener el precio.
def convertir_a_api_steam(url_publica: str, moneda: int = 19) -> tuple[str, str]:
    partes = urlparse(url_publica)
    segmentos = partes.path.strip("/").split("/")
    
    if len(segmentos) < 4 or segmentos[0] != "market" or segmentos[1] != "listings":
        raise ValueError(f"URL inválida del mercado de Steam: {url_publica}")
    
    appid = segmentos[2]
    nombre_raw = unquote(segmentos[3])
    market_hash_name = quote(nombre_raw)
    
    url_json = f"https://steamcommunity.com/market/priceoverview/?appid={appid}&currency={moneda}&market_hash_name={market_hash_name}"
    return url_json, nombre_raw

# Pega aquí las URLs  del mercado de Steam que quieras monitorear
urls_cliente = [
    "https://steamcommunity.com/market/listings/730/Clutch%20Case",
    "https://steamcommunity.com/market/listings/730/Fracture%20Case",
    "https://steamcommunity.com/market/listings/730/Dreams%20%26%20Nightmares%20Case",
    "https://steamcommunity.com/market/listings/730/Prisma%20Case",
    "https://steamcommunity.com/market/listings/730/Operation%20Hydra%20Case"
    
]
#Cambiar el valor de MONEDA según la moneda deseada: 1 = USD, 19 = MXN, 3 = EUR
MONEDA = 19 

#Usar SOLO en caso de que la petición sea bloqueada por Steam, para evitar el error 403, agregarlo en respuesta=requests.get(url, headers=encabezados, timeout=10) Linea 72.
#Tambien cambiar la cookie de "browserid" por la tuya propia, que se puede obtener iniciando sesión en Steam y revisando las cookies del navegador.
encabezados = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Cookie": "browserid=TU_BROWSERID_AQUI; sessionid=TU_SESSIONID_AQUI; steamLoginSecure=TU_STEAMLOGINSECURE_AQUI"
}

#Asegúrate de reemplazar el webhook con tu propio webhook de Discord para recibir las alertas.
#El webhook se puede crear en un canal de Discord, en la configuración del canal, sección "Integraciones" -> "Webhooks" -> "Crear Webhook". Solo si tienes permisos de administrador o permisos para gestionar webhooks en el canal.
url_webhook = "Pon aquí tu webhook de Discord"

conexion = sqlite3.connect("steam.db")
cursor = conexion.cursor()

cursor.execute('''
    CREATE TABLE IF NOT EXISTS historial (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        articulo TEXT,
        precio REAL,
        fecha TEXT
    )
''')
count = 0
for link in urls_cliente:
    try:
        url, nombre_articulo = convertir_a_api_steam(link, moneda=MONEDA)
    except ValueError as e:
        print(e)
        continue
    pausa = round(random.uniform(4.0, 9.0), 2)
    print(f"\nEsperando {pausa}s antes de consultar: {nombre_articulo}...")
    time.sleep(pausa)
    
    try:
        #si se bloquea la peticion usar el encabezado de la siguiente manera:
        #respuesta = requests.get(url, headers=encabezados, timeout=10)
        respuesta = requests.get(url,  timeout=10)
        
        if respuesta.status_code == 200:
            datos_steam = respuesta.json()
            if datos_steam.get("success") and "lowest_price" in datos_steam:
                precio_texto = datos_steam["lowest_price"]
                precio_limpio = precio_texto.replace("Mex$", "").replace("$", "").replace(",", "").strip()
                precio_final = float(precio_limpio)
                fecha_actual = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                
                cursor.execute('''
                    SELECT precio FROM historial 
                    WHERE articulo = ? 
                    ORDER BY id DESC LIMIT 1
                ''', (nombre_articulo,))
                resultado = cursor.fetchone()
                #Manda mensaje a discord si el precio sube o baja. Si no hay registro previo, solo guarda el precio.
                if resultado is not None:
                    ultimo_precio = resultado[0]
                    if precio_final < ultimo_precio:
                        print(f"¡Bajó! De ${ultimo_precio} a ${precio_final}. Enviando alerta...")
                        mensaje = {
                            "content": f"🚨 **¡Caída de precio!**\nEl artículo **{nombre_articulo}** bajó de **${ultimo_precio}** a **${precio_final}**.\n link:{urls_cliente[count]}"
                        }
                        requests.post(url_webhook, json=mensaje, timeout=10)
                        
                    elif precio_final > ultimo_precio:
                        print(f"Subió. De ${ultimo_precio} a ${precio_final}. Enviando alerta...")
                        mensaje = {
                            "content": f"📈 **¡Subida de precio!**\nEl artículo **{nombre_articulo}** subió de **${ultimo_precio}** a **${precio_final}**.\nlink:{urls_cliente[count]}"
                        }
                        requests.post(url_webhook, json=mensaje, timeout=10)
                    else:
                        print(f"Sin cambios en ${precio_final}.")
                    count += 1
                else:
                    print(f"Primer registro guardado para {nombre_articulo}: ${precio_final}")
                
                cursor.execute('''
                    INSERT INTO historial (articulo, precio, fecha)
                    VALUES (?, ?, ?)
                ''', (nombre_articulo, precio_final, fecha_actual))
                conexion.commit()
            else:
                print(f"Aviso: Steam respondió sin precio disponible para {nombre_articulo}.")
                
        elif respuesta.status_code == 429:
            print("Límite de peticiones alcanzado (429). Deteniendo el ciclo por hoy.")
            break
        else:
            print(f"Error HTTP {respuesta.status_code} al consultar {nombre_articulo}.")
    
    except requests.exceptions.RequestException as e:
        print(f"Error de conexión con {nombre_articulo}: {e}")
    except ValueError as e:
        print(f"Error al convertir el precio numérico de {nombre_articulo}: {e}")

conexion.close()
print("\nCiclo de monitoreo finalizado.")