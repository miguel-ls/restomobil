import os
import re
from playwright.sync_api import sync_playwright, Page, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # Construir la ruta absoluta al archivo del formulario
        # Usamos os.getcwd() que nos da el directorio raíz del proyecto en este entorno
        project_root = os.getcwd()
        form_path = os.path.join(project_root, 'frontend_web', 'movimiento_form.php')

        # Navegar al archivo local usando el protocolo file://
        page.goto(f"file://{form_path}")
        print(f"Abriendo archivo estático: file://{form_path}")

        # Verificar que los elementos clave del formulario están presentes en el HTML
        expect(page.get_by_role("heading", name=re.compile("Movimiento"))).to_be_visible()
        expect(page.get_by_label("Tipo de Movimiento (E/S)")).to_be_visible()
        expect(page.get_by_label("Código de Movimiento")).to_be_visible()
        expect(page.get_by_label("Tipo Documento")).to_be_visible()
        expect(page.get_by_label("Cliente / Proveedor")).to_be_visible()
        print("Elementos del formulario estático verificados con éxito.")

        # Tomar una captura de pantalla para la verificación visual
        screenshot_path = "jules-scratch/verification/verification_static.png"
        page.screenshot(path=screenshot_path)
        print(f"Captura de pantalla guardada en: {screenshot_path}")

    except Exception as e:
        print(f"Ocurrió un error durante la verificación estática: {e}")
        page.screenshot(path="jules-scratch/verification/error_static.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)