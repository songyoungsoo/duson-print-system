<?php
/**
 * 관리자 견적 관리 핵심 클래스
 *
 * 핵심 원칙:
 * - 모든 금액은 DB에서 조회 (계산 금지)
 * - 스티커만 예외: 단가 x 수량 = 공급가액
 * - VAT는 저장 전 계산: 공급가액 x 0.1
 */

require_once __DIR__ . '/PriceHelper.php';

class AdminQuoteManager
{
    private $db;
    private $priceHelper;

    public function __construct($db)
    {
        $this->db = $db;
        $this->priceHelper = new PriceHelper($db);
    }

    /**
     * 견적번호 생성
     * 형식: AQ-YYYYMMDD-XXXX
     */
    public function generateQuoteNo(): string
    {
        $date = date('Ymd');
        $prefix = "AQ-{$date}-";

        // 오늘 마지막 번호 조회
        $query = "SELECT quote_no FROM admin_quotes
                  WHERE quote_no LIKE ? ORDER BY quote_no DESC LIMIT 1";
        $stmt = mysqli_prepare($this->db, $query);
        $pattern = $prefix . '%';
        mysqli_stmt_bind_param($stmt, "s", $pattern);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $lastSeq = intval(substr($row['quote_no'], -4));
            $newSeq = $lastSeq + 1;
        } else {
            $newSeq = 1;
        }
        mysqli_stmt_close($stmt);

        return $prefix . str_pad($newSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 견적 저장
     *
     * @param array $quoteData 견적 기본 정보
     * @param array $items 품목 배열
     * @param bool $isDraft 임시저장 여부
     * @return int 생성된 견적 ID
     */
    public function saveQuote(array $quoteData, array $items, bool $isDraft = true): int
    {
        // 견적번호가 없으면 새로 생성
        if (empty($quoteData['quote_no'])) {
            $quoteData['quote_no'] = $this->generateQuoteNo();
        }

        // 금액 합계 계산 (품목의 supply_price 합산)
        $totals = $this->priceHelper->calculateTotals($items);

        // 견적 기본 정보 저장
        $query = "INSERT INTO admin_quotes
                  (quote_no, customer_company, customer_name, customer_phone, customer_email,
                   customer_address, supply_total, vat_total, grand_total, status, valid_until,
                   admin_memo, customer_memo, created_by)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            throw new Exception('Query preparation failed: ' . mysqli_error($this->db));
        }

        $status = 'draft';
        $validUntil = date('Y-m-d', strtotime('+30 days'));

        $customerCompany = $quoteData['customer_company'] ?? '';
        $customerName = $quoteData['customer_name'] ?? '';
        $customerPhone = $quoteData['customer_phone'] ?? '';
        $customerEmail = $quoteData['customer_email'] ?? '';
        $customerAddress = $quoteData['customer_address'] ?? '';
        $adminMemo = $quoteData['admin_memo'] ?? '';
        $customerMemo = $quoteData['customer_memo'] ?? '';
        $createdBy = $quoteData['created_by'] ?? '';

        // 3번 검증: placeholder 14개 = type 14개 = var 14개
        // s:quote_no, s:company, s:name, s:phone, s:email, s:address,
        // i:supply, i:vat, i:grand, s:status, s:valid, s:admin_memo, s:customer_memo, s:created_by
        mysqli_stmt_bind_param(
            $stmt,
            "ssssssiiisssss",
            $quoteData['quote_no'],
            $customerCompany,
            $customerName,
            $customerPhone,
            $customerEmail,
            $customerAddress,
            $totals['supply_total'],
            $totals['vat_total'],
            $totals['grand_total'],
            $status,
            $validUntil,
            $adminMemo,
            $customerMemo,
            $createdBy
        );

        // 14 params: quote_no(s) + customer_company(s) + customer_name(s) + customer_phone(s)
        // + customer_email(s) + customer_address(s) + supply_total(i) + vat_total(i)
        // + grand_total(i) + status(s) + valid_until(s) + admin_memo(s) + customer_memo(s) + created_by(s)

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Quote save failed: ' . mysqli_stmt_error($stmt));
        }

        $quoteId = mysqli_insert_id($this->db);
        mysqli_stmt_close($stmt);

        // 품목 저장
        if (!empty($items)) {
            $this->saveQuoteItems($quoteId, $items);
        }

        return $quoteId;
    }

    /**
     * 견적 수정
     */
    public function updateQuote(int $quoteId, array $quoteData, array $items): bool
    {
        // 금액 합계 재계산
        $totals = $this->priceHelper->calculateTotals($items);

        // 견적 기본 정보 업데이트
        $query = "UPDATE admin_quotes SET
                  customer_company = ?, customer_name = ?, customer_phone = ?, customer_email = ?,
                  customer_address = ?, supply_total = ?, vat_total = ?, grand_total = ?,
                  admin_memo = ?, customer_memo = ?, updated_at = NOW()
                  WHERE id = ?";

        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            throw new Exception('Query preparation failed: ' . mysqli_error($this->db));
        }

        $customerCompany = $quoteData['customer_company'] ?? '';
        $customerName = $quoteData['customer_name'] ?? '';
        $customerPhone = $quoteData['customer_phone'] ?? '';
        $customerEmail = $quoteData['customer_email'] ?? '';
        $customerAddress = $quoteData['customer_address'] ?? '';
        $adminMemo = $quoteData['admin_memo'] ?? '';
        $customerMemo = $quoteData['customer_memo'] ?? '';

        mysqli_stmt_bind_param(
            $stmt,
            "sssssiiissi",
            $customerCompany,
            $customerName,
            $customerPhone,
            $customerEmail,
            $customerAddress,
            $totals['supply_total'],
            $totals['vat_total'],
            $totals['grand_total'],
            $adminMemo,
            $customerMemo,
            $quoteId
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Quote update failed: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        // 기존 품목 삭제 후 재저장
        $this->deleteQuoteItems($quoteId);
        if (!empty($items)) {
            $this->saveQuoteItems($quoteId, $items);
        }

        return true;
    }

    /**
     * 품목 저장
     */
    private function saveQuoteItems(int $quoteId, array $items): void
    {
        $query = "INSERT INTO admin_quote_items
                  (quote_id, item_no, source_type, product_type, product_name,
                   specification, quantity, unit, quantity_display, unit_price,
                   supply_price, source_data, notes)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            throw new Exception('Item query preparation failed: ' . mysqli_error($this->db));
        }

        $itemNo = 1;
        foreach ($items as $item) {
            $sourceType = $item['source_type'] ?? 'manual';
            $productType = $item['product_type'] ?? '';
            $productName = $item['product_name'] ?? '';
            $specification = $item['specification'] ?? '';
            $quantity = floatval($item['quantity'] ?? 1);
            $unit = $item['unit'] ?? '개';
            $quantityDisplay = $item['quantity_display'] ?? '';
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $supplyPrice = intval($item['supply_price'] ?? 0);
            $sourceData = isset($item['source_data']) && $item['source_data']
                ? json_encode($item['source_data'], JSON_UNESCAPED_UNICODE)
                : null;
            $notes = $item['notes'] ?? '';

            mysqli_stmt_bind_param(
                $stmt,
                "iissssdssdiss",
                $quoteId,
                $itemNo,
                $sourceType,
                $productType,
                $productName,
                $specification,
                $quantity,
                $unit,
                $quantityDisplay,
                $unitPrice,
                $supplyPrice,
                $sourceData,
                $notes
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Item save failed: ' . mysqli_stmt_error($stmt));
            }

            $itemNo++;
        }

        mysqli_stmt_close($stmt);
    }

    /**
     * 품목 삭제
     */
    private function deleteQuoteItems(int $quoteId): void
    {
        $query = "DELETE FROM admin_quote_items WHERE quote_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $quoteId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    /**
     * 견적 조회
     */
    public function getQuote(int $quoteId): ?array
    {
        $query = "SELECT * FROM admin_quotes WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $quoteId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $quote = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $quote ?: null;
    }

    /**
     * 견적 품목 조회
     */
    public function getQuoteItems(int $quoteId): array
    {
        $query = "SELECT * FROM admin_quote_items WHERE quote_id = ? ORDER BY item_no ASC";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $quoteId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // source_data JSON 디코딩
            if (!empty($row['source_data'])) {
                $row['source_data'] = json_decode($row['source_data'], true);

                // qty_sheets 추출 (전단지 매수)
                if (!empty($row['source_data']['qty_sheets'])) {
                    $row['qty_sheets'] = intval($row['source_data']['qty_sheets']);
                }

                // qty_val, qty_unit 추출 (신규 스키마)
                if (isset($row['source_data']['quantity'])) {
                    $row['qty_val'] = floatval($row['source_data']['quantity']);
                }
                if (!empty($row['source_data']['unit'])) {
                    $row['qty_unit'] = $row['source_data']['unit'] === '연' ? 'R' : 'E';
                }
            }
            $items[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $items;
    }

    /**
     * 견적 목록 조회
     */
    public function getQuoteList(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];
        $types = "";

        if (!empty($filters['status'])) {
            $where[] = "q.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $where[] = "(q.quote_no LIKE ? OR q.customer_name LIKE ? OR q.customer_company LIKE ?)";
            $searchParam = "%" . $filters['search'] . "%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "sss";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(q.created_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(q.created_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // 목록 조회
        $query = "
            SELECT
                q.*,
                COUNT(qi.id) as item_count
            FROM admin_quotes q
            LEFT JOIN admin_quote_items qi ON q.id = qi.quote_id
            {$whereClause}
            GROUP BY q.id
            ORDER BY q.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = mysqli_prepare($this->db, $query);
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $quotes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $quotes[] = $row;
        }
        mysqli_stmt_close($stmt);

        // 전체 개수
        $countQuery = "SELECT COUNT(*) as total FROM admin_quotes q {$whereClause}";
        $countStmt = mysqli_prepare($this->db, $countQuery);
        $countTypes = substr($types, 0, -2);
        $countParams = array_slice($params, 0, -2);
        if (!empty($countParams)) {
            mysqli_stmt_bind_param($countStmt, $countTypes, ...$countParams);
        }
        mysqli_stmt_execute($countStmt);
        $countResult = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt));
        mysqli_stmt_close($countStmt);

        return [
            'quotes' => $quotes,
            'total' => intval($countResult['total']),
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($countResult['total'] / $limit)
        ];
    }

    /**
     * 견적 삭제
     */
    public function deleteQuote(int $quoteId): bool
    {
        // CASCADE로 품목도 자동 삭제됨
        $query = "DELETE FROM admin_quotes WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $quoteId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $result;
    }

    /**
     * 견적 상태 변경
     */
    public function updateStatus(int $quoteId, string $status): bool
    {
        $validStatuses = ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'converted'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid status: ' . $status);
        }

        $query = "UPDATE admin_quotes SET status = ?, updated_at = NOW() WHERE id = ?";

        // 상태별 추가 필드 업데이트
        $extraField = '';
        switch ($status) {
            case 'sent':
                $extraField = ', sent_at = NOW()';
                break;
            case 'viewed':
                $extraField = ', viewed_at = NOW()';
                break;
            case 'accepted':
                $extraField = ', accepted_at = NOW()';
                break;
        }

        if (!empty($extraField)) {
            $query = "UPDATE admin_quotes SET status = ?{$extraField}, updated_at = NOW() WHERE id = ?";
        }

        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "si", $status, $quoteId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $result;
    }

    /**
     * 견적 통계
     */
    public function getStatistics(): array
    {
        $query = "
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'viewed' THEN 1 ELSE 0 END) as viewed,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted
            FROM admin_quotes
        ";

        $result = mysqli_query($this->db, $query);
        return mysqli_fetch_assoc($result) ?: [];
    }

    /**
     * 임시 품목 추가 (계산기에서)
     */
    public function addTempItem(string $sessionId, array $calcItem): int
    {
        // 수동 입력인지 계산기인지 확인
        $isManual = !empty($calcItem['is_manual']);

        if ($isManual) {
            $query = "INSERT INTO admin_quotation_temp
                      (admin_session_id, is_manual, manual_product_name, manual_specification,
                       manual_quantity, manual_unit, manual_supply_price, product_type)
                      VALUES (?, 1, ?, ?, ?, ?, ?, '')";

            $stmt = mysqli_prepare($this->db, $query);
            mysqli_stmt_bind_param(
                $stmt,
                "sssdsi",
                $sessionId,
                $calcItem['product_name'],
                $calcItem['specification'],
                $calcItem['quantity'],
                $calcItem['unit'],
                $calcItem['supply_price']
            );
        } else {
            // 계산기 품목
            $query = "INSERT INTO admin_quotation_temp
                      (admin_session_id, is_manual, product_type, specification, unit_price,
                       jong, garo, sero, mesu, domusong,
                       uhyung, MY_type, MY_Fsd, PN_type, MY_amount, POtype, ordertype,
                       st_price, st_price_vat, Section, spec_type, spec_material, spec_size,
                       spec_sides, spec_design, quantity_display, coating_enabled, coating_type,
                       coating_price, folding_enabled, folding_type, folding_price,
                       creasing_enabled, creasing_lines, creasing_price, additional_options_total)
                      VALUES (?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($this->db, $query);

            $productType = $calcItem['product_type'] ?? '';
            $specification = $calcItem['specification'] ?? '';
            $unitPrice = floatval($calcItem['unit_price'] ?? 0);
            $jong = $calcItem['jong'] ?? '';
            $garo = $calcItem['garo'] ?? '';
            $sero = $calcItem['sero'] ?? '';
            $mesu = $calcItem['mesu'] ?? '';
            $domusong = $calcItem['domusong'] ?? '';
            $uhyung = intval($calcItem['uhyung'] ?? 0);
            $myType = $calcItem['MY_type'] ?? '';
            $myFsd = $calcItem['MY_Fsd'] ?? '';
            $pnType = $calcItem['PN_type'] ?? '';
            $myAmount = $calcItem['MY_amount'] ?? '';
            $potype = $calcItem['POtype'] ?? '';
            $ordertype = $calcItem['ordertype'] ?? '';
            $stPrice = floatval($calcItem['st_price'] ?? 0);
            $stPriceVat = floatval($calcItem['st_price_vat'] ?? 0);
            $section = $calcItem['Section'] ?? '';
            $specType = $calcItem['spec_type'] ?? '';
            $specMaterial = $calcItem['spec_material'] ?? '';
            $specSize = $calcItem['spec_size'] ?? '';
            $specSides = $calcItem['spec_sides'] ?? '';
            $specDesign = $calcItem['spec_design'] ?? '';
            $quantityDisplay = $calcItem['quantity_display'] ?? '';
            $coatingEnabled = intval($calcItem['coating_enabled'] ?? 0);
            $coatingType = $calcItem['coating_type'] ?? '';
            $coatingPrice = intval($calcItem['coating_price'] ?? 0);
            $foldingEnabled = intval($calcItem['folding_enabled'] ?? 0);
            $foldingType = $calcItem['folding_type'] ?? '';
            $foldingPrice = intval($calcItem['folding_price'] ?? 0);
            $creasingEnabled = intval($calcItem['creasing_enabled'] ?? 0);
            $creasingLines = intval($calcItem['creasing_lines'] ?? 0);
            $creasingPrice = intval($calcItem['creasing_price'] ?? 0);
            $additionalOptionsTotal = intval($calcItem['additional_options_total'] ?? 0);

            // 3번 검증: 35 placeholders = 35 types = 35 variables
            // 1-2: ss (session, productType)
            // 3-4: sd (specification, unitPrice)
            // 5-9: sssss (jong~domusong)
            // 10: i (uhyung)
            // 11-16: ssssss (myType~ordertype)
            // 17-18: dd (stPrice, stPriceVat)
            // 19-25: sssssss (section~quantityDisplay)
            // 26-35: isiisiiiii (coatingEnabled~additionalOptionsTotal)
            mysqli_stmt_bind_param(
                $stmt,
                "sssdsssssissssssddsssssssisiisiiiii",
                $sessionId,
                $productType,
                $specification,
                $unitPrice,
                $jong,
                $garo,
                $sero,
                $mesu,
                $domusong,
                $uhyung,
                $myType,
                $myFsd,
                $pnType,
                $myAmount,
                $potype,
                $ordertype,
                $stPrice,
                $stPriceVat,
                $section,
                $specType,
                $specMaterial,
                $specSize,
                $specSides,
                $specDesign,
                $quantityDisplay,
                $coatingEnabled,
                $coatingType,
                $coatingPrice,
                $foldingEnabled,
                $foldingType,
                $foldingPrice,
                $creasingEnabled,
                $creasingLines,
                $creasingPrice,
                $additionalOptionsTotal
            );
        }

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Temp item save failed: ' . mysqli_stmt_error($stmt));
        }

        $itemId = mysqli_insert_id($this->db);
        mysqli_stmt_close($stmt);

        return $itemId;
    }

    /**
     * 임시 품목 목록 조회
     */
    public function getTempItems(string $sessionId): array
    {
        $query = "SELECT * FROM admin_quotation_temp WHERE admin_session_id = ? ORDER BY no ASC";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $sessionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $items;
    }

    /**
     * 임시 품목 삭제
     */
    public function deleteTempItem(int $itemNo): bool
    {
        $query = "DELETE FROM admin_quotation_temp WHERE no = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $itemNo);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $result;
    }

    /**
     * 세션의 임시 품목 모두 삭제
     */
    public function clearTempItems(string $sessionId): bool
    {
        $query = "DELETE FROM admin_quotation_temp WHERE admin_session_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $sessionId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $result;
    }

    /**
     * 임시 품목 → 견적 품목 변환
     *
     * @param array $tempItems admin_quotation_temp 배열
     * @return array 견적 품목 형식 배열
     */
    public function convertTempToQuoteItems(array $tempItems): array
    {
        $quoteItems = [];

        foreach ($tempItems as $temp) {
            // 수동 입력 품목
            if (!empty($temp['is_manual'])) {
                $quoteItems[] = $this->priceHelper->formatManualItem([
                    'product_name' => $temp['manual_product_name'],
                    'specification' => $temp['manual_specification'],
                    'quantity' => $temp['manual_quantity'],
                    'unit' => $temp['manual_unit'],
                    'supply_price' => $temp['manual_supply_price']
                ]);
            }
            // 계산기 품목
            else {
                $quoteItems[] = $this->priceHelper->formatCalculatorItem($temp);
            }
        }

        return $quoteItems;
    }
}
