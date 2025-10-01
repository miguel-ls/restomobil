<?php
class Venta {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function update($data) {
        // CORRECCIÓN: La llamada al SP ahora solo incluye los parámetros que realmente existen.
        $query = "CALL sp_update_venta(:id, :fecha_emision)";

        $stmt = $this->pdo->prepare($query);

        // Limpiar y vincular los parámetros correctos
        $stmt->bindParam(':id', $data->id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_emision', $data->fecha_emision);

        if ($stmt->execute()) {
            return true;
        }

        // Imprimir errores si la ejecución falla
        error_log("Error al ejecutar sp_update_venta: " . implode(";", $stmt->errorInfo()));
        return false;
    }
}
?>