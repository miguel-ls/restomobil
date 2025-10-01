<?php
class Venta {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function update($data) {
        $query = "CALL sp_update_venta(:id, :fecha_emision, :nombre_cliente, :ruc_cliente, :direccion_cliente)";

        $stmt = $this->pdo->prepare($query);

        // Limpiar y vincular los parámetros
        $stmt->bindParam(':id', $data->id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_emision', $data->fecha_emision);
        $stmt->bindParam(':nombre_cliente', $data->nombre_cliente);
        $stmt->bindParam(':ruc_cliente', $data->ruc_cliente);
        $stmt->bindParam(':direccion_cliente', $data->direccion_cliente);

        if ($stmt->execute()) {
            return true;
        }

        // Imprimir errores si la ejecución falla
        error_log("Error al ejecutar sp_update_venta: " . implode(";", $stmt->errorInfo()));
        return false;
    }
}
?>