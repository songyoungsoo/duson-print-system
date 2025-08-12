<?php
include "../../db.php";
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";
$T_DirFole = "./int/info.php";

// Handle form submissions and actions
if ($mode == "ModifyOk") {
    $stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET Type_1=?, name=?, email=?, zip=?, zip1=?, zip2=?, phone=?, Hendphone=?, bizname=?, bank=?, bankname=?, cont=?, Gensu=? WHERE no=?");
    $stmt->bind_param("ssssssssssssi", $TypeOne, $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, $bank, $bankname, $cont, $Gensu, $no);

    if (!$stmt->execute()) {
        echo "<script>alert('DB update failed!'); history.go(-1);</script>";
        exit;
    } else {
        echo "<script>alert('Data updated successfully.'); opener.parent.location.reload();</script>";
        echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>";
        exit;
    }

    $stmt->close();
    $db->close();
} elseif ($mode == "SubmitOk") {
    $result = $db->query("SELECT max(no) FROM MlangOrder_PrintAuto");
    $row = $result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
    }

    $date = date("Y-m-d H:i:s");
    $stmt = $db->prepare("INSERT INTO MlangOrder_PrintAuto (no, Type, ImgFolder, Type_1, money_1, money_2, money_3, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, bizname, bank, bankname, cont, date, OrderStyle, pass, Gensu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $OrderStyle = 3;
    $stmt->bind_param("issssssssssssssssssssssi", $new_no, $Type, $ImgFolder, $TypeOne, $money_1, $money_2, $money_3, $money_4, $money_5, $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, $bank, $bankname, $cont, $date, $OrderStyle, $phone, $Gensu);

    if (!$stmt->execute()) {
        echo "<script>alert('DB insert failed!'); history.go(-1);</script>";
        exit;
    } else {
        echo "<script>alert('Data submitted successfully.'); opener.parent.location.reload();</script>";
        echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$new_no'>";
        exit;
    }

    $stmt->close();
    $db->close();
} elseif ($mode == "BankForm") {
    include "../title.php";
    include "$T_DirFole";
    $Bgcolor1 = "408080";
    ?>

<head>
    <style>
        .Left1 {
            font-size: 10pt;
            color: #FFFFFF;
            font-weight: bold;
        }
    </style>
    <script>
        self.moveTo(0, 0);
        self.resizeTo(screen.availWidth, screen.availHeight);

        function MemberXCheckField() {
            var f = document.myForm;

            if (f.BankName.value == "") {
                alert("은행명을 입력해 주세요!!");
                f.BankName.focus();
                return false;
            }

            if (f.TName.value == "") {
                alert("예금주명을 입력해 주세요!!");
                f.TName.focus();
                return false;
            }

            if (f.BankNo.value == "") {
                alert("계좌번호를 입력해 주세요!!");
                f.BankNo.focus();
                return false;
            }
        }
    </script>
</head>

<body class='coolBar'>
    <table border=0 align=center width=100% cellpadding=5 cellspacing=5>
        <form name='myForm' method='post' onsubmit='return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
            <input type="hidden" name='mode' value='BankModifyOk'>
            <tr>
                <td colspan=2 bgcolor='#484848'>
                    <font color=white><b>&nbsp;&nbsp;은행 계좌 정보 수정</b></font>
                </td>
            </tr>
            <tr>
                <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>뱅킹 사용여부&nbsp;&nbsp;</td>
                <td>
                    <input type="radio" name="SignMMk" value='yes' <?php echo  $View_SignMMk == "yes" ? "checked" : "" ?>>YES
                    <input type="radio" name="SignMMk" value='no' <?php echo  $View_SignMMk == "no" ? "checked" : "" ?>>NO
                </td>
            </tr>
            <tr>
                <td colspan=2 bgcolor='#484848'>
                    <font color=white><b>&nbsp;&nbsp;계좌 정보</b></font>
                </td>
            </tr>
            <tr>
                <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>은행명&nbsp;&nbsp;</td>
                <td><input type="text" name="BankName" size=20 maxLength='200' value='<?php echo $View_BankName?>'></td>
            </tr>
            <tr>
                <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>예금주명&nbsp;&nbsp;</td>
                <td><input type="text" name="TName" size=20 maxLength='200' value='<?php echo $View_TName?>'></td>
            </tr>
            <tr>
                <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>계좌번호&nbsp;&nbsp;</td>
                <td><input type="text" name="BankNo" size=40 maxLength='200' value='<?php echo $View_BankNo?>'></td>
            </tr>
            <tr>
                <td colspan=2 bgcolor='#484848'>
                    <font color=white><b>&nbsp;&nbsp;TEXT 입력</b><br>&nbsp;&nbsp;&nbsp;&nbsp;*주의: 따옴표 및 큰따옴표 사용 불가</font>
                </td>
            </tr>
            <?php
            if ($ConDb_A) {
                $Si_LIST_script = explode(":", $ConDb_A);
                $k = 0;
                foreach ($Si_LIST_script as $script) {
                    echo "<tr>
                            <td bgcolor='#$Bgcolor1' width=100 class='Left1' align=right>$script&nbsp;&nbsp;</td>
                            <td><textarea name='ContText_$k' rows='4' cols='58'>${"View_ContText_$k"}</textarea></td>
                          </tr>";
                    $k++;
                }
            }
            ?>
            <tr>
                <td>&nbsp;&nbsp;</td>
                <td><input type='submit' value=' 수정 완료 '></td>
            </tr>
        </form>
    </table>
    <br>
</body>

<?php
} elseif ($mode == "BankModifyOk") {
    $fp = fopen("$T_DirFole", "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$View_SignMMk=\"$SignMMk\";\n");
    fwrite($fp, "\$View_BankName=\"$BankName\";\n");
    fwrite($fp, "\$View_TName=\"$TName\";\n");
    fwrite($fp, "\$View_BankNo=\"$BankNo\";\n");

    if ($ConDb_A) {
        $Si_LIST_script = explode(":", $ConDb_A);
        foreach ($Si_LIST_script as $index => $script) {
            $tempTwo = "ContText_$index";
            $get_tempTwo = $$tempTwo;
            fwrite($fp, "\$View_ContText_$index=\"$get_tempTwo\";\n");
        }
    }

    fwrite($fp, "?>");
    fclose($fp);

    echo "<script>alert('수정 완료....*^^*');</script>";
    echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=BankForm'>";
    exit;
} elseif ($mode == "OrderView") {
    include "../title.php";
    if ($no) {
        $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no=?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['OrderStyle'] == "2") {
                $stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle='3' WHERE no=?");
                $stmt->bind_param("i", $no);
                $stmt->execute();
                echo "<script>opener.parent.location.reload();</script>";
            }
        }
        $stmt->close();
    }
    ?>

<style>
    a.file:link,
    a.file:visited {
        font-family: 굴림;
        font-size: 10pt;
        color: #336699;
        line-height: 130%;
        text-decoration: underline
    }

    a.file:hover,
    a.file:active {
        font-family: 굴림;
        font-size: 10pt;
        color: #333333;
        line-height: 130%;
        text-decoration: underline
    }
</style>

<?php
    $ViewDiwr = "../../MlangOrder_PrintAuto";
    include "$ViewDiwr/OrderFormOrderTree.php";
    if ($no) {
        echo "<br>
              <font style='font:bold; color:#336699;'>* 첨부 파일 *</font> 파일명을 클릭하시면 열람/다운로드가 가능합니다. =============================<br>
              <table border=0 align=center width=100% cellpadding=20 cellspacing=0>
                <tr>
                  <td>";
        if (is_dir("../../ImgFolder/$View_ImgFolder")) {
            $dir_path = "../../ImgFolder/$View_ImgFolder";
            if ($View_ImgFolder) {
                $dir_handle = opendir($dir_path);
                $i = 1;
                while ($tmp = readdir($dir_handle)) {
                    if ($tmp != "." && $tmp != "..") {
                        echo is_file("$dir_path/$tmp") ? "" : "[$i] 파일: <a href='$dir_path/$tmp' target='_blank' class='file'>$tmp</a><br>";
                        $i++;
                    }
                }
                closedir($dir_handle);
            }
        }
        echo "</td>
              </tr>
              </table>
              ===================================================================================================
              <p align=center>
              <input type='button' value=' 인쇄하기 ' onclick='window.print();'>
              <input type='button' onclick='javascript:window.close();' value=' 창 닫기 '>
              </p>";
    }
} elseif ($mode == "SinForm") {
    include "../title.php";
    ?>

<head>
    <script>
        self.moveTo(0, 0);
        self.resizeTo(screen.availWidth, 200);

        function MlangFriendSiteCheckField() {
            var f = document.MlangFriendSiteInfo;
            if (f.photofile.value == "") {
                alert("업로드할 이미지를 선택해 주세요.");
                f.photofile.focus();
                return false;
            }

            <?php include "$T_DirFole";
            if ($View_SignMMk == "yes"): ?>
            if (f.pass.value == "") {
                alert("비밀번호를 입력해 주세요.");
                f.pass.focus();
                return false;
            }
            <?php endif; ?>

            return true;
        }

        function Mlamg_image(image) {
            var Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
            Mlangwindow.document.open();
            Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
            Mlangwindow.document.write("<body>");
            Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
            Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='창 닫기' onClick='window.close()'></p>");
            Mlangwindow.document.write("</body></html>");
            Mlangwindow.document.close();
        }
    </script>
</head>

<body class='coolBar'>
    <table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='<?php echo $Bgcolor_1?>'>
        <form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' onsubmit='return MlangFriendSiteCheckField()' action='<?php echo $PHP_SELF?>'>
            <input type="hidden" name='mode' value='SinFormModifyOk'>
            <input type="hidden" name='no' value='<?php echo $no?>'>
            <?php if ($ModifyCode): ?>
            <input type="hidden" name='ModifyCode' value='ok'>
            <?php endif; ?>

            <tr>
                <td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>첨부 파일 - 테스트 용</font></td>
            </tr>

            <tr>
                <td align=right>이미지 파일:&nbsp;</td>
                <td>
                    <input type="hidden" name="photofileModify" value='ok'>
                    <input type="file" size=45 name="photofile" onChange="Mlamg_image(this.value)">
                </td>
            </tr>

            <?php
            if ($View_SignMMk == "yes") {
                $result_SignTy = $db->query("SELECT pass FROM MlangOrder_PrintAuto WHERE no='$no'");
                $row_SignTy = $result_SignTy->fetch_assoc();
                $ViewSignTy_pass = $row_SignTy['pass'];
                ?>
            <tr>
                <td align=right>비밀번호:&nbsp;</td>
                <td><input type="text" name="pass" size=20 value='<?php echo $ViewSignTy_pass?>'></td>
            </tr>
            <?php
            }
            ?>

            <tr>
                <td>&nbsp;</td>
                <td><?php if ($ModifyCode): ?>
                    <input type='submit' value=' 수정 완료 '>
                    <?php else: ?>
                    <input type='submit' value=' 등록 완료 '>
                    <?php endif; ?>
                </td>
            </tr>
        </form>
    </table>
    <br>
</body>

<?php
} elseif ($mode == "SinFormModifyOk") {
    $TOrderStyle = $ModifyCode == "ok" ? 7 : 6;
    $ModifyCode = $no;

    $stmt = $db->prepare("SELECT ThingCate FROM MlangOrder_PrintAuto WHERE no=?");
    $stmt->bind_param("i", $ModifyCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $GF_upfile = $row['ThingCate'];

    if (!$GF_upfile) {
        echo "<p align=center><b>DB에 $ModifyCode 항목이 없습니다.</b></p>";
        exit;
    }

    $dir = "../../MlangOrder_PrintAuto/upload/$no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
    }

    if ($GF_upfile) {
        if ($photofileModify && $photofile) {
            $upload_dir = $dir;
            include "upload.php";
            unlink("$dir/$GF_upfile");
        } else {
            $photofileNAME = $GF_upfile;
        }
    } else {
        if ($photofile) {
            $upload_dir = $dir;
            include "upload.php";
        }
    }

    $stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle=?, ThingCate=?, pass=? WHERE no=?");
    $stmt->bind_param("issi", $TOrderStyle, $photofileNAME, $pass, $no);

    if (!$stmt->execute()) {
        echo "<script>alert('DB update failed!'); history.go(-1);</script>";
        exit;
    } else {
        echo "<script>alert('Data updated successfully.'); opener.parent.location.reload(); window.self.close();</script>";
    }

    $stmt->close();
    $db->close();
    exit;
} elseif ($mode == "AdminMlangOrdert") {
    include "../title.php";
    ?>

<head>
    <script>
        self.moveTo(0, 0);
        self.resizeTo(screen.availWidth, screen.availHeight);

        function MlangFriendSiteCheckField() {
            var f = document.MlangFriendSiteInfo;

            if (!f.MlangFriendSiteInfoS[0].checked && !f.MlangFriendSiteInfoS[1].checked) {
                alert('상태를 선택해 주세요');
                return false;
            }

            if (f.OrderName.value == "") {
                alert("주문명을 입력해 주세요");
                f.OrderName.focus();
                return false;
            }

            if (f.Designer.value == "") {
                alert("디자이너명을 입력해 주세요");
                f.Designer.focus();
                return false;
            }

            if (f.OrderStyle.value == "0") {
                alert("주문 상태를 선택해 주세요");
                f.OrderStyle.focus();
                return false;
            }

            if (f.date.value == "") {
                alert("주문 날짜를 입력해 주세요\n\n마우스를 클릭하면 자동입력창이 나옵니다.");
                f.date.focus();
                return false;
            }

            if (f.photofile.value == "") {
                alert("업로드할 이미지를 선택해 주세요.");
                f.photofile.focus();
                return false;
            }

            <?php include "$T_DirFole";
            if ($View_SignMMk == "yes"): ?>
            if (f.pass.value == "") {
                alert("비밀번호를 입력해 주세요.");
                f.pass.focus();
                return false;
            }
            <?php endif; ?>

            return true;
        }

        function Mlamg_image(image) {
            var Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
            Mlangwindow.document.open();
            Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
            Mlangwindow.document.write("<body>");
            Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
            Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='창 닫기' onClick='window.close()'></p>");
            Mlangwindow.document.write("</body></html>");
            Mlangwindow.document.close();
        }

        function MlangFriendSiteInfocheck() {
            var f = document.MlangFriendSiteInfo;
            var ThingNoVal;
            if (f.MlangFriendSiteInfoS[0].checked) {
                ThingNoVal = "<select name='Thing' OnChange='inThing(this.value)'>";
                <?php include "../../MlangPrintAuto/ConDb.php";
                if ($ConDb_A) {
                    $OrderCate_LIST_script = explode(":", $ConDb_A);
                    foreach ($OrderCate_LIST_script as $cate) {
                        $selected = $OrderCate == $cate ? "selected style='background-color:#000000; color:#FFFFFF;'" : "";
                        echo "ThingNoVal += \"<option value='$cate' $selected>$cate</option>\";";
                    }
                }
                ?>
                ThingNoVal += "</select>";
                document.getElementById('Mlang_go').innerHTML = ThingNoVal;
            }
            if (f.MlangFriendSiteInfoS[1].checked) {
                ThingNoVal = "<input type='text' name='Thing' size='30' onblur='inThing(this.value)'>";
                document.getElementById('Mlang_go').innerHTML = ThingNoVal;
            }
        }

        function inThing(HYO) {
            var f = document.MlangFriendSiteInfo;
            f.ThingNo.value = HYO;
        }
    </script>
</head>

<body class='coolBar'>
    <table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='<?php echo $Bgcolor_1?>'>
        <form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' onsubmit='return MlangFriendSiteCheckField()' action='<?php echo $PHP_SELF?>'>
            <input type="hidden" name='mode' value='AdminMlangOrdertOk'>
            <input type="hidden" name='no' value='<?php echo $no?>'>
            <?php if ($ModifyCode): ?>
            <input type="hidden" name='ModifyCode' value='ok'>
            <?php endif; ?>

            <tr>
                <td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>주문/견적 - 테스트 용</font></td>
            </tr>

            <tr>
                <td bgcolor='#6699CC' align=right>상태&nbsp;</td>
                <td>
                    <input type="radio" name="MlangFriendSiteInfoS" onclick='MlangFriendSiteInfocheck()'>고객입력
                    <input type="radio" name="MlangFriendSiteInfoS" onclick='MlangFriendSiteInfocheck()'>관리자입력
                    <input type='hidden' name='ThingNo'>
                    <br>
                    <table border=0 align=center width=100% cellpadding=5 cellspacing=0>
                        <tr>
                            <td id='Mlang_go'></td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td bgcolor='#6699CC' align=right>주문명&nbsp;</td>
                <td>
                    <input type="text" name="OrderName" size=20>
                    <font style='color:#363636; font-size:8pt;'>(주문명은 사용자가 확인할 수 있습니다)</font>
                </td>
            </tr>

            <tr>
                <td bgcolor='#6699CC' align=right>디자이너명&nbsp;</td>
                <td><input type="text" name="Designer" size=20></td>
            </tr>

            <tr>
                <td bgcolor='#6699CC' align=right>주문상태&nbsp;</td>
                <td>
                    <select name='OrderStyle'>
                        <option value='0'>:::선택:::</option>
                        <option value='6'>견적</option>
                        <option value='7'>주문</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td bgcolor='#6699CC' align=right>주문 날짜&nbsp;</td>
                <td><input type="text" name="date" size=20 onclick="Calendar(this);"><font style='color:#363636; font-size:8pt;'>(입력예: 2005-08-10)</font></td>
            </tr>

            <tr>
                <td bgcolor='#6699CC' align=right>이미지 파일&nbsp;</td>
                <td><input type="hidden" name="photofileModify" value='ok'><input type="file" size=45 name="photofile" onchange="Mlamg_image(this.value)"></td>
            </tr>

            <?php if ($View_SignMMk == "yes"): ?>
            <tr>
                <td bgcolor='#6699CC' align=right>비밀번호&nbsp;</td>
                <td><input type="text" size=25 name="pass"></td>
            </tr>
            <?php endif; ?>

            <tr>
                <td align=center colspan=2><?php if ($ModifyCode): ?>
                    <input type='submit' value=' 수정 완료 '>
                    <?php else: ?>
                    <input type='submit' value=' 등록 완료 '>
                    <?php endif; ?>
                </td>
            </tr>
        </form>
    </table>
    <br>
</body>

<?php
} elseif ($mode == "AdminMlangOrdertOk") {
    $ToTitle = $ThingNo;
    include "../../MlangPrintAuto/ConDb.php";

    if (!$ThingNoOkp) {
        $ThingNoOkp = $ThingNo;
    } else {
        $ThingNoOkp = $View_TtableB;
    }

    $result = $db->query("SELECT max(no) FROM MlangOrder_PrintAuto");
    $row = $result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
    }

    if ($photofile) {
        $upload_dir = $dir;
        include "upload.php";
    }

    $stmt = $db->prepare("INSERT INTO MlangOrder_PrintAuto (no, ThingCate, ImgFolder, Type_1, money_1, money_2, money_3, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, bizname, bank, bankname, cont, date, OrderStyle, pass, Designer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssssssssssssssssi", $new_no, $ThingNoOkp, $ImgFolder, $Type_1, $money_1, $money_2, $money_3, $money_4, $money_5, $OrderName, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, $bank, $bankname, $cont, $date, $OrderStyle, $photofileNAME, $pass, $Designer);

    if (!$stmt->execute()) {
        echo "<script>alert('DB insert failed!'); history.go(-1);</script>";
        exit;
    } else {
        echo "<script>alert('Data submitted successfully.'); opener.parent.location.reload(); window.self.close();</script>";
    }

    $stmt->close();
    $db->close();
    exit;
}
?>