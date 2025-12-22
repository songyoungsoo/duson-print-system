<?php
/**
 * BBS 게시판 테이블 자동 생성 스크립트
 * 실행 후 보안을 위해 이 파일을 삭제하세요!
 */

include "db.php"; // 데이터베이스 연결

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>BBS 테이블 생성</title></head><body>";
echo "<h2>🔧 BBS 게시판 테이블 생성</h2>";

// SQL 쿼리 배열
$queries = [
    // 1. leaflet_bbs
    "CREATE TABLE IF NOT EXISTS `mlang_leaflet_bbs` (
      `Mlang_bbs_no` mediumint unsigned NOT NULL AUTO_INCREMENT,
      `Mlang_bbs_member` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_bbs_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_style` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'br',
      `Mlang_bbs_connent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_file` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_pass` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_bbs_count` int NOT NULL DEFAULT '0',
      `Mlang_bbs_rec` int NOT NULL DEFAULT '0',
      `Mlang_bbs_secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
      `Mlang_bbs_reply` int NOT NULL DEFAULT '0',
      `Mlang_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `CATEGORY` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `NoticeSelect` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
      PRIMARY KEY (`Mlang_bbs_no`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // 2. leaflet_bbs_coment
    "CREATE TABLE IF NOT EXISTS `mlang_leaflet_bbs_coment` (
      `Mlang_coment_no` mediumint unsigned NOT NULL AUTO_INCREMENT,
      `Mlang_coment_BBS_no` int NOT NULL DEFAULT '0',
      `Mlang_coment_member` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_member_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_connent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_coment_ip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`Mlang_coment_no`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // 3. job_bbs
    "CREATE TABLE IF NOT EXISTS `mlang_job_bbs` (
      `Mlang_bbs_no` mediumint unsigned NOT NULL AUTO_INCREMENT,
      `Mlang_bbs_member` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_bbs_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_style` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'br',
      `Mlang_bbs_connent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_file` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_pass` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_bbs_count` int NOT NULL DEFAULT '0',
      `Mlang_bbs_rec` int NOT NULL DEFAULT '0',
      `Mlang_bbs_secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
      `Mlang_bbs_reply` int NOT NULL DEFAULT '0',
      `Mlang_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `CATEGORY` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `NoticeSelect` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
      PRIMARY KEY (`Mlang_bbs_no`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // 4. job_bbs_coment
    "CREATE TABLE IF NOT EXISTS `mlang_job_bbs_coment` (
      `Mlang_coment_no` mediumint unsigned NOT NULL AUTO_INCREMENT,
      `Mlang_coment_BBS_no` int NOT NULL DEFAULT '0',
      `Mlang_coment_member` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_member_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_connent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_coment_ip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`Mlang_coment_no`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // 5. hj_bbs
    "CREATE TABLE IF NOT EXISTS `mlang_hj_bbs` (
      `Mlang_bbs_no` mediumint unsigned NOT NULL AUTO_INCREMENT,
      `Mlang_bbs_member` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_bbs_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_style` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'br',
      `Mlang_bbs_connent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_file` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_bbs_pass` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_bbs_count` int NOT NULL DEFAULT '0',
      `Mlang_bbs_rec` int NOT NULL DEFAULT '0',
      `Mlang_bbs_secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
      `Mlang_bbs_reply` int NOT NULL DEFAULT '0',
      `Mlang_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `CATEGORY` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `NoticeSelect` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
      PRIMARY KEY (`Mlang_bbs_no`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // 6. hj_bbs_coment
    "CREATE TABLE IF NOT EXISTS `mlang_hj_bbs_coment` (
      `Mlang_coment_no` mediumint unsigned NOT NULL AUTO_INCREMENT,
      `Mlang_coment_BBS_no` int NOT NULL DEFAULT '0',
      `Mlang_coment_member` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_member_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_connent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
      `Mlang_coment_ip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
      `Mlang_coment_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`Mlang_coment_no`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // 7. admin 설정 추가
    "INSERT IGNORE INTO mlang_bbs_admin (
        no, title, id, pass, skin, recnum, lnum, cutlen, New_Article,
        date_select, name_select, count_select, recommendation_select, secret_select,
        write_select, view_select, file_select, link_select
    ) VALUES 
    (21, '전단지 갤러리', 'leaflet', '', 'portfolio', 15, 8, 100, 3,
     'yes', 'yes', 'yes', 'yes', 'yes', 'guest', 'guest', 'yes', 'yes'),
    (22, '구인구직', 'job', '', 'board', 15, 8, 100, 3,
     'yes', 'yes', 'yes', 'yes', 'yes', 'guest', 'guest', 'yes', 'yes'),
    (23, '환경게시판', 'hj', '', 'LeftPortfolio', 15, 8, 100, 3,
     'yes', 'yes', 'yes', 'yes', 'yes', 'guest', 'guest', 'yes', 'yes')"
];

$success_count = 0;
$error_count = 0;

foreach ($queries as $index => $query) {
    $num = $index + 1;
    echo "<p>$num. ";
    
    if (mysqli_query($db, $query)) {
        echo "<span style='color:green'>✅ 성공</span>";
        $success_count++;
    } else {
        echo "<span style='color:red'>❌ 실패: " . mysqli_error($db) . "</span>";
        $error_count++;
    }
    echo "</p>";
}

echo "<hr>";
echo "<h3>📊 결과 요약</h3>";
echo "<p>✅ 성공: $success_count 개</p>";
echo "<p>❌ 실패: $error_count 개</p>";

if ($error_count == 0) {
    echo "<p style='color:green; font-weight:bold; font-size:16px;'>🎉 모든 테이블이 성공적으로 생성되었습니다!</p>";
    echo "<p style='color:red;'>⚠️ <strong>보안을 위해 이 파일을 삭제하세요!</strong></p>";
} else {
    echo "<p style='color:orange;'>일부 쿼리가 실패했습니다. 에러 메시지를 확인하세요.</p>";
}

echo "</body></html>";

mysqli_close($db);
?>
