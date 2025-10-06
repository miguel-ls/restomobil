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
        expect(page).to_have_url(re.compile(r".*dashboard\.php"))
        print("Login successful.")

        # 2. Navegar directamente al formulario de nuevo movimiento
        page.goto("http://localhost:8000/movimiento_form.php")
        expect(page.get_by_role("heading", name="Nuevo Movimiento")).to_be_visible()
        print("Navigated to movimiento_form.php.")

        # 3. Probar la lógica de carga de Cliente/Proveedor
        # Seleccionar 'Proveedor' en el dropdown de Tipo Entidad
        page.get_by_label("Tipo Entidad").select_option("P")
        print("Selected 'Proveedor'.")

        # Esperar y verificar que el dropdown 'Cliente / Proveedor' se cargue con el proveedor de prueba
        # Usamos `to_contain_text` para verificar que la opción esperada esté presente.
        proveedor_dropdown = page.locator("#id_entidad")
        expect(proveedor_dropdown).to_contain_text("Proveedor de Prueba S.R.L.", timeout=15000)
        print("Dropdown 'Cliente / Proveedor' cargado correctamente con el proveedor.")

        # 4. Tomar captura de pantalla del formulario con el proveedor cargado
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