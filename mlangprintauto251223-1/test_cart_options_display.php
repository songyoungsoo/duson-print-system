<?php
session_start();
include "../db.php";
include "shop_temp_helper.php";

mysqli_set_charset($db, "utf8");

$session_id = session_id();

// 장바구니 아이템 가져오기
$query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$cart_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cart_items[] = $row;
}

mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 장바구니 옵션 표시 테스트</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #2c3e50; text-align: center; border-bottom: 3px solid #3498db; padding-bottom: 15px; }
        .test-section { background: white; margin: 20px 0; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .product-card { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 5px solid #3498db; }
        .product-card.envelope { border-left-color: #e74c3c; }
        .product-card.namecard { border-left-color: #f39c12; }
        .product-title { font-size: 20px; font-weight: bold; margin-bottom: 15px; color: #2c3e50; }
        .detail-row { padding: 8px 0; border-bottom: 1px solid #dee2e6; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: bold; color: #495057; display: inline-block; width: 120px; }
        .detail-value { color: #212529; }
        .option-box { background: #e7f3ff; padding: 12px; border-radius: 5px; margin-top: 15px; border-left: 4px solid #0066cc; }
        .option-box strong { color: #0066cc; }
        .raw-data { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; margin-top: 15px; font-family: 'Courier New', monospace; font-size: 12px; overflow-x: auto; }
        .status { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .status.success { background: #d4edda; border-left: 5px solid #28a745; color: #155724; }
        .status.warning { background: #fff3cd; border-left: 5px solid #ffc107; color: #856404; }
        .status.error { background: #f8d7da; border-left: 5px solid #dc3545; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .badge.yes { background: #28a745; color: white; }
        .badge.no { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 장바구니 옵션 표시 통일 테스트</h1>

        <div class="status success">
            <strong>✅ 통일 완료:</strong> 봉투와 명함의 옵션 표시 방식이 동일한 형식으로 통일되었습니다.<br>
            - 명함: <code>premium_options</code> JSON → <code>additional_options_summary</code> 통일<br>
            - 봉투: 개별 컬럼 → <code>additional_options_summary</code> 통일<br>
            - 장바구니: 단일 옵션 박스로 표시
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="status warning">
                <strong>⚠️ 장바구니가 비어있습니다.</strong><br>
                테스트를 위해 봉투 또는 명함을 장바구니에 추가하세요.
            </div>
        <?php else: ?>
            <div class="status success">
                <strong>📊 총 <?php echo count($cart_items); ?>개 상품</strong>
            </div>

            <div class="test-section">
                <h2>🎨 포맷된 표시 (사용자에게 보이는 형식)</h2>
                <?php foreach ($cart_items as $item): ?>
                    <?php $formatted = formatCartItemForDisplay($db, $item); ?>
                    <div class="product-card <?php echo $item['product_type']; ?>">
                        <div class="product-title">
                            <?php echo htmlspecialchars($formatted['name']); ?>
                            <span style="float: right; font-size: 14px; color: #6c757d;">
                                상품번호: <?php echo $item['no']; ?>
                            </span>
                        </div>

                        <?php foreach ($formatted['details'] as $label => $value): ?>
                            <div class="detail-row">
                                <span class="detail-label"><?php echo htmlspecialchars($label); ?>:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($value); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!empty($formatted['additional_options_summary'])): ?>
                            <div class="option-box">
                                <strong>✨ 추가옵션:</strong>
                                <span><?php echo htmlspecialchars($formatted['additional_options_summary']); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="raw-data">
                            <strong>원본 데이터 (디버깅용):</strong><br>
                            product_type: <?php echo $item['product_type']; ?><br>
                            <?php if ($item['product_type'] === 'envelope'): ?>
                                envelope_tape_enabled: <?php echo $item['envelope_tape_enabled'] ?? '0'; ?><br>
                                envelope_tape_price: <?php echo $item['envelope_tape_price'] ?? '0'; ?>원<br>
                                envelope_additional_options_total: <?php echo $item['envelope_additional_options_total'] ?? '0'; ?>원<br>
                                coating_enabled: <?php echo $item['coating_enabled'] ?? '0'; ?><br>
                                coating_price: <?php echo $item['coating_price'] ?? '0'; ?>원<br>
                                folding_enabled: <?php echo $item['folding_enabled'] ?? '0'; ?><br>
                                creasing_enabled: <?php echo $item['creasing_enabled'] ?? '0'; ?><br>
                            <?php elseif ($item['product_type'] === 'namecard'): ?>
                                premium_options: <?php echo substr($item['premium_options'] ?? 'null', 0, 200); ?><br>
                                premium_options_total: <?php echo $item['premium_options_total'] ?? '0'; ?>원<br>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="test-section">
                <h2>📋 옵션 컬럼 비교표</h2>
                <table>
                    <thead>
                        <tr>
                            <th>상품번호</th>
                            <th>상품타입</th>
                            <th>옵션 데이터 존재</th>
                            <th>표시 통일 여부</th>
                            <th>옵션 총액</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <?php
                            $formatted = formatCartItemForDisplay($db, $item);
                            $has_options = !empty($formatted['additional_options_summary']);

                            $option_total = 0;
                            if ($item['product_type'] === 'envelope') {
                                $option_total = $item['envelope_additional_options_total'] ?? $item['additional_options_total'] ?? 0;
                            } elseif ($item['product_type'] === 'namecard') {
                                $option_total = $item['premium_options_total'] ?? 0;
                            }
                            ?>
                            <tr>
                                <td><?php echo $item['no']; ?></td>
                                <td><?php echo $item['product_type']; ?></td>
                                <td>
                                    <?php if ($option_total > 0): ?>
                                        <span class="badge yes">있음</span>
                                    <?php else: ?>
                                        <span class="badge no">없음</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($has_options): ?>
                                        <span class="badge yes">✅ 통일됨</span>
                                    <?php else: ?>
                                        <span class="badge no">옵션없음</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($option_total); ?>원</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="test-section">
            <h2>🔍 통일 효과</h2>
            <div class="detail-row">
                <span class="detail-label">이전 방식:</span>
                <span class="detail-value">
                    봉투 = 개별 컬럼 / 명함 = JSON / 장바구니 = 2가지 박스
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">현재 방식:</span>
                <span class="detail-value">
                    <strong>모두 additional_options_summary로 통일</strong> → 단일 옵션 박스
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">장점:</span>
                <span class="detail-value">
                    ✅ 일관된 사용자 경험 &nbsp;|&nbsp; ✅ 코드 유지보수 용이 &nbsp;|&nbsp; ✅ 확장 가능
                </span>
            </div>
        </div>

        <div class="test-section" style="text-align: center;">
            <a href="cart.php" style="display: inline-block; background: #3498db; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                🛒 실제 장바구니 보기
            </a>
            <a href="../mlangprintauto/envelope/index.php" style="display: inline-block; background: #e74c3c; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-left: 10px;">
                📨 봉투 주문하기
            </a>
            <a href="../mlangprintauto/namecard/index.php" style="display: inline-block; background: #f39c12; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-left: 10px;">
                📇 명함 주문하기
            </a>
        </div>
    </div>
</body>
</html>
