<?php
// 프로덕션 DB 연결
require_once __DIR__ . '/../db.php';

echo "<pre>";

// 1. knowledge_base 테이블 생성
$create_table_sql = "CREATE TABLE IF NOT EXISTS `knowledge_base` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(500) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `tags` VARCHAR(500),
  `category` VARCHAR(50) DEFAULT 'general',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FULLTEXT INDEX `ft_search` (`title`, `content`, `tags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($db, $create_table_sql)) {
    echo "✅ knowledge_base 테이블 생성 완료\n";
} else {
    echo "❌ 테이블 생성 실패: " . mysqli_error($db) . "\n";
    exit(1);
}

// 2. 문서 내용 준비 (프로덕션 경로)
$title = "도메인 스왑 완벽 가이드 (dsp114.com ↔ dsp114.co.kr)";
$category = "workflow";
$tags = "domain, dns, migration, plesk, deployment, 도메인스왑";
$content_file = __DIR__ . '/../kb_domain_swap_guide.md';

if (!file_exists($content_file)) {
    echo "❌ 문서 파일을 찾을 수 없습니다: $content_file\n";
    exit(1);
}

$content = file_get_contents($content_file);

if ($content === false) {
    echo "❌ 문서 파일 읽기 실패\n";
    exit(1);
}

// 3. 문서 삽입
$stmt = mysqli_prepare($db, "INSERT INTO knowledge_base (title, content, tags, category) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'ssss', $title, $content, $tags, $category);

if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($db);
    echo "✅ 문서 저장 완료 (ID: $id)\n\n";
    echo "접속 URL: https://dsp114.co.kr/kb/article.php?id=$id\n\n";
    echo "이 파일(setup.php)은 보안을 위해 삭제해주세요.\n";
} else {
    echo "❌ 문서 저장 실패: " . mysqli_error($db) . "\n";
    exit(1);
}

mysqli_close($db);
echo "</pre>";
