<?php
/**
 * ProductConfig - 제품 관리 시스템 설정
 *
 * 9개 제품의 메타데이터를 중앙에서 관리합니다.
 * - 테이블 정보
 * - 컬럼 매핑
 * - 표시 설정
 * - 검색/필터 설정
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

class ProductConfig {
    /**
     * 전체 제품 설정
     * 9개 제품: inserted, namecard, envelope, sticker, msticker, cadarok, littleprint, merchandisebond, ncrflambeau
     */
    public static $products = [
        'inserted' => [
            'name' => '전단지',
            'table' => 'mlangprintauto_inserted',
            'trans_table' => 'inserted',
            'category' => 'complex',
            'selectors' => 3,
            'selector_labels' => ['인쇄색상', '종이종류', '종이규격'],
            'selector_query_type' => ['BigNo', 'TreeNo', 'BigNo'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'TreeSelect',
                'selector3' => 'Section',
                'quantity' => 'quantity',
                'quantity_display' => 'quantityTwo',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ],
            'has_mesu' => true,
            'display_columns' => ['no', 'style', 'Section', 'quantity', 'quantityTwo', 'money'],
            'search_fields' => ['style', 'Section'],
            'sortable' => ['no', 'money', 'quantity'],
            'filters' => ['style', 'Section']
        ],

        'namecard' => [
            'name' => '명함',
            'table' => 'mlangprintauto_namecard',
            'trans_table' => 'NameCard',
            'category' => 'standard',
            'selectors' => 2,
            'selector_labels' => ['종류', '재질'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ],
            'display_columns' => ['no', 'style', 'Section', 'quantity', 'money'],
            'search_fields' => ['style', 'Section'],
            'sortable' => ['no', 'money', 'quantity'],
            'filters' => ['style']
        ],

        'envelope' => [
            'name' => '봉투',
            'table' => 'mlangprintauto_envelope',
            'trans_table' => 'envelope',
            'category' => 'standard',
            'selectors' => 2,
            'selector_labels' => ['규격', '용지'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ],
            'display_columns' => ['no', 'style', 'Section', 'quantity', 'money'],
            'search_fields' => ['style', 'Section'],
            'sortable' => ['no', 'money', 'quantity'],
            'filters' => ['style']
        ],

        'sticker' => [
            'name' => '스티커',
            'table' => 'mlangprintauto_sticker',
            'trans_table' => 'sticker',
            'category' => 'individual',
            'selectors' => 4,
            'selector_labels' => ['종류', '가로', '세로', '매수'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'jong',
                'selector2' => 'garo',
                'selector3' => 'sero',
                'selector4' => 'mesu',
                'price_single' => 'money'
            ],
            'display_columns' => ['no', 'jong', 'garo', 'sero', 'mesu', 'money'],
            'search_fields' => ['jong'],
            'sortable' => ['no', 'money', 'mesu'],
            'filters' => ['jong']
        ],

        'msticker' => [
            'name' => '자석스티커',
            'table' => 'mlangprintauto_msticker',
            'trans_table' => 'msticker',
            'category' => 'standard',
            'selectors' => 2,
            'selector_labels' => ['종류', '규격'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ],
            'display_columns' => ['no', 'style', 'Section', 'quantity', 'money'],
            'search_fields' => ['style', 'Section'],
            'sortable' => ['no', 'money', 'quantity'],
            'filters' => ['style']
        ],

        'cadarok' => [
            'name' => '카다록',
            'table' => 'mlangprintauto_cadarok',
            'trans_table' => 'cadarok',
            'category' => 'standard',
            'selectors' => 3,
            'selector_labels' => ['구분', '규격', '종이종류'],
            'selector_query_type' => ['BigNo', 'BigNo', 'TreeNo'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'selector3' => 'TreeSelect',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ],
            'display_columns' => ['no', 'style', 'Section', 'quantity', 'money'],
            'search_fields' => ['style', 'Section'],
            'sortable' => ['no', 'money', 'quantity'],
            'filters' => ['style']
        ],

        'littleprint' => [
            'name' => '포스터',
            'table' => 'mlangprintauto_littleprint',
            'trans_table' => 'LittlePrint',
            'category' => 'standard',
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
            ],
            'display_columns' => ['no', 'style', 'Section', 'quantity', 'money'],
            'search_fields' => ['style', 'Section'],
            'sortable' => ['no', 'money', 'quantity'],
            'filters' => ['style']
        ],

        'merchandisebond' => [
            'name' => '상품권',
            'table' => 'mlangprintauto_merchandisebond',
            'trans_table' => 'MerchandiseBond',
            'category' => 'standard',
            'selectors' => 2,
            'selector_labels' => ['종류', '후가공'],
            'columns' => [
                'id' => 'no',
                'selector1' => 'style',
                'selector2' => 'Section',
                'quantity' => 'quantity',
                'price_single' => 'money',
                'price_double' => 'DesignMoney'
            ],
            'display_columns' => ['no', 'style', 'Section', 'quantity', 'money'],
            'search_fields' => ['style', 'Section'],
            'sortable' => ['no', 'money', 'quantity'],
            'filters' => ['style']
        ],

        'ncrflambeau' => [
            'name' => 'NCR양식',
            'table' => 'mlangprintauto_ncrflambeau',
            'trans_table' => 'NcrFlambeau',
            'category' => 'special',
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
            ],
            'display_columns' => ['no', 'style', 'Section', 'quantity', 'money'],
            'search_fields' => ['style', 'Section'],
            'sortable' => ['no', 'money', 'quantity'],
            'filters' => ['style']
        ]
    ];

    /**
     * 컬럼 한글명 매핑
     */
    public static $column_labels = [
        'no' => '번호',
        'style' => '종류',
        'Section' => '규격',
        'TreeSelect' => '종이종류',
        'quantity' => '수량',
        'quantityTwo' => '매수',
        'money' => '가격',
        'DesignMoney' => '디자인비',
        'jong' => '종류',
        'garo' => '가로',
        'sero' => '세로',
        'mesu' => '매수'
    ];

    /**
     * 특정 제품 설정 가져오기
     *
     * @param string $product_key 제품 코드
     * @return array|null 제품 설정
     */
    public static function getConfig($product_key) {
        return self::$products[$product_key] ?? null;
    }

    /**
     * 전체 제품 목록 가져오기
     *
     * @return array 제품 목록
     */
    public static function getAllProducts() {
        $result = [];
        foreach (self::$products as $key => $config) {
            $result[] = [
                'key' => $key,
                'name' => $config['name'],
                'category' => $config['category'],
                'selectors' => $config['selectors'],
                'table' => $config['table']
            ];
        }
        return $result;
    }

    /**
     * 카테고리별 제품 목록
     *
     * @param string $category 카테고리 (standard|complex|individual|special)
     * @return array 제품 목록
     */
    public static function getProductsByCategory($category) {
        $result = [];
        foreach (self::$products as $key => $config) {
            if ($config['category'] === $category) {
                $result[$key] = $config;
            }
        }
        return $result;
    }

    /**
     * 제품 키 검증
     *
     * @param string $product_key 제품 코드
     * @return bool 유효 여부
     */
    public static function isValidProduct($product_key) {
        return isset(self::$products[$product_key]);
    }

    /**
     * 제품 테이블명 가져오기
     *
     * @param string $product_key 제품 코드
     * @return string|null 테이블명
     */
    public static function getTableName($product_key) {
        $config = self::getConfig($product_key);
        return $config['table'] ?? null;
    }

    /**
     * 표시 컬럼 가져오기
     *
     * @param string $product_key 제품 코드
     * @return array 표시 컬럼 배열
     */
    public static function getDisplayColumns($product_key) {
        $config = self::getConfig($product_key);
        return $config['display_columns'] ?? [];
    }

    /**
     * 검색 가능 필드 가져오기
     *
     * @param string $product_key 제품 코드
     * @return array 검색 필드 배열
     */
    public static function getSearchFields($product_key) {
        $config = self::getConfig($product_key);
        return $config['search_fields'] ?? [];
    }

    /**
     * 정렬 가능 필드 가져오기
     *
     * @param string $product_key 제품 코드
     * @return array 정렬 필드 배열
     */
    public static function getSortableFields($product_key) {
        $config = self::getConfig($product_key);
        return $config['sortable'] ?? [];
    }

    /**
     * 컬럼 한글명 가져오기
     *
     * @param string $column_key 컬럼 키
     * @return string 한글명
     */
    public static function getColumnLabel($column_key) {
        return self::$column_labels[$column_key] ?? $column_key;
    }

    /**
     * 제품 카테고리 가져오기
     *
     * @param string $product_key 제품 코드
     * @return string 카테고리
     */
    public static function getCategory($product_key) {
        $config = self::getConfig($product_key);
        return $config['category'] ?? 'unknown';
    }
}
