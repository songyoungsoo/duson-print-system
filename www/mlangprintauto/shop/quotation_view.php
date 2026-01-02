<?php
/**
 * 견적서 공개 링크 조회 페이지
 * URL: /mlangprintauto/shop/quotation_view.php?token=xxxxx
 *
 * 고객이 이메일로 받은 링크를 통해 견적서를 조회하고
 * 승인/거절/협의요청을 할 수 있습니다.
 */

require_once __DIR__ . '/../../db.php';

// 토큰 검증
$token = $_GET['token'] ?? '';
if (empty($token) || strlen($token) < 20) {
    http_response_code(404);
    die('<div style="text-align:center;padding:50px;font-family:sans-serif;">
        <h1>잘못된 접근입니다</h1>
        <p>유효하지 않은 링크입니다.</p>
    </div>');
}

// 견적서 조회
$stmt = mysqli_prepare($db, "SELECT * FROM quotations WHERE public_token = ?");
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($result);

if (!$quotation) {
    http_response_code(404);
    die('<div style="text-align:center;padding:50px;font-family:sans-serif;">
        <h1>견적서를 찾을 수 없습니다</h1>
        <p>삭제되었거나 존재하지 않는 견적서입니다.</p>
    </div>');
}

// 만료 확인
$is_expired = strtotime($quotation['expires_at']) < strtotime('today');
$is_responded = in_array($quotation['customer_response'], ['accepted', 'rejected']);

// 장바구니 데이터 파싱
$cart_items = json_decode($quotation['cart_items_json'], true) ?: [];
$custom_items = json_decode($quotation['custom_items_json'], true) ?: [];

// 상태 한글 변환
function getStatusKorean($status, $response) {
    if ($response === 'accepted') return ['승인됨', 'success'];
    if ($response === 'rejected') return ['거절됨', 'danger'];
    if ($response === 'negotiate') return ['협의요청', 'warning'];

    switch ($status) {
        case 'draft': return ['임시저장', 'secondary'];
        case 'sent': return ['발송완료', 'primary'];
        case 'expired': return ['만료됨', 'secondary'];
        default: return ['대기중', 'secondary'];
    }
}

// 제품명 변환
function getProductName($type) {
    $names = [
        'inserted' => '전단지',
        'leaflet' => '리플렛',
        'namecard' => '명함',
        'envelope' => '봉투',
        'sticker' => '스티커',
        'msticker' => '자석스티커',
        'cadarok' => '카다록',
        'littleprint' => '포스터',
        'ncrflambeau' => 'NCR양식',
        'merchandisebond' => '상품권'
    ];
    return $names[$type] ?? $type;
}

[$status_text, $status_color] = getStatusKorean($quotation['status'], $quotation['customer_response']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 - <?php echo htmlspecialchars($quotation['quotation_no']); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header .quote-no { font-size: 16px; opacity: 0.9; }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        .status-success { background: #27ae60; }
        .status-danger { background: #e74c3c; }
        .status-warning { background: #f39c12; }
        .status-primary { background: #3498db; }
        .status-secondary { background: #95a5a6; }

        .content { padding: 30px; }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
        }

        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        .info-box h3 {
            color: #2c3e50;
            font-size: 14px;
            margin-bottom: 15px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #7f8c8d; }
        .info-value { font-weight: 500; }

        .total-box {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
        }
        .total-box .label { font-size: 14px; opacity: 0.8; }
        .total-box .amount { font-size: 36px; font-weight: bold; margin-top: 5px; }
        .total-box .vat-note { font-size: 12px; opacity: 0.7; margin-top: 5px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-accept { background: #27ae60; color: white; }
        .btn-accept:hover { background: #219a52; }
        .btn-reject { background: #e74c3c; color: white; }
        .btn-reject:hover { background: #c0392b; }
        .btn-negotiate { background: #f39c12; color: white; }
        .btn-negotiate:hover { background: #d68910; }
        .btn-pdf { background: #3498db; color: white; }
        .btn-pdf:hover { background: #2980b9; }
        .btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
        }

        .expired-notice, .responded-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .responded-notice.accepted {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .responded-notice.rejected {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .company-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 14px;
        }
        .company-info h4 { margin-bottom: 10px; color: #2c3e50; }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }
        .modal h3 { margin-bottom: 20px; }
        .modal textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            resize: vertical;
        }
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .modal .btn { padding: 10px 25px; }
        .btn-cancel { background: #95a5a6; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>견 적 서</h1>
            <div class="quote-no"><?php echo htmlspecialchars($quotation['quotation_no']); ?></div>
            <span class="status-badge status-<?php echo $status_color; ?>"><?php echo $status_text; ?></span>
        </div>

        <div class="content">
            <?php if ($is_expired && !$is_responded): ?>
            <div class="expired-notice">
                ⚠️ 이 견적서는 <?php echo date('Y년 m월 d일', strtotime($quotation['expires_at'])); ?>에 만료되었습니다.<br>
                새로운 견적을 요청해주세요.
            </div>
            <?php endif; ?>

            <?php if ($quotation['customer_response'] === 'accepted'): ?>
            <div class="responded-notice accepted">
                ✅ <?php echo date('Y년 m월 d일 H:i', strtotime($quotation['response_date'])); ?>에 승인하셨습니다.
                <?php if ($quotation['converted_order_no']): ?>
                <br>주문번호: <strong><?php echo htmlspecialchars($quotation['converted_order_no']); ?></strong>
                <?php endif; ?>
            </div>
            <?php elseif ($quotation['customer_response'] === 'rejected'): ?>
            <div class="responded-notice rejected">
                ❌ <?php echo date('Y년 m월 d일 H:i', strtotime($quotation['response_date'])); ?>에 거절하셨습니다.
                <?php if ($quotation['response_notes']): ?>
                <br>사유: <?php echo htmlspecialchars($quotation['response_notes']); ?>
                <?php endif; ?>
            </div>
            <?php elseif ($quotation['customer_response'] === 'negotiate'): ?>
            <div class="responded-notice" style="background:#fff3cd;border-color:#ffc107;color:#856404;">
                💬 <?php echo date('Y년 m월 d일 H:i', strtotime($quotation['response_date'])); ?>에 협의를 요청하셨습니다.<br>
                담당자가 곧 연락드리겠습니다.
            </div>
            <?php endif; ?>

            <div class="total-box">
                <div class="label">합계금액 (VAT 포함)</div>
                <div class="amount"><?php echo number_format($quotation['total_price']); ?>원</div>
                <div class="vat-note">공급가 <?php echo number_format($quotation['total_supply']); ?>원 + 부가세 <?php echo number_format($quotation['total_vat']); ?>원</div>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <h3>📋 견적 정보</h3>
                    <div class="info-row">
                        <span class="info-label">견적번호</span>
                        <span class="info-value"><?php echo htmlspecialchars($quotation['quotation_no']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">견적일자</span>
                        <span class="info-value"><?php echo date('Y-m-d', strtotime($quotation['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">유효기간</span>
                        <span class="info-value"><?php echo date('Y-m-d', strtotime($quotation['expires_at'])); ?>까지</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">담당자</span>
                        <span class="info-value"><?php echo htmlspecialchars($quotation['customer_name']); ?></span>
                    </div>
                </div>

                <div class="info-box">
                    <h3>🏢 공급자 정보</h3>
                    <div class="info-row">
                        <span class="info-label">상호</span>
                        <span class="info-value">두손기획인쇄</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">대표자</span>
                        <span class="info-value">이두선</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">전화</span>
                        <span class="info-value">051-341-1830</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">팩스</span>
                        <span class="info-value">051-341-1831</span>
                    </div>
                </div>
            </div>

            <h3 style="margin-bottom:15px;">📦 상품 내역</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>품목</th>
                        <th>수량</th>
                        <th style="text-align:right;">금액</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($cart_items as $item):
                        $product_name = getProductName($item['product_type'] ?? 'unknown');
                        $quantity = $item['MY_amount'] ?? $item['quantity'] ?? 1;
                        $price = $item['st_price_vat'] ?? $item['st_price'] ?? 0;
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($product_name); ?></td>
                        <td><?php echo number_format($quantity); ?>부</td>
                        <td style="text-align:right;"><?php echo number_format($price); ?>원</td>
                    </tr>
                    <?php endforeach; ?>

                    <?php foreach ($custom_items as $item): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($item['name'] ?? '추가항목'); ?></td>
                        <td><?php echo $item['quantity'] ?? 1; ?></td>
                        <td style="text-align:right;"><?php echo number_format($item['price'] ?? 0); ?>원</td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if ($quotation['delivery_price'] > 0): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>배송비 (<?php echo htmlspecialchars($quotation['delivery_type'] ?? '택배'); ?>)</td>
                        <td>1</td>
                        <td style="text-align:right;"><?php echo number_format($quotation['delivery_price']); ?>원</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8f9fa;">
                        <td colspan="3" style="text-align:right;font-weight:bold;">합계</td>
                        <td style="text-align:right;font-weight:bold;color:#e74c3c;">
                            <?php echo number_format($quotation['total_price']); ?>원
                        </td>
                    </tr>
                </tfoot>
            </table>

            <?php if ($quotation['notes']): ?>
            <div class="info-box" style="margin-bottom:20px;">
                <h3>📝 비고</h3>
                <p style="padding:10px 0;"><?php echo nl2br(htmlspecialchars($quotation['notes'])); ?></p>
            </div>
            <?php endif; ?>

            <div class="company-info">
                <h4>💳 입금 계좌 안내</h4>
                <p><strong>농협은행 301-0185-6461-71 (예금주: 이두선)</strong></p>
                <p style="margin-top:10px;color:#7f8c8d;">
                    • 견적서 유효기간 내 입금해주시면 자동 주문 처리됩니다.<br>
                    • 문의사항: 051-341-1830
                </p>
            </div>

            <div class="action-buttons">
                <button class="btn btn-pdf" onclick="window.print()">🖨️ 인쇄/PDF</button>

                <?php if (!$is_expired && !$is_responded): ?>
                <button class="btn btn-accept" onclick="showModal('accept')">✅ 승인</button>
                <button class="btn btn-negotiate" onclick="showModal('negotiate')">💬 협의요청</button>
                <button class="btn btn-reject" onclick="showModal('reject')">❌ 거절</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 응답 모달 -->
    <div class="modal" id="responseModal">
        <div class="modal-content">
            <h3 id="modalTitle">견적서 응답</h3>
            <p id="modalDesc" style="margin-bottom:15px;color:#666;"></p>
            <textarea id="responseNotes" placeholder="의견이나 요청사항을 입력해주세요 (선택사항)"></textarea>
            <div class="modal-buttons">
                <button class="btn btn-cancel" onclick="closeModal()">취소</button>
                <button class="btn" id="modalSubmit" onclick="submitResponse()">확인</button>
            </div>
        </div>
    </div>

    <script>
        let currentAction = '';
        const token = '<?php echo htmlspecialchars($token); ?>';

        function showModal(action) {
            currentAction = action;
            const modal = document.getElementById('responseModal');
            const title = document.getElementById('modalTitle');
            const desc = document.getElementById('modalDesc');
            const submit = document.getElementById('modalSubmit');

            if (action === 'accept') {
                title.textContent = '✅ 견적 승인';
                desc.textContent = '이 견적서를 승인하시겠습니까? 승인 후 담당자가 주문 처리를 진행합니다.';
                submit.className = 'btn btn-accept';
                submit.textContent = '승인';
            } else if (action === 'reject') {
                title.textContent = '❌ 견적 거절';
                desc.textContent = '이 견적서를 거절하시겠습니까? 거절 사유를 입력해주시면 더 나은 견적을 제안드릴 수 있습니다.';
                submit.className = 'btn btn-reject';
                submit.textContent = '거절';
            } else if (action === 'negotiate') {
                title.textContent = '💬 협의 요청';
                desc.textContent = '가격, 수량, 납기 등 협의가 필요하신 내용을 입력해주세요.';
                submit.className = 'btn btn-negotiate';
                submit.textContent = '협의요청';
            }

            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('responseModal').classList.remove('active');
            document.getElementById('responseNotes').value = '';
        }

        async function submitResponse() {
            const notes = document.getElementById('responseNotes').value;
            const submitBtn = document.getElementById('modalSubmit');

            submitBtn.disabled = true;
            submitBtn.textContent = '처리중...';

            try {
                const response = await fetch('quotation_respond.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        token: token,
                        action: currentAction,
                        notes: notes
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('오류: ' + result.message);
                    submitBtn.disabled = false;
                }
            } catch (error) {
                alert('요청 처리 중 오류가 발생했습니다.');
                submitBtn.disabled = false;
            }
        }

        // ESC 키로 모달 닫기
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>

    <style media="print">
        .action-buttons, .modal { display: none !important; }
        body { background: white; padding: 0; }
        .container { box-shadow: none; }
    </style>
</body>
</html>
