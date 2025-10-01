document.addEventListener('DOMContentLoaded', function () {
    // --- Element References ---
    const availableColumnsEl = document.getElementById('available-columns');
    const selectedColumnsEl = document.getElementById('selected-columns');
    const addColBtn = document.getElementById('add-col');
    const addAllColsBtn = document.getElementById('add-all-cols');
    const removeColBtn = document.getElementById('remove-col');
    const removeAllColsBtn = document.getElementById('remove-all-cols');

    const filterContainer = document.getElementById('filterContainer');
    const addFilterBtn = document.getElementById('addFilterBtn');
    const generateReportBtn = document.getElementById('generateReportBtn');
    const exportXlsxBtn = document.getElementById('exportXlsxBtn');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsPlaceholder = document.getElementById('resultsPlaceholder');
    const resultsTable = document.getElementById('resultsTable');
    const paginationContainer = document.getElementById('paginationContainer');
    const filterTemplate = document.getElementById('filterTemplate');

    const templateSelect = document.getElementById('template-select');
    const loadTemplateBtn = document.getElementById('load-template-btn');
    const deleteTemplateBtn = document.getElementById('delete-template-btn');
    const templateNameInput = document.getElementById('template-name-input');
    const saveTemplateBtn = document.getElementById('save-template-btn');

    // --- State Variables ---
    let reportColumns = []; // Stores the column dictionary from the backend
    let fullReportData = []; // Stores the complete dataset for pagination/export
    let currentPage = 1;
    const rowsPerPage = 15;
    // La variable API_BASE_URL es proporcionada en el HTML a través de PHP.
    const API_URL = `${API_BASE_URL}/reportes_ventas_handler.php`;

    // --- Initialization ---
    // Aplicar SortableJS solo a la lista de seleccionadas para reordenar.
    // Esto evita que la librería interfiera con los eventos de clic en la lista de disponibles.
    new Sortable(selectedColumnsEl, {
        animation: 150,
        ghostClass: 'active' // Usar la clase 'active' de Bootstrap para el estilo de arrastre
    });

    function showAlert(message, type = 'danger') {
        // You can implement a more sophisticated alert system if you have one
        alert(message);
    }

    // --- Core Functions ---
    function fetchFromAPI(action, options = {}) {
        const url = `${API_URL}?action=${action}`;
        return fetch(url, options)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'Error en la comunicación con el servidor') });
                }
                return response.json();
            })
            .then(result => {
                if (result.success === false) {
                    throw new Error(result.error || 'Ocurrió un error en el backend.');
                }
                return result;
            });
    }

    function populateAvailableColumns(columns) {
        availableColumnsEl.innerHTML = '';
        columns.forEach(col => {
            const item = document.createElement('div');
            // Usar una clase de Bootstrap que no interfiera con los eventos de JS
            item.className = 'list-group-item';
            item.dataset.key = col.key;
            item.textContent = col.friendly_name;
            item.style.cursor = 'pointer'; // Añadir cursor para indicar que es clickeable
            availableColumnsEl.appendChild(item);
        });
    }

    function addFilterRow() {
        const newFilter = filterTemplate.content.cloneNode(true);
        const columnDropdown = newFilter.querySelector('.filter-column');

        // Populate with all available columns from the dictionary
        reportColumns.forEach(col => {
            const option = document.createElement('option');
            option.value = col.key;
            option.textContent = col.friendly_name;
            columnDropdown.appendChild(option);
        });

        const valueInput = newFilter.querySelector('.filter-value');
        columnDropdown.addEventListener('change', () => {
            const selectedKey = columnDropdown.value;
            const selectedCol = reportColumns.find(c => c.key === selectedKey);
            valueInput.type = (selectedCol && selectedCol.type === 'date') ? 'date' : 'text';
            valueInput.placeholder = 'Valor';
            valueInput.value = '';
        });

        filterContainer.appendChild(newFilter);
    }

    function renderResults(data, isNewReport = false) {
        if (isNewReport) {
            fullReportData = data;
            currentPage = 1;
        }

        const tbody = resultsTable.querySelector('tbody');
        const thead = resultsTable.querySelector('thead');
        thead.innerHTML = '';
        tbody.innerHTML = '';
        paginationContainer.innerHTML = '';

        if (!fullReportData || fullReportData.length === 0) {
            resultsPlaceholder.textContent = 'La consulta no devolvió resultados.';
            resultsPlaceholder.style.display = 'block';
            resultsTable.style.display = 'none';
            exportXlsxBtn.style.display = 'none';
            return;
        }

        resultsPlaceholder.style.display = 'none';
        resultsTable.style.display = '';
        exportXlsxBtn.style.display = '';

        const headers = Object.keys(fullReportData[0]);
        const headerRow = document.createElement('tr');
        headers.forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);

        const paginatedData = fullReportData.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage);
        paginatedData.forEach(row => {
            const tr = document.createElement('tr');
            headers.forEach(header => {
                const td = document.createElement('td');
                td.textContent = row[header];
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });

        renderPagination(fullReportData.length, currentPage);
    }

    function renderPagination(totalRows, page) {
        const pageCount = Math.ceil(totalRows / rowsPerPage);
        if (pageCount <= 1) return;

        const ul = document.createElement('ul');
        ul.className = 'pagination';

        for (let i = 1; i <= pageCount; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === page ? 'active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.dataset.page = i;
            a.textContent = i;
            li.appendChild(a);
            ul.appendChild(li);
        }
        paginationContainer.appendChild(ul);
    }

    function exportToXlsx(data) {
        if (typeof XLSX === 'undefined') {
            showAlert('La librería de exportación (XLSX) no está disponible.');
            return;
        }

        const ws = XLSX.utils.json_to_sheet(data);
        ws['!autofilter'] = { ref: ws['!ref'] };
        ws['!view'] = { state: 'frozen', ySplit: 1 };

        const border = {
            top: { style: 'thin', color: { rgb: "FFD0D7E5" } },
            bottom: { style: 'thin', color: { rgb: "FFD0D7E5" } },
            left: { style: 'thin', color: { rgb: "FFD0D7E5" } },
            right: { style: 'thin', color: { rgb: "FFD0D7E5" } }
        };
        const headerStyle = {
            font: { bold: true, color: { rgb: "FFFFFFFF" } },
            fill: { fgColor: { rgb: "FF005A9E" } },
            alignment: { vertical: 'center', horizontal: 'center' },
            border: border
        };
        const baseCellStyle = { border: border };
        const oddRowStyle = {
            fill: { fgColor: { rgb: "FFEAF1F8" } },
            border: border
        };

        let colWidths = [];
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let C = range.s.c; C <= range.e.c; ++C) {
            let maxWidth = 0;
            for (let R = range.s.r; R <= range.e.r; ++R) {
                const cell_ref = XLSX.utils.encode_cell({ c: C, r: R });
                if (!ws[cell_ref]) continue;

                if (R === 0) {
                    ws[cell_ref].s = headerStyle;
                } else {
                    ws[cell_ref].s = (R % 2 !== 0) ? oddRowStyle : baseCellStyle;
                }

                const cellTextLength = ws[cell_ref].v ? String(ws[cell_ref].v).length : 0;
                if (cellTextLength > maxWidth) {
                    maxWidth = cellTextLength;
                }
            }
            const headerCell = XLSX.utils.encode_cell({ c: C, r: 0 });
            const headerTextLength = ws[headerCell] ? String(ws[headerCell].v).length : 0;
            maxWidth = Math.max(maxWidth, headerTextLength);
            colWidths[C] = { wch: maxWidth + 2 };
        }
        ws['!cols'] = colWidths;

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Reporte");
        XLSX.writeFile(wb, "Reporte_Ventas.xlsx", { bookSST: true });
    }

    // --- Event Listeners ---
    function moveItems(from, to, items) {
        items.forEach(item => {
            // Usar la clase 'active' de Bootstrap para la selección
            item.classList.remove('active');
            to.appendChild(item);
        });
    }

    // Actualizar selectores para usar la nueva clase de item y la clase 'active'
    addColBtn.addEventListener('click', () => moveItems(availableColumnsEl, selectedColumnsEl, availableColumnsEl.querySelectorAll('.list-group-item.active')));
    addAllColsBtn.addEventListener('click', () => moveItems(availableColumnsEl, selectedColumnsEl, availableColumnsEl.querySelectorAll('.list-group-item')));
    removeColBtn.addEventListener('click', () => moveItems(selectedColumnsEl, availableColumnsEl, selectedColumnsEl.querySelectorAll('.list-group-item.active')));
    removeAllColsBtn.addEventListener('click', () => moveItems(selectedColumnsEl, availableColumnsEl, selectedColumnsEl.querySelectorAll('.list-group-item')));

    // Event listener más robusto para la selección de items
    document.getElementById('dual-list-container').addEventListener('click', e => {
        // Usar .closest() para encontrar el elemento de la lista, sin importar si se hizo clic en el texto o en el div
        const item = e.target.closest('.list-group-item');

        // Asegurarse de que el item existe y pertenece a este contenedor
        if (item && document.getElementById('dual-list-container').contains(item)) {
            e.preventDefault();
            item.classList.toggle('active');
        }
    });

    addFilterBtn.addEventListener('click', addFilterRow);

    filterContainer.addEventListener('click', e => {
        if (e.target.closest('.remove-filter-btn')) e.target.closest('.filter-row').remove();
    });

    generateReportBtn.addEventListener('click', () => {
        const selectedColumns = Array.from(selectedColumnsEl.querySelectorAll('.list-item')).map(item => item.dataset.key);
        if (selectedColumns.length === 0) {
            showAlert('Por favor, seleccione al menos una columna.');
            return;
        }

        const filters = [];
        document.querySelectorAll('.filter-row').forEach(row => {
            const column = row.querySelector('.filter-column').value;
            const operator = row.querySelector('.filter-operator').value;
            const value = row.querySelector('.filter-value').value;
            if (column && value.trim() !== '') {
                filters.push({ column, operator, value });
            }
        });

        generateReportBtn.disabled = true;
        generateReportBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generando...';

        fetchFromAPI('get_report', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ columns: selectedColumns, filters: filters })
        })
        .then(result => renderResults(result.data, true))
        .catch(error => {
            showAlert(`Error al generar el reporte: ${error.message}`);
            resultsPlaceholder.textContent = 'Ocurrió un error al generar el reporte.';
            resultsPlaceholder.style.display = 'block';
            resultsTable.style.display = 'none';
        })
        .finally(() => {
            generateReportBtn.disabled = false;
            generateReportBtn.innerHTML = '<i class="bi bi-gear-wide-connected"></i> Generar Reporte';
        });
    });

    exportXlsxBtn.addEventListener('click', () => exportToXlsx(fullReportData));

    paginationContainer.addEventListener('click', e => {
        e.preventDefault();
        if (e.target.tagName === 'A') {
            const page = parseInt(e.target.dataset.page, 10);
            if (page) {
                currentPage = page;
                renderResults(fullReportData, false);
            }
        }
    });

    // --- Template Management Event Listeners ---
    function loadTemplates(selectId = null) {
        fetchFromAPI('get_templates')
            .then(result => {
                templateSelect.innerHTML = '<option value="" selected>-- Seleccione una plantilla --</option>';
                result.templates.forEach(template => {
                    const option = document.createElement('option');
                    option.value = template.id;
                    option.textContent = template.nombre_plantilla;
                    templateSelect.appendChild(option);
                });
                if (selectId) templateSelect.value = selectId;
            })
            .catch(error => showAlert(`No se pudieron cargar las plantillas: ${error.message}`));
    }

    saveTemplateBtn.addEventListener('click', () => {
        const name = templateNameInput.value.trim();
        if (!name) {
            showAlert('Por favor, ingrese un nombre para la plantilla.');
            return;
        }
        const columns = Array.from(selectedColumnsEl.querySelectorAll('.list-item')).map(item => item.dataset.key);
        if (columns.length === 0) {
            showAlert('Seleccione al menos una columna para guardar en la plantilla.');
            return;
        }

        fetchFromAPI('save_template', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, columns })
        })
        .then(result => {
            alert('Plantilla guardada con éxito.');
            templateNameInput.value = '';
            loadTemplates(result.new_id);
        })
        .catch(error => showAlert(`Error al guardar la plantilla: ${error.message}`));
    });

    loadTemplateBtn.addEventListener('click', () => {
        const templateId = templateSelect.value;
        if (!templateId) {
            showAlert('Por favor, seleccione una plantilla para cargar.');
            return;
        }
        fetchFromAPI(`get_template_details&id=${templateId}`)
            .then(result => {
                // Reset current selection
                moveItems(selectedColumnsEl, availableColumnsEl, selectedColumnsEl.querySelectorAll('.list-item'));

                // Load new columns
                const columnsToSelect = result.columnas || [];
                columnsToSelect.forEach(columnKey => {
                    const columnElement = availableColumnsEl.querySelector(`.list-item[data-key="${columnKey}"]`);
                    if (columnElement) {
                        moveItems(availableColumnsEl, selectedColumnsEl, [columnElement]);
                    }
                });
                templateNameInput.value = result.nombre_plantilla;
            })
            .catch(error => showAlert(`Error al cargar la plantilla: ${error.message}`));
    });

    deleteTemplateBtn.addEventListener('click', () => {
        const templateId = templateSelect.value;
        if (!templateId) {
            showAlert('Por favor, seleccione una plantilla para eliminar.');
            return;
        }
        if (!confirm('¿Está seguro de que desea eliminar la plantilla seleccionada?')) return;

        fetchFromAPI('delete_template', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: templateId })
        })
        .then(() => {
            alert('Plantilla eliminada con éxito.');
            loadTemplates();
        })
        .catch(error => showAlert(`Error al eliminar la plantilla: ${error.message}`));
    });

    // --- Initial Load ---
    fetchFromAPI('get_dictionary')
        .then(data => {
            // Accedemos a la propiedad 'columns' de la respuesta de la API
            reportColumns = data.columns;
            populateAvailableColumns(reportColumns);
            addFilterRow();
            loadTemplates();
        })
        .catch(error => {
            showAlert(`Error fatal: No se pudo cargar la configuración inicial de reportes. ${error.message}`);
            resultsPlaceholder.textContent = 'Error al inicializar el generador de reportes.';
        });
});