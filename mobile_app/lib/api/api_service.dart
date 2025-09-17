// Nota: Para que este código funcione, añade el paquete http a tu pubspec.yaml:
// dependencies:
//   flutter:
//     sdk: flutter
//   http: ^0.13.4

import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // Ajusta esta URL a la dirección de tu backend.
  static const String _baseUrl = 'http://10.0.2.2/restaurante_system/backend/api/v1';
  // Nota: 10.0.2.2 es la IP que el emulador de Android usa para referirse al localhost de la máquina anfitriona.

  /**
   * Intenta iniciar sesión en el sistema.
   * @param email El correo del usuario.
   * @param password La contraseña del usuario.
   * @return Un Map con los datos del usuario si el login es exitoso, o null si falla.
   */
  Future<Map<String, dynamic>?> login(String email, String password) async {
    final url = Uri.parse('$_baseUrl/login.php');

    try {
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json; charset=UTF-T',
        },
        body: json.encode({
          'email': email,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        // Login exitoso
        final responseBody = json.decode(response.body);
        return responseBody['user'];
      } else {
        // El login falló (credenciales incorrectas, usuario no encontrado, etc.)
        print('Error en el login: ${response.statusCode}');
        print('Respuesta: ${response.body}');
        return null;
      }
    } catch (e) {
      // Error de conexión (red, DNS, etc.)
      print('Excepción al intentar conectar con el servidor: $e');
      return null;
    }
  }

  // Aquí se añadirían otros métodos para interactuar con la API:
  // Future<List<Product>> getProducts() async { ... }
  // Future<List<Table>> getTables() async { ... }
  // Future<bool> createOrder(Order order) async { ... }
}
