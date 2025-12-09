<?php
/**
 * 원격과 로컬의 mlangorder_printauto 테이블 구조 비교
 */

set_time_limit(60);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>테이블 구조 비교</title>";
echo "<style>
body{font-family:monospace;padding:20px;background:#f5f5f5;}
.success{color:green;}.error{color:red;}.info{color:blue;}.warning{color:orange;}
table{border-collapse:collapse;width:100%;margin:20px 0;background:white;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background:#4CAF50;color:white;}
.diff{background:#ffeb3b;}
.missing{background:#f44336;color:white;}
</style></head><body>";

echo "<h2>📊 테이블 구조 비교</h2>\n";
echo "<p class='info'>mlangorder_printauto 테이블 구조 분석</p>\n";
echo "<hr>\n";

// 원격 DB 연결 설정
$remote_host = 'dsp1830.shop';
$remote_user = 'dsp1830';
$remote_pass = 'ds701018';
$remote_db = 'dsp1830';

// 로컬 DB 연결 설정
$local_host = 'localhost';
$local_user = 'dsp1830';
$local_pass = 'ds701018';
$local_db = 'dsp1830';

// 원격 DB 연결
echo "<p>🔌 원격 서버 연결 중...</p>\n";
flush();
$remote_conn = @mysqli_connect($remote_host, $remote_user, $remote_pass, $remote_db);
if (!$remote_conn) {
    die("<p class='error'>❌ 원격 서버 연결 실패: " . mysqli_connect_error() . "</p></body></html>");
}
mysqli_set_charset($remote_conn, "utf8mb4");
echo "<p class='success'>✅ 원격 서버 연결 성공</p>\n";
flush();

// 로컬 DB 연결
echo "<p>🔌 로컬 DB 연결 중...</p>\n";
flush();
$local_conn = @mysqli_connect($local_host, $local_user, $local_pass, $local_db);
if (!$local_conn) {
    mysqli_close($remote_conn);
    die("<p class='error'>❌ 로컬 DB 연결 실패: " . mysqli_connect_error() . "</p></body></html>");
}
mysqli_set_charset($local_conn, "utf8mb4");
echo "<p class='success'>✅ 로컬 DB 연결 성공</p>\n";
flush();

// 원격 테이블 구조 가져오기
echo "<p>📋 원격 테이블 구조 조회 중...</p>\n";
flush();
$remote_query = "DESCRIBE mlangorder_printauto";
$remote_result = mysqli_query($remote_conn, $remote_query);
if (!$remote_result) {
    die("<p class='error'>❌ 원격 테이블 구조 조회 실패: " . mysqli_error($remote_conn) . "</p></body></html>");
}

$remote_fields = [];
while ($row = mysqli_fetch_assoc($remote_result)) {
    $remote_fields[$row['Field']] = $row;
}
echo "<p class='success'>✅ 원격 필드 수: " . count($remote_fields) . "개</p>\n";
flush();

// 로컬 테이블 구조 가져오기
echo "<p>📋 로컬 테이블 구조 조회 중...</p>\n";
flush();
$local_query = "DESCRIBE mlangorder_printauto";
$local_result = mysqli_query($local_conn, $local_query);
if (!$local_result) {
    die("<p class='error'>❌ 로컬 테이블 구조 조회 실패: " . mysqli_error($local_conn) . "</p></body></html>");
}

$local_fields = [];
while ($row = mysqli_fetch_assoc($local_result)) {
    $local_fields[$row['Field']] = $row;
}
echo "<p class='success'>✅ 로컬 필드 수: " . count($local_fields) . "개</p>\n";
flush();

// 비교 결과
echo "<hr>\n";
echo "<h3>📊 비교 결과</h3>\n";

$remote_only = array_diff(array_keys($remote_fields), array_keys($local_fields));
$local_only = array_diff(array_keys($local_fields), array_keys($remote_fields));
$common = array_intersect(array_keys($remote_fields), array_keys($local_fields));

if (count($remote_only) > 0) {
    echo "<p class='warning'>⚠️ 원격에만 있는 필드: " . count($remote_only) . "개</p>\n";
    echo "<ul>\n";
    foreach ($remote_only as $field) {
        echo "<li class='warning'>{$field} ({$remote_fields[$field]['Type']})</li>\n";
    }
    echo "</ul>\n";
}

if (count($local_only) > 0) {
    echo "<p class='warning'>⚠️ 로컬에만 있는 필드: " . count($local_only) . "개</p>\n";
    echo "<ul>\n";
    foreach ($local_only as $field) {
        echo "<li class='warning'>{$field} ({$local_fields[$field]['Type']})</li>\n";
    }
    echo "</ul>\n";
}

echo "<p class='info'>ℹ️ 공통 필드: " . count($common) . "개</p>\n";

// 상세 비교 테이블
echo "<h3>📋 상세 필드 비교</h3>\n";
echo "<table>\n";
echo "<tr><th>필드명</th><th>원격 타입</th><th>로컬 타입</th><th>상태</th></tr>\n";

$all_fields = array_unique(array_merge(array_keys($remote_fields), array_keys($local_fields)));
sort($all_fields);

foreach ($all_fields as $field) {
    $remote_exists = isset($remote_fields[$field]);
    $local_exists = isset($local_fields[$field]);
    
    $remote_type = $remote_exists ? $remote_fields[$field]['Type'] : '-';
    $local_type = $local_exists ? $local_fields[$field]['Type'] : '-';
    
    $status = '';
    $row_class = '';
    
    if (!$remote_exists) {
        $status = '로컬에만 존재';
        $row_class = 'class="missing"';
    } elseif (!$local_exists) {
        $status = '원격에만 존재';
        $row_class = 'class="missing"';
    } elseif ($remote_type != $local_type) {
        $status = '타입 불일치';
        $row_class = 'class="diff"';
    } else {
        $status = '일치';
    }
    
    echo "<tr {$row_class}><td>{$field}</td><td>{$remote_type}</td><td>{$local_type}</td><td>{$status}</td></tr>\n";
}

echo "</table>\n";

// 권장 사항
echo "<hr>\n";
echo "<h3>💡 권장 사항</h3>\n";

if (count($remote_only) > 0 || count($local_only) > 0) {
    echo "<p class='warning'>⚠️ 테이블 구조가 다릅니다. 동기화 전에 구조를 맞춰야 합니다.</p>\n";
    
    if (count($remote_only) > 0) {
        echo "<h4>로컬에 추가할 필드:</h4>\n";
        echo "<pre style='background:#f0f0f0;padding:10px;'>\n";
        foreach ($remote_only as $field) {
            $f = $remote_fields[$field];
            $null = $f['Null'] == 'YES' ? 'NULL' : 'NOT NULL';
            $default = $f['Default'] !== null ? "DEFAULT '{$f['Default']}'" : '';
            echo "ALTER TABLE mlangorder_printauto ADD COLUMN `{$field}` {$f['Type']} {$null} {$default};\n";
        }
        echo "</pre>\n";
    }
    
    if (count($local_only) > 0) {
        echo "<h4>로컬에서 제거할 필드 (선택사항):</h4>\n";
        echo "<pre style='background:#f0f0f0;padding:10px;'>\n";
        foreach ($local_only as $field) {
            echo "ALTER TABLE mlangorder_printauto DROP COLUMN `{$field}`;\n";
        }
        echo "</pre>\n";
    }
} else {
    echo "<p class='success'>✅ 테이블 구조가 일치합니다. 동기화를 진행할 수 있습니다.</p>\n";
    echo "<p><a href='sync_mlangorder_from_remote.php' style='padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;border-radius:4px;'>🔄 동기화 시작</a></p>\n";
}

mysqli_close($remote_conn);
mysqli_close($local_conn);

echo "<p><a href='javascript:history.back()'>← 돌아가기</a></p>\n";
echo "</body></html>";
?>
