#!/usr/bin/env php
<?php
/**
 * 데이터베이스 스키마 비교 도구
 * 로컬과 프로덕션 DB의 테이블 필드를 비교하여 차이점을 출력
 */

// 사용법 체크
if ($argc < 3) {
    echo "사용법: php compare_db_schema.php <로컬_덤프.sql> <프로덕션_덤프.sql>\n";
    echo "예시: php compare_db_schema.php local_schema_dump.sql production_schema_dump.sql\n";
    exit(1);
}

$local_file = $argv[1];
$prod_file = $argv[2];

if (!file_exists($local_file)) {
    die("로컬 덤프 파일이 존재하지 않습니다: $local_file\n");
}

if (!file_exists($prod_file)) {
    die("프로덕션 덤프 파일이 존재하지 않습니다: $prod_file\n");
}

/**
 * SQL 덤프 파일에서 테이블 구조 파싱
 */
function parseSchema($file) {
    $content = file_get_contents($file);
    $tables = [];

    // CREATE TABLE 문 추출 (여러 줄에 걸쳐 있을 수 있음)
    preg_match_all('/CREATE TABLE `([^`]+)`\s*\((.*?)\)\s*ENGINE/s', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $table_name = $match[1];
        $table_def = $match[2];

        // 필드 정의 추출
        $fields = [];
        $lines = explode("\n", $table_def);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'PRIMARY KEY') === 0 || strpos($line, 'KEY') === 0 || strpos($line, 'UNIQUE') === 0) {
                continue;
            }

            // 필드명과 타입 추출
            if (preg_match('/^`([^`]+)`\s+(.+?)(,|$)/', $line, $field_match)) {
                $field_name = $field_match[1];
                $field_def = rtrim($field_match[2], ',');
                $fields[$field_name] = $field_def;
            }
        }

        $tables[$table_name] = $fields;
    }

    return $tables;
}

echo "🔍 데이터베이스 스키마 비교 시작...\n\n";

$local_tables = parseSchema($local_file);
$prod_tables = parseSchema($prod_file);

echo "📊 로컬 테이블 수: " . count($local_tables) . "\n";
echo "📊 프로덕션 테이블 수: " . count($prod_tables) . "\n\n";

$all_tables = array_unique(array_merge(array_keys($local_tables), array_keys($prod_tables)));
sort($all_tables);

$differences = [];
$alter_scripts = [];

foreach ($all_tables as $table) {
    $local_fields = $local_tables[$table] ?? [];
    $prod_fields = $prod_tables[$table] ?? [];

    // 테이블이 한쪽에만 존재
    if (empty($local_fields)) {
        echo "⚠️  테이블 '$table'이 로컬에 없습니다.\n";
        continue;
    }

    if (empty($prod_fields)) {
        echo "⚠️  테이블 '$table'이 프로덕션에 없습니다.\n";
        continue;
    }

    // 필드 비교
    $all_fields = array_unique(array_merge(array_keys($local_fields), array_keys($prod_fields)));
    $table_diff = [];

    foreach ($all_fields as $field) {
        $local_def = $local_fields[$field] ?? null;
        $prod_def = $prod_fields[$field] ?? null;

        if ($local_def === null) {
            // 프로덕션에만 있는 필드
            $table_diff[] = [
                'field' => $field,
                'type' => 'prod_only',
                'prod_def' => $prod_def
            ];
        } elseif ($prod_def === null) {
            // 로컬에만 있는 필드 (추가해야 함)
            $table_diff[] = [
                'field' => $field,
                'type' => 'local_only',
                'local_def' => $local_def
            ];

            // ALTER TABLE 스크립트 생성
            $alter_scripts[] = "ALTER TABLE `$table` ADD COLUMN `$field` $local_def;";
        } elseif ($local_def !== $prod_def) {
            // 정의가 다른 필드
            $table_diff[] = [
                'field' => $field,
                'type' => 'different',
                'local_def' => $local_def,
                'prod_def' => $prod_def
            ];

            // ALTER TABLE 스크립트 생성
            $alter_scripts[] = "ALTER TABLE `$table` MODIFY COLUMN `$field` $local_def;";
        }
    }

    if (!empty($table_diff)) {
        $differences[$table] = $table_diff;
    }
}

// 결과 출력
if (empty($differences)) {
    echo "✅ 모든 테이블의 스키마가 동일합니다!\n";
} else {
    echo "=" . str_repeat("=", 80) . "\n";
    echo "📋 스키마 차이점 상세 분석\n";
    echo "=" . str_repeat("=", 80) . "\n\n";

    foreach ($differences as $table => $diffs) {
        echo "📌 테이블: $table\n";
        echo str_repeat("-", 80) . "\n";

        foreach ($diffs as $diff) {
            switch ($diff['type']) {
                case 'local_only':
                    echo "  ➕ 추가 필요: `{$diff['field']}`\n";
                    echo "     로컬 정의: {$diff['local_def']}\n";
                    break;

                case 'prod_only':
                    echo "  ⚠️  프로덕션에만 존재: `{$diff['field']}`\n";
                    echo "     프로덕션 정의: {$diff['prod_def']}\n";
                    break;

                case 'different':
                    echo "  🔄 정의 차이: `{$diff['field']}`\n";
                    echo "     로컬:      {$diff['local_def']}\n";
                    echo "     프로덕션:  {$diff['prod_def']}\n";
                    break;
            }
            echo "\n";
        }

        echo "\n";
    }
}

// ALTER TABLE 스크립트 저장
if (!empty($alter_scripts)) {
    echo "=" . str_repeat("=", 80) . "\n";
    echo "🔧 프로덕션 DB 업데이트 스크립트\n";
    echo "=" . str_repeat("=", 80) . "\n\n";

    $script_file = dirname(__FILE__) . '/update_production_schema.sql';
    $script_content = "-- 프로덕션 데이터베이스 스키마 업데이트 스크립트\n";
    $script_content .= "-- 생성일: " . date('Y-m-d H:i:s') . "\n";
    $script_content .= "-- 주의: 프로덕션 DB 백업 후 실행하세요!\n\n";
    $script_content .= "USE dsp1830;\n\n";
    $script_content .= implode("\n", $alter_scripts);

    file_put_contents($script_file, $script_content);

    echo "✅ ALTER TABLE 스크립트 저장됨: $script_file\n\n";
    echo "📝 스크립트 내용:\n";
    echo str_repeat("-", 80) . "\n";
    echo $script_content;
    echo str_repeat("-", 80) . "\n";
} else {
    echo "\n✅ 스키마 업데이트가 필요하지 않습니다.\n";
}

echo "\n✅ 비교 완료!\n";
