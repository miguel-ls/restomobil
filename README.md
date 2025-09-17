# Sistema de Gestión para Restaurantes

Este es un proyecto de software integral diseñado para la gestión de restaurantes, cafeterías y bares. El sistema se compone de tres partes principales que trabajan en conjunto:

1.  **Backend (API RESTful)**: El corazón del sistema, construido con PHP puro. Se encarga de toda la lógica de negocio, la interacción con la base de datos MySQL y la exposición de datos a los clientes (web y móvil).
2.  **Frontend Web**: Una aplicación de administración basada en PHP, diseñada para ser utilizada por el personal de caja y los administradores del restaurante. Permite gestionar productos, mesas, pedidos, etc.
3.  **Aplicación Móvil**: Una aplicación desarrollada en Flutter para los mozos, que les permite tomar pedidos directamente desde las mesas de forma rápida y eficiente.

## Estructura de Directorios

-   `/backend`: Contiene todo el código de la API REST en PHP.
    -   `/api`: Los endpoints públicos.
    -   `/config`: Archivos de configuración, como la conexión a la base de datos.
    -   `/core`: Clases base del sistema (ej. Database).
    -   `/models`: Clases que representan los objetos de negocio (ej. Producto, Usuario).
    -   `database.sql`: El script para crear la estructura de la base de datos.
-   `/frontend_web`: Contiene la aplicación web de administración.
    -   `/assets`: Archivos estáticos como CSS, JS e imágenes.
    -   `/templates`: Partes reutilizables de la UI como la cabecera, pie de página y barra lateral.
-   `/mobile_app`: Contiene el código fuente de la aplicación móvil en Flutter.
    -   `/lib`: El directorio principal del código Dart.
    -   `/api`: Servicios para la comunicación con el backend.
    -   `/screens`: Las diferentes pantallas de la aplicación.

## Configuración del Entorno de Desarrollo Local

1.  **Backend**:
    -   Asegúrate de tener un servidor local como XAMPP, WAMP o MAMP que incluya Apache, PHP y MySQL.
    -   Crea una base de datos en MySQL llamada `restaurante_db`.
    -   Importa el archivo `backend/database.sql` en tu base de datos.
    -   Verifica que las credenciales en `backend/config/database.php` coincidan con tu configuración de MySQL.
2.  **Frontend Web**:
    -   Coloca los directorios `backend` y `frontend_web` dentro del directorio `htdocs` (o `www`) de tu servidor local.
    -   Abre `frontend_web/login_handler.php` y asegúrate de que `$api_url` apunta a la ubicación correcta de tu backend local.
3.  **App Móvil**:
    -   Abre `mobile_app/lib/api/api_service.dart` y ajusta la constante `_baseUrl` para que apunte a la IP de tu máquina local (no `localhost`). Si usas el emulador de Android, la IP `10.0.2.2` generalmente funciona para referirse al `localhost` de la máquina anfitriona.

Con estos pasos, deberías poder ejecutar el sistema en un entorno local.
