// Nota: Para que este código funcione, añade el paquete http a tu pubspec.yaml:
// dependencies:
//   flutter:
//     sdk: flutter
//   http: ^0.13.4

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:mobile_app/config/api_config.dart'; // Importar la configuración

class ApiService {
  // La URL base ahora se gestiona en `config/api_config.dart`.
  static const String _baseUrl = baseUrl;

  /// Intenta iniciar sesión en el sistema.
  /// @param username El nombre de usuario.
  /// @param password La contraseña del usuario.
  /// @return Un Map con los datos del usuario si el login es exitoso, o null si falla.
  Future<Map<String, dynamic>?> login(String username, String password) async {
    final url = Uri.parse('$_baseUrl/login.php');

    try {
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json; charset=UTF-8',
        },
        body: json.encode({
          'username': username,
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
