<?php
declare(strict_types=1);

/**
 * Product Configuration
 * 
 * UI Types:
 * - dropdown_4level: style → TreeSelect(종이) → Section(규격) → quantity (전단지)
 * - dropdown_3level: style → TreeSelect → quantity (명함, 봉투, 카다록 등)
 * - dropdown_ncr: MY_type → MY_Fsd → PN_type → quantity (NCR양식지)
 * - formula_input: 재질 + 가로×세로×수량 + 도무송 직접입력 (스티커)
 * 
 * Dropdown Hierarchy (mlangprintauto_transactioncate):
 * - Level 1: BigNo = 0 (최상위 style)
 * - Level 2: TreeNo = Level1.no (하위 카테고리)
 * - Level 3: BigNo = Level2.no (세부 옵션)
 */

return [
    'sticker_new' => [
        'name' => '스티커',
        'folder' => 'sticker_new',
        'unit_code' => 'S',
        'unit_name' => '매',
        'has_template' => true,
        'icon' => 'tag',
        'ui_type' => 'formula_input',
        'ui_config' => [
            'materials' => [
                ['value' => 'jil 아트유광코팅', 'label' => '아트지유광'],
                ['value' => 'jil 아트무광코팅', 'label' => '아트지무광'],
                ['value' => 'jil 아트비코팅', 'label' => '아트지비코팅'],
                ['value' => 'jka 강접아트유광코팅', 'label' => '강접아트유광'],
                ['value' => 'cka 초강접아트코팅', 'label' => '초강접아트유광'],
                ['value' => 'cka 초강접아트비코팅', 'label' => '초강접아트비코팅'],
                ['value' => 'jsp 유포지', 'label' => '유포지'],
                ['value' => 'jsp 은데드롱', 'label' => '은데드롱'],
                ['value' => 'jsp 투명스티커', 'label' => '투명스티커'],
                ['value' => 'jil 모조비코팅', 'label' => '모조지비코팅'],
                ['value' => 'jsp 크라프트지', 'label' => '크라프트스티커'],
            ],
            'quantities' => [500, 1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000],
            'shapes' => [
                ['value' => '00000 사각', 'label' => '기본사각'],
                ['value' => '08000 사각도무송', 'label' => '사각도무송'],
                ['value' => '08000 귀돌', 'label' => '귀돌이(라운드)'],
                ['value' => '08000 원형', 'label' => '원형'],
                ['value' => '08000 타원', 'label' => '타원형'],
                ['value' => '19000 복잡', 'label' => '모양도무송'],
            ],
        ],
    ],
    'inserted' => [
        'name' => '전단지',
        'folder' => 'inserted',
        'unit_code' => 'R',
        'unit_name' => '연',
        'has_template' => true,
        'icon' => 'file-text',
        // 전단지는 4단계 드롭다운
        'ui_type' => 'dropdown_4level',
        'ui_config' => [
            'levels' => [
                ['name' => 'style', 'label' => '인쇄도수', 'source' => 'cate_level1'],
                ['name' => 'TreeSelect', 'label' => '용지', 'source' => 'cate_level2', 'depends_on' => 'style'],
                ['name' => 'Section', 'label' => '규격', 'source' => 'cate_level3', 'depends_on' => 'TreeSelect'],
                ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'Section'],
            ],
            'price_table' => 'mlangprintauto_inserted',
        ],
    ],
    'namecard' => [
        'name' => '명함',
        'folder' => 'namecard',
        'unit_code' => 'S',
        'unit_name' => '매',
        'has_template' => false,
        'icon' => 'credit-card',
        // 명함은 2단계 드롭다운 (style → Section) + 프리미엄 옵션
        // TreeSelect 사용 안함 - Section이 재질(용지)
        'ui_type' => 'dropdown_2level',
        'ui_config' => [
            'levels' => [
                ['name' => 'style', 'label' => '명함종류', 'source' => 'cate_level1'],
                ['name' => 'Section', 'label' => '용지(재질)', 'source' => 'cate_level3', 'depends_on' => 'style'],
                ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'Section'],
            ],
            'price_table' => 'mlangprintauto_namecard',
        ],
        // 명함 프리미엄 옵션 (V1 기반)
        'premium_options' => [
            'foil' => [
                'name' => 'foil',
                'label' => '박',
                'type' => 'select',
                'base_qty' => 500,
                'base_price' => 30000,
                'unit_price' => 60,
                'options' => [
                    ['value' => 'gold_matte', 'label' => '금박무광'],
                    ['value' => 'gold_gloss', 'label' => '금박유광'],
                    ['value' => 'silver_matte', 'label' => '은박무광'],
                    ['value' => 'silver_gloss', 'label' => '은박유광'],
                    ['value' => 'blue_gloss', 'label' => '청박유광'],
                    ['value' => 'red_gloss', 'label' => '적박유광'],
                    ['value' => 'green_gloss', 'label' => '녹박유광'],
                    ['value' => 'black_gloss', 'label' => '먹박유광'],
                ],
                'note' => '박(20mm×20mm 이하)',
            ],
            'numbering' => [
                'name' => 'numbering',
                'label' => '넘버링',
                'type' => 'select',
                'base_qty' => 500,
                'base_price' => 60000,
                'unit_price' => 120,
                'options' => [
                    ['value' => 'single', 'label' => '1개', 'extra_per_1000' => 0],
                    ['value' => 'double', 'label' => '2개', 'extra_per_1000' => 15000],
                ],
            ],
            'perforation' => [
                'name' => 'perforation',
                'label' => '미싱',
                'type' => 'select',
                'base_qty' => 500,
                'base_price' => 20000,
                'unit_price' => 25,
                'options' => [
                    ['value' => 'single', 'label' => '1개', 'extra_per_1000' => 0],
                    ['value' => 'double', 'label' => '2개', 'extra_per_1000' => 15000],
                ],
            ],
            'rounding' => [
                'name' => 'rounding',
                'label' => '귀돌이',
                'type' => 'checkbox',
                'base_qty' => 500,
                'base_price' => 10000,
                'unit_price' => 24,
            ],
            'creasing' => [
                'name' => 'creasing',
                'label' => '오시',
                'type' => 'select',
                'base_qty' => 500,
                'base_price' => 20000,
                'unit_price' => 25,
                'options' => [
                    ['value' => '1line', 'label' => '1줄', 'extra_per_1000' => 0],
                    ['value' => '2line', 'label' => '2줄', 'extra_per_1000' => 0],
                    ['value' => '3line', 'label' => '3줄', 'extra_per_1000' => 15000],
                ],
            ],
        ],
    ],
    'envelope' => [
        'name' => '봉투',
        'folder' => 'envelope',
        'unit_code' => 'S',
        'unit_name' => '매',
        'has_template' => false,
        'icon' => 'mail',
        // 봉투는 2단계: style → Section (명함과 동일 구조)
        'ui_type' => 'dropdown_2level',
        'ui_config' => [
            'levels' => [
                ['name' => 'style', 'label' => '봉투종류', 'source' => 'cate_level1'],
                ['name' => 'Section', 'label' => '용지/규격', 'source' => 'cate_level3', 'depends_on' => 'style'],
                ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'Section'],
            ],
            'price_table' => 'mlangprintauto_envelope',
        ],
        // 봉투 추가 옵션
        'premium_options' => [
            'tape' => [
                'name' => 'tape',
                'label' => '양면테이프',
                'type' => 'checkbox',
                'base_qty' => 1000,
                'base_price' => 5000,
                'unit_price' => 5,
            ],
        ],
    ],
    'cadarok' => [
        'name' => '카다록/리플렛',
        'folder' => 'cadarok',
        'unit_code' => 'B',
        'unit_name' => '부',
        'has_template' => false,
        'icon' => 'book-open',
        // 카다록은 2단계: style → Section (명함과 동일 구조)
        'ui_type' => 'dropdown_2level',
        'ui_config' => [
            'levels' => [
                ['name' => 'style', 'label' => '종류', 'source' => 'cate_level1'],
                ['name' => 'Section', 'label' => '규격/페이지', 'source' => 'cate_level3', 'depends_on' => 'style'],
                ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'Section'],
            ],
            'price_table' => 'mlangprintauto_cadarok',
        ],
    ],
    'littleprint' => [
        'name' => '포스터/소량인쇄',
        'folder' => 'littleprint',
        'unit_code' => 'P',
        'unit_name' => '장',
        'has_template' => false,
        'icon' => 'image',
        // littleprint는 4단계: style(종류) → TreeSelect(용지) → Section(규격) → quantity
        'ui_type' => 'dropdown_4level',
        'ui_config' => [
            'levels' => [
                ['name' => 'style', 'label' => '종류', 'source' => 'cate_level1'],
                ['name' => 'TreeSelect', 'label' => '용지', 'source' => 'cate_level2', 'depends_on' => 'style'],
                ['name' => 'Section', 'label' => '규격', 'source' => 'cate_level3', 'depends_on' => 'TreeSelect'],
                ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'Section'],
            ],
            'price_table' => 'mlangprintauto_littleprint',
        ],
    ],
     'merchandisebond' => [
         'name' => '상품권',
         'folder' => 'merchandisebond',
         'unit_code' => 'S',
         'unit_name' => '매',
         'has_template' => false,
         'icon' => 'ticket',
         // 상품권은 2단계: style(상품권종류) → Section(옵션)
         'ui_type' => 'dropdown_2level',
         'ui_config' => [
             'levels' => [
                 ['name' => 'style', 'label' => '상품권종류', 'source' => 'cate_level1'],
                 ['name' => 'Section', 'label' => '옵션', 'source' => 'cate_level3', 'depends_on' => 'style'],
                 ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'Section'],
             ],
             'price_table' => 'mlangprintauto_merchandisebond',
         ],
         // 상품권 프리미엄 옵션 (V1 기반)
         'premium_options' => [
             'foil' => [
                 'name' => 'foil',
                 'label' => '박',
                 'type' => 'select',
                 'base_qty' => 500,
                 'base_price' => 30000,
                 'unit_price' => 60,
                 'options' => [
                     ['value' => 'gold_matte', 'label' => '금박무광'],
                     ['value' => 'gold_gloss', 'label' => '금박유광'],
                     ['value' => 'silver_matte', 'label' => '은박무광'],
                     ['value' => 'silver_gloss', 'label' => '은박유광'],
                     ['value' => 'blue_gloss', 'label' => '청박유광'],
                     ['value' => 'red_gloss', 'label' => '적박유광'],
                     ['value' => 'green_gloss', 'label' => '녹박유광'],
                     ['value' => 'black_gloss', 'label' => '먹박유광'],
                 ],
             ],
             'numbering' => [
                 'name' => 'numbering',
                 'label' => '넘버링',
                 'type' => 'select',
                 'base_qty' => 500,
                 'base_price' => 60000,
                 'unit_price' => 120,
                 'options' => [
                     ['value' => 'single', 'label' => '1개 (4~6자리)', 'extra_per_1000' => 0],
                     ['value' => 'double', 'label' => '2개 (4~6자리)', 'extra_per_1000' => 15000],
                 ],
                 'note' => '넘버링(1~9999)',
             ],
             'perforation' => [
                 'name' => 'perforation',
                 'label' => '미싱',
                 'type' => 'select',
                 'base_qty' => 500,
                 'base_price' => 20000,
                 'unit_price' => 40,
                 'options' => [
                     ['value' => 'horizontal', 'label' => '가로미싱'],
                     ['value' => 'vertical', 'label' => '세로미싱'],
                     ['value' => 'cross', 'label' => '십자미싱', 'base_price' => 30000, 'unit_price' => 60],
                 ],
                 'note' => '미싱선 1줄 기준',
             ],
             'rounding' => [
                 'name' => 'rounding',
                 'label' => '귀돌이',
                 'type' => 'select',
                 'base_qty' => 500,
                 'options' => [
                     ['value' => '4corners', 'label' => '네귀돌이', 'base_price' => 15000, 'unit_price' => 30],
                     ['value' => '2corners', 'label' => '두귀돌이', 'base_price' => 12000, 'unit_price' => 25],
                 ],
             ],
         ],
     ],
    'ncrflambeau' => [
        'name' => 'NCR양식지',
        'folder' => 'ncrflambeau',
        'unit_code' => 'V',
        'unit_name' => '권',
        'has_template' => false,
        'icon' => 'clipboard',
        // NCR은 특수한 드롭다운 구조
        'ui_type' => 'dropdown_ncr',
        'ui_config' => [
            'levels' => [
                ['name' => 'MY_type', 'label' => '매수', 'source' => 'cate_level1'],
                ['name' => 'MY_Fsd', 'label' => '규격', 'source' => 'cate_level2', 'depends_on' => 'MY_type'],
                ['name' => 'PN_type', 'label' => '인쇄도수', 'source' => 'cate_level3', 'depends_on' => 'MY_Fsd'],
                ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'PN_type'],
            ],
            'price_table' => 'mlangprintauto_ncrflambeau',
        ],
        // NCR 추가 옵션
        'premium_options' => [
            'numbering' => [
                'name' => 'numbering',
                'label' => '넘버링',
                'type' => 'checkbox',
                'fixed_price' => 10000,
            ],
            'perforation' => [
                'name' => 'perforation',
                'label' => '미싱',
                'type' => 'checkbox',
                'fixed_price' => 10000,
            ],
        ],
    ],
     'msticker' => [
         'name' => '자석스티커',
         'folder' => 'msticker',
         'unit_code' => 'S',
         'unit_name' => '매',
         'has_template' => false,
         'icon' => 'disc',
         // 자석스티커는 2단계: style(종류) → Section(규격)
         'ui_type' => 'dropdown_2level',
         'ui_config' => [
             'levels' => [
                 ['name' => 'style', 'label' => '자석스티커종류', 'source' => 'cate_level1'],
                 ['name' => 'Section', 'label' => '규격', 'source' => 'cate_level3', 'depends_on' => 'style'],
                 ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'Section'],
             ],
             'price_table' => 'mlangprintauto_msticker',
         ],
     ],
];
