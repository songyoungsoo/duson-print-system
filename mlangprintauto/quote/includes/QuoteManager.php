<?php
/**
 * 견적서/거래명세표 관리 클래스
 */

require_once __DIR__ . '/ProductSpecFormatter.php';
require_once __DIR__ . '/../../../includes/QuantityFormatter.php';

class QuoteManager {
    private $db;
    private $formatter;

    public function __construct($db) {
        $this->db = $db;
        $this->formatter = new ProductSpecFormatter($db);
    }

    /**
     * 견적번호 생성
     * @param string $type 'quotation' | 'transaction'
     * @return string QT-YYYYMMDD-NNN 또는 TX-YYYYMMDD-NNN
     */
    public function generateQuoteNo($type = 'quotation') {
        $prefix = ($type === 'transaction') ? 'TX' : 'QT';
        $today = date('Ymd');
        $pattern = "{$prefix}-{$today}-%";

        // 오늘 생성된 문서 수 조회
        $query = "SELECT COUNT(*) as cnt FROM quotes WHERE quote_no LIKE ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $pattern);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $nextNum = intval($row['cnt']) + 1;
        $quoteNo = "{$prefix}-{$today}-" . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        // 중복 체크
        while ($this->quoteNoExists($quoteNo)) {
            $nextNum++;
            $quoteNo = "{$prefix}-{$today}-" . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        return $quoteNo;
    }

    /**
     * 견적번호 중복 체크
     */
    private function quoteNoExists($quoteNo) {
        $query = "SELECT id FROM quotes WHERE quote_no = ? LIMIT 1";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $quoteNo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        return $exists;
    }

    /**
     * 공개 토큰 생성
     */
    public function generatePublicToken() {
        return bin2hex(random_bytes(32));
    }

    /**
     * 장바구니에서 견적서 생성
     */
    public function createFromCart($sessionId, $data) {
        mysqli_begin_transaction($this->db);

        try {
            // 장바구니 아이템 조회
            $cartItems = $this->getCartItems($sessionId);
            if (empty($cartItems)) {
                throw new Exception('장바구니가 비어있습니다.');
            }

            // 견적서 기본 정보 저장
            $quoteNo = $this->generateQuoteNo('quotation');
            $publicToken = $this->generatePublicToken();
            $validUntil = date('Y-m-d', strtotime('+' . ($data['valid_days'] ?? 7) . ' days'));

            $query = "INSERT INTO quotes (
                quote_no, quote_type, public_token, session_id,
                customer_name, customer_company, customer_phone, customer_email, recipient_email,
                delivery_type, delivery_address, delivery_price,
                supply_total, vat_total, discount_amount, discount_reason, grand_total,
                payment_terms, valid_days, valid_until,
                notes, status, created_by
            ) VALUES (?, 'quotation', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)";

            $stmt = mysqli_prepare($this->db, $query);
            if (!$stmt) {
                throw new Exception('쿼리 준비 실패: ' . mysqli_error($this->db));
            }

            $customerName = $data['customer_name'] ?? '';
            $customerCompany = $data['customer_company'] ?? '';
            $customerPhone = $data['customer_phone'] ?? '';
            $customerEmail = $data['customer_email'] ?? '';
            $recipientEmail = $data['recipient_email'] ?? '';
            $deliveryType = $data['delivery_type'] ?? '';
            $deliveryAddress = $data['delivery_address'] ?? '';
            $deliveryPrice = intval($data['delivery_price'] ?? 0);
            $supplyTotal = intval($data['supply_total'] ?? 0);
            $vatTotal = intval($data['vat_total'] ?? 0);
            $discountAmount = intval($data['discount_amount'] ?? 0);
            $discountReason = $data['discount_reason'] ?? '';
            $grandTotal = intval($data['grand_total'] ?? 0);
            $paymentTerms = $data['payment_terms'] ?? '발행일로부터 7일';
            $validDays = intval($data['valid_days'] ?? 7);
            $notes = $data['notes'] ?? '';
            $createdBy = intval($data['created_by'] ?? 0);

            // 21개 파라미터: s×10 + i×4 + s×1 + i×1 + s×1 + i×1 + s×1 + s×1 + i×1 = ssssssssssiiiisissssi
            mysqli_stmt_bind_param($stmt, "ssssssssssiiiisissssi",
                $quoteNo,           // 1: s
                $publicToken,       // 2: s
                $sessionId,         // 3: s
                $customerName,      // 4: s
                $customerCompany,   // 5: s
                $customerPhone,     // 6: s
                $customerEmail,     // 7: s
                $recipientEmail,    // 8: s
                $deliveryType,      // 9: s
                $deliveryAddress,   // 10: s
                $deliveryPrice,     // 11: i
                $supplyTotal,       // 12: i
                $vatTotal,          // 13: i
                $discountAmount,    // 14: i
                $discountReason,    // 15: s
                $grandTotal,        // 16: i
                $paymentTerms,      // 17: s
                $validDays,         // 18: i (수정: s → i)
                $validUntil,        // 19: s (수정: i → s)
                $notes,             // 20: s
                $createdBy          // 21: i
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('견적서 저장 실패: ' . mysqli_stmt_error($stmt));
            }

            $quoteId = mysqli_insert_id($this->db);
            mysqli_stmt_close($stmt);

            // 장바구니 아이템을 견적 품목으로 저장
            $itemNo = 1;
            foreach ($cartItems as $item) {
                $this->addItemFromCart($quoteId, $itemNo, $item);
                $itemNo++;
            }

            // quotation_temp 품목 저장 (계산기 모달에서 추가한 품목)
            $quoteTempItems = $this->getQuoteTempItems($sessionId);
            if (!empty($quoteTempItems)) {
                error_log("[QuoteManager::createFromCart] Processing " . count($quoteTempItems) . " items from quotation_temp");
                foreach ($quoteTempItems as $tempItem) {
                    error_log("[QuoteManager::createFromCart] Adding quotation_temp item #$itemNo: " . ($tempItem['product_name'] ?? ''));
                    $this->addItemFromQuoteTemp($quoteId, $itemNo, $tempItem);
                    $itemNo++;
                }
            }

            // 추가 품목 (수동 입력) 저장
            if (!empty($data['items']) && is_array($data['items'])) {
                error_log("[QuoteManager::createFromCart] Processing " . count($data['items']) . " items from POST");
                foreach ($data['items'] as $idx => $item) {
                    $sourceType = $item['source_type'] ?? '';
                    $productName = trim($item['product_name'] ?? '');

                    // __direct__ 선택 시 직접입력 필드의 값을 확인
                    if ($productName === '__direct__' || empty($productName)) {
                        $productName = trim($item['product_name_custom'] ?? '');
                    }

                    error_log("[QuoteManager::createFromCart] Item[$idx]: source_type='$sourceType', product_name='$productName'");

                    // 장바구니/quotation_temp에서 온 항목은 이미 위에서 추가했으므로 제외
                    if (!in_array($sourceType, ['cart', 'quotation_temp']) && !empty($productName)) {
                        error_log("[QuoteManager::createFromCart] Adding manual item #$itemNo: $productName");
                        $this->addManualItem($quoteId, $itemNo, $item);
                        $itemNo++;
                    } else if (in_array($sourceType, ['cart', 'quotation_temp'])) {
                        error_log("[QuoteManager::createFromCart] Skipping $sourceType item (already added): $productName");
                    } else {
                        error_log("[QuoteManager::createFromCart] Skipping empty item at index $idx");
                    }
                }
            } else {
                error_log("[QuoteManager::createFromCart] No items array in POST data");
            }

            // quotation_temp 품목 삭제 (견적서에 반영되었으므로)
            if (!empty($quoteTempItems)) {
                $this->clearQuoteTempItems($sessionId);
                error_log("[QuoteManager::createFromCart] Cleared " . count($quoteTempItems) . " items from quotation_temp");
            }

            mysqli_commit($this->db);

            return [
                'success' => true,
                'quote_id' => $quoteId,
                'quote_no' => $quoteNo,
                'public_token' => $publicToken,
                'public_url' => $this->getPublicUrl($publicToken)
            ];

        } catch (Exception $e) {
            mysqli_rollback($this->db);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 빈 견적서 생성 (수동 입력용)
     */
    public function createEmpty($data) {
        mysqli_begin_transaction($this->db);

        try {
            $quoteNo = $this->generateQuoteNo($data['quote_type'] ?? 'quotation');
            $publicToken = $this->generatePublicToken();
            $validUntil = date('Y-m-d', strtotime('+' . ($data['valid_days'] ?? 7) . ' days'));

            $query = "INSERT INTO quotes (
                quote_no, quote_type, public_token,
                customer_name, customer_company, customer_phone, customer_email, recipient_email,
                delivery_type, delivery_address, delivery_price,
                supply_total, vat_total, discount_amount, discount_reason, grand_total,
                payment_terms, valid_days, valid_until,
                notes, status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)";

            $stmt = mysqli_prepare($this->db, $query);
            if (!$stmt) {
                throw new Exception('쿼리 준비 실패: ' . mysqli_error($this->db));
            }

            $quoteType = $data['quote_type'] ?? 'quotation';
            $customerName = $data['customer_name'] ?? '';
            $customerCompany = $data['customer_company'] ?? '';
            $customerPhone = $data['customer_phone'] ?? '';
            $customerEmail = $data['customer_email'] ?? '';
            $recipientEmail = $data['recipient_email'] ?? '';
            $deliveryType = $data['delivery_type'] ?? '';
            $deliveryAddress = $data['delivery_address'] ?? '';
            $deliveryPrice = intval($data['delivery_price'] ?? 0);
            $supplyTotal = intval($data['supply_total'] ?? 0);
            $vatTotal = intval($data['vat_total'] ?? 0);
            $discountAmount = intval($data['discount_amount'] ?? 0);
            $discountReason = $data['discount_reason'] ?? '';
            $grandTotal = intval($data['grand_total'] ?? 0);
            $paymentTerms = $data['payment_terms'] ?? '발행일로부터 7일';
            $validDays = intval($data['valid_days'] ?? 7);
            $notes = $data['notes'] ?? '';
            $createdBy = intval($data['created_by'] ?? 0);

            // 21개 파라미터: s×10 + i×4 + s×1 + i×1 + s×1 + i×1 + s×1 + s×1 + i×1 = ssssssssssiiiisissssi
            mysqli_stmt_bind_param($stmt, "ssssssssssiiiisissssi",
                $quoteNo,           // 1: s
                $quoteType,         // 2: s
                $publicToken,       // 3: s
                $customerName,      // 4: s
                $customerCompany,   // 5: s
                $customerPhone,     // 6: s
                $customerEmail,     // 7: s
                $recipientEmail,    // 8: s
                $deliveryType,      // 9: s
                $deliveryAddress,   // 10: s
                $deliveryPrice,     // 11: i
                $supplyTotal,       // 12: i
                $vatTotal,          // 13: i
                $discountAmount,    // 14: i
                $discountReason,    // 15: s
                $grandTotal,        // 16: i
                $paymentTerms,      // 17: s
                $validDays,         // 18: i (수정: s → i)
                $validUntil,        // 19: s (수정: i → s)
                $notes,             // 20: s
                $createdBy          // 21: i
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('견적서 저장 실패: ' . mysqli_stmt_error($stmt));
            }

            $quoteId = mysqli_insert_id($this->db);
            mysqli_stmt_close($stmt);

            $itemNo = 1;

            // quotation_temp 품목 저장 (계산기 모달에서 추가한 품목)
            $sessionId = session_id();
            $quoteTempItems = $this->getQuoteTempItems($sessionId);
            if (!empty($quoteTempItems)) {
                error_log("[QuoteManager::createEmpty] Processing " . count($quoteTempItems) . " items from quotation_temp");
                foreach ($quoteTempItems as $tempItem) {
                    error_log("[QuoteManager::createEmpty] Adding quotation_temp item #$itemNo: " . ($tempItem['product_name'] ?? ''));
                    $this->addItemFromQuoteTemp($quoteId, $itemNo, $tempItem);
                    $itemNo++;
                }
            }

            // 품목 저장 (수동 입력)
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $sourceType = $item['source_type'] ?? '';
                    $productName = trim($item['product_name'] ?? '');

                    // __direct__ 선택 시 직접입력 필드의 값을 확인
                    if ($productName === '__direct__' || empty($productName)) {
                        $productName = trim($item['product_name_custom'] ?? '');
                    }

                    // quotation_temp에서 온 항목은 이미 위에서 추가했으므로 제외
                    if ($sourceType !== 'quotation_temp' && !empty($productName)) {
                        $this->addManualItem($quoteId, $itemNo, $item);
                        $itemNo++;
                    }
                }
            }

            // quotation_temp 품목 삭제 (견적서에 반영되었으므로)
            if (!empty($quoteTempItems)) {
                $this->clearQuoteTempItems($sessionId);
                error_log("[QuoteManager::createEmpty] Cleared " . count($quoteTempItems) . " items from quotation_temp");
            }

            mysqli_commit($this->db);

            return [
                'success' => true,
                'quote_id' => $quoteId,
                'quote_no' => $quoteNo,
                'public_token' => $publicToken,
                'public_url' => $this->getPublicUrl($publicToken)
            ];

        } catch (Exception $e) {
            mysqli_rollback($this->db);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 장바구니 아이템 조회
     */
    private function getCartItems($sessionId) {
        $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no ASC";
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
     * quotation_temp 품목 조회
     */
    private function getQuoteTempItems($sessionId) {
        $query = "SELECT * FROM quotation_temp WHERE session_id = ? ORDER BY created_at ASC";
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
     * quotation_temp 품목 삭제
     */
    private function clearQuoteTempItems($sessionId) {
        $query = "DELETE FROM quotation_temp WHERE session_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $sessionId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    /**
     * 장바구니 아이템을 견적 품목으로 추가
     * [하이브리드 모델] qty_val, qty_unit, qty_sheets, is_manual 포함
     */
    private function addItemFromCart($quoteId, $itemNo, $cartItem) {
        $productType = $cartItem['product_type'] ?? '';

        // 레거시 스티커 감지
        if (empty($productType) && !empty($cartItem['jong']) && !empty($cartItem['garo'])) {
            $productType = 'sticker';
        }

        $productName = ProductSpecFormatter::getProductTypeName($productType);
        $specification = $this->formatter->format($cartItem);
        $quantity = ProductSpecFormatter::getQuantity($cartItem);
        $unit = ProductSpecFormatter::getUnit($cartItem);
        $supplyPrice = ProductSpecFormatter::getSupplyPrice($cartItem);
        $totalPrice = ProductSpecFormatter::getPrice($cartItem);
        $vatAmount = $totalPrice - $supplyPrice;

        // === 하이브리드 모델: 표준화된 수량 데이터 ===
        $qtyVal = floatval($quantity);
        // ✅ FIX: getProductUnitCode() 사용 (product_type → 단위코드)
        $qtyUnit = QuantityFormatter::getProductUnitCode($productType);
        $qtySheets = null;

        // 전단지/리플렛: 매수 계산
        $flyerMesu = intval($cartItem['flyer_mesu'] ?? $cartItem['mesu'] ?? 0);
        if (in_array($productType, ['inserted', 'leaflet'])) {
            $qtySheets = $flyerMesu > 0 ? $flyerMesu : null;
            if ($flyerMesu > 0) {
                $unitPrice = round($supplyPrice / $flyerMesu, 1);
            } else {
                $unitPrice = $quantity > 0 ? intval($supplyPrice / $quantity) : 0;
            }
        } else {
            $unitPrice = $quantity > 0 ? intval($supplyPrice / $quantity) : 0;
        }

        // 장바구니 품목은 is_manual = 0 (정규 품목)
        $isManual = 0;

        $query = "INSERT INTO quote_items (
            quote_id, item_no, product_type, product_name, specification,
            quantity, qty_val, qty_unit, qty_sheets, unit, unit_price,
            supply_price, vat_amount, total_price,
            source_type, is_manual, source_id, source_data
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'cart', ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        $sourceData = json_encode($cartItem, JSON_UNESCAPED_UNICODE);
        $sourceId = intval($cartItem['no'] ?? 0);

        // 17개 파라미터: i i s s s d d s i s d i i i i i s
        mysqli_stmt_bind_param($stmt, "iisssddsisdiiiiis",
            $quoteId, $itemNo, $productType, $productName, $specification,
            $quantity, $qtyVal, $qtyUnit, $qtySheets, $unit, $unitPrice,
            $supplyPrice, $vatAmount, $totalPrice,
            $isManual, $sourceId, $sourceData
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    /**
     * quotation_temp 품목을 견적 품목으로 추가
     * [하이브리드 모델] qty_val, qty_unit, qty_sheets, is_manual 포함
     */
    private function addItemFromQuoteTemp($quoteId, $itemNo, $tempItem) {
        // 1. 제품 정보 추출
        $productType = $tempItem['product_type'] ?? '';

        // ✅ 레거시 스티커 감지: product_type이 없으면 jong/garo/sero로 판별
        if (empty($productType) && !empty($tempItem['jong']) && !empty($tempItem['garo'])) {
            $productType = 'sticker';
        }

        $productName = ProductSpecFormatter::getProductTypeName($productType);
        $specification = $this->formatter->format($tempItem);

        // 2. 수량 및 단위 추출
        $quantity = ProductSpecFormatter::getQuantity($tempItem);
        $unit = ProductSpecFormatter::getUnit($tempItem);

        // 3. 가격 정보 추출
        $supplyPrice = ProductSpecFormatter::getSupplyPrice($tempItem);
        $totalPrice = ProductSpecFormatter::getPrice($tempItem);
        $vatAmount = $totalPrice - $supplyPrice;

        // === 하이브리드 모델: 표준화된 수량 데이터 ===
        $qtyVal = floatval($quantity);
        // ✅ FIX: getProductUnitCode() 사용 (product_type → 단위코드)
        $qtyUnit = QuantityFormatter::getProductUnitCode($productType);
        $qtySheets = null;

        // 4. 단가 계산 및 매수 처리
        $flyerMesu = intval($tempItem['flyer_mesu'] ?? $tempItem['mesu'] ?? 0);
        if (in_array($productType, ['inserted', 'leaflet'])) {
            $qtySheets = $flyerMesu > 0 ? $flyerMesu : null;
            if ($flyerMesu > 0) {
                $unitPrice = round($supplyPrice / $flyerMesu, 1);
            } else {
                $unitPrice = $quantity > 0 ? round($supplyPrice / $quantity, 1) : 0;
            }
        } else {
            $unitPrice = $quantity > 0 ? round($supplyPrice / $quantity, 1) : 0;
        }

        // quotation_temp 품목은 is_manual = 0 (정규 품목)
        $isManual = 0;

        // 5. 메모 추출
        $notes = $tempItem['MY_comment'] ?? $tempItem['work_memo'] ?? '';

        // 6. source_data JSON 생성
        $sourceData = json_encode($tempItem, JSON_UNESCAPED_UNICODE);

        // 7. DB INSERT (하이브리드 필드 포함)
        $query = "INSERT INTO quote_items (
            quote_id, item_no, product_type, product_name, specification,
            quantity, qty_val, qty_unit, qty_sheets, unit, unit_price,
            supply_price, vat_amount, total_price,
            source_type, is_manual, source_id, source_data, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'quotation_temp', ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        $sourceId = intval($tempItem['no'] ?? $tempItem['id'] ?? 0);

        // 18개 파라미터
        mysqli_stmt_bind_param($stmt, "iisssddsisdiiiiiss",
            $quoteId, $itemNo, $productType, $productName, $specification,
            $quantity, $qtyVal, $qtyUnit, $qtySheets, $unit, $unitPrice,
            $supplyPrice, $vatAmount, $totalPrice,
            $isManual, $sourceId, $sourceData, $notes
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    /**
     * 수동 입력 품목 추가
     * [하이브리드 모델] qty_val, qty_unit, qty_sheets, is_manual 포함
     *
     * 비규격 품목(배너, 현수막 등)도 동일한 데이터 구조 사용
     * - is_manual = 1 (수동 입력)
     * - qty_unit = 'E' (기본: 개) 또는 사용자 지정 코드
     */
    private function addManualItem($quoteId, $itemNo, $item) {
        // 변수 추출 (PHP bind_param은 참조만 허용)
        $productType = $item['product_type'] ?? '';
        $productName = trim($item['product_name'] ?? '');

        // __direct__ 선택 시 직접입력 필드(product_name_custom)의 값을 사용
        if ($productName === '__direct__' || empty($productName)) {
            $productName = trim($item['product_name_custom'] ?? '');
        }

        $specification = $item['specification'] ?? '';
        $unit = $item['unit'] ?? '개';
        $notes = $item['notes'] ?? '';

        // 필수 필드 검증 - 품목명이 비어있으면 저장하지 않음
        if (empty($productName)) {
            error_log("[QuoteManager] 경고: 품목명이 비어있어 저장하지 않음 (quote_id=$quoteId, item_no=$itemNo)");
            return;
        }

        $quantity = floatval($item['quantity'] ?? 1);
        $flyerMesu = intval($item['flyer_mesu'] ?? $item['mesu'] ?? 0);

        // 수량 검증 - 0 이하 방지
        if ($quantity <= 0) {
            error_log("[QuoteManager] 경고: 품목 '$productName'의 수량이 0 이하입니다. 기본값 1로 설정 (quote_id=$quoteId)");
            $quantity = 1;
        }

        // === 하이브리드 모델: 표준화된 수량 데이터 ===
        $qtyVal = floatval($quantity);

        // 단위 코드 결정: 정규 품목이면 코드 조회, 비규격이면 텍스트→코드 변환
        $unitTextToCode = [
            '연' => 'R', '매' => 'S', '부' => 'B', '권' => 'V', '장' => 'P', '개' => 'E',
            '식' => 'E', '세트' => 'E', '박스' => 'E', '롤' => 'E', 'm²' => 'E', '헤베' => 'E'
        ];
        if (!empty($productType) && isset(QuantityFormatter::PRODUCT_UNITS[$productType])) {
            // ✅ FIX: getProductUnitCode() 사용 (product_type → 단위코드)
            $qtyUnit = QuantityFormatter::getProductUnitCode($productType);
        } else {
            $qtyUnit = $unitTextToCode[$unit] ?? 'E';
        }

        $qtySheets = null;
        if (in_array($productType, ['inserted', 'leaflet']) && $flyerMesu > 0) {
            $qtySheets = $flyerMesu;
        }

        // 수동 입력 품목: is_manual = 1
        $isManual = 1;

        // 공급가 계산
        if (isset($item['supply_price']) && $item['supply_price'] !== '' && $item['supply_price'] !== null) {
            $supplyPrice = intval($item['supply_price']);
        } else {
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $supplyPrice = intval($quantity * $unitPrice);
        }
        $vatAmount = intval(round($supplyPrice * 0.1));
        $totalPrice = $supplyPrice + $vatAmount;

        // 단가 계산
        if (in_array($productType, ['inserted', 'leaflet']) && $flyerMesu > 0) {
            $unitPrice = round($supplyPrice / $flyerMesu, 1);
        } else {
            $unitPrice = floatval($item['unit_price'] ?? 0);
            if ($unitPrice === 0.0 && $quantity > 0) {
                $unitPrice = round($supplyPrice / $quantity, 1);
            }
        }

        if ($unitPrice == 0) {
            error_log("[QuoteManager] 경고: 품목 '$productName'의 단가가 0원입니다 (quote_id=$quoteId)");
        }

        // source_type 결정
        $sourceType = $item['source_type'] ?? 'manual';
        if ($sourceType === 'calculator') {
            $sourceType = 'manual';
        }

        // source_data 생성
        $sourceData = null;
        if ($flyerMesu > 0 || !empty($item['_debug'])) {
            $sourceDataArray = [
                'flyer_mesu' => $flyerMesu,
                'mesu' => $flyerMesu,
                'product_type' => $productType,
                'from_calculator' => isset($item['source_type']) && $item['source_type'] === 'calculator'
            ];
            $sourceData = json_encode($sourceDataArray, JSON_UNESCAPED_UNICODE);
        }

        $query = "INSERT INTO quote_items (
            quote_id, item_no, product_type, product_name, specification,
            quantity, qty_val, qty_unit, qty_sheets, unit, unit_price,
            supply_price, vat_amount, total_price,
            source_type, is_manual, source_data, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);

        // 18개 파라미터
        mysqli_stmt_bind_param($stmt, "iisssddsisdiiisiss",
            $quoteId, $itemNo,
            $productType, $productName, $specification,
            $quantity, $qtyVal, $qtyUnit, $qtySheets, $unit, $unitPrice,
            $supplyPrice, $vatAmount, $totalPrice,
            $sourceType, $isManual, $sourceData, $notes
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    /**
     * 견적서 조회 (ID)
     */
    public function getById($id) {
        $query = "SELECT * FROM quotes WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $quote = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($quote) {
            $quote['items'] = $this->getItems($quote['id']);
        }

        return $quote;
    }

    /**
     * 견적서 조회 (토큰)
     */
    public function getByToken($token) {
        $query = "SELECT * FROM quotes WHERE public_token = ? LIMIT 1";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $quote = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($quote) {
            $quote['items'] = $this->getItems($quote['id']);
        }

        return $quote;
    }

    /**
     * 견적 품목 조회
     */
    public function getItems($quoteId) {
        $query = "SELECT * FROM quote_items WHERE quote_id = ? ORDER BY item_no ASC";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $quoteId);
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
     * 상태 업데이트
     */
    public function updateStatus($id, $status) {
        $query = "UPDATE quotes SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    /**
     * PDF 경로 저장
     */
    public function updatePdfPath($id, $pdfPath) {
        $query = "UPDATE quotes SET pdf_path = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "si", $pdfPath, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    /**
     * 공개 URL 생성
     */
    public function getPublicUrl($token) {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        return "{$protocol}://{$host}/mlangprintauto/quote/public/view.php?token={$token}";
    }

    /**
     * 견적서 목록 조회
     */
    public function getList($filters = [], $page = 1, $perPage = 20) {
        $where = "1=1";
        $params = [];
        $types = "";

        if (!empty($filters['quote_type'])) {
            $where .= " AND quote_type = ?";
            $params[] = $filters['quote_type'];
            $types .= "s";
        }

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where .= " AND (quote_no LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_company LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }

        // 전체 개수
        $countQuery = "SELECT COUNT(*) as total FROM quotes WHERE {$where}";
        $stmt = mysqli_prepare($this->db, $countQuery);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $countResult = mysqli_stmt_get_result($stmt);
        $total = mysqli_fetch_assoc($countResult)['total'];
        mysqli_stmt_close($stmt);

        // 목록 조회
        $offset = ($page - 1) * $perPage;
        $query = "SELECT * FROM quotes WHERE {$where} ORDER BY created_at DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $perPage;
        $types .= "ii";

        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        mysqli_stmt_close($stmt);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * 회사 정보 조회
     */
    public function getCompanySettings() {
        $query = "SELECT * FROM company_settings WHERE id = 1 LIMIT 1";
        $result = mysqli_query($this->db, $query);
        return mysqli_fetch_assoc($result) ?: [];
    }

    /**
     * 견적서 조회 (별칭 메서드)
     */
    public function getQuoteById($id) {
        return $this->getById($id);
    }

    /**
     * 견적서 품목 조회 (별칭 메서드)
     */
    public function getQuoteItems($quoteId) {
        return $this->getItems($quoteId);
    }

    /**
     * 금액 재계산
     */
    public function recalculateTotals($quoteId) {
        // 품목 합계 조회
        $query = "SELECT SUM(supply_price) as supply, SUM(vat_amount) as vat, SUM(total_price) as total FROM quote_items WHERE quote_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $quoteId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $totals = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // 견적서 업데이트
        $query = "UPDATE quotes SET supply_total = ?, vat_total = ?, grand_total = supply_total + vat_total + delivery_price - discount_amount WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        $supplyTotal = intval($totals['supply'] ?? 0);
        $vatTotal = intval($totals['vat'] ?? 0);
        mysqli_stmt_bind_param($stmt, "iii", $supplyTotal, $vatTotal, $quoteId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    /**
     * 견적서 수정 (draft 상태만)
     * @param array $data 견적서 데이터
     * @return array ['success' => bool, 'message' => string, 'public_url' => string]
     */
    public function updateQuote($data) {
        $quoteId = intval($data['quote_id'] ?? 0);

        if (!$quoteId) {
            return ['success' => false, 'message' => '견적서 ID가 필요합니다.'];
        }

        // 기존 견적서 조회
        $quote = $this->getQuoteById($quoteId);
        if (!$quote) {
            return ['success' => false, 'message' => '견적서를 찾을 수 없습니다.'];
        }

        // draft 상태만 수정 가능
        if ($quote['status'] !== 'draft') {
            return ['success' => false, 'message' => '이미 발송된 견적서는 수정할 수 없습니다.'];
        }

        mysqli_begin_transaction($this->db);

        try {
            // 유효기간 계산
            $validDays = intval($data['valid_days'] ?? 7);
            $validUntil = date('Y-m-d', strtotime('+' . $validDays . ' days'));

            // 견적서 기본 정보 업데이트
            $query = "UPDATE quotes SET
                quote_type = ?,
                customer_name = ?,
                customer_company = ?,
                customer_phone = ?,
                customer_email = ?,
                recipient_email = ?,
                delivery_type = ?,
                delivery_address = ?,
                delivery_price = ?,
                supply_total = ?,
                vat_total = ?,
                discount_amount = ?,
                discount_reason = ?,
                grand_total = ?,
                payment_terms = ?,
                valid_days = ?,
                valid_until = ?,
                notes = ?
            WHERE id = ?";

            $stmt = mysqli_prepare($this->db, $query);
            if (!$stmt) {
                throw new Exception('쿼리 준비 실패: ' . mysqli_error($this->db));
            }

            $quoteType = $data['quote_type'] ?? 'quotation';
            $customerName = $data['customer_name'] ?? '';
            $customerCompany = $data['customer_company'] ?? '';
            $customerPhone = $data['customer_phone'] ?? '';
            $customerEmail = $data['customer_email'] ?? '';
            $recipientEmail = $data['recipient_email'] ?? '';
            $deliveryType = $data['delivery_type'] ?? '';
            $deliveryAddress = $data['delivery_address'] ?? '';
            $deliveryPrice = intval($data['delivery_price'] ?? 0);
            $supplyTotal = intval($data['supply_total'] ?? 0);
            $vatTotal = intval($data['vat_total'] ?? 0);
            $discountAmount = intval($data['discount_amount'] ?? 0);
            $discountReason = $data['discount_reason'] ?? '';
            $grandTotal = intval($data['grand_total'] ?? 0);
            $paymentTerms = $data['payment_terms'] ?? '발행일로부터 7일';
            $notes = $data['notes'] ?? '';

            // 19개 파라미터: s×8 + i×4 + s×1 + i×1 + s×1 + i×1 + s×1 + s×1 + i×1 = ssssssssiiiisissssi
            mysqli_stmt_bind_param($stmt, "ssssssssiiiisissssi",
                $quoteType,           // 1: s
                $customerName,        // 2: s
                $customerCompany,     // 3: s
                $customerPhone,       // 4: s
                $customerEmail,       // 5: s
                $recipientEmail,      // 6: s
                $deliveryType,        // 7: s
                $deliveryAddress,     // 8: s
                $deliveryPrice,       // 9: i
                $supplyTotal,         // 10: i
                $vatTotal,            // 11: i
                $discountAmount,      // 12: i
                $discountReason,      // 13: s
                $grandTotal,          // 14: i
                $paymentTerms,        // 15: s
                $validDays,           // 16: i (수정: s → i)
                $validUntil,          // 17: s (수정: i → s)
                $notes,               // 18: s
                $quoteId              // 19: i
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('견적서 업데이트 실패: ' . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

            // 기존 품목 삭제
            $deleteQuery = "DELETE FROM quote_items WHERE quote_id = ?";
            $deleteStmt = mysqli_prepare($this->db, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "i", $quoteId);
            mysqli_stmt_execute($deleteStmt);
            mysqli_stmt_close($deleteStmt);

            // 새 품목 저장
            if (!empty($data['items']) && is_array($data['items'])) {
                $itemNo = 1;
                foreach ($data['items'] as $item) {
                    if (!empty($item['product_name'])) {
                        $this->addManualItem($quoteId, $itemNo, $item);
                        $itemNo++;
                    }
                }
            }

            mysqli_commit($this->db);

            // 공개 URL
            $publicUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/public/view.php?token=' . $quote['public_token'];

            return [
                'success' => true,
                'message' => '견적서가 수정되었습니다.',
                'public_url' => $publicUrl
            ];

        } catch (Exception $e) {
            mysqli_rollback($this->db);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 견적서 개정판 생성 (sent 상태 견적 복사 + 버전 관리)
     * @param int $originalQuoteId 원본 견적서 ID
     * @param array $data 수정할 데이터
     * @return array ['success' => bool, 'quote_id' => int, 'quote_no' => string, ...]
     */
    public function createRevision($originalQuoteId, $data) {
        mysqli_begin_transaction($this->db);

        try {
            // 원본 견적서 조회
            $original = $this->getQuoteById($originalQuoteId);
            if (!$original) {
                throw new Exception('원본 견적서를 찾을 수 없습니다.');
            }

            // sent 상태만 개정판 생성 가능
            if ($original['status'] !== 'sent') {
                throw new Exception('발송된 견적서만 개정판을 생성할 수 있습니다.');
            }

            // 원본의 최신 버전 플래그 해제
            $updateOriginalQuery = "UPDATE quotes SET is_latest = 0 WHERE id = ?";
            $updateStmt = mysqli_prepare($this->db, $updateOriginalQuery);
            mysqli_stmt_bind_param($updateStmt, "i", $originalQuoteId);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);

            // 동일한 original_quote_id를 가진 모든 이전 버전도 is_latest = 0
            $rootId = $original['original_quote_id'] ?? $originalQuoteId;
            $updateAllQuery = "UPDATE quotes SET is_latest = 0 WHERE original_quote_id = ? OR id = ?";
            $updateAllStmt = mysqli_prepare($this->db, $updateAllQuery);
            mysqli_stmt_bind_param($updateAllStmt, "ii", $rootId, $rootId);
            mysqli_stmt_execute($updateAllStmt);
            mysqli_stmt_close($updateAllStmt);

            // 새 버전 번호 계산 - DB에서 실제 최대 버전 조회
            $maxVersionQuery = "SELECT MAX(version) as max_version FROM quotes WHERE original_quote_id = ? OR id = ?";
            $maxVersionStmt = mysqli_prepare($this->db, $maxVersionQuery);
            mysqli_stmt_bind_param($maxVersionStmt, "ii", $rootId, $rootId);
            mysqli_stmt_execute($maxVersionStmt);
            $maxVersionResult = mysqli_stmt_get_result($maxVersionStmt);
            $maxVersionRow = mysqli_fetch_assoc($maxVersionResult);
            mysqli_stmt_close($maxVersionStmt);

            $currentMaxVersion = intval($maxVersionRow['max_version'] ?? $original['version'] ?? 1);
            $newVersion = $currentMaxVersion + 1;

            // 새 견적번호 생성 (버전 표시)
            $baseQuoteNo = preg_replace('/-v\d+$/', '', $original['quote_no']); // 기존 버전 제거
            $newQuoteNo = $baseQuoteNo . '-v' . $newVersion;

            // 새 공개 토큰 생성
            $publicToken = $this->generatePublicToken();
            $validUntil = date('Y-m-d', strtotime('+' . ($data['valid_days'] ?? 7) . ' days'));

            // 개정판 견적서 INSERT
            $query = "INSERT INTO quotes (
                quote_no, quote_type, public_token,
                original_quote_id, version, is_latest,
                customer_name, customer_company, customer_phone, customer_email, recipient_email,
                delivery_type, delivery_address, delivery_price,
                supply_total, vat_total, discount_amount, discount_reason, grand_total,
                payment_terms, valid_days, valid_until,
                notes, status, created_by
            ) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)";

            $stmt = mysqli_prepare($this->db, $query);
            if (!$stmt) {
                throw new Exception('쿼리 준비 실패: ' . mysqli_error($this->db));
            }

            $quoteType = $data['quote_type'] ?? $original['quote_type'];
            $customerName = $data['customer_name'] ?? $original['customer_name'];
            $customerCompany = $data['customer_company'] ?? $original['customer_company'];
            $customerPhone = $data['customer_phone'] ?? $original['customer_phone'];
            $customerEmail = $data['customer_email'] ?? $original['customer_email'];
            $recipientEmail = $data['recipient_email'] ?? $original['recipient_email'];
            $deliveryType = $data['delivery_type'] ?? $original['delivery_type'];
            $deliveryAddress = $data['delivery_address'] ?? $original['delivery_address'];
            $deliveryPrice = intval($data['delivery_price'] ?? $original['delivery_price']);
            $supplyTotal = intval($data['supply_total'] ?? $original['supply_total']);
            $vatTotal = intval($data['vat_total'] ?? $original['vat_total']);
            $discountAmount = intval($data['discount_amount'] ?? $original['discount_amount']);
            $discountReason = $data['discount_reason'] ?? $original['discount_reason'];
            $grandTotal = intval($data['grand_total'] ?? $original['grand_total']);
            $paymentTerms = $data['payment_terms'] ?? $original['payment_terms'];
            $validDays = intval($data['valid_days'] ?? $original['valid_days']);
            $notes = $data['notes'] ?? $original['notes'];
            $createdBy = intval($_SESSION['user_id'] ?? 0);

            // 23개 파라미터: s×3 + i×2 + s×7 + i×4 + s×1 + i×1 + s×1 + i×1 + s×1 + s×1 + i×1 = sssiiissssssiiiisissssi
            mysqli_stmt_bind_param($stmt, "sssiiissssssiiiisissssi",
                $newQuoteNo,        // 1: s
                $quoteType,         // 2: s
                $publicToken,       // 3: s
                $rootId,            // 4: i
                $newVersion,        // 5: i
                $customerName,      // 6: s
                $customerCompany,   // 7: s
                $customerPhone,     // 8: s
                $customerEmail,     // 9: s
                $recipientEmail,    // 10: s
                $deliveryType,      // 11: s
                $deliveryAddress,   // 12: s
                $deliveryPrice,     // 13: i
                $supplyTotal,       // 14: i
                $vatTotal,          // 15: i
                $discountAmount,    // 16: i
                $discountReason,    // 17: s
                $grandTotal,        // 18: i
                $paymentTerms,      // 19: s
                $validDays,         // 20: i (수정: s → i)
                $validUntil,        // 21: s (수정: i → s)
                $notes,             // 22: s
                $createdBy          // 23: i
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('개정판 저장 실패: ' . mysqli_stmt_error($stmt));
            }

            $newQuoteId = mysqli_insert_id($this->db);
            mysqli_stmt_close($stmt);

            // 품목 복사 (data에 items가 있으면 사용, 없으면 원본 복사)
            if (!empty($data['items']) && is_array($data['items'])) {
                $itemNo = 1;
                foreach ($data['items'] as $item) {
                    if (!empty($item['product_name'])) {
                        $this->addManualItem($newQuoteId, $itemNo, $item);
                        $itemNo++;
                    }
                }
            } else {
                // 원본 품목 복사
                $originalItems = $this->getQuoteItems($originalQuoteId);
                $itemNo = 1;
                foreach ($originalItems as $item) {
                    $this->addManualItem($newQuoteId, $itemNo, [
                        'product_type' => $item['product_type'],
                        'product_name' => $item['product_name'],
                        'specification' => $item['specification'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'],
                        'unit_price' => $item['unit_price'],
                        'supply_price' => $item['supply_price'],  // 원본 공급가 유지
                        'notes' => $item['notes']
                    ]);
                    $itemNo++;
                }
            }

            mysqli_commit($this->db);

            $publicUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/public/view.php?token=' . $publicToken;

            return [
                'success' => true,
                'quote_id' => $newQuoteId,
                'quote_no' => $newQuoteNo,
                'public_token' => $publicToken,
                'public_url' => $publicUrl,
                'message' => '개정판이 생성되었습니다. (버전 ' . $newVersion . ')'
            ];

        } catch (Exception $e) {
            mysqli_rollback($this->db);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>
