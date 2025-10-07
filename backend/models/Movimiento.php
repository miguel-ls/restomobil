<?php
require_once __DIR__ . '/../core/Database.php';

class Movimiento {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function getAll($filter, $tipo_movimiento, $tipo_entidad, $id_almacen, $anio, $mes, $limit, $offset) {
        $stmt = $this->conn->prepare("CALL sp_read_movimientos(:p_filter, :p_tipo_movimiento, :p_tipo_entidad, :p_id_almacen, :p_anio, :p_mes, :p_limit, :p_offset)");
        $stmt->bindParam(':p_filter', $filter, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo_movimiento', $tipo_movimiento, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo_entidad', $tipo_entidad, PDO::PARAM_STR);
        $stmt->bindParam(':p_id_almacen', $id_almacen, PDO::PARAM_INT);
        $stmt->bindParam(':p_anio', $anio, PDO::PARAM_STR);
        $stmt->bindParam(':p_mes', $mes, PDO::PARAM_STR);
        $stmt->bindParam(':p_limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':p_offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $results;
    }

    public function count($filter, $tipo_movimiento, $tipo_entidad, $id_almacen, $anio, $mes) {
        $stmt = $this->conn->prepare("CALL sp_count_movimientos(:p_filter, :p_tipo_movimiento, :p_tipo_entidad, :p_id_almacen, :p_anio, :p_mes)");
        $stmt->bindParam(':p_filter', $filter, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo_movimiento', $tipo_movimiento, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo_entidad', $tipo_entidad, PDO::PARAM_STR);
        $stmt->bindParam(':p_id_almacen', $id_almacen, PDO::PARAM_INT);
        $stmt->bindParam(':p_anio', $anio, PDO::PARAM_STR);
        $stmt->bindParam(':p_mes', $mes, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result['total'];
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("CALL sp_get_movimiento_by_id(:p_id)");
        $stmt->bindParam(':p_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $header = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$header) {
            return false;
        }

        $stmt->nextRowset();

        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $header['detalle'] = $details;

        $stmt->closeCursor();
        return $header;
    }

    public function create($data) {
        $stmt = $this->conn->prepare("CALL sp_create_movimiento(:p_id_almacen, :p_anio, :p_periodo, :p_codigo_movimiento, :p_fecha_movimiento, :p_id_tipo_documento_venta, :p_serie_documento, :p_numero_documento, :p_tipo_entidad, :p_id_cliente, :p_id_proveedor, :p_detalle)");

        $detalleJson = json_encode($data['detalle']);

        $stmt->bindParam(':p_id_almacen', $data['id_almacen']);
        $stmt->bindParam(':p_anio', $data['anio']);
        $stmt->bindParam(':p_periodo', $data['periodo']);
        $stmt->bindParam(':p_codigo_movimiento', $data['codigo_movimiento']);
        $stmt->bindParam(':p_fecha_movimiento', $data['fecha_movimiento']);
        $stmt->bindParam(':p_id_tipo_documento_venta', $data['id_tipo_documento_venta']);
        $stmt->bindParam(':p_serie_documento', $data['serie_documento']);
        $stmt->bindParam(':p_numero_documento', $data['numero_documento']);
        $stmt->bindParam(':p_tipo_entidad', $data['tipo_entidad']);
        $stmt->bindParam(':p_id_cliente', $data['id_cliente']);
        $stmt->bindParam(':p_id_proveedor', $data['id_proveedor']);
        $stmt->bindParam(':p_detalle', $detalleJson);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result;
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare("CALL sp_update_movimiento(:p_id_movimiento, :p_id_almacen, :p_anio, :p_periodo, :p_codigo_movimiento, :p_fecha_movimiento, :p_id_tipo_documento_venta, :p_serie_documento, :p_numero_documento, :p_tipo_entidad, :p_id_cliente, :p_id_proveedor, :p_estado, :p_detalle)");

        $detalleJson = json_encode($data['detalle']);

        $stmt->bindParam(':p_id_movimiento', $id);
        $stmt->bindParam(':p_id_almacen', $data['id_almacen']);
        $stmt->bindParam(':p_anio', $data['anio']);
        $stmt->bindParam(':p_periodo', $data['periodo']);
        $stmt->bindParam(':p_codigo_movimiento', $data['codigo_movimiento']);
        $stmt->bindParam(':p_fecha_movimiento', $data['fecha_movimiento']);
        $stmt->bindParam(':p_id_tipo_documento_venta', $data['id_tipo_documento_venta']);
        $stmt->bindParam(':p_serie_documento', $data['serie_documento']);
        $stmt->bindParam(':p_numero_documento', $data['numero_documento']);
        $stmt->bindParam(':p_tipo_entidad', $data['tipo_entidad']);
        $stmt->bindParam(':p_id_cliente', $data['id_cliente']);
        $stmt->bindParam(':p_id_proveedor', $data['id_proveedor']);
        $stmt->bindParam(':p_estado', $data['estado']);
        $stmt->bindParam(':p_detalle', $detalleJson);

        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("CALL sp_delete_movimiento(:p_id_movimiento)");
        $stmt->bindParam(':p_id_movimiento', $id);
        return $stmt->execute();
    }
}
?>