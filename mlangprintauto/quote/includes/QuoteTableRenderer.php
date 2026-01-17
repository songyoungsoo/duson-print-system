<?php
/**
 * QuoteTableRenderer - 견적서 테이블 렌더링 SSOT
 *
 * "데이터는 하나로, 출력은 표준 렌더러로"
 * 견적서/주문서/PDF/이메일 모두 동일한 포맷 출력
 *
 * @package DusonPrint
 * @since 2026-01-17
 */

require_once __DIR__ . '/../../../includes/QuantityFormatter.php';

class QuoteTableRenderer {
    private $db;

    /**
     * 표준 7개 컬럼 정의
     * NO, 품목, 규격/옵션, 수량, 단위, 단가, 공급가액
     */
    const STANDARD_COLUMNS = [
        'no'         => 'NO',
        'product'    => '품 목',
        'spec'       => '규격/옵션',
        'quantity'   => '수량',
        'unit'       => '단위',
        'unit_price' => '단가',
        'supply'     => '공급가액',
        'notes'      => '비고'
    ];

    /**
     * 단위 코드 → 한글 매핑 (QuantityFormatter와 동기화)
     */
    const UNIT_MAP = [
        'R' => '연',
        'S' => '매',
        'B' => '부',
        'V' => '권',
        'P' => '장',
        'E' => '개'
    ];

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 수량 셀 포맷팅 (SSOT)
     *
     * 하이브리드 모델: qty_val/qty_unit 우선 사용, 없으면 레거시 fallback
     *
     * @param array $item 견적 아이템 데이터
     * @param bool $includeSheets 매수 표시 여부 (연/권 단위용)
     * @param string $separator 수량/매수 구분자 (기본: '<br>')
     * @return string 포맷된 수량 문자열
     */
    public function formatQuantityCell(array $item, bool $includeSheets = true, string $separator = '<br>'): string {
        // 1. qty_val/qty_unit 표준 필드 우선 사용
        $qtyVal = floatval($item['qty_val'] ?? $item['quantity'] ?? 0);
        $qtyUnit = $item['qty_unit'] ?? 'E';
        $qtySheets = intval($item['qty_sheets'] ?? 0);

        // 숫자 포맷팅: 정수면 소수점 없이, 소수면 필요한 만큼만
        if (floor($qtyVal) == $qtyVal) {
            $qtyDisplay = number_format($qtyVal);
        } else {
            $qtyDisplay = rtrim(rtrim(number_format($qtyVal, 2), '0'), '.');
        }

        // 2. 연/권 단위인 경우 매수 조회/계산
        if ($includeSheets && $qtyVal > 0 && in_array($qtyUnit, ['R', 'V'])) {
            // qty_sheets가 0이면 조회/계산
            if ($qtySheets <= 0) {
                $qtySheets = $this->lookupSheets($qtyVal, $qtyUnit, $item);
            }

            // 매수 표시 추가
            if ($qtySheets > 0) {
                $qtyDisplay .= $separator . '<span style="font-size: 10px; color: #1e88ff;">(' . number_format($qtySheets) . '매)</span>';
            }
        }

        return $qtyDisplay;
    }

    /**
     * 단위 셀 포맷팅 (SSOT)
     *
     * ✅ 2026-01-17: SKILL.md Part 4.1 단위 코드 매핑 준수
     * - 레거시 unit 필드를 무시하고, product_type 기반으로 단위 결정
     *
     * @param array $item 견적 아이템 데이터
     * @return string 한글 단위명
     */
    public function formatUnitCell(array $item): string {
        // ✅ SSOT 우선순위:
        // 1. product_type 기반 (SKILL.md Part 4.1 - 최우선)
        // 2. qty_unit (표준 필드)
        // 3. 레거시 unit (fallback)

        // 1. product_type이 있으면 무조건 SSOT 규칙 적용
        $productType = $item['product_type'] ?? '';
        if (!empty($productType) && isset(QuantityFormatter::PRODUCT_UNITS[$productType])) {
            $unitCode = QuantityFormatter::getProductUnitCode($productType);
            return QuantityFormatter::getUnitName($unitCode);
        }

        // 2. 비규격 품목: qty_unit 사용
        $qtyUnit = $item['qty_unit'] ?? null;
        if ($qtyUnit && isset(QuantityFormatter::UNIT_CODES[$qtyUnit])) {
            return QuantityFormatter::getUnitName($qtyUnit);
        }

        // 3. 최후 fallback: 레거시 unit 필드
        return htmlspecialchars($item['unit'] ?? '개');
    }

    /**
     * 단가 셀 포맷팅
     *
     * @param array $item 견적 아이템 데이터
     * @return string 포맷된 단가
     */
    public function formatUnitPriceCell(array $item): string {
        $unitPrice = floatval($item['unit_price'] ?? 0);

        // 소수점이 있으면 1자리까지, 정수면 정수로
        if ($unitPrice == floor($unitPrice)) {
            return number_format($unitPrice);
        }
        return number_format($unitPrice, 1);
    }

    /**
     * 공급가액 셀 포맷팅
     *
     * @param array $item 견적 아이템 데이터
     * @return string 포맷된 공급가액
     */
    public function formatSupplyPriceCell(array $item): string {
        return number_format(intval($item['supply_price'] ?? 0));
    }

    /**
     * 매수 조회/계산 (내부 메서드)
     *
     * @param float $qtyVal 수량 값
     * @param string $qtyUnit 단위 코드
     * @param array $item 아이템 데이터
     * @return int 매수
     */
    private function lookupSheets(float $qtyVal, string $qtyUnit, array $item): int {
        $sheets = 0;

        if ($qtyUnit === 'R') {
            // 전단지: mlangprintauto_inserted 테이블에서 매수 조회
            $stmt = mysqli_prepare($this->db, "SELECT quantityTwo FROM mlangprintauto_inserted WHERE quantity = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "d", $qtyVal);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $sheets = intval($row['quantityTwo']);
                }
                mysqli_stmt_close($stmt);
            }
        } elseif ($qtyUnit === 'V') {
            // NCR양식지: 권 × 50 × multiplier(기본 2)
            $multiplier = $this->extractNcrMultiplier($item);
            $sheets = QuantityFormatter::calculateNcrSheets(intval($qtyVal), $multiplier);
        }

        return $sheets;
    }

    /**
     * NCR 복사 매수 추출
     *
     * @param array $item 아이템 데이터
     * @return int multiplier (2, 3, 4)
     */
    private function extractNcrMultiplier(array $item): int {
        $searchFields = [
            $item['spec_material'] ?? '',
            $item['MY_Fsd_name'] ?? '',
            $item['MY_Fsd'] ?? '',
            $item['source_data'] ?? ''
        ];

        $materialText = implode(' ', array_filter($searchFields));

        // 패턴 매칭: X매 형식
        if (preg_match('/([2-4])매/u', $materialText, $matches)) {
            return intval($matches[1]);
        }

        return 2;  // 기본값
    }

    /**
     * 테이블 행 HTML 렌더링
     *
     * @param array $item 견적 아이템 데이터
     * @param int $rowNo 행 번호
     * @return string HTML 문자열
     */
    public function renderTableRow(array $item, int $rowNo): string {
        $html = '<tr>';
        $html .= '<td class="col-no center">' . $rowNo . '</td>';
        $html .= '<td class="col-name">' . htmlspecialchars($item['product_name'] ?? '') . '</td>';
        $html .= '<td class="col-spec spec">' . nl2br(htmlspecialchars($item['specification'] ?? '')) . '</td>';
        $html .= '<td class="col-qty center">' . $this->formatQuantityCell($item) . '</td>';
        $html .= '<td class="col-unit center">' . $this->formatUnitCell($item) . '</td>';
        $html .= '<td class="col-price right">' . $this->formatUnitPriceCell($item) . '</td>';
        $html .= '<td class="col-supply right">' . $this->formatSupplyPriceCell($item) . '</td>';
        $html .= '<td class="col-notes spec">' . htmlspecialchars($item['notes'] ?? '') . '</td>';
        $html .= '</tr>';

        return $html;
    }

    /**
     * 전체 테이블 바디 HTML 렌더링
     *
     * @param array $items 견적 아이템 배열
     * @param int $minRows 최소 행 수 (빈 행 추가용)
     * @return string HTML 문자열
     */
    public function renderTableBody(array $items, int $minRows = 5): string {
        $html = '';
        $rowNo = 1;

        foreach ($items as $item) {
            $html .= $this->renderTableRow($item, $rowNo++);
        }

        // 빈 행 추가 (최소 행 수 보장)
        $emptyRows = max(0, $minRows - count($items));
        for ($i = 0; $i < $emptyRows; $i++) {
            $html .= '<tr>';
            $html .= '<td>&nbsp;</td>';
            for ($j = 0; $j < 7; $j++) {
                $html .= '<td></td>';
            }
            $html .= '</tr>';
        }

        return $html;
    }

    /**
     * 이메일용 테이블 행 HTML 렌더링 (인라인 스타일)
     *
     * @param array $item 견적 아이템 데이터
     * @param int $rowNo 행 번호
     * @return string HTML 문자열
     */
    public function renderEmailTableRow(array $item, int $rowNo): string {
        $productName = htmlspecialchars($item['product_name'] ?? '');
        $specification = htmlspecialchars($item['specification'] ?? '');
        $unit = $this->formatUnitCell($item);
        $unitPrice = $this->formatUnitPriceCell($item);
        $supplyPrice = $this->formatSupplyPriceCell($item);
        $notes = htmlspecialchars($item['notes'] ?? '');

        // 수량 포맷 (이메일용)
        $qtyVal = floatval($item['qty_val'] ?? $item['quantity'] ?? 0);
        $qtyUnit = $item['qty_unit'] ?? 'E';
        $qtySheets = intval($item['qty_sheets'] ?? 0);

        $qtyDisplay = (floor($qtyVal) == $qtyVal) ? number_format($qtyVal) : rtrim(rtrim(number_format($qtyVal, 2), '0'), '.');

        // 연/권 단위 매수 표시
        if (in_array($qtyUnit, ['R', 'V']) && $qtySheets <= 0 && $qtyVal > 0) {
            $qtySheets = $this->lookupSheets($qtyVal, $qtyUnit, $item);
        }
        if ($qtySheets > 0 && in_array($qtyUnit, ['R', 'V'])) {
            $qtyDisplay .= '<br><span style="font-size:9px;color:#888;">(' . number_format($qtySheets) . '매)</span>';
        }

        $html = "<tr>
            <td style='border:1px solid #ddd;padding:5px;text-align:center;font-size:14px;'>{$rowNo}</td>
            <td style='border:1px solid #ddd;padding:5px;font-size:14px;'>{$productName}</td>
            <td style='border:1px solid #ddd;padding:5px;font-size:13px;line-height:1.4;'>{$specification}</td>
            <td style='border:1px solid #ddd;padding:5px;text-align:center;font-size:14px;'>{$qtyDisplay}</td>
            <td style='border:1px solid #ddd;padding:5px;text-align:center;font-size:14px;'>{$unit}</td>
            <td style='border:1px solid #ddd;padding:5px;text-align:right;font-size:14px;'>{$unitPrice}</td>
            <td style='border:1px solid #ddd;padding:5px;text-align:right;font-size:14px;font-weight:600;'>{$supplyPrice}</td>
            <td style='border:1px solid #ddd;padding:5px;font-size:12px;color:#666;'>{$notes}</td>
        </tr>";

        return $html;
    }

    /**
     * 이메일용 전체 테이블 바디 HTML 렌더링
     *
     * @param array $items 견적 아이템 배열
     * @return string HTML 문자열
     */
    public function renderEmailTableBody(array $items): string {
        $html = '';
        $rowNo = 1;

        foreach ($items as $item) {
            $html .= $this->renderEmailTableRow($item, $rowNo++);
        }

        return $html;
    }
}
