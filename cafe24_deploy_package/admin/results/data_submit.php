<script language='javascript'>
// 창의 위치와 크기를 조정하는 스크립트
self.moveTo(0,0);
self.resizeTo(screen.availWidth, screen.availHeight);
</script>

<?php
// 데이터베이스 연결
include "../../db.php";

if ($mode == "modify_ok") {
    // 게시글 수정 처리
    if ($HH_code == "text") {
        // 텍스트로 게시글 수정
        $query = "UPDATE Mlang_${id}_Results SET 
            Mlang_bbs_member = ?, 
            Mlang_bbs_link = ?, 
            Mlang_bbs_title = ?, 
            Mlang_bbs_connent = ?, 
            Mlang_bbs_reply = ? 
            WHERE Mlang_bbs_no = ?";
        
        // prepared statement로 보안 강화
        $stmt = $db->prepare($query);
        $stmt->bind_param('sssssi', $main, $Mlang_bbs_link, $title, $connent, $Y8y_year, $no);
        $result = $stmt->execute();

        if (!$result) {
            echo "
                <script language='javascript'>
                    window.alert('DB 접속 에러입니다!');
                    history.go(-1);
                </script>";
            exit;
        } else {
            echo "
                <script language='javascript'>
                alert('정보가 정상적으로 수정되었습니다.');
                opener.parent.location.reload();
                window.self.close();
                </script>";
            exit;
        }
    } else {
        // 파일 업로드 처리 및 게시글 수정
        if ($Sofileset == "yes") {
            // 파일 업로드 처리
            include "../int/upload.inc"; // 파일 업로드 함수 포함
            $forbid_ext = array("php", "asp", "jsp", "inc", "c", "cpp", "sh");
            $result = func_multi_upload($upfile, $upfile_name, $upfile_size, $upfile_type, "../../results/upload/$id/$no/", $forbid_ext);
        } else {
            $result = "no_file";
        }

        if ($result) {
            // 파일 업로드 성공 후 데이터 업데이트
            $query = "UPDATE Mlang_${id}_Results SET 
                Mlang_bbs_member = ?, 
                Mlang_bbs_link = ?, 
                Mlang_bbs_file = ?, 
                Mlang_bbs_title = ?, 
                Mlang_bbs_connent = ?, 
                Mlang_bbs_reply = ? 
                WHERE Mlang_bbs_no = ?";

            $stmt = $db->prepare($query);
            $stmt->bind_param('ssssssi', $main, $BigUPFILENAMETwo, $BigUPFILENAME, $title, $connent, $Y8y_year, $no);
            $result_date = $stmt->execute();

            if (!$result_date) {
                echo "
                    <script language='javascript'>
                        window.alert('DB 접속 에러입니다!');
                        history.go(-1);
                    </script>";
                exit;
            } else {
                echo "
                    <script language='javascript'>
                    alert('파일이 정상적으로 업로드되었습니다.');
                    opener.parent.location.reload();
                    </script>
                    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=modify&id=$id&no=$no'>";
                exit;
            }
        } else {
            echo "
                <script language='javascript'>
                alert('파일 업로드가 실패했습니다. 다시 시도해 주세요.');
                history.go(-1);
                </script>";
            exit;
        }
    }
}

// 데이터 입력 처리
if ($mode == "submit_ok") {
    // 텍스트 게시글 입력
    if ($HH_code == "text") {
        // 새로운 게시글 번호 가져오기
        $result = $db->query("SELECT max(Mlang_bbs_no) FROM Mlang_${id}_Results");
        $row = $result->fetch_row();
        $new_no = $row[0] ? $row[0] + 1 : 1;

        // 데이터 삽입
        $date = date("Y-m-d H:i:s");
        $query = "INSERT INTO Mlang_${id}_Results (Mlang_bbs_no, Mlang_bbs_member, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_reply, Mlang_date) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param('isssss', $new_no, $main, $title, $connent, $Y8y_year, $date);
        $result_insert = $stmt->execute();

        // 완료 후 메시지 출력 및 페이지 이동
        echo "
            <script language='javascript'>
            alert('데이터가 성공적으로 저장되었습니다.');
            opener.parent.location.reload();
            </script>
            <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=submit&id=$id'>";
        exit;
    } else {
        // 파일 업로드를 통한 게시글 입력
        $result = $db->query("SELECT max(Mlang_bbs_no) FROM Mlang_${id}_Results");
        $row = $result->fetch_row();
        $new_no = $row[0] ? $row[0] + 1 : 1;

        // 업로드할 디렉토리 생성
        $dir = "../../results/upload/$id/$new_no";
        if (!is_dir($dir)) {
            mkdir($dir, 0755);
            exec("chmod 777 $dir");
        }

        if ($Sofileset1 == "yes") {
            // 파일 업로드 처리
            include "../int/upload.inc"; 
            $forbid_ext = array("php", "asp", "jsp", "inc", "c", "cpp", "sh");
            $result = func_multi_upload($upfile, $upfile_name, $upfile_size, $upfile_type, "$dir/", $forbid_ext);
        }

        // 데이터베이스에 파일 정보와 함께 삽입
        $date = date("Y-m-d H:i:s");
        $query = "INSERT INTO Mlang_${id}_Results (Mlang_bbs_no, Mlang_bbs_member, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link, Mlang_bbs_file, Mlang_bbs_reply, Mlang_date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param('isssssss', $new_no, $main, $title, $connent, $BigUPFILENAMETwo, $BigUPFILENAME, $Y8y_year, $date);
        $result_insert = $stmt->execute();

        // 완료 후 메시지 출력 및 페이지 이동
        echo "
            <script language='javascript'>
            alert('데이터가 성공적으로 업로드되었습니다.');
            opener.parent.location.reload();
            </script>
            <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=submit&id=$id'>";
        exit;
    }
}

mysqli_close($db);
?>
