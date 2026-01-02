"""
견적서 계산기 시스템 종합 E2E 테스트
Tests all 9 products for specification display and price accuracy
"""

from playwright.sync_api import sync_playwright, expect
import time
import json

# Test configuration
BASE_URL = "http://localhost/mlangprintauto/quote/create.php"
PRODUCTS = [
    "전단지",
    "명함",
    "봉투",
    "스티커",
    "자석스티커",
    "카다록",
    "포스터",
    "상품권",
    "NCR양식"
]

def test_quote_calculator_system():
    """Comprehensive test of all 9 quote calculator products"""

    results = {
        "total_products": len(PRODUCTS),
        "passed": 0,
        "failed": 0,
        "details": []
    }

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        print(f"\n{'='*80}")
        print(f"견적서 계산기 시스템 종합 테스트 시작")
        print(f"{'='*80}\n")

        for product in PRODUCTS:
            print(f"\n[테스트 중] {product}...")
            product_result = test_single_product(page, product)
            results["details"].append(product_result)

            if product_result["status"] == "PASS":
                results["passed"] += 1
                print(f"✅ {product}: 통과")
            else:
                results["failed"] += 1
                print(f"❌ {product}: 실패 - {product_result['error']}")

        browser.close()

    # Print summary
    print(f"\n{'='*80}")
    print(f"테스트 결과 요약")
    print(f"{'='*80}")
    print(f"총 제품: {results['total_products']}")
    print(f"✅ 통과: {results['passed']}")
    print(f"❌ 실패: {results['failed']}")
    print(f"{'='*80}\n")

    # Print detailed results
    print("\n상세 결과:\n")
    for detail in results["details"]:
        status_icon = "✅" if detail["status"] == "PASS" else "❌"
        print(f"{status_icon} {detail['product']}")

        if detail["status"] == "PASS":
            print(f"   규격: {detail['specification'][:50]}..." if len(detail['specification']) > 50 else f"   규격: {detail['specification']}")
            print(f"   수량: {detail['quantity']} {detail['unit']}")
            print(f"   공급가: {detail['supply_price']:,}원")
            print(f"   부가세: {detail['vat']:,}원")
            print(f"   총액: {detail['total']:,}원")
        else:
            print(f"   오류: {detail['error']}")
        print()

    # Save results to file
    with open('/var/www/html/claudedocs/quote_test_results.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

    print(f"결과가 /var/www/html/claudedocs/quote_test_results.json에 저장되었습니다.\n")

    return results

def test_single_product(page, product_name):
    """Test a single product through the calculator modal"""

    result = {
        "product": product_name,
        "status": "FAIL",
        "error": None,
        "specification": None,
        "quantity": None,
        "unit": None,
        "supply_price": None,
        "vat": None,
        "total": None,
        "screenshot": None
    }

    try:
        # Navigate to create page
        page.goto(BASE_URL)
        page.wait_for_load_state('networkidle')
        time.sleep(1)

        # Find and click "품목 추가" button
        add_button = page.locator('button:has-text("품목 추가")')
        if add_button.count() == 0:
            result["error"] = "품목 추가 버튼을 찾을 수 없음"
            return result

        add_button.click()
        time.sleep(0.5)

        # Find the newly added row (last row)
        rows = page.locator('tbody tr')
        last_row = rows.last

        # Select the product from dropdown
        product_select = last_row.locator('.product-select')
        product_select.select_option(product_name)
        time.sleep(1)

        # Check if calculator modal opened
        modal = page.locator('#calculatorModal')
        if not modal.is_visible():
            result["error"] = "계산기 모달이 열리지 않음"
            return result

        # Wait for iframe to load
        iframe_element = page.frame_locator('#calculatorIframe')
        time.sleep(2)

        # Try to find and click the "견적서에 적용" button in the iframe
        # Different products may have different button structures
        apply_button_selectors = [
            'button:has-text("견적서에 적용")',
            'button:has-text("✅ 견적서에 적용")',
            'input[value*="견적서에 적용"]',
            'button.apply-to-quote',
            '#applyToQuote'
        ]

        button_found = False
        for selector in apply_button_selectors:
            try:
                button = iframe_element.locator(selector).first
                if button.count() > 0:
                    button.click()
                    button_found = True
                    break
            except:
                continue

        if not button_found:
            # Some calculators might auto-submit or have different flow
            # Wait a bit and check if modal closed
            time.sleep(2)

        # Wait for modal to close
        time.sleep(2)

        # Check if modal is closed
        if modal.is_visible():
            # Try clicking close button
            close_button = page.locator('#calcModalClose')
            if close_button.count() > 0:
                close_button.click()
                time.sleep(1)

        # Debug: Check console logs
        console_logs = []
        page.on('console', lambda msg: console_logs.append(f"{msg.type}: {msg.text}"))

        # Debug: Take screenshot before extraction
        debug_screenshot = f'/tmp/debug_{product_name}_before_extract.png'
        page.screenshot(path=debug_screenshot, full_page=True)
        print(f"  디버그 스크린샷: {debug_screenshot}")

        # Extract data from the row
        spec_display = last_row.locator('.spec-display').first
        qty_input = last_row.locator('.qty-input').first
        unit_input = last_row.locator('input[name*="[unit]"]').first
        supply_input = last_row.locator('.supply-input').first
        vat_cell = last_row.locator('.vat-cell').first
        total_cell = last_row.locator('.total-cell').first

        # Get values
        specification = spec_display.text_content().strip()
        quantity = qty_input.input_value()
        unit = unit_input.input_value()
        supply_price_str = supply_input.input_value()
        vat_str = vat_cell.text_content().strip()
        total_str = total_cell.text_content().strip()

        # Validate data
        if not specification:
            result["error"] = "규격 데이터가 비어있음"
            return result

        if not quantity:
            result["error"] = "수량 데이터가 비어있음"
            return result

        if not supply_price_str:
            result["error"] = "공급가 데이터가 비어있음"
            return result

        # Parse numbers
        try:
            supply_price = int(supply_price_str)
            vat = int(vat_str.replace(',', '')) if vat_str else 0
            total = int(total_str.replace(',', '')) if total_str else 0
        except ValueError as e:
            result["error"] = f"숫자 파싱 실패: {e}"
            return result

        # Validate price logic
        if supply_price <= 0:
            result["error"] = f"공급가가 0 이하: {supply_price}"
            return result

        if total <= supply_price:
            result["error"] = f"총액({total})이 공급가({supply_price})보다 작거나 같음"
            return result

        if vat != (total - supply_price):
            result["error"] = f"부가세 계산 오류: 부가세({vat}) != 총액({total}) - 공급가({supply_price})"
            return result

        # Check for newlines in specification (multi-line test)
        if '\n' not in specification and len(specification) < 20:
            result["error"] = f"규격에 줄바꿈이 없음 (단일 라인): {specification}"
            return result

        # Take screenshot
        screenshot_path = f'/tmp/quote_test_{product_name}.png'
        page.screenshot(path=screenshot_path, full_page=True)

        # All validations passed
        result["status"] = "PASS"
        result["specification"] = specification
        result["quantity"] = quantity
        result["unit"] = unit
        result["supply_price"] = supply_price
        result["vat"] = vat
        result["total"] = total
        result["screenshot"] = screenshot_path

    except Exception as e:
        result["error"] = f"예외 발생: {str(e)}"

    return result

if __name__ == "__main__":
    results = test_quote_calculator_system()

    # Exit with error code if any tests failed
    exit(0 if results["failed"] == 0 else 1)
