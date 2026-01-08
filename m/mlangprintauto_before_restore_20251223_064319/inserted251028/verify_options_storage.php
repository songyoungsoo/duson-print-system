<?php
session_start();
include "../../db.php";
mysqli_set_charset($db, "utf8");

$session_id = session_id();

// 최근 장바구니 아이템 확인
$query = "SELECT * FROM shop_temp WHERE session_id = ? AND product_type IN ('inserted', 'leaflet') ORDER BY no DESC LIMIT 5";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ 전단지 옵션 저장 검증</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #2c3e50; text-align: center; border-bottom: 3px solid #3498db; padding-bottom: 15px; }
        .status { padding: 20px; margin: 20px 0; border-radius: 10px; font-size: 16px; }
        .status.success { background: #d4edda; border-left: 5px solid #28a745; color: #155724; }
        .status.warning { background: #fff3cd; border-left: 5px solid #ffc107; color: #856404; }
        .status.error { background: #f8d7da; border-left: 5px solid #dc3545; color: #721c24; }
        .status.info { background: #d1ecf1; border-left: 5px solid #17a2b8; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; font-weight: bold; position: sticky; top: 0; }
        tr:hover { background: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; margin: 2px; }
        .badge.yes { background: #28a745; color: white; }
        .badge.no { background: #6c757d; color: white; }
        .code { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; margin: 15px 0; overflow-x: auto; }
        .instruction { background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 5px solid #0066cc; }
        .instruction h3 { color: #0066cc; margin-top: 0; }
        .button { display: inline-block; background: #3498db; color: white; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: bold; margin: 5px; }
        .button:hover { background: #2980b9; }
        .button.danger { background: #e74c3c; }
        .button.success { background: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ 전단지 추가옵션 저장 검증 시스템</h1>

        <div class="status info">
            <strong>🎯 검증 목적:</strong> shop_temp 테이블에 전단지(inserted/leaflet)의 추가 옵션 필드가 제대로 저장되는지 확인<br>
            <strong>📋 검증 항목:</strong>
            <ul style="margin: 10px 0 0 0;">
                <li><strong>coating_enabled, coating_type, coating_price</strong> - 코팅 옵션</li>
                <li><strong>folding_enabled, folding_type, folding_price</strong> - 접지 옵션</li>
                <li><strong>creasing_enabled, creasing_lines, creasing_price</strong> - 오시 옵션</li>
                <li><strong>additional_options_total</strong> - 총 옵션 가격</li>
            </ul>
        </div>

        <?php if (empty($items)): ?>
            <div class="status warning">
                <strong>⚠️ 현재 세션에 전단지 장바구니 데이터가 없습니다.</strong><br>
                아래 테스트 순서를 따라 진행하세요.
            </div>

            <div class="instruction">
                <h3>📝 테스트 순서</h3>
                <ol>
                    <li><strong>전단지 주문 페이지로 이동</strong>
                        <a href="index.php" class="button" target="_blank">📄 전단지 주문하기</a>
                    </li>
                    <li><strong>옵션을 선택하세요:</strong>
                        <ul>
                            <li>✅ 코팅 체크박스 클릭 → 코팅 타입 선택 (예: 단면유광코팅)</li>
                            <li>✅ 접지 체크박스 클릭 → 접지 타입 선택 (예: 2단접지)</li>
                            <li>✅ 오시 체크박스 클릭 → 오시 줄 수 선택 (예: 1줄)</li>
                        </ul>
                    </li>
                    <li><strong>파일 업로드 및 주문하기 버튼 클릭</strong></li>
                    <li><strong>이 페이지를 새로고침하여 결과 확인</strong>
                        <a href="" class="button success">🔄 새로고침</a>
                    </li>
                </ol>
            </div>
        <?php else: ?>
            <div class="status success">
                <strong>✅ 전단지 장바구니 데이터 발견!</strong><br>
                총 <?php echo count($items); ?>개의 전단지 아이템이 있습니다.
            </div>

            <h2 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;">📊 옵션 필드 저장 상태</h2>

            <table>
                <thead>
                    <tr>
                        <th>상품번호</th>
                        <th>제품타입</th>
                        <th>수량</th>
                        <th>코팅</th>
                        <th>접지</th>
                        <th>오시</th>
                        <th>옵션총액</th>
                        <th>저장상태</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $has_coating = !empty($item['coating_enabled']) && $item['coating_enabled'] == 1;
                        $has_folding = !empty($item['folding_enabled']) && $item['folding_enabled'] == 1;
                        $has_creasing = !empty($item['creasing_enabled']) && $item['creasing_enabled'] == 1;
                        $has_options = $has_coating || $has_folding || $has_creasing;
                        ?>
                        <tr>
                            <td><?php echo $item['no']; ?></td>
                            <td><?php echo $item['product_type']; ?></td>
                            <td><?php echo $item['MY_amount']; ?></td>
                            <td>
                                <?php if ($has_coating): ?>
                                    <span class="badge yes">있음</span><br>
                                    <?php echo $item['coating_type']; ?><br>
                                    <?php echo number_format($item['coating_price']); ?>원
                                <?php else: ?>
                                    <span class="badge no">없음</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($has_folding): ?>
                                    <span class="badge yes">있음</span><br>
                                    <?php echo $item['folding_type']; ?><br>
                                    <?php echo number_format($item['folding_price']); ?>원
                                <?php else: ?>
                                    <span class="badge no">없음</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($has_creasing): ?>
                                    <span class="badge yes">있음</span><br>
                                    <?php echo $item['creasing_lines']; ?>줄<br>
                                    <?php echo number_format($item['creasing_price']); ?>원
                                <?php else: ?>
                                    <span class="badge no">없음</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($item['additional_options_total']); ?>원</td>
                            <td>
                                <?php if ($has_options): ?>
                                    <span class="badge yes">✅ 정상</span>
                                <?php else: ?>
                                    <span class="badge no">옵션없음</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;">🔍 상세 데이터 (최신 1개)</h2>

            <?php $latest = $items[0]; ?>
            <div class="code">
<strong>상품번호:</strong> <?php echo $latest['no']; ?>
<strong>세션ID:</strong> <?php echo $latest['session_id']; ?>
<strong>제품타입:</strong> <?php echo $latest['product_type']; ?>

<strong style="color: #4CAF50;">코팅 옵션:</strong>
  coating_enabled: <?php echo $latest['coating_enabled'] ?? '0'; ?>
  coating_type: <?php echo $latest['coating_type'] ?? 'NULL'; ?>
  coating_price: <?php echo number_format($latest['coating_price'] ?? 0); ?>원

<strong style="color: #2196F3;">접지 옵션:</strong>
  folding_enabled: <?php echo $latest['folding_enabled'] ?? '0'; ?>
  folding_type: <?php echo $latest['folding_type'] ?? 'NULL'; ?>
  folding_price: <?php echo number_format($latest['folding_price'] ?? 0); ?>원

<strong style="color: #FF9800;">오시 옵션:</strong>
  creasing_enabled: <?php echo $latest['creasing_enabled'] ?? '0'; ?>
  creasing_lines: <?php echo $latest['creasing_lines'] ?? '0'; ?>
  creasing_price: <?php echo number_format($latest['creasing_price'] ?? 0); ?>원

<strong style="color: #E91E63;">총액:</strong>
  additional_options_total: <?php echo number_format($latest['additional_options_total'] ?? 0); ?>원
            </div>

            <?php
            // 검증 결과 분석
            $coating_ok = !empty($latest['coating_enabled']) && !empty($latest['coating_type']) && !empty($latest['coating_price']);
            $folding_ok = !empty($latest['folding_enabled']) && !empty($latest['folding_type']) && !empty($latest['folding_price']);
            $creasing_ok = !empty($latest['creasing_enabled']) && !empty($latest['creasing_lines']) && !empty($latest['creasing_price']);
            $total_ok = !empty($latest['additional_options_total']) && $latest['additional_options_total'] > 0;

            $all_ok = ($coating_ok || $folding_ok || $creasing_ok) && $total_ok;
            ?>

            <?php if ($all_ok): ?>
                <div class="status success">
                    <strong>🎉 검증 성공!</strong><br>
                    전단지 추가 옵션 필드들이 shop_temp 테이블에 정상적으로 저장되고 있습니다.<br><br>
                    <strong>저장된 옵션:</strong>
                    <?php if ($coating_ok): ?>✅ 코팅<?php endif; ?>
                    <?php if ($folding_ok): ?>✅ 접지<?php endif; ?>
                    <?php if ($creasing_ok): ?>✅ 오시<?php endif; ?>
                </div>
            <?php elseif ($total_ok): ?>
                <div class="status warning">
                    <strong>⚠️ 부분 성공</strong><br>
                    옵션 총액은 저장되었지만, 일부 옵션 세부 정보가 누락되었습니다.<br>
                    체크박스를 선택했는지 확인하세요.
                </div>
            <?php else: ?>
                <div class="status error">
                    <strong>❌ 옵션 데이터 없음</strong><br>
                    추가 옵션을 선택하지 않았거나, JavaScript에서 데이터 전송이 실패했을 수 있습니다.<br><br>
                    <strong>해결 방법:</strong>
                    <ol>
                        <li>전단지 주문 페이지로 이동</li>
                        <li>코팅/접지/오시 체크박스를 <strong>반드시 클릭</strong></li>
                        <li>각 옵션의 세부 항목 선택</li>
                        <li>장바구니에 추가</li>
                    </ol>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin: 30px 0;">
                <a href="index.php" class="button">📄 전단지 주문하기</a>
                <a href="../cart.php" class="button success">🛒 장바구니 보기</a>
                <a href="" class="button">🔄 새로고침</a>
            </div>
        <?php endif; ?>

        <div class="status info">
            <strong>💡 참고사항:</strong><br>
            - shop_temp 테이블의 옵션 필드는 자동으로 생성됩니다 (add_to_basket.php에서 ALTER TABLE 자동 실행)<br>
            - 봉투, 명함, 전단지 모두 동일한 필드명을 사용하여 통일되었습니다<br>
            - 장바구니 표시는 shop_temp_helper.php의 formatCartItemForDisplay() 함수가 처리합니다
        </div>
    </div>
</body>
</html>
