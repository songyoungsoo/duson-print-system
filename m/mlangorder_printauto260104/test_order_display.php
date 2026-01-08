<?php
session_start();
include "../db.php";
mysqli_set_charset($db, "utf8");

// 주문번호 90044 확인
$order_no = $_GET['order_no'] ?? 90044;

$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>주문 #{<?php echo $order_no; ?>} 데이터 분석</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 15px; }
        .section { margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .section h2 { color: #3498db; margin-top: 0; }
        .code { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; margin: 10px 0; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        .json-viewer { background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; margin: 2px; }
        .badge.success { background: #28a745; color: white; }
        .badge.error { background: #dc3545; color: white; }
        .badge.warning { background: #ffc107; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 주문 #<?php echo $order_no; ?> 데이터 분석</h1>

        <?php if (!$order): ?>
            <div class="section" style="background: #f8d7da; border-left: 5px solid #dc3545;">
                <strong>❌ 주문을 찾을 수 없습니다.</strong>
            </div>
        <?php else: ?>
            <div class="section">
                <h2>📋 기본 정보</h2>
                <table>
                    <tr><th>주문번호</th><td><?php echo $order['no']; ?></td></tr>
                    <tr><th>주문일시</th><td><?php echo $order['regdate']; ?></td></tr>
                    <tr><th>Type (제품타입)</th><td><?php echo htmlspecialchars($order['Type']); ?></td></tr>
                    <tr><th>가격 (VAT제외)</th><td><?php echo number_format($order['price']); ?>원</td></tr>
                    <tr><th>가격 (VAT포함)</th><td><?php echo number_format($order['price_vat']); ?>원</td></tr>
                </table>
            </div>

            <div class="section">
                <h2>📦 Type_1 데이터 (원본)</h2>
                <div class="code"><?php echo htmlspecialchars($order['Type_1']); ?></div>
            </div>

            <div class="section">
                <h2>🔍 Type_1 JSON 파싱</h2>
                <?php
                $json_data = json_decode($order['Type_1'], true);
                if ($json_data && is_array($json_data)):
                ?>
                    <div class="badge success">✅ JSON 파싱 성공</div>
                    <table style="margin-top: 15px;">
                        <tr><th>키</th><th>값</th></tr>
                        <?php foreach ($json_data as $key => $value): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                                <td><?php echo htmlspecialchars(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <div style="margin-top: 20px;">
                        <strong>product_type:</strong>
                        <?php if (isset($json_data['product_type'])): ?>
                            <span class="badge success"><?php echo $json_data['product_type']; ?></span>
                        <?php else: ?>
                            <span class="badge error">❌ product_type 없음</span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="badge error">❌ JSON 파싱 실패</div>
                    <p>일반 텍스트 데이터로 간주됩니다.</p>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2>✨ 추가 옵션 데이터</h2>
                <table>
                    <tr>
                        <th>옵션</th>
                        <th>활성화</th>
                        <th>타입/값</th>
                        <th>가격</th>
                    </tr>
                    <tr>
                        <td>양면테이프</td>
                        <td><span class="badge <?php echo $order['envelope_tape_enabled'] ? 'success' : 'error'; ?>">
                            <?php echo $order['envelope_tape_enabled'] ? '있음' : '없음'; ?>
                        </span></td>
                        <td><?php echo number_format($order['envelope_tape_quantity'] ?? 0); ?>개</td>
                        <td><?php echo number_format($order['envelope_tape_price'] ?? 0); ?>원</td>
                    </tr>
                    <tr>
                        <td>코팅</td>
                        <td><span class="badge <?php echo $order['coating_enabled'] ? 'success' : 'error'; ?>">
                            <?php echo $order['coating_enabled'] ? '있음' : '없음'; ?>
                        </span></td>
                        <td><?php echo $order['coating_type'] ?? '-'; ?></td>
                        <td><?php echo number_format($order['coating_price'] ?? 0); ?>원</td>
                    </tr>
                    <tr>
                        <td>접지</td>
                        <td><span class="badge <?php echo $order['folding_enabled'] ? 'success' : 'error'; ?>">
                            <?php echo $order['folding_enabled'] ? '있음' : '없음'; ?>
                        </span></td>
                        <td><?php echo $order['folding_type'] ?? '-'; ?></td>
                        <td><?php echo number_format($order['folding_price'] ?? 0); ?>원</td>
                    </tr>
                    <tr>
                        <td>오시</td>
                        <td><span class="badge <?php echo $order['creasing_enabled'] ? 'success' : 'error'; ?>">
                            <?php echo $order['creasing_enabled'] ? '있음' : '없음'; ?>
                        </span></td>
                        <td><?php echo ($order['creasing_lines'] ?? 0) . '줄'; ?></td>
                        <td><?php echo number_format($order['creasing_price'] ?? 0); ?>원</td>
                    </tr>
                    <tr style="background: #e7f3ff; font-weight: bold;">
                        <td colspan="3">추가옵션 총액</td>
                        <td><?php echo number_format($order['envelope_additional_options_total'] ?? $order['additional_options_total'] ?? 0); ?>원</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h2>🎨 OrderComplete_universal.php에서 표시될 내용</h2>
                <?php
                include "OrderComplete_universal.php";
                echo formatProductDetails($db, $order);
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
