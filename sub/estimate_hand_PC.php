<?php
include("./dbConn.inc");

// 관리자 페이지에서 확인이면
if($no && isset($_COOKIE["adminVar"])){
    $sql = "UPDATE orderDB2 SET viewCheck='Y' WHERE no=$no";
    $result = mysqli_query($connection, $sql);
    if(!$result){
        echo("<script>
            alert('확인 체크가 안되었습니다.');
            history.go(-1);
            </script>");
        exit;
    }
    echo("<script>
        opener.location.reload();
        self.close();
        </script>");
} else if($del != "" && isset($_COOKIE["adminVar"])){
    $sql = "DELETE FROM orderDB2 WHERE no=$del";
    $result = mysqli_query($connection, $sql);
    if(!$result){
        error("QUERY_ERROR");
        exit;
    }
    echo("<script>
        opener.location.reload();
        self.close();
        </script>");
} else {
    // 주문서 작성 양식을 입력받아 처리하는 페이지
    // 주문서에 입력된 값을 받아온다.
    $tel = $tel1."-".$tel2."-".$tel3;
    $fax = $fax1."-".$fax2."-".$fax3;
    $cel = $cel1."-".$cel2."-".$cel3;
    $cnt = $cntchk;    // 수량

    // 파일자료를 먼저 업로드한다.
    $upDir = $_SERVER['DOCUMENT_ROOT'] . "/upHand/$kind/";    // 파일을 업로드할 폴더 : 종류별로 업로드됨
    if (!is_dir($upDir)){
        mkdir($upDir, 0707, true); // true를 추가하여 하위 디렉토리를 생성합니다.
    }

    // 파일 업로드를 위한 함수
    function fileup($atch, $atchName, $atchSize, $upDir, $i){
        // 파일 확장자 검색
        $file_ext = strtolower(pathinfo($atchName, PATHINFO_EXTENSION));
        $forbidden_extensions = array("php", "html", "htm", "phtml", "inc", "js");
        if(in_array($file_ext, $forbidden_extensions)){
            echo("<script>
                alert('확장자가 $file_ext 인 경우는 파일을 첨부할 수 없습니다.');
                history.go(-1);
                </script>");
            exit;
        }
        
        // 한글파일때문에 이름을 변경한다.
        $atchName = date("Ymd") . time() . rand(1, 1500000) . "." . $file_ext;

        // 파일 저장 경로
        $target_file = $upDir . $atchName;

        if (file_exists($target_file)){
            echo("<script>
                alert('이미 $atchName 은 존재합니다. 확인하고 다시 올려주세요.');
                history.go(-1);
                </script>");
            exit;
        }

        if (!$atchSize || $atchSize == 0){
            echo("<script>
                alert('지정한 파일이 없거나 파일 크기가 0KB입니다.');
                history.go(-1);
                </script>");
            exit;
        } elseif ($atchSize > 10240000){
            echo("<script>
                alert('파일 크기가 10MB를 넘었습니다. 웹하드로 올려주세요.');
                history.go(-1);
                </script>");
            exit;
        }

        if(move_uploaded_file($atch, $target_file)) {
            return $atchName;
        } else {
            echo("<script>
                alert('파일 저장이 실패했습니다. 죄송하지만 다시 올려주세요.');
                history.go(-1);
                </script> ");
            exit;
        }
    }
    
    // 파일 저장 시작 : 업로드있는 만큼만 반복한다.
    for ($i = 1; $i <= 5; $i++){
        $fileN = "atch".$i;        // 업로드 변수?
        $fileName = "atch".$i."_name";        // 업로드 파일 이름
        $filesize = "atch".$i."_size";        // 업로드 파일 크기
        if (isset($$fileN) && $$fileN){
            $$fileN = fileup($$fileN, $$fileName, $$filesize, $upDir, $i);
        } else {
            break;
        }
    }

    // DB에 자료값을 입력한다.
    $wdate = date("Y-m-d");
    $sql = "INSERT INTO orderDB2 (`content`,`name`,`company`,`tel`,`fax`,`cell`,`email`,`file1`,`file2`,`file3`,`file4`,`file5`,`ordDate`,`kind`) 
            VALUES ('$content','$ordername','$company','$tel','$fax','$cel','$email','$atch1','$atch2','$atch3','$atch4','$atch5','$wdate','$kind')";
    $result = mysqli_query($connection, $sql); 

    if($result){
        echo("<script language='javascript'>  
            alert('정상적으로 입력되었습니다.');
            location.href='estimate_hand.htm';
            </script>");
        exit;
    } else {
        echo("<script language='javascript'>  
            alert('정상적으로 입력되지 않았습니다. 다시 입력하여주세요.');
            history.go(-1);
            </script>");
        exit;
    }
}
?>