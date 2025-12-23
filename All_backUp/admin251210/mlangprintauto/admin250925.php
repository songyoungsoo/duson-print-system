<?php
declare(strict_types=1);

// 기본 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';

// 데이터베이스 연결
include"../../db.php";
include"../config.php";

$T_DirUrl="../../mlangprintauto";
include"$T_DirUrl/ConDb.php";

$T_DirFole="./int/info.php";

// 주문정보 보기 모드
if($mode=="OrderView"){
    include"../title.php";

    if($no){
        $result= mysqli_query($db, "select * from mlangorder_printauto where no='$no'");
        if($result){
            $row= mysqli_fetch_array($result);
            if($row){
                if($row['OrderStyle']=="2"){
                    $query ="UPDATE mlangorder_printauto SET OrderStyle='3' WHERE no='$no'";
                    $result_update= mysqli_query($db, $query);

                    echo ("
                        <script language=javascript>
                        opener.parent.location.reload();
                        </script>
                    ");
                }
            }
        }
    }

    // 주문정보 표시 HTML
    ?>
    <style>
    a.file:link,  a.file:visited{font-family:굴림; font-size: 10pt; color:#336699; line-height:130%; text-decoration:underline}
    a.file:hover, a.file:active{font-family:굴림; font-size: 10pt; color:#333333; line-height:130%; text-decoration:underline}
    </style>

    <h1>주문정보 보기</h1>

    <?php if($no && isset($row)): ?>
        <p><strong>주문번호:</strong> <?= htmlspecialchars($no) ?></p>
        <p><strong>주문자:</strong> <?= htmlspecialchars($row['name'] ?? '') ?></p>
        <p><strong>주문일:</strong> <?= htmlspecialchars($row['date'] ?? '') ?></p>
        <p><strong>상태:</strong> <?= htmlspecialchars($row['OrderStyle'] ?? '') ?></p>

        <hr>
        <p><strong>첨부파일:</strong></p>
        <?php if(!empty($row['ThingCate'])): ?>
            <a href='download.php?downfile=<?= urlencode($row['ThingCate']) ?>'><?= htmlspecialchars($row['ThingCate']) ?></a>
        <?php endif; ?>

    <?php else: ?>
        <p>주문정보를 찾을 수 없습니다.</p>
    <?php endif; ?>

    <br><br>
    <input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE'>

    <?php
}

// 기타 모드들을 위한 기본 응답
elseif($mode) {
    echo "<h1>모드: " . htmlspecialchars($mode) . "</h1>";
    echo "<p>이 기능은 현재 점검중입니다.</p>";
    echo "<input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE'>";
}

// 기본 응답
else {
    echo "<h1>Admin Panel</h1>";
    echo "<p>모드가 지정되지 않았습니다.</p>";
}

// 데이터베이스 연결 종료
if(isset($db)){
    mysqli_close($db);
}
?>