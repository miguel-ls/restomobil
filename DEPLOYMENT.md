# Guía de Despliegue

Este documento proporciona una guía paso a paso para desplegar los tres componentes del Sistema de Gestión de Restaurantes en un entorno de producción.

## Requisitos Previos

-   Un servidor web (hosting) compatible con PHP y MySQL. Puede ser un VPS, un servidor dedicado o un hosting compartido de buena calidad.
-   Acceso al servidor (vía SSH o un panel de control como cPanel).
-   Un nombre de dominio (ej. `api.mi-restaurante.com`).

---

### **1. Despliegue del Backend (API REST)**

El backend es el componente más crítico y debe ser desplegado primero.

1.  **Preparar la Base de Datos:**
    -   En tu servidor de hosting, crea una nueva base de datos MySQL.
    -   Crea un usuario de base de datos y asígnale todos los privilegios sobre la nueva base de datos. Anota el nombre de la base de datos, el nombre de usuario y la contraseña.
    -   Importa el archivo `backend/database.sql` en la nueva base de datos. Esto creará todas las tablas necesarias.

2.  **Configurar el Código:**
    -   Abre el archivo `backend/config/database.php`.
    -   Actualiza las constantes `DB_HOST`, `DB_USER`, `DB_PASS`, y `DB_NAME` con las credenciales de la base de datos de producción que creaste en el paso anterior.

3.  **Subir los Archivos:**
    -   Sube todo el contenido del directorio `backend` a tu servidor. Se recomienda colocarlo en un subdominio dedicado (ej. `api.mi-restaurante.com`) para mantener la API separada de la web principal.

4.  **Probar el Endpoint:**
    -   Abre un navegador y visita una de las rutas GET de tu API, como `https://api.mi-restaurante.com/api/v1/productos.php`. Deberías ver una respuesta JSON (posiblemente vacía si aún no hay datos).

---

### **2. Despliegue del Frontend Web**

1.  **Configurar el Código:**
    -   Abre los archivos del frontend que realizan llamadas a la API (empezando por `frontend_web/login_handler.php` y `frontend_web/productos.php`).
    -   Cambia la variable `$api_url` de la URL local (`http://localhost/...`) a la URL de producción de tu backend (ej. `https://api.mi-restaurante.com/api/v1/...`).

2.  **Subir los Archivos:**
    -   Sube todo el contenido del directorio `frontend_web` a la raíz del dominio principal de tu sitio web (ej. `public_html` o `www`).

---

### **3. Despliegue de la Aplicación Móvil (Flutter)**

1.  **Configurar el Código:**
    -   Abre el archivo `mobile_app/lib/api/api_service.dart`.
    -   Actualiza la constante `_baseUrl` para que apunte a la URL de producción de tu API (ej. `https://api.mi-restaurante.com/api/v1`).

2.  **Compilar la Aplicación para Producción:**
    -   Asegúrate de tener el SDK de Flutter y las herramientas de construcción de Android/iOS instaladas.
    -   **Para Android:**
        -   Navega al directorio `mobile_app` en tu terminal.
        -   Ejecuta el comando `flutter build apk --release`.
        -   El archivo APK firmado se encontrará en `build/app/outputs/flutter-apk/app-release.apk`.
    -   **Para iOS:**
        -   Ejecuta `flutter build ios --release`.
        -   Abre el proyecto en Xcode para la configuración final y el archivado.

3.  **Publicar en las Tiendas de Aplicaciones:**
    -   Sigue las guías oficiales de Google Play Console y Apple App Store Connect para subir, describir y publicar tu aplicación. Este es un proceso detallado que requiere crear una ficha de la aplicación, subir capturas de pantalla, etc.
