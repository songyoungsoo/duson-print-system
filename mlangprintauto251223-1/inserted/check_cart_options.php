<?php
/**
 * 🔍 장바구니 추가옵션 진단 페이지
 * 현재 세션의 장바구니 아이템에 추가옵션이 포함되어 있는지 확인
 */

session_start();
$session_id = session_id();

include "../../db.php";
$connect = $db;
mysqli_set_charset($connect, 'utf8mb4');

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 장바구니 추가옵션 진단</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .info-box { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #2196f3; }
        .success { background: #c8e6c9; border-left-color: #4caf50; color: #1b5e20; }
        .warning { background: #fff3cd; border-left-color: #ff9800; color: #856404; }
        .error { background: #ffcdd2; border-left-color: #f44336; color: #c62828; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        tr:hover { background: #f5f5f5; }
        .highlight { background: #fff3cd !important; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #2980b9; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 장바구니 추가옵션 진단</h1>

        <div class="info-box">
            <strong>📌 현재 세션 ID:</strong> <code><?php echo htmlspecialchars($session_id); ?></code>
        </div>

        <?php
        // 현재 세션의 장바구니 아이템 조회
        $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }

        if (empty($items)) {
            echo '<div class="info-box warning">';
            echo '<strong>⚠️ 장바구니가 비어있습니다.</strong><br>';
            echo '먼저 전단지 페이지에서 상품을 장바구니에 담아주세요.';
            echo '</div>';
        } else {
            echo '<h2>🛒 장바구니 아이템 (' . count($items) . '개)</h2>';

            foreach ($items as $index => $item) {
                $has_options = ($item['additional_options_total'] > 0);

                echo '<div class="info-box ' . ($has_options ? 'success' : 'warning') . '" style="margin: 20px 0;">';
                echo '<h3>상품 #' . ($index + 1) . ' - ' . htmlspecialchars($item['product_type']) . '</h3>';

                echo '<table>';
                echo '<tr><th>필드</th><th>값</th><th>상태</th></tr>';

                // 기본 정보
                echo '<tr><td>주문번호</td><td>' . $item['no'] . '</td><td>-</td></tr>';
                echo '<tr><td>상품타입</td><td>' . htmlspecialchars($item['product_type']) . '</td><td>-</td></tr>';
                echo '<tr><td>기본가격 (VAT제외)</td><td>' . number_format($item['st_price']) . '원</td><td>-</td></tr>';
                echo '<tr><td>총가격 (VAT포함)</td><td>' . number_format($item['st_price_vat']) . '원</td><td>-</td></tr>';

                // 코팅 옵션
                echo '<tr class="highlight">';
                echo '<td>✨ 코팅 활성화</td>';
                echo '<td>' . ($item['coating_enabled'] ? 'ON' : 'OFF') . '</td>';
                echo '<td>' . ($item['coating_enabled'] ? '✅ 선택됨' : '❌ 선택안됨') . '</td>';
                echo '</tr>';

                if ($item['coating_enabled']) {
                    echo '<tr class="highlight">';
                    echo '<td>  └ 코팅 종류</td>';
                    echo '<td>' . htmlspecialchars($item['coating_type']) . '</td>';
                    echo '<td>-</td>';
                    echo '</tr>';
                    echo '<tr class="highlight">';
                    echo '<td>  └ 코팅 가격</td>';
                    echo '<td>' . number_format($item['coating_price']) . '원</td>';
                    echo '<td>-</td>';
                    echo '</tr>';
                }

                // 접지 옵션
                echo '<tr class="highlight">';
                echo '<td>📄 접지 활성화</td>';
                echo '<td>' . ($item['folding_enabled'] ? 'ON' : 'OFF') . '</td>';
                echo '<td>' . ($item['folding_enabled'] ? '✅ 선택됨' : '❌ 선택안됨') . '</td>';
                echo '</tr>';

                if ($item['folding_enabled']) {
                    echo '<tr class="highlight">';
                    echo '<td>  └ 접지 종류</td>';
                    echo '<td>' . htmlspecialchars($item['folding_type']) . '</td>';
                    echo '<td>-</td>';
                    echo '</tr>';
                    echo '<tr class="highlight">';
                    echo '<td>  └ 접지 가격</td>';
                    echo '<td>' . number_format($item['folding_price']) . '원</td>';
                    echo '<td>-</td>';
                    echo '</tr>';
                }

                // 오시 옵션
                echo '<tr class="highlight">';
                echo '<td>📏 오시 활성화</td>';
                echo '<td>' . ($item['creasing_enabled'] ? 'ON' : 'OFF') . '</td>';
                echo '<td>' . ($item['creasing_enabled'] ? '✅ 선택됨' : '❌ 선택안됨') . '</td>';
                echo '</tr>';

                if ($item['creasing_enabled']) {
                    echo '<tr class="highlight">';
                    echo '<td>  └ 오시 줄수</td>';
                    echo '<td>' . $item['creasing_lines'] . '줄</td>';
                    echo '<td>-</td>';
                    echo '</tr>';
                    echo '<tr class="highlight">';
                    echo '<td>  └ 오시 가격</td>';
                    echo '<td>' . number_format($item['creasing_price']) . '원</td>';
                    echo '<td>-</td>';
                    echo '</tr>';
                }

                // 총액
                echo '<tr class="highlight" style="background: #c8e6c9 !important; font-weight: bold; font-size: 1.1em;">';
                echo '<td>💰 추가옵션 총액</td>';
                echo '<td>' . number_format($item['additional_options_total']) . '원</td>';
                echo '<td>' . ($item['additional_options_total'] > 0 ? '✅ 정상' : '⚠️ 옵션없음') . '</td>';
                echo '</tr>';

                echo '</table>';

                // 진단 결과
                if ($has_options) {
                    echo '<div style="margin-top: 15px; padding: 10px; background: #4caf50; color: white; border-radius: 5px;">';
                    echo '<strong>✅ 이 상품은 추가옵션이 정상적으로 저장되었습니다!</strong><br>';
                    echo '장바구니와 주문완료 페이지에 추가옵션이 표시됩니다.';
                    echo '</div>';
                } else {
                    echo '<div style="margin-top: 15px; padding: 10px; background: #ff9800; color: white; border-radius: 5px;">';
                    echo '<strong>⚠️ 이 상품은 추가옵션이 선택되지 않았습니다.</strong><br>';
                    echo '전단지 페이지에서 "코팅", "접지", "오시" 체크박스를 선택하고 장바구니에 담아주세요.';
                    echo '</div>';
                }

                echo '</div>';
            }
        }
        ?>

        <h2>📖 사용 방법</h2>
        <div class="info-box">
            <ol>
                <li><strong>전단지 페이지</strong>로 이동: <a href="index.php">http://localhost/mlangprintauto/inserted/</a></li>
                <li>기본 옵션 선택 (종류, 용지, 규격, 수량, 면수)</li>
                <li><strong>추가옵션 체크박스</strong> 선택:
                    <ul>
                        <li>✅ <strong>코팅</strong>: 체크 → 코팅 종류 선택 (단면유광/양면유광 등)</li>
                        <li>✅ <strong>접지</strong>: 체크 → 접지 종류 선택 (2단/3단 등)</li>
                        <li>✅ <strong>오시</strong>: 체크 → 줄 수 선택 (1줄/2줄/3줄)</li>
                    </ul>
                </li>
                <li>실시간 가격에 <strong>추가옵션 가격</strong>이 추가되는지 확인</li>
                <li>"파일 업로드 및 장바구니 담기" 클릭</li>
                <li>이 페이지를 <strong>새로고침</strong>하여 옵션이 저장되었는지 확인</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">← 전단지 페이지로</a>
            <a href="../cart.php" class="btn">장바구니 보기 →</a>
            <a href="javascript:location.reload()" class="btn">🔄 새로고침</a>
        </div>
    </div>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($connect);
?>
