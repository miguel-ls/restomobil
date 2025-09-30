<?php
class ReportesModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Construye y ejecuta una consulta para obtener los datos del reporte dinámico.
     * @param array $selectedColumns - Array de keys de columnas (ej. 'v.id').
     * @param array $filters - Array de objetos de filtro.
     * @param array $dictionary - El diccionario completo de columnas para validación y mapeo.
     * @return array|false - Un array de resultados o false en caso de error.
     */
    public function getReportData($selectedColumns, $filters, $dictionary) {
        // Mapea la key de la columna a su nombre amigable para los alias en el SELECT
        $columnMap = array_column($dictionary, 'friendly_name', 'key');

        $selectClause = [];
        $joinClauses = [];
        $tables = ['v' => 'ventas']; // Siempre partimos de la tabla de ventas

        // Validar y construir la cláusula SELECT y los JOINS necesarios
        foreach ($selectedColumns as $colKey) {
            if (isset($columnMap[$colKey])) {
                $selectClause[] = "{$colKey} AS `{$columnMap[$colKey]}`";

                // Extraer el alias de la tabla (ej. 'c' de 'c.nombre_razon_social')
                $tableAlias = explode('.', $colKey)[0];
                if ($tableAlias !== 'v' && !isset($tables[$tableAlias])) {
                    $tables[$tableAlias] = true; // Marcar la tabla como necesaria
                    switch ($tableAlias) {
                        case 'c':
                            $joinClauses['clientes'] = 'LEFT JOIN clientes c ON v.id_cliente = c.id';
                            break;
                        case 'u':
                            $joinClauses['usuarios'] = 'LEFT JOIN usuarios u ON v.id_usuario = u.id';
                            break;
                        case 'td':
                            $joinClauses['tipos_documentos'] = 'LEFT JOIN tipos_documentos td ON v.id_tipo_documento = td.id';
                            break;
                        case 'mp':
                            // Asumimos que existe una tabla metodos_pago y una columna id_metodo_pago en ventas
                            $joinClauses['metodos_pago'] = 'LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id';
                            break;
                    }
                }
            }
        }

        if (empty($selectClause)) {
            return []; // No hay columnas válidas seleccionadas
        }

        // Construir la cláusula WHERE de forma segura
        $whereClause = "";
        $params = [];
        $paramTypes = "";

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

                    $conditions[] = "{$filter['column']} {$operator} ?";
                    $params[] = $value;
                    $paramTypes .= 's'; // 's' para string, MySQL maneja la conversión
                }
            }

            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(' AND ', $conditions);
            }
        }

        // Construir la consulta final
        $sql = "SELECT " . implode(', ', $selectClause) .
               " FROM ventas v " . implode(' ', $joinClauses) .
               " " . $whereClause . " ORDER BY v.id DESC";

        // Preparar, enlazar y ejecutar la consulta
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Error al preparar la consulta: " . $this->db->error);
            return false;
        }

        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene todas las plantillas de la base de datos.
     * @return array
     */
    public function getTemplates() {
        $stmt = $this->db->prepare("SELECT id, nombre_plantilla FROM reporte_plantillas ORDER BY nombre_plantilla ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los detalles de una plantilla específica por su ID.
     * @param int $id
     * @return array|null
     */
    public function getTemplateById($id) {
        $stmt = $this->db->prepare("SELECT id, nombre_plantilla, columnas FROM reporte_plantillas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $template = $result->fetch_assoc();

        if ($template) {
            // Decodificar el JSON de las columnas a un array de PHP
            $template['columnas'] = json_decode($template['columnas'], true);
        }
        return $template;
    }

    /**
     * Guarda o actualiza una plantilla.
     * @param string $name - Nombre de la plantilla.
     * @param array $columnsConfig - Configuración de las columnas en formato array.
     * @return int - El ID de la plantilla guardada o actualizada.
     */
    public function saveTemplate($name, $columnsConfig) {
        // Verificar si ya existe una plantilla con el mismo nombre para decidir si es INSERT o UPDATE
        $stmt_check = $this->db->prepare("SELECT id FROM reporte_plantillas WHERE nombre_plantilla = ?");
        $stmt_check->bind_param('s', $name);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $existing = $result->fetch_assoc();
        $stmt_check->close();

        $jsonColumns = json_encode($columnsConfig);
        $newId = 0;

        if ($existing) {
            // Actualizar plantilla existente
            $stmt = $this->db->prepare("UPDATE reporte_plantillas SET columnas = ? WHERE id = ?");
            $stmt->bind_param('si', $jsonColumns, $existing['id']);
            $stmt->execute();
            $newId = $existing['id'];
        } else {
            // Insertar nueva plantilla
            $stmt = $this->db->prepare("INSERT INTO reporte_plantillas (nombre_plantilla, columnas) VALUES (?, ?)");
            $stmt->bind_param('ss', $name, $jsonColumns);
            $stmt->execute();
            $newId = $this->db->insert_id;
        }
        $stmt->close();
        return $newId;
    }

    /**
     * Elimina una plantilla por su ID.
     * @param int $id
     * @return bool - True si se eliminó, false en caso contrario.
     */
    public function deleteTemplate($id) {
        $stmt = $this->db->prepare("DELETE FROM reporte_plantillas WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
?>