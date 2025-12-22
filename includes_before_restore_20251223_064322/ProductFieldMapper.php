<?php
/**
 * 🔧 ProductFieldMapper - 제품별 필드 의미 매핑 클래스
 * 
 * 각 제품(명함, 전단지, 포스터 등)의 동일한 필드명이 다른 의미를 가지는 문제를 해결하기 위해
 * 컨텍스트 기반으로 필드의 실제 의미를 매핑하는 클래스입니다.
 * 
 * 작성일: 2025년 8월 9일
 * 상태: 스마트 컴포넌트 시스템 구현 - 1단계
 */

class ProductFieldMapper {
    
    /**
     * 제품별 필드 컨텍스트 매핑 테이블
     * 
     * 구조: [제품코드][필드명] = [라벨, 아이콘, 타입, 설명]
     */
    private static $field_contexts = [
        
        // 📋 전단지 (완전형 패턴 - 모든 필드 사용)
        'leaflet' => [
            'MY_type' => [
                'label' => '구분', 
                'icon' => '🏷️', 
                'type' => 'category',
                'description' => '전단지 종류 구분'
            ],
            'MY_Fsd' => [
                'label' => '종이종류', 
                'icon' => '📄', 
                'type' => 'material',
                'description' => '인쇄용지 재질 선택'
            ],
            'PN_type' => [
                'label' => '종이규격', 
                'icon' => '📏', 
                'type' => 'size',
                'description' => '용지 크기 규격'
            ],
            'POtype' => [
                'label' => '인쇄면', 
                'icon' => '🔄', 
                'type' => 'sides',
                'description' => '단면/양면 인쇄 선택'
            ],
            'MY_amount' => [
                'label' => '수량', 
                'icon' => '📊', 
                'type' => 'quantity',
                'description' => '주문 수량'
            ],
            'ordertype' => [
                'label' => '편집비', 
                'icon' => '✏️', 
                'type' => 'design',
                'description' => '디자인 작업 선택'
            ]
        ],

        // 🎯 포스터 (완전형 패턴 - 전단지와 동일)
        'poster' => [
            'MY_type' => [
                'label' => '구분', 
                'icon' => '🎨', 
                'type' => 'category',
                'description' => '포스터 종류 구분'
            ],
            'MY_Fsd' => [
                'label' => '종이종류', 
                'icon' => '📄', 
                'type' => 'material',
                'description' => '포스터 용지 재질'
            ],
            'PN_type' => [
                'label' => '종이규격', 
                'icon' => '📏', 
                'type' => 'size',
                'description' => '포스터 크기 규격'
            ],
            'POtype' => [
                'label' => '인쇄면', 
                'icon' => '🔄', 
                'type' => 'sides',
                'description' => '단면/양면 인쇄'
            ],
            'MY_amount' => [
                'label' => '수량', 
                'icon' => '📊', 
                'type' => 'quantity',
                'description' => '주문 수량'
            ],
            'ordertype' => [
                'label' => '편집비', 
                'icon' => '✏️', 
                'type' => 'design',
                'description' => '디자인 작업 선택'
            ]
        ],

        // 💳 명함 (재질 특화형 패턴)
        'namecard' => [
            'MY_type' => [
                'label' => '종류', 
                'icon' => '💳', 
                'type' => 'category',
                'description' => '명함 종류 선택'
            ],
            'PN_type' => [
                'label' => '명함재질', 
                'icon' => '🏷️', 
                'type' => 'material',  // 여기서는 재질이 PN_type!
                'description' => '명함 용지 재질'
            ],
            'POtype' => [
                'label' => '인쇄면', 
                'icon' => '🔄', 
                'type' => 'sides',
                'description' => '단면/양면 인쇄'
            ],
            'MY_amount' => [
                'label' => '수량', 
                'icon' => '📊', 
                'type' => 'quantity',
                'description' => '주문 수량'
            ],
            'ordertype' => [
                'label' => '편집비', 
                'icon' => '✏️', 
                'type' => 'design',
                'description' => '디자인 작업 선택'
            ]
        ],

        // 🎫 쿠폰/상품권 (특수 패턴)
        'coupon' => [
            'MY_type' => [
                'label' => '종류', 
                'icon' => '🎫', 
                'type' => 'category',
                'description' => '쿠폰/상품권 종류'
            ],
            'PN_type' => [
                'label' => '규격선택', 
                'icon' => '📏', 
                'type' => 'size',
                'description' => '쿠폰 크기 규격'
            ],
            'POtype' => [
                'label' => '후가공', 
                'icon' => '⚙️', 
                'type' => 'finishing',  // 여기서는 후가공이 POtype!
                'description' => '코팅, 접합 등 후가공'
            ],
            'MY_amount' => [
                'label' => '수량', 
                'icon' => '📊', 
                'type' => 'quantity',
                'description' => '주문 수량'
            ],
            'ordertype' => [
                'label' => '편집비', 
                'icon' => '✏️', 
                'type' => 'design',
                'description' => '디자인 작업 선택'
            ]
        ],

        // ✉️ 봉투 (색상 특화형 패턴)
        'envelope' => [
            'MY_type' => [
                'label' => '구분', 
                'icon' => '✉️', 
                'type' => 'category',
                'description' => '봉투 종류 구분'
            ],
            'PN_type' => [
                'label' => '종류', 
                'icon' => '📏', 
                'type' => 'size',
                'description' => '봉투 규격 종류'
            ],
            'POtype' => [
                'label' => '인쇄색상', 
                'icon' => '🎨', 
                'type' => 'color',  // 여기서는 색상이 POtype!
                'description' => '인쇄 색상 선택'
            ],
            'MY_amount' => [
                'label' => '수량', 
                'icon' => '📊', 
                'type' => 'quantity',
                'description' => '주문 수량'
            ],
            'ordertype' => [
                'label' => '편집비', 
                'icon' => '✏️', 
                'type' => 'design',
                'description' => '디자인 작업 선택'
            ]
        ],

        // 📋 양식지 (색상 특화형 패턴)
        'form' => [
            'MY_type' => [
                'label' => '구분', 
                'icon' => '📋', 
                'type' => 'category',
                'description' => '양식지 종류 구분'
            ],
            'PN_type' => [
                'label' => '규격', 
                'icon' => '📏', 
                'type' => 'size',
                'description' => '양식지 규격'
            ],
            'MY_Fsd' => [
                'label' => '인쇄색상', 
                'icon' => '🎨', 
                'type' => 'color',  // 여기서는 색상이 MY_Fsd!
                'description' => '인쇄 색상 선택'
            ],
            'MY_amount' => [
                'label' => '수량', 
                'icon' => '📊', 
                'type' => 'quantity',
                'description' => '주문 수량'
            ],
            'ordertype' => [
                'label' => '편집비', 
                'icon' => '✏️', 
                'type' => 'design',
                'description' => '디자인 작업 선택'
            ]
        ],

        // 🧲 자석스티커 (단순형 패턴)
        'magnetic_sticker' => [
            'MY_type' => [
                'label' => '종류', 
                'icon' => '🧲', 
                'type' => 'category',
                'description' => '자석스티커 종류'
            ],
            'PN_type' => [
                'label' => '규격', 
                'icon' => '📏', 
                'type' => 'size',
                'description' => '스티커 규격'
            ],
            'MY_amount' => [
                'label' => '수량', 
                'icon' => '📊', 
                'type' => 'quantity',
                'description' => '주문 수량'
            ],
            'ordertype' => [
                'label' => '편집비', 
                'icon' => '✏️', 
                'type' => 'design',
                'description' => '디자인 작업 선택'
            ]
        ],

        // 📖 카다록 (거의 완전형 패턴)
        'catalog' => [
            'MY_type' => [
                'label' => '구분', 
                'icon' => '📖', 
                'type' => 'category',
                'description' => '카다록 종류 구분'
            ],
            'PN_type' => [
                'label' => '규격', 
                'icon' => '📏', 
                'type' => 'size',
                'description' => '카다록 규격'
            ],
            'MY_Fsd' => [
                'label' => '종이종류', 
                'icon' => '📄', 
                'type' => 'material',
                'description' => '카다록 용지 재질'
            ],
            'MY_amount' => [
                'label' => '수량', 
                'icon' => '📊', 
                'type' => 'quantity',
                'description' => '주문 수량'
            ],
            'ordertype' => [
                'label' => '편집비', 
                'icon' => '✏️', 
                'type' => 'design',
                'description' => '디자인 작업 선택'
            ]
        ]
    ];

    /**
     * 제품별 활성 필드 리스트
     * 어떤 필드가 해당 제품에서 사용되는지 정의
     */
    private static $product_active_fields = [
        'leaflet' => ['MY_type', 'MY_Fsd', 'PN_type', 'POtype', 'MY_amount', 'ordertype'],
        'poster' => ['MY_type', 'MY_Fsd', 'PN_type', 'POtype', 'MY_amount', 'ordertype'],
        'namecard' => ['MY_type', 'PN_type', 'POtype', 'MY_amount', 'ordertype'],
        'coupon' => ['MY_type', 'PN_type', 'POtype', 'MY_amount', 'ordertype'],
        'envelope' => ['MY_type', 'PN_type', 'POtype', 'MY_amount', 'ordertype'],
        'form' => ['MY_type', 'PN_type', 'MY_Fsd', 'MY_amount', 'ordertype'],
        'magnetic_sticker' => ['MY_type', 'PN_type', 'MY_amount', 'ordertype'],
        'catalog' => ['MY_type', 'PN_type', 'MY_Fsd', 'MY_amount', 'ordertype']
    ];

    /**
     * 특정 제품의 특정 필드에 대한 컨텍스트 정보를 반환
     * 
     * @param string $product_code 제품 코드 (예: 'namecard', 'leaflet')
     * @param string $field_name 필드명 (예: 'MY_type', 'PN_type')
     * @return array|null 컨텍스트 정보 또는 null
     */
    public static function getFieldContext($product_code, $field_name) {
        return self::$field_contexts[$product_code][$field_name] ?? null;
    }

    /**
     * 특정 제품에서 사용되는 모든 필드의 컨텍스트 정보를 반환
     * 
     * @param string $product_code 제품 코드
     * @return array 필드별 컨텍스트 정보 배열
     */
    public static function getProductFields($product_code) {
        return self::$field_contexts[$product_code] ?? [];
    }

    /**
     * 특정 제품에서 활성화된 필드 목록을 반환
     * 
     * @param string $product_code 제품 코드
     * @return array 활성 필드명 배열
     */
    public static function getActiveFields($product_code) {
        return self::$product_active_fields[$product_code] ?? [];
    }

    /**
     * 필드가 특정 제품에서 사용되는지 확인
     * 
     * @param string $product_code 제품 코드
     * @param string $field_name 필드명
     * @return boolean 사용 여부
     */
    public static function isFieldActive($product_code, $field_name) {
        $active_fields = self::getActiveFields($product_code);
        return in_array($field_name, $active_fields);
    }

    /**
     * 모든 제품 코드 목록을 반환
     * 
     * @return array 제품 코드 배열
     */
    public static function getAllProductCodes() {
        return array_keys(self::$field_contexts);
    }

    /**
     * 제품 코드에 따른 한국어 제품명 반환
     * 
     * @param string $product_code 제품 코드
     * @return string 한국어 제품명
     */
    public static function getProductName($product_code) {
        $product_names = [
            'leaflet' => '전단지',
            'poster' => '포스터', 
            'namecard' => '명함',
            'coupon' => '쿠폰/상품권',
            'envelope' => '봉투',
            'form' => '양식지',
            'magnetic_sticker' => '자석스티커',
            'catalog' => '카다록'
        ];
        
        return $product_names[$product_code] ?? $product_code;
    }

    /**
     * 디버깅용: 특정 제품의 필드 매핑 상태를 출력
     * 
     * @param string $product_code 제품 코드
     * @return string HTML 형태의 디버깅 정보
     */
    public static function debugProductMapping($product_code) {
        $product_name = self::getProductName($product_code);
        $fields = self::getProductFields($product_code);
        $active_fields = self::getActiveFields($product_code);
        
        $debug_html = "<h3>🔍 {$product_name} ({$product_code}) 필드 매핑</h3>";
        $debug_html .= "<table border='1' cellpadding='5'>";
        $debug_html .= "<tr><th>필드명</th><th>라벨</th><th>아이콘</th><th>타입</th><th>설명</th><th>활성</th></tr>";
        
        foreach($fields as $field_name => $context) {
            $is_active = in_array($field_name, $active_fields) ? '✅' : '❌';
            $debug_html .= "<tr>";
            $debug_html .= "<td><code>{$field_name}</code></td>";
            $debug_html .= "<td>{$context['icon']} {$context['label']}</td>";
            $debug_html .= "<td>{$context['icon']}</td>";
            $debug_html .= "<td>{$context['type']}</td>";
            $debug_html .= "<td>{$context['description']}</td>";
            $debug_html .= "<td>{$is_active}</td>";
            $debug_html .= "</tr>";
        }
        
        $debug_html .= "</table>";
        return $debug_html;
    }
}
?>