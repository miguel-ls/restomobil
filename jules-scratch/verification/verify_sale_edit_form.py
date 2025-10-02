import re
from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # 1. Iniciar sesión
        page.goto("http://localhost:8000/index.php")
        page.get_by_label("Usuario").fill("admin")
        page.get_by_label("Contraseña").fill("password")
        page.get_by_role("button", name="Ingresar").click()

        # Esperar a que la página de dashboard cargue
        expect(page).to_have_url("http://localhost:8000/dashboard.php")

        # 2. Navegar a la página de ventas
        page.goto("http://localhost:8000/ventas.php")

        # Esperar a que la tabla de ventas cargue
        expect(page.get_by_role("heading", name="Historial de Ventas")).to_be_visible()

        # 3. Hacer clic en el botón de editar de la primera venta en la tabla
        edit_button = page.locator('a.btn-edit-custom').first
        expect(edit_button).to_be_visible(timeout=10000)
        edit_button.click()

        # 4. Verificar que el formulario de edición se ha cargado usando una expresión regular
        expect(page).to_have_url(re.compile(r"venta_edit_form\.php\?id=\d+"))

        # 5. Verificar que los campos de solo lectura están presentes y visibles
        expect(page.get_by_label("Tipo de Comprobante")).to_be_visible()
        expect(page.get_by_label("Serie")).to_be_visible()
        expect(page.get_by_label("Número")).to_be_visible()

        # Verificar que los campos no están vacíos
        expect(page.get_by_label("Tipo de Comprobante")).not_to_be_empty()
        expect(page.get_by_label("Serie")).not_to_be_empty()
        expect(page.get_by_label("Número")).not_to_be_empty()

        # 6. Tomar una captura de pantalla para la verificación visual
        screenshot_path = "jules-scratch/verification/verification.png"
        page.screenshot(path=screenshot_path)
        print(f"Captura de pantalla guardada en {screenshot_path}")

    except Exception as e:
        print(f"Ha ocurrido un error durante la verificación: {e}")
        page.screenshot(path="jules-scratch/verification/error.png")
    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)