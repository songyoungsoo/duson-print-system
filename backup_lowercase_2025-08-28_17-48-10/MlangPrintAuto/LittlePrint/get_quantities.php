<?php
// 디버깅을 위한 로깅
error_log("=== LittlePrint get_quantities.php 호출됨 ===");
error_log("GET parameters: " . print_r($_GET, true));

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// GET 파라미터 받기
$style = $_GET['style'] ?? '';        // 구분
$section = $_GET['section'] ?? $_GET['Section'] ?? '';    // 재질 (TreeSelect)
$pn_type = $_GET['size'] ?? $_GET['pn_type'] ?? $_GET['PN_type'] ?? '';  // 규격 (Section)
$potype = $_GET['potype'] ?? '';      // 인쇄면

error_log("Parsed parameters - style: $style, section: $section, pn_type: $pn_type, potype: $potype");

// 테이블 우선순위 확인 (littleprint 전용 테이블이 없으면 namecard 테이블 사용)
$possible_tables = [
    "mlangprintauto_littleprint",
    "mlangprintauto_littleprint", 
    "mlangprintauto_namecard",
    "mlangprintauto_namecard"
];

$TABLE = null;
foreach ($possible_tables as $test_table) {
    $table_check = mysqli_query($db, "SHOW TABLES LIKE '$test_table'");
    if (mysqli_num_rows($table_check) > 0) {
        $TABLE = $test_table;
        error_log("사용할 테이블: $TABLE");
        break;
    }
}

if (!$TABLE) {
    error_log("사용 가능한 테이블이 없습니다: " . implode(', ', $possible_tables));
    error_response("사용 가능한 테이블이 없습니다.");
}

// Section 매핑 로직 추가 (604-609, 679-680, 958 → 610으로 통합)
$section_mapping = [
    '604' => '610', // 120아트/스노우 → 기본 포스터 데이터
    '605' => '610', // 150아트/스노우 → 기본 포스터 데이터
    '606' => '610', // 180아트/스노우 → 기본 포스터 데이터
    '607' => '610', // 200아트/스노우 → 기본 포스터 데이터
    '608' => '610', // 250아트/스노우 → 기본 포스터 데이터
    '609' => '610', // 300아트/스노우 → 기본 포스터 데이터
    '679' => '610', // 80모조 → 기본 포스터 데이터
    '680' => '610', // 100모조 → 기본 포스터 데이터
    '958' => '610'  // 200g아트/스노우지 → 기본 포스터 데이터
];

// 매핑된 section 값 사용
$mapped_section = $section_mapping[$section] ?? $section;
error_log("Section 매핑: $section → $mapped_section");

// 입력값 검증 (pn_type는 선택적 파라미터)
if (empty($style) || empty($section) || empty($potype)) {
    error_log("파라미터 누락 - style: '$style', section: '$section', pn_type: '$pn_type', potype: '$potype'");
    error_response('필수 파라미터가 누락되었습니다. (style, section, potype)');
}

// 선택된 조건에 맞는 수량 옵션들을 가져오기
// section = TreeSelect (재질), pn_type = Section (규격)
$query = "SELECT DISTINCT quantity 
          FROM $TABLE 
          WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
          AND TreeSelect='" . mysqli_real_escape_string($db, $section) . "'";

// 규격(pn_type)이 있으면 추가 조건
if (!empty($pn_type)) {
    $query .= " AND Section='" . mysqli_real_escape_string($db, $pn_type) . "'";
}

$query .= " AND POtype='" . mysqli_real_escape_string($db, $potype) . "'
          AND quantity IS NOT NULL 
          ORDER BY CAST(quantity AS UNSIGNED) ASC";

error_log("실행할 쿼리: $query");

$result = mysqli_query($db, $query);
$quantities = [];

if ($result) {
    $row_count = 0;
    while ($row = mysqli_fetch_array($result)) {
        $row_count++;
        $quantities[] = [
            'value' => $row['quantity'],
            'text' => format_number($row['quantity']) . '매'
        ];
    }
    error_log("첫 번째 쿼리 결과: $row_count 개의 수량 옵션 발견");
    
    if (empty($quantities)) {
        error_log("첫 번째 쿼리에서 결과 없음. fallback 쿼리 실행...");
        // 수량 정보가 없을 경우, potype 없이 한 번 더 조회 (일부 데이터는 potype이 없을 수 있음)
        $query_fallback = "SELECT DISTINCT quantity 
                           FROM $TABLE 
                           WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
                           AND TreeSelect='" . mysqli_real_escape_string($db, $section) . "'";
        
        // 규격(pn_type)이 있으면 추가 조건                   
        if (!empty($pn_type)) {
            $query_fallback .= " AND Section='" . mysqli_real_escape_string($db, $pn_type) . "'";
        }
        
        $query_fallback .= " AND quantity IS NOT NULL 
                           ORDER BY CAST(quantity AS UNSIGNED) ASC";
        error_log("Fallback 쿼리: $query_fallback");
        
        $result_fallback = mysqli_query($db, $query_fallback);
        if($result_fallback) {
            $fallback_count = 0;
            while ($row_fallback = mysqli_fetch_array($result_fallback)) {
                $fallback_count++;
                $quantities[] = [
                    'value' => $row_fallback['quantity'],
                    'text' => format_number($row_fallback['quantity']) . '매'
                ];
            }
            error_log("Fallback 쿼리 결과: $fallback_count 개의 수량 옵션 발견");
        }
    }
} else {
    error_log("쿼리 실행 오류: " . mysqli_error($db));
    error_response('수량 조회 중 오류가 발생했습니다: ' . mysqli_error($db));
}

error_log("최종 수량 배열: " . print_r($quantities, true));

mysqli_close($db);
success_response($quantities);
?>