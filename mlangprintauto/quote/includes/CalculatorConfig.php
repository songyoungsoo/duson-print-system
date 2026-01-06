<?php
/**
 * 견적서 통합 계산기 설정
 *
 * 11개 품목의 설정을 중앙 관리합니다.
 * - 제품 목록 및 표시명
 * - 수량 필드명 (mesu vs MY_amount)
 * - 단위 (매/연/권/부)
 * - 가격 계산 타입 (formula vs table)
 * - 폼 필드 정의
 *
 * @author Claude Code
 * @version 2.0
 * @date 2026-01-06
 */

class CalculatorConfig {
    /**
     * 제품 코드 → 한글명 매핑
     */
    const PRODUCTS = [
        'sticker' => '스티커',
        'namecard' => '명함',
        'inserted' => '전단지',
        'envelope' => '봉투',
        'msticker' => '자석스티커',
        'cadarok' => '카다록',
        'littleprint' => '포스터',
        'merchandisebond' => '상품권',
        'ncrflambeau' => 'NCR양식',
        'leaflet' => '리플렛'
    ];

    /**
     * 제품별 수량 필드명
     * mesu: 스티커, 자석스티커 (매수)
     * MY_amount: 나머지 제품 (연 또는 매수)
     */
    const QUANTITY_FIELDS = [
        'sticker' => 'mesu',
        'msticker' => 'mesu',
        'inserted' => 'MY_amount',     // 연 단위 (0.5, 1, 1.5...)
        'namecard' => 'MY_amount',
        'envelope' => 'MY_amount',
        'cadarok' => 'MY_amount',
        'littleprint' => 'MY_amount',
        'merchandisebond' => 'MY_amount',
        'ncrflambeau' => 'MY_amount',
        'leaflet' => 'MY_amount'
    ];

    /**
     * 제품별 수량 단위
     */
    const UNITS = [
        'sticker' => '매',
        'msticker' => '매',
        'inserted' => '연',
        'namecard' => '매',
        'envelope' => '매',
        'cadarok' => '부',
        'littleprint' => '매',
        'merchandisebond' => '매',
        'ncrflambeau' => '권',
        'leaflet' => '권'
    ];

    /**
     * 가격 계산 타입
     * formula: 수식 기반 (가로×세로×수량×요율)
     * table: DB 테이블 조회
     */
    const CALCULATION_TYPES = [
        'sticker' => 'formula',
        'msticker' => 'formula',
        'inserted' => 'table',
        'namecard' => 'table',
        'envelope' => 'table',
        'cadarok' => 'table',
        'littleprint' => 'table',
        'merchandisebond' => 'table',
        'ncrflambeau' => 'table',
        'leaflet' => 'table'
    ];

    /**
     * 제품별 DB 테이블명
     */
    const DB_TABLES = [
        'sticker' => null,  // 수식 계산이므로 테이블 없음
        'msticker' => null,
        'inserted' => 'mlangprintauto_inserted',
        'namecard' => 'mlangprintauto_namecard',
        'envelope' => 'mlangprintauto_envelope',
        'cadarok' => 'mlangprintauto_cadarok',
        'littleprint' => 'mlangprintauto_littleprint',
        'merchandisebond' => 'mlangprintauto_merchandisebond',
        'ncrflambeau' => 'mlangprintauto_ncrflambeau',
        'leaflet' => 'mlangprintauto_leaflet'
    ];

    /**
     * 모든 제품 목록 반환
     *
     * @return array [product_code => product_name]
     */
    public static function getAllProducts() {
        return self::PRODUCTS;
    }

    /**
     * 제품별 수량 필드명 반환
     *
     * @param string $productType 제품 코드
     * @return string 'mesu' | 'MY_amount'
     */
    public static function getQuantityField($productType) {
        return self::QUANTITY_FIELDS[$productType] ?? 'MY_amount';
    }

    /**
     * 제품별 단위 반환
     *
     * @param string $productType 제품 코드
     * @return string '매' | '연' | '권' | '부'
     */
    public static function getUnit($productType) {
        return self::UNITS[$productType] ?? '매';
    }

    /**
     * 가격 계산 타입 반환
     *
     * @param string $productType 제품 코드
     * @return string 'formula' | 'table'
     */
    public static function getPriceCalculationType($productType) {
        return self::CALCULATION_TYPES[$productType] ?? 'table';
    }

    /**
     * DB 테이블명 반환
     *
     * @param string $productType 제품 코드
     * @return string|null 테이블명 또는 null
     */
    public static function getDBTable($productType) {
        return self::DB_TABLES[$productType] ?? null;
    }

    /**
     * 제품별 폼 필드 정의 반환
     *
     * @param string $productType 제품 코드
     * @return array 필드 정의 배열
     */
    public static function getFormFields($productType) {
        $fields = [];

        switch ($productType) {
            case 'sticker':
            case 'msticker':
                $fields = [
                    [
                        'name' => 'jong',
                        'label' => '종류',
                        'type' => 'select',
                        'options' => self::getStickerJongOptions()
                    ],
                    [
                        'name' => 'domusong',
                        'label' => '도무송',
                        'type' => 'select',
                        'options' => self::getStickerDomusongOptions()
                    ],
                    [
                        'name' => 'garo',
                        'label' => '가로 (mm)',
                        'type' => 'number',
                        'min' => 10,
                        'max' => 1000
                    ],
                    [
                        'name' => 'sero',
                        'label' => '세로 (mm)',
                        'type' => 'number',
                        'min' => 10,
                        'max' => 1000
                    ],
                    [
                        'name' => 'mesu',
                        'label' => '수량 (매)',
                        'type' => 'select',
                        'options' => self::getStickerQuantityOptions()
                    ],
                    [
                        'name' => 'uhyung',
                        'label' => '유형',
                        'type' => 'select',
                        'options' => [
                            '0' => '인쇄만',
                            '1' => '디자인+인쇄'
                        ]
                    ]
                ];
                break;

            case 'namecard':
                $fields = [
                    [
                        'name' => 'MY_type',
                        'label' => '스타일',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드
                    ],
                    [
                        'name' => 'Section',
                        'label' => '용지',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드
                    ],
                    [
                        'name' => 'POtype',
                        'label' => '인쇄 색상',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드
                    ],
                    [
                        'name' => 'MY_amount',
                        'label' => '수량 (매)',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드
                    ]
                ];
                break;

            case 'inserted':
                $fields = [
                    [
                        'name' => 'MY_type',
                        'label' => '스타일',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드
                    ],
                    [
                        'name' => 'PN_type',
                        'label' => '규격',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드
                    ],
                    [
                        'name' => 'MY_Fsd',
                        'label' => '용지',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드
                    ],
                    [
                        'name' => 'MY_amount',
                        'label' => '수량 (연)',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드 (0.5연~100연)
                    ],
                    [
                        'name' => 'POtype',
                        'label' => '인쇄 색상',
                        'type' => 'select',
                        'options' => []  // DB에서 동적 로드
                    ]
                ];
                break;

            // 기타 제품들은 유사한 구조
            default:
                $fields = [
                    [
                        'name' => 'MY_type',
                        'label' => '종류',
                        'type' => 'select',
                        'options' => []
                    ],
                    [
                        'name' => 'MY_amount',
                        'label' => '수량',
                        'type' => 'select',
                        'options' => []
                    ]
                ];
                break;
        }

        return $fields;
    }

    /**
     * 스티커 종류 옵션
     */
    private static function getStickerJongOptions() {
        return [
            'jil유포지' => '유포지',
            'jil아트지' => '아트지',
            'jil크라프트지' => '크라프트지',
            'jil투명' => '투명',
            'jil은무지' => '은무지',
            'jil금무지' => '금무지'
        ];
    }

    /**
     * 스티커 도무송 옵션
     */
    private static function getStickerDomusongOptions() {
        return [
            '0사각' => '사각',
            '1원형' => '원형',
            '2타원형' => '타원형',
            '3귀도라지' => '귀도라지'
        ];
    }

    /**
     * 스티커 수량 옵션
     */
    private static function getStickerQuantityOptions() {
        return [
            '100' => '100',
            '200' => '200',
            '300' => '300',
            '500' => '500',
            '700' => '700',
            '1000' => '1000',
            '1500' => '1500',
            '2000' => '2000',
            '3000' => '3000',
            '5000' => '5000',
            '10000' => '10000'
        ];
    }

    /**
     * 제품 유효성 검사
     *
     * @param string $productType 제품 코드
     * @return bool
     */
    public static function isValidProduct($productType) {
        return isset(self::PRODUCTS[$productType]);
    }

    /**
     * 제품 한글명 반환
     *
     * @param string $productType 제품 코드
     * @return string 한글명
     */
    public static function getProductName($productType) {
        return self::PRODUCTS[$productType] ?? '알 수 없는 제품';
    }
}
