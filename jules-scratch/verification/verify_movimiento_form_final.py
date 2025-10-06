import re
from playwright.sync_api import sync_playwright, Page, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # 1. Iniciar sesión
        page.goto("http://localhost:8000/index.php")
        page.get_by_label("Nombre de Usuario").fill("admin")
        page.get_by_label("Contraseña").fill("password")
        page.get_by_role("button", name="Ingresar").click()

        # Esperar a la redirección al dashboard
        expect(page).to_have_url(re.compile(r".*dashboard\.php"))
        print("Login successful.")

        # 2. Navegar a la página de movimientos
        page.goto("http://localhost:8000/movimientos.php")
        expect(page.get_by_role("heading", name="Gestión de Movimientos")).to_be_visible()
        print("Navigated to apgina de movimientos.")

        # 3. Hacer clic en "Nuevo Movimiento" para ir al formulario
        page.get_by_role("link", name="Nuevo Movimiento").click()

        # 4. Verificar que la página del formulario se ha cargado
        expect(page).to_have_url(re.compile(r".*movimiento_form\.php"))
        expect(page.get_by_role("heading", name="Nuevo Movimiento")).to_be_visible()
        print("Navigated to movimiento_form.php.")

        # 5. Probar la lógica de filtrado dinámico
        # Esperar a que el dropdown de Tipo Documento se cargue
        expect(page.locator("#id_tipo_documento_venta option:not([value=''])")).to_have_count(5, timeout=15000)
        print("Dropdown 'Tipo Documento' cargado correctamente.")

        # Seleccionar 'Entrada' en el dropdown de Operación
        page.get_by_label("Tipo de Movimiento (E/S)").select_option("E")
        print("Selected 'Entrada'.")

        # Esperar y verificar que el dropdown 'Código de Movimiento' se filtre y se cargue
        expect(page.locator("#codigo_movimiento option:not([value=''])")).to_have_count(2, timeout=15000)
        print("Dropdown 'Código de Movimiento' filtrado y cargado correctamente.")

        # 6. Tomar captura de pantalla del formulario con los datos cargados
        screenshot_path = "jules-scratch/verification/verification.png"
        page.screenshot(path=screenshot_path)
        print(f"Screenshot taken and saved to {screenshot_path}")

    except Exception as e:
        print(f"An error occurred during verification: {e}")
        page.screenshot(path="jules-scratch/verification/error.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)