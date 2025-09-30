<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Reportes Dinámicos de Ventas';
include_once 'templates/header.php';
// Incluimos la librería de SheetJS para exportar a Excel.
// La descargaremos y guardaremos localmente para asegurar su disponibilidad.
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <header class="mb-4">
                <h1>Generador de Reportes Dinámicos de Ventas</h1>
            </header>

            <section class="report-builder">
                <!-- Sección de Plantillas -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bookmark-star-fill"></i> Plantillas de Reporte
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-end gx-2">
                                    <!-- Cargar Plantilla -->
                                    <div class="col-md-6 mb-2">
                                        <label for="template-select" class="form-label">Cargar plantilla existente</label>
                                        <select id="template-select" class="form-select">
                                            <option value="" selected>-- Seleccione una plantilla --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button id="load-template-btn" class="btn btn-secondary w-100">
                                            <i class="bi bi-arrow-down-circle"></i> Cargar
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button id="delete-template-btn" class="btn btn-outline-danger w-100">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="row align-items-end gx-2">
                                    <!-- Guardar Plantilla -->
                                    <div class="col-md-6 mb-2">
                                        <label for="template-name-input" class="form-label">Guardar configuración actual como plantilla</label>
                                        <input type="text" id="template-name-input" class="form-control" placeholder="Escriba un nombre para la plantilla...">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button id="save-template-btn" class="btn btn-primary w-100">
                                            <i class="bi bi-save"></i> Guardar
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <small class="text-muted">Si el nombre ya existe, se actualizará la plantilla existente.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila para Selectores y Filtros -->
                <div class="row">
                    <!-- Selector de Columnas -->
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">1. Seleccione y Ordene las Columnas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row" id="dual-list-container">
                                    <div class="col-md-5">
                                        <strong>Columnas Disponibles</strong>
                                        <div id="available-columns" class="list-box"></div>
                                    </div>
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center actions-col">
                                        <button id="add-col" class="btn btn-sm btn-outline-secondary" title="Añadir">&gt;</button>
                                        <button id="add-all-cols" class="btn btn-sm btn-outline-secondary" title="Añadir Todos">&gt;&gt;</button>
                                        <button id="remove-col" class="btn btn-sm btn-outline-secondary" title="Quitar">&lt;</button>
                                        <button id="remove-all-cols" class="btn btn-sm btn-outline-secondary" title="Quitar Todos">&lt;&lt;</button>
                                    </div>
                                    <div class="col-md-5">
                                        <strong>Columnas Seleccionadas (arrastre para ordenar)</strong>
                                        <div id="selected-columns" class="list-box"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Constructor de Filtros -->
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">2. Construya los Filtros (Opcional)</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Añada condiciones para filtrar los datos. Todos los filtros se aplican con un "Y" lógico.</p>
                                <div id="filterContainer">
                                    <!-- Los filtros se añadirán aquí dinámicamente -->
                                </div>
                                <button id="addFilterBtn" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-plus-circle"></i> Añadir Filtro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de Generar Reporte -->
                <div class="text-center my-4">
                    <button id="generateReportBtn" class="btn btn-primary btn-lg">
                        <i class="bi bi-gear-wide-connected"></i> Generar Reporte
                    </button>
                    <button id="exportXlsxBtn" class="btn btn-success btn-lg" style="display: none;">
                        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                    </button>
                </div>

                <!-- Grilla de Resultados -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Resultados del Reporte</h5>
                    </div>
                    <div class="card-body">
                        <div id="resultsContainer" class="table-responsive">
                            <p id="resultsPlaceholder">Los resultados de su consulta aparecerán aquí. Por favor, genere un reporte.</p>
                            <table id="resultsTable" class="table table-striped table-bordered" style="display: none;">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <!-- Paginación -->
                        <nav id="paginationContainer" aria-label="Page navigation" style="display: none;"></nav>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>

<!-- Template para un nuevo filtro -->
<template id="filterTemplate">
    <div class="filter-row input-group mb-2">
        <select class="form-select filter-column" aria-label="Columna de filtro"></select>
        <select class="form-select filter-operator" style="max-width: 120px;" aria-label="Operador de filtro">
            <option value="=">=</option>
            <option value="!=">!=</option>
            <option value=">">&gt;</option>
            <option value="<">&lt;</option>
            <option value=">=">&gt;=</option>
            <option value="<=">&lt;=</option>
            <option value="LIKE">Contiene</option>
            <option value="NOT LIKE">No Contiene</option>
        </select>
        <input type="text" class="form-control filter-value" placeholder="Valor" aria-label="Valor de filtro">
        <button class="btn btn-outline-danger remove-filter-btn" type="button" title="Eliminar filtro">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>

<!-- Estilos específicos para el generador de reportes -->
<style>
    .list-box { width: 100%; border: 1px solid #ccc; padding: 5px; height: 250px; overflow-y: auto; border-radius: 5px; }
    .list-item { padding: 8px 10px; border-bottom: 1px solid #eee; cursor: grab; user-select: none; background-color: #fff; }
    .list-item:last-child { border-bottom: none; }
    .list-item:hover { background-color: #f0f0f0; }
    .list-item.selected { background-color: #dcedff; font-weight: bold; }
    .actions-col button { margin-bottom: 10px; width: 60px; }
    .filter-row .form-select, .filter-row .form-control { min-width: 150px; }
    .pagination { display: flex; justify-content: center; }
</style>

<!-- Incluir el JS del constructor de reportes y SortableJS -->
<script>
    // Pasamos la URL base de la API de PHP a JavaScript
    const API_BASE_URL = "<?php echo rtrim(API_BASE_URL, '/'); ?>";
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="assets/js/reportes_ventas_builder.js"></script>