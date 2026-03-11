<?php
/**
 * QE_QuoteEngine — 견적서/거래명세서 CRUD 엔진
 * 경로: /includes/quote-engine/QuoteEngine.php
 *
 * 테이블: qe_quotes, qe_items
 * 모든 SQL은 prepared statement + bind_param 3단계 검증.
 */

class QE_QuoteEngine
{
    /** @var mysqli */
    private $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    // ═══════════════════════════════════════════════════════════
    //  견적번호 생성
    // ═══════════════════════════════════════════════════════════

    /**
     * 견적번호 자동 생성
     *   quotation  → QE-20260311-001
     *   transaction → TX-20260311-001
     *
     * @param string $docType 'quotation' | 'transaction'
     * @return string
     */
    public function generateQuoteNo(string $docType = 'quotation'): string
    {
        $prefix = ($docType === 'transaction') ? 'TX' : 'QE';
        $today  = date('Ymd');
        $like   = "{$prefix}-{$today}-%";

        $stmt = mysqli_prepare($this->db,
            "SELECT quote_no FROM qe_quotes WHERE quote_no LIKE ? ORDER BY quote_no DESC LIMIT 1"
        );
        // bind_param 검증: ? = 1, 's' = 1, $like = 1 ✓
        mysqli_stmt_bind_param($stmt, 's', $like);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $seq = 1;
        if ($row) {
            // QE-20260311-003 → 003 → 4
            $lastSeq = (int)substr($row['quote_no'], -3);
            $seq = $lastSeq + 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $today, $seq);
    }

    // ═══════════════════════════════════════════════════════════
    //  CREATE
    // ═══════════════════════════════════════════════════════════

    /**
     * 견적서 저장 (마스터 + 아이템 트랜잭션)
     *
     * @param array $quoteData 마스터 데이터
     * @param array $items     품목 배열 [ [item_type, product_type, product_name, ...], ... ]
     * @return array ['success'=>bool, 'id'=>int, 'quote_no'=>string]
     */
    public function saveQuote(array $quoteData, array $items): array
    {
        $docType = $quoteData['doc_type'] ?? 'quotation';
        $quoteNo = $this->generateQuoteNo($docType);

        // 유효기한: MySQL DATE_ADD 사용 (PHP date 직렬화 회피)
        $validDays = (int)($quoteData['valid_days'] ?? 7);

        mysqli_begin_transaction($this->db);

        try {
            // ── 마스터 INSERT ──
            $sql = "INSERT INTO qe_quotes (
                        quote_no, doc_type, customer_id,
                        customer_company, customer_name, customer_phone,
                        customer_email, customer_address, customer_biz_no,
                        supply_total, vat_total, discount_amount, discount_reason, grand_total,
                        valid_days, valid_until, payment_terms,
                        customer_memo, admin_memo, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(CURRENT_DATE, INTERVAL ? DAY), ?, ?, ?, ?)";

            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) {
                throw new Exception('마스터 INSERT 준비 실패: ' . mysqli_error($this->db));
            }

            $customerId      = !empty($quoteData['customer_id']) ? (int)$quoteData['customer_id'] : null;
            $customerCompany = $quoteData['customer_company'] ?? null;
            $customerName    = $quoteData['customer_name'] ?? null;
            $customerPhone   = $quoteData['customer_phone'] ?? null;
            $customerEmail   = $quoteData['customer_email'] ?? null;
            $customerAddress = $quoteData['customer_address'] ?? null;
            $customerBizNo   = $quoteData['customer_biz_no'] ?? null;
            $supplyTotal     = (int)($quoteData['supply_total'] ?? 0);
            $vatTotal        = (int)($quoteData['vat_total'] ?? 0);
            $discountAmount  = (int)($quoteData['discount_amount'] ?? 0);
            $discountReason  = $quoteData['discount_reason'] ?? null;
            $grandTotal      = (int)($quoteData['grand_total'] ?? 0);
            $paymentTerms    = $quoteData['payment_terms'] ?? '발행일로부터 7일';
            $customerMemo    = $quoteData['customer_memo'] ?? null;
            $adminMemo       = $quoteData['admin_memo'] ?? null;
            $status          = $quoteData['status'] ?? 'draft';

            // bind_param 검증: ? = 20개, 타입 = 20자, 변수 = 20개 ✓
            // pos16: DATE_ADD(CURRENT_DATE, INTERVAL ? DAY) → validDays(int) 재사용
            mysqli_stmt_bind_param($stmt, 'ssissssssiiisiiissss',
                $quoteNo, $docType, $customerId,
                $customerCompany, $customerName, $customerPhone,
                $customerEmail, $customerAddress, $customerBizNo,
                $supplyTotal, $vatTotal, $discountAmount, $discountReason, $grandTotal,
                $validDays, $validDays, $paymentTerms,
                $customerMemo, $adminMemo, $status
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('마스터 INSERT 실패: ' . mysqli_stmt_error($stmt));
            }

            $quoteId = (int)mysqli_insert_id($this->db);
            mysqli_stmt_close($stmt);

            // ── 아이템 INSERT (반복) ──
            $itemSql = "INSERT INTO qe_items (
                            quote_id, item_no, item_type, product_type,
                            product_name, specification, quantity, unit,
                            unit_price, supply_price, extra_category, note, source_data
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $itemStmt = mysqli_prepare($this->db, $itemSql);
            if (!$itemStmt) {
                throw new Exception('아이템 INSERT 준비 실패: ' . mysqli_error($this->db));
            }

            foreach ($items as $idx => $item) {
                $itemNo       = $idx + 1;
                $itemType     = $item['item_type'] ?? 'manual';
                $productType  = $item['product_type'] ?? null;
                $productName  = $item['product_name'] ?? '품목';
                $specification = $item['specification'] ?? null;
                $quantity     = floatval($item['quantity'] ?? 1);
                $unit         = $item['unit'] ?? '개';
                $unitPrice    = (int)($item['unit_price'] ?? 0);
                $supplyPrice  = (int)($item['supply_price'] ?? 0);
                $extraCat     = $item['extra_category'] ?? null;
                $note         = $item['note'] ?? null;
                $sourceData   = isset($item['source_data']) ? json_encode($item['source_data'], JSON_UNESCAPED_UNICODE) : null;

                $quantityStr  = (string)$quantity; // bind as string for DECIMAL

                // bind_param 검증: ? = 13개, 타입 = 13자, 변수 = 13개 ✓
                // i(quoteId) i(itemNo) s(itemType) s(productType) s(productName)
                // s(specification) s(quantity) s(unit) i(unitPrice) i(supplyPrice)
                // s(extraCat) s(note) s(sourceData)
                mysqli_stmt_bind_param($itemStmt, 'iissssssiiiss',
                    $quoteId, $itemNo, $itemType, $productType,
                    $productName, $specification, $quantityStr, $unit,
                    $unitPrice, $supplyPrice, $extraCat, $note, $sourceData
                );

                if (!mysqli_stmt_execute($itemStmt)) {
                    throw new Exception("아이템 #{$itemNo} INSERT 실패: " . mysqli_stmt_error($itemStmt));
                }
            }
            mysqli_stmt_close($itemStmt);

            // 합계 재계산
            $this->recalculateTotals($quoteId);

            mysqli_commit($this->db);

            return [
                'success'  => true,
                'id'       => $quoteId,
                'quote_no' => $quoteNo,
            ];

        } catch (Exception $e) {
            mysqli_rollback($this->db);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  READ
    // ═══════════════════════════════════════════════════════════

    /**
     * 견적서 단건 조회 (마스터 + 아이템)
     */
    public function getQuote(int $quoteId): ?array
    {
        // 마스터
        $stmt = mysqli_prepare($this->db, "SELECT * FROM qe_quotes WHERE id = ?");
        if (!$stmt) return null;
        mysqli_stmt_bind_param($stmt, 'i', $quoteId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $quote = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$quote) return null;

        // 아이템
        $quote['items'] = $this->getItems($quoteId);

        return $quote;
    }

    /**
     * quote_no 로 조회
     */
    public function getQuoteByNo(string $quoteNo): ?array
    {
        $stmt = mysqli_prepare($this->db, "SELECT id FROM qe_quotes WHERE quote_no = ?");
        if (!$stmt) return null;
        mysqli_stmt_bind_param($stmt, 's', $quoteNo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row) return null;
        return $this->getQuote((int)$row['id']);
    }

    /**
     * 아이템 목록 조회
     */
    public function getItems(int $quoteId): array
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT * FROM qe_items WHERE quote_id = ? ORDER BY item_no ASC"
        );
        if (!$stmt) return [];

        mysqli_stmt_bind_param($stmt, 'i', $quoteId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // source_data JSON 디코딩
            if (!empty($row['source_data'])) {
                $row['source_data'] = json_decode($row['source_data'], true);
            }
            $items[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $items;
    }

    /**
     * 목록 조회 (페이지네이션 + 검색 + 필터)
     *
     * @param array $filters  status, doc_type, search, date_from, date_to
     * @param int   $page     페이지 번호 (1-based)
     * @param int   $perPage  페이지당 개수
     * @return array ['items'=>[...], 'total'=>N, 'page'=>N, 'pages'=>N]
     */
    public function listQuotes(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $conditions = [];
        $types = '';
        $values = [];

        // 상태 필터
        if (!empty($filters['status'])) {
            $conditions[] = 'status = ?';
            $types .= 's';
            $values[] = $filters['status'];
        }

        // 문서유형 필터
        if (!empty($filters['doc_type'])) {
            $conditions[] = 'doc_type = ?';
            $types .= 's';
            $values[] = $filters['doc_type'];
        }

        // 검색 (견적번호, 고객명, 회사명)
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $conditions[] = '(quote_no LIKE ? OR customer_name LIKE ? OR customer_company LIKE ?)';
            $types .= 'sss';
            $values[] = $searchTerm;
            $values[] = $searchTerm;
            $values[] = $searchTerm;
        }

        // 날짜 필터
        if (!empty($filters['date_from'])) {
            $conditions[] = 'created_at >= ?';
            $types .= 's';
            $values[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = 'created_at <= ?';
            $types .= 's';
            $values[] = $filters['date_to'] . ' 23:59:59';
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

        // ── COUNT ──
        $countSql = "SELECT COUNT(*) AS cnt FROM qe_quotes {$where}";
        $countStmt = mysqli_prepare($this->db, $countSql);

        if ($types !== '' && count($values) > 0) {
            mysqli_stmt_bind_param($countStmt, $types, ...$values);
        }
        mysqli_stmt_execute($countStmt);
        $countResult = mysqli_stmt_get_result($countStmt);
        $total = (int)(mysqli_fetch_assoc($countResult)['cnt'] ?? 0);
        mysqli_stmt_close($countStmt);

        $pages = ($perPage > 0) ? (int)ceil($total / $perPage) : 1;
        $page  = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        // ── SELECT ──
        $dataSql = "SELECT * FROM qe_quotes {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $dataTypes  = $types . 'ii';
        $dataValues = array_merge($values, [$perPage, $offset]);

        $dataStmt = mysqli_prepare($this->db, $dataSql);

        // bind_param 검증
        $placeholders = substr_count($dataSql, '?');
        $typeLen = strlen($dataTypes);
        $valLen  = count($dataValues);
        if ($placeholders !== $typeLen || $typeLen !== $valLen) {
            return ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 0];
        }

        mysqli_stmt_bind_param($dataStmt, $dataTypes, ...$dataValues);
        mysqli_stmt_execute($dataStmt);
        $result = mysqli_stmt_get_result($dataStmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        mysqli_stmt_close($dataStmt);

        return [
            'items' => $items,
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  UPDATE
    // ═══════════════════════════════════════════════════════════

    /**
     * 견적서 수정 (마스터 UPDATE + 아이템 DELETE/INSERT)
     */
    public function updateQuote(int $quoteId, array $quoteData, array $items): array
    {
        // 존재 확인
        $existing = $this->getQuote($quoteId);
        if (!$existing) {
            return ['success' => false, 'error' => '견적서를 찾을 수 없습니다'];
        }

        // 유효기한: MySQL DATE_ADD 사용
        $validDays = (int)($quoteData['valid_days'] ?? $existing['valid_days'] ?? 7);

        mysqli_begin_transaction($this->db);

        try {
            // ── 마스터 UPDATE ──
            $sql = "UPDATE qe_quotes SET
                        customer_id = ?, customer_company = ?, customer_name = ?,
                        customer_phone = ?, customer_email = ?, customer_address = ?,
                        customer_biz_no = ?,
                        discount_amount = ?, discount_reason = ?,
                        valid_days = ?, valid_until = DATE_ADD(created_at, INTERVAL ? DAY), payment_terms = ?,
                        customer_memo = ?, admin_memo = ?, status = ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) throw new Exception('UPDATE 준비 실패: ' . mysqli_error($this->db));

            $customerId      = !empty($quoteData['customer_id']) ? (int)$quoteData['customer_id'] : null;
            $customerCompany = $quoteData['customer_company'] ?? $existing['customer_company'];
            $customerName    = $quoteData['customer_name'] ?? $existing['customer_name'];
            $customerPhone   = $quoteData['customer_phone'] ?? $existing['customer_phone'];
            $customerEmail   = $quoteData['customer_email'] ?? $existing['customer_email'];
            $customerAddress = $quoteData['customer_address'] ?? $existing['customer_address'];
            $customerBizNo   = $quoteData['customer_biz_no'] ?? $existing['customer_biz_no'];
            $discountAmount  = (int)($quoteData['discount_amount'] ?? $existing['discount_amount']);
            $discountReason  = $quoteData['discount_reason'] ?? $existing['discount_reason'];
            $paymentTerms    = $quoteData['payment_terms'] ?? $existing['payment_terms'];
            $customerMemo    = $quoteData['customer_memo'] ?? $existing['customer_memo'];
            $adminMemo       = $quoteData['admin_memo'] ?? $existing['admin_memo'];
            $status          = $quoteData['status'] ?? $existing['status'];

            // bind_param 검증: ? = 16개, 타입 = 16자, 변수 = 16개 ✓
            // i(custId) s×6 i(discount) s(reason) i(days) i(interval) s(terms) s(memo) s(admin) s(status) i(id)
            mysqli_stmt_bind_param($stmt, 'issssssisiiisssi',
                $customerId, $customerCompany, $customerName,
                $customerPhone, $customerEmail, $customerAddress,
                $customerBizNo,
                $discountAmount, $discountReason,
                $validDays, $validDays, $paymentTerms,
                $customerMemo, $adminMemo, $status,
                $quoteId
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('UPDATE 실패: ' . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

            // ── 기존 아이템 삭제 → 재삽입 ──
            $delStmt = mysqli_prepare($this->db, "DELETE FROM qe_items WHERE quote_id = ?");
            mysqli_stmt_bind_param($delStmt, 'i', $quoteId);
            mysqli_stmt_execute($delStmt);
            mysqli_stmt_close($delStmt);

            // 아이템 INSERT
            $itemSql = "INSERT INTO qe_items (
                            quote_id, item_no, item_type, product_type,
                            product_name, specification, quantity, unit,
                            unit_price, supply_price, extra_category, note, source_data
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $itemStmt = mysqli_prepare($this->db, $itemSql);
            if (!$itemStmt) throw new Exception('아이템 INSERT 준비 실패');

            foreach ($items as $idx => $item) {
                $itemNo       = $idx + 1;
                $itemType     = $item['item_type'] ?? 'manual';
                $productType  = $item['product_type'] ?? null;
                $productName  = $item['product_name'] ?? '품목';
                $specification = $item['specification'] ?? null;
                $quantity     = (string)floatval($item['quantity'] ?? 1);
                $unit         = $item['unit'] ?? '개';
                $unitPrice    = (int)($item['unit_price'] ?? 0);
                $supplyPrice  = (int)($item['supply_price'] ?? 0);
                $extraCat     = $item['extra_category'] ?? null;
                $note         = $item['note'] ?? null;
                $sourceData   = isset($item['source_data']) ? json_encode($item['source_data'], JSON_UNESCAPED_UNICODE) : null;

                // bind_param 검증: ? = 13, 타입 = 13, 변수 = 13 ✓
                mysqli_stmt_bind_param($itemStmt, 'iissssssiiiss',
                    $quoteId, $itemNo, $itemType, $productType,
                    $productName, $specification, $quantity, $unit,
                    $unitPrice, $supplyPrice, $extraCat, $note, $sourceData
                );

                if (!mysqli_stmt_execute($itemStmt)) {
                    throw new Exception("아이템 #{$itemNo} INSERT 실패");
                }
            }
            mysqli_stmt_close($itemStmt);

            // 합계 재계산
            $this->recalculateTotals($quoteId);

            mysqli_commit($this->db);

            return ['success' => true, 'id' => $quoteId, 'quote_no' => $existing['quote_no']];

        } catch (Exception $e) {
            mysqli_rollback($this->db);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 상태만 변경
     */
    public function updateStatus(int $quoteId, string $status): bool
    {
        $allowed = ['draft', 'completed', 'sent', 'expired'];
        if (!in_array($status, $allowed, true)) return false;

        $sql = "UPDATE qe_quotes SET status = ?";
        $types = 's';
        $values = [$status];

        // sent 상태 시 sent_at 기록
        if ($status === 'sent') {
            $sql .= ", sent_at = NOW()";
        }

        $sql .= " WHERE id = ?";
        $types .= 'i';
        $values[] = $quoteId;

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return false;

        // bind_param 검증: ? = 2, 타입 = 2, 변수 = 2 ✓
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    }

    // ═══════════════════════════════════════════════════════════
    //  DELETE
    // ═══════════════════════════════════════════════════════════

    /**
     * 견적서 삭제 (아이템은 CASCADE로 자동 삭제)
     */
    public function deleteQuote(int $quoteId): array
    {
        $stmt = mysqli_prepare($this->db, "DELETE FROM qe_quotes WHERE id = ?");
        if (!$stmt) return ['success' => false, 'error' => 'DELETE 준비 실패'];

        mysqli_stmt_bind_param($stmt, 'i', $quoteId);
        $ok = mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if (!$ok || $affected === 0) {
            return ['success' => false, 'error' => '견적서를 찾을 수 없거나 삭제 실패'];
        }

        return ['success' => true, 'deleted_id' => $quoteId];
    }

    // ═══════════════════════════════════════════════════════════
    //  CONVERT (견적서 → 거래명세서)
    // ═══════════════════════════════════════════════════════════

    /**
     * 견적서를 거래명세서로 변환 (복사 + 새 번호)
     */
    public function convertToTransaction(int $quoteId): array
    {
        $source = $this->getQuote($quoteId);
        if (!$source) {
            return ['success' => false, 'error' => '원본 견적서를 찾을 수 없습니다'];
        }

        // 마스터 데이터 복사 (doc_type 변경)
        $newData = $source;
        $newData['doc_type'] = 'transaction';
        $newData['status']   = 'draft';
        unset($newData['id'], $newData['quote_no'], $newData['items'],
              $newData['created_at'], $newData['updated_at'], $newData['sent_at']);

        // 아이템 데이터 복사
        $newItems = [];
        foreach ($source['items'] as $item) {
            $newItem = $item;
            unset($newItem['id'], $newItem['quote_id'], $newItem['created_at']);
            $newItems[] = $newItem;
        }

        $result = $this->saveQuote($newData, $newItems);

        if ($result['success']) {
            $result['source_quote_id'] = $quoteId;
            $result['source_quote_no'] = $source['quote_no'];
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════════
    //  Private helpers
    // ═══════════════════════════════════════════════════════════

    /**
     * 아이템 합산 → 마스터 금액 필드 업데이트
     */
    private function recalculateTotals(int $quoteId): void
    {
        // 공급가 합산
        $stmt = mysqli_prepare($this->db,
            "SELECT COALESCE(SUM(supply_price), 0) AS supply_sum FROM qe_items WHERE quote_id = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $quoteId);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $supplyTotal = (int)($row['supply_sum'] ?? 0);
        mysqli_stmt_close($stmt);

        $vatTotal = (int)round($supplyTotal * 0.1);

        // 할인금액 현재값 조회
        $stmt2 = mysqli_prepare($this->db, "SELECT discount_amount FROM qe_quotes WHERE id = ?");
        mysqli_stmt_bind_param($stmt2, 'i', $quoteId);
        mysqli_stmt_execute($stmt2);
        $row2 = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
        $discount = (int)($row2['discount_amount'] ?? 0);
        mysqli_stmt_close($stmt2);

        $grandTotal = $supplyTotal + $vatTotal - $discount;

        // UPDATE
        $upStmt = mysqli_prepare($this->db,
            "UPDATE qe_quotes SET supply_total = ?, vat_total = ?, grand_total = ? WHERE id = ?"
        );
        // bind_param 검증: ? = 4, 타입 = 4, 변수 = 4 ✓
        mysqli_stmt_bind_param($upStmt, 'iiii', $supplyTotal, $vatTotal, $grandTotal, $quoteId);
        mysqli_stmt_execute($upStmt);
        mysqli_stmt_close($upStmt);
    }
}
