<?php
include "../../db.php";
include "../config.php";

// (예시) DB 연결, 환경설정 파일 등
$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE  = "inserted";
include "$T_DirUrl/ConDb.php";  // DB: $db 등
$T_DirFole = "$T_DirUrl/$T_TABLE/inc.php";
$TABLE = "MlangPrintAuto_{$T_TABLE}";
$MlangPrintAutoFildView_POtype = "";

// --------------------------------------------------------------------------------
// 공통 함수 예시
// --------------------------------------------------------------------------------
function TypeCheck($s, $spc)
{
    for ($i = 0; $i < strlen($s); $i++) {
        if (strpos($spc, substr($s, $i, 1)) === false) {
            return false;
        }
    }
    return true;
}

// $_REQUEST에서 넘어온 값을 받아둡니다.
// (mode, code, 등등 필요 변수 받기)
$mode      = isset($_REQUEST['mode'])      ? $_REQUEST['mode']      : "";
$code      = isset($_REQUEST['code'])      ? $_REQUEST['code']      : "";
$no        = isset($_REQUEST['no'])        ? $_REQUEST['no']        : "";
$RadOne    = isset($_REQUEST['RadOne'])    ? $_REQUEST['RadOne']    : "";
$myList    = isset($_REQUEST['myList'])    ? $_REQUEST['myList']    : "";
$quantity  = isset($_REQUEST['quantity'])  ? $_REQUEST['quantity']  : "";
$money     = isset($_REQUEST['money'])     ? $_REQUEST['money']     : "";
$myListTreeSelect = isset($_REQUEST['myListTreeSelect']) ? $_REQUEST['myListTreeSelect'] : "";
$TDesignMoney     = isset($_REQUEST['TDesignMoney'])     ? $_REQUEST['TDesignMoney']     : "";
$POtype           = isset($_REQUEST['POtype'])           ? $_REQUEST['POtype']           : "";
$quantityTwo      = isset($_REQUEST['quantityTwo'])      ? $_REQUEST['quantityTwo']      : "";

// 파일 업로드 관련 변수
$ImeOneChick  = isset($_REQUEST['ImeOneChick'])  ? $_REQUEST['ImeOneChick']  : "";
$ImeTwoChick  = isset($_REQUEST['ImeTwoChick'])  ? $_REQUEST['ImeTwoChick']  : "";
$ImeTreeChick = isset($_REQUEST['ImeTreeChick']) ? $_REQUEST['ImeTreeChick'] : "";
$ImeFourChick = isset($_REQUEST['ImeFourChick']) ? $_REQUEST['ImeFourChick'] : "";
$ImeFiveChick = isset($_REQUEST['ImeFiveChick']) ? $_REQUEST['ImeFiveChick'] : "";

// 그 외 inc에서 사용되는 textarea·파일명
$Section1 = isset($_REQUEST['Section1']) ? $_REQUEST['Section1'] : "";
$Section2 = isset($_REQUEST['Section2']) ? $_REQUEST['Section2'] : "";
$Section3 = isset($_REQUEST['Section3']) ? $_REQUEST['Section3'] : "";
$Section4 = isset($_REQUEST['Section4']) ? $_REQUEST['Section4'] : "";
$Section5 = isset($_REQUEST['Section5']) ? $_REQUEST['Section5'] : "";

$File1 = isset($_FILES['File1']['name']) ? $_FILES['File1']['name'] : "";
$File2 = isset($_FILES['File2']['name']) ? $_FILES['File2']['name'] : "";
$File3 = isset($_FILES['File3']['name']) ? $_FILES['File3']['name'] : "";
$File4 = isset($_FILES['File4']['name']) ? $_FILES['File4']['name'] : "";
$File5 = isset($_FILES['File5']['name']) ? $_FILES['File5']['name'] : "";

$File1_Y = isset($_REQUEST['File1_Y']) ? $_REQUEST['File1_Y'] : "";
$File2_Y = isset($_REQUEST['File2_Y']) ? $_REQUEST['File2_Y'] : "";
$File3_Y = isset($_REQUEST['File3_Y']) ? $_REQUEST['File3_Y'] : "";
$File4_Y = isset($_REQUEST['File4_Y']) ? $_REQUEST['File4_Y'] : "";
$File5_Y = isset($_REQUEST['File5_Y']) ? $_REQUEST['File5_Y'] : "";

// --------------------------------------------------------------------------------
// mode별 분기 (switch문)
// --------------------------------------------------------------------------------
switch ($mode) {

    // --------------------------------------------------------------------------
    // 1. 입력/수정 화면 (form)
    // --------------------------------------------------------------------------
    case "form":
        doForm($code, $T_DirFole, $TABLE, $quantity, $money);
        break;

    // --------------------------------------------------------------------------
    // 2. form_ok: DB insert 처리
    // --------------------------------------------------------------------------
    case "form_ok":
        doFormOk($TABLE, $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo);
        break;

    // --------------------------------------------------------------------------
    // 3. Modify_ok: DB update 처리
    // --------------------------------------------------------------------------
    case "Modify_ok":
        doModifyOk($TABLE, $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo, $no, $db);
        break;

    // --------------------------------------------------------------------------
    // 4. delete: 레코드 삭제 처리
    // --------------------------------------------------------------------------
    case "delete":
        doDelete($TABLE, $no);
        break;

    // --------------------------------------------------------------------------
    // 5. IncForm: inc 파일(설정) 입력/수정 폼
    // --------------------------------------------------------------------------
    case "IncForm":
        doIncForm($T_DirFole);
        break;

    // --------------------------------------------------------------------------
    // 6. IncFormOk: inc 파일(설정) 저장 처리
    // --------------------------------------------------------------------------
    case "IncFormOk":
        doIncFormOk(
            $T_DirUrl, $T_DirFole,
            $ImeOneChick, $File1, $File1_Y,
            $ImeTwoChick, $File2, $File2_Y,
            $ImeTreeChick, $File3, $File3_Y,
            $ImeFourChick, $File4, $File4_Y,
            $ImeFiveChick, $File5, $File5_Y,
            $_REQUEST['moeny'],  // incForm에서 넘어온 변수
            $Section1, $Section2, $Section3, $Section4, $Section5
        );
        break;

    // --------------------------------------------------------------------------
    // 기본 동작(아무것도 안 함)
    // --------------------------------------------------------------------------
    default:
        // 필요하다면 기본 안내문을 띄우거나 리스트 페이지로 리다이렉트 하는 용도로 쓰세요.
        echo "<p>mode 파라미터가 지정되지 않았습니다.</p>";
        break;
}

// --------------------------------------------------------------------------------
// 함수 정의부
// --------------------------------------------------------------------------------

// (A) 입력/수정 화면
function doForm($code, $T_DirFole, $TABLE, $quantity, $money)
{
    include "../title.php";
    include "$T_DirFole";    // inc.php

    // 예시로 배경색
    $Bgcolor1 = "408080";

    // 수정 상태면 특정 필드 include
    if ($code=="Modify") {
        $T_TABLE  = "inserted";
        include "./{$T_TABLE}_NoFild.php";
    }

    ?>
    <head>
    <style>
    .Left1 { font-size:10pt; color:#FFFFFF; font-weight:bold; }
    </style>
    <script language="javascript">
    var NUM   = "0123456789."; 
    var SALPHA= "abcdefghijklmnopqrstuvwxyz";
    var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

    function TypeCheck (s, spc) {
        for (var i=0; i < s.length; i++) {
            if (spc.indexOf(s.substring(i, i+1)) < 0) {
                return false;
            }
        }
        return true;
    }

    function MemberXCheckField() {
        var f = document.myForm;

        if (f.RadOne.value == "#" || f.RadOne.value == "==================") {
            alert("[인쇄색상]를 선택해주세요!!");
            f.RadOne.focus();
            return false;
        }
        if (f.myListTreeSelect.value == "#" || f.myListTreeSelect.value == "==================") {
            alert("[종이종류]을 선택해주세요!!");
            f.myListTreeSelect.focus();
            return false;
        }
        if (f.myList.value == "#" || f.myList.value == "==================") {
            alert("[종이규격]을 선택해주세요!!");
            f.myList.focus();
            return false;
        }
        if (f.quantity.value == "") {
            alert("수량을 입력해주세요!!");
            f.quantity.focus();
            return false;
        }
        if (!TypeCheck(f.quantity.value, NUM)) {
            alert("수량은 숫자로만 입력하세요.");
            f.quantity.focus();
            return false;
        }
        if (f.quantityTwo.value == "") {
            alert("수량(옆)을 입력해주세요!!");
            f.quantityTwo.focus();
            return false;
        }
        if (f.money.value == "") {
            alert("가격을 입력해주세요!!");
            f.money.focus();
            return false;
        }
        if (!TypeCheck(f.money.value, NUM)) {
            alert("가격은 숫자로만 입력하세요.");
            f.money.focus();
            return false;
        }
        if (f.TDesignMoney.value == "") {
            alert("디자인비를 입력해주세요!!");
            f.TDesignMoney.focus();
            return false;
        }
        if (!TypeCheck(f.TDesignMoney.value, NUM)) {
            alert("디자인비는 숫자로만 입력하세요.");
            f.TDesignMoney.focus();
            return false;
        }
        // 통과
        return true;
    }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
    </head>

    <body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>
    <form name="myForm" method="post" action="<?php echo $_SERVER['PHP_SELF']?>" onsubmit="return MemberXCheckField()">
    <input type="hidden" name="mode" value="<?php echo ($code=='Modify')?'Modify_ok':'form_ok'?>">
    <input type="hidden" name="code" value="<?php echo $code?>">

    <?php if($code=="Modify"){ ?>
        <b>&nbsp;&nbsp;▒[<?php echo $View_TtableC?>] 자료 수정 ▒▒▒▒▒</b><br>
    <?php } else {          $View_TtableC = "신규 자료입력";?>
        <b>&nbsp;&nbsp;▒[<?php echo $View_TtableC?>] 신 자료 입력 ▒▒▒▒▒</b><br>
    <?php } ?>

    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">

    <?php include "inserted_Script.php"; ?>



<?php 
include "../../db.php";
$db = mysqli_connect($host, $user, $password, $dataname); ?>
    <tr>
    <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">인쇄면&nbsp;&nbsp;</td>
    <td>
    <?php $MlangPrintAutoFildView_POtype = ""; ?>
        <select name="POtype">
            <option value="1" <?php if($MlangPrintAutoFildView_POtype=="1"){echo("selected style='background-color:#429EB2; color:#FFFFFF;'");} ?>>단면</option>
            <option value="2" <?php if($MlangPrintAutoFildView_POtype=="2"){echo("selected style='background-color:#429EB2; color:#FFFFFF;'");} ?>>양면</option>
        </select>
    </td>
    </tr>

    <tr>
    <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">수량&nbsp;&nbsp;</td>
    <td>
        <input type="text" name="quantity" size="20" maxlength="20" 
        <?php if($code=="Modify"){ echo("value='$MlangPrintAutoFildView_quantity'"); } ?>> 연
    </td>
    </tr>

    <tr>
    <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">수량(옆)&nbsp;&nbsp;</td>
    <td>
        <input type="text" name="quantityTwo" size="20" maxlength="20"
        <?php if($code=="Modify"){ echo("value='$MlangPrintAutoFildView_quantityTwo'"); } ?>> 장
    </td>
    </tr>

    <tr>
    <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">가격&nbsp;&nbsp;</td>
    <td>
        <input type="text" name="money" size="20" maxlength="20"
        <?php if($code=="Modify"){ echo("value='$MlangPrintAutoFildView_money'"); } ?>>
    </td>
    </tr>

    <tr>
    <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">디자인비&nbsp;&nbsp;</td>
    <td>
        <input type="text" name="TDesignMoney" size="20" maxlength="20"
        <?php 
            if($code=="Modify"){
                echo("value='$MlangPrintAutoFildView_DesignMoney'");
            } else {
                echo("value='$DesignMoney'"); 
            }
        ?>>
    </td>
    </tr>

    <tr>
    <td>&nbsp;&nbsp;</td>
    <td>
        <input type="submit" value=" 저장 ">
    </td>
    </tr>

    </table>
    </form>
    </body>
    <?php
    // 종료
}

// (B) 새 데이터 입력(form_ok)
function doFormOk($TABLE, $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo)
{
    $db = mysqli_connect($host, $user, $password, $dataname); 
    global $db; // 데이터베이스 연결 객체

    // Prepared Statement 사용 (SQL Injection 방지)
    $query = "INSERT INTO $TABLE 
              VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($db, $query)) {
        mysqli_stmt_bind_param($stmt, "ssssssss", 
            $RadOne, 
            $myList, 
            $quantity, 
            $money, 
            $myListTreeSelect, 
            $TDesignMoney, 
            $POtype, 
            $quantityTwo
        );

        $execute_result = mysqli_stmt_execute($stmt);

        if ($execute_result) {
            echo "<script>
                alert('자료를 정상적으로 저장 하였습니다.');
                opener.parent.location.reload();
                window.location.href = '{$_SERVER['PHP_SELF']}?mode=form&Ttable={$TABLE}';
            </script>";
        } else {
            echo "<script>alert('데이터 저장 실패: " . mysqli_error($db) . "');</script>";
        }

        mysqli_stmt_close($stmt); // Statement 닫기
    } else {
        echo "<script>alert('쿼리 준비 실패: " . mysqli_error($db) . "');</script>";
    }

    exit;
}

function doModifyOk($TABLE, $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo, $no, $db)
{
    // Prepared Statement 사용 (SQL Injection 방지)
    $query = "UPDATE $TABLE 
              SET style = ?, 
                  Section = ?, 
                  quantity = ?, 
                  money = ?, 
                  TreeSelect = ?, 
                  DesignMoney = ?, 
                  POtype = ?, 
                  quantityTwo = ? 
              WHERE no = ?";

    if ($stmt = mysqli_prepare($db, $query)) {
        mysqli_stmt_bind_param($stmt, "ssssssssi", 
            $RadOne, 
            $myList, 
            $quantity, 
            $money, 
            $myListTreeSelect, 
            $TDesignMoney, 
            $POtype, 
            $quantityTwo, 
            $no
        );

        $execute_result = mysqli_stmt_execute($stmt);

        if ($execute_result) {
            echo "<script>
                alert('데이터가 정상적으로 수정되었습니다.');
                opener.parent.location.reload();
                window.location.href = '{$_SERVER['PHP_SELF']}?mode=form&code=Modify&no={$no}&Ttable={$TABLE}';
            </script>";
        } else {
            echo "<script>alert('DB 업데이트 실패: " . mysqli_error($db) . "');</script>";
        }

        mysqli_stmt_close($stmt); // Statement 닫기
    } else {
        echo "<script>alert('쿼리 준비 실패: " . mysqli_error($db) . "');</script>";
    }

    mysqli_close($db); // 연결 닫기
    exit;
}


// (D) 데이터 삭제(delete)
function doDelete($TABLE, $no, $db)
{
    // Prepared Statement 사용 (SQL Injection 방지)
    $query = "DELETE FROM $TABLE WHERE no = ?";

    if ($stmt = mysqli_prepare($db, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $no);
        $execute_result = mysqli_stmt_execute($stmt);

        if ($execute_result) {
            echo "<script>
                alert('$no 번 데이터를 삭제했습니다.');
                opener.parent.location.reload();
                window.self.close();
            </script>";
        } else {
            echo "<script>alert('DB 삭제 실패: " . mysqli_error($db) . "');</script>";
        }

        mysqli_stmt_close($stmt); // Statement 닫기
    } else {
        echo "<script>alert('쿼리 준비 실패: " . mysqli_error($db) . "');</script>";
    }

    mysqli_close($db); // DB 연결 닫기
    exit;
}


// (E) inc 설정 파일 입력/수정 (IncForm)
function doIncForm($T_DirFole)
{
    include "$T_DirFole";
    include "../title.php";
    ?>
    <head>
    <script language="javascript">
    var NUM    = "0123456789"; 
    var SALPHA = "abcdefghijklmnopqrstuvwxyz";
    var ALPHA  = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

    function TypeCheck (s, spc) {
        for (var i=0; i< s.length; i++) {
            if (spc.indexOf(s.substring(i, i+1)) < 0) {
                return false;
            }
        }
        return true;
    }
    function AdminPassKleCheckField()
    {
        var f = document.AdminPassKleInfo;
        if (f.moeny.value == "") {
            alert("디자인비 기본값을 입력해주세요?");
            f.moeny.focus();
            return false;
        }
        if (!TypeCheck(f.moeny.value, NUM)) {
            alert("디자인비는 숫자로 입력해주세요.");
            f.moeny.focus();
            return false;
        }
        return true;
    }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
    </head>

    <body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

    <br>
    <p align="center">
    <form name='AdminPassKleInfo' method='post' 
        onsubmit='return AdminPassKleCheckField()' 
        action='<?php echo $_SERVER["PHP_SELF"]?>'
        enctype='multipart/form-data'>
        <input type="hidden" name="mode" value="IncFormOk">
        
        <table border="1" width="100%" align="center" cellpadding="5" cellspacing="0">
        <tr>
            <td bgcolor='#6699CC' class='td11' colspan="2">
                아래 항목을 숫자로 입력해주세요.
            </td>
        </tr>
        <tr>
            <td align="center">디자인비 설정</td>
            <td>
                <input type='text' name='moeny' maxlength='10' size='15' value='<?php echo $DesignMoney?>'> 원
            </td>
        </tr>

        <tr>
            <td bgcolor='#6699CC' class='td11' colspan="2">
                <font style='color:#FFFFFF; line-height:130%;'>
                아래 텍스트박스에 마우스를 갖다대면 툴팁이 뜹니다만,<br>
                실제로 이 부분은 HTML 인식을 하지만 자동 줄바꿈은 적용되지 않습니다.<br>
                입력 시 줄바꿈 문자를 직접 넣어주세요.<br>
                <font color="red">*</font> 등등 안내 문구...
                </font>
            </td>
        </tr>

        <!-- 부서/구분별 TEXTAREA + 이미지 업로드 -->
        <tr>
            <td align="center">인쇄색상</td>
            <td>
                <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center">
                            <textarea name="Section1" rows="4" cols="50"><?php echo $SectionOne?></textarea>
                        </td>
                        <td align="center">
                            <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <input type='file' name='File1' size='20'><br>
                                        <?php if($ImgOne) { ?>
                                            <input type="checkbox" name="ImeOneChick">이미지를 삭제하려면 체크
                                            <input type="hidden" name="File1_Y" value="<?php echo $ImgOne?>">
                                        <?php } ?>
                                    </td>
                                    <?php if($ImgOne) { ?>
                                        <td align="center">
                                            <img src='<?php echo $upload_dir?>/<?php echo $ImgOne?>' width="80" height="95" border="0">
                                        </td>
                                    <?php } ?>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

<!-- Section2 예시 -->
<tr>
    <td align="center">종이종류</td>
    <td>
        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <textarea name="Section2" rows="4" cols="50"><?php echo $SectionTwo?></textarea>
                </td>
                <td align="center">
                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center">
                                <input type='file' name='File2' size='20'><br>
                                <?php if($ImgTwo) { ?>
                                    <input type="checkbox" name="ImeTwoChick">이미지를 삭제하려면 체크
                                    <input type="hidden" name="File2_Y" value="<?php echo $ImgTwo?>">
                                <?php } ?>
                            </td>
                            <?php if($ImgTwo) { ?>
                                <td align="center">
                                    <img src='<?php echo $upload_dir?>/<?php echo $ImgTwo?>' width="80" height="95" border="0">
                                </td>
                            <?php } ?>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>

<!-- Section3 예시 -->
<tr>
    <td align="center">종이규격</td>
    <td>
        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <textarea name="Section3" rows="4" cols="50"><?php echo $SectionTree?></textarea>
                </td>
                <td align="center">
                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center">
                                <input type='file' name='File3' size='20'><br>
                                <?php if($ImgTree) { ?>
                                    <input type="checkbox" name="ImeTreeChick">이미지를 삭제하려면 체크
                                    <input type="hidden" name="File3_Y" value="<?php echo $ImgTree?>">
                                <?php } ?>
                            </td>
                            <?php if($ImgTree) { ?>
                                <td align="center">
                                    <img src='<?php echo $upload_dir?>/<?php echo $ImgTree?>' width="80" height="95" border="0">
                                </td>
                            <?php } ?>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>

<!-- Section4 예시 -->
<tr>
    <td align="center">수량</td>
    <td>
        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <textarea name="Section4" rows="4" cols="50"><?php echo $SectionFour?></textarea>
                </td>
                <td align="center">
                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center">
                                <input type='file' name='File4' size='20'><br>
                                <?php if($ImgFour) { ?>
                                    <input type="checkbox" name="ImeFourChick">이미지를 삭제하려면 체크
                                    <input type="hidden" name="File4_Y" value="<?php echo $ImgFour?>">
                                <?php } ?>
                            </td>
                            <?php if($ImgFour) { ?>
                                <td align="center">
                                    <img src='<?php echo $upload_dir?>/<?php echo $ImgFour?>' width="80" height="95" border="0">
                                </td>
                            <?php } ?>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>

<!-- Section5 예시 -->
<tr>
    <td align="center">디자인</td>
    <td>
        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <textarea name="Section5" rows="4" cols="50"><?php echo $SectionFive?></textarea>
                </td>
                <td align="center">
                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center">
                                <input type='file' name='File5' size='20'><br>
                                <?php if($ImgFive) { ?>
                                    <input type="checkbox" name="ImeFiveChick">이미지를 삭제하려면 체크
                                    <input type="hidden" name="File5_Y" value="<?php echo $ImgFive?>">
                                <?php } ?>
                            </td>
                            <?php if($ImgFive) { ?>
                                <td align="center">
                                    <img src='<?php echo $upload_dir?>/<?php echo $ImgFive?>' width="80" height="95" border="0">
                                </td>
                            <?php } ?>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>

<!--
    여기까지 Section1 ~ Section5가 모두 같은 구조로 반복됨
    필요에 맞게 부서명(또는 항목명), 변수 등을 변경해주세요.
-->

</table>

        <br>
        <input type='submit' value=' 저장하기 '>
        <input type='button' value=' 창 닫기 ' onclick='javascript:window.self.close();'>
    </form>
    </p>
    <?php
    exit;
}

// (F) inc 설정 파일 저장 (IncFormOk)
function doIncFormOk(
    $T_DirUrl, $T_DirFole,
    $ImeOneChick,  $File1,  $File1_Y,
    $ImeTwoChick,  $File2,  $File2_Y,
    $ImeTreeChick, $File3,  $File3_Y,
    $ImeFourChick, $File4,  $File4_Y,
    $ImeFiveChick, $File5,  $File5_Y,
    $moeny,
    $Section1, $Section2, $Section3, $Section4, $Section5
)
{
    global $upload_dir; // 업로드 디렉토리가 있다면

    // ---------------------------------------------------------------------------------
    // File1 처리
    // ---------------------------------------------------------------------------------
    if ($ImeOneChick == "on") {
        // 이미지 삭제 + 신규 업로드 처리
        if ($File1) {
            if ($File1_Y) {
                unlink("$upload_dir/$File1_Y");
            }
            include "$T_DirUrl/Upload_1.php";   // 업로드 함수
        } else {
            // 새로운 파일 업로드 없이 삭제만 체크한 경우
            if ($File1_Y) { 
                unlink("$upload_dir/$File1_Y"); 
            }
        }
    } else {
        // 삭제 체크(X)
        if ($File1_Y) {
            // 기존 이미지 유지
            $File1NAME = $File1_Y;
        } else {
            // 새 이미지 업로드만 존재할 경우
            if ($File1) { 
                include "$T_DirUrl/Upload_1.php"; 
            }
        }
    }

    // ---------------------------------------------------------------------------------
    // File2 처리
    // ---------------------------------------------------------------------------------
    if ($ImeTwoChick == "on") {
        if ($File2) {
            if ($File2_Y) {
                unlink("$upload_dir/$File2_Y");
            }
            include "$T_DirUrl/Upload_2.php"; 
        } else {
            if ($File2_Y) {
                unlink("$upload_dir/$File2_Y");
            }
        }
    } else {
        if ($File2_Y) {
            $File2NAME = $File2_Y;
        } else {
            if ($File2) { 
                include "$T_DirUrl/Upload_2.php"; 
            }
        }
    }

    // ---------------------------------------------------------------------------------
    // File3 처리
    // ---------------------------------------------------------------------------------
    if ($ImeTreeChick == "on") {
        if ($File3) {
            if ($File3_Y) {
                unlink("$upload_dir/$File3_Y");
            }
            include "$T_DirUrl/Upload_3.php";
        } else {
            if ($File3_Y) {
                unlink("$upload_dir/$File3_Y");
            }
        }
    } else {
        if ($File3_Y) {
            $File3NAME = $File3_Y;
        } else {
            if ($File3) { 
                include "$T_DirUrl/Upload_3.php"; 
            }
        }
    }

    // ---------------------------------------------------------------------------------
    // File4 처리
    // ---------------------------------------------------------------------------------
    if ($ImeFourChick == "on") {
        if ($File4) {
            if ($File4_Y) {
                unlink("$upload_dir/$File4_Y");
            }
            include "$T_DirUrl/Upload_4.php";
        } else {
            if ($File4_Y) {
                unlink("$upload_dir/$File4_Y");
            }
        }
    } else {
        if ($File4_Y) {
            $File4NAME = $File4_Y;
        } else {
            if ($File4) { 
                include "$T_DirUrl/Upload_4.php"; 
            }
        }
    }

    // ---------------------------------------------------------------------------------
    // File5 처리
    // ---------------------------------------------------------------------------------
    if ($ImeFiveChick == "on") {
        if ($File5) {
            if ($File5_Y) {
                unlink("$upload_dir/$File5_Y");
            }
            include "$T_DirUrl/Upload_5.php";
        } else {
            if ($File5_Y) {
                unlink("$upload_dir/$File5_Y");
            }
        }
    } else {
        if ($File5_Y) {
            $File5NAME = $File5_Y;
        } else {
            if ($File5) { 
                include "$T_DirUrl/Upload_5.php"; 
            }
        }
    }

    // ---------------------------------------------------------------------------------
    // inc 파일로 최종 값 저장
    // ---------------------------------------------------------------------------------
    $fp = fopen($T_DirFole, "w");
    fwrite($fp, "<?\n");
    fwrite($fp, "\$DesignMoney=\"$moeny\";\n");
    fwrite($fp, "\$SectionOne=\"$Section1\";\n");
    fwrite($fp, "\$SectionTwo=\"$Section2\";\n");
    fwrite($fp, "\$SectionTree=\"$Section3\";\n");
    fwrite($fp, "\$SectionFour=\"$Section4\";\n");
    fwrite($fp, "\$SectionFive=\"$Section5\";\n");
    fwrite($fp, "\$ImgOne=\"$File1NAME\";\n");
    fwrite($fp, "\$ImgTwo=\"$File2NAME\";\n");
    fwrite($fp, "\$ImgTree=\"$File3NAME\";\n");
    fwrite($fp, "\$ImgFour=\"$File4NAME\";\n");
    fwrite($fp, "\$ImgFive=\"$File5NAME\";\n");
    fwrite($fp, "?>");
    fclose($fp);

    echo ("<script language='javascript'>
    window.alert('설정이 저장되었습니다! *^^*');
    </script>
    <meta http-equiv='Refresh' content='0; URL={$_SERVER['PHP_SELF']}?mode=IncForm'>
    ");
    exit;
}
?>
