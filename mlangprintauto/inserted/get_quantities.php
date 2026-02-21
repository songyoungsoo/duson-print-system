<?php
header('Content-Type: application/json; charset=UTF-8');

// 데이터베이스 연결 - db.php 사용
include "../../db.php";
require_once __DIR__ . '/../../includes/QuantityFormatter.php';  // ✅ paper_standard_master 연동

$connect = $db;
if (!$connect) {
    echo json_encode(['error' => '데이터베이스 연결 실패']);
    exit;
}

mysqli_set_charset($connect, "utf8");

// GET 파라미터 받기
$MY_type = $_GET['MY_type'] ?? '';
$PN_type = $_GET['PN_type'] ?? '';
$MY_Fsd = $_GET['MY_Fsd'] ?? '';
$POtype = $_GET['POtype'] ?? '1'; // 단면/양면 파라미터 추가 (기본값: 단면)

$TABLE = "mlangprintauto_inserted";

// ✅ Step 1: 규격명(PN_type)에서 paper_standard_master용 스펙 추출
$spec_name = null;
$sheets_per_ream = null;

if (!empty($PN_type)) {
    // transactioncate에서 규격명 가져오기
    $spec_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ?";
    $spec_stmt = mysqli_prepare($connect, $spec_query);
    if ($spec_stmt) {
        mysqli_stmt_bind_param($spec_stmt, "s", $PN_type);
        mysqli_stmt_execute($spec_stmt);
        $spec_result = mysqli_stmt_get_result($spec_stmt);
        if ($spec_row = mysqli_fetch_assoc($spec_result)) {
            // 규격명에서 A4, B4 등 추출 (예: "A4 (210x297)" → "A4")
            $spec_name = QuantityFormatter::extractSpecName($spec_row['title']);

            if ($spec_name) {
                // paper_standard_master에서 1연당 매수 조회
                $sheets_per_ream = QuantityFormatter::getSheetsPerReam($connect, $spec_name);
                error_log("get_quantities: spec_name=$spec_name, sheets_per_ream=$sheets_per_ream (from paper_standard_master)");
            }
        }
        mysqli_stmt_close($spec_stmt);
    }
}

// ✅ Step 2: 가격 DB에서 수량 옵션 가져오기
$query = "SELECT DISTINCT quantity, quantityTwo
          FROM $TABLE
          WHERE style='$MY_type'
          AND Section='$PN_type'
          AND TreeSelect='$MY_Fsd'
          AND POtype='$POtype'
          AND quantity IS NOT NULL
          ORDER BY CAST(quantity AS DECIMAL(10,1)) ASC";

$result = mysqli_query($connect, $query);
$quantities = [];

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $reams = floatval($row['quantity']);
        $db_sheets = intval($row['quantityTwo'] ?? 0);

        // ✅ Step 3: 매수 결정 - DB값 우선, 없으면 paper_standard_master로 자동 계산
        if ($db_sheets > 0) {
            $final_sheets = $db_sheets;
        } elseif ($sheets_per_ream && $sheets_per_ream > 0) {
            // paper_standard_master 기반 자동 계산
            $final_sheets = intval($reams * $sheets_per_ream);
            error_log("get_quantities: Auto-calculated sheets for {$reams}연: $final_sheets매 (using $spec_name)");
        } else {
            // 폴백: 기본값 (A4 기준 4000매/연)
            $final_sheets = intval($reams * 4000);
            error_log("get_quantities: Fallback sheets for {$reams}연: $final_sheets매 (default A4)");
        }

        $quantities[] = [
            'value' => $row['quantity'],
            'text' => rtrim(rtrim(sprintf('%.1f', $reams), '0'), '.') . '연 (' . number_format($final_sheets) . '매)',
            'sheets' => $final_sheets,  // ✅ 추가: 프론트엔드에서 활용 가능
            'spec' => $spec_name ?? 'unknown'  // ✅ 추가: 규격 정보
        ];
    }
}

mysqli_close($connect);

// 0.5연을 맨 아래로 이동
$half = [];
$rest = [];
foreach ($quantities as $q) {
    if (floatval($q['value']) == 0.5) {
        $half[] = $q;
    } else {
        $rest[] = $q;
    }
}
$quantities = array_merge($rest, $half);

echo json_encode($quantities);
?>