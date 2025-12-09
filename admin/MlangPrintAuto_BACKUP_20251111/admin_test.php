<?php
// 기본 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';

include"../../db.php";
include"../config.php";

$T_DirUrl="../../mlangprintauto";
include"$T_DirUrl/ConDb.php";

$T_DirFole="./int/info.php";

if($mode=="OrderView"){
    include"../title.php";

    if($no){
        $result= mysqli_query($db, "select * from mlangorder_printauto where no='$no'");
        $row= mysqli_fetch_array($result);
        if($row){
            if($row['OrderStyle']=="2"){
                $query ="UPDATE mlangorder_printauto SET OrderStyle='3' WHERE no='$no'";
                $result= mysqli_query($db, $query);

                echo ("
                    <script language=javascript>
                    opener.parent.location.reload();
                    </script>
                ");
            }
        }
    }

    echo "<h1>주문정보 보기 - 테스트 모드</h1>";
    echo "<p>No: $no</p>";
    echo "<input type='button' onClick='javascript:window.close();' value=' 창닫기-CLOSE '>";
}

mysqli_close($db);
?>