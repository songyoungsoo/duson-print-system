<?php
/**
 * QuoteAdapterInterface - 견적서 품목 어댑터 인터페이스
 *
 * 각 제품(9개)의 계산기 API 파라미터/응답을 QuoteItemPayload로 정규화하는 계약.
 * Phase 2 통합 API, Phase 3 위젯에서 이 인터페이스를 통해 품목을 다룹니다.
 *
 * @since Phase 1 - Standard Interface Layer
 */

require_once __DIR__ . '/QuoteItemPayload.php';

interface QuoteAdapterInterface
{
    /**
     * @return string 제품 유형 식별자 (e.g., 'sticker', 'inserted')
     */
    public function getProductType();

    /**
     * @return string 제품 한글명 (e.g., '스티커', '전단지')
     */
    public function getProductName();

    /**
     * @return string 기본 단위 (e.g., '매', '연')
     */
    public function getDefaultUnit();

    /**
     * 계산기 원본 파라미터 + 가격 응답 → QuoteItemPayload 정규화
     *
     * @param array $calcParams 계산기 원본 파라미터 (MY_type, Section, jong 등)
     * @param array $priceResponse calculate_price_ajax.php 응답
     * @return QuoteItemPayload
     */
    public function normalize(array $calcParams, array $priceResponse);

    /**
     * 규격 문자열 생성 ("Line1\nLine2" 형식)
     *
     * @param array $calcParams
     * @return string
     */
    public function buildSpecification(array $calcParams);

    /**
     * @return string 가격 API URL (e.g., '/mlangprintauto/inserted/calculate_price_ajax.php')
     */
    public function getPriceEndpoint();

    /**
     * @return string HTTP 메서드 ('GET' 또는 'POST')
     */
    public function getPriceMethod();

    /**
     * 폼 파라미터 → 가격 API 파라미터 매핑
     *
     * @param array $formParams 원본 폼 데이터
     * @return array 가격 API에 필요한 파라미터만 추출
     */
    public function mapToPriceParams(array $formParams);
}
