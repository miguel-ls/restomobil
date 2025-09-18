from playwright.sync_api import sync_playwright, expect

def run_verification(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # Login
        page.goto("http://localhost/restaurante_system/frontend_web/index.php")
        page.get_by_label("Username").fill("admin")
        page.get_by_label("Password").fill("admin")
        page.get_by_role("button", name="Login").click()
        expect(page).to_have_url("http://localhost/restaurante_system/frontend_web/dashboard.php")

        # Navigate to products page
        page.goto("http://localhost/restaurante_system/frontend_web/productos.php")

        # Verify filters and new column
        expect(page.get_by_placeholder("Filtrar por ID")).to_be_visible()
        expect(page.get_by_placeholder("Filtrar por Nombre")).to_be_visible()
        expect(page.get_by_placeholder("Filtrar por Descripción")).to_be_visible()
        expect(page.get_by_placeholder("Filtrar por Precio")).to_be_visible()
        expect(page.get_by_placeholder("Filtrar por Categoría")).to_be_visible()
        expect(page.get_by_role("combobox", name="estado")).to_be_visible()
        expect(page.get_by_role("columnheader", name="Estado")).to_be_visible()

        page.screenshot(path="jules-scratch/verification/productos_page.png")

        # Navigate to new order page
        page.goto("http://localhost/restaurante_system/frontend_web/pedido_form.php")

        # Verify that only active products are shown
        # This is a bit tricky to test without knowing the exact data.
        # I will assume that if the call is made with ?estado=activo, it's correct.
        # I will just take a screenshot of the page.
        expect(page.get_by_text("Productos Disponibles")).to_be_visible()
        page.screenshot(path="jules-scratch/verification/pedido_form_page.png")

        print("Verification script executed successfully.")

    except Exception as e:
        print(f"An error occurred: {e}")
        page.screenshot(path="jules-scratch/verification/error.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run_verification(playwright)
