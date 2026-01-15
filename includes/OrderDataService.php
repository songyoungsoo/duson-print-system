<?php
/**
 * OrderDataService - 5대 표준 인자 API Wrapper
 *
 * 5대 표준 인자:
 * 1. item: 품목명 (전단지, 명함, 스티커 등)
 * 2. spec: 규격/사양 (2줄 형식)
 * 3. qty: 수량 값 (0.5, 1000 등)
 * 4. unit: 단위 (연, 매, 부, 권)
 * 5. price: 가격 (공급가액, VAT, VAT포함)
 *
 * SSOT 컴포넌트 연동:
 * - QuantityFormatter: 수량/단위 SSOT
 * - ProductSpecFormatter: 규격 포맷팅 SSOT
 * - DataAdapter: 레거시→표준 변환
 *
 * @package DusonPrint
 * @since 2026-01-15
 */

require_once __DIR__ . '/QuantityFormatter.php';
require_once __DIR__ . '/ProductSpecFormatter.php';
require_once __DIR__ . '/DataAdapter.php';

class OrderDataService {
    private $db;
    private $specFormatter;

    /**
     * Constructor
     *
     * @param mysqli $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
        $this->specFormatter = new ProductSpecFormatter($db);
    }

    /**
     * 주문 데이터에서 5대 표준 인자 추출
     *
     * @param array $orderRow mlangorder_printauto 또는 shop_temp 행
     * @return array [
     *   'item' => string,        // 품목명
     *   'item_code' => string,   // 품목 코드 (product_type)
     *   'spec' => array,         // 규격 (line1, line2, additional)
     *   'spec_text' => string,   // 규격 단일 텍스트
     *   'spec_html' => string,   // 규격 HTML
     *   'qty' => float,          // 수량 값
     *   'unit' => string,        // 단위 (한글)
     *   'unit_code' => string,   // 단위 코드
     *   'qty_sheets' => int,     // 매수 (전단지용)
     *   'qty_display' => string, // 수량 표시 ("0.5연 (2,000매)")
     *   'price_supply' => int,   // 공급가액
     *   'price_vat_amount' => int, // VAT
     *   'price_vat' => int,      // VAT 포함 가격
     *   'data_version' => int    // 데이터 버전 (1=레거시, 2=표준)
     * ]
     */
    public function getStandardized(array $orderRow): array {
        // 1. product_type 확인
        $productType = $this->detectProductType($orderRow);

        // 2. data_version 확인 (1=레거시, 2=표준)
        $dataVersion = intval($orderRow['data_version'] ?? 1);

        // 3. 표준 데이터 추출 (버전에 따라 분기)
        if ($dataVersion >= 2) {
            return $this->extractFromStandard($orderRow, $productType);
        } else {
            return $this->extractFromLegacy($orderRow, $productType);
        }
    }

    /**
     * 표준 스키마 데이터에서 5대 인자 추출
     *
     * @param array $orderRow 주문 데이터
     * @param string $productType 제품 타입
     * @return array 5대 표준 인자
     */
    private function extractFromStandard(array $orderRow, string $productType): array {
        // 품목명
        $item = ProductSpecFormatter::getProductTypeName($productType);

        // 규격 포맷팅 (ProductSpecFormatter SSOT)
        $spec = $this->specFormatter->format($orderRow);
        $specText = $this->specFormatter->formatText($orderRow);
        $specHtml = $this->specFormatter->formatHtml($orderRow);

        // 수량 정보 (표준 필드 직접 사용)
        $qtyValue = floatval($orderRow['quantity_value'] ?? $orderRow['qty_value'] ?? 0);
        $unitCode = $orderRow['quantity_unit_code'] ?? $orderRow['qty_unit_code'] ??
                    QuantityFormatter::getProductUnitCode($productType);
        $qtySheets = intval($orderRow['quantity_sheets'] ?? $orderRow['qty_sheets'] ?? 0);

        // ✅ 2026-01-16: NCR양식지 매수 재계산 (잘못 저장된 레거시 데이터 보정)
        // quantity_sheets == quantity_value인 경우 명백히 잘못된 값
        if ($productType === 'ncrflambeau' && $qtyValue > 0 && $qtySheets <= $qtyValue) {
            $multiplier = QuantityFormatter::extractNcrMultiplier($orderRow);
            $qtySheets = QuantityFormatter::calculateNcrSheets(intval($qtyValue), $multiplier);
            error_log("[OrderDataService] NCR sheets recalculated: {$qtyValue}권 × multiplier={$multiplier} = {$qtySheets}매");
        }

        // 수량 표시 (QuantityFormatter SSOT)
        $qtyDisplay = QuantityFormatter::format($qtyValue, $unitCode, $qtySheets);

        // 가격 정보 (표준 필드)
        $priceSupply = intval($orderRow['price_supply'] ?? $orderRow['money_4'] ?? 0);
        $priceVat = intval($orderRow['price_vat'] ?? $orderRow['money_5'] ?? 0);
        $priceVatAmount = $priceVat - $priceSupply;

        return [
            'item' => $item,
            'item_code' => $productType,
            'spec' => $spec,
            'spec_text' => $specText,
            'spec_html' => $specHtml,
            'qty' => $qtyValue,
            'unit' => QuantityFormatter::getUnitName($unitCode),
            'unit_code' => $unitCode,
            'qty_sheets' => $qtySheets,
            'qty_display' => $qtyDisplay,
            'price_supply' => $priceSupply,
            'price_vat_amount' => $priceVatAmount,
            'price_vat' => $priceVat,
            'data_version' => 2
        ];
    }

    /**
     * 레거시 스키마 데이터에서 5대 인자 추출
     *
     * @param array $orderRow 주문 데이터
     * @param string $productType 제품 타입
     * @return array 5대 표준 인자
     */
    private function extractFromLegacy(array $orderRow, string $productType): array {
        // Type_1 JSON 파싱 (레거시 데이터 보충)
        $orderRow = $this->parseType1Json($orderRow);

        // 품목명
        $item = ProductSpecFormatter::getProductTypeName($productType);

        // 규격 포맷팅 (ProductSpecFormatter SSOT - 레거시 분기 자동 처리)
        $spec = $this->specFormatter->format($orderRow);
        $specText = $this->specFormatter->formatText($orderRow);
        $specHtml = $this->specFormatter->formatHtml($orderRow);

        // 수량 정보 (QuantityFormatter 레거시 추출)
        $qtyInfo = QuantityFormatter::extractFromLegacy($orderRow, $productType);
        $qtyValue = $qtyInfo['qty_value'];
        $unitCode = $qtyInfo['qty_unit_code'];
        $qtySheets = $qtyInfo['qty_sheets'];

        // 전단지 매수 DB 조회 (샛밥 방식)
        if (in_array($productType, ['inserted', 'leaflet']) && $qtyValue > 0 && empty($qtySheets)) {
            $qtySheets = $this->lookupInsertedSheets($qtyValue);
        }

        // ✅ 2026-01-15: NCR양식지 매수 계산 (권 × 50 × multiplier)
        if ($productType === 'ncrflambeau' && $qtyValue > 0 && empty($qtySheets)) {
            $multiplier = QuantityFormatter::extractNcrMultiplier($orderRow);
            $qtySheets = QuantityFormatter::calculateNcrSheets(intval($qtyValue), $multiplier);
        }

        // 수량 표시 (QuantityFormatter SSOT)
        $qtyDisplay = QuantityFormatter::format($qtyValue, $unitCode, $qtySheets);

        // 가격 정보 (레거시 필드)
        $priceSupply = intval($orderRow['money_4'] ?? $orderRow['st_price'] ?? 0);
        $priceVat = intval($orderRow['money_5'] ?? $orderRow['st_price_vat'] ?? 0);
        $priceVatAmount = $priceVat - $priceSupply;

        return [
            'item' => $item,
            'item_code' => $productType,
            'spec' => $spec,
            'spec_text' => $specText,
            'spec_html' => $specHtml,
            'qty' => $qtyValue,
            'unit' => QuantityFormatter::getUnitName($unitCode),
            'unit_code' => $unitCode,
            'qty_sheets' => $qtySheets,
            'qty_display' => $qtyDisplay,
            'price_supply' => $priceSupply,
            'price_vat_amount' => $priceVatAmount,
            'price_vat' => $priceVat,
            'data_version' => 1
        ];
    }

    /**
     * Type_1 JSON 필드 파싱
     *
     * @param array $orderRow 주문 데이터
     * @return array 병합된 주문 데이터
     */
    private function parseType1Json(array $orderRow): array {
        if (empty($orderRow['Type_1']) || !is_string($orderRow['Type_1'])) {
            return $orderRow;
        }

        $type1Data = json_decode($orderRow['Type_1'], true);
        if (!$type1Data || !is_array($type1Data)) {
            return $orderRow;
        }

        // order_details 중첩 구조 처리
        if (isset($type1Data['order_details']) && is_array($type1Data['order_details'])) {
            $type1Data = array_merge($type1Data, $type1Data['order_details']);
        }

        // DB 컬럼 값이 없는 경우에만 Type_1 값 사용
        foreach ($type1Data as $key => $value) {
            if ($value !== '' && $value !== null &&
                (!isset($orderRow[$key]) || $orderRow[$key] === '' || $orderRow[$key] === null)) {
                $orderRow[$key] = $value;
            }
        }

        return $orderRow;
    }

    /**
     * 제품 타입 감지
     *
     * @param array $orderRow 주문 데이터
     * @return string 제품 타입
     */
    private function detectProductType(array $orderRow): string {
        // product_type 필드 우선
        if (!empty($orderRow['product_type'])) {
            return $orderRow['product_type'];
        }

        // Type_1 JSON에서 추출
        if (!empty($orderRow['Type_1'])) {
            $type1 = json_decode($orderRow['Type_1'], true);
            if (!empty($type1['product_type'])) {
                return $type1['product_type'];
            }
        }

        // 레거시 스티커 감지 (jong, garo, sero 필드 존재)
        if (!empty($orderRow['jong']) && !empty($orderRow['garo']) && !empty($orderRow['sero'])) {
            return 'sticker';
        }

        return 'unknown';
    }

    /**
     * 전단지 매수 DB 조회
     *
     * @param float $reams 연수
     * @return int 매수
     */
    private function lookupInsertedSheets(float $reams): int {
        if (!$this->db || $reams <= 0) {
            return 0;
        }

        $stmt = mysqli_prepare($this->db,
            "SELECT quantityTwo FROM mlangprintauto_inserted WHERE quantity = ? LIMIT 1"
        );

        if (!$stmt) {
            return 0;
        }

        mysqli_stmt_bind_param($stmt, "d", $reams);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $sheets = 0;
        if ($row = mysqli_fetch_assoc($result)) {
            $sheets = intval($row['quantityTwo']);
        }

        mysqli_stmt_close($stmt);
        return $sheets;
    }

    /**
     * 복수 주문 일괄 처리
     *
     * @param array $orderRows 주문 데이터 배열
     * @return array 5대 표준 인자 배열
     */
    public function getStandardizedBatch(array $orderRows): array {
        $results = [];
        foreach ($orderRows as $orderRow) {
            $results[] = $this->getStandardized($orderRow);
        }
        return $results;
    }

    /**
     * 주문번호로 표준화된 데이터 조회
     *
     * @param int $orderNo 주문번호
     * @return array|null 5대 표준 인자 또는 null
     */
    public function getByOrderNo(int $orderNo): ?array {
        $stmt = mysqli_prepare($this->db,
            "SELECT * FROM mlangorder_printauto WHERE no = ?"
        );

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $orderNo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $orderRow = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$orderRow) {
            return null;
        }

        return $this->getStandardized($orderRow);
    }

    /**
     * 이메일 테이블 행 생성
     *
     * @param array $orderRow 주문 데이터
     * @return string HTML 테이블 행
     */
    public function formatEmailRow(array $orderRow): string {
        $std = $this->getStandardized($orderRow);

        return sprintf(
            '<tr>
                <td style="border:1px solid #ddd;padding:8px;">%s</td>
                <td style="border:1px solid #ddd;padding:8px;">%s</td>
                <td style="border:1px solid #ddd;padding:8px;text-align:center;">%s</td>
                <td style="border:1px solid #ddd;padding:8px;text-align:right;">%s원</td>
            </tr>',
            htmlspecialchars($std['item']),
            $std['spec_html'],
            htmlspecialchars($std['qty_display']),
            number_format($std['price_vat'])
        );
    }

    /**
     * 견적서용 데이터 포맷
     *
     * @param array $orderRow 주문 데이터
     * @return array 견적서용 형식화된 데이터
     */
    public function formatForQuote(array $orderRow): array {
        $std = $this->getStandardized($orderRow);

        return [
            'product_name' => $std['item'],
            'spec_text' => $std['spec_text'],
            'quantity' => $std['qty'],
            'unit' => $std['unit'],
            'quantity_display' => $std['qty_display'],
            'unit_price' => $std['qty'] > 0 ? intval($std['price_supply'] / $std['qty']) : 0,
            'supply_price' => $std['price_supply'],
            'vat' => $std['price_vat_amount'],
            'total_price' => $std['price_vat']
        ];
    }
}
