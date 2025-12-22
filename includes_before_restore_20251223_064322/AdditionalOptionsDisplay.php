<?php
/**
 * 추가 옵션 표시 시스템 - 장바구니/주문/완료 페이지용 모듈
 * 
 * 목적: 선택된 추가 옵션을 각 페이지에 맞게 표시
 * 특징: 개별 가격 명시, 총액 계산, 부가세 포함 표시
 * 
 * @version 1.0
 * @date 2025-01-08
 * @author SuperClaude Architecture System
 */

class AdditionalOptionsDisplay {
    private $additionalOptions;
    
    public function __construct($database_connection = null) {
        // AdditionalOptions 클래스 재사용
        if ($database_connection === null) {
            global $connect;
            $database_connection = $connect;
        }
        
        require_once 'AdditionalOptions.php';
        $this->additionalOptions = new AdditionalOptions($database_connection);
    }
    
    /**
     * 장바구니용 옵션 요약 표시
     * 형식: "코팅(단면유광)80,000원+접지(2단)40,000원"
     */
    public function getCartSummary($cart_data) {
        $options_text = [];

        // 코팅 옵션 (전단지)
        if (!empty($cart_data['coating_enabled']) && !empty($cart_data['coating_type'])) {
            $coating_name = $this->getOptionDisplayName('coating', $cart_data['coating_type']);
            $coating_price = number_format($cart_data['coating_price']);
            $options_text[] = "코팅({$coating_name}){$coating_price}원";
        }

        // 접지 옵션 (전단지)
        if (!empty($cart_data['folding_enabled']) && !empty($cart_data['folding_type'])) {
            $folding_name = $this->getOptionDisplayName('folding', $cart_data['folding_type']);
            $folding_price = number_format($cart_data['folding_price']);
            $options_text[] = "접지({$folding_name}){$folding_price}원";
        }

        // 오시 옵션 (전단지)
        if (!empty($cart_data['creasing_enabled']) && !empty($cart_data['creasing_lines'])) {
            $creasing_name = $this->getOptionDisplayName('creasing', $cart_data['creasing_lines'] . 'line');
            $creasing_price = number_format($cart_data['creasing_price']);
            $options_text[] = "오시({$creasing_name}){$creasing_price}원";
        }

        // 🔧 봉투 양면테이프 옵션
        if (!empty($cart_data['envelope_tape_enabled']) && !empty($cart_data['envelope_tape_quantity'])) {
            $tape_quantity = number_format($cart_data['envelope_tape_quantity']);
            $tape_price = number_format($cart_data['envelope_tape_price']);
            $options_text[] = "양면테이프({$tape_quantity}개){$tape_price}원";
        }

        // 🆕 프리미엄 옵션 (명함)
        if (!empty($cart_data['premium_options'])) {
            $premium_options = is_string($cart_data['premium_options'])
                ? json_decode($cart_data['premium_options'], true)
                : $cart_data['premium_options'];

            if ($premium_options && is_array($premium_options)) {
                $premium_option_names = [
                    'foil' => ['name' => '박', 'types' => [
                        'gold_matte' => '금박무광',
                        'gold_gloss' => '금박유광',
                        'silver_matte' => '은박무광',
                        'silver_gloss' => '은박유광',
                        'blue_gloss' => '청박유광',
                        'red_gloss' => '적박유광',
                        'green_gloss' => '녹박유광',
                        'black_gloss' => '먹박유광'
                    ]],
                    'numbering' => ['name' => '넘버링', 'types' => ['single' => '1개', 'double' => '2개']],
                    'perforation' => ['name' => '미싱', 'types' => ['horizontal' => '가로미싱', 'vertical' => '세로미싱', 'cross' => '십자미싱']],
                    'rounding' => ['name' => '귀돌이', 'types' => ['4corners' => '네귀돌이', '2corners' => '두귀돌이']],
                    'creasing' => ['name' => '오시', 'types' => ['single_crease' => '1줄오시', 'double_crease' => '2줄오시']]
                ];

                foreach ($premium_option_names as $option_key => $option_info) {
                    if (!empty($premium_options[$option_key . '_enabled']) && $premium_options[$option_key . '_enabled'] == 1) {
                        $price = intval($premium_options[$option_key . '_price'] ?? 0);
                        if ($price > 0) {
                            $display_text = $option_info['name'];
                            $option_type = $premium_options[$option_key . '_type'] ?? '';
                            if (!empty($option_type) && isset($option_info['types'][$option_type])) {
                                $display_text .= '(' . $option_info['types'][$option_type] . ')';
                            } elseif (empty($option_type)) {
                                $display_text .= '(타입미선택)';
                            }
                            $options_text[] = $display_text . number_format($price) . '원';
                        }
                    }
                }
            }
        } elseif (!empty($cart_data['premium_options_total']) && $cart_data['premium_options_total'] > 0) {
            // premium_options가 없지만 총액만 있는 경우
            $options_text[] = "프리미엄옵션 " . number_format($cart_data['premium_options_total']) . '원';
        }

        if (empty($options_text)) {
            return '옵션 없음';
        }

        return implode('+', $options_text);
    }
    
    /**
     * 주문페이지용 상세 옵션 정보
     */
    public function getOrderDetails($cart_data) {
        $details = [
            'options' => [],
            'total_price' => 0,
            'has_options' => false
        ];
        
        // 코팅 옵션
        if (!empty($cart_data['coating_enabled']) && !empty($cart_data['coating_type'])) {
            $coating_name = $this->getOptionDisplayName('coating', $cart_data['coating_type']);
            $coating_price = intval($cart_data['coating_price']);
            
            $details['options'][] = [
                'category' => '코팅',
                'name' => $coating_name,
                'price' => $coating_price,
                'formatted_price' => number_format($coating_price) . '원'
            ];
            $details['total_price'] += $coating_price;
            $details['has_options'] = true;
        }
        
        // 접지 옵션
        if (!empty($cart_data['folding_enabled']) && !empty($cart_data['folding_type'])) {
            $folding_name = $this->getOptionDisplayName('folding', $cart_data['folding_type']);
            $folding_price = intval($cart_data['folding_price']);
            
            $details['options'][] = [
                'category' => '접지',
                'name' => $folding_name,
                'price' => $folding_price,
                'formatted_price' => number_format($folding_price) . '원'
            ];
            $details['total_price'] += $folding_price;
            $details['has_options'] = true;
        }
        
        // 오시 옵션
        if (!empty($cart_data['creasing_enabled']) && !empty($cart_data['creasing_lines'])) {
            $creasing_name = $this->getOptionDisplayName('creasing', $cart_data['creasing_lines'] . 'line');
            $creasing_price = intval($cart_data['creasing_price']);

            $details['options'][] = [
                'category' => '오시',
                'name' => $creasing_name,
                'price' => $creasing_price,
                'formatted_price' => number_format($creasing_price) . '원'
            ];
            $details['total_price'] += $creasing_price;
            $details['has_options'] = true;
        }

        // 🆕 봉투 양면테이프 옵션
        if (!empty($cart_data['envelope_tape_enabled']) && !empty($cart_data['envelope_tape_quantity'])) {
            $tape_quantity = intval($cart_data['envelope_tape_quantity']);
            $tape_price = intval($cart_data['envelope_tape_price']);

            $details['options'][] = [
                'category' => '양면테이프',
                'name' => number_format($tape_quantity) . '개',
                'price' => $tape_price,
                'formatted_price' => number_format($tape_price) . '원'
            ];
            $details['total_price'] += $tape_price;
            $details['has_options'] = true;
        }

        // 🆕 명함 프리미엄 옵션 (JSON)
        if (!empty($cart_data['premium_options'])) {
            $premium_options = is_string($cart_data['premium_options'])
                ? json_decode($cart_data['premium_options'], true)
                : $cart_data['premium_options'];

            if ($premium_options && is_array($premium_options)) {
                $premium_option_names = [
                    'foil' => ['name' => '박', 'types' => [
                        'gold_matte' => '금박무광',
                        'gold_gloss' => '금박유광',
                        'silver_matte' => '은박무광',
                        'silver_gloss' => '은박유광',
                        'blue_gloss' => '청박유광',
                        'red_gloss' => '적박유광',
                        'green_gloss' => '녹박유광',
                        'black_gloss' => '먹박유광'
                    ]],
                    'numbering' => ['name' => '넘버링', 'types' => ['single' => '1개', 'double' => '2개']],
                    'perforation' => ['name' => '미싱', 'types' => ['horizontal' => '가로미싱', 'vertical' => '세로미싱', 'cross' => '십자미싱']],
                    'rounding' => ['name' => '귀돌이', 'types' => ['4corners' => '네귀돌이', '2corners' => '두귀돌이']],
                    'creasing' => ['name' => '오시', 'types' => ['single_crease' => '1줄오시', 'double_crease' => '2줄오시']]
                ];

                foreach ($premium_option_names as $option_key => $option_info) {
                    if (!empty($premium_options[$option_key . '_enabled']) && $premium_options[$option_key . '_enabled'] == 1) {
                        $price = intval($premium_options[$option_key . '_price'] ?? 0);
                        if ($price > 0) {
                            $option_type = $premium_options[$option_key . '_type'] ?? '';

                            // 타입이 비어있으면 "타입미선택" 표시
                            if (empty($option_type)) {
                                $display_name = '타입미선택';
                            } elseif (isset($option_info['types'][$option_type])) {
                                $display_name = $option_info['types'][$option_type];
                            } else {
                                $display_name = $option_type;
                            }

                            $details['options'][] = [
                                'category' => $option_info['name'],
                                'name' => $display_name,
                                'price' => $price,
                                'formatted_price' => number_format($price) . '원'
                            ];
                            $details['total_price'] += $price;
                            $details['has_options'] = true;
                        }
                    }
                }
            }
        } elseif (!empty($cart_data['premium_options_total']) && $cart_data['premium_options_total'] > 0) {
            // premium_options JSON이 없지만 총액만 있는 경우
            $details['options'][] = [
                'category' => '프리미엄옵션',
                'name' => '상세정보 없음',
                'price' => intval($cart_data['premium_options_total']),
                'formatted_price' => number_format($cart_data['premium_options_total']) . '원'
            ];
            $details['total_price'] += intval($cart_data['premium_options_total']);
            $details['has_options'] = true;
        }

        return $details;
    }
    
    /**
     * 이메일용 HTML 테이블 형식
     */
    public function getEmailDisplay($cart_data, $is_detailed = true) {
        $details = $this->getOrderDetails($cart_data);
        
        if (!$details['has_options']) {
            return '';
        }
        
        $html = '';
        
        if ($is_detailed) {
            // 상세 테이블 형식 (관리자용)
            $html .= '<h4>📎 선택된 추가 옵션</h4>';
            $html .= '<table border="1" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
            $html .= '<tr style="background: #f8f9fa;"><th style="padding: 8px;">옵션</th><th style="padding: 8px;">가격</th></tr>';
            
            foreach ($details['options'] as $option) {
                $html .= '<tr>';
                $html .= '<td style="padding: 8px;">' . $option['category'] . '(' . $option['name'] . ')</td>';
                $html .= '<td style="padding: 8px; text-align: right;">' . $option['formatted_price'] . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '<tr style="background: #e3f2fd; font-weight: bold;">';
            $html .= '<td style="padding: 8px;">추가옵션 소계</td>';
            $html .= '<td style="padding: 8px; text-align: right;">' . number_format($details['total_price']) . '원</td>';
            $html .= '</tr>';
            $html .= '</table>';
        } else {
            // 간단 형식 (고객용)
            $html .= '<p><strong>추가 옵션:</strong> ' . $this->getCartSummary($cart_data) . '</p>';
            $html .= '<p><strong>추가 옵션 소계:</strong> ' . number_format($details['total_price']) . '원</p>';
        }
        
        return $html;
    }
    
    /**
     * 총 가격 계산 (기본 가격 + 추가 옵션)
     * 주의: st_price에 이미 옵션 가격이 포함되어 있으므로 중복 추가하지 않음
     */
    public function calculateTotalWithOptions($base_price, $cart_data) {
        $options_details = $this->getOrderDetails($cart_data);
        
        // st_price에 이미 옵션 가격이 포함되어 있으므로 
        // 실제 기본 가격 = st_price - 옵션 가격
        $actual_base_price = intval($base_price) - $options_details['total_price'];
        $total = intval($base_price); // st_price 그대로 사용 (이미 옵션 포함)
        $total_vat = intval($total * 1.1);
        
        return [
            'base_price' => $actual_base_price,
            'options_price' => $options_details['total_price'],
            'total_price' => $total,
            'total_vat' => $total_vat,
            'formatted' => [
                'base_price' => number_format($actual_base_price) . '원',
                'options_price' => number_format($options_details['total_price']) . '원',
                'total_price' => number_format($total) . '원',
                'total_vat' => number_format($total_vat) . '원'
            ]
        ];
    }
    
    /**
     * 옵션별 표시명 반환
     */
    private function getOptionDisplayName($category, $type) {
        $names = [
            'coating' => [
                'single' => '단면유광',
                'double' => '양면유광',
                'single_matte' => '단면무광',
                'double_matte' => '양면무광'
            ],
            'folding' => [
                '2fold' => '2단',
                '3fold' => '3단',
                'accordion' => '병풍',
                'gate' => '대문'
            ],
            'creasing' => [
                '1line' => '1줄',
                '2line' => '2줄',
                '3line' => '3줄'
            ]
        ];
        
        return $names[$category][$type] ?? $type;
    }
    
    /**
     * 장바구니 테이블에 표시할 옵션 컬럼 HTML
     */
    public function getCartColumnHtml($cart_data) {
        $summary = $this->getCartSummary($cart_data);
        
        if ($summary === '옵션 없음') {
            return '<span style="color: #6c757d; font-style: italic;">옵션 없음</span>';
        }
        
        return '<span style="color: #28a745; font-weight: 600; font-size: 0.9em;">' . $summary . '</span>';
    }
}

/**
 * 전역 헬퍼 함수들
 */

/**
 * AdditionalOptionsDisplay 인스턴스 생성 (싱글톤 패턴)
 */
function getAdditionalOptionsDisplay($db = null) {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new AdditionalOptionsDisplay($db);
    }
    
    return $instance;
}

/**
 * 장바구니용 옵션 요약 표시 헬퍼 함수
 */
function displayCartOptions($cart_data) {
    $display = getAdditionalOptionsDisplay();
    return $display->getCartColumnHtml($cart_data);
}

/**
 * 총액 계산 헬퍼 함수 (기본가격 + 옵션가격)
 */
function calculateTotalWithAdditionalOptions($base_price, $cart_data) {
    $display = getAdditionalOptionsDisplay();
    return $display->calculateTotalWithOptions($base_price, $cart_data);
}
?>