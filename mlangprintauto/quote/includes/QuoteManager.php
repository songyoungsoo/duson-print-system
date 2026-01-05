<?php
/**
 * 견적서/거래명세표 관리 클래스
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/ProductSpecFormatter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/DataAdapter.php';  // Phase 2

class QuoteManager {
    private $db;
    private $formatter;

    public function __construct($db) {
        $this->db = $db;
        $this->formatter = new ProductSpecFormatter($db);
    }

    /**
     * formatted_display 생성 헬퍼 함수
     * 제품 정보를 주문서/견적서 표시용 텍스트로 포맷팅
     *
     * @param string $productType 제품 타입 (inserted, leaflet, namecard, etc.)
     * @param array $data 제품 데이터 배열
     * @return string 포맷팅된 표시 텍스트
     */
    private function generateFormattedDisplay($productType, $data) {
        $formatted = '';

        // 용지 정보
        if (!empty($data['PN_type'])) {
            $formatted .= "용지: " . $data['PN_type'] . "\n";
        }

        // 규격 정보
        if (!empty($data['MY_type'])) {
            $formatted .= "규격: " . $data['MY_type'] . "\n";
        }

        // ★★★ PRIORITY: Use stored quantity_display if available ★★★
        if (!empty($data['quantity_display'])) {
            $formatted .= "수량: " . $data['quantity_display'] . "\n";
        } else {
            // Fallback: 레거시 데이터를 위한 계산 로직
            if (($productType === 'inserted' || $productType === 'leaflet')) {
                $myAmount = floatval($data['MY_amount'] ?? 0);
                $mesu = intval($data['mesu'] ?? 0);

                if ($myAmount > 0 && $mesu > 0) {
                    // 연수 표시: 정수면 소수점 없이, 소수면 1자리
                    $yeonDisplay = floor($myAmount) == $myAmount
                        ? number_format($myAmount)
                        : number_format($myAmount, 1);
                    $formatted .= "수량: " . $yeonDisplay . "연 (" . number_format($mesu) . "매)\n";
                }
            } else {
                // 기타 제품: 일반 수량 표시
                $quantity = floatval($data['quantity'] ?? 0);
                $unit = $data['unit'] ?? '매';

                if ($quantity > 0) {
                    $quantityDisplay = floor($quantity) == $quantity
                        ? number_format($quantity)
                        : number_format($quantity, 1);
                    $formatted .= "수량: " . $quantityDisplay . " " . $unit . "\n";
                }
            }
        }

        // 인쇄 타입
        if (!empty($data['ordertype'])) {
            $formatted .= "인쇄: " . $data['ordertype'] . "\n";
        }

        return $formatted;
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
        $query = "SELECT COUNT(*) as cnt FROM quotations WHERE quotation_no LIKE ?";
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
        $query = "SELECT id FROM quotations WHERE quotation_no = ? LIMIT 1";
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
            $quoteTempItems = $this->getQuoteTempItems($sessionId);

            // 장바구니 또는 quotation_temp에 최소 1개 이상의 품목이 있어야 함
            if (empty($cartItems) && empty($quoteTempItems)) {
                throw new Exception('장바구니가 비어있습니다.');
            }

            // 견적서 기본 정보 저장
            $quoteNo = $this->generateQuoteNo('quotation');
            $publicToken = $this->generatePublicToken();
            $validUntil = date('Y-m-d', strtotime('+' . ($data['valid_days'] ?? 7) . ' days'));

            // 🆕 Phase C: quote_source 결정
            $quoteSource = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true
                ? 'admin_auto'
                : 'customer';

            $query = "INSERT INTO quotations (
                quotation_no, quote_type, public_token, session_id,
                customer_name, customer_company, customer_phone, customer_email, recipient_email,
                delivery_type, delivery_address, delivery_price, delivery_vat,
                supply_total, vat_total, discount_amount, discount_reason, grand_total,
                payment_terms, valid_days, valid_until,
                notes, status, created_by, quote_source
            ) VALUES (?, 'quotation', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)";

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
            $deliveryVat = intval($data['delivery_vat'] ?? round($deliveryPrice * 0.1));
            $supplyTotal = intval($data['supply_total'] ?? 0);
            $vatTotal = intval($data['vat_total'] ?? 0);
            $discountAmount = intval($data['discount_amount'] ?? 0);
            $discountReason = $data['discount_reason'] ?? '';
            $grandTotal = intval($data['grand_total'] ?? 0);
            $paymentTerms = $data['payment_terms'] ?? '발행일로부터 7일';
            $validDays = intval($data['valid_days'] ?? 7);
            $notes = $data['notes'] ?? '';
            $createdBy = intval($data['created_by'] ?? 0);

            // 23개 파라미터 (Phase C): s×10 + i×5 + s×1 + i×1 + s×1 + i×1 + s×1 + i×1 + s×1 = ssssssssssiiiiisisissis
            mysqli_stmt_bind_param($stmt, "ssssssssssiiiiisisissis",
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
                $deliveryVat,       // 12: i (배송비 VAT)
                $supplyTotal,       // 13: i
                $vatTotal,          // 14: i
                $discountAmount,    // 15: i
                $discountReason,    // 16: s
                $grandTotal,        // 17: i
                $paymentTerms,      // 18: s
                $validDays,         // 19: i
                $validUntil,        // 20: s
                $notes,             // 21: s
                $createdBy,         // 22: i
                $quoteSource        // 23: s (Phase C)
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

            // 🆕 Phase C: quote_source 결정 (빈 견적서 = 수동입력)
            $quoteSource = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true
                ? 'admin_manual'
                : 'customer';

            $query = "INSERT INTO quotations (
                quotation_no, quote_type, public_token,
                customer_name, customer_company, customer_phone, customer_email, recipient_email,
                delivery_type, delivery_address, delivery_price, delivery_vat,
                supply_total, vat_total, discount_amount, discount_reason, grand_total,
                payment_terms, valid_days, valid_until,
                notes, status, created_by, quote_source
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)";

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
            $deliveryVat = intval($data['delivery_vat'] ?? round($deliveryPrice * 0.1));
            $supplyTotal = intval($data['supply_total'] ?? 0);
            $vatTotal = intval($data['vat_total'] ?? 0);
            $discountAmount = intval($data['discount_amount'] ?? 0);
            $discountReason = $data['discount_reason'] ?? '';
            $grandTotal = intval($data['grand_total'] ?? 0);
            $paymentTerms = $data['payment_terms'] ?? '발행일로부터 7일';
            $validDays = intval($data['valid_days'] ?? 7);
            $notes = $data['notes'] ?? '';
            $createdBy = intval($data['created_by'] ?? 0);

            // 23개 파라미터 (Phase C): s×10 + i×5 + s×1 + i×1 + s×1 + i×1 + s×1 + i×1 + s×1 = ssssssssssiiiiisisissis
            mysqli_stmt_bind_param($stmt, "ssssssssssiiiiisisissis",
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
                $deliveryVat,       // 12: i (배송비 VAT)
                $supplyTotal,       // 13: i
                $vatTotal,          // 14: i
                $discountAmount,    // 15: i
                $discountReason,    // 16: s
                $grandTotal,        // 17: i
                $paymentTerms,      // 18: s
                $validDays,         // 19: i
                $validUntil,        // 20: s
                $notes,             // 21: s
                $createdBy,         // 22: i
                $quoteSource        // 23: s (Phase C)
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
        $query = "SELECT * FROM quotation_temp WHERE session_id = ? ORDER BY regdate ASC";
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
     * 장바구니 아이템을 견적 품목으로 추가 (11개 필드 확장)
     */
    private function addItemFromCart($quoteId, $itemNo, $cartItem) {
        $productType = $cartItem['product_type'] ?? '';

        // 레거시 스티커 감지
        if (empty($productType) && !empty($cartItem['jong']) && !empty($cartItem['garo'])) {
            $productType = 'sticker';
        }

        // ✅ Phase 2: DataAdapter로 표준 데이터 추출
        $standard_data = DataAdapter::legacyToStandard($cartItem, $productType);
        $quantity_display = $standard_data['quantity_display'] ?? '';

        // 기본 필드 추출
        $productName = ProductSpecFormatter::getProductTypeName($productType);
        $specification = $this->formatter->format($cartItem);
        $quantity = ProductSpecFormatter::getQuantity($cartItem);
        $unit = ProductSpecFormatter::getUnit($cartItem);
        $supplyPrice = ProductSpecFormatter::getSupplyPrice($cartItem);
        $totalPrice = ProductSpecFormatter::getPrice($cartItem);
        $vatAmount = $totalPrice - $supplyPrice;

        // 전단지는 quantityTwo(mesu)로 단가 계산 (소수점 2자리), 나머지는 quantity로 계산
        if ($productType === 'inserted' && !empty($cartItem['mesu']) && intval($cartItem['mesu']) > 0) {
            $unitPrice = round($supplyPrice / intval($cartItem['mesu']), 2);
        } else {
            $unitPrice = $quantity > 0 ? round($supplyPrice / $quantity, 2) : 0;
        }

        // ===== 11개 신규 필드 추출 =====

        // 1-4. 제품 사양 필드 (MY_type, PN_type, MY_Fsd, POtype)
        $myType = $cartItem['MY_type'] ?? '';
        $pnType = $cartItem['PN_type'] ?? '';
        $myFsd = $cartItem['MY_Fsd'] ?? '';
        $poType = $cartItem['POtype'] ?? '';

        // 5-6. 연수/매수 필드 (MY_amount, mesu)
        $myAmount = floatval($cartItem['MY_amount'] ?? 0);
        $mesu = intval($cartItem['mesu'] ?? 0);

        // 7. 인쇄 타입 (ordertype)
        $ordertype = $cartItem['ordertype'] ?? '';

        // 8. product_data JSON 생성 (Type_1 JSON 호환 구조)
        $productDataArray = [
            'MY_type' => $myType,
            'PN_type' => $pnType,
            'MY_Fsd' => $myFsd,
            'POtype' => $poType,
            'MY_amount' => $myAmount,
            'mesu' => $mesu,
            'ordertype' => $ordertype,
            'unit' => $unit,
            'quantity' => $quantity
        ];
        $productData = json_encode($productDataArray, JSON_UNESCAPED_UNICODE);

        // 9. formatted_display 생성 (헬퍼 함수 사용)
        $displayData = [
            'PN_type' => $pnType,
            'MY_type' => $myType,
            'MY_amount' => $myAmount,
            'mesu' => $mesu,
            'quantity' => $quantity,
            'unit' => $unit,
            'ordertype' => $ordertype,
            'quantity_display' => $quantity_display  // ★ Phase 2: 저장된 드롭다운 텍스트 우선 사용
        ];
        $formattedDisplay = $this->generateFormattedDisplay($productType, $displayData);

        // 10-11. 추가 옵션 필드 (additional_options, additional_options_total)
        $additionalOptionsArray = [];
        $additionalOptionsTotal = 0;

        // 코팅 옵션
        if (!empty($cartItem['coating_enabled']) || !empty($cartItem['coating_type'])) {
            $coatingPrice = intval($cartItem['coating_price'] ?? 0);
            $additionalOptionsArray['coating'] = [
                'enabled' => intval($cartItem['coating_enabled'] ?? 0),
                'type' => $cartItem['coating_type'] ?? '',
                'price' => $coatingPrice
            ];
            $additionalOptionsTotal += $coatingPrice;
        }

        // 접지 옵션 (리플렛)
        if (!empty($cartItem['folding_enabled']) || !empty($cartItem['folding_type'])) {
            $foldingPrice = intval($cartItem['folding_price'] ?? 0);
            $additionalOptionsArray['folding'] = [
                'enabled' => intval($cartItem['folding_enabled'] ?? 0),
                'type' => $cartItem['folding_type'] ?? '',
                'price' => $foldingPrice
            ];
            $additionalOptionsTotal += $foldingPrice;
        }

        // 오시 옵션
        if (!empty($cartItem['creasing_enabled']) || !empty($cartItem['creasing_lines'])) {
            $creasingPrice = intval($cartItem['creasing_price'] ?? 0);
            $additionalOptionsArray['creasing'] = [
                'enabled' => intval($cartItem['creasing_enabled'] ?? 0),
                'lines' => $cartItem['creasing_lines'] ?? '',
                'price' => $creasingPrice
            ];
            $additionalOptionsTotal += $creasingPrice;
        }

        $additionalOptions = json_encode($additionalOptionsArray, JSON_UNESCAPED_UNICODE);

        $isManualEntry = 0; // 🆕 Phase C: 자동계산 품목

        // ===== Phase 3: Extract Phase 3 fields from cart item =====
        $specType = $cartItem['spec_type'] ?? '';
        $specMaterial = $cartItem['spec_material'] ?? '';
        $specSize = $cartItem['spec_size'] ?? '';
        $specSides = $cartItem['spec_sides'] ?? '';
        $specDesign = $cartItem['spec_design'] ?? '';
        $quantityValue = $cartItem['quantity_value'] ?? null;
        $quantityUnitPhase3 = $cartItem['quantity_unit'] ?? '매';
        $quantitySheets = $cartItem['quantity_sheets'] ?? null;
        $quantityDisplayPhase3 = $cartItem['quantity_display'] ?? '';
        $priceSupplyPhase3 = $cartItem['price_supply'] ?? 0;
        $priceVat = $cartItem['price_vat'] ?? 0;
        $priceVatAmountPhase3 = $cartItem['price_vat_amount'] ?? 0;
        $dataVersion = $cartItem['data_version'] ?? 1;

        // ===== INSERT 쿼리 (37개 파라미터) - Phase 3: 12개 필드 추가 =====
        $query = "INSERT INTO quote_items (
            quote_id, item_no, product_type, MY_type, PN_type, MY_Fsd, POtype, MY_amount, mesu,
            product_name, specification,
            quantity, unit, ordertype, unit_price, supply_price, vat_amount, total_price,
            source_type, source_id, source_data,
            product_data, formatted_display, additional_options, additional_options_total, is_manual_entry,
            spec_type, spec_material, spec_size, spec_sides, spec_design,
            quantity_value, quantity_unit_phase3, quantity_sheets, quantity_display,
            price_supply_phase3, price_vat, price_vat_amount_phase3, data_version
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'cart', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        $sourceData = json_encode($cartItem, JSON_UNESCAPED_UNICODE);
        $sourceId = intval($cartItem['no'] ?? 0);

        // bind_param 타입 문자열: 38개 파라미터 (Phase 3: 25 original + 13 Phase 3)
        // Original 25: i i s s s s s d i s s d s s d i i i i s s s s i i
        // Phase 3 13:  s s s s s d s i s i i i i
        mysqli_stmt_bind_param($stmt, "iisssssdissdssdiiiissssii" . "sssssdsissiii",
            $quoteId, $itemNo, $productType, $myType, $pnType, $myFsd, $poType, $myAmount, $mesu,
            $productName, $specification,
            $quantity, $unit, $ordertype, $unitPrice, $supplyPrice, $vatAmount, $totalPrice,
            $sourceId, $sourceData,
            $productData, $formattedDisplay, $additionalOptions, $additionalOptionsTotal, $isManualEntry,
            $specType, $specMaterial, $specSize, $specSides, $specDesign,
            $quantityValue, $quantityUnitPhase3, $quantitySheets, $quantityDisplayPhase3,
            $priceSupplyPhase3, $priceVat, $priceVatAmountPhase3, $dataVersion
        );

        if (!mysqli_stmt_execute($stmt)) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new Exception("Failed to execute addItemFromCart INSERT: " . $error);
        }

        mysqli_stmt_close($stmt);
    }

    /**
     * quotation_temp 품목을 견적 품목으로 추가
     */
    private function addItemFromQuoteTemp($quoteId, $itemNo, $tempItem) {
        // === ProductSpecFormatter를 사용하여 quotation_temp 데이터 처리 ===
        // (이미 상단에서 include됨)
        $formatter = new ProductSpecFormatter($this->db);

        // 1. 제품 정보 추출
        $productType = $tempItem['product_type'] ?? '';
        $productName = ProductSpecFormatter::getProductTypeName($productType);
        $specification = $formatter->format($tempItem);

        // 2. 수량 및 단위 추출 (ProductSpecFormatter 사용)
        $quantity = ProductSpecFormatter::getQuantity($tempItem);
        $unit = ProductSpecFormatter::getUnit($tempItem);

        // 3. 가격 정보 추출 (shop_temp 구조: st_price, st_price_vat)
        $supplyPrice = ProductSpecFormatter::getSupplyPrice($tempItem);  // VAT 제외
        $totalPrice = ProductSpecFormatter::getPrice($tempItem);         // VAT 포함
        $vatAmount = $totalPrice - $supplyPrice;

        // 4. 단가 계산
        // 전단지/리플렛: mesu(매수)로 단가 계산, 기타: quantity로 계산
        if (in_array($productType, ['inserted', 'leaflet'])) {
            $mesu = intval($tempItem['mesu'] ?? 0);
            if ($mesu > 0) {
                $unitPrice = round($supplyPrice / $mesu, 1);
            } else {
                $unitPrice = $quantity > 0 ? round($supplyPrice / $quantity, 1) : 0;
            }
        } else {
            $unitPrice = $quantity > 0 ? round($supplyPrice / $quantity, 1) : 0;
        }

        // 5. 메모 추출
        $notes = $tempItem['MY_comment'] ?? $tempItem['work_memo'] ?? '';

        $isManualEntry = 0; // 🆕 Phase C: 자동계산 품목

        // ===== Phase 3: Extract Phase 3 fields from quotation_temp =====
        $specType = $tempItem['spec_type'] ?? '';
        $specMaterial = $tempItem['spec_material'] ?? '';
        $specSize = $tempItem['spec_size'] ?? '';
        $specSides = $tempItem['spec_sides'] ?? '';
        $specDesign = $tempItem['spec_design'] ?? '';
        $quantityValue = $tempItem['quantity_value'] ?? null;
        $quantityUnitPhase3 = $tempItem['quantity_unit'] ?? '매';
        $quantitySheets = $tempItem['quantity_sheets'] ?? null;
        $quantityDisplayPhase3 = $tempItem['quantity_display'] ?? '';
        $priceSupplyPhase3 = $tempItem['price_supply'] ?? 0;
        $priceVat = $tempItem['price_vat'] ?? 0;
        $priceVatAmountPhase3 = $tempItem['price_vat_amount'] ?? 0;
        $dataVersion = $tempItem['data_version'] ?? 1;

        // 6. DB INSERT (Phase 3: 13개 필드 추가)
        $query = "INSERT INTO quote_items (
            quote_id, item_no, product_type, product_name, specification,
            quantity, unit, unit_price, supply_price, vat_amount, total_price,
            source_type, source_id, notes, is_manual_entry,
            spec_type, spec_material, spec_size, spec_sides, spec_design,
            quantity_value, quantity_unit_phase3, quantity_sheets, quantity_display,
            price_supply_phase3, price_vat, price_vat_amount_phase3, data_version
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'quotation_temp', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        $sourceId = intval($tempItem['no'] ?? $tempItem['id'] ?? 0);

        // 27개 파라미터 (Phase 3: 14 original + 13 Phase 3)
        // Original 14: i i s s s d s d i i i i s i
        // Phase 3 13:  s s s s s d s i s i i i i
        mysqli_stmt_bind_param($stmt, "iisssdsdiiissi" . "sssssdsissiii",
            $quoteId, $itemNo, $productType, $productName, $specification,
            $quantity, $unit, $unitPrice, $supplyPrice, $vatAmount, $totalPrice,
            $sourceId, $notes, $isManualEntry,
            $specType, $specMaterial, $specSize, $specSides, $specDesign,
            $quantityValue, $quantityUnitPhase3, $quantitySheets, $quantityDisplayPhase3,
            $priceSupplyPhase3, $priceVat, $priceVatAmountPhase3, $dataVersion
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    /**
     * 수동 입력 품목 추가
     */
    private function addManualItem($quoteId, $itemNo, $item) {
        // 변수 추출 (PHP bind_param은 참조만 허용)
        $productType = $item['product_type'] ?? '';
        $productName = trim($item['product_name'] ?? '');
        $specification = $item['specification'] ?? '';
        $unit = $item['unit'] ?? '개';
        $notes = $item['notes'] ?? '';

        // ✅ __direct__ 처리: 직접입력 선택 시 product_name_custom 사용
        if ($productName === '__direct__') {
            $productName = trim($item['product_name_custom'] ?? '');
            error_log("[QuoteManager] 직접입력 품목: product_name_custom='$productName' (quote_id=$quoteId)");
        }

        // 필수 필드 검증 - 품목명이 비어있거나 __direct__이면 저장하지 않음
        if (empty($productName) || $productName === '__direct__') {
            error_log("[QuoteManager] 경고: 품목명이 비어있어 저장하지 않음 (quote_id=$quoteId, item_no=$itemNo)");
            return;
        }

        $quantity = floatval($item['quantity'] ?? 1);

        // 수량 검증 - 0 이하 방지
        if ($quantity <= 0) {
            error_log("[QuoteManager] 경고: 품목 '$productName'의 수량이 0 이하입니다. 기본값 1로 설정 (quote_id=$quoteId)");
            $quantity = 1;
        }

        // 공급가 직접 사용 (단가는 역산)
        $supplyPrice = intval($item['supply_price'] ?? 0);
        $unitPrice = $quantity > 0 ? round($supplyPrice / $quantity, 2) : 0;

        // 공급가 0원 경고 (저장은 하되 로그 기록)
        if ($supplyPrice === 0) {
            error_log("[QuoteManager] 경고: 품목 '$productName'의 공급가가 0원입니다 (quote_id=$quoteId)");
        }

        $vatAmount = intval(round($supplyPrice * 0.1));
        $totalPrice = $supplyPrice + $vatAmount;

        $sourceType = 'manual';
        $isManualEntry = 1; // 🆕 Phase C: 수동입력 품목

        // ===== Phase 3: Manual entry uses NULL/empty for most Phase 3 fields =====
        // 수동입력은 사용자가 직접 입력한 값만 사용하고, Phase 3 표준화 안함
        $specType = null;
        $specMaterial = null;
        $specSize = null;
        $specSides = null;
        $specDesign = null;
        $quantityValue = $quantity;  // 사용자 입력 수량
        $quantityUnitPhase3 = $unit;  // 사용자 입력 단위
        $quantitySheets = null;
        $quantityDisplayPhase3 = $quantity . $unit;  // "10개" 형식
        $priceSupplyPhase3 = $supplyPrice;  // 사용자 입력 공급가
        $priceVat = $totalPrice;
        $priceVatAmountPhase3 = $vatAmount;
        $dataVersion = 1;  // Legacy format (manual entry)

        $query = "INSERT INTO quote_items (
            quote_id, item_no, product_type, product_name, specification,
            quantity, unit, unit_price, supply_price, vat_amount, total_price,
            source_type, notes, is_manual_entry,
            spec_type, spec_material, spec_size, spec_sides, spec_design,
            quantity_value, quantity_unit_phase3, quantity_sheets, quantity_display,
            price_supply_phase3, price_vat, price_vat_amount_phase3, data_version
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);

        // 타입 문자열: 27개 파라미터 (Phase 3: 14 original + 13 Phase 3)
        // Original 14: i i s s s d s d i i i s s i
        // Phase 3 13:  s s s s s d s i s i i i i
        mysqli_stmt_bind_param($stmt, "iisssdsdiiissi" . "sssssdsissiii",
            $quoteId, $itemNo,
            $productType,
            $productName,
            $specification,
            $quantity,
            $unit,
            $unitPrice, $supplyPrice, $vatAmount, $totalPrice,
            $sourceType,
            $notes,
            $isManualEntry,
            $specType, $specMaterial, $specSize, $specSides, $specDesign,
            $quantityValue, $quantityUnitPhase3, $quantitySheets, $quantityDisplayPhase3,
            $priceSupplyPhase3, $priceVat, $priceVatAmountPhase3, $dataVersion
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    /**
     * 견적서 조회 (ID)
     */
    public function getById($id) {
        $query = "SELECT * FROM quotations WHERE id = ? LIMIT 1";
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
        $query = "SELECT * FROM quotations WHERE public_token = ? LIMIT 1";
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
        $query = "UPDATE quotations SET status = ? WHERE id = ?";
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
        $query = "UPDATE quotations SET pdf_path = ? WHERE id = ?";
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
            $where .= " AND (quotation_no LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_company LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }

        // 전체 개수
        $countQuery = "SELECT COUNT(*) as total FROM quotations WHERE {$where}";
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
        $query = "SELECT * FROM quotations WHERE {$where} ORDER BY created_at DESC LIMIT ?, ?";
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

            // 견적서 기본 정보 업데이트 (delivery_vat 포함)
            $query = "UPDATE quotations SET
                quote_type = ?,
                customer_name = ?,
                customer_company = ?,
                customer_phone = ?,
                customer_email = ?,
                recipient_email = ?,
                delivery_type = ?,
                delivery_address = ?,
                delivery_price = ?,
                delivery_vat = ?,
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
            $deliveryVat = intval($data['delivery_vat'] ?? round($deliveryPrice * 0.1));
            $supplyTotal = intval($data['supply_total'] ?? 0);
            $vatTotal = intval($data['vat_total'] ?? 0);
            $discountAmount = intval($data['discount_amount'] ?? 0);
            $discountReason = $data['discount_reason'] ?? '';
            $grandTotal = intval($data['grand_total'] ?? 0);
            $paymentTerms = $data['payment_terms'] ?? '발행일로부터 7일';
            $notes = $data['notes'] ?? '';

            // 20개 파라미터: s×8 + i×5 + s×1 + i×1 + s×1 + i×1 + s×1 + s×1 + i×1 = ssssssssiiiiisissssi
            mysqli_stmt_bind_param($stmt, "ssssssssiiiiisissssi",
                $quoteType,           // 1: s
                $customerName,        // 2: s
                $customerCompany,     // 3: s
                $customerPhone,       // 4: s
                $customerEmail,       // 5: s
                $recipientEmail,      // 6: s
                $deliveryType,        // 7: s
                $deliveryAddress,     // 8: s
                $deliveryPrice,       // 9: i
                $deliveryVat,         // 10: i (NEW)
                $supplyTotal,         // 11: i
                $vatTotal,            // 12: i
                $discountAmount,      // 13: i
                $discountReason,      // 14: s
                $grandTotal,          // 15: i
                $paymentTerms,        // 16: s
                $validDays,           // 17: i
                $validUntil,          // 18: s
                $notes,               // 19: s
                $quoteId              // 20: i
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
            $updateOriginalQuery = "UPDATE quotations SET is_latest = 0 WHERE id = ?";
            $updateStmt = mysqli_prepare($this->db, $updateOriginalQuery);
            mysqli_stmt_bind_param($updateStmt, "i", $originalQuoteId);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);

            // 동일한 original_quote_id를 가진 모든 이전 버전도 is_latest = 0
            $rootId = $original['original_quote_id'] ?? $originalQuoteId;
            $updateAllQuery = "UPDATE quotations SET is_latest = 0 WHERE original_quote_id = ? OR id = ?";
            $updateAllStmt = mysqli_prepare($this->db, $updateAllQuery);
            mysqli_stmt_bind_param($updateAllStmt, "ii", $rootId, $rootId);
            mysqli_stmt_execute($updateAllStmt);
            mysqli_stmt_close($updateAllStmt);

            // 새 버전 번호 계산 - DB에서 실제 최대 버전 조회
            $maxVersionQuery = "SELECT MAX(version) as max_version FROM quotations WHERE original_quote_id = ? OR id = ?";
            $maxVersionStmt = mysqli_prepare($this->db, $maxVersionQuery);
            mysqli_stmt_bind_param($maxVersionStmt, "ii", $rootId, $rootId);
            mysqli_stmt_execute($maxVersionStmt);
            $maxVersionResult = mysqli_stmt_get_result($maxVersionStmt);
            $maxVersionRow = mysqli_fetch_assoc($maxVersionResult);
            mysqli_stmt_close($maxVersionStmt);

            $currentMaxVersion = intval($maxVersionRow['max_version'] ?? $original['version'] ?? 1);
            $newVersion = $currentMaxVersion + 1;

            // 새 견적번호 생성 (버전 표시)
            $baseQuoteNo = preg_replace('/-v\d+$/', '', $original['quotation_no']); // 기존 버전 제거
            $newQuoteNo = $baseQuoteNo . '-v' . $newVersion;

            // 새 공개 토큰 생성
            $publicToken = $this->generatePublicToken();
            $validUntil = date('Y-m-d', strtotime('+' . ($data['valid_days'] ?? 7) . ' days'));

            // 개정판 견적서 INSERT (delivery_vat 포함)
            $query = "INSERT INTO quotations (
                quotation_no, quote_type, public_token,
                original_quote_id, version, is_latest,
                customer_name, customer_company, customer_phone, customer_email, recipient_email,
                delivery_type, delivery_address, delivery_price, delivery_vat,
                supply_total, vat_total, discount_amount, discount_reason, grand_total,
                payment_terms, valid_days, valid_until,
                notes, status, created_by
            ) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)";

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
            $deliveryVat = intval($data['delivery_vat'] ?? $original['delivery_vat'] ?? round($deliveryPrice * 0.1));
            $supplyTotal = intval($data['supply_total'] ?? $original['supply_total']);
            $vatTotal = intval($data['vat_total'] ?? $original['vat_total']);
            $discountAmount = intval($data['discount_amount'] ?? $original['discount_amount']);
            $discountReason = $data['discount_reason'] ?? $original['discount_reason'];
            $grandTotal = intval($data['grand_total'] ?? $original['grand_total']);
            $paymentTerms = $data['payment_terms'] ?? $original['payment_terms'];
            $validDays = intval($data['valid_days'] ?? $original['valid_days']);
            $notes = $data['notes'] ?? $original['notes'];
            $createdBy = intval($_SESSION['user_id'] ?? 0);

            // 24개 파라미터: s×3 + i×2 + s×7 + i×5 + s×1 + i×1 + s×1 + i×1 + s×1 + s×1 + i×1 = sssiisssssssiiiiisisissі
            mysqli_stmt_bind_param($stmt, "sssiisssssssiiiiisisissi",
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
                $deliveryVat,       // 14: i (NEW)
                $supplyTotal,       // 15: i
                $vatTotal,          // 16: i
                $discountAmount,    // 17: i
                $discountReason,    // 18: s
                $grandTotal,        // 19: i
                $paymentTerms,      // 20: s
                $validDays,         // 21: i
                $validUntil,        // 22: s
                $notes,             // 23: s
                $createdBy          // 24: i
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
                        'supply_price' => $item['supply_price'],
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
