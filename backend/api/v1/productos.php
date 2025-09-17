<?php
// Headers para permitir el acceso desde cualquier origen (CORS) y definir el tipo de contenido.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Incluir archivos de la base de datos y del modelo de producto.
include_once __DIR__ . '/../../core/Database.php';
include_once __DIR__ . '/../../models/Product.php';

// Instanciar la base de datos y el objeto producto.
$product = new Product();

// Obtener los productos.
$stmt = $product->readAll();
$num = $stmt->rowCount();

// Verificar si se encontraron productos.
if ($num > 0) {
    // Array de productos.
    $products_arr = array();
    $products_arr["records"] = array();

    // Recorrer el resultado.
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $product_item = array(
            "id" => $id,
            "nombre" => $nombre,
            "descripcion" => html_entity_decode($descripcion),
            "precio" => $precio,
            "categoria_nombre" => $categoria_nombre
        );
        array_push($products_arr["records"], $product_item);
    }

    // Establecer el código de respuesta - 200 OK.
    http_response_code(200);

    // Mostrar los datos en formato JSON.
    echo json_encode($products_arr);
} else {
    // Establecer el código de respuesta - 404 Not found.
    http_response_code(404);

    // Mensaje de que no se encontraron productos.
    echo json_encode(
        array("message" => "No se encontraron productos.")
    );
}
?>
