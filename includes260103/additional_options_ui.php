<?php
/**
 * 추가 옵션 UI 공통 컴포넌트
 * 모든 제품 페이지에서 재사용 가능한 추가 옵션 UI 생성
 *
 * @author SuperClaude
 * @version 2.0
 */

/**
 * 추가 옵션 HTML 생성 함수
 *
 * @param string $product_type 제품 타입 (inserted, envelope, namecard 등)
 * @return string HTML 코드
 */
function generateAdditionalOptionsUI($product_type = 'default') {
    // 제품별 옵션 설정
    $options_config = getOptionsConfigByProduct($product_type);

    ob_start();
    ?>
    <!-- 추가 옵션 섹션 -->
    <div class="additional-options-section" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
        <h4 style="margin-bottom: 15px; color: #333;">📎 추가 옵션</h4>

        <?php if ($options_config['coating']): ?>
        <!-- 코팅 옵션 -->
        <div class="option-group" style="margin-bottom: 15px;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" id="coating-toggle" class="option-toggle" name="coating_enabled" style="margin-right: 10px;">
                <strong>코팅</strong>
            </label>
            <div class="option-details" id="coating-options" style="display: none; margin-top: 10px; padding-left: 25px;">
                <select id="coating-type" name="coating_type" style="width: 100%; padding: 8px;">
                    <option value="">코팅 종류 선택</option>
                    <option value="single">단면유광</option>
                    <option value="double">양면유광</option>
                    <option value="single_matte">단면무광</option>
                    <option value="double_matte">양면무광</option>
                </select>
                <div class="option-price" style="margin-top: 5px; color: #28a745;">
                    <span id="coating-price">0</span>원
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($options_config['folding']): ?>
        <!-- 접지 옵션 -->
        <div class="option-group" style="margin-bottom: 15px;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" id="folding-toggle" class="option-toggle" name="folding_enabled" style="margin-right: 10px;">
                <strong>접지</strong>
            </label>
            <div class="option-details" id="folding-options" style="display: none; margin-top: 10px; padding-left: 25px;">
                <select id="folding-type" name="folding_type" style="width: 100%; padding: 8px;">
                    <option value="">접지 종류 선택</option>
                    <option value="2fold">2단</option>
                    <option value="3fold">3단</option>
                    <option value="accordion">병풍</option>
                    <option value="gate">대문</option>
                </select>
                <div class="option-price" style="margin-top: 5px; color: #28a745;">
                    <span id="folding-price">0</span>원
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($options_config['creasing']): ?>
        <!-- 오시 옵션 -->
        <div class="option-group" style="margin-bottom: 15px;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" id="creasing-toggle" class="option-toggle" name="creasing_enabled" style="margin-right: 10px;">
                <strong>오시</strong>
            </label>
            <div class="option-details" id="creasing-options" style="display: none; margin-top: 10px; padding-left: 25px;">
                <select id="creasing-lines" name="creasing_lines" style="width: 100%; padding: 8px;">
                    <option value="">오시 줄 수 선택</option>
                    <option value="1">1줄</option>
                    <option value="2">2줄</option>
                    <option value="3">3줄</option>
                </select>
                <div class="option-price" style="margin-top: 5px; color: #28a745;">
                    <span id="creasing-price">0</span>원
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($options_config['tape'] && $product_type == 'envelope'): ?>
        <!-- 봉투 전용: 양면테이프 옵션 -->
        <div class="option-group" style="margin-bottom: 15px;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" id="tape-toggle" class="option-toggle" name="tape_enabled" style="margin-right: 10px;">
                <strong>양면테이프</strong>
            </label>
            <div class="option-details" id="tape-options" style="display: none; margin-top: 10px; padding-left: 25px;">
                <div class="option-price" style="margin-top: 5px; color: #28a745;">
                    <span id="tape-price">15000</span>원 (고정 가격)
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 추가 옵션 총액 -->
        <div style="border-top: 2px solid #dee2e6; margin-top: 15px; padding-top: 15px;">
            <strong>추가 옵션 총액: </strong>
            <span id="additional-options-total" style="color: #dc3545; font-weight: bold;">0</span>원
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * 제품별 옵션 설정 반환
 *
 * @param string $product_type 제품 타입
 * @return array 옵션 설정
 */
function getOptionsConfigByProduct($product_type) {
    $configs = [
        'inserted' => [
            'coating' => true,
            'folding' => true,
            'creasing' => true,
            'tape' => false
        ],
        'envelope' => [
            'coating' => true,
            'folding' => false,
            'creasing' => true,
            'tape' => true
        ],
        'namecard' => [
            'coating' => true,
            'folding' => false,
            'creasing' => false,
            'tape' => false
        ],
        'sticker' => [
            'coating' => true,
            'folding' => false,
            'creasing' => false,
            'tape' => false
        ],
        'poster' => [
            'coating' => true,
            'folding' => true,
            'creasing' => false,
            'tape' => false
        ],
        'littleprint' => [
            'coating' => true,
            'folding' => true,
            'creasing' => true,
            'tape' => false
        ],
        'cadarok' => [
            'coating' => true,
            'folding' => true,
            'creasing' => true,
            'tape' => false
        ],
        'merchandisebond' => [
            'coating' => false,
            'folding' => false,
            'creasing' => true,
            'tape' => false
        ],
        'msticker' => [
            'coating' => false,
            'folding' => false,
            'creasing' => false,
            'tape' => false
        ],
        'ncrflambeau' => [
            'coating' => false,
            'folding' => false,
            'creasing' => true,
            'tape' => false
        ],
        'default' => [
            'coating' => true,
            'folding' => true,
            'creasing' => true,
            'tape' => false
        ]
    ];

    return $configs[$product_type] ?? $configs['default'];
}

/**
 * 추가 옵션 JavaScript 코드 생성
 *
 * @return string JavaScript 코드
 */
function generateAdditionalOptionsJS() {
    ?>
    <script>
    // 추가 옵션 관리자
    class AdditionalOptionsHandler {
        constructor() {
            this.init();
        }

        init() {
            // 추가 옵션 토글 기능
            const toggles = document.querySelectorAll('.option-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('change', (e) => {
                    this.handleToggleChange(e.target);
                });
            });

            // 옵션 선택 변경 이벤트
            const selects = document.querySelectorAll('.option-details select');
            selects.forEach(select => {
                select.addEventListener('change', () => {
                    this.updateOptionPrice(select);
                    this.updateAdditionalOptionsTotal();
                });
            });
        }

        handleToggleChange(toggle) {
            const optionId = toggle.id.replace('-toggle', '-options');
            const optionDiv = document.getElementById(optionId);

            if (optionDiv) {
                optionDiv.style.display = toggle.checked ? 'block' : 'none';

                // 체크 해제 시 관련 선택 초기화
                if (!toggle.checked) {
                    const select = optionDiv.querySelector('select');
                    if (select) {
                        select.value = '';
                    }
                    const priceSpan = optionDiv.querySelector('.option-price span');
                    if (priceSpan) {
                        priceSpan.textContent = '0';
                    }
                }
            }

            this.updateAdditionalOptionsTotal();
        }

        updateOptionPrice(selectElement) {
            const optionGroup = selectElement.closest('.option-group');
            const priceSpan = optionGroup.querySelector('.option-price span');

            if (!priceSpan) return;

            // 가격 매핑 (실제로는 서버에서 가져와야 함)
            const prices = {
                // 코팅 가격
                'single': 30000,
                'double': 50000,
                'single_matte': 35000,
                'double_matte': 55000,
                // 접지 가격
                '2fold': 20000,
                '3fold': 25000,
                'accordion': 30000,
                'gate': 35000,
                // 오시 가격
                '1': 15000,
                '2': 25000,
                '3': 35000
            };

            const price = prices[selectElement.value] || 0;
            priceSpan.textContent = price.toLocaleString();
        }

        updateAdditionalOptionsTotal() {
            let total = 0;

            document.querySelectorAll('.option-price span').forEach(span => {
                const price = parseInt(span.textContent.replace(/[^0-9]/g, '')) || 0;
                total += price;
            });

            const totalSpan = document.getElementById('additional-options-total');
            if (totalSpan) {
                totalSpan.textContent = total.toLocaleString();
            }

            // 전역 변수 업데이트 (다른 시스템과 연동)
            if (typeof window.additionalOptionsTotal !== 'undefined') {
                window.additionalOptionsTotal = total;
            }
        }

        // 장바구니 추가 시 옵션 데이터 수집
        collectOptionsData() {
            const data = {};

            // 코팅
            const coatingToggle = document.getElementById('coating-toggle');
            if (coatingToggle) {
                data.coating_enabled = coatingToggle.checked ? '1' : '0';
                if (coatingToggle.checked) {
                    data.coating_type = document.getElementById('coating-type')?.value || '';
                    data.coating_price = document.getElementById('coating-price')?.textContent.replace(/[^0-9]/g, '') || '0';
                }
            }

            // 접지
            const foldingToggle = document.getElementById('folding-toggle');
            if (foldingToggle) {
                data.folding_enabled = foldingToggle.checked ? '1' : '0';
                if (foldingToggle.checked) {
                    data.folding_type = document.getElementById('folding-type')?.value || '';
                    data.folding_price = document.getElementById('folding-price')?.textContent.replace(/[^0-9]/g, '') || '0';
                }
            }

            // 오시
            const creasingToggle = document.getElementById('creasing-toggle');
            if (creasingToggle) {
                data.creasing_enabled = creasingToggle.checked ? '1' : '0';
                if (creasingToggle.checked) {
                    data.creasing_lines = document.getElementById('creasing-lines')?.value || '';
                    data.creasing_price = document.getElementById('creasing-price')?.textContent.replace(/[^0-9]/g, '') || '0';
                }
            }

            // 양면테이프 (봉투용)
            const tapeToggle = document.getElementById('tape-toggle');
            if (tapeToggle) {
                data.tape_enabled = tapeToggle.checked ? '1' : '0';
                if (tapeToggle.checked) {
                    data.tape_price = '15000';
                }
            }

            // 총액
            data.additional_options_total = document.getElementById('additional-options-total')?.textContent.replace(/[^0-9]/g, '') || '0';

            return data;
        }
    }

    // 전역으로 사용할 수 있도록 설정
    window.additionalOptionsHandler = null;

    // DOM 로드 후 초기화
    document.addEventListener('DOMContentLoaded', function() {
        window.additionalOptionsHandler = new AdditionalOptionsHandler();
        console.log('✅ 추가 옵션 시스템 초기화 완료');
    });
    </script>
    <?php
}
?>