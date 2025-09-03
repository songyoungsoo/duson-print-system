<?php
define('DB_ACCESS_ALLOWED', true);
include "../../db.php"; // 통합 데이터베이스 연결 사용
include "../config.php"; // 추가 설정

// $db 변수는 db.php에서 이미 연결되어 제공됨
if (!$db) {
    echo "<script>
            alert('DB 연결 에러입니다!');
            history.go(-1);
          </script>";
    exit;
}

// 1. 입력한 ID가 이미 존재하는지 확인
$stmt = $db->prepare("SELECT * FROM Mlnag_Results_Admin WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result_ID_inspection = $stmt->get_result();
$rows_ID_inspection = $result_ID_inspection->num_rows;

if ($rows_ID_inspection > 0) {
    // ID가 이미 존재할 경우 경고 메시지를 출력하고 이전 페이지로 돌아감
    echo "<script>
            window.alert('입력하신 $id 와 일치하는 데이터베이스가 존재합니다.\\n\\n다른 값을 입력해 주세요.');
            history.go(-1);
          </script>";
    exit;
} else {
    // 2. ID에 해당하는 테이블이 이미 존재하는지 확인
    $table_name = "Mlang_{$id}_Results";
    $result = $db->query("SHOW TABLES LIKE '$table_name'");

    if ($result->num_rows > 0) {
        // ID와 동일한 테이블이 이미 존재하는 경우 경고 메시지 출력
        echo "<script>
                window.alert('ERROR(2): $table_name 테이블 ID와 동일한 테이블이 이미 존재합니다.\\n\\n다른 ID를 입력해 주세요.');
                history.go(-1);
              </script>";
        exit;
    } else {
        // 3. Mlnag_Results_Admin 테이블에 새로운 ID 추가
        $stmt = $db->query("SELECT MAX(no) FROM Mlnag_Results_Admin");
        if (!$stmt) {
            echo "<script>
                    alert('DB 접속 에러입니다!');
                    history.go(-1);
                  </script>";
            exit;
        }

        $row = $stmt->fetch_row();
        $new_no = $row[0] ? $row[0] + 1 : 1;

        // 현재 날짜 설정
        $date = date("Y-m-d");

        // 새 데이터를 Mlnag_Results_Admin 테이블에 삽입
        $stmt = $db->prepare("INSERT INTO Mlnag_Results_Admin (no, item, title, id, celect, date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $new_no, $item, $title, $id, $celect, $date);
        $result_insert = $stmt->execute();

        if (!$result_insert) {
            echo "<script>
                    alert('DB 접속 에러입니다!');
                    history.go(-1);
                  </script>";
            exit;
        }

        // 4. 새로운 결과 테이블 생성
        $create_table_sql = "CREATE TABLE Mlang_${id}_Results (
            Mlang_bbs_no MEDIUMINT(12) UNSIGNED NOT NULL AUTO_INCREMENT, 
            Mlang_bbs_member VARCHAR(100) NOT NULL DEFAULT '',                
            Mlang_bbs_title TEXT,                                                                 
            Mlang_bbs_style VARCHAR(100) NOT NULL DEFAULT 'br',                
            Mlang_bbs_connent TEXT,                                                             
            Mlang_bbs_link TEXT,                                                                   
            Mlang_bbs_file TEXT,                                                                  
            Mlang_bbs_pass VARCHAR(100) NOT NULL DEFAULT '',                  
            Mlang_bbs_count INT(12) NOT NULL DEFAULT '0',                              
            Mlang_bbs_rec INT(12) NOT NULL DEFAULT '0',                                                  
            Mlang_bbs_secret VARCHAR(100) NOT NULL DEFAULT 'yes',                  
            Mlang_bbs_reply INT(12) NOT NULL DEFAULT '0',                                           
            Mlang_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',                   
            PRIMARY KEY (Mlang_bbs_no)
        )";

        if (!$db->query($create_table_sql)) {
            echo "<script>
                    alert('테이블 생성 오류가 발생했습니다.');
                    history.go(-1);
                  </script>";
            exit;
        }

        // 5. 결과 업로드를 위한 디렉터리 생성
        $dir = "../../results/upload/$id";
        if (!file_exists($dir)) {
            mkdir($dir, 0755);
            exec("chmod 777 $dir");
        }

        // 6. 성공 메시지를 출력하고 리스트 페이지로 이동
        echo "<script>
                alert('성공적으로 결과 저장 시스템이 설정되었습니다.');
              </script>";
        echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list'>";
        exit;
    }
}

// 데이터베이스 연결 종료
mysqli_close($db);
?>
