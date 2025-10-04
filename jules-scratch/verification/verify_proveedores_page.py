from playwright.sync_api import sync_playwright, expect
import re

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

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

        # 3. Tomar captura de pantalla
        page.screenshot(path="jules-scratch/verification/proveedores_page.png")

        print("Script de verificación de proveedores ejecutado con éxito.")

    except Exception as e:
        print(f"Ocurrió un error durante la verificación: {e}")
        page.screenshot(path="jules-scratch/verification/error_proveedores.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)