<?php
/**
 * 배송 일괄 등록 페이지
 */
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../db.php';

requireAdminAuth();

// 배송 대기 주문 조회
$query = "SELECT o.no, o.name, o.Hendphone, o.zip1, o.zip2, o.Type, o.money_4, o.date,
                 s.courier_code, s.tracking_number, s.status as ship_status
          FROM mlangorder_printauto o
          LEFT JOIN shipping_info s ON o.no = s.order_id
          WHERE o.OrderStyle IN ('shipping_ready', 'quality_check', 'in_production', 'proof_approved')
             OR (o.ship_status = 'pending' AND o.payment_status = 'paid')
          ORDER BY o.date DESC";
$result = mysqli_query($db, $query);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

// 택배사 목록
$couriers = [
    'cj' => 'CJ대한통운',
    'hanjin' => '한진택배',
    'lotte' => '롯데택배',
    'logen' => '로젠택배',
    'post' => '우체국택배'
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>배송 일괄 등록 - 두손기획</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .header {
            background: #fff;
            padding: 16px 24px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header h1 { font-size: 20px; font-weight: 500; }
        .container { max-width: 1400px; margin: 0 auto; padding: 24px; }
        .toolbar {
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn:hover { background: #f5f5f5; }
        .btn-primary { background: #1a73e8; color: #fff; border-color: #1a73e8; }
        .btn-primary:hover { background: #1557b0; }
        .btn-success { background: #34a853; color: #fff; border-color: #34a853; }
        .btn-success:hover { background: #2d8f47; }
        select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            min-width: 150px;
        }
        .table-wrapper {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #f8f9fa;
            font-weight: 500;
            color: #5f6368;
            font-size: 13px;
        }
        tr:hover { background: #f8f9fa; }
        .checkbox-cell { width: 40px; text-align: center; }
        input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        input[type="text"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 140px;
            font-size: 13px;
        }
        input[type="text"]:focus { border-color: #1a73e8; outline: none; }
        .courier-select {
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-ready { background: #cce5ff; color: #004085; }
        .badge-shipped { background: #d4edda; color: #155724; }
        .summary {
            background: #e8f0fe;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            color: #1a73e8;
            font-size: 14px;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 16px 24px;
            background: #323232;
            color: #fff;
            border-radius: 8px;
            display: none;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        .toast.success { background: #34a853; }
        .toast.error { background: #ea4335; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 배송 일괄 등록</h1>
        <div>
            <button class="btn" onclick="location.href='admin.php'">← 관리자</button>
            <button class="btn" onclick="location.href='/admin/dashboard.php'">📊 대시보드</button>
        </div>
    </div>

    <div class="container">
        <div class="summary">
            <strong>📋 배송 대기 주문:</strong> <?php echo count($orders); ?>건
            | <strong>선택:</strong> <span id="selectedCount">0</span>건
        </div>

        <div class="toolbar">
            <button class="btn" onclick="selectAll()">✓ 전체 선택</button>
            <button class="btn" onclick="deselectAll()">✗ 선택 해제</button>
            <select id="bulkCourier" onchange="applyBulkCourier()">
                <option value="">-- 택배사 일괄 적용 --</option>
                <?php foreach ($couriers as $code => $name): ?>
                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-success" onclick="registerShipping()">🚚 선택 항목 배송 등록</button>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th class="checkbox-cell"><input type="checkbox" id="checkAll" onclick="toggleAll()"></th>
                        <th>주문번호</th>
                        <th>품목</th>
                        <th>주문자</th>
                        <th>연락처</th>
                        <th>주소</th>
                        <th>금액</th>
                        <th>택배사</th>
                        <th>운송장번호</th>
                        <th>상태</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="10" style="text-align:center; padding: 40px; color: #999;">배송 대기 주문이 없습니다.</td></tr>
                    <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr data-order-id="<?php echo $order['no']; ?>">
                        <td class="checkbox-cell">
                            <input type="checkbox" class="order-check" value="<?php echo $order['no']; ?>" onchange="updateCount()">
                        </td>
                        <td><strong>#<?php echo $order['no']; ?></strong></td>
                        <td><?php echo htmlspecialchars($order['Type']); ?></td>
                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                        <td><?php echo htmlspecialchars($order['Hendphone']); ?></td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($order['zip1'] . ' ' . $order['zip2']); ?>
                        </td>
                        <td><?php echo number_format($order['money_4']); ?>원</td>
                        <td>
                            <select class="courier-select" data-order="<?php echo $order['no']; ?>">
                                <option value="">선택</option>
                                <?php foreach ($couriers as $code => $name): ?>
                                <option value="<?php echo $code; ?>" <?php echo ($order['courier_code'] == $code) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="tracking-input" data-order="<?php echo $order['no']; ?>"
                                   value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>"
                                   placeholder="운송장번호">
                        </td>
                        <td>
                            <?php
                            $status = $order['ship_status'] ?? 'pending';
                            $statusClass = [
                                'pending' => 'badge-pending',
                                'ready' => 'badge-ready',
                                'shipped' => 'badge-shipped'
                            ][$status] ?? 'badge-pending';
                            $statusText = [
                                'pending' => '대기',
                                'ready' => '준비',
                                'shipped' => '발송'
                            ][$status] ?? '대기';
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        function updateCount() {
            const checked = document.querySelectorAll('.order-check:checked').length;
            document.getElementById('selectedCount').textContent = checked;
        }

        function selectAll() {
            document.querySelectorAll('.order-check').forEach(cb => cb.checked = true);
            document.getElementById('checkAll').checked = true;
            updateCount();
        }

        function deselectAll() {
            document.querySelectorAll('.order-check').forEach(cb => cb.checked = false);
            document.getElementById('checkAll').checked = false;
            updateCount();
        }

        function toggleAll() {
            const checkAll = document.getElementById('checkAll').checked;
            document.querySelectorAll('.order-check').forEach(cb => cb.checked = checkAll);
            updateCount();
        }

        function applyBulkCourier() {
            const courier = document.getElementById('bulkCourier').value;
            if (!courier) return;

            document.querySelectorAll('.order-check:checked').forEach(cb => {
                const orderId = cb.value;
                const select = document.querySelector(`.courier-select[data-order="${orderId}"]`);
                if (select) select.value = courier;
            });

            document.getElementById('bulkCourier').value = '';
            showToast('택배사가 일괄 적용되었습니다.');
        }

        async function registerShipping() {
            const checkedOrders = document.querySelectorAll('.order-check:checked');
            if (checkedOrders.length === 0) {
                showToast('선택된 주문이 없습니다.', 'error');
                return;
            }

            const shippingData = {};
            let valid = true;

            checkedOrders.forEach(cb => {
                const orderId = cb.value;
                const courier = document.querySelector(`.courier-select[data-order="${orderId}"]`).value;
                const tracking = document.querySelector(`.tracking-input[data-order="${orderId}"]`).value.trim();

                if (!courier || !tracking) {
                    valid = false;
                    return;
                }

                shippingData[orderId] = {
                    courier_code: courier,
                    tracking_number: tracking
                };
            });

            if (!valid) {
                showToast('택배사와 운송장번호를 모두 입력해주세요.', 'error');
                return;
            }

            try {
                const response = await fetch('/admin/api/batch_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'register_shipping',
                        order_ids: Object.keys(shippingData).map(Number),
                        shipping_data: shippingData
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.error || '처리 중 오류가 발생했습니다.', 'error');
                }
            } catch (error) {
                showToast('서버 오류가 발생했습니다.', 'error');
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type;
            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 3000);
        }
    </script>
</body>
</html>
