<?php
/**
 * 🎨 SmartFieldComponent - 스마트 필드 렌더링 컴포넌트
 * 
 * ProductFieldMapper에서 제공하는 컨텍스트 정보를 기반으로
 * 각 제품에 맞는 폼 필드를 동적으로 렌더링하는 컴포넌트입니다.
 * 
 * 작성일: 2025년 8월 9일
 * 상태: 스마트 컴포넌트 시스템 구현 - 2단계
 */

require_once 'ProductFieldMapper.php';

class SmartFieldComponent {
    
    private $db;
    private $product_code;
    
    /**
     * 생성자
     * 
     * @param mysqli $db 데이터베이스 연결 객체
     * @param string $product_code 제품 코드
     */
    public function __construct($db, $product_code) {
        $this->db = $db;
        $this->product_code = $product_code;
    }

    /**
     * 단일 필드를 렌더링
     * 
     * @param string $field_name 필드명 (예: 'MY_type', 'PN_type')
     * @param string $current_value 현재 선택된 값 (옵션)
     * @param array $options 추가 옵션 (클래스, 스타일 등)
     * @return string HTML 폼 요소
     */
    public function renderField($field_name, $current_value = '', $options = []) {
        $context = ProductFieldMapper::getFieldContext($this->product_code, $field_name);
        
        if (!$context) {
            return "<p style='color: red;'>⚠️ 알 수 없는 필드: {$field_name} (제품: {$this->product_code})</p>";
        }

        // 기본 옵션 설정
        $default_options = [
            'class' => 'form-control smart-field',
            'onchange' => 'calculate_price()',
            'required' => true,
            'show_label' => true,
            'show_icon' => true
        ];
        $options = array_merge($default_options, $options);

        // 필드별 데이터 가져오기
        $field_data = $this->getFieldData($field_name);
        
        // HTML 생성
        $html = '';
        
        // 라벨 표시
        if ($options['show_label']) {
            $icon = $options['show_icon'] ? $context['icon'] . ' ' : '';
            $required_mark = $options['required'] ? ' <span style="color: red;">*</span>' : '';
            $html .= "<label for='{$field_name}' class='field-label'>";
            $html .= "{$icon}<strong>{$context['label']}</strong>{$required_mark}";
            $html .= "<small style='color: #666; margin-left: 10px;'>({$context['description']})</small>";
            $html .= "</label>";
        }

        // 셀렉트 박스 생성
        $html .= "<select name='{$field_name}' id='{$field_name}' class='{$options['class']}' ";
        $html .= "data-field-type='{$context['type']}' data-product='{$this->product_code}' ";
        
        if ($options['onchange']) {
            $html .= "onchange='{$options['onchange']}' ";
        }
        
        if ($options['required']) {
            $html .= "required ";
        }
        
        $html .= ">";

        // 기본 옵션
        $html .= "<option value=''>-- {$context['label']} 선택 --</option>";

        // 데이터 옵션들
        foreach ($field_data as $option) {
            $selected = ($current_value == $option['value']) ? 'selected' : '';
            $html .= "<option value='{$option['value']}' {$selected}>{$option['text']}</option>";
        }

        $html .= "</select>";

        // 추가 도움말이 있는 경우
        if (isset($options['help_text'])) {
            $html .= "<small class='form-text text-muted'>{$options['help_text']}</small>";
        }

        return $html;
    }

    /**
     * 제품의 모든 활성 필드를 렌더링
     * 
     * @param array $current_values 현재 값들 (필드명 => 값)
     * @param array $field_options 필드별 개별 옵션
     * @return string HTML 폼 섹션
     */
    public function renderAllFields($current_values = [], $field_options = []) {
        $active_fields = ProductFieldMapper::getActiveFields($this->product_code);
        $product_name = ProductFieldMapper::getProductName($this->product_code);
        
        $html = "<div class='smart-field-group' data-product='{$this->product_code}'>";
        $html .= "<h4 class='field-group-title'>{$product_name} 옵션 선택</h4>";
        
        foreach ($active_fields as $field_name) {
            $current_value = $current_values[$field_name] ?? '';
            $options = $field_options[$field_name] ?? [];
            
            $html .= "<div class='form-group mb-3'>";
            $html .= $this->renderField($field_name, $current_value, $options);
            $html .= "</div>";
        }
        
        $html .= "</div>";
        return $html;
    }

    /**
     * 필드 데이터를 데이터베이스에서 가져오기 (JOIN으로 실제 제목 표시)
     * 
     * @param string $field_name 필드명
     * @return array 옵션 배열 [{value, text}, ...]
     */
    private function getFieldData($field_name) {
        // 제품별 데이터 테이블 매핑
        $table_mapping = $this->getTableMapping();
        
        if (!isset($table_mapping[$this->product_code])) {
            return $this->getDefaultFieldData($field_name);
        }

        $table_info = $table_mapping[$this->product_code];
        $table_name = $table_info['table'];
        $field_mapping = $table_info['fields'][$field_name] ?? null;

        if (!$field_mapping) {
            return $this->getDefaultFieldData($field_name);
        }

        try {
            $column = $field_mapping['column'];
            
            // 특별 처리가 필요한 필드들
            if ($field_name === 'POtype') {
                return $this->getPOtypeOptions();
            }
            
            if ($field_name === 'MY_amount') {
                return $this->getQuantityOptions();
            }
            
            if ($field_name === 'ordertype') {
                return $this->getOrderTypeOptions();
            }
            
            // JOIN 쿼리로 번호와 제목을 함께 가져오기
            $query = "SELECT DISTINCT 
                        {$table_name}.{$column} as value,
                        COALESCE(tc.title, {$table_name}.{$column}) as text
                      FROM {$table_name} 
                      LEFT JOIN mlangprintauto_transactioncate tc ON tc.no = {$table_name}.{$column}
                      WHERE {$table_name}.{$column} IS NOT NULL AND {$table_name}.{$column} != '' 
                      ORDER BY {$table_name}.{$column}";
            
            $result = mysqli_query($this->db, $query);
            $options = [];
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if (!empty($row['value'])) {
                        $options[] = [
                            'value' => $row['value'],
                            'text' => $row['text'] // 이제 실제 제목이 표시됨!
                        ];
                    }
                }
            }
            
            // 데이터가 없으면 기본값 사용
            return count($options) > 0 ? $options : $this->getDefaultFieldData($field_name);
            
        } catch (Exception $e) {
            error_log("SmartFieldComponent: DB 조회 오류 - " . $e->getMessage());
            return $this->getDefaultFieldData($field_name);
        }
    }

    /**
     * POtype 필드 전용 옵션 처리 (완전 DB 기반)
     * 
     * @return array POtype 옵션 배열
     */
    private function getPOtypeOptions() {
        $table_mapping = $this->getTableMapping();
        $table_info = $table_mapping[$this->product_code];
        $table_name = $table_info['table'];
        
        try {
            // 1단계: 해당 제품 테이블에서 실제 사용되는 POtype 값들 조회
            $potype_query = "SELECT DISTINCT 
                                t.POtype as value,
                                COALESCE(tc.title, t.POtype) as text,
                                tc.title as transaction_title
                             FROM {$table_name} t
                             LEFT JOIN mlangprintauto_transactioncate tc ON tc.no = t.POtype
                             WHERE t.POtype IS NOT NULL AND t.POtype != ''
                             ORDER BY t.POtype";
            
            $result = mysqli_query($this->db, $potype_query);
            $options = [];
            
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $display_text = $row['text'];
                    
                    // transactioncate에 제목이 없으면 컨텍스트 기반으로 의미 추정
                    if (!$row['transaction_title']) {
                        $display_text = $this->guessPOtypeText($row['value']);
                    }
                    
                    $options[] = [
                        'value' => $row['value'],
                        'text' => $display_text
                    ];
                }
            }
            
            // 2단계: 옵션이 없으면 컨텍스트 기반 기본값 제공
            if (empty($options)) {
                return $this->getContextBasedPOtypeOptions();
            }
            
            return $options;
            
        } catch (Exception $e) {
            error_log("POtype 조회 오류: " . $e->getMessage());
            return $this->getContextBasedPOtypeOptions();
        }
    }
    
    /**
     * POtype 값에 대한 의미 추정 (transactioncate에 제목이 없는 경우)
     */
    private function guessPOtypeText($value) {
        $context = ProductFieldMapper::getFieldContext($this->product_code, 'POtype');
        
        switch ($context['type']) {
            case 'sides': // 인쇄면 (포스터, 전단지, 명함)
                switch ($value) {
                    case '1': return '단면 (앞면만)';
                    case '2': return '양면 (앞뒤 모두)';
                    default: return "인쇄면 {$value}";
                }
                
            case 'color': // 인쇄색상 (봉투, 양식지)
                switch ($value) {
                    case '1': return '1도 (흑백)';
                    case '2': return '2도 (2색)';
                    case '3': return '3도 (3색)';
                    case '4': return '4도 (컬러)';
                    default: return "{$value}도";
                }
                
            case 'finishing': // 후가공 (쿠폰)
                switch ($value) {
                    case '1': return '후가공 없음';
                    case '2': return '코팅';
                    case '3': return '특수 후가공';
                    default: return "후가공 {$value}";
                }
                
            default:
                return "옵션 {$value}";
        }
    }
    
    /**
     * 컨텍스트 기반 POtype 기본 옵션 (DB에서 조회 실패시 사용)
     */
    private function getContextBasedPOtypeOptions() {
        $context = ProductFieldMapper::getFieldContext($this->product_code, 'POtype');
        
        switch ($context['type']) {
            case 'sides':
                return [
                    ['value' => '1', 'text' => '단면 (앞면만)'],
                    ['value' => '2', 'text' => '양면 (앞뒤 모두)']
                ];
                
            case 'color':
                return [
                    ['value' => '1', 'text' => '1도 (흑백)'],
                    ['value' => '2', 'text' => '2도 (2색)'],
                    ['value' => '3', 'text' => '3도 (3색)'],
                    ['value' => '4', 'text' => '4도 (컬러)']
                ];
                
            case 'finishing':
                return [
                    ['value' => '1', 'text' => '후가공 없음'],
                    ['value' => '2', 'text' => '코팅'],
                    ['value' => '3', 'text' => '특수 후가공']
                ];
                
            default:
                return [
                    ['value' => '1', 'text' => '옵션 1'],
                    ['value' => '2', 'text' => '옵션 2']
                ];
        }
    }

    /**
     * 수량(MY_amount) 필드 전용 옵션 처리 (완전 DB 기반)
     * 
     * @return array 수량 옵션 배열
     */
    private function getQuantityOptions() {
        $table_mapping = $this->getTableMapping();
        $table_info = $table_mapping[$this->product_code];
        $table_name = $table_info['table'];
        
        try {
            // 해당 제품 테이블에서 실제 사용되는 수량 값들 조회
            $quantity_query = "SELECT DISTINCT quantity as value
                               FROM {$table_name} 
                               WHERE quantity IS NOT NULL AND quantity > 0
                               ORDER BY quantity";
            
            $result = mysqli_query($this->db, $quantity_query);
            $options = [];
            
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $quantity = $row['value'];
                    // 수량은 숫자 그대로 표시하되, 단위 추가
                    $display_text = number_format($quantity) . '매';
                    
                    $options[] = [
                        'value' => $quantity,
                        'text' => $display_text
                    ];
                }
            }
            
            // 옵션이 없으면 기본 수량 제공
            if (empty($options)) {
                return [
                    ['value' => '100', 'text' => '100매'],
                    ['value' => '200', 'text' => '200매'],
                    ['value' => '500', 'text' => '500매'],
                    ['value' => '1000', 'text' => '1,000매']
                ];
            }
            
            return $options;
            
        } catch (Exception $e) {
            error_log("수량 조회 오류: " . $e->getMessage());
            return [
                ['value' => '100', 'text' => '100매'],
                ['value' => '500', 'text' => '500매'],
                ['value' => '1000', 'text' => '1,000매']
            ];
        }
    }

    /**
     * 편집비(ordertype) 필드 전용 옵션 처리 (완전 DB 기반)
     * 
     * @return array 편집비 옵션 배열
     */
    private function getOrderTypeOptions() {
        $table_mapping = $this->getTableMapping();
        $table_info = $table_mapping[$this->product_code];
        $table_name = $table_info['table'];
        
        try {
            // 해당 제품 테이블에서 실제 사용되는 편집비(DesignMoney) 값들 조회
            $ordertype_query = "SELECT DISTINCT DesignMoney as value
                                FROM {$table_name} 
                                WHERE DesignMoney IS NOT NULL AND DesignMoney != ''
                                ORDER BY CAST(DesignMoney AS UNSIGNED)";
            
            $result = mysqli_query($this->db, $ordertype_query);
            $options = [];
            
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $design_fee = $row['value'];
                    
                    // 편집비에 따른 표시 텍스트 생성
                    if ($design_fee == 0 || $design_fee == '0') {
                        $display_text = '편집 없음 (인쇄만)';
                    } else {
                        $display_text = '디자인 + 인쇄 (+' . number_format($design_fee) . '원)';
                    }
                    
                    $options[] = [
                        'value' => $design_fee,
                        'text' => $display_text
                    ];
                }
            }
            
            // 옵션이 없으면 기본 편집비 제공
            if (empty($options)) {
                return [
                    ['value' => '0', 'text' => '편집 없음 (인쇄만)'],
                    ['value' => '10000', 'text' => '기본 편집 (+10,000원)'],
                    ['value' => '30000', 'text' => '고급 편집 (+30,000원)']
                ];
            }
            
            return $options;
            
        } catch (Exception $e) {
            error_log("편집비 조회 오류: " . $e->getMessage());
            return [
                ['value' => '0', 'text' => '편집 없음 (인쇄만)'],
                ['value' => '10000', 'text' => '기본 편집 (+10,000원)'],
                ['value' => '30000', 'text' => '고급 편집 (+30,000원)']
            ];
        }
    }

    /**
     * 제품별 데이터베이스 테이블 매핑 정보 (실제 DB 구조 반영)
     * 
     * @return array 테이블 매핑 정보
     */
    private function getTableMapping() {
        return [
            'leaflet' => [
                'table' => 'mlangprintauto_inserted',  // 전단지 테이블 (소문자)
                'fields' => [
                    'MY_type' => ['column' => 'style'],         // 실제 필드: style
                    'MY_Fsd' => ['column' => 'TreeSelect'],     // 실제 필드: TreeSelect  
                    'PN_type' => ['column' => 'Section'],       // 실제 필드: Section
                    'POtype' => ['column' => 'POtype'],         // 실제 필드: POtype (일치!)
                    'MY_amount' => ['column' => 'quantity'],    // 실제 필드: quantity
                    'ordertype' => ['column' => 'DesignMoney'] // 실제 필드: DesignMoney
                ]
            ],
            'poster' => [
                'table' => 'mlangprintauto_littleprint',  // 포스터 테이블 (소문자)
                'fields' => [
                    'MY_type' => ['column' => 'style'],         // 실제 필드: style
                    'MY_Fsd' => ['column' => 'TreeSelect'],     // 실제 필드: TreeSelect
                    'PN_type' => ['column' => 'Section'],       // 실제 필드: Section  
                    'POtype' => ['column' => 'POtype'],         // 실제 필드: POtype (일치!)
                    'MY_amount' => ['column' => 'quantity'],    // 실제 필드: quantity
                    'ordertype' => ['column' => 'DesignMoney'] // 실제 필드: DesignMoney
                ]
            ],
            'namecard' => [
                'table' => 'mlangprintauto_NameCard',  // 명함 테이블
                'fields' => [
                    'MY_type' => ['column' => 'style'],         // 실제 필드: style
                    'PN_type' => ['column' => 'Section'],       // 실제 필드: Section (명함재질)
                    'POtype' => ['column' => 'POtype'],         // 실제 필드: POtype
                    'MY_amount' => ['column' => 'quantity'],    // 실제 필드: quantity
                    'ordertype' => ['column' => 'DesignMoney'] // 실제 필드: DesignMoney
                ]
            ],
            'coupon' => [
                'table' => 'mlangprintauto_merchandisebond',  // 쿠폰 테이블
                'fields' => [
                    'MY_type' => ['column' => 'style'],         // 실제 필드: style
                    'PN_type' => ['column' => 'Section'],       // 실제 필드: Section
                    'POtype' => ['column' => 'POtype'],         // 실제 필드: POtype (후가공)
                    'MY_amount' => ['column' => 'quantity'],    // 실제 필드: quantity
                    'ordertype' => ['column' => 'DesignMoney'] // 실제 필드: DesignMoney
                ]
            ],
            'envelope' => [
                'table' => 'mlangprintauto_envelope',  // 봉투 테이블
                'fields' => [
                    'MY_type' => ['column' => 'style'],         // 실제 필드: style
                    'PN_type' => ['column' => 'Section'],       // 실제 필드: Section
                    'POtype' => ['column' => 'POtype'],         // 실제 필드: POtype (인쇄색상)
                    'MY_amount' => ['column' => 'quantity'],    // 실제 필드: quantity
                    'ordertype' => ['column' => 'DesignMoney'] // 실제 필드: DesignMoney
                ]
            ],
            'form' => [
                'table' => 'mlangprintauto_ncrflambeau',  // 양식지 테이블
                'fields' => [
                    'MY_type' => ['column' => 'style'],         // 실제 필드: style
                    'PN_type' => ['column' => 'Section'],       // 실제 필드: Section
                    'MY_Fsd' => ['column' => 'TreeSelect'],     // 실제 필드: TreeSelect (인쇄색상)
                    'POtype' => ['column' => 'POtype'],         // 실제 필드: POtype
                    'MY_amount' => ['column' => 'quantity'],    // 실제 필드: quantity
                    'ordertype' => ['column' => 'DesignMoney'] // 실제 필드: DesignMoney
                ]
            ],
            'magnetic_sticker' => [
                'table' => 'mlangprintauto_msticker',  // 자석스티커 테이블
                'fields' => [
                    'MY_type' => ['column' => 'style'],         // 실제 필드: style
                    'PN_type' => ['column' => 'Section'],       // 실제 필드: Section
                    'MY_amount' => ['column' => 'quantity'],    // 실제 필드: quantity
                    'ordertype' => ['column' => 'DesignMoney'] // 실제 필드: DesignMoney
                ]
            ],
            'catalog' => [
                'table' => 'mlangprintauto_cadarok',  // 카다록 테이블
                'fields' => [
                    'MY_type' => ['column' => 'style'],         // 실제 필드: style
                    'PN_type' => ['column' => 'Section'],       // 실제 필드: Section
                    'MY_Fsd' => ['column' => 'TreeSelect'],     // 실제 필드: TreeSelect
                    'POtype' => ['column' => 'POtype'],         // 실제 필드: POtype
                    'MY_amount' => ['column' => 'quantity'],    // 실제 필드: quantity
                    'ordertype' => ['column' => 'DesignMoney'] // 실제 필드: DesignMoney
                ]
            ]
        ];
    }

    /**
     * 기본 필드 데이터 (데이터베이스 조회가 실패한 경우 사용)
     * 
     * @param string $field_name 필드명
     * @return array 기본 옵션 배열
     */
    private function getDefaultFieldData($field_name) {
        $default_data = [
            'MY_type' => [
                ['value' => 'general', 'text' => '일반'],
                ['value' => 'premium', 'text' => '프리미엄'],
                ['value' => 'special', 'text' => '특수']
            ],
            'PN_type' => [
                ['value' => 'A4', 'text' => 'A4'],
                ['value' => 'A3', 'text' => 'A3'],
                ['value' => 'B4', 'text' => 'B4'],
                ['value' => 'B5', 'text' => 'B5']
            ],
            'MY_Fsd' => [
                ['value' => '일반용지', 'text' => '일반용지'],
                ['value' => '고급용지', 'text' => '고급용지'],
                ['value' => '재생용지', 'text' => '재생용지']
            ],
            'POtype' => [
                ['value' => '단면', 'text' => '단면'],
                ['value' => '양면', 'text' => '양면']
            ],
            'MY_amount' => [
                ['value' => '100', 'text' => '100매'],
                ['value' => '200', 'text' => '200매'],
                ['value' => '500', 'text' => '500매'],
                ['value' => '1000', 'text' => '1,000매']
            ],
            'ordertype' => [
                ['value' => 'none', 'text' => '편집 없음'],
                ['value' => 'basic', 'text' => '기본 편집 (+10,000원)'],
                ['value' => 'premium', 'text' => '고급 편집 (+30,000원)']
            ]
        ];

        return $default_data[$field_name] ?? [
            ['value' => 'default', 'text' => '기본값']
        ];
    }

    /**
     * 필드의 현재 값을 기반으로 다음 단계 필드들을 업데이트하는 AJAX 엔드포인트용 데이터 반환
     * 
     * @param string $field_name 변경된 필드명
     * @param string $field_value 변경된 값
     * @return array 연관된 필드들의 업데이트 데이터
     */
    public function getFieldUpdateData($field_name, $field_value) {
        // 필드 간 의존성 매핑 (예: MY_type이 바뀌면 PN_type 옵션이 달라짐)
        $dependencies = $this->getFieldDependencies();
        
        $update_data = [];
        
        if (isset($dependencies[$field_name])) {
            foreach ($dependencies[$field_name] as $dependent_field) {
                $update_data[$dependent_field] = $this->getFilteredFieldData($dependent_field, $field_name, $field_value);
            }
        }
        
        return $update_data;
    }

    /**
     * 필드 간 의존성 매핑
     * 
     * @return array 의존성 매핑 정보
     */
    private function getFieldDependencies() {
        // 제품별 필드 의존성 정의
        $dependencies = [
            'leaflet' => [
                'MY_type' => ['PN_type', 'MY_Fsd'], // 구분이 바뀌면 규격과 종이종류 옵션이 달라짐
                'PN_type' => ['POtype'], // 규격이 바뀌면 인쇄면 옵션이 달라질 수 있음
            ],
            'namecard' => [
                'MY_type' => ['PN_type'], // 명함 종류가 바뀌면 재질 옵션이 달라짐
            ],
            'coupon' => [
                'MY_type' => ['PN_type', 'POtype'], // 쿠폰 종류가 바뀌면 규격과 후가공이 달라짐
            ]
        ];

        return $dependencies[$this->product_code] ?? [];
    }

    /**
     * 특정 조건으로 필터링된 필드 데이터 반환
     * 
     * @param string $target_field 대상 필드
     * @param string $filter_field 필터 기준 필드
     * @param string $filter_value 필터 값
     * @return array 필터링된 옵션 배열
     */
    private function getFilteredFieldData($target_field, $filter_field, $filter_value) {
        $table_mapping = $this->getTableMapping();
        
        if (!isset($table_mapping[$this->product_code])) {
            return $this->getDefaultFieldData($target_field);
        }

        try {
            $table_info = $table_mapping[$this->product_code];
            $table_name = $table_info['table'];
            $target_column = $table_info['fields'][$target_field]['column'];
            $filter_column = $table_info['fields'][$filter_field]['column'];

            $query = "SELECT DISTINCT {$target_column} as value, {$target_column} as text 
                      FROM {$table_name} 
                      WHERE {$filter_column} = ? 
                      AND {$target_column} IS NOT NULL AND {$target_column} != '' 
                      ORDER BY {$target_column}";
            
            $stmt = mysqli_prepare($this->db, $query);
            mysqli_stmt_bind_param($stmt, 's', $filter_value);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $options = [];
            while ($row = mysqli_fetch_assoc($result)) {
                if (!empty($row['value'])) {
                    $options[] = [
                        'value' => $row['value'],
                        'text' => $row['text']
                    ];
                }
            }
            
            return count($options) > 0 ? $options : $this->getDefaultFieldData($target_field);
            
        } catch (Exception $e) {
            error_log("SmartFieldComponent: 필터링 조회 오류 - " . $e->getMessage());
            return $this->getDefaultFieldData($target_field);
        }
    }

    /**
     * 디버깅용: 컴포넌트 정보 출력
     * 
     * @return string HTML 디버깅 정보
     */
    public function debugComponent() {
        $product_name = ProductFieldMapper::getProductName($this->product_code);
        $active_fields = ProductFieldMapper::getActiveFields($this->product_code);
        
        $debug_html = "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px; background: #f9f9f9;'>";
        $debug_html .= "<h4>🔧 SmartFieldComponent 디버그 정보</h4>";
        $debug_html .= "<p><strong>제품:</strong> {$product_name} ({$this->product_code})</p>";
        $debug_html .= "<p><strong>활성 필드:</strong> " . implode(', ', $active_fields) . "</p>";
        $debug_html .= "<p><strong>DB 연결:</strong> " . (isset($this->db) ? '✅ 연결됨' : '❌ 미연결') . "</p>";
        $debug_html .= "</div>";
        
        return $debug_html;
    }
}
?>