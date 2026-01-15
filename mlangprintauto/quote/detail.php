<?php
/**
 * 견적서 상세 페이지 (관리자용) - 엑셀 스타일
 */

session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/includes/QuoteManager.php';
require_once __DIR__ . '/../includes/QuantityFormatter.php';

if (!$db) {
    die('데이터베이스 연결 실패');
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$manager = new QuoteManager($db);
$quote = $manager->getById($id);

if (!$quote) {
    header('Location: index.php');
    exit;
}

$company = $manager->getCompanySettings();
$items = $quote['items'];

// 이메일 발송 이력
$emailLogs = [];
$emailQuery = "SELECT * FROM quote_emails WHERE quote_id = ? ORDER BY sent_at DESC";
$stmt = mysqli_prepare($db, $emailQuery);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$emailResult = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($emailResult)) {
    $emailLogs[] = $row;
}

// 상태 라벨 (SP 컬러 적용: Success = Navy, Error = Red)
$statusLabels = [
    'draft' => ['label' => '작성중', 'color' => '#6c757d'],
    'sent' => ['label' => '발송', 'color' => '#0d6efd'],
    'viewed' => ['label' => '확인', 'color' => '#17a2b8'],
    'accepted' => ['label' => '승인', 'color' => '#1E4E79'],  // SP: Navy
    'rejected' => ['label' => '거절', 'color' => '#dc3545'],
    'expired' => ['label' => '만료', 'color' => '#6c757d'],
    'converted' => ['label' => '주문', 'color' => '#1E4E79']  // SP: Navy
];

$statusInfo = $statusLabels[$quote['status']] ?? ['label' => $quote['status'], 'color' => '#6c757d'];
$isExpired = ($quote['valid_until'] && strtotime($quote['valid_until']) > 0 && strtotime($quote['valid_until']) < time()) && !in_array($quote['status'], ['accepted', 'rejected', 'converted']);

// 공개 URL
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$publicUrl = $baseUrl . '/mlangprintauto/quote/public/view.php?token=' . $quote['public_token'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 상세 - <?php echo htmlspecialchars($quote['quote_no']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/color-system-unified.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans KR', sans-serif; background: #f0f0f0; font-size: 13px; }

        .container { max-width: 1400px; margin: 0 auto; padding: 12px; }

        /* 헤더 */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            background: #fff;
            padding: 10px 18px;
            border: 1px solid #ccc;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-back {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-right: 12px;
            transition: background 0.2s;
        }
        .btn-back:hover {
            background: #5a6268;
            color: #fff;
        }

        .header-actions { display: flex; gap: 4px; }
        .btn {
            padding: 5px 12px;
            border: 1px solid #ccc;
            background: #f8f8f8;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-size: 13px;
        }
        .btn:hover { background: #e0e0e0; }
        .btn-primary { background: var(--dsp-primary, #1E4E79); color: #fff; border-color: var(--dsp-primary, #1E4E79); }
        .btn-primary:hover { background: var(--dsp-primary-dark, #153A5A); }
        .btn-warning { background: #ffc107; color: #000; border-color: #ffc107; }
        .btn-warning:hover { background: #e0a800; }

        .status-tag {
            display: inline-block;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 500;
            color: #fff;
        }
        .expired-tag { background: #dc3545; margin-left: 5px; }

        /* 알림 박스 */
        .alert {
            padding: 10px 15px;
            margin-bottom: 12px;
            border: 1px solid;
            font-size: 13px;
        }
        .alert-warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        .alert-success { background: var(--dsp-primary-lighter, #E8F0F7); border-color: var(--dsp-primary, #1E4E79); color: var(--dsp-primary-dark, #153A5A); }
        .alert-danger { background: #f8d7da; border-color: #dc3545; color: #721c24; }

        /* 그리드 레이아웃 */
        .grid { display: grid; grid-template-columns: 1fr 350px; gap: 12px; }

        /* 섹션 박스 */
        .section {
            background: #fff;
            border: 1px solid #8c8c8c;
            margin-bottom: 12px;
        }
        .section-header {
            background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 100%);
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #8c8c8c;
        }
        .section-body { padding: 12px; }

        /* 정보 테이블 (라벨-값) */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table th, .info-table td {
            padding: 6px 10px;
            border: 1px solid #c0c0c0;
            font-size: 13px;
            vertical-align: middle;
        }
        .info-table th {
            background: #f5f5f5;
            font-weight: normal;
            width: 90px;
            text-align: left;
            color: #555;
        }
        .info-table td {
            background: #fff;
        }

        /* 품목 테이블 (엑셀 스타일) */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
        }
        .excel-table th {
            background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 100%);
            border: 1px solid #8c8c8c;
            padding: 7px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            color: #333;
        }
        .excel-table td {
            border: 1px solid #c0c0c0;
            padding: 6px 10px;
            font-size: 13px;
            vertical-align: middle;
        }
        .excel-table tbody tr:hover { background: #e8f4fc; }
        .excel-table tbody tr:nth-child(even) { background: #fafafa; }
        .excel-table tbody tr:nth-child(even):hover { background: #e8f4fc; }

        .col-no { width: 40px; text-align: center; }
        .col-name { }
        .col-spec { }
        .col-qty { width: 80px; text-align: center; }
        .col-unit { width: 50px; text-align: center; }
        .col-price { width: 60px; text-align: right; font-family: 'Noto Sans KR', sans-serif; }
        .col-supply { width: 130px; text-align: right; font-family: 'Noto Sans KR', sans-serif; }
        .col-notes { width: 10%; text-align: left; font-size: 12px; color: #666; }

        /* 합계 테이블 */
        .summary-table {
            width: 250px;
            margin-left: auto;
            margin-top: 12px;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 6px 10px;
            border: 1px solid #c0c0c0;
            font-size: 13px;
        }
        .summary-table th {
            background: #f5f5f5;
            font-weight: normal;
            text-align: left;
            width: 80px;
        }
        .summary-table td {
            text-align: right;
            font-family: 'Noto Sans KR', sans-serif;
        }
        .summary-table tr.total-row th,
        .summary-table tr.total-row td {
            background: var(--dsp-primary, #1E4E79);
            color: #fff;
            font-weight: bold;
            border-color: var(--dsp-primary, #1E4E79);
        }

        /* URL 박스 */
        .url-box {
            display: flex;
            gap: 4px;
        }
        .url-box input {
            flex: 1;
            padding: 5px 8px;
            border: 1px solid #ccc;
            font-size: 12px;
        }
        .url-box button {
            padding: 5px 12px;
            background: #5a5a5a;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }
        .url-box button:hover { background: #444; }

        /* 이메일 이력 */
        .email-list { }
        .email-item {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        .email-item:last-child { border-bottom: none; }
        .email-status { margin-right: 6px; }
        .email-addr { font-weight: 500; }
        .email-date { color: #666; font-size: 12px; margin-left: 8px; }
        .email-error { color: #dc3545; font-size: 12px; display: block; margin-top: 3px; }

        /* 비고 박스 */
        .notes-box {
            background: #fafafa;
            border: 1px solid #e0e0e0;
            padding: 10px;
            font-size: 13px;
            white-space: pre-wrap;
        }

        /* 빈 상태 */
        .empty-text {
            color: #999;
            text-align: center;
            padding: 15px;
            font-size: 13px;
        }

        @media (max-width: 1024px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 헤더 -->
        <div class="header">
            <h1>
                <a href="index.php" class="btn-back">📋 견적서 관리</a>
                <?php echo htmlspecialchars($quote['quote_no']); ?>
                <span class="status-tag" style="background:<?php echo $statusInfo['color']; ?>;">
                    <?php echo $statusInfo['label']; ?>
                </span>
                <?php if ($isExpired): ?>
                <span class="status-tag expired-tag">만료</span>
                <?php endif; ?>
            </h1>
            <div class="header-actions">
                <?php if ($quote['status'] === 'draft'): ?>
                    <!-- draft 상태: 수정 가능 -->
                    <a href="edit.php?id=<?php echo $quote['id']; ?>" class="btn btn-warning">✏️ 수정</a>
                    <a href="api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">PDF</a>
                    <a href="public/view.php?token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">미리보기</a>
                    <button class="btn btn-primary" onclick="sendEmail()">📧 메일 발송</button>
                <?php elseif ($quote['status'] === 'sent'): ?>
                    <!-- sent 상태: 개정판 작성 + 주문 변환 가능 -->
                    <a href="revise.php?id=<?php echo $quote['id']; ?>" class="btn btn-warning">📝 개정판 작성</a>
                    <a href="api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">PDF</a>
                    <a href="public/view.php?token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">미리보기</a>
                    <button class="btn btn-primary" onclick="sendEmail()">📧 다시 보내기</button>
                    <button class="btn" style="background: #1E4E79; color: white;" onclick="convertToOrder(<?php echo $quote['id']; ?>)">🛒 주문 변환</button>
                <?php elseif ($quote['status'] === 'accepted'): ?>
                    <!-- accepted 상태: 주문 변환 가능 -->
                    <a href="api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">PDF</a>
                    <a href="public/view.php?token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">미리보기</a>
                    <button class="btn" style="background: #1E4E79; color: white; font-weight: bold;" onclick="convertToOrder(<?php echo $quote['id']; ?>)">🛒 주문으로 변환</button>
                <?php elseif ($quote['status'] === 'converted'): ?>
                    <!-- converted 상태: 주문 보기 -->
                    <a href="api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">PDF</a>
                    <a href="public/view.php?token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">미리보기</a>
                    <?php if (!empty($quote['converted_order_no'])): ?>
                    <a href="/admin/mlangprintauto/admin.php?mode=OrderView&no=<?php echo htmlspecialchars($quote['converted_order_no']); ?>" class="btn" style="background: #1E4E79; color: white;" target="_blank">📦 주문 보기 (#<?php echo htmlspecialchars($quote['converted_order_no']); ?>)</a>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- 기타 상태: 조회만 가능 -->
                    <a href="api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">PDF</a>
                    <a href="public/view.php?token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">미리보기</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 알림 메시지 -->
        <?php if ($quote['status'] === 'accepted'): ?>
        <div class="alert alert-success">
            이 견적서는 고객이 승인하였습니다.
            <?php if ($quote['responded_at']): ?>
            (<?php echo date('Y-m-d H:i', strtotime($quote['responded_at'])); ?>)
            <?php endif; ?>
        </div>
        <?php elseif ($quote['status'] === 'rejected'): ?>
        <div class="alert alert-danger">
            이 견적서는 고객이 거절하였습니다.
            <?php if ($quote['responded_at']): ?>
            (<?php echo date('Y-m-d H:i', strtotime($quote['responded_at'])); ?>)
            <?php endif; ?>
        </div>
        <?php elseif ($quote['status'] === 'converted'): ?>
        <div class="alert" style="background: #d1e7dd; border-color: #badbcc; color: #0f5132;">
            ✅ 이 견적서는 주문으로 변환되었습니다.
            <?php if (!empty($quote['converted_order_no'])): ?>
            <a href="/admin/mlangprintauto/admin.php?mode=OrderView&no=<?php echo htmlspecialchars($quote['converted_order_no']); ?>" target="_blank" style="color: #0d6efd; text-decoration: underline; margin-left: 10px;">
                📦 주문 #<?php echo htmlspecialchars($quote['converted_order_no']); ?> 보기
            </a>
            <?php endif; ?>
        </div>
        <?php elseif ($isExpired): ?>
        <div class="alert alert-warning">
            <?php
            $validUntil = $quote['valid_until'];
            if ($validUntil && strtotime($validUntil) > 0) {
                echo '이 견적서는 ' . date('Y년 m월 d일', strtotime($validUntil)) . '에 만료되었습니다.';
            } else {
                echo '이 견적서는 만료되었습니다.';
            }
            ?>
        </div>
        <?php endif; ?>

        <div class="grid">
            <!-- 메인 영역 -->
            <div class="main-content">
                <!-- 견적 정보 -->
                <div class="section">
                    <div class="section-header">견적 정보</div>
                    <div class="section-body">
                        <table class="info-table">
                            <tr>
                                <th>회사명</th>
                                <td><?php echo htmlspecialchars($quote['customer_company'] ?: '-'); ?></td>
                                <th>담당자</th>
                                <td><?php echo htmlspecialchars($quote['customer_name']); ?></td>
                            </tr>
                            <tr>
                                <th>이메일</th>
                                <td><?php echo htmlspecialchars($quote['customer_email'] ?: '-'); ?></td>
                                <th>연락처</th>
                                <td><?php echo htmlspecialchars($quote['customer_phone'] ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>작성일</th>
                                <td><?php echo date('Y-m-d H:i', strtotime($quote['created_at'])); ?></td>
                                <th>유효기간</th>
                                <td><?php
                                    if ($quote['valid_until'] && strtotime($quote['valid_until']) > 0) {
                                        echo date('Y-m-d', strtotime($quote['valid_until'])) . '까지';
                                    } else {
                                        echo '설정 안 됨';
                                    }
                                ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- 품목 목록 -->
                <div class="section">
                    <div class="section-header">품목 목록 (<?php echo count($items); ?>건)</div>
                    <div class="section-body" style="padding: 0;">
                        <table class="excel-table">
                            <thead>
                                <tr>
                                    <th class="col-no">NO</th>
                                    <th class="col-name">품명</th>
                                    <th class="col-spec">규격/사양</th>
                                    <th class="col-qty">수량</th>
                                    <th class="col-unit">단위</th>
                                    <th class="col-price">단가</th>
                                    <th class="col-supply">공급가액</th>
                                    <th class="col-notes">비고</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($items as $item): ?>
                                <tr>
                                    <td class="col-no"><?php echo $no++; ?></td>
                                    <td class="col-name"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td class="col-spec"><?php echo htmlspecialchars($item['specification']); ?></td>
                                    <td class="col-qty"><?php
                                        // === 하이브리드 모델: qty_val/qty_unit 우선 사용, 없으면 레거시 fallback ===
                                        $qtyVal = $item['qty_val'] ?? $item['quantity'] ?? 0;
                                        $qtyUnit = $item['qty_unit'] ?? 'E';
                                        $qtySheets = intval($item['qty_sheets'] ?? 0);

                                        // 숫자 포맷팅: 정수면 소수점 없이, 소수면 필요한 만큼만
                                        $qtyVal = floatval($qtyVal);
                                        if (floor($qtyVal) == $qtyVal) {
                                            $qtyDisplay = number_format($qtyVal);
                                        } else {
                                            $qtyDisplay = rtrim(rtrim(number_format($qtyVal, 2), '0'), '.');
                                        }
                                        echo $qtyDisplay;

                                        // ✅ 2026-01-16: SSOT 연동 - 연/권 단위 모두 매수 표시
                                        // qty_sheets가 0이면 DB 조회(전단지) 또는 계산(NCR)
                                        if ($qtySheets <= 0 && $qtyVal > 0 && in_array($qtyUnit, ['R', 'V'])) {
                                            if ($qtyUnit === 'R') {
                                                // 전단지: mlangprintauto_inserted 테이블에서 매수 조회
                                                $sheetStmt = mysqli_prepare($db, "SELECT quantityTwo FROM mlangprintauto_inserted WHERE quantity = ? LIMIT 1");
                                                if ($sheetStmt) {
                                                    mysqli_stmt_bind_param($sheetStmt, "d", $qtyVal);
                                                    mysqli_stmt_execute($sheetStmt);
                                                    $sheetResult = mysqli_stmt_get_result($sheetStmt);
                                                    if ($sheetRow = mysqli_fetch_assoc($sheetResult)) {
                                                        $qtySheets = intval($sheetRow['quantityTwo']);
                                                    }
                                                    mysqli_stmt_close($sheetStmt);
                                                }
                                            } elseif ($qtyUnit === 'V') {
                                                // NCR양식지: 권 × 50 × multiplier(기본 2)
                                                $qtySheets = QuantityFormatter::calculateNcrSheets(intval($qtyVal), 2);
                                            }
                                        }

                                        // 연/권 단위인 경우 매수 표시 추가 - 파란색 강조
                                        if (in_array($qtyUnit, ['R', 'V']) && $qtySheets > 0) {
                                            echo '<br><span style="font-size: 10px; color: #1e88ff;">(' . number_format($qtySheets) . '매)</span>';
                                        }
                                    ?></td>
                                    <td class="col-unit"><?php
                                        // 하이브리드 모델: qty_unit 코드 → 한글 단위명 변환
                                        $qtyUnitDisplay = $item['qty_unit'] ?? null;
                                        if ($qtyUnitDisplay && isset(QuantityFormatter::UNIT_CODES[$qtyUnitDisplay])) {
                                            echo QuantityFormatter::getUnitName($qtyUnitDisplay);
                                        } else {
                                            // 레거시 fallback: 기존 unit 필드 사용
                                            echo htmlspecialchars($item['unit'] ?? '개');
                                        }
                                    ?></td>
                                    <td class="col-price"><?php
                                        // 소수점 1자리까지 표시 (모든 품목)
                                        $unitPrice = floatval($item['unit_price']);
                                        // 소수점이 있으면 소수점 1자리까지, 정수면 정수로 표시
                                        if ($unitPrice == floor($unitPrice)) {
                                            echo number_format($unitPrice);
                                        } else {
                                            echo number_format($unitPrice, 1);
                                        }
                                    ?></td>
                                    <td class="col-supply"><?php echo number_format($item['supply_price']); ?></td>
                                    <td class="col-notes"><?php echo htmlspecialchars($item['notes'] ?? ''); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="padding: 12px;">
                            <table class="summary-table">
                                <tr>
                                    <th>공급가액</th>
                                    <td><?php echo number_format($quote['supply_total']); ?> 원</td>
                                </tr>
                                <tr>
                                    <th>부가세</th>
                                    <td><?php echo number_format($quote['vat_total']); ?> 원</td>
                                </tr>
                                <?php if ($quote['delivery_price'] > 0): ?>
                                <tr>
                                    <th>배송비</th>
                                    <td><?php echo number_format($quote['delivery_price']); ?> 원</td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($quote['discount_amount'] > 0): ?>
                                <tr>
                                    <th>할인</th>
                                    <td>-<?php echo number_format($quote['discount_amount']); ?> 원</td>
                                </tr>
                                <?php endif; ?>
                                <tr class="total-row">
                                    <th>합계</th>
                                    <td><?php echo number_format($quote['grand_total']); ?> 원</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 비고 -->
                <?php if ($quote['notes']): ?>
                <div class="section">
                    <div class="section-header">비고</div>
                    <div class="section-body">
                        <div class="notes-box"><?php echo htmlspecialchars($quote['notes']); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 고객 메모 -->
                <?php if (!empty($quote['customer_notes'])): ?>
                <div class="section">
                    <div class="section-header">고객 메모</div>
                    <div class="section-body">
                        <div class="notes-box"><?php echo htmlspecialchars($quote['customer_notes']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 사이드바 -->
            <div class="sidebar">
                <!-- 공개 링크 -->
                <div class="section">
                    <div class="section-header">공개 링크</div>
                    <div class="section-body">
                        <div class="url-box">
                            <input type="text" id="publicUrl" value="<?php echo htmlspecialchars($publicUrl); ?>" readonly>
                            <button onclick="copyUrl()">복사</button>
                        </div>
                    </div>
                </div>

                <!-- 이메일 발송 이력 -->
                <div class="section">
                    <div class="section-header">이메일 발송 이력</div>
                    <div class="section-body" style="padding: <?php echo count($emailLogs) > 0 ? '8px 12px' : '0'; ?>;">
                        <?php if (count($emailLogs) > 0): ?>
                        <div class="email-list">
                            <?php foreach ($emailLogs as $log): ?>
                            <div class="email-item">
                                <span class="email-status"><?php echo $log['status'] === 'sent' ? '✓' : '✗'; ?></span>
                                <span class="email-addr"><?php echo htmlspecialchars($log['recipient_email']); ?></span>
                                <span class="email-date"><?php echo date('Y-m-d H:i', strtotime($log['sent_at'])); ?></span>
                                <?php if ($log['status'] !== 'sent'): ?>
                                <span class="email-error"><?php echo htmlspecialchars($log['error_message']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-text">발송 이력이 없습니다.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 관리자 도구 (하단) -->
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px;">
            <h3 style="margin-top: 0; color: #6c757d; font-size: 16px;">🔧 관리자 도구</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <span style="color: #6c757d; font-weight: 500;">현재 상태:</span>
                <span style="background: <?php echo $statusInfo['color']; ?>; color: white; padding: 5px 12px; border-radius: 4px; font-size: 14px;">
                    <?php echo $statusInfo['label']; ?>
                </span>
                <span style="color: #999;">→</span>
                <button onclick="changeStatus('draft')" class="btn" style="background: #6c757d; color: white; font-size: 13px; padding: 6px 12px;">📝 초안</button>
                <button onclick="changeStatus('sent')" class="btn" style="background: #0d6efd; color: white; font-size: 13px; padding: 6px 12px;">📧 발송됨</button>
                <button onclick="changeStatus('viewed')" class="btn" style="background: #17a2b8; color: white; font-size: 13px; padding: 6px 12px;">👀 조회됨</button>
                <button onclick="changeStatus('accepted')" class="btn" style="background: #1E4E79; color: white; font-size: 13px; padding: 6px 12px;">✅ 승인됨</button>
                <button onclick="changeStatus('rejected')" class="btn" style="background: #dc3545; color: white; font-size: 13px; padding: 6px 12px;">❌ 거절됨</button>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
                💡 <strong>draft</strong>: 수정 버튼 표시 | <strong>sent</strong>: 개정판 작성 버튼 표시 | 기타: 조회만 가능
            </p>
        </div>
    </div>

    <script>
    function copyUrl() {
        const input = document.getElementById('publicUrl');
        input.select();
        document.execCommand('copy');
        alert('링크가 복사되었습니다.');
    }

    function sendEmail() {
        const email = '<?php echo addslashes($quote['customer_email'] ?? ''); ?>';
        if (!email) {
            alert('고객 이메일이 등록되어 있지 않습니다.');
            return;
        }

        if (!confirm('견적서를 ' + email + '(으)로 발송하시겠습니까?')) {
            return;
        }

        fetch('api/send_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'quote_id=<?php echo $quote['id']; ?>&recipient_email=' + encodeURIComponent(email)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        })
        .catch(err => {
            alert('발송 중 오류가 발생했습니다.');
        });
    }

    // 견적서 → 주문 변환 함수
    function convertToOrder(quoteId) {
        const itemCount = <?php echo count($items); ?>;

        if (!confirm('이 견적서를 주문으로 변환하시겠습니까?\n\n' +
                     '• ' + itemCount + '개 품목이 주문으로 생성됩니다.\n' +
                     '• 변환 후에는 취소할 수 없습니다.')) {
            return;
        }

        // 버튼 비활성화
        const buttons = document.querySelectorAll('button');
        buttons.forEach(btn => btn.disabled = true);

        fetch('api/convert_to_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'quote_id=' + quoteId + '&confirm=1'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let message = '✅ ' + data.message + '\n\n';
                message += '생성된 주문:\n';
                if (data.orders && data.orders.length > 0) {
                    data.orders.forEach((order, index) => {
                        message += '  ' + (index + 1) + '. 주문 #' + order.no + ' - ' + order.product_name + '\n';
                    });
                }
                alert(message);
                location.reload();
            } else {
                alert('❌ 오류: ' + data.message);
                buttons.forEach(btn => btn.disabled = false);
            }
        })
        .catch(err => {
            alert('❌ 주문 변환 중 오류가 발생했습니다.');
            buttons.forEach(btn => btn.disabled = false);
        });
    }

    function changeStatus(newStatus) {
        const statusLabels = {
            'draft': '📝 초안',
            'sent': '📧 발송됨',
            'viewed': '👀 조회됨',
            'accepted': '✅ 승인됨',
            'rejected': '❌ 거절됨',
            'expired': '⏰ 만료됨'
        };

        const currentStatus = '<?php echo $quote['status']; ?>';
        if (currentStatus === newStatus) {
            alert('이미 ' + statusLabels[newStatus] + ' 상태입니다.');
            return;
        }

        if (!confirm('견적서 상태를 ' + statusLabels[newStatus] + '(으)로 변경하시겠습니까?')) {
            return;
        }

        // API 호출
        fetch('api/update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'quote_id=<?php echo $quote['id']; ?>&status=' + encodeURIComponent(newStatus)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ 상태가 ' + statusLabels[newStatus] + '(으)로 변경되었습니다.');
                location.reload();
            } else {
                alert('❌ 오류: ' + data.message);
            }
        })
        .catch(err => {
            alert('❌ 상태 변경 중 오류가 발생했습니다.');
        });
    }
    </script>
</body>
</html>
