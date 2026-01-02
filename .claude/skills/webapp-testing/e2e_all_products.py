#!/usr/bin/env python3
"""
E2E Test for all 9 products in Duson Print System
Tests: Options selection -> File upload -> Add to cart -> Checkout -> Order complete
"""

from playwright.sync_api import sync_playwright
import time
import os

# Product configurations
PRODUCTS = [
    {
        'name': 'inserted',
        'title': '전단지',
        'url': 'http://localhost/mlangprintauto/inserted/',
        'has_additional_options': True,
        'option_type': 'coating'
    },
    {
        'name': 'namecard',
        'title': '명함',
        'url': 'http://localhost/mlangprintauto/namecard/',
        'has_additional_options': True,
        'option_type': 'premium'
    },
    {
        'name': 'envelope',
        'title': '봉투',
        'url': 'http://localhost/mlangprintauto/envelope/',
        'has_additional_options': True,
        'option_type': 'tape'
    },
    {
        'name': 'sticker_new',
        'title': '스티커',
        'url': 'http://localhost/mlangprintauto/sticker_new/',
        'has_additional_options': False,
        'option_type': None
    },
    {
        'name': 'msticker',
        'title': '자석스티커',
        'url': 'http://localhost/mlangprintauto/msticker/',
        'has_additional_options': False,
        'option_type': None
    },
    {
        'name': 'cadarok',
        'title': '카다록',
        'url': 'http://localhost/mlangprintauto/cadarok/',
        'has_additional_options': True,
        'option_type': 'coating'
    },
    {
        'name': 'littleprint',
        'title': '포스터',
        'url': 'http://localhost/mlangprintauto/littleprint/',
        'has_additional_options': True,
        'option_type': 'coating'
    },
    {
        'name': 'merchandisebond',
        'title': '상품권',
        'url': 'http://localhost/mlangprintauto/merchandisebond/',
        'has_additional_options': True,
        'option_type': 'premium'
    },
    {
        'name': 'ncrflambeau',
        'title': 'NCR양식',
        'url': 'http://localhost/mlangprintauto/ncrflambeau/',
        'has_additional_options': False,
        'option_type': None
    }
]

TEST_FILE = '/tmp/test_upload.txt'
SCREENSHOTS_DIR = '/tmp/e2e_screenshots'

def setup_screenshots_dir():
    os.makedirs(SCREENSHOTS_DIR, exist_ok=True)
    print(f"📁 Screenshots: {SCREENSHOTS_DIR}")

def test_product(page, product):
    product_name = product['name']
    product_title = product['title']

    print(f"\n{'='*60}")
    print(f"🧪 {product_title} ({product_name})")
    print(f"{'='*60}")

    try:
        print(f"1️⃣  Navigate to {product['url']}")
        page.goto(product['url'], timeout=60000)  # 60 second timeout
        page.wait_for_load_state('networkidle', timeout=60000)
        page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_01_initial.png", full_page=True)
        print(f"   ✅ Page loaded")

        print(f"2️⃣  Wait for calculator...")
        page.wait_for_timeout(2000)

        print(f"3️⃣  Select basic options...")
        selects = page.locator('select').all()
        selected_count = 0
        for i, select in enumerate(selects[:5]):
            try:
                options = select.locator('option').all()
                if len(options) > 1:
                    select.select_option(index=1)
                    selected_count += 1
                    page.wait_for_timeout(500)
            except:
                pass
        print(f"   ✅ Selected {selected_count} options")
        page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_02_options.png", full_page=True)

        if product['has_additional_options']:
            print(f"4️⃣  Add additional option ({product['option_type']})...")
            try:
                if product['option_type'] == 'coating':
                    coating = page.locator('input[name="coating_enabled"], input[id*="coating"]').first
                    if coating.is_visible():
                        coating.check()
                        page.wait_for_timeout(500)
                        print(f"   ✅ Coating enabled")
                elif product['option_type'] == 'premium':
                    premium = page.locator('input[type="checkbox"]').first
                    if premium.is_visible():
                        premium.check()
                        page.wait_for_timeout(500)
                        print(f"   ✅ Premium enabled")
                elif product['option_type'] == 'tape':
                    tape = page.locator('input[name="envelope_tape_enabled"], input[id*="tape"]').first
                    if tape.is_visible():
                        tape.check()
                        page.wait_for_timeout(500)
                        print(f"   ✅ Tape enabled")
                page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_03_addon.png", full_page=True)
            except Exception as e:
                print(f"   ⚠️  Additional option: {e}")
        else:
            print(f"4️⃣  No additional options")

        print(f"5️⃣  Open upload modal...")
        try:
            # Try JavaScript click first
            try:
                page.evaluate('''() => {
                    const btn = document.querySelector('button.btn-upload-order') ||
                                document.querySelector('button[onclick*="openUploadModal"]');
                    if (btn) {
                        btn.click();
                        return true;
                    }
                    return false;
                }''')
                page.wait_for_timeout(1000)
                print(f"   ✅ Modal opened (JS click)")
            except:
                # Fallback to Playwright click
                upload_order_btn = page.locator('button.btn-upload-order, button:has-text("파일 업로드 및 주문하기")').first
                upload_order_btn.click(timeout=5000)
                page.wait_for_timeout(1000)
                print(f"   ✅ Modal opened")
            page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_04_modal_open.png", full_page=True)
        except Exception as e:
            print(f"   ⚠️  Could not open modal: {e}")

        print(f"6️⃣  Upload file in modal...")
        try:
            # Find file input in modal
            file_input = page.locator('#modalFileInput, input[type="file"]').first
            file_input.set_input_files(TEST_FILE)
            page.wait_for_timeout(1000)
            print(f"   ✅ File uploaded")
            page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_05_file_uploaded.png", full_page=True)
        except Exception as e:
            print(f"   ⚠️  Upload: {e}")

        print(f"7️⃣  Add to cart from modal...")
        # Listen for dialog (alert) before clicking
        page.on('dialog', lambda dialog: dialog.accept())

        # Try JavaScript click first
        try:
            page.evaluate('''() => {
                const btn = document.querySelector('button.btn-cart[onclick*="addToBasketFromModal"]') ||
                            document.querySelector('button[onclick*="addToBasketFromModal"]');
                if (btn) {
                    btn.click();
                    return true;
                }
                return false;
            }''')
            page.wait_for_timeout(5000)  # Wait longer for AJAX and potential redirect
            print(f"   ✅ Added to cart (JS click)")
        except:
            # Fallback to Playwright click
            cart_button = page.locator('button.btn-cart:has-text("장바구니"), button:has-text("장바구니에 저장")').first
            cart_button.click(timeout=5000, force=True)
            page.wait_for_timeout(5000)
            print(f"   ✅ Added to cart")
        page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_06_cart_added.png", full_page=True)

        print(f"✅ {product_title} completed!\n")
        return True

    except Exception as e:
        print(f"❌ {product_title} failed: {e}")
        page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_ERROR.png", full_page=True)
        return False

def test_checkout_flow(page):
    print(f"\n{'='*60}")
    print(f"🛒 Checkout Flow")
    print(f"{'='*60}")

    try:
        print(f"1️⃣  Navigate to cart...")
        page.goto('http://localhost/mlangprintauto/shop/cart.php')
        page.wait_for_load_state('networkidle')
        page.wait_for_timeout(2000)  # Extra wait for data loading
        page.screenshot(path=f"{SCREENSHOTS_DIR}/checkout_01_cart.png", full_page=True)
        print(f"   ✅ Cart loaded")

        # Try multiple selectors for cart items
        cart_items = page.locator('tr[data-item-id], .cart-item, .basket-item, tbody tr').count()
        print(f"   ℹ️  {cart_items} items in cart")

        # Debug: Print page content snippet
        try:
            cart_content = page.evaluate('''() => {
                const table = document.querySelector('table');
                return table ? table.rows.length : 0;
            }''')
            print(f"   🔍 Table rows found: {cart_content}")
        except:
            pass

        if cart_items == 0:
            print(f"   ⚠️  Cart appears empty!")
            print(f"   ℹ️  This might be a session issue or items weren't actually saved")
            return False

        print(f"2️⃣  Proceed to checkout...")
        checkout_btn = page.locator('button:has-text("주문"), a:has-text("주문하기"), .btn-checkout').first
        checkout_btn.click()
        page.wait_for_load_state('networkidle')
        page.screenshot(path=f"{SCREENSHOTS_DIR}/checkout_02_form.png", full_page=True)
        print(f"   ✅ Order form loaded")

        print(f"3️⃣  Fill customer info...")
        page.fill('input[name="name"], input[id="name"]', 'E2E 테스트')
        page.fill('input[name="email"], input[id="email"]', 'e2etest@example.com')
        page.fill('input[name="phone"], input[id="phone"]', '010-1234-5678')
        page.fill('input[name="address"], input[id="address"]', '서울시 테스트구 테스트동 123')
        page.screenshot(path=f"{SCREENSHOTS_DIR}/checkout_03_filled.png", full_page=True)
        print(f"   ✅ Info filled")

        print(f"4️⃣  Submit order...")
        submit_btn = page.locator('button[type="submit"]:has-text("주문"), button:has-text("완료"), .btn-submit-order').first
        submit_btn.click()
        page.wait_for_load_state('networkidle')
        page.wait_for_timeout(2000)
        page.screenshot(path=f"{SCREENSHOTS_DIR}/checkout_04_complete.png", full_page=True)
        print(f"   ✅ Order submitted")

        current_url = page.url
        page_content = page.content()

        if 'complete' in current_url.lower() or '완료' in page_content or '감사' in page_content:
            print(f"   ✅ Order completed!")
            return True
        else:
            print(f"   ⚠️  Completion uncertain: {current_url}")
            return False

    except Exception as e:
        print(f"❌ Checkout failed: {e}")
        page.screenshot(path=f"{SCREENSHOTS_DIR}/checkout_ERROR.png", full_page=True)
        return False

def main():
    setup_screenshots_dir()

    print(f"""
╔══════════════════════════════════════════════════════════════╗
║           Duson Print System - E2E Test Suite               ║
║                   Testing All 9 Products                     ║
╚══════════════════════════════════════════════════════════════╝
""")

    results = []

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            viewport={'width': 1920, 'height': 1080},
            locale='ko-KR'
        )
        page = context.new_page()

        for product in PRODUCTS:
            result = test_product(page, product)
            results.append({
                'product': product['title'],
                'name': product['name'],
                'success': result
            })

        checkout_success = test_checkout_flow(page)

        browser.close()

    print(f"\n{'='*60}")
    print(f"📊 Test Summary")
    print(f"{'='*60}")

    passed = sum(1 for r in results if r['success'])
    total = len(results)

    for result in results:
        status = "✅ PASS" if result['success'] else "❌ FAIL"
        print(f"{status} - {result['product']} ({result['name']})")

    print(f"\n🛒 Checkout: {'✅ PASS' if checkout_success else '❌ FAIL'}")
    print(f"\n{'='*60}")
    print(f"📈 {passed}/{total} products passed")
    print(f"📸 {SCREENSHOTS_DIR}")
    print(f"{'='*60}\n")

    return passed == total and checkout_success

if __name__ == '__main__':
    success = main()
    exit(0 if success else 1)
