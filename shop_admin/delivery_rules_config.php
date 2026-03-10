<?php
/**
 * 택배 선불 배송비 규칙 (delivery_rules_config.php)
 *
 * ===== 선불 택배비 자동계산의 SSOT =====
 * 사용처: ShippingCalculator::classifyPrepaid()
 *
 * 구조:
 *   'config_key' => [
 *       'label'        => '한글 제품명',
 *       'prepaid_type' => 'auto' | 'call_required' | 'cod_only',
 *       'rules'        => [
 *           ['min' => 0, 'max' => 5000, 'box' => 1, 'price' => 3000, 'label' => '설명'],
 *       ]
 *   ]
 *
 * ⚠️ rules의 min/max 기준:
 *   - 명함, 스티커, 봉투, 상품권, 포스터: 매수(sheets)
 *   - 전단지: 매수(sheets) — 연수 변환은 ShippingCalculator가 처리
 *   - NCR양식지: 권수(volumes)
 *
 * === 박스 그룹핑 규칙 ===
 * 합포장 가능: 명함 + 스티커 + 상품권 (총 20kg까지)
 * 별도 박스: 봉투, 전단지, 양식지, 포스터, 카다록, 자석스티커
 * 무조건 착불: 자석스티커
 * 상세: ShippingCalculator::MIXABLE_PRODUCTS, SEPARATE_BOX_PRODUCTS, ALWAYS_COD_PRODUCTS
 *
 * === config_key 매핑 (ShippingCalculator::getDeliveryConfigKey) ===
 * namecard           → 'namecard'
 * merchandisebond    → 'merchandisebond'
 * sticker/sticker_new→ 'sticker'
 * envelope + 소봉투  → 'envelope_small'
 * envelope + 대봉투  → 'envelope_large'
 * inserted + A4      → 'inserted_a4'       (100g 이하 합판)
 * inserted + B5/16절 → 'inserted_b5'       (100g 이하 합판)
 * inserted + A5      → 'inserted_a5'       (100g 이하 합판)
 * inserted + B6/32절 → 'inserted_b6'       (100g 이하 합판)
 * inserted + 대형/고평량 → 'inserted_large' (전화 요망)
 * littleprint        → 'littleprint'
 * ncrflambeau        → 'ncrflambeau'
 * cadarok            → 'cadarok'           (전화 요망)
 * msticker           → 'msticker'          (착불 전용)
 *
 * @since 2026-03-09
 */

return [

    // ===== 명함 (namecard) =====
    'namecard' => [
        'label' => '명함',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,     'max' => 5000,        'box' => 1, 'price' => 3000, 'label' => '5,000매 이하'],
            ['min' => 5001,  'max' => 10000,       'box' => 2, 'price' => 4000, 'label' => '10,000매'],
            ['min' => 10001, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 5000, 'label' => '10,000매 초과'],
        ],
    ],

    // ===== 상품권 (merchandisebond) =====
    'merchandisebond' => [
        'label' => '상품권',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,     'max' => 5000,        'box' => 1, 'price' => 3000, 'label' => '5,000매 이하'],
            ['min' => 5001,  'max' => 10000,       'box' => 2, 'price' => 4000, 'label' => '10,000매'],
            ['min' => 10001, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 5000, 'label' => '10,000매 초과'],
        ],
    ],

    // ===== 스티커 (sticker / sticker_new) =====
    'sticker' => [
        'label' => '스티커',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,    'max' => 1000,        'box' => 1, 'price' => 3000, 'label' => '1,000매 이하'],
            ['min' => 1001, 'max' => 3000,        'box' => 1, 'price' => 3000, 'label' => '3,000매 이하'],
            ['min' => 3001, 'max' => PHP_INT_MAX, 'box' => 2, 'price' => 4000, 'label' => '3,000매 초과'],
        ],
    ],

    // ===== 소봉투 (envelope_small) =====
    // 소봉투, A4소봉투, A4자켓, 쟈켓소봉투
    // 2,000매까지 1박스, 3,500원
    'envelope_small' => [
        'label' => '소봉투',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,    'max' => 2000,        'box' => 1, 'price' => 3500, 'label' => '2,000매 이하 (1박스)'],
            ['min' => 2001, 'max' => PHP_INT_MAX, 'box' => 2, 'price' => 7000, 'label' => '2,000매 초과 (2박스)'],
        ],
    ],

    // ===== 대봉투 (envelope_large) =====
    // 500매까지 1박스, 3,500원
    'envelope_large' => [
        'label' => '대봉투',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,   'max' => 500,         'box' => 1, 'price' => 3500, 'label' => '500매 이하 (1박스)'],
            ['min' => 501, 'max' => PHP_INT_MAX, 'box' => 2, 'price' => 7000, 'label' => '500매 초과 (2박스)'],
        ],
    ],

    // ===== 전단지 A4 (inserted_a4) =====
    // 합판인쇄 표준 (80g~100g). 1연 = 4,000매. A3박스. 로젠A4특약.
    'inserted_a4' => [
        'label' => '전단지 A4',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,    'max' => 2000,        'box' => 1, 'price' => 3500,  'label' => '0.5연 (2,000매) A4특약'],
            ['min' => 2001, 'max' => 4000,        'box' => 1, 'price' => 6000,  'label' => '1연 (4,000매)'],
            ['min' => 4001, 'max' => 8000,        'box' => 2, 'price' => 12000, 'label' => '2연 (8,000매)'],
            ['min' => 8001, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 18000, 'label' => '3연+ (12,000매+)'],
        ],
    ],

    // ===== 전단지 B5/16절 (inserted_b5) =====
    // 1연 = 8,000매(B5). 8절박스. 로젠16절특약.
    'inserted_b5' => [
        'label' => '전단지 16절 (B5)',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,    'max' => 4000,        'box' => 1, 'price' => 3500,  'label' => '0.5연 (4,000매) 16절특약'],
            ['min' => 4001, 'max' => 8000,        'box' => 2, 'price' => 7000,  'label' => '1연 (8,000매)'],
            ['min' => 8001, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 10500, 'label' => '2연+ (16,000매+)'],
        ],
    ],

    // ===== 전단지 A5 (inserted_a5) =====
    // 1연 = 8,000매(A5). A3박스.
    'inserted_a5' => [
        'label' => '전단지 A5',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,    'max' => 4000,        'box' => 1, 'price' => 3500,  'label' => '0.5연 (4,000매)'],
            ['min' => 4001, 'max' => 8000,        'box' => 1, 'price' => 6000,  'label' => '1연 (8,000매)'],
            ['min' => 8001, 'max' => PHP_INT_MAX, 'box' => 2, 'price' => 12000, 'label' => '2연+ (16,000매+)'],
        ],
    ],

    // ===== 전단지 B6/32절 (inserted_b6) =====
    // 1연 = 16,000매(B6). 8절박스. 16절특약 귀속.
    'inserted_b6' => [
        'label' => '전단지 32절 (B6)',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,     'max' => 8000,        'box' => 1, 'price' => 3500,  'label' => '0.5연 (8,000매) 16절특약'],
            ['min' => 8001,  'max' => 16000,       'box' => 2, 'price' => 7000,  'label' => '1연 (16,000매)'],
            ['min' => 16001, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 10500, 'label' => '2연+ (32,000매+)'],
        ],
    ],

    // ===== 전단지 A3 (inserted_a3) =====
    // 합판 90g. 1연 = 2,000매. 1박스 6,000원.
    'inserted_a3' => [
        'label' => '전단지 A3',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,    'max' => 1000,        'box' => 1, 'price' => 3500,  'label' => '0.5연 (1,000매)'],
            ['min' => 1001, 'max' => 2000,        'box' => 1, 'price' => 6000,  'label' => '1연 (2,000매)'],
            ['min' => 2001, 'max' => 4000,        'box' => 2, 'price' => 12000, 'label' => '2연 (4,000매)'],
            ['min' => 4001, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 18000, 'label' => '3연+ (6,000매+)'],
        ],
    ],

    // ===== 전단지 B4/8절 (inserted_b4) =====
    // 합판 90g. 1연 = 4,000매. 0.5연(2,000매) 1박스 3,500원. 1연(4,000매) 2박스 7,000원.
    'inserted_b4' => [
        'label' => '전단지 8절 (B4)',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,    'max' => 2000,        'box' => 1, 'price' => 3500,  'label' => '0.5연 (2,000매)'],
            ['min' => 2001, 'max' => 4000,        'box' => 2, 'price' => 7000,  'label' => '1연 (4,000매)'],
            ['min' => 4001, 'max' => PHP_INT_MAX, 'box' => 4, 'price' => 14000, 'label' => '2연+ (8,000매+)'],
        ],
    ],

    // ===== 전단지 대형/고평량 (inserted_large) — 전화 요망 =====
    // B4, A3, 4절, 국2절, 또는 150g 이상 두꺼운 용지
    'inserted_large' => [
        'label' => '전단지 (대형/고평량)',
        'prepaid_type' => 'call_required',
        'rules' => [],
    ],

    // ===== 포스터 (littleprint) =====
    'littleprint' => [
        'label' => '포스터',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,    'max' => 1000,        'box' => 1, 'price' => 3000, 'label' => '1,000매 이하'],
            ['min' => 1001, 'max' => PHP_INT_MAX, 'box' => 2, 'price' => 4000, 'label' => '1,000매 초과'],
        ],
    ],

    // ===== NCR양식지 (ncrflambeau) =====
    // ⚠️ min/max = 권수 (1권 = 50조)
    'ncrflambeau' => [
        'label' => 'NCR양식지',
        'prepaid_type' => 'auto',
        'rules' => [
            ['min' => 0,  'max' => 30,           'box' => 1, 'price' => 3000, 'label' => '30권 이하'],
            ['min' => 31, 'max' => PHP_INT_MAX,  'box' => 2, 'price' => 4000, 'label' => '30권 초과'],
        ],
    ],

    // ===== 카다록 (cadarok) — 전화 요망 =====
    // 무거움/부피 큼 → 택배비 전화 문의 또는 다마스
    'cadarok' => [
        'label' => '카다록',
        'prepaid_type' => 'call_required',
        'rules' => [],
    ],

    // ===== 자석스티커 (msticker) — 착불 전용 =====
    // 무조건 착불 (무게/부피 → 택배사 수취인 직접 청구)
    'msticker' => [
        'label' => '자석스티커',
        'prepaid_type' => 'cod_only',
        'rules' => [],
    ],

    // ===== 기본 (매칭 안 되는 제품) — 전화 요망 =====
    'default' => [
        'label' => '기타',
        'prepaid_type' => 'call_required',
        'rules' => [],
    ],
];
