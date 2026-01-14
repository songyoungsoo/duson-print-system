<?php
/**
 * 전단지 수량 데이터 수정 스크립트
 * - quantityTwo가 없는 구형 전단지 주문에 매수 정보 추가
 * - formatted_display의 "수량: 1매" 형식을 "수량: 0.5연 (2,000매)"로 수정
 *
 * 사용법: 브라우저에서 직접 접근하거나 CLI에서 실행
 */

// 관리자 인증 체크 (보안)
session_start();
require_once __DIR__ . '/../db.php';

/**
 * 전단지 매수 DB 조회 (SSOT 준수 - 계산 금지)
 */
function lookupInsertedSheets($db, $reams, $myType = '', $pnType = '', $myFsd = '', $poType = '') {
    // 모든 조건이 있으면 정확한 조회
    if (!empty($myType) && !empty($pnType) && !empty($myFsd) && !empty($poType)) {
        $stmt = mysqli_prepare($db, "SELECT quantityTwo FROM mlangprintauto_inserted WHERE style = ? AND Section = ? AND quantity = ? AND TreeSelect = ? AND POtype = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssdss", $myType, $pnType, $reams, $myFsd, $poType);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            if (!empty($row['quantityTwo'])) {
                return intval($row['quantityTwo']);
            }
        }
    }

    // 조건이 부족하면 수량만으로 대표값 조회
    $stmt = mysqli_prepare($db, "SELECT quantityTwo, COUNT(*) as cnt FROM mlangprintauto_inserted WHERE quantity = ? AND quantityTwo > 0 GROUP BY quantityTwo ORDER BY cnt DESC LIMIT 1");
    if (!$stmt) return 0;
    mysqli_stmt_bind_param($stmt, "d", $reams);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return intval($row['quantityTwo'] ?? 0);
}

// 관리자만 실행 가능하도록 체크 (또는 CLI)
if (php_sapi_name() !== 'cli') {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // 간단한 인증 - URL 파라미터로 키 확인
        if (!isset($_GET['key']) || $_GET['key'] !== 'fix_qty_2024') {
            die('접근 권한이 없습니다. ?key=fix_qty_2024 파라미터를 추가하세요.');
        }
    }
}

echo "<pre>\n";
echo "=== 전단지 수량 데이터 수정 스크립트 ===\n\n";

// 1단계: quantityTwo 추가
echo "1단계: quantityTwo 필드 추가\n";
echo str_repeat("-", 50) . "\n";

$query = "SELECT no, Type_1 FROM mlangorder_printauto
          WHERE (Type LIKE '%전단지%' OR Type LIKE '%inserted%')
          AND Type_1 IS NOT NULL";
$result = mysqli_query($db, $query);

$need_update = [];
$already_ok = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $type1 = json_decode($row['Type_1'], true);
    if (!$type1) continue;

    // 🔧 quantityTwo가 0보다 커야 유효함 (0은 잘못된 값)
    $has_qty2 = isset($type1['quantityTwo']) && $type1['quantityTwo'] !== null && $type1['quantityTwo'] !== '' && intval($type1['quantityTwo']) > 0;

    // MY_amount 찾기 (두 가지 구조 지원)
    $my_amount = null;
    $mesu = null;

    // 1. 최상위 레벨에서 찾기
    if (isset($type1['MY_amount'])) {
        $my_amount = floatval($type1['MY_amount']);
    }

    // 2. calculator_data에서 찾기 (견적서 변환 주문)
    if ($my_amount === null && isset($type1['calculator_data']['MY_amount'])) {
        $my_amount = floatval($type1['calculator_data']['MY_amount']);
        $mesu = isset($type1['calculator_data']['mesu']) ? intval($type1['calculator_data']['mesu']) : null;
    }

    if ($my_amount !== null && !$has_qty2) {
        $qty2 = $mesu; // calculator_data에서 mesu가 있으면 사용
        if ($qty2 === null) {
            if ($my_amount > 0 && $my_amount <= 10) {
                // 연수 → 매수 DB 조회 (계산 금지 - SSOT 준수)
                $qty2 = lookupInsertedSheets(
                    $db,
                    $my_amount,
                    $type1['MY_type'] ?? $type1['calculator_data']['MY_type'] ?? '',
                    $type1['PN_type'] ?? $type1['calculator_data']['PN_type'] ?? '',
                    $type1['MY_Fsd'] ?? $type1['calculator_data']['MY_Fsd'] ?? '',
                    $type1['POtype'] ?? $type1['calculator_data']['POtype'] ?? ''
                );
            } else if ($my_amount > 1000) {
                // 이미 매수로 보임
                $qty2 = intval($my_amount);
            }
        }

        if ($qty2 !== null && $qty2 > 0) {
            $need_update[] = [
                'no' => $row['no'],
                'type1' => $type1,
                'quantityTwo' => $qty2,
                'my_amount' => $my_amount
            ];
        }
    } else {
        $already_ok++;
    }
}

echo "- 이미 정상: $already_ok 개\n";
echo "- 수정 필요: " . count($need_update) . " 개\n";

// quantityTwo 업데이트 실행
$updated_qty = 0;
foreach ($need_update as $item) {
    $type1 = $item['type1'];
    $type1['quantityTwo'] = $item['quantityTwo'];

    // MY_amount가 최상위에 없으면 추가 (견적서 변환 주문용)
    if (!isset($type1['MY_amount']) && isset($item['my_amount'])) {
        $type1['MY_amount'] = strval($item['my_amount']);
    }

    $new_type1 = json_encode($type1, JSON_UNESCAPED_UNICODE);

    $update_query = "UPDATE mlangorder_printauto SET Type_1 = ? WHERE no = ?";
    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, 'si', $new_type1, $item['no']);

    if (mysqli_stmt_execute($stmt)) {
        $updated_qty++;
    }
    mysqli_stmt_close($stmt);
}

echo "- 업데이트 완료: $updated_qty 개\n\n";

// 2단계: formatted_display 수정
echo "2단계: formatted_display 수정\n";
echo str_repeat("-", 50) . "\n";

$query2 = "SELECT no, Type_1 FROM mlangorder_printauto
           WHERE (Type LIKE '%전단지%' OR Type LIKE '%inserted%')
           AND Type_1 IS NOT NULL";
$result2 = mysqli_query($db, $query2);

$updated_fmt = 0;
$skipped_fmt = 0;

while ($row = mysqli_fetch_assoc($result2)) {
    $type1 = json_decode($row['Type_1'], true);
    if (!$type1) continue;

    if (!isset($type1['formatted_display'])) {
        $skipped_fmt++;
        continue;
    }

    // MY_amount와 quantityTwo 찾기 (두 가지 구조 지원)
    $my_amount = null;
    $qty2 = null;

    if (isset($type1['MY_amount'])) {
        $my_amount = floatval($type1['MY_amount']);
    } elseif (isset($type1['calculator_data']['MY_amount'])) {
        $my_amount = floatval($type1['calculator_data']['MY_amount']);
    }

    if (isset($type1['quantityTwo'])) {
        $qty2 = intval($type1['quantityTwo']);
    } elseif (isset($type1['calculator_data']['mesu'])) {
        $qty2 = intval($type1['calculator_data']['mesu']);
    }

    if ($my_amount === null || $qty2 === null) {
        $skipped_fmt++;
        continue;
    }

    $formatted = $type1['formatted_display'];

    if ($my_amount <= 10 && $qty2 > 0) {
        // 올바른 표시 형식: X연 (Y,YYY매)
        $correct_qty = $my_amount . '연 (' . number_format($qty2) . '매)';

        // 여러 패턴 수정 (수량: 1매, 수량: 1연, 수량: 2000 등)
        $old_patterns = [
            '/수량:\s*[0-9,]+매/',           // 수량: 1매, 수량: 1,000매
            '/수량:\s*[0-9,.]+연/',          // 수량: 1연, 수량: 0.5연 (🔧 추가)
            '/수량:\s*[0-9,.]+\s*$/',        // 줄 끝의 숫자만 있는 경우
        ];

        $new_formatted = $formatted;
        $changed = false;

        foreach ($old_patterns as $pattern) {
            $temp = preg_replace($pattern, '수량: ' . $correct_qty, $new_formatted);
            if ($temp !== $new_formatted) {
                $new_formatted = $temp;
                $changed = true;
            }
        }

        // 수량 정보가 아예 없으면 추가
        if (!$changed && strpos($formatted, '수량:') === false) {
            $new_formatted = $formatted . "\n수량: " . $correct_qty;
            $changed = true;
        }

        if ($changed) {
            $type1['formatted_display'] = $new_formatted;
            $new_type1_json = json_encode($type1, JSON_UNESCAPED_UNICODE);

            $update_query = "UPDATE mlangorder_printauto SET Type_1 = ? WHERE no = ?";
            $stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($stmt, 'si', $new_type1_json, $row['no']);

            if (mysqli_stmt_execute($stmt)) {
                $updated_fmt++;
            }
            mysqli_stmt_close($stmt);
        } else {
            $skipped_fmt++;
        }
    } else {
        $skipped_fmt++;
    }
}

echo "- 수정됨: $updated_fmt 개\n";
echo "- 건너뜀: $skipped_fmt 개\n\n";

// 결과 확인
echo "=== 수정 결과 확인 ===\n";
echo str_repeat("-", 50) . "\n";

$check_query = "SELECT no,
                       JSON_EXTRACT(Type_1, '$.MY_amount') as MY_amount,
                       JSON_EXTRACT(Type_1, '$.quantityTwo') as quantityTwo,
                       JSON_EXTRACT(Type_1, '$.formatted_display') as formatted_display
                FROM mlangorder_printauto
                WHERE (Type LIKE '%전단지%' OR Type LIKE '%inserted%')
                ORDER BY no DESC
                LIMIT 5";
$check_result = mysqli_query($db, $check_query);

printf("%-10s %-12s %-12s %s\n", '주문번호', 'MY_amount', 'quantityTwo', '표시');
echo str_repeat("-", 70) . "\n";

while ($row = mysqli_fetch_assoc($check_result)) {
    $my_amount = floatval(trim($row['MY_amount'] ?? '0', '"'));
    $qty2 = intval(trim($row['quantityTwo'] ?? '0', '"'));
    $formatted = substr(trim($row['formatted_display'] ?? '', '"'), 0, 40);

    // 수량 부분만 추출
    if (preg_match('/수량:[^|]+/', $formatted, $matches)) {
        $qty_display = $matches[0];
    } else {
        $qty_display = '(수량 없음)';
    }

    printf("%-10s %-12s %-12s %s\n",
           $row['no'],
           $row['MY_amount'] ?? 'NULL',
           $row['quantityTwo'] ?? 'NULL',
           $qty_display);
}

echo "\n✅ 수정 완료!\n";
echo "</pre>";
?>
