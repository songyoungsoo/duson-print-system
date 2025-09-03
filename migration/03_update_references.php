<?php
/**
 * Step 3: Update all references from member to users
 * 모든 참조를 member에서 users로 업데이트
 */

echo "===== STEP 3: 참조 업데이트 =====\n\n";

$files_to_update = [
    '../db.php' => [
        ['$admin_table = "member"', '$admin_table = "users"'],
    ],
    '../MlangPrintAuto/db.php' => [
        ['$admin_table = "member"', '$admin_table = "users"'],
    ],
    '../MlangPrintAuto/cadarok/db.php' => [
        ['$admin_table = "member"', '$admin_table = "users"'],
    ],
    '../MlangPrintAuto/msticker/db.php' => [
        ['$admin_table = "member"', '$admin_table = "users"'],
    ],
    '../MlangPrintAuto/NcrFlambeau/db.php' => [
        ['$admin_table = "member"', '$admin_table = "users"'],
    ],
    '../MlangPrintAuto/Poster/db.php' => [
        ['$admin_table = "member"', '$admin_table = "users"'],
    ],
];

$updated_files = 0;
$failed_files = [];

foreach ($files_to_update as $file => $replacements) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $original_content = $content;
        
        foreach ($replacements as $replacement) {
            $content = str_replace($replacement[0], $replacement[1], $content);
        }
        
        if ($content !== $original_content) {
            // 백업 생성
            $backup_file = $file . '.backup_' . date('Ymd_His');
            file_put_contents($backup_file, $original_content);
            
            // 업데이트된 내용 저장
            if (file_put_contents($file, $content)) {
                echo "✓ 업데이트 완료: {$file}\n";
                echo "  백업 생성: {$backup_file}\n";
                $updated_files++;
            } else {
                $failed_files[] = $file;
                echo "✗ 업데이트 실패: {$file}\n";
            }
        } else {
            echo "- 변경 없음: {$file}\n";
        }
    } else {
        echo "- 파일 없음: {$file}\n";
    }
}

echo "\n===== SQL 쿼리 업데이트 파일 생성 =====\n";

// SQL 쿼리 업데이트를 위한 도우미 파일 생성
$sql_update_helper = '<?php
/**
 * SQL Query Update Helper
 * member 테이블 참조를 users 테이블로 변경하는 도우미
 */

class MemberToUsersHelper {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * member 테이블 쿼리를 users 테이블 쿼리로 변환
     */
    public function convertQuery($query) {
        // 테이블 이름 변경
        $query = preg_replace("/\bmember\b/i", "users", $query);
        
        // 컬럼 매핑
        $columnMap = [
            "no" => "member_no",  // member.no -> users.member_no
            "pass" => "password", // member.pass -> users.password
            "id" => "username",   // member.id -> users.username
            "Logincount" => "login_count",
            "EndLogin" => "last_login"
        ];
        
        foreach ($columnMap as $old => $new) {
            // 정확한 컬럼명만 변경 (테이블.컬럼 형식도 처리)
            $query = preg_replace("/\busers\." . $old . "\b/i", "users." . $new, $query);
            $query = preg_replace("/\b" . $old . "\b(?!\s*=\s*[\"\']\w+[\"\']\s*)/i", $new, $query);
        }
        
        return $query;
    }
    
    /**
     * 로그인 체크 함수
     */
    public function checkLogin($username, $password) {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // 비밀번호 확인 (해시된 비밀번호)
            if (password_verify($password, $user["password"])) {
                return $user;
            }
            // 임시: 평문 비밀번호 체크 (마이그레이션 중)
            else if ($password === $user["password"]) {
                // 평문 비밀번호를 해시로 업데이트
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update = "UPDATE users SET password = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($this->db, $update);
                mysqli_stmt_bind_param($update_stmt, "si", $hashed, $user["id"]);
                mysqli_stmt_execute($update_stmt);
                return $user;
            }
        }
        
        return false;
    }
    
    /**
     * 사용자 정보 조회
     */
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * 사용자 정보 조회 (username)
     */
    public function getUserByUsername($username) {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * 레벨 확인
     */
    public function checkUserLevel($username, $required_level = 1) {
        $user = $this->getUserByUsername($username);
        if ($user) {
            return intval($user["level"]) >= intval($required_level);
        }
        return false;
    }
}

// 전역 헬퍼 인스턴스 생성
if (isset($db)) {
    $userHelper = new MemberToUsersHelper($db);
}
?>';

file_put_contents('../includes/member_to_users_helper.php', $sql_update_helper);
echo "✓ 헬퍼 파일 생성: ../includes/member_to_users_helper.php\n";

echo "\n===== 완료 =====\n";
echo "- 업데이트된 파일: {$updated_files}개\n";
if (count($failed_files) > 0) {
    echo "- 실패한 파일:\n";
    foreach ($failed_files as $file) {
        echo "  - {$file}\n";
    }
}

echo "\n다음 단계: php 04_test_and_verify.php\n";
?>