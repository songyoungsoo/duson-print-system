<?php
/**
 * 포트폴리오 게시판 초기 설정 스크립트
 * 한 번만 실행하여 게시판 테이블과 관리 설정을 생성합니다.
 */

include "../db.php";

echo "<h2>포트폴리오 게시판 설정</h2>";

// 1. 게시판 테이블 생성
$create_table_query = "
CREATE TABLE IF NOT EXISTS `Mlang_portfolio_bbs` (
  `Mlang_bbs_no` int(10) NOT NULL AUTO_INCREMENT,
  `Mlang_bbs_member` varchar(100) NOT NULL,
  `Mlang_bbs_title` varchar(255) NOT NULL,
  `Mlang_bbs_style` varchar(50) DEFAULT '',
  `Mlang_bbs_connent` varchar(255) DEFAULT '',
  `Mlang_bbs_link` varchar(500) DEFAULT '',
  `Mlang_bbs_file` varchar(255) DEFAULT '',
  `Mlang_bbs_pass` varchar(100) DEFAULT '',
  `Mlang_bbs_count` int(10) DEFAULT 0,
  `Mlang_bbs_recommendation` int(10) DEFAULT 0,
  `Mlang_bbs_secret` varchar(10) DEFAULT 'yes',
  `Mlang_bbs_reply` int(10) DEFAULT 0,
  `Mlang_bbs_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `CATEGORY` varchar(50) DEFAULT '',
  `Mlang_bbs_coment` text,
  PRIMARY KEY (`Mlang_bbs_no`),
  KEY `idx_date` (`Mlang_bbs_date`),
  KEY `idx_category` (`CATEGORY`),
  KEY `idx_secret` (`Mlang_bbs_secret`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
";

$result = mysqli_query($db, $create_table_query);
if ($result) {
    echo "<p style='color:green;'>✅ 포트폴리오 게시판 테이블 생성 완료</p>";
} else {
    echo "<p style='color:red;'>❌ 테이블 생성 오류: " . mysqli_error($db) . "</p>";
}

// 2. 게시판 관리 설정 추가/업데이트
$admin_insert_query = "
INSERT INTO `Mlang_BBS_Admin` (
    `id`, `title`, `skin`, `recnum`, `lnum`, `cutlen`, 
    `New_Article`, `date_select`, `name_select`, `count_select`, 
    `recommendation_select`, `secret_select`, `write_select`, `view_select`,
    `td_width`, `td_color1`, `td_color2`, `MAXFSIZE`, 
    `file_select`, `link_select`, `cate`, `advance`,
    `header`, `footer`, `header_include`, `footer_include`
) VALUES (
    'portfolio', '포트폴리오', 'portfolio', 12, 10, 50,
    3, 'yes', 'yes', 'yes',
    'no', 'yes', 'guest', 'guest',
    '100%', '#4a6da7', '#ffffff', '5000000',
    'yes', 'yes', 'yes', 'no',
    '', '', '', ''
)
ON DUPLICATE KEY UPDATE
    `title` = '포트폴리오',
    `skin` = 'portfolio',
    `recnum` = 12,
    `lnum` = 10,
    `cutlen` = 50,
    `New_Article` = 3,
    `date_select` = 'yes',
    `name_select` = 'yes',
    `count_select` = 'yes',
    `recommendation_select` = 'no',
    `secret_select` = 'yes',
    `write_select` = 'guest',
    `view_select` = 'guest',
    `td_width` = '100%',
    `td_color1` = '#4a6da7',
    `td_color2` = '#ffffff',
    `MAXFSIZE` = '5000000',
    `file_select` = 'yes',
    `link_select` = 'yes',
    `cate` = 'yes',
    `advance` = 'no'
";

$result = mysqli_query($db, $admin_insert_query);
if ($result) {
    echo "<p style='color:green;'>✅ 포트폴리오 게시판 관리 설정 완료</p>";
} else {
    echo "<p style='color:red;'>❌ 관리 설정 오류: " . mysqli_error($db) . "</p>";
}

// 3. 업로드 디렉토리 생성
$upload_dir = __DIR__ . '/upload/portfolio/';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "<p style='color:green;'>✅ 업로드 디렉토리 생성 완료: $upload_dir</p>";
    } else {
        echo "<p style='color:red;'>❌ 업로드 디렉토리 생성 실패</p>";
    }
} else {
    echo "<p style='color:blue;'>ℹ️ 업로드 디렉토리 이미 존재: $upload_dir</p>";
}

// 4. .htaccess 파일 생성 (보안)
$htaccess_content = "# Portfolio Upload Security\n";
$htaccess_content .= "Options -Indexes\n";
$htaccess_content .= "Options -ExecCGI\n";
$htaccess_content .= "<FilesMatch \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|aspx|sh)$\">\n";
$htaccess_content .= "    Order Allow,Deny\n";
$htaccess_content .= "    Deny from all\n";
$htaccess_content .= "</FilesMatch>\n";
$htaccess_content .= "<FilesMatch \"\\.(jpg|jpeg|png|gif|bmp)$\">\n";
$htaccess_content .= "    Order Allow,Deny\n";
$htaccess_content .= "    Allow from all\n";
$htaccess_content .= "</FilesMatch>\n";

$htaccess_path = $upload_dir . '.htaccess';
if (file_put_contents($htaccess_path, $htaccess_content)) {
    echo "<p style='color:green;'>✅ 보안 설정 파일 생성 완료</p>";
} else {
    echo "<p style='color:red;'>❌ 보안 설정 파일 생성 실패</p>";
}

// 5. 테스트 데이터 삽입 (선택사항)
$test_data_query = "
INSERT INTO `Mlang_portfolio_bbs` 
(Mlang_bbs_member, Mlang_bbs_title, Mlang_bbs_connent, CATEGORY, Mlang_bbs_secret, Mlang_bbs_date) 
VALUES 
('관리자', '포트폴리오 게시판 테스트', '', 'poster', 'yes', NOW())
";

$result = mysqli_query($db, $test_data_query);
if ($result) {
    echo "<p style='color:green;'>✅ 테스트 데이터 생성 완료</p>";
} else {
    echo "<p style='color:orange;'>⚠️ 테스트 데이터는 이미 존재하거나 생성할 수 없습니다.</p>";
}

echo "<h3>설정 완료!</h3>";
echo "<p><strong>포트폴리오 게시판 URL:</strong> <a href='../bbs.php?table=portfolio&mode=list' target='_blank'>http://localhost/bbs/bbs.php?table=portfolio&mode=list</a></p>";
echo "<p><strong>글쓰기 URL:</strong> <a href='../bbs.php?table=portfolio&mode=write' target='_blank'>http://localhost/bbs/bbs.php?table=portfolio&mode=write</a></p>";

echo "<hr>";
echo "<p style='color:#666; font-size:12px;'>이 설정 스크립트는 한 번만 실행하면 됩니다. 설정 완료 후 이 파일을 삭제하셔도 됩니다.</p>";

mysqli_close($db);
?>