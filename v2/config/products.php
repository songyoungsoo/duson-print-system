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
            // 명함 프리미엄 옵션
            'premium_options' => [
                ['name' => 'foil', 'label' => '박가공', 'type' => 'checkbox'],
                ['name' => 'numbering', 'label' => '넘버링', 'type' => 'checkbox'],
                ['name' => 'round_corner', 'label' => '귀도리', 'type' => 'checkbox'],
                ['name' => 'embossing', 'label' => '형압', 'type' => 'checkbox'],
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
            'extra_options' => [
                ['name' => 'tape', 'label' => '양면테이프', 'type' => 'checkbox'],
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
        'ui_type' => 'dropdown_3level',
        'ui_config' => [
            'levels' => [
                ['name' => 'style', 'label' => '인쇄도수', 'source' => 'cate_level1'],
                ['name' => 'TreeSelect', 'label' => '용지/규격', 'source' => 'cate_level2', 'depends_on' => 'style'],
                ['name' => 'quantity', 'label' => '수량', 'source' => 'price_table', 'depends_on' => 'TreeSelect'],
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
