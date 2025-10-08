import re
from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # 1. Iniciar sesión
        page.goto("http://localhost/frontend/index.php")
        page.get_by_label("Nombre de Usuario").fill("admin")
        page.get_by_label("Contraseña").fill("password")
        page.get_by_role("button", name="Ingresar").click()
        expect(page).to_have_url("http://localhost/frontend/dashboard.php")

        # 2. Navegar a la página de almacenes
        page.goto("http://localhost/frontend/almacenes.php")

        # Esperar a que la tabla se cargue
        expect(page.locator("#almacenes-tbody tr").first).to_be_visible(timeout=10000)

        # 3. Editar el primer almacén
        first_edit_button = page.locator('.btn-edit').first
        first_edit_button.click()

        # Esperar a que el formulario de edición cargue
        expect(page).to_have_url(re.compile(r"almacen_form\.php"))

        # 4. Marcar como predeterminado y guardar
        predeterminado_checkbox = page.locator('input[name="predeterminado"]')
        predeterminado_checkbox.check()

        page.get_by_role("button", name="Actualizar Almacén").click()

        # 5. Esperar la redirección y verificar el resultado
        expect(page).to_have_url(re.compile(r"almacenes\.php\?success=.*"))

        # Esperar a que la tabla se recargue y verificar que el checkbox está marcado
        first_row_checkbox = page.locator('#almacenes-tbody tr:first-child input[type="checkbox"]')
        expect(first_row_checkbox).to_be_checked(timeout=10000)

        # Tomar captura de pantalla final
        screenshot_path = "jules-scratch/verification/verification.png"
        page.screenshot(path=screenshot_path)
        print(f"Screenshot saved to {screenshot_path}")

    except Exception as e:
        print(f"An error occurred: {e}")
        page.screenshot(path="jules-scratch/verification/error.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)