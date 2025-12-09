<?php
/**
 * Member 테이블 → Users 테이블 데이터 마이그레이션
 * 레벨 체계: 양쪽 테이블 모두 동일
 *            level 1 = 관리자
 *            level 5 = 일반회원 (기본값)
 * 마이그레이션: level 값 그대로 복사 (변환 없음)
 *
 * 실행 후 삭제 권장
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

echo "<h2>🔄 Member → Users 데이터 마이그레이션</h2>";
echo "<hr>";

// 1. member 테이블 확인
echo "<h3>1. member 테이블 구조 확인</h3>";
$check_member = mysqli_query($db, "SHOW TABLES LIKE 'member'");

if (mysqli_num_rows($check_member) == 0) {
    die("❌ member 테이블이 존재하지 않습니다.");
}

$member_columns = mysqli_query($db, "DESCRIBE member");
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>Key</th></tr>";

$has_level = false;
while ($row = mysqli_fetch_assoc($member_columns)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "</tr>";

    if ($row['Field'] === 'level' || $row['Field'] === 'Level') {
        $has_level = true;
    }
}
echo "</table>";

if (!$has_level) {
    echo "<p style='color: orange;'>⚠️ member 테이블에 level 컬럼이 없습니다. 모든 회원을 일반회원(level=5)으로 가져옵니다.</p>";
}

// 2. 현재 member 테이블 데이터 확인
echo "<br><h3>2. member 테이블 데이터 현황</h3>";
$member_count = mysqli_query($db, "SELECT COUNT(*) as cnt FROM member");
$count_data = mysqli_fetch_assoc($member_count);
echo "<p>총 회원 수: <strong>{$count_data['cnt']}</strong>명</p>";

// 레벨별 분포 확인 (level 컬럼이 있는 경우만)
if ($has_level) {
    echo "<h4>레벨별 회원 분포 (현재 member 테이블 기준)</h4>";
    $level_dist = mysqli_query($db, "SELECT level, COUNT(*) as cnt FROM member GROUP BY level ORDER BY level");

    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Member Level</th><th>의미</th><th>→</th><th>Users Level</th><th>회원 수</th></tr>";

    while ($row = mysqli_fetch_assoc($level_dist)) {
        $member_level = $row['level'];
        $users_level = '';
        $meaning = '';

        // 레벨 그대로 유지 (변환 없음)
        $users_level = $member_level;
        if ($member_level == 1) {
            $meaning = '관리자';
        } else if ($member_level == 5) {
            $meaning = '일반회원';
        } else {
            $meaning = '기타 레벨';
        }

        echo "<tr>";
        echo "<td style='text-align: center;'><strong>{$member_level}</strong></td>";
        echo "<td>{$meaning}</td>";
        echo "<td style='text-align: center;'>→</td>";
        echo "<td style='text-align: center; color: blue;'><strong>{$users_level}</strong></td>";
        echo "<td style='text-align: right;'>{$row['cnt']}명</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. 마이그레이션 실행
echo "<br><h3>3. 데이터 마이그레이션 실행</h3>";

// 마이그레이션 쿼리 작성 (level 컬럼 유무에 따라 분기)
if ($has_level) {
    // level 컬럼이 있는 경우: 레벨 그대로 복사 (변환 없음)
    $migrate_query = "
        INSERT INTO users (username, password, name, email, phone, level, created_at)
        SELECT
            id,
            pass,
            name,
            email,
            CONCAT(hendphone1, hendphone2, hendphone3) as phone,
            level,                           -- member의 level 그대로 복사 (1=관리자, 5=일반회원)
            date
        FROM member
        WHERE NOT EXISTS (
            SELECT 1 FROM users WHERE users.username = member.id
        )
    ";
} else {
    // level 컬럼이 없는 경우: 모두 일반회원(level=5)로 처리
    $migrate_query = "
        INSERT INTO users (username, password, name, email, phone, level, created_at)
        SELECT
            id,
            pass,
            name,
            email,
            CONCAT(hendphone1, hendphone2, hendphone3) as phone,
            5 as level,                      -- 모두 일반회원 (기본값)
            date
        FROM member
        WHERE NOT EXISTS (
            SELECT 1 FROM users WHERE users.username = member.id
        )
    ";
}

// 실행
$result = mysqli_query($db, $migrate_query);

if ($result) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 마이그레이션 완료! <strong>{$affected}</strong>명의 회원이 users 테이블로 복사되었습니다.<br>";

    if ($affected == 0) {
        echo "<p style='color: orange;'>⚠️ 복사된 회원이 없습니다. 이미 모든 회원이 users 테이블에 존재하거나, member 테이블이 비어있습니다.</p>";
    }
} else {
    echo "❌ 마이그레이션 실패: " . mysqli_error($db) . "<br>";
}

// 4. 마이그레이션 결과 확인
echo "<br><h3>4. 마이그레이션 결과 확인</h3>";

$users_count = mysqli_query($db, "SELECT COUNT(*) as cnt FROM users");
$users_data = mysqli_fetch_assoc($users_count);
echo "<p>현재 users 테이블 총 회원 수: <strong>{$users_data['cnt']}</strong>명</p>";

// users 테이블 레벨별 분포
echo "<h4>Users 테이블 레벨별 분포 (변환 후)</h4>";
$users_level_dist = mysqli_query($db, "SELECT level, COUNT(*) as cnt FROM users GROUP BY level ORDER BY level DESC");

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Level</th><th>권한</th><th>회원 수</th></tr>";

while ($row = mysqli_fetch_assoc($users_level_dist)) {
    $level = $row['level'];
    $role = '';
    $color = '';

    if ($level <= 1) {
        $role = '관리자';
        $color = 'color: red; font-weight: bold;';
    } else {
        $role = '일반회원';
        $color = 'color: green;';
    }

    echo "<tr>";
    echo "<td style='text-align: center; {$color}'><strong>{$level}</strong></td>";
    echo "<td>{$role}</td>";
    echo "<td style='text-align: right;'>{$row['cnt']}명</td>";
    echo "</tr>";
}
echo "</table>";

// 5. 관리자 계정 확인
echo "<br><h3>5. 관리자 계정 목록 (level <= 1)</h3>";
$admin_list = mysqli_query($db, "SELECT id, username, name, email, level, created_at FROM users WHERE level <= 1 ORDER BY level, created_at");

if (mysqli_num_rows($admin_list) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Level</th><th>Created</th></tr>";

    while ($admin = mysqli_fetch_assoc($admin_list)) {
        echo "<tr>";
        echo "<td>{$admin['id']}</td>";
        echo "<td><strong>{$admin['username']}</strong></td>";
        echo "<td>{$admin['name']}</td>";
        echo "<td>{$admin['email']}</td>";
        echo "<td style='text-align: center; color: red; font-weight: bold;'>{$admin['level']}</td>";
        echo "<td>{$admin['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ 관리자 계정이 없습니다.</p>";
}

echo "<br><hr>";
echo "<h3>✅ 마이그레이션 완료!</h3>";

echo "<h4>📋 레벨 체계 (동일)</h4>";
echo "<ul>";
echo "<li><strong>Member 테이블</strong>: level 1 = 관리자, level 5 = 일반회원</li>";
echo "<li><strong>Users 테이블</strong>: level 1 = 관리자, level 5 = 일반회원</li>";
echo "<li><strong>마이그레이션</strong>: Member의 level → Users의 level (그대로 복사, 변환 없음)</li>";
echo "</ul>";

echo "<h4>⚠️ 주의사항</h4>";
echo "<ul>";
echo "<li>비밀번호는 member 테이블의 암호화 방식 그대로 복사됩니다.</li>";
echo "<li>member 테이블은 평문(Plain Text) 비밀번호를 사용하므로, 보안을 위해 password_hash() 재암호화가 필요합니다.</li>";
echo "<li>이미 users 테이블에 있는 username은 중복 방지로 인해 복사되지 않습니다.</li>";
echo "<li>휴대폰 번호는 hendphone1, hendphone2, hendphone3를 합쳐서 저장됩니다.</li>";
echo "</ul>";

echo "<br><p style='color: #999;'><em>⚠️ 이 파일은 보안을 위해 실행 후 삭제하는 것을 권장합니다.</em></p>";

mysqli_close($db);
?>
