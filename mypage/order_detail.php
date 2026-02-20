<?php
/**
 * 주문 상세보기 페이지
 *
 * @author Claude
 * @date 2025-12-30
 */

require_once __DIR__ . '/auth_required.php';

// ProductSpecFormatter 로드
require_once __DIR__ . '/../includes/ProductSpecFormatter.php';
require_once __DIR__ . '/../includes/ImagePathResolver.php';
$specFormatter = new ProductSpecFormatter();

// 주문번호 파라미터 확인
$order_no = isset($_GET['no']) ? intval($_GET['no']) : 0;
if ($order_no <= 0) {
    header("Location: orders.php?error=invalid_order");
    exit;
}

// 사용자 정보 (auth_required.php에서 제공)
$user_email = $current_user['email'] ?? '';
$user_name = $current_user['name'] ?? '';

// SQL 쿼리 초기화
$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";

$params = [$order_no];
$types = "i";

// 이메일 또는 이름으로 본인 확인
if (!empty($user_email)) {
    $where_check = " AND email = ?";
    $params[] = $user_email;
    $types .= "s";
} else if (!empty($user_name)) {
    $where_check = " AND name = ?";
    $params[] = $user_name;
    $types .= "s";
} else {
    // 둘 다 없으면 조회 불가
    header("Location: orders.php");
    exit;
}

$query .= $where_check;

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    // 주문이 없거나 본인 주문이 아님
    header("Location: orders.php?error=not_found");
    exit;
}

// 주문 상태 매핑
$order_statuses = [
    '0' => '미선택',
    '1' => '견적접수',
    '2' => '주문접수',
    '3' => '접수완료',
    '4' => '입금대기',
    '5' => '시안제작중',
    '6' => '시안',
    '7' => '교정',
    '8' => '작업완료',
    '9' => '작업중',
    '10' => '교정작업중'
];

// 원고파일 목록 (ImagePathResolver 통합)
$file_result = ImagePathResolver::getFilesFromRow($order, false);
$order_files = $file_result['files'] ?? [];
// 하위호환: uploaded_files 변수도 유지
$uploaded_files = $order_files;

// 프리미엄 옵션 파싱
$premium_options = [];
if (!empty($order['premium_options'])) {
    $premium_options = json_decode($order['premium_options'], true) ?: [];
}

// 봉투 추가옵션 파싱
$envelope_options = [];
if (!empty($order['envelope_additional_options'])) {
    $envelope_options = json_decode($order['envelope_additional_options'], true) ?: [];
}

// 추가옵션 표시용 함수
function formatAdditionalOptions($order) {
    $options = [];

    // 코팅
    if (!empty($order['coating_enabled'])) {
        $coating_types = ['1' => '단면코팅', '2' => '양면코팅', 'glossy' => '유광코팅', 'matte' => '무광코팅'];
        $type = $coating_types[$order['coating_type']] ?? $order['coating_type'];
        $options[] = "코팅: {$type} (₩" . number_format($order['coating_price']) . ")";
    }

    // 접지
    if (!empty($order['folding_enabled'])) {
        $folding_types = ['2fold' => '2단접지', '3fold' => '3단접지', '4fold' => '4단접지'];
        $type = $folding_types[$order['folding_type']] ?? $order['folding_type'];
        $options[] = "접지: {$type} (₩" . number_format($order['folding_price']) . ")";
    }

    // 오시
    if (!empty($order['creasing_enabled'])) {
        $options[] = "오시: {$order['creasing_lines']}줄 (₩" . number_format($order['creasing_price']) . ")";
    }

    return $options;
}

function formatPremiumOptions($premium_options) {
    if (empty($premium_options)) return [];

    $labels = [
        'foil' => '박/금박',
        'embossing' => '형압',
        'numbering' => '넘버링',
        'perforation' => '미싱',
        'rounding' => '라운딩',
        'edge_coloring' => '에지컬러',
        'creasing' => '오시'
    ];

    $options = [];
    foreach ($premium_options as $key => $value) {
        if ($key === 'total' || empty($value)) continue;
        $label = $labels[$key] ?? $key;
        if (is_array($value)) {
            $options[] = "{$label}: " . ($value['type'] ?? '사용') . " (₩" . number_format($value['price'] ?? 0) . ")";
        } else {
            $options[] = "{$label}: {$value}";
        }
    }
    return $options;
}

/**
 * Type_1 JSON을 읽기 쉬운 텍스트로 변환
 * ✅ ProductSpecFormatter 사용으로 중복 코드 제거
 */
function formatType1Json($type1_data) {
    global $specFormatter;

    if (!$type1_data || !is_array($type1_data)) {
        return null;
    }

    // ProductSpecFormatter로 규격 정보 추출
    $order_data = $type1_data;
    $order_data['product_type'] = $type1_data['product_type'] ?? '';
    $order_data['Type_1'] = json_encode($type1_data);

    $spec_result = $specFormatter->format($order_data);

    // 2줄 형식을 단일 출력으로 변환
    $output_parts = [];
    if (!empty($spec_result['line1'])) {
        $output_parts[] = htmlspecialchars($spec_result['line1']);
    }
    if (!empty($spec_result['line2'])) {
        $output_parts[] = htmlspecialchars($spec_result['line2']);
    }

    return !empty($output_parts) ? implode('<br>', $output_parts) : null;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 상세 - <?php echo htmlspecialchars($order['no']); ?> - 두손기획인쇄</title>
    <link rel="stylesheet" href="/mlangprintauto/css/common-styles.css">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }

        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header h1 { color: #333; margin: 0; }
        .header .order-no { color: #667eea; font-size: 0.9em; margin-top: 5px; }

        .nav-link { margin: 20px 0; }
        .nav-link a { color: #667eea; text-decoration: none; }

        .section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section h2 {
            color: #333;
            font-size: 18px;
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .info-item .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .info-item .value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        .status-0, .status-1 { background: #fff3cd; color: #856404; }
        .status-2, .status-3, .status-4 { background: #d1ecf1; color: #0c5460; }
        .status-5, .status-6, .status-7, .status-9, .status-10 { background: #d4edda; color: #155724; }
        .status-8 { background: #c3e6cb; color: #155724; font-weight: bold; }

        .price-table {
            width: 100%;
            border-collapse: collapse;
        }
        .price-table th, .price-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .price-table th { color: #666; font-weight: 500; width: 40%; }
        .price-table td { color: #333; }
        .price-table tr.total { background: #f8f9fa; font-weight: bold; }
        .price-table tr.total td { color: #667eea; font-size: 18px; }

        .options-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .options-list li {
            padding: 8px 12px;
            background: #e8f4f8;
            border-radius: 4px;
            margin-bottom: 8px;
            color: #0c5460;
        }
        .no-options {
            color: #999;
            font-style: italic;
        }

        .files-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .files-list li {
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .files-list .file-icon { font-size: 20px; }
        .files-list .file-name { flex: 1; color: #333; }
        .files-list .file-size { color: #666; font-size: 13px; }

        .tracking-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .tracking-info .company { font-weight: 500; color: #2e7d32; }
        .tracking-info .number { font-family: monospace; font-size: 16px; color: #333; }

        .memo-box {
            background: #fffde7;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #fbc02d;
            white-space: pre-wrap;
            color: #333;
        }

        @media (max-width: 768px) {
            .info-grid { grid-template-columns: 1fr; }
            .container { padding: 10px; }
            .section { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-link">
            <a href="orders.php">← 주문 내역으로 돌아가기</a>
        </div>

        <div class="header">
            <h1>주문 상세</h1>
            <p class="order-no">주문번호: <?php echo htmlspecialchars($order['no']); ?></p>
        </div>

        <!-- 주문 기본 정보 -->
        <div class="section">
            <h2>주문 정보</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">주문번호</div>
                    <div class="value"><?php echo htmlspecialchars($order['no']); ?></div>
                </div>
                <div class="info-item">
                    <div class="label">주문일시</div>
                    <div class="value"><?php echo date('Y-m-d H:i', strtotime($order['date'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="label">제품</div>
                    <div class="value"><?php echo htmlspecialchars($order['Type']); ?></div>
                </div>
                <div class="info-item">
                    <div class="label">상태</div>
                    <div class="value">
                        <span class="status-badge status-<?php echo $order['OrderStyle']; ?>">
                            <?php echo $order_statuses[$order['OrderStyle']] ?? $order['OrderStyle']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 제품 상세 -->
        <div class="section">
            <h2>제품 상세</h2>
            <div class="info-grid">
                <?php if (!empty($order['Type_1'])): ?>
                <?php
                // Type_1 JSON 파싱 및 표시
                $type1_data = json_decode($order['Type_1'], true);
                $formatted_spec = formatType1Json($type1_data);
                ?>
                <div class="info-item" style="grid-column: span 2;">
                    <div class="label">규격/사양</div>
                    <div class="value">
                        <?php if ($formatted_spec): ?>
                            <?php echo $formatted_spec; ?>
                        <?php else: ?>
                            <?php echo nl2br(htmlspecialchars($order['Type_1'])); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($order['mesu'])): ?>
                <div class="info-item">
                    <div class="label">수량</div>
                    <div class="value"><?php echo htmlspecialchars($order['mesu']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($order['quantity']) && $order['quantity'] != '1.00'): ?>
                <div class="info-item">
                    <div class="label">수량 (숫자)</div>
                    <div class="value"><?php echo htmlspecialchars($order['quantity']); ?> <?php echo htmlspecialchars($order['unit'] ?? '개'); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($order['ThingCate'])): ?>
                <div class="info-item">
                    <div class="label">품목코드</div>
                    <div class="value"><?php echo htmlspecialchars($order['ThingCate']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 주문자 정보 -->
        <div class="section">
            <h2>주문자 정보</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">주문자명</div>
                    <div class="value"><?php echo htmlspecialchars($order['name']); ?></div>
                </div>
                <?php if (!empty($order['Hendphone'])): ?>
                <div class="info-item">
                    <div class="label">휴대폰</div>
                    <div class="value"><?php echo htmlspecialchars($order['Hendphone']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['phone'])): ?>
                <div class="info-item">
                    <div class="label">전화번호</div>
                    <div class="value"><?php echo htmlspecialchars($order['phone']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['email'])): ?>
                <div class="info-item">
                    <div class="label">이메일</div>
                    <div class="value"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['bizname'])): ?>
                <div class="info-item">
                    <div class="label">상호/업체명</div>
                    <div class="value"><?php echo htmlspecialchars($order['bizname']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 배송 정보 -->
        <?php if (!empty($order['zip']) || !empty($order['zip1'])): ?>
        <div class="section">
            <h2>배송 정보</h2>
            <div class="info-grid">
                <?php if (!empty($order['zip'])): ?>
                <div class="info-item">
                    <div class="label">우편번호</div>
                    <div class="value"><?php echo htmlspecialchars($order['zip']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['zip1'])): ?>
                <div class="info-item" style="grid-column: span 2;">
                    <div class="label">주소</div>
                    <div class="value"><?php echo htmlspecialchars($order['zip1']); ?> <?php echo htmlspecialchars($order['zip2'] ?? ''); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['delivery'])): ?>
                <div class="info-item">
                    <div class="label">배송방법</div>
                    <div class="value"><?php echo htmlspecialchars($order['delivery']); ?></div>
                </div>
                <?php endif; ?>
                <?php
                $logen_fee_type = $order['logen_fee_type'] ?? '';
                $logen_delivery_fee = intval($order['logen_delivery_fee'] ?? 0);
                if ($logen_fee_type === '선불'):
                ?>
                <div class="info-item">
                    <div class="label">운임구분</div>
                    <div class="value">선불</div>
                </div>
                <div class="info-item">
                    <div class="label">택배비</div>
                    <div class="value">
                        <?php if ($logen_delivery_fee > 0): ?>
                            <span style="color: #155724; font-weight: 600;">₩<?php echo number_format($logen_delivery_fee); ?> <span style="font-size: 12px; color: #666;">(+VAT ₩<?php echo number_format(round($logen_delivery_fee * 0.1)); ?>)</span></span>
                        <?php else: ?>
                            <span style="color: #856404; font-weight: 500;">확인중 (전화 안내 예정)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php
            $tracking_no = $order['waybill_no'] ?? $order['logen_tracking_no'] ?? '';
            $delivery_co = $order['delivery_company'] ?? '로젠택배';
            if (!empty($tracking_no)):
            ?>
            <div style="margin-top: 15px;">
                <div class="tracking-info">
                    <span class="company"><?php echo htmlspecialchars($delivery_co); ?></span>
                    <span class="number"><?php echo htmlspecialchars($tracking_no); ?></span>
                    <a href="https://www.ilogen.com/web/personal/trace/<?php echo urlencode($tracking_no); ?>"
                       target="_blank"
                       style="color: #667eea; text-decoration: none;">
                       배송조회 →
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- 가격 정보 -->
        <div class="section">
            <h2>결제 정보</h2>
            <table class="price-table">
                <tr>
                    <th>공급가액</th>
                    <td>₩<?php echo number_format(intval($order['money_1'])); ?></td>
                </tr>
                <?php if (!empty($order['additional_options_total']) && $order['additional_options_total'] > 0): ?>
                <tr>
                    <th>추가옵션</th>
                    <td>₩<?php echo number_format($order['additional_options_total']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['premium_options_total']) && $order['premium_options_total'] > 0): ?>
                <tr>
                    <th>프리미엄옵션</th>
                    <td>₩<?php echo number_format($order['premium_options_total']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['envelope_additional_options_total']) && $order['envelope_additional_options_total'] > 0): ?>
                <tr>
                    <th>봉투옵션</th>
                    <td>₩<?php echo number_format($order['envelope_additional_options_total']); ?></td>
                </tr>
                <?php endif; ?>
                <?php
                $order_total = intval($order['money_2']);
                $lf_type = $order['logen_fee_type'] ?? '';
                $lf_fee = intval($order['logen_delivery_fee'] ?? 0);
                $has_prepaid = ($lf_type === '선불');
                $shipping_with_vat = $lf_fee + round($lf_fee * 0.1);
                ?>
                <?php if ($has_prepaid && $lf_fee > 0): ?>
                <tr>
                    <th>인쇄비 소계 (VAT포함)</th>
                    <td>₩<?php echo number_format($order_total); ?></td>
                </tr>
                <tr>
                    <th>택배비 (VAT포함)</th>
                    <td>₩<?php echo number_format($shipping_with_vat); ?></td>
                </tr>
                <tr class="total">
                    <th>총 입금액</th>
                    <td>₩<?php echo number_format($order_total + $shipping_with_vat); ?></td>
                </tr>
                <?php elseif ($has_prepaid && $lf_fee === 0): ?>
                <tr class="total">
                    <th>인쇄비 (VAT포함)</th>
                    <td>₩<?php echo number_format($order_total); ?></td>
                </tr>
                <tr>
                    <th>택배비</th>
                    <td style="color: #856404; font-weight: 500;">확인중 (전화 안내 예정)</td>
                </tr>
                <?php else: ?>
                <tr class="total">
                    <th>총 결제금액 (VAT포함)</th>
                    <td>₩<?php echo number_format($order_total); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['bank']) || !empty($order['bankname'])): ?>
                <tr>
                    <th>입금은행</th>
                    <td><?php echo htmlspecialchars($order['bank'] ?? ''); ?> <?php echo htmlspecialchars($order['bankname'] ?? ''); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- 추가옵션 -->
        <?php
        $additional_options = formatAdditionalOptions($order);
        $premium_opts = formatPremiumOptions($premium_options);
        if (!empty($additional_options) || !empty($premium_opts) || !empty($order['envelope_tape_enabled'])):
        ?>
        <div class="section">
            <h2>추가옵션</h2>
            <?php if (!empty($additional_options)): ?>
            <ul class="options-list">
                <?php foreach ($additional_options as $opt): ?>
                <li><?php echo htmlspecialchars($opt); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if (!empty($premium_opts)): ?>
            <h3 style="font-size: 14px; color: #666; margin: 15px 0 10px;">프리미엄 옵션</h3>
            <ul class="options-list">
                <?php foreach ($premium_opts as $opt): ?>
                <li><?php echo htmlspecialchars($opt); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if (!empty($order['envelope_tape_enabled'])): ?>
            <h3 style="font-size: 14px; color: #666; margin: 15px 0 10px;">봉투 옵션</h3>
            <ul class="options-list">
                <li>양면테이프: <?php echo number_format($order['envelope_tape_quantity']); ?>개 (₩<?php echo number_format($order['envelope_tape_price']); ?>)</li>
            </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- 원고파일 -->
        <?php if (!empty($order_files)): ?>
        <div class="section">
            <h2>원고파일 (<?php echo count($order_files); ?>개)</h2>
            <ul class="files-list">
                <?php foreach ($order_files as $f):
                    $fname = $f['name'] ?? $f['saved_name'] ?? 'file';
                    $fsize = isset($f['size']) ? number_format($f['size'] / 1024, 1) . ' KB' : '';
                    $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                    $is_image = in_array($ext, ['jpg','jpeg','png','gif','bmp','tif','tiff']);
                    $dl_url = '/mypage/download.php?downfile=' . urlencode($fname) . '&no=' . $order_no;
                ?>
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0;">
                    <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                        <span class="file-icon"><?php echo $is_image ? '🖼️' : '📄'; ?></span>
                        <div style="min-width: 0;">
                            <span class="file-name" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($fname); ?></span>
                            <?php if ($fsize): ?>
                            <span class="file-size" style="color: #999; font-size: 12px;"><?php echo $fsize; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?php echo htmlspecialchars($dl_url); ?>"
                       style="flex-shrink: 0; padding: 4px 12px; font-size: 12px; color: #667eea; border: 1px solid #667eea; border-radius: 4px; text-decoration: none; white-space: nowrap;"
                       title="다운로드">
                       다운로드
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- 메모/요청사항 -->
        <?php if (!empty($order['cont'])): ?>
        <div class="section">
            <h2>요청사항</h2>
            <div class="memo-box"><?php echo nl2br(htmlspecialchars($order['cont'])); ?></div>
        </div>
        <?php endif; ?>

        <!-- 교정 정보 -->
        <?php if (!empty($order['proofreading_confirmed'])): ?>
        <div class="section">
            <h2>교정 승인</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">승인 상태</div>
                    <div class="value" style="color: #28a745;">✅ 승인 완료</div>
                </div>
                <?php if (!empty($order['proofreading_date'])): ?>
                <div class="info-item">
                    <div class="label">승인 일시</div>
                    <div class="value"><?php echo date('Y-m-d H:i', strtotime($order['proofreading_date'])); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['proofreading_by'])): ?>
                <div class="info-item">
                    <div class="label">승인자</div>
                    <div class="value"><?php echo htmlspecialchars($order['proofreading_by']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 결제 섹션 -->
        <?php
        $is_unpaid = in_array($order['OrderStyle'], ['2', '3', '4']);
        $lf_type_pay = $order['logen_fee_type'] ?? '';
        $lf_fee_pay = intval($order['logen_delivery_fee'] ?? 0);
        $is_prepaid_pay = ($lf_type_pay === '선불');

        // 결제 가능: 미결제 + (선불 아님 OR 택배비 확정)
        $can_pay = $is_unpaid && (!$is_prepaid_pay || $lf_fee_pay > 0);

        $print_amount_pay = intval($order['money_5'] ?? $order['money_4'] ?? 0);
        $shipping_total_pay = 0;
        if ($is_prepaid_pay && $lf_fee_pay > 0) {
            $shipping_total_pay = $lf_fee_pay + round($lf_fee_pay * 0.1);
        }
        $total_payment = $print_amount_pay + $shipping_total_pay;
        ?>

        <?php if ($can_pay): ?>
        <div class="section" style="border: 2px solid #667eea; background: #f8f9ff;">
            <h2 style="color: #667eea; border-bottom-color: #667eea;">결제하기</h2>

            <table class="price-table" style="margin-bottom: 20px;">
                <tr>
                    <th>인쇄비 (VAT포함)</th>
                    <td>₩<?php echo number_format($print_amount_pay); ?></td>
                </tr>
                <?php if ($is_prepaid_pay && $lf_fee_pay > 0): ?>
                <tr>
                    <th>택배비 (VAT포함)</th>
                    <td>₩<?php echo number_format($shipping_total_pay); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total">
                    <th>총 결제금액</th>
                    <td>₩<?php echo number_format($total_payment); ?>원</td>
                </tr>
            </table>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <!-- 카드결제 -->
                <a href="/payment/inicis_request.php?order_no=<?php echo $order['no']; ?>"
                   style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 12px;
                          padding: 16px 20px; background: #667eea; color: #fff; border-radius: 8px;
                          text-decoration: none; font-weight: 600; font-size: 15px;
                          transition: background 0.2s;">
                    <span style="font-size: 24px;">💳</span>
                    <div>
                        <div>카드결제 / 실시간이체</div>
                        <div style="font-size: 12px; font-weight: 400; opacity: 0.85; margin-top: 2px;">신용카드 또는 실시간 계좌이체</div>
                    </div>
                </a>

                <!-- 무통장입금 -->
                <button onclick="document.getElementById('bankInfoSection').style.display = document.getElementById('bankInfoSection').style.display === 'none' ? 'block' : 'none';"
                        style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 12px;
                               padding: 16px 20px; background: #fff; color: #333; border: 2px solid #ddd;
                               border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 15px;
                               transition: border-color 0.2s; text-align: left;">
                    <span style="font-size: 24px;">🏦</span>
                    <div>
                        <div>무통장입금</div>
                        <div style="font-size: 12px; font-weight: 400; color: #888; margin-top: 2px;">계좌번호 확인 후 직접 입금</div>
                    </div>
                </button>
            </div>

            <!-- 무통장입금 계좌 정보 (토글) -->
            <div id="bankInfoSection" style="display: none; margin-top: 16px; padding: 20px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px;">
                <h3 style="margin: 0 0 12px; font-size: 15px; color: #333;">입금 계좌 안내</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 8px 0; font-weight: 600; width: 90px;">국민은행</td>
                        <td style="padding: 8px 0; font-family: monospace; font-size: 15px;">999-1688-2384</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 8px 0; font-weight: 600;">신한은행</td>
                        <td style="padding: 8px 0; font-family: monospace; font-size: 15px;">110-342-543507</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 8px 0; font-weight: 600;">농협</td>
                        <td style="padding: 8px 0; font-family: monospace; font-size: 15px;">301-2632-1830-11</td>
                    </tr>
                </table>
                <p style="margin: 12px 0 0; font-size: 13px; color: #666;">
                    <strong>예금주: 두손기획인쇄 차경선</strong><br>
                    입금자명을 주문자명과 동일하게 해주세요.
                </p>
            </div>
        </div>

        <?php elseif ($is_unpaid && $is_prepaid_pay && $lf_fee_pay === 0): ?>
        <div class="section" style="border: 2px solid #e67e22; background: #fff8f0;">
            <h2 style="color: #e67e22; border-bottom-color: #e67e22;">택배비 확정 대기중</h2>
            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 40px; margin-bottom: 12px;">📦</div>
                <p style="color: #856404; font-size: 15px; line-height: 1.8; margin: 0;">
                    택배비가 아직 확정되지 않았습니다.<br>
                    관리자가 택배비를 확정하면 결제가 가능합니다.<br>
                    <strong style="font-size: 16px; color: #c0392b;">☎ 02-2632-1830</strong>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <div class="nav-link" style="text-align: center; margin: 30px 0;">
            <a href="orders.php">← 주문 내역으로 돌아가기</a>
        </div>
    </div>
</body>
</html>
