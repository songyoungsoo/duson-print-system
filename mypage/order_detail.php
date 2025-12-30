<?php
/**
 * 주문 상세보기 페이지
 *
 * @author Claude
 * @date 2025-12-30
 */

require_once __DIR__ . '/auth_required.php';

// 주문번호 확인
$order_no = isset($_GET['no']) ? intval($_GET['no']) : 0;

if (!$order_no) {
    header("Location: orders.php");
    exit;
}

// 사용자 정보
$user_email = $current_user['email'];
$user_name = $current_user['name'];

// 주문 조회 (본인 주문만)
$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$where_check = "";
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

// 업로드 파일 파싱
$uploaded_files = [];
if (!empty($order['uploaded_files'])) {
    $uploaded_files = json_decode($order['uploaded_files'], true) ?: [];
}

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
                // Type_1이 JSON인 경우 파싱하여 보기 좋게 표시
                $type1_data = json_decode($order['Type_1'], true);
                if ($type1_data && is_array($type1_data)):
                ?>
                <div class="info-item" style="grid-column: span 2;">
                    <div class="label">규격/사양</div>
                    <div class="value">
                        <?php
                        $display_parts = [];
                        $labels = [
                            'MY_type' => '종류',
                            'MY_Fsd' => '규격',
                            'PN_t' => '용지',
                            'formatted_display' => '상세'
                        ];
                        foreach ($type1_data as $key => $val) {
                            if (in_array($key, ['product_type', 'created_at', 'type'])) continue;
                            if (empty($val)) continue;
                            $label = $labels[$key] ?? $key;
                            if (is_string($val) && strlen($val) > 100) {
                                // 긴 텍스트는 줄바꿈으로 표시
                                echo "<div style='margin-bottom: 8px;'><strong>{$label}:</strong><br>{$val}</div>";
                            } else {
                                $display_parts[] = is_string($val) ? $val : json_encode($val);
                            }
                        }
                        if (!empty($display_parts)) {
                            echo implode(' / ', array_slice($display_parts, 0, 5));
                        }
                        ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="info-item" style="grid-column: span 2;">
                    <div class="label">규격/사양</div>
                    <div class="value"><?php echo nl2br(htmlspecialchars($order['Type_1'])); ?></div>
                </div>
                <?php endif; ?>
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
            </div>

            <?php if (!empty($order['logen_tracking_no'])): ?>
            <div style="margin-top: 15px;">
                <div class="tracking-info">
                    <span class="company"><?php echo htmlspecialchars($order['delivery_company'] ?? '로젠택배'); ?></span>
                    <span class="number"><?php echo htmlspecialchars($order['logen_tracking_no']); ?></span>
                    <a href="https://www.ilogen.com/web/personal/trace/<?php echo urlencode($order['logen_tracking_no']); ?>"
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
                <tr class="total">
                    <th>총 결제금액 (VAT포함)</th>
                    <td>₩<?php echo number_format(intval($order['money_2'])); ?></td>
                </tr>
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

        <!-- 업로드 파일 -->
        <?php if (!empty($uploaded_files)): ?>
        <div class="section">
            <h2>업로드 파일</h2>
            <ul class="files-list">
                <?php foreach ($uploaded_files as $file): ?>
                <li>
                    <span class="file-icon">📄</span>
                    <span class="file-name"><?php echo htmlspecialchars($file['original_name'] ?? $file['saved_name'] ?? 'file'); ?></span>
                    <?php if (!empty($file['size'])): ?>
                    <span class="file-size"><?php echo number_format($file['size'] / 1024, 1); ?> KB</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php elseif (!empty($order['ImgFolder'])): ?>
        <div class="section">
            <h2>업로드 파일</h2>
            <div class="info-item">
                <div class="label">파일 폴더</div>
                <div class="value" style="font-family: monospace; font-size: 13px;"><?php echo htmlspecialchars($order['ImgFolder']); ?></div>
            </div>
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

        <div class="nav-link" style="text-align: center; margin: 30px 0;">
            <a href="orders.php">← 주문 내역으로 돌아가기</a>
        </div>
    </div>
</body>
</html>
