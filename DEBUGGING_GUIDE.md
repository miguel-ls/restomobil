# Guía para Ejecutar y Depurar la App Móvil (Flutter) en VS Code

¡Qué bueno que pudiste ingresar a la aplicación web! Este es un gran avance.

Esta guía te explicará cómo puedes ejecutar la aplicación móvil (hecha en Flutter/Dart) en tu entorno de desarrollo local usando Visual Studio Code.

### 1. Prerrequisitos

Antes de empezar, asegúrate de tener instalado lo siguiente:

-   **Flutter SDK**: El kit de desarrollo de Flutter.
-   **Visual Studio Code**: Tu editor de código.
-   **La extensión de Flutter para VS Code**: Si no la tienes, búscala en el panel de Extensiones (`Ctrl+Shift+X`) como `Flutter` y dale a "Instalar".
-   **Un emulador o dispositivo físico**: Debes tener un emulador de Android configurado (vía Android Studio) o un simulador de iOS (en macOS con Xcode), o un teléfono físico conectado a tu PC con el modo de depuración USB activado.

### 2. Pasos a seguir

1.  **Abrir la carpeta del proyecto**:
    -   En Visual Studio Code, ve a `Archivo > Abrir Carpeta...` (o `File > Open Folder...`).
    -   Selecciona la carpeta `mobile_app` que generé. **No la carpeta principal**, sino específicamente la que contiene el proyecto de Flutter.

2.  **Instalar las dependencias**:
    -   Una vez abierto el proyecto, abre una terminal dentro de VS Code (`Terminal > Nuevo Terminal` o `Ctrl+Shift+Ñ`).
    -   Escribe el siguiente comando y presiona Enter. Esto descargará los paquetes necesarios (como el paquete `http` para conectar con la API).
      ```bash
      flutter pub get
      ```

3.  **Verificar la URL del Backend (¡Paso Crítico!)**:
    -   Abre el archivo `mobile_app/lib/api/api_service.dart`.
    -   Busca la línea que dice `static const String _baseUrl = ...`.
    -   **Asegúrate de que tu servidor local (XAMPP, etc.) donde corre el backend PHP esté en ejecución.**
    -   La URL debe ser la correcta para tu caso:
        -   **Si usas un Emulador de Android**: La URL que puse (`http://10.0.2.2/...`) debería funcionar, ya que es la dirección que usa el emulador para conectarse al `localhost` de tu computadora.
        -   **Si usas un Simulador de iOS**: Debes cambiar `10.0.2.2` por `localhost`. La URL sería `http://localhost/restaurante_system/backend/api/v1`.
        -   **Si usas un teléfono físico**: Debes usar la dirección IP de tu computadora en la red local. Por ejemplo: `http://192.168.1.10/...`. (Puedes encontrar tu IP local con `ipconfig` en Windows o `ifconfig` en Mac/Linux).

4.  **Seleccionar tu dispositivo**:
    -   En la esquina inferior derecha de la barra de estado de VS Code, verás el nombre de un dispositivo (o dirá "No Device"). Haz clic ahí.
    -   Se desplegará una lista en la parte superior con los emuladores o dispositivos conectados. Selecciona el que quieres usar.

5.  **Iniciar la depuración**:
    -   Asegúrate de tener un archivo Dart abierto (como `lib/main.dart`).
    -   Presiona la tecla **F5**.
    -   Alternativamente, puedes ir al panel "Ejecutar y Depurar" (el icono de play con un insecto en la barra lateral izquierda) y hacer clic en el botón verde "Iniciar depuración".

La primera vez, Flutter tardará un poco en compilar y lanzar la aplicación en tu dispositivo/emulador. Una vez que inicie, podrás ver la pantalla de login, interactuar con ella, y si pones puntos de interrupción (haciendo clic a la izquierda de los números de línea en el código), la ejecución se detendrá ahí para que puedas inspeccionar las variables.

Con estos pasos, deberías tener la aplicación móvil funcionando en modo de depuración.
