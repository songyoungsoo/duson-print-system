<?php
/**
 * 품목 관리 시스템 설정
 * 두손기획인쇄 - 8개 품목 통합 관리
 */

class ProductConfig {
    /**
     * 전체 품목 설정
     * - 2개 셀렉터: 명함, 봉투, 쿠폰, 스티커
     * - 3개 셀렉터: 전단지, 양식지, 카다록, 카다록2, 포스터
     */
    public static $products = [
        'namecard' => [
            'name' => '명함',
            'table' => 'mlangprintauto_namecard',
            'trans_table' => 'NameCard',
            'selectors' => 2,
            'selector_labels' => ['종류', '재질'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'envelope' => [
            'name' => '봉투',
            'table' => 'mlangprintauto_envelope',
            'trans_table' => 'envelope',
            'selectors' => 2,
            'selector_labels' => ['규격', '용지'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'merchandisebond' => [
            'name' => '쿠폰',
            'table' => 'mlangprintauto_merchandisebond',
            'trans_table' => 'MerchandiseBond',
            'selectors' => 2,
            'selector_labels' => ['종류', '후가공'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'sticker' => [
            'name' => '스티커',
            'table' => 'mlangprintauto_sticker',
            'trans_table' => 'sticker',
            'selectors' => 2,
            'selector_labels' => ['스티커종류', '규격'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'inserted' => [
            'name' => '전단지',
            'table' => 'mlangprintauto_inserted',
            'trans_table' => 'inserted',
            'selectors' => 3,
            'selector_labels' => ['인쇄색상', '종이종류', '종이규격'],
            'selector_query_type' => ['BigNo', 'TreeNo', 'BigNo'], // 1단계: BigNo=0, 2단계: TreeNo=1단계no, 3단계: BigNo=1단계no
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',        // 인쇄색상
                'selector2' => 'TreeSelect',   // 종이종류
                'selector3' => 'Section',      // 종이규격
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'ncrflambeau' => [
            'name' => '양식지',
            'table' => 'mlangprintauto_ncrflambeau',
            'trans_table' => 'NcrFlambeau',
            'selectors' => 3,
            'selector_labels' => ['구분', '규격', '종이종류'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'selector3' => 'TreeSelect',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'cadarok' => [
            'name' => '카다록',
            'table' => 'mlangprintauto_cadarok',
            'trans_table' => 'cadarok',
            'selectors' => 3,
            'selector_labels' => ['구분', '규격', '종이종류'],
            'selector_query_type' => ['BigNo', 'BigNo', 'TreeNo'], // 1단계: BigNo=0, 2단계: BigNo=1단계no, 3단계: TreeNo=1단계no
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',      // 규격 (BigNo 기반)
                'selector3' => 'TreeSelect',   // 종이종류 (TreeNo 기반)
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'cadaroktwo' => [
            'name' => '카다록2',
            'table' => 'mlangprintauto_cadaroktwo',
            'trans_table' => 'cadarokTwo',
            'selectors' => 3,
            'selector_labels' => ['구분', '규격', '종이종류'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'selector3' => 'TreeSelect',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'littleprint' => [
            'name' => '포스터(소량인쇄)',
            'table' => 'mlangprintauto_littleprint',
            'trans_table' => 'LittlePrint',
            'selectors' => 3,
            'selector_labels' => ['종류', '종이종류', '종이규격'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'TreeSelect',
                'selector3' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ]
        ],

        'leaflet' => [
            'name' => '리플렛',
            'table' => 'mlangprintauto_inserted', // inserted 테이블 공유 (전단지 가격 활용)
            'fold_table' => 'mlangprintauto_leaflet_fold', // 접지방식 추가 금액 테이블
            'trans_table' => 'Leaflet',
            'selectors' => 4, // 3개 + 접지방식
            'selector_labels' => ['인쇄색상', '종이종류', '종이규격', '접지방식'],
            'selector_query_type' => ['BigNo', 'TreeNo', 'BigNo', 'FoldType'], // 접지방식은 별도 테이블
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',        // 인쇄색상
                'selector2' => 'TreeSelect',   // 종이종류
                'selector3' => 'Section',      // 종이규격
                'selector4' => 'fold_type',    // 접지방식 (leaflet_fold 테이블)
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney',
                'fold_price' => 'additional_price' // 접지 추가 금액
            ],
            'special_pricing' => true, // 특수 가격 계산 (기본가격 + 접지추가금)
            'description' => '전단지 가격 + 접지방식 추가 금액으로 계산'
        ]
    ];

    /**
     * 특정 품목 설정 가져오기
     */
    public static function getConfig($product_key) {
        return self::$products[$product_key] ?? null;
    }

    /**
     * 전체 품목 목록 가져오기
     */
    public static function getAllProducts() {
        $result = [];
        foreach (self::$products as $key => $config) {
            $result[] = [
                'key' => $key,
                'name' => $config['name'],
                'selectors' => $config['selectors']
            ];
        }
        return $result;
    }

    /**
     * 품목 키 검증
     */
    public static function isValidProduct($product_key) {
        return isset(self::$products[$product_key]);
    }
}
?>
