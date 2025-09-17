import 'package:flutter/material.dart';
import 'package:mobile_app/screens/login_screen.dart';

class DashboardScreen extends StatelessWidget {
  final Map<String, dynamic> user;

  const DashboardScreen({Key? key, required this.user}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Bienvenido, ${user['nombre']}'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Cerrar Sesión',
            onPressed: () {
              // Navegar de vuelta a la pantalla de login, limpiando las rutas anteriores.
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (context) => const LoginScreen()),
                (Route<dynamic> route) => false,
              );
            },
          ),
        ],
      ),
      body: GridView.count(
        crossAxisCount: 2, // Muestra 2 mesas por fila, por ejemplo
        padding: const EdgeInsets.all(16.0),
        crossAxisSpacing: 16.0,
        mainAxisSpacing: 16.0,
        children: <Widget>[
          // Placeholder para las mesas. Esto se llenaría con datos de la API.
          _buildTableCard(context, 'Mesa 1', 'disponible'),
          _buildTableCard(context, 'Mesa 2', 'ocupada'),
          _buildTableCard(context, 'Mesa 3', 'reservada'),
          _buildTableCard(context, 'Mesa 4', 'disponible'),
          _buildTableCard(context, 'Mesa 5', 'disponible'),
          _buildTableCard(context, 'Mesa 6', 'ocupada'),
        ],
      ),
    );
  }

  Widget _buildTableCard(BuildContext context, String tableName, String status) {
    Color statusColor;
    IconData statusIcon;

    switch (status) {
      case 'ocupada':
        statusColor = Colors.orange;
        statusIcon = Icons.people;
        break;
      case 'reservada':
        statusColor = Colors.blue;
        statusIcon = Icons.bookmark;
        break;
      default: // disponible
        statusColor = Colors.green;
        statusIcon = Icons.event_seat;
    }

    return Card(
      elevation: 4.0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: InkWell(
        onTap: () {
          // Lógica para seleccionar una mesa y tomar un pedido
          print('$tableName seleccionada');
        },
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Icon(
              statusIcon,
              size: 40,
              color: statusColor,
            ),
            const SizedBox(height: 10),
            Text(
              tableName,
              style: Theme.of(context).textTheme.headline6,
            ),
            const SizedBox(height: 5),
            Chip(
              label: Text(
                status.toUpperCase(),
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
              ),
              backgroundColor: statusColor,
            ),
          ],
        ),
      ),
    );
  }
}
