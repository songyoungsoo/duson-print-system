/**
 * 가격 데이터 정규화 어댑터 (JavaScript)
 *
 * 목적: 11개 제품의 서로 다른 가격 필드명을 통일된 형식으로 변환
 * 사용: quotation-modal-common.js, add_to_basket 로직, 가격 계산기
 *
 * 표준 포맷:
 * {
 *   supply: 10000,      // 공급가액 (VAT 제외, INTEGER)
 *   vat: 11000,         // VAT 포함 가격 (INTEGER)
 *   vatAmount: 1000     // VAT 금액
 * }
 */

(function(window) {
    'use strict';

    /**
     * 가격 데이터 정규화 메인 함수
     *
     * @param {Object} rawData - 제품별 가격 데이터 (다양한 필드명)
     * @returns {Object} - 표준 포맷 {supply, vat, vatAmount}
     * @throws {Error} - 가격 필드를 찾을 수 없을 때
     */
    function normalizePriceData(rawData) {
        if (!rawData || typeof rawData !== 'object') {
            throw new Error('Invalid price data: expected object');
        }

        // 1단계: 표준 필드 우선 (Phase 2 이후)
        if (rawData.price_supply !== undefined && rawData.price_vat !== undefined) {
            return {
                supply: normalizeNumber(rawData.price_supply),
                vat: normalizeNumber(rawData.price_vat),
                vatAmount: normalizeNumber(rawData.price_vat_amount || 0)
            };
        }

        // 2단계: 레거시 필드 fallback
        const legacyResult = normalizeLegacyPrice(rawData);
        if (legacyResult) {
            return legacyResult;
        }

        // 3단계: 실패
        console.error('Unknown price format:', rawData);
        throw new Error('Cannot normalize price data: no recognized fields');
    }

    /**
     * 레거시 가격 필드 정규화
     */
    function normalizeLegacyPrice(data) {
        // 전단지/리플렛: Order_PriceForm, Total_PriceForm
        if (data.Order_PriceForm !== undefined) {
            const supply = normalizeNumber(data.Order_PriceForm);
            const vat = normalizeNumber(data.Total_PriceForm || supply * 1.1);
            return {
                supply: supply,
                vat: vat,
                vatAmount: vat - supply
            };
        }

        // 전단지/리플렛: calculated_price (alternative)
        if (data.calculated_price !== undefined) {
            const supply = normalizeNumber(data.calculated_price);
            const vat = normalizeNumber(data.calculated_vat_price || supply * 1.1);
            return {
                supply: supply,
                vat: vat,
                vatAmount: vat - supply
            };
        }

        // 상품권: PriceForm
        if (data.PriceForm !== undefined) {
            const supply = normalizeNumber(data.PriceForm);
            const vat = normalizeNumber(data.Total_PriceForm || supply * 1.1);
            return {
                supply: supply,
                vat: vat,
                vatAmount: vat - supply
            };
        }

        // 명함/봉투/스티커: price, vat_price
        if (data.price !== undefined) {
            const supply = normalizeNumber(data.price);
            const vat = normalizeNumber(data.vat_price || data.price_vat || supply * 1.1);
            return {
                supply: supply,
                vat: vat,
                vatAmount: vat - supply
            };
        }

        // 포스터: total_price, total_with_vat
        if (data.total_price !== undefined) {
            const supply = normalizeNumber(data.total_price);
            const vat = normalizeNumber(data.total_with_vat || supply * 1.1);
            return {
                supply: supply,
                vat: vat,
                vatAmount: vat - supply
            };
        }

        // 포스터: base_price (alternative)
        if (data.base_price !== undefined) {
            const supply = normalizeNumber(data.base_price);
            const vat = normalizeNumber(data.total_with_vat || supply * 1.1);
            return {
                supply: supply,
                vat: vat,
                vatAmount: vat - supply
            };
        }

        // 일반: total_supply_price, final_total_with_vat
        if (data.total_supply_price !== undefined) {
            const supply = normalizeNumber(data.total_supply_price);
            const vat = normalizeNumber(data.final_total_with_vat || supply * 1.1);
            return {
                supply: supply,
                vat: vat,
                vatAmount: vat - supply
            };
        }

        return null;
    }

    /**
     * 숫자 정규화 (문자열 → 정수)
     *
     * @param {*} value - 정규화할 값
     * @returns {number} - 정수
     */
    function normalizeNumber(value) {
        if (typeof value === 'number') {
            return Math.round(value);
        }

        if (typeof value === 'string') {
            // 콤마, 공백, '원' 제거
            const cleaned = value.replace(/[,\s원]/g, '');
            const num = parseFloat(cleaned);
            return isNaN(num) ? 0 : Math.round(num);
        }

        return 0;
    }

    /**
     * 표준 포맷으로 변환된 가격 데이터를 레거시 포맷으로 역변환
     * (transition 기간 중 backward compatibility용)
     *
     * @param {Object} normalized - 표준 포맷 {supply, vat}
     * @param {string} productType - 제품 타입
     * @returns {Object} - 레거시 필드 포함 객체
     */
    function toLegacyFormat(normalized, productType) {
        const base = {
            price_supply: normalized.supply,
            price_vat: normalized.vat,
            price_vat_amount: normalized.vatAmount
        };

        // 제품별 레거시 필드 추가
        switch (productType) {
            case 'inserted':
            case 'leaflet':
                return Object.assign(base, {
                    Order_PriceForm: normalized.supply,
                    Total_PriceForm: normalized.vat,
                    calculated_price: normalized.supply,
                    calculated_vat_price: normalized.vat
                });

            case 'merchandisebond':
                return Object.assign(base, {
                    PriceForm: normalized.supply,
                    Total_PriceForm: normalized.vat
                });

            case 'littleprint':
            case 'poster':
                return Object.assign(base, {
                    base_price: normalized.supply,
                    total_price: normalized.supply,
                    total_with_vat: normalized.vat
                });

            case 'namecard':
            case 'envelope':
            case 'sticker':
            case 'msticker':
            default:
                return Object.assign(base, {
                    price: normalized.supply,
                    vat_price: normalized.vat
                });
        }
    }

    /**
     * window.currentPriceData 업데이트 헬퍼
     *
     * @param {Object} priceData - 가격 계산 결과
     * @param {string} productType - 제품 타입
     */
    function updateCurrentPriceData(priceData, productType) {
        const normalized = normalizePriceData(priceData);
        const legacy = toLegacyFormat(normalized, productType);

        // 글로벌 변수 업데이트 (quotation-modal-common.js 사용)
        window.currentPriceData = legacy;

        return legacy;
    }

    /**
     * 제품 타입 자동 감지 (URL 기반)
     *
     * @returns {string} - 제품 타입
     */
    function detectProductType() {
        const path = window.location.pathname;

        if (path.includes('/namecard/')) return 'namecard';
        if (path.includes('/sticker')) return 'sticker';
        if (path.includes('/inserted/')) return 'inserted';
        if (path.includes('/envelope/')) return 'envelope';
        if (path.includes('/cadarok/')) return 'cadarok';
        if (path.includes('/littleprint/')) return 'littleprint';
        if (path.includes('/merchandisebond/')) return 'merchandisebond';
        if (path.includes('/ncrflambeau/')) return 'ncrflambeau';
        if (path.includes('/msticker/')) return 'msticker';
        if (path.includes('/leaflet/')) return 'leaflet';

        return 'unknown';
    }

    /**
     * 가격 표시 포맷 (천 단위 콤마)
     *
     * @param {number} price - 가격
     * @returns {string} - "10,000원"
     */
    function formatPrice(price) {
        return price.toLocaleString('ko-KR') + '원';
    }

    /**
     * 가격 데이터 검증
     *
     * @param {Object} priceData - 가격 데이터
     * @returns {boolean} - 유효 여부
     */
    function validatePriceData(priceData) {
        try {
            const normalized = normalizePriceData(priceData);
            return normalized.supply > 0 && normalized.vat > 0;
        } catch (e) {
            return false;
        }
    }

    // Export to global scope
    window.PriceDataAdapter = {
        normalize: normalizePriceData,
        toLegacy: toLegacyFormat,
        updateGlobal: updateCurrentPriceData,
        detectProductType: detectProductType,
        formatPrice: formatPrice,
        validate: validatePriceData,
        normalizeNumber: normalizeNumber
    };

    // Alias for backward compatibility
    window.normalizePriceData = normalizePriceData;

})(window);

/**
 * 사용 예제:
 *
 * // 1. 가격 계산 응답 정규화
 * const response = {price: "10,000", vat_price: "11,000"};  // 명함
 * const normalized = PriceDataAdapter.normalize(response);
 * console.log(normalized);  // {supply: 10000, vat: 11000, vatAmount: 1000}
 *
 * // 2. 전단지 가격 정규화
 * const flyer = {Order_PriceForm: 50000, Total_PriceForm: 55000};
 * const result = PriceDataAdapter.normalize(flyer);
 * console.log(result);  // {supply: 50000, vat: 55000, vatAmount: 5000}
 *
 * // 3. currentPriceData 업데이트
 * PriceDataAdapter.updateGlobal(response, 'namecard');
 * console.log(window.currentPriceData);  // 레거시 + 표준 필드 모두 포함
 *
 * // 4. 검증
 * if (PriceDataAdapter.validate(response)) {
 *     console.log('Valid price data');
 * }
 */
