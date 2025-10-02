<?php
// Incluir la configuración de la base de datos
require_once __DIR__ . '/../config/database.php';

class Database {
    // Propiedad para almacenar la instancia de la conexión
    private static $pdo = null;

    /**
     * Constructor privado para prevenir la instanciación directa.
     */
    private function __construct() {
    }

    /**
     * Prevenir la clonación de la instancia.
     */
    private function __clone() {
    }

    /**
     * Método estático para obtener la instancia de la conexión a la base de datos (PDO).
     * Si la conexión no existe, la crea.
     *
     * @return PDO
     */
    public static function getInstance() {
        if (self::$pdo === null) {
            try {
                // Usar las constantes y variables definidas en config/database.php
                global $pdo_options;
                self::$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $pdo_options);
            } catch (PDOException $e) {
                // Lanzar la excepción para que pueda ser manejada por el código que llama.
                throw $e;
            }
        }
        return self::$pdo;
    }
}
?>
