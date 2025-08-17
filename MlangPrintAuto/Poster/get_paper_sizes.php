<?php
// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// GET 파라미터 받기
$section = $_GET['section'] ?? '';  // 용지 재질 ID (TreeSelect)

error_log("=== LittlePrint get_paper_sizes.php 호출됨 ===");
error_log("GET parameters: section=" . $section);

// 입력값 검증
if (empty($section)) {
    error_log("파라미터 누락 - section: '$section'");
    error_response('필수 파라미터가 누락되었습니다. (section)');
}

// mlangprintauto_littleprint 테이블에서 해당 재질(TreeSelect)에 사용 가능한 규격(Section) 찾기
$query = "SELECT DISTINCT Section FROM mlangprintauto_littleprint 
          WHERE TreeSelect = '" . mysqli_real_escape_string($db, $section) . "' 
          AND Section IS NOT NULL AND Section != ''
          ORDER BY Section ASC";

error_log("실행할 쿼리: $query");

$result = mysqli_query($db, $query);
$section_ids = [];

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        if (!empty($row['Section'])) {
            $section_ids[] = $row['Section'];
        }
    }
    error_log("mlangprintauto_littleprint에서 발견된 Section IDs: " . implode(', ', $section_ids));
}

// 규격 데이터가 없으면 모든 사용 가능한 규격 찾기
if (empty($section_ids)) {
    error_log("해당 재질에 대한 규격 데이터가 없음. 모든 사용 가능한 규격 찾기");
    
    // mlangprintauto_littleprint에서 사용되는 모든 Section 찾기
    $query_all = "SELECT DISTINCT Section FROM mlangprintauto_littleprint 
                  WHERE Section IS NOT NULL AND Section != ''
                  ORDER BY Section ASC";
    
    $result_all = mysqli_query($db, $query_all);
    if ($result_all) {
        while ($row = mysqli_fetch_array($result_all)) {
            if (!empty($row['Section'])) {
                $section_ids[] = $row['Section'];
            }
        }
        error_log("전체 사용 가능한 Section IDs: " . implode(', ', $section_ids));
    }
}

// mlangprintauto_transactioncate 테이블에서 규격 정보 가져오기
$sizes = [];

if (!empty($section_ids)) {
    // Section ID들을 기반으로 규격 이름 가져오기
    $section_ids_str = "'" . implode("','", array_map(function($id) use ($db) {
        return mysqli_real_escape_string($db, $id);
    }, $section_ids)) . "'";
    
    $query2 = "SELECT no, title FROM mlangprintauto_transactioncate 
               WHERE no IN ($section_ids_str) AND Ttable='LittlePrint'
               ORDER BY no ASC";
    
    error_log("규격 이름 조회 쿼리: $query2");
    
    $result2 = mysqli_query($db, $query2);
    if ($result2) {
        while ($row = mysqli_fetch_array($result2)) {
            $sizes[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
        error_log("최종 규격 옵션: " . count($sizes) . "개");
    }
}

error_log("최종 규격 배열: " . print_r($sizes, true));

mysqli_close($db);
success_response($sizes);
?>