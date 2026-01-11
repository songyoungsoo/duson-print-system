<?php
/**
 * 표준 견적서 데이터 주입부
 *
 * DB 또는 배열에서 견적서 데이터를 로드하고 정규화합니다.
 * 모든 출력(view, pdf, mail)에서 동일한 데이터 구조를 사용합니다.
 */

require_once __DIR__ . '/../../../db.php';

/**
 * 공급자(회사) 기본 정보
 * 실제 운영 시 DB 또는 설정 파일에서 로드
 */
function getSupplierInfo(): array {
    return [
        'company_name'   => '두손기획인쇄',
        'business_no'    => '125-22-28970',
        'ceo_name'       => '차경선',
        'address'        => '서울시 중구 을지로33길 33, 청자빌딩 301호',
        'phone'          => '02-2267-1830',
        'fax'            => '02-2285-1830',
        'email'          => 'duson1830@naver.com',
        'stamp_image'    => '/images/stamp.png', // 직인 이미지 경로
        'account_holder' => '두손기획인쇄 차경선',
        'bank_accounts'  => [
            ['bank_name' => '국민은행', 'account_no' => '999-1688-2384'],
            ['bank_name' => '신한은행', 'account_no' => '110-342-543507'],
            ['bank_name' => '농협',     'account_no' => '301-2632-1830-11'],
        ],
    ];
}

/**
 * 견적서 데이터 로드 (DB에서)
 *
 * @param mysqli $db DB 연결
 * @param int $quoteId 견적서 ID
 * @return array|null 견적서 데이터 또는 null
 */
function loadQuoteFromDB(mysqli $db, int $quoteId): ?array {
    // 견적서 기본 정보 조회
    $stmt = $db->prepare("
        SELECT
            q.id,
            q.quote_no,
            q.customer_company,
            q.customer_name,
            q.customer_phone,
            q.customer_email,
            q.customer_address,
            q.customer_memo,
            q.admin_memo,
            q.status,
            q.supply_total,
            q.vat_amount,
            q.grand_total,
            q.valid_until,
            q.created_at AS quote_date
        FROM mlangprintauto_quotes q
        WHERE q.id = ?
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $quoteId);
    $stmt->execute();
    $result = $stmt->get_result();
    $quote = $result->fetch_assoc();
    $stmt->close();

    if (!$quote) {
        return null;
    }

    // 유효기간 계산 (days)
    if (!empty($quote['valid_until']) && !empty($quote['quote_date'])) {
        $validDate = new DateTime($quote['valid_until']);
        $quoteDate = new DateTime($quote['quote_date']);
        $diff = $quoteDate->diff($validDate);
        $quote['validity_days'] = $diff->days;
    } else {
        $quote['validity_days'] = 7;
    }

    return $quote;
}

/**
 * 견적서 품목 로드 (DB에서)
 *
 * @param mysqli $db DB 연결
 * @param int $quoteId 견적서 ID
 * @return array 품목 목록
 */
function loadQuoteItemsFromDB(mysqli $db, int $quoteId): array {
    $stmt = $db->prepare("
        SELECT
            qi.id,
            qi.product_type,
            qi.product_name,
            qi.specification,
            qi.quantity,
            qi.unit,
            qi.quantity_display,
            qi.unit_price,
            qi.supply_price,
            qi.vat_amount,
            qi.total_price
        FROM mlangprintauto_quote_items qi
        WHERE qi.quote_id = ?
        ORDER BY qi.id ASC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $quoteId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];

    while ($row = $result->fetch_assoc()) {
        $items[] = normalizeItem($row);
    }

    $stmt->close();
    return $items;
}

/**
 * 품목 데이터 정규화
 * DB 컬럼이 변경되어도 출력에 영향 없도록 함
 *
 * @param array $row DB 원본 행
 * @return array 정규화된 품목 데이터
 */
function normalizeItem(array $row): array {
    // 수량 표시 생성 (없으면 생성)
    $quantityDisplay = $row['quantity_display'] ?? '';
    if (empty($quantityDisplay)) {
        $qty = floatval($row['quantity'] ?? 1);
        $unit = $row['unit'] ?? '개';
        if ($qty == intval($qty)) {
            $quantityDisplay = number_format($qty) . $unit;
        } else {
            $quantityDisplay = rtrim(rtrim(number_format($qty, 1), '0'), '.') . $unit;
        }
    }

    return [
        'id'               => $row['id'] ?? 0,
        'product_type'     => $row['product_type'] ?? '',
        'product_name'     => $row['product_name'] ?? '',
        'specification'    => $row['specification'] ?? '',
        'quantity'         => floatval($row['quantity'] ?? 1),
        'unit'             => $row['unit'] ?? '개',
        'quantity_display' => $quantityDisplay,
        'unit_price'       => intval($row['unit_price'] ?? 0),
        'supply_price'     => intval($row['supply_price'] ?? 0),
        'vat_amount'       => intval($row['vat_amount'] ?? 0),
        'total_price'      => intval($row['total_price'] ?? 0),
    ];
}

/**
 * 테스트/샘플 데이터 생성
 * 개발 및 테스트 용도
 */
function getSampleQuoteData(): array {
    return [
        'quote' => [
            'id'               => 1,
            'quote_no'         => 'Q2026-0110-001',
            'customer_company' => '테스트상사',
            'customer_name'    => '홍길동',
            'customer_phone'   => '010-1234-5678',
            'customer_email'   => 'test@example.com',
            'customer_address' => '서울시 강남구 테헤란로 123',
            'quote_date'       => date('Y-m-d'),
            'valid_until'      => date('Y-m-d', strtotime('+7 days')),
            'validity_days'    => 7,
            'status'           => 'sent',
        ],
        'items' => [
            [
                'id'               => 1,
                'product_type'     => 'inserted',
                'product_name'     => '전단지',
                'specification'    => '아트지 150g / A4 / 양면 4도 / 무코팅',
                'quantity'         => 0.5,
                'unit'             => '연',
                'quantity_display' => '0.5연',
                'unit_price'       => 60000,
                'supply_price'     => 30000,
            ],
            [
                'id'               => 2,
                'product_type'     => 'namecard',
                'product_name'     => '명함',
                'specification'    => '스노우지 250g / 90×50mm / 양면 4도',
                'quantity'         => 200,
                'unit'             => '매',
                'quantity_display' => '200매',
                'unit_price'       => 50,
                'supply_price'     => 10000,
            ],
            [
                'id'               => 3,
                'product_type'     => 'sticker',
                'product_name'     => '스티커',
                'specification'    => '아트지유광 / 50×30mm / 사각 / 칼선',
                'quantity'         => 1000,
                'unit'             => '매',
                'quantity_display' => '1,000매',
                'unit_price'       => 25,
                'supply_price'     => 25000,
            ],
        ],
    ];
}

/**
 * 전체 견적서 데이터 패키지 로드
 * view, pdf, mail 모든 출력에서 이 함수 하나로 데이터 로드
 *
 * @param mysqli|null $db DB 연결 (null이면 샘플 데이터)
 * @param int $quoteId 견적서 ID (0이면 샘플 데이터)
 * @return array [quote, items, supplier]
 */
function loadQuoteDataPackage(?mysqli $db, int $quoteId): array {
    // 샘플 데이터 사용
    if (!$db || $quoteId <= 0) {
        $sample = getSampleQuoteData();
        return [
            'quote'    => $sample['quote'],
            'items'    => $sample['items'],
            'supplier' => getSupplierInfo(),
        ];
    }

    // DB에서 로드
    $quote = loadQuoteFromDB($db, $quoteId);
    if (!$quote) {
        // 견적서 없으면 빈 데이터
        return [
            'quote'    => [],
            'items'    => [],
            'supplier' => getSupplierInfo(),
        ];
    }

    $items = loadQuoteItemsFromDB($db, $quoteId);

    return [
        'quote'    => $quote,
        'items'    => $items,
        'supplier' => getSupplierInfo(),
    ];
}
