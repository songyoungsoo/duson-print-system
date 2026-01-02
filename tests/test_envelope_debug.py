"""
봉투 계산기 디버그 테스트
"""

from playwright.sync_api import sync_playwright
import time

BASE_URL = "http://localhost/mlangprintauto/quote/create.php"

def test_envelope():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False)  # 브라우저 표시
        page = browser.new_page()

        print("\n=== 봉투 테스트 시작 ===\n")

        # Console 로그 수집
        console_logs = []
        page.on('console', lambda msg: console_logs.append(f"[{msg.type}] {msg.text}"))

        # 1. 견적서 페이지 열기
        print("1. 견적서 페이지 로딩...")
        page.goto(BASE_URL)
        page.wait_for_load_state('networkidle')
        time.sleep(1)

        # 2. 품목 추가 버튼 클릭
        print("2. 품목 추가 버튼 클릭...")
        add_button = page.locator('button:has-text("품목 추가")')
        add_button.click()
        time.sleep(0.5)

        # 3. 마지막 행에서 봉투 선택
        print("3. 제품 선택: 봉투...")
        rows = page.locator('tbody tr')
        last_row = rows.last
        product_select = last_row.locator('.product-select')
        product_select.select_option('봉투')
        time.sleep(1)

        # 4. 모달 확인
        print("4. 계산기 모달 확인...")
        modal = page.locator('#calculatorModal')
        if modal.is_visible():
            print("   ✓ 모달이 열렸습니다")
        else:
            print("   ✗ 모달이 열리지 않았습니다")
            browser.close()
            return

        # 5. iframe 확인
        print("5. iframe 로딩 대기...")
        time.sleep(3)  # iframe 로딩 대기

        # 6. 스크린샷
        print("6. 스크린샷 저장...")
        page.screenshot(path='/tmp/envelope_modal.png', full_page=True)
        print("   저장: /tmp/envelope_modal.png")

        # 7. iframe 내부 확인
        print("7. iframe 내부 확인...")
        iframe = page.frame_locator('#calculatorIframe')

        # iframe 내부 HTML 일부 확인
        try:
            # "견적서에 적용" 버튼 찾기
            apply_selectors = [
                'button:has-text("견적서에 적용")',
                'button:has-text("✅ 견적서에 적용")',
                'button.btn-quotation-apply',
                'input[value*="견적서"]'
            ]

            button_found = False
            for selector in apply_selectors:
                try:
                    button = iframe.locator(selector).first
                    count = button.count()
                    print(f"   {selector}: {count}개")
                    if count > 0:
                        button_found = True
                        print(f"   ✓ 버튼 발견: {selector}")

                        # 옵션 자동 선택
                        print("8. 옵션 자동 선택 시도...")
                        my_type = iframe.locator('#MY_type')
                        if my_type.count() > 0:
                            my_type.select_option(index=1)
                            print("   ✓ MY_type 선택")
                            time.sleep(0.5)

                        section = iframe.locator('#Section')
                        if section.count() > 0:
                            section.select_option(index=1)
                            print("   ✓ Section 선택")
                            time.sleep(0.5)

                        potype = iframe.locator('#POtype')
                        if potype.count() > 0:
                            potype.select_option(index=1)
                            print("   ✓ POtype 선택")
                            time.sleep(0.5)

                        my_amount = iframe.locator('#MY_amount')
                        if my_amount.count() > 0:
                            my_amount.select_option(index=1)
                            print("   ✓ MY_amount 선택")
                            time.sleep(0.5)

                        ordertype = iframe.locator('#ordertype')
                        if ordertype.count() > 0:
                            ordertype.select_option(index=1)
                            print("   ✓ ordertype 선택")
                            time.sleep(0.5)

                        # 가격 표시 확인
                        price_display = iframe.locator('#priceAmount, .price-amount, [class*="price"]')
                        if price_display.count() > 0:
                            price_text = price_display.first.text_content()
                            print(f"   가격 표시: {price_text}")

                        # "견적서에 적용" 버튼 클릭
                        print("9. '견적서에 적용' 버튼 클릭...")
                        button.click()
                        time.sleep(2)
                        break
                except Exception as e:
                    print(f"   {selector}: 오류 - {e}")

            if not button_found:
                print("   ✗ '견적서에 적용' 버튼을 찾을 수 없습니다")

        except Exception as e:
            print(f"   iframe 접근 오류: {e}")

        # 10. 모달 닫힘 대기
        print("10. 모달 닫힘 대기...")
        time.sleep(2)

        if modal.is_visible():
            print("   모달이 아직 열려있음 - 수동으로 닫기")
            close_button = page.locator('#calcModalClose')
            if close_button.count() > 0:
                close_button.click()
                time.sleep(1)

        # 11. 데이터 추출
        print("11. 견적서 테이블에서 데이터 추출...")
        spec_display = last_row.locator('.spec-display').first
        spec_text = spec_display.text_content().strip()
        print(f"   규격: '{spec_text}'")
        print(f"   규격 길이: {len(spec_text)}")
        print(f"   규격 줄바꿈: {'\\n' in spec_text}")

        qty_input = last_row.locator('.qty-input').first
        qty_value = qty_input.input_value()
        print(f"   수량: '{qty_value}'")

        supply_input = last_row.locator('.supply-input').first
        supply_value = supply_input.input_value()
        print(f"   공급가: '{supply_value}'")

        vat_cell = last_row.locator('.vat-cell').first
        vat_text = vat_cell.text_content().strip()
        print(f"   부가세: '{vat_text}'")

        total_cell = last_row.locator('.total-cell').first
        total_text = total_cell.text_content().strip()
        print(f"   총액: '{total_text}'")

        # 12. 최종 스크린샷
        print("12. 최종 스크린샷...")
        page.screenshot(path='/tmp/envelope_result.png', full_page=True)
        print("   저장: /tmp/envelope_result.png")

        # 13. Console 로그 출력
        print("\n=== Console 로그 ===")
        for log in console_logs[-20:]:  # 마지막 20개만
            print(f"  {log}")

        print("\n=== 테스트 완료 ===")
        print("브라우저를 10초간 열어둡니다...")
        time.sleep(10)

        browser.close()

if __name__ == "__main__":
    test_envelope()
