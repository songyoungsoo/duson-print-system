<?php
/**
 * 추가 옵션 시스템 - 재사용 가능한 모듈형 클래스
 * 
 * 목적: 코팅, 접지, 오시 등 추가 옵션의 통합 관리
 * 특징: 다른 제품 페이지에서도 재사용 가능한 모듈형 구조
 * 
 * @version 1.0
 * @date 2025-01-08
 * @author SuperClaude Architecture System
 */

// db.php 포함하여 safe_mysqli_query 함수 사용
require_once dirname(__FILE__) . '/../db.php';

class AdditionalOptions {
    private $db;
    private $options_config = [];
    
    // 기본 옵션 가격 (1연 기준)
    private $BASE_PRICES = [
        'coating' => [
            'single' => 80000,        // 단면유광코팅
            'double' => 160000,       // 양면유광코팅
            'single_matte' => 90000,  // 단면무광코팅 (80,000 → 90,000)
            'double_matte' => 180000  // 양면무광코팅 (160,000 → 180,000)
        ],
        'folding' => [
            '2fold' => 40000,     // 2단접지
            '3fold' => 40000,     // 3단접지
            'accordion' => 60000, // 병풍접지
            'gate' => 100000      // 대문접지
        ],
        'creasing' => [
            '1line' => 40000,  // 1줄 (32,000 → 40,000)
            '2line' => 40000,  // 2줄 (32,000 → 40,000)
            '3line' => 45000   // 3줄 (40,000 → 45,000)
        ]
    ];
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
        $this->loadOptionsConfig();
    }
    
    /**
     * 데이터베이스에서 옵션 설정 로드
     */
    private function loadOptionsConfig() {
        try {
            $query = "SELECT * FROM additional_options_config WHERE is_active = 1 ORDER BY option_category, sort_order";
            $result = safe_mysqli_query($this->db, $query);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $this->options_config[$row['option_category']][] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("AdditionalOptions 설정 로드 오류: " . $e->getMessage());
        }
    }
    
    /**
     * 수량(연수)을 기준으로 배수 계산
     * 
     * @param int $quantity 수량
     * @return float 연수 배수 (0.5연 이하는 1연으로 계산)
     */
    public function calculateQuantityMultiplier($quantity) {
        if (empty($quantity) || $quantity <= 0) {
            return 1.0;
        }
        
        // 전단지 수량을 연수로 변환 (예: 1000매 = 1연 기준)
        $yeon = $quantity / 1000;
        
        // 0.5연 이하는 1연 가격 적용
        if ($yeon <= 0.5) {
            return 1.0;
        }
        
        return $yeon;
    }
    
    /**
     * 개별 옵션 가격 계산
     * 
     * @param string $category 옵션 카테고리 (coating/folding/creasing)
     * @param string $type 옵션 타입
     * @param int $quantity 수량
     * @return int 계산된 가격
     */
    public function calculateOptionPrice($category, $type, $quantity = 1000) {
        // 기본 가격 확인
        if (!isset($this->BASE_PRICES[$category][$type])) {
            return 0;
        }
        
        $base_price = $this->BASE_PRICES[$category][$type];
        $multiplier = $this->calculateQuantityMultiplier($quantity);
        
        return intval($base_price * $multiplier);
    }
    
    /**
     * 모든 선택된 옵션의 총 가격 계산
     * 
     * @param array $selected_options 선택된 옵션 배열
     * @param int $quantity 수량
     * @return array 계산 결과 배열
     */
    public function calculateTotalPrice($selected_options, $quantity = 1000) {
        $total = 0;
        $details = [];
        $multiplier = $this->calculateQuantityMultiplier($quantity);
        
        // 코팅 옵션
        if (!empty($selected_options['coating_enabled']) && !empty($selected_options['coating_type'])) {
            $coating_price = $this->calculateOptionPrice('coating', $selected_options['coating_type'], $quantity);
            $details['coating'] = [
                'type' => $selected_options['coating_type'],
                'price' => $coating_price,
                'name' => $this->getOptionName('coating', $selected_options['coating_type'])
            ];
            $total += $coating_price;
        }
        
        // 접지 옵션
        if (!empty($selected_options['folding_enabled']) && !empty($selected_options['folding_type'])) {
            $folding_price = $this->calculateOptionPrice('folding', $selected_options['folding_type'], $quantity);
            $details['folding'] = [
                'type' => $selected_options['folding_type'],
                'price' => $folding_price,
                'name' => $this->getOptionName('folding', $selected_options['folding_type'])
            ];
            $total += $folding_price;
        }
        
        // 오시 옵션
        if (!empty($selected_options['creasing_enabled']) && !empty($selected_options['creasing_lines'])) {
            $creasing_type = $selected_options['creasing_lines'] . 'line';
            $creasing_price = $this->calculateOptionPrice('creasing', $creasing_type, $quantity);
            $details['creasing'] = [
                'lines' => $selected_options['creasing_lines'],
                'price' => $creasing_price,
                'name' => $this->getOptionName('creasing', $creasing_type)
            ];
            $total += $creasing_price;
        }
        
        return [
            'total' => $total,
            'details' => $details,
            'multiplier' => $multiplier,
            'quantity' => $quantity
        ];
    }
    
    /**
     * 옵션 이름 가져오기
     */
    private function getOptionName($category, $type) {
        $names = [
            'coating' => [
                'single' => '단면유광코팅',
                'double' => '양면유광코팅',
                'single_matte' => '단면무광코팅',
                'double_matte' => '양면무광코팅'
            ],
            'folding' => [
                '2fold' => '2단접지',
                '3fold' => '3단접지',
                'accordion' => '병풍접지',
                'gate' => '대문접지'
            ],
            'creasing' => [
                '1line' => '1줄 오시',
                '2line' => '2줄 오시',
                '3line' => '3줄 오시'
            ]
        ];
        
        return $names[$category][$type] ?? $type;
    }
    
    /**
     * 봉투 전용 추가 옵션 HTML 생성
     * 
     * @return string HTML 코드
     */
    public function generateEnvelopeOptionsHtml() {
        $html = '
        <div class="additional-options-section envelope-specific" id="additionalOptionsSection">
            <!-- 봉투 전용 옵션 헤더 -->
            <div class="option-headers-row">
                <div class="option-checkbox-group">
                    <input type="checkbox" id="coating_enabled" name="coating_enabled" class="option-toggle" value="1">
                    <label for="coating_enabled" class="toggle-label">코팅</label>
                </div>
                <div class="option-checkbox-group">
                    <input type="checkbox" id="printing_enabled" name="printing_enabled" class="option-toggle" value="1">
                    <label for="printing_enabled" class="toggle-label">인쇄</label>
                </div>
                <div class="option-price-display">
                    <span class="option-price-total" id="optionPriceTotal">(+0원)</span>
                </div>
            </div>
            
            <!-- 코팅 옵션 상세 -->
            <div class="option-details" id="coating_options" style="display: none;">
                <select name="coating_type" id="coating_type" class="option-select">
                    <option value="single">단면유광코팅 - 80,000원</option>
                    <option value="double">양면유광코팅 - 160,000원</option>
                    <option value="single_matte">단면무광코팅 - 90,000원</option>
                    <option value="double_matte">양면무광코팅 - 180,000원</option>
                </select>
            </div>
            
            <!-- 인쇄 옵션 상세 -->
            <div class="option-details" id="printing_options" style="display: none;">
                <select name="printing_type" id="printing_type" class="option-select">
                    <option value="single_color">단색인쇄 - 30,000원</option>
                    <option value="multi_color">컬러인쇄 - 50,000원</option>
                    <option value="spot_color">별색인쇄 - 80,000원</option>
                </select>
            </div>
            
            <!-- 숨겨진 필드들 -->
            <input type="hidden" name="coating_price" id="coating_price" value="0">
            <input type="hidden" name="printing_price" id="printing_price" value="0">
            <input type="hidden" name="additional_options_total" id="additional_options_total" value="0">
        </div>';
        
        return $html;
    }

    /**
     * HTML 폼 생성
     * 
     * @param string $product_type 제품 타입 (inserted, namecard 등)
     * @return string HTML 코드
     */
    public function generateOptionsHtml($product_type = 'inserted') {
        $html = '
        <div class="additional-options-section" id="additionalOptionsSection">
            <!-- 한 줄 체크박스 헤더 -->
            <div class="option-headers-row">
                <div class="option-checkbox-group">
                    <input type="checkbox" id="coating_enabled" name="coating_enabled" class="option-toggle" value="1">
                    <label for="coating_enabled" class="toggle-label">코팅</label>
                </div>
                <div class="option-checkbox-group">
                    <input type="checkbox" id="folding_enabled" name="folding_enabled" class="option-toggle" value="1">
                    <label for="folding_enabled" class="toggle-label">접지</label>
                </div>
                <div class="option-checkbox-group">
                    <input type="checkbox" id="creasing_enabled" name="creasing_enabled" class="option-toggle" value="1">
                    <label for="creasing_enabled" class="toggle-label">오시</label>
                </div>
                <div class="option-price-display">
                    <span class="option-price-total" id="optionPriceTotal">(+0원)</span>
                </div>
            </div>
            
            <!-- 코팅 옵션 상세 -->
            <div class="option-details" id="coating_options" style="display: none;">
                <select name="coating_type" id="coating_type" class="option-select">
                    <option value="single">단면유광코팅 - 80,000원</option>
                    <option value="double">양면유광코팅 - 160,000원</option>
                    <option value="single_matte">단면무광코팅 - 90,000원</option>
                    <option value="double_matte">양면무광코팅 - 180,000원</option>
                </select>
            </div>
            
            <!-- 접지 옵션 상세 -->
            <div class="option-details" id="folding_options" style="display: none;">
                <select name="folding_type" id="folding_type" class="option-select">
                    <option value="2fold">2단접지 - 40,000원</option>
                    <option value="3fold">3단접지 - 40,000원</option>
                    <option value="accordion">병풍접지 - 60,000원</option>
                    <option value="gate">대문접지 - 100,000원</option>
                </select>
            </div>
            
            <!-- 오시 옵션 상세 -->
            <div class="option-details" id="creasing_options" style="display: none;">
                <select name="creasing_lines" id="creasing_lines" class="option-select">
                    <option value="1">1줄 - 40,000원</option>
                    <option value="2">2줄 - 40,000원</option>
                    <option value="3">3줄 - 45,000원</option>
                </select>
            </div>
            
            <!-- 숨겨진 필드들 -->
            <input type="hidden" name="coating_price" id="coating_price" value="0">
            <input type="hidden" name="folding_price" id="folding_price" value="0">
            <input type="hidden" name="creasing_price" id="creasing_price" value="0">
            <input type="hidden" name="additional_options_total" id="additional_options_total" value="0">
        </div>';
        
        return $html;
    }
    
    /**
     * AJAX 응답용 옵션 정보 반환
     */
    public function getOptionsForAjax() {
        return [
            'base_prices' => $this->BASE_PRICES,
            'config' => $this->options_config
        ];
    }
    
    /**
     * 봉투 양면테이프 가격 계산
     *
     * @param int $quantity 봉투 수량
     * @return int 양면테이프 가격
     */
    public function calculateEnvelopeTapePrice($quantity) {
        if (empty($quantity) || $quantity <= 0) {
            return 0;
        }

        $quantity = intval($quantity);

        // 양면테이프 가격 구조 (수정된 로직)
        if ($quantity == 500) {
            return 25000; // 500매일 때만: 25,000원 고정
        } else {
            return $quantity * 40; // 다른 모든 수량: 수량 × 40원
        }
    }

    /**
     * 폼 데이터 검증
     *
     * @param array $form_data 폼 데이터
     * @return array 검증 결과
     */
    public function validateFormData($form_data) {
        $errors = [];
        $validated_data = [];
        
        // 코팅 옵션 검증
        if (!empty($form_data['coating_enabled'])) {
            if (empty($form_data['coating_type']) || !isset($this->BASE_PRICES['coating'][$form_data['coating_type']])) {
                $errors[] = '올바른 코팅 옵션을 선택해주세요.';
            } else {
                $validated_data['coating_enabled'] = 1;
                $validated_data['coating_type'] = $form_data['coating_type'];
            }
        }
        
        // 접지 옵션 검증
        if (!empty($form_data['folding_enabled'])) {
            if (empty($form_data['folding_type']) || !isset($this->BASE_PRICES['folding'][$form_data['folding_type']])) {
                $errors[] = '올바른 접지 옵션을 선택해주세요.';
            } else {
                $validated_data['folding_enabled'] = 1;
                $validated_data['folding_type'] = $form_data['folding_type'];
            }
        }
        
        // 오시 옵션 검증
        if (!empty($form_data['creasing_enabled'])) {
            $creasing_lines = intval($form_data['creasing_lines']);
            if ($creasing_lines < 1 || $creasing_lines > 3) {
                $errors[] = '올바른 오시 줄 수를 선택해주세요.';
            } else {
                $validated_data['creasing_enabled'] = 1;
                $validated_data['creasing_lines'] = $creasing_lines;
            }
        }
        
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'data' => $validated_data
        ];
    }
}

/**
 * 전역 헬퍼 함수들
 */

/**
 * 추가 옵션 인스턴스 생성 (싱글톤 패턴)
 */
function getAdditionalOptions($db = null) {
    static $instance = null;
    
    if ($instance === null) {
        if ($db === null) {
            global $connect;
            $db = $connect;
        }
        $instance = new AdditionalOptions($db);
    }
    
    return $instance;
}

/**
 * 옵션 가격 계산 헬퍼 함수
 */
function calculateAdditionalOptionsPrice($selected_options, $quantity = 1000) {
    $additionalOptions = getAdditionalOptions();
    return $additionalOptions->calculateTotalPrice($selected_options, $quantity);
}
?>