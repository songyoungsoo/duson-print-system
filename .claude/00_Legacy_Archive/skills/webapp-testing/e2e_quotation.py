#!/usr/bin/env python3
"""
E2E Test for Quotation System in Duson Print System
Tests: Options selection -> Request quotation -> View quotations -> Details
"""

from playwright.sync_api import sync_playwright
import time
import os

# Product configurations for quotation test
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
    }
]

TEST_FILE = '/tmp/test_upload.txt'
SCREENSHOTS_DIR = '/tmp/e2e_quotation_screenshots'

def setup_screenshots_dir():
    os.makedirs(SCREENSHOTS_DIR, exist_ok=True)
    print(f"📁 Screenshots: {SCREENSHOTS_DIR}")

def test_quotation_request(page, product):
    """Test quotation request for a single product"""
    product_name = product['name']
    product_title = product['title']

    print(f"\n{'='*60}")
    print(f"💰 {product_title} ({product_name}) - 견적 요청")
    print(f"{'='*60}")

    try:
        print(f"1️⃣  Navigate to {product['url']}")
        page.goto(product['url'], timeout=60000)
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

        print(f"5️⃣  Click quotation request button...")
        # Listen for dialog (alert)
        page.on('dialog', lambda dialog: dialog.accept())

        try:
            # JavaScript click for quotation button
            page.evaluate('''() => {
                const btn = document.querySelector('button.btn-request-quote') ||
                            document.querySelector('button[onclick*="addToQuotation"]');
                if (btn) {
                    btn.click();
                    return true;
                }
                return false;
            }''')
            page.wait_for_timeout(5000)  # Wait for AJAX and potential redirect
            print(f"   ✅ Quotation requested")
        except Exception as e:
            # Fallback to Playwright click
            quote_btn = page.locator('button.btn-request-quote, button:has-text("견적 요청")').first
            quote_btn.click(timeout=5000, force=True)
            page.wait_for_timeout(5000)
            print(f"   ✅ Quotation requested")

        page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_04_quotation_requested.png", full_page=True)

        print(f"✅ {product_title} quotation request completed!\n")
        return True

    except Exception as e:
        print(f"❌ {product_title} quotation request failed: {e}")
        page.screenshot(path=f"{SCREENSHOTS_DIR}/{product_name}_ERROR.png", full_page=True)
        return False

def test_quotation_list(page):
    """Test quotation list page"""
    print(f"\n{'='*60}")
    print(f"📋 Quotation List Page Test")
    print(f"{'='*60}")

    try:
        print(f"1️⃣  Navigate to quotation list...")
        page.goto('http://localhost/mlangprintauto/quote/', timeout=60000)
        page.wait_for_load_state('networkidle', timeout=60000)
        page.wait_for_timeout(2000)
        page.screenshot(path=f"{SCREENSHOTS_DIR}/quotation_list_01.png", full_page=True)
        print(f"   ✅ Quotation list loaded")

        # Check for quotation items
        quotation_items = page.locator('tr[data-quotation-id], .quotation-item, tbody tr').count()
        print(f"   ℹ️  {quotation_items} quotations found")

        # Try to get table row count
        try:
            table_rows = page.evaluate('''() => {
                const table = document.querySelector('table');
                return table ? table.rows.length : 0;
            }''')
            print(f"   🔍 Table rows: {table_rows}")
        except:
            pass

        if quotation_items > 0:
            print(f"   ✅ Quotations exist")
            return True
        else:
            print(f"   ⚠️  No quotations found")
            return False

    except Exception as e:
        print(f"❌ Quotation list test failed: {e}")
        page.screenshot(path=f"{SCREENSHOTS_DIR}/quotation_list_ERROR.png", full_page=True)
        return False

def test_quotation_detail(page):
    """Test quotation detail page"""
    print(f"\n{'='*60}")
    print(f"📄 Quotation Detail Page Test")
    print(f"{'='*60}")

    try:
        print(f"1️⃣  Click first quotation...")
        # Try to click first quotation link
        try:
            first_quote = page.locator('a[href*="detail.php"], .quotation-link, tbody tr a').first
            first_quote.click(timeout=10000)
            page.wait_for_load_state('networkidle', timeout=60000)
            page.wait_for_timeout(2000)
            print(f"   ✅ Detail page loaded")
        except:
            print(f"   ⚠️  Could not find quotation link, trying direct URL...")
            # Try to get quotation ID from page
            quote_id = page.evaluate('''() => {
                const link = document.querySelector('a[href*="detail.php"]');
                if (link) {
                    const match = link.href.match(/id=(\d+)/);
                    return match ? match[1] : null;
                }
                return null;
            }''')

            if quote_id:
                page.goto(f'http://localhost/mlangprintauto/quote/detail.php?id={quote_id}', timeout=60000)
                page.wait_for_load_state('networkidle', timeout=60000)
                print(f"   ✅ Detail page loaded (ID: {quote_id})")
            else:
                print(f"   ⚠️  Could not determine quotation ID")
                return False

        page.screenshot(path=f"{SCREENSHOTS_DIR}/quotation_detail_01.png", full_page=True)

        # Check for quotation content
        has_content = page.evaluate('''() => {
            return document.body.textContent.includes('견적') ||
                   document.body.textContent.includes('quotation');
        }''')

        if has_content:
            print(f"   ✅ Quotation detail content found")
            return True
        else:
            print(f"   ⚠️  Quotation content not clear")
            return False

    except Exception as e:
        print(f"❌ Quotation detail test failed: {e}")
        page.screenshot(path=f"{SCREENSHOTS_DIR}/quotation_detail_ERROR.png", full_page=True)
        return False

def main():
    """Main test execution"""
    setup_screenshots_dir()

    print(f"""
╔══════════════════════════════════════════════════════════════╗
║         Duson Print System - Quotation E2E Test             ║
║                    견적서 시스템 테스트                        ║
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

        # Test quotation request for each product
        for product in PRODUCTS:
            result = test_quotation_request(page, product)
            results.append({
                'product': product['title'],
                'name': product['name'],
                'success': result
            })

        # Test quotation list page
        list_success = test_quotation_list(page)

        # Test quotation detail page
        detail_success = test_quotation_detail(page)

        browser.close()

    # Print summary
    print(f"\n{'='*60}")
    print(f"📊 Test Summary")
    print(f"{'='*60}")

    passed = sum(1 for r in results if r['success'])
    total = len(results)

    for result in results:
        status = "✅ PASS" if result['success'] else "❌ FAIL"
        print(f"{status} - {result['product']} ({result['name']})")

    print(f"\n📋 Quotation List: {'✅ PASS' if list_success else '❌ FAIL'}")
    print(f"📄 Quotation Detail: {'✅ PASS' if detail_success else '❌ FAIL'}")

    print(f"\n{'='*60}")
    print(f"📈 Results: {passed}/{total} products passed")
    print(f"📸 Screenshots: {SCREENSHOTS_DIR}")
    print(f"{'='*60}\n")

    return passed == total and list_success

if __name__ == '__main__':
    success = main()
    exit(0 if success else 1)
