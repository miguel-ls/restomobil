<?php
class ReportesModel {
    private $db; // Esta variable contendrá la instancia de PDO

    public function __construct($pdo_instance) {
        $this->db = $pdo_instance;
    }

    /**
     * Construye y ejecuta una consulta para obtener los datos del reporte dinámico usando PDO.
     * Corregido para coincidir con `esquema_ventas.sql`.
     */
    public function getReportData($selectedColumns, $filters, $dictionary) {
        $columnMap = array_column($dictionary, 'friendly_name', 'key');

        $selectClause = [];
        $joinClauses = [];
        $tables = ['v' => 'ventas'];

        foreach ($selectedColumns as $colKey) {
            if (isset($columnMap[$colKey])) {
                // El alias del resultado (AS `...`) ya viene del diccionario
                $selectClause[] = "{$colKey} AS `{$columnMap[$colKey]}`";

                $tableAlias = explode('.', $colKey)[0];
                if ($tableAlias !== 'v' && !isset($tables[$tableAlias])) {
                    $tables[$tableAlias] = true;
                    switch ($tableAlias) {
                        case 'c':
                            $joinClauses['clientes'] = 'LEFT JOIN clientes c ON v.id_cliente = c.id';
                            break;
                        case 'u':
                            // Corregido: la columna es `id_usuario_cajero`
                            $joinClauses['usuarios'] = 'LEFT JOIN usuarios u ON v.id_usuario_cajero = u.id';
                            break;
                        case 'tdv':
                            // Corregido: la tabla es `tipo_documento_venta`
                            $joinClauses['tipo_documento_venta'] = 'LEFT JOIN tipo_documento_venta tdv ON v.id_tipo_documento_venta = tdv.id';
                            break;
                        case 'sd':
                             // Corregido: la tabla es `series_documentos`
                            $joinClauses['series_documentos'] = 'LEFT JOIN series_documentos sd ON v.id_serie_documento = sd.id';
                            break;
                    }
                }
            }
        }

        if (empty($selectClause)) {
            return [];
        }

        $whereClause = "";
        $params = [];

        if (!empty($filters)) {
            $conditions = [];
            $validOperators = ['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE'];

            foreach ($filters as $filter) {
                if (isset($filter['column'], $filter['operator'], $filter['value']) &&
                    isset($columnMap[$filter['column']]) &&
                    in_array($filter['operator'], $validOperators)) {

                    $value = $filter['value'];
                    $operator = $filter['operator'];

                    if ($operator === 'LIKE' || $operator === 'NOT LIKE') {
                        $value = '%' . $value . '%';
                    }

                    $placeholder = ":" . str_replace('.', '_', $filter['column']) . count($params);
                    $conditions[] = "{$filter['column']} {$operator} {$placeholder}";
                    $params[$placeholder] = $value;
                }
            }

            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(' AND ', $conditions);
            }
        }

        $sql = "SELECT " . implode(', ', $selectClause) .
               " FROM ventas v " . implode(' ', $joinClauses) .
               " " . $whereClause . " ORDER BY v.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las plantillas usando PDO.
     */
    public function getTemplates() {
        $stmt = $this->db->prepare("SELECT id, nombre_plantilla FROM reporte_plantillas ORDER BY nombre_plantilla ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los detalles de una plantilla específica por su ID usando PDO.
     */
    public function getTemplateById($id) {
        $stmt = $this->db->prepare("SELECT id, nombre_plantilla, columnas FROM reporte_plantillas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($template) {
            $template['columnas'] = json_decode($template['columnas'], true);
        }
        return $template;
    }

    /**
     * Guarda o actualiza una plantilla usando PDO.
     */
    public function saveTemplate($name, $columnsConfig) {
        $stmt_check = $this->db->prepare("SELECT id FROM reporte_plantillas WHERE nombre_plantilla = :name");
        $stmt_check->execute([':name' => $name]);
        $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);

        $jsonColumns = json_encode($columnsConfig);
        $newId = 0;

        if ($existing) {
            $stmt = $this->db->prepare("UPDATE reporte_plantillas SET columnas = :columns WHERE id = :id");
            $stmt->execute([':columns' => $jsonColumns, ':id' => $existing['id']]);
            $newId = $existing['id'];
        } else {
            $stmt = $this->db->prepare("INSERT INTO reporte_plantillas (nombre_plantilla, columnas) VALUES (:name, :columns)");
            $stmt->execute([':name' => $name, ':columns' => $jsonColumns]);
            $newId = $this->db->lastInsertId();
        }
        return $newId;
    }

    /**
     * Elimina una plantilla por su ID usando PDO.
     */
    public function deleteTemplate($id) {
        $stmt = $this->db->prepare("DELETE FROM reporte_plantillas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>