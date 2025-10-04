from playwright.sync_api import sync_playwright, expect
import time
import re

# Generar datos únicos para el proveedor
unique_doc_number = f"20{int(time.time() % 100000000)}"
initial_name = f"Proveedor de Prueba {unique_doc_number}"
edited_name = f"Proveedor Editado {unique_doc_number}"

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    # Aceptar automáticamente los diálogos de confirmación
    page.on("dialog", lambda dialog: dialog.accept())

    try:
        # 1. Iniciar sesión
        page.goto("http://localhost:8000/index.php")
        page.get_by_label("Usuario").fill("admin")
        page.get_by_label("Contraseña").fill("admin")
        page.get_by_role("button", name="Ingresar").click()
        expect(page).to_have_url("http://localhost:8000/dashboard.php")

        # 2. Navegar a la página de Proveedores
        page.get_by_role("link", name="Logística").click()
        page.get_by_role("link", name="Proveedores").click()
        expect(page).to_have_url("http://localhost:8000/proveedores.php")
        page.screenshot(path="jules-scratch/verification/01_proveedores_lista_inicial.png")

        # 3. Crear un nuevo proveedor
        page.get_by_role("link", name="Nuevo Proveedor").click()
        expect(page).to_have_url("http://localhost:8000/proveedor_form.php")

        # Esperar a que los tipos de documento se carguen
        expect(page.get_by_label("Tipo de Documento")).not_to_be_empty()

        page.get_by_label("Tipo de Documento").select_option(label="RUC")
        page.get_by_label("N° de Documento").fill(unique_doc_number)
        page.get_by_label("Nombres y Apellidos / Razón Social").fill(initial_name)
        page.get_by_label("Dirección").fill("Av. Principal 123")
        page.get_by_label("Email").fill("contacto@proveedor.test")
        page.get_by_label("Teléfono").fill("987654321")

        page.screenshot(path="jules-scratch/verification/02_proveedores_formulario_crear.png")
        page.get_by_role("button", name="Crear Proveedor").click()

        # 4. Verificar creación y cerrar modal
        expect(page).to_have_url(re.compile(r".*proveedores\.php\?success=Proveedor\+creado\+con\+%C3%A9xito."))
        expect(page.get_by_role("button", name="OK")).to_be_visible()
        page.get_by_role("button", name="OK").click()
        expect(page.get_by_text(initial_name)).to_be_visible()
        page.screenshot(path="jules-scratch/verification/03_proveedores_lista_con_nuevo.png")

        # 5. Editar el registro
        row_to_edit = page.get_by_role("row").filter(has=page.get_by_text(unique_doc_number))
        row_to_edit.get_by_role("link", name="Editar").click()
        expect(page).to_have_url(re.compile(r".*proveedor_form\.php\?id=\d+"))

        page.get_by_label("Nombres y Apellidos / Razón Social").fill(edited_name)
        page.get_by_label("Estado").select_option("Desactivado")
        page.screenshot(path="jules-scratch/verification/04_proveedores_formulario_editar.png")
        page.get_by_role("button", name="Actualizar Proveedor").click()

        # 6. Verificar actualización y cerrar modal
        expect(page).to_have_url(re.compile(r".*proveedores\.php\?success=Proveedor\+actualizado\+con\+%C3%A9xito."))
        expect(page.get_by_role("button", name="OK")).to_be_visible()
        page.get_by_role("button", name="OK").click()

        row_edited = page.get_by_role("row").filter(has=page.get_by_text(unique_doc_number))
        expect(row_edited.get_by_text(edited_name)).to_be_visible()
        expect(row_edited.get_by_text("Desactivado")).to_be_visible()
        page.screenshot(path="jules-scratch/verification/05_proveedores_lista_con_editado.png")

        # 7. Eliminar el registro (desactivación lógica)
        row_edited.get_by_role("link", name="Eliminar").click()

        # 8. Verificar eliminación y cerrar modal
        expect(page).to_have_url(re.compile(r".*proveedores\.php\?success=Proveedor\+desactivado\+con\+%C3%A9xito."))
        expect(page.get_by_role("button", name="OK")).to_be_visible()
        page.get_by_role("button", name="OK").click()

        row_deleted = page.get_by_role("row").filter(has=page.get_by_text(unique_doc_number))
        expect(row_deleted.get_by_text("Desactivado")).to_be_visible()
        page.screenshot(path="jules-scratch/verification/06_proveedores_lista_con_eliminado.png")

        print("Script de verificación de proveedores ejecutado con éxito.")

    except Exception as e:
        print(f"Ocurrió un error durante la verificación: {e}")
        page.screenshot(path="jules-scratch/verification/error_proveedores.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)