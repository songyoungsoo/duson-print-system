<?php
// 1부: 초기 설정 및 수정 기능
if ($mode == "ModifyOk") {
    include "../../db.php";
    include "../config.php";

    $T_DirUrl = "../../MlangPrintAuto";
    include "$T_DirUrl/ConDb.php";

    $T_DirFole = "./int/info.php";

    $query = "UPDATE MlangOrder_PrintAuto SET Type_1=?";
    $stmt = $db->prepare($query);

    if (!$stmt) {
        echo "
            <script language='javascript'>
                window.alert('DB 접속 에러입니다!')
                history.go(-1);
            </script>";
        exit;
    } else {
        $stmt->execute([$Type]);
        
        echo ("
            <script language='javascript'>
            alert('\\n정보를 정상적으로 수정하였습니다.\\n');
            opener.parent.location.reload();
            </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>
        ");
        exit;
    }

    $db = null; // 연결 종료
}

// 2부: 제출 기능
if ($mode == "SubmitOk") {
    include "../../db.php";
    include "../config.php";

    $Table_result = $db->query("SELECT max(no) FROM MlangOrder_PrintAuto");
    
    if (!$Table_result) {
        echo "
            <script>
                window.alert('DB 접속 에러입니다!')
                history.go(-1)
            </script>";
        exit;
    }
    
    $row = $Table_result->fetch(PDO::FETCH_NUM);

    if ($row[0]) {
        $new_no = $row[0] + 1;
    } else {
        $new_no = 1;
    }

    // 자료를 업로드할 폴더를 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no"; 
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        exec("chmod 777 $dir");
    }

    $date = date("Y-m-d H:i:s");
    $dbinsert = "INSERT INTO MlangOrder_PrintAuto VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_insert = $db->prepare($dbinsert);
    $stmt_insert->execute([
        $new_no,
        $Type,
        $ImgFolder,
        $TypeOne,
        $money_1,
        $money_2,
        $money_3,
        $money_4,
        $money_5,
        $name,
        $email,
        $zip,
        $zip1,
        $zip2,
        $phone,
        $Hendphone,
        $bizname,
        $bank,
        $bankname,
        $cont,
        $date,
        '3',
        $phone,
        $Gensu
    ]);

    echo ("
        <script language='javascript'>
        alert('\\n정보를 정상적으로 [저장] 하였습니다.\\n');
        opener.parent.location.reload();
        </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$new_no'>
    ");
    exit;
}

// 3부: 은행 정보 수정 폼
if ($mode == "BankForm") {
    include "../title.php";
    $Bgcolor1 = "408080";
?>

<head>
    <style>
        .Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
    </style>
    <script>
        self.moveTo(0, 0);
        self.resizeTo(availWidth = 680, availHeight = 500);
    </script>
    <script language="javascript">
        function TypeCheck(s, spc) {
            for (var i = 0; i < s.length; i++) {
                if (spc.indexOf(s.substring(i, i + 1)) < 0) {
                    return false;
                }
            }
            return true;
        }

        function MemberXCheckField() {
            var f = document.myForm;

            if (f.BankName.value == "") {
                alert("은행명을 입력하여주세요!!");
                f.BankName.focus();
                return false;
            }

            if (f.TName.value == "") {
                alert("예금주를 입력하여주세요!!");
                f.TName.focus();
                return false;
            }

            if (f.BankNo.value == "") {
                alert("계좌번호를 입력하여주세요!!");
                f.BankNo.focus();
                return false;
            }
        }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border="0" align="center" width="100%" cellpadding="5" cellspacing="5">

<form name='myForm' method='post' onsubmit='javascript:return MemberXCheckField()' action='<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>'>
    <input type="hidden" name='mode' value='BankModifyOk'>
    <input type="hidden" name='no' value='<?php echo  htmlspecialchars($no) ?>'>

    <tr>
        <td colspan="2" bgcolor='#484848'>
            <font color="white"><b>&nbsp;&nbsp;▒ 교정시안 비밀번호 기능 수정 ▒▒▒▒▒</b></font>
        </td>
    </tr>

    <tr>
        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width="100" class='Left1' align="right">사용여부&nbsp;&nbsp;</td>
        <td>
            <input type="radio" name="SignMMk" <?php echo  ($View_SignMMk == "yes") ? "checked" : "" ?> value='yes'>YES
            <input type="radio" name="SignMMk" <?php echo  ($View_SignMMk == "no") ? "checked" : "" ?> value='no'>NO
        </td>
    </tr>

    <tr>
        <td colspan="2" bgcolor='#484848'>
            <font color="white"><b>&nbsp;&nbsp;▒ 입금은행 수정 ▒▒▒▒▒</b></font>
        </td>
    </tr>

    <tr>
        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width="100" class='Left1' align="right">은행명&nbsp;&nbsp;</td>
        <td><input type="text" name="BankName" size="20" maxlength='200' value='<?php echo  htmlspecialchars($View_BankName) ?>'></td>
    </tr>

    <tr>
        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width="100" class='Left1' align="right">예금주&nbsp;&nbsp;</td>
        <td><input type="text" name="TName" size="20" maxlength='200' value='<?php echo  htmlspecialchars($View_TName) ?>'></td>
    </tr>

    <tr>
        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width="100" class='Left1' align="right">계좌번호&nbsp;&nbsp;</td>
        <td><input type="text" name="BankNo" size="40" maxlength='200' value='<?php echo  htmlspecialchars($View_BankNo) ?>'></td>
    </tr>

    <tr>
        <td colspan="2" bgcolor='#484848'>
            <font color="white"><b>&nbsp;&nbsp;▒ 견적안내 하단 TEXT 내용 수정 ▒▒▒▒▒</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;*주의사항 <big><b>'</b></big> 외 따옴표와 <big><b>"</b></big> 쌍 따옴표 입력 불가</font>
        </td>
    </tr>

    <?php
    if ($ConDb_A) {
        $Si_LIST_script = explode(":", $ConDb_A);
        foreach ($Si_LIST_script as $kt => $si_item) {
    ?>
    <tr>
        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width="100" class='Left1' align="right"><?php echo  htmlspecialchars($si_item) ?>&nbsp;&nbsp;</td>
        <td><textarea name="ContText_<?php echo  $kt ?>" rows="4" cols="58"><?php echo  htmlspecialchars(${"View_ContText_$kt"}) ?></textarea></td>
    </tr>
    <?php
        }
    }
    ?>

    <tr>
        <td>&nbsp;&nbsp;</td>
        <td>
            <input type='submit' value=' 수정 합니다.'>
        </td>
    </tr>
</form>
</table>
<br>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// 4부: 수정 처리 및 파일 업로드
if ($mode == "SinFormModifyOk") {
    if ($ModifyCode == "ok") {
        $TOrderStyle = "7";
    } else {
        $TOrderStyle = "6";
    }
    $ModifyCode = $no;

    $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->execute([$ModifyCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $GF_upfile = $row['ThingCate'];
    } else {
        echo ("<p align=center><b>DB에 $ModifyCode의 등록 자료가 없음.</b></p>");
        exit;
    }

    // 자료를 업로드할 폴더를 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$no"; 
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        exec("chmod 777 $dir");
    }

    if ($GF_upfile) {
        if ($photofileModify) {
            if ($photofile) {
                $upload_dir = "../../MlangOrder_PrintAuto/upload/$no";
                include "upload.php";
                unlink("../../MlangOrder_PrintAuto/upload/$no/$GF_upfile");
            }
        } else {
            $photofileNAME = $GF_upfile;
        }
    } else {
        if ($photofile) {
            $upload_dir = "../../MlangOrder_PrintAuto/upload/$no";
            include "upload.php";
        }
    }

    $query = "UPDATE MlangOrder_PrintAuto SET OrderStyle = ?";
    $stmt_update = $db->prepare($query);
    $stmt_update->execute([$TOrderStyle]);

    if (!$stmt_update) {
        echo "
            <script language='javascript'>
                window.alert('DB 접속 에러입니다!');
                history.go(-1);
            </script>";
        exit;
    } else {
        echo ("
            <script language='javascript'>
                alert('\\n정보를 정상적으로 수정하였습니다.\\n');
                opener.parent.location.reload();
                window.self.close();
            </script>
        ");
    }

    $db = null; // 연결 종료
    exit;
}

// 5부: 관리자 주문 등록/수정
if ($mode == "AdminMlangOrdert") {
    include "../title.php";
?>

<head>
    <script>
        self.moveTo(0, 0);
        self.resizeTo(availWidth = 680, availHeight = 400);
    </script>

    <script language="javascript">
        function MlangFriendSiteInfocheck() {
            var f = document.MlangFriendSiteInfo;

            if (f.MlangFriendSiteInfo[0].checked == false && f.MlangFriendSiteInfo[1].checked == false) {
                alert('종류를 선택해주세요');
                return false;
            }

            if (f.OrderName.value == "") {
                alert("주문자 성함을 입력해주세요");
                f.OrderName.focus();
                return false;
            }

            if (f.Designer.value == "") {
                alert("담당 디자이너를 입력해주세요");
                f.Designer.focus();
                return false;
            }

            if (f.OrderStyle.value == "0") {
                alert("결과 처리를 선택해주세요");
                f.OrderStyle.focus();
                return false;
            }

            if (f.date.value == "") {
                alert("주문 날짜를 입력해주세요\n\n마우스로 콕 찍으면 자동 입력창이 나옵니다.");
                f.date.focus();
                return false;
            }

            if (f.photofile.value == "") {
                alert("업로드할 이미지를 올려주시기 바랍니다.");
                f.photofile.focus();
                return false;
            }

            <?php
            include "$T_DirFole";
            if ($View_SignMMk == "yes") {
            ?>
                if (f.pass.value == "") {
                    alert("사용할 비밀번호를 입력해 주시기 바랍니다.");
                    f.pass.focus();
                    return false;
                }
            <?php } ?>
        }

        function Mlamg_image(image) {
            var Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
            Mlangwindow.document.open();
            Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
            Mlangwindow.document.write("<body>");
            Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
            Mlangwindow.document.write("<p align=center><input type='button' value='윈도우 닫기' onClick='window.close()'></p>");
            Mlangwindow.document.write("</body></html>");
            Mlangwindow.document.close();
        }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
    <script language="javascript" src='../js/exchange.js'></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border="0" align="center" width="100%" cellpadding="8" cellspacing="1" bgcolor='<?php echo  $Bgcolor_1 ?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' onsubmit='return MlangFriendSiteCheckField()' action='<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>'>

    <input type="hidden" name='mode' value='AdminMlangOrdertOk'>
    <input type="hidden" name='no' value='<?php echo  htmlspecialchars($no) ?>'>
    <?php if ($ModifyCode) { ?><input type="hidden" name='ModifyCode' value='ok'><?php } ?>

    <tr>
        <td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>교정/시안 - 등록/수정</font></td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>종류&nbsp;</td>
        <td>
            <input type="radio" name="MlangFriendSiteInfo" onClick='MlangFriendSiteInfocheck()'>선택박스
            <input type="radio" name="MlangFriendSiteInfo" onClick='MlangFriendSiteInfocheck()'>직접입력
            <br>
            <table border=0 align=center width=100% cellpadding=5 cellspacing=0>
                <tr>
                    <td id='Mlang_go'></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>주문자 성함&nbsp;</td>
        <td>
            <input type="text" name="OrderName" size=20> 
            <font style='color:#363636; font-size:8pt;'>(주문자 성함은 사용자가 검색하는 코드 임으로 실수 없이 입력하세요)</font>
        </td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>담당 디자이너&nbsp;</td>
        <td>
            <input type="text" name="Designer" size=20> 
        </td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>결과처리&nbsp;</td>
        <td>
            <select name='OrderStyle'>
                <option value='0'>:::선택:::</option>
                <option value='6'>시안</option>
                <option value='7'>교정</option>
            </select>
        </td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>주문날짜&nbsp;</td>
        <td>
            <input type="text" name="date" size=20 onClick="Calendar(this);">
            <font style='color:#363636; font-size:8pt;'>(입력예:2005-08-10 * 마우스로 콕 찍으면 자동입력창 나옴 * )</font>
        </td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>이미지 자료&nbsp;</td>
        <td>
            <input type="hidden" name="photofileModify" value='ok'>
            <input type="file" size=45 name="photofile" onChange="Mlamg_image(this.value)">
        </td>
    </tr>

    <?php if ($View_SignMMk == "yes") { ?>
    <tr>
        <td bgcolor='#6699CC' align=right>비밀번호&nbsp;</td>
        <td>
            <input type="text" size=25 name="pass">
        </td>
    </tr>
    <?php } ?>

    <tr>
        <td align=center colspan=2>
            <?php if ($ModifyCode) { ?>
                <input type='submit' value='수정 합니다.'>
            <?php } else { ?>
                <input type='submit' value='등록 합니다.'>
            <?php } ?>
        </td>
    </tr>

</table>

</form>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//
// 6부: 관리자 주문 처리
if ($mode == "AdminMlangOrdertOk") {
    $ToTitle = "$ThingNo";
    include "../../MlangPrintAuto/ConDb.php";

    if (!$ThingNoOkp) {
        $ThingNoOkp = "$ThingNo";
    } else {
        $ThingNoOkp = "$View_TtableB";
    }

    $stmt = $db->prepare("SELECT max(no) FROM MlangOrder_PrintAuto");
    $stmt->execute();

    if (!$stmt) {
        echo "
            <script>
                window.alert('DB 접속 에러입니다!');
                history.go(-1);
            </script>";
        exit;
    }

    $row = $stmt->fetch(PDO::FETCH_NUM);

    if ($row[0]) {
        $new_no = $row[0] + 1;
    } else {
        $new_no = 1;
    }

    // 자료를 업로드할 폴더를 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no"; 
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        exec("chmod 777 $dir");
    }

    if ($photofile) {
        $upload_dir = "$dir";
        include "upload.php";
    }

    // 디비에 관련 자료 저장
    $dbinsert = "INSERT INTO MlangOrder_PrintAuto VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_insert = $db->prepare($dbinsert);
    $result_insert = $stmt_insert->execute([
        $new_no,
        $ThingNoOkp,
        $ImgFolder,
        "$Type_1\n$Type_2\n$Type_3\n$Type_4\n$Type_5\n$Type_6",
        $money_1,
        $money_2,
        $money_3,
        $money_4,
        $money_5,
        $OrderName,
        $email,
        $zip,
        $zip1,
        $zip2,
        $phone,
        $Hendphone,
        $bizname,
        $bank,
        $bankname,
        $cont,
        $date,
        $OrderStyle,
        $photofileNAME,
        $pass,
        '',
        $Designer
    ]);

    if ($result_insert) {
        echo ("
            <script language='javascript'>
                alert('\\n정보를 정상적으로 저장 하였습니다.\\n');
                opener.parent.location.reload();
                window.self.close();
            </script>
        ");
    } else {
        echo "
            <script>
                window.alert('DB 저장 에러입니다!');
                history.go(-1);
            </script>";
    }

    $db = null; // 연결 종료
    exit;
}

// 7부: 은행 정보 수정 처리
if ($mode == "BankModifyOk") {
    $fp = fopen($T_DirFole, "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$View_SignMMk=\"$SignMMk\";\n");
    fwrite($fp, "\$View_BankName=\"$BankName\";\n");
    fwrite($fp, "\$View_TName=\"$TName\";\n");
    fwrite($fp, "\$View_BankNo=\"$BankNo\";\n");

    if ($ConDb_A) {
        $Si_LIST_script = explode(":", $ConDb_A);
        foreach ($Si_LIST_script as $kt => $si_item) {
            $tempTwo = "ContText_" . $kt; 
            $get_tempTwo = $$tempTwo;
            fwrite($fp, "\$View_ContText_{$kt}=\"$get_tempTwo\";\n");
        }
    }

    fwrite($fp, "?>");
    fclose($fp);

    echo ("
        <script language='javascript'>
            window.alert('수정 완료....*^^*');
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=BankForm'>
    ");
    exit;
}
?>

