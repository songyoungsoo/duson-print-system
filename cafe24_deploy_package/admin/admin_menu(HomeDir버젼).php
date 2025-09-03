<?php
if (!isset($M123)) {
    $M123 = ".";
}
include_once "$M123/../db.php";
include_once "$M123/config.php";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>MlangWeb관리프로그램(3.2)</title>
    <link rel="stylesheet" type="text/css" href="<?php echo $M123?>/styles.css">
    <script src="<?php echo $M123?>/js/coolbar.js" type="text/javascript"></script>
    <script src="<?php echo $M123?>/js/admin_menu.js" type="text/javascript"></script>
    <script>
        function MM_jumpMenu(targ, selObj, restore) {
            eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
            if (restore) selObj.selectedIndex = 0;
        }
    </script>
</head>
<body>

<?php include "$M123/admin_menu.php"; ?>

<table cellspacing="0" cellpadding="0" width="100%" class='coolBar'>
    <tr>
        <td onmouseover="TopMenuHidden()" colspan="3" height="2"></td>
    </tr>
    <tr>
        <td onmouseover="TopMenuHidden()" width="1"></td>
        <td valign="bottom" align="left" width="910">
            <table align="center" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="160" onmouseover="TopMenuVisible('TopMenus1_pnlMyDirect'); this.className='down';" onmouseout="this.className='coolBar';" align="center" height="25" class='coolBar'>
                        <font class='admin_menu'>PAGE 관리기능</font>
                    </td>
                    <td width="150" onmouseover="TopMenuVisible('TopMenus1_pnlBill'); this.className='down';" onmouseout="this.className='coolBar';" align="center" height="25" class='coolBar'>
                        <font class='admin_menu'>게시판 관리기능</font>
                    </td>
                    <td width="150" onmouseover="TopMenuVisible('TopMenus1_pnlService'); this.className='down';" onmouseout="this.className='coolBar';" align="center" height="25" class='coolBar'>
                        <font class='admin_menu'>회원 관리기능</font>
                    </td>
                    <td width="150" onmouseover="TopMenuVisible('TopMenus1_pnlAddService'); this.className='down';" onmouseout="this.className='coolBar';" align="center" height="25" class='coolBar'>
                        <font class='admin_menu'>여성회원 관리기능</font>
                    </td>
                    <td width="150" onmouseover="TopMenuVisible('TopMenus1_pnlAS9'); this.className='down';" onmouseout="this.className='coolBar';" align="center" height="25" class='coolBar'>
                        <font class='admin_menu'>기타 홈페이지 관리</font>
                    </td>
                    <td width="150" onmouseover="TopMenuVisible('TopMenus1_pnlCom9'); this.className='down';" onmouseout="this.className='coolBar';" align="center" height="25" class='coolBar'>
                        <font class='admin_menu'>관리자 환경설정</font>
                    </td>
                </tr>
            </table>
        </td>
        <td onmouseover="TopMenuHidden()"></td>
    </tr>
    <tr>
        <td height="2" colspan="3"></td>
    </tr>
</table>

<div id="TopMenus1_pnlMyDirect" style="z-index:99; position:absolute; display:none; left:5px; top:30px;">
    <table align="center" width="160" cellpadding="2" cellspacing="2" class='coolBar'>
        <tr><td height="3"></td></tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='#' onclick="window.open('<?php echo $Homedir?>/admin/page/page_menu_submit.php?mode=form', 'page_menu_submit', 'width=650,height=200,top=10,left=10,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" class='mune123'>주메뉴 등록</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/page/page_menu_list.php' class='mune123'>주메뉴 LIST/수정/삭제</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/page/page_submit.php?mode=form' class='mune123'>내용 등록</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/page/page_page_list.php' class='mune123'>내용 LIST/수정/삭제</a>
            </td>
        </tr>
        <tr><td onmouseover="TopMenuHidden()" height="1"></td></tr>
    </table>
</div>

<div id="TopMenus1_pnlBill" style="z-index:99; position:absolute; display:none; left:165px; top:30px;">
    <table align="center" width="150" cellpadding="3" cellspacing="2" class='coolBar'>
        <tr><td height="3"></td></tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/bbs_admin.php?mode=list' class='mune123'>생성/관리/삭제</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/results/admin.php?mode=list' class='mune123'>앨범 프로그램</a>
            </td>
        </tr>
        <tr><td onmouseover="TopMenuHidden()" height="1"></td></tr>
    </table>
</div>

<div id="TopMenus1_pnlService" style="z-index:99; position:absolute; display:none; left:315px; top:30px;">
    <table align="center" width="150" cellpadding="2" cellspacing="2" class='coolBar'>
        <tr><td height="3"></td></tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/member/JoinAdmin.php' class='mune123'>회원가입완료메일 관리</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/member/index.php' class='mune123'>회원 LIST/검색/관리</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/member/MaillingJoinAdmin.php' class='mune123'>회원전체메일 관리</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/member/MemberMailling.php?mode=go' class='mune123'>회원전체 메일 보내기</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/member/Free_index.php' class='mune123'>Free Member LIST</a>
            </td>
        </tr>
        <tr><td onmouseover="TopMenuHidden()" height="1"></td></tr>
    </table>
</div>

<div id="TopMenus1_pnlAddService" style="z-index:99; position:absolute; display:none; left:465px; top:30px;">
    <table align="center" width="150" cellpadding="2" cellspacing="2" class='coolBar'>
        <tr><td height="3"></td></tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='#' onclick="window.open('<?php echo $Homedir?>/admin/WomanMember/admin.php?mode=submit', 'WomanMemberForm', 'width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" class='mune123'>여성회원 정보입력</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/WomanMember/index.php' class='mune123'>LIST/검색/관리</a>
            </td>
        </tr>
        <tr><td onmouseover="TopMenuHidden()" height="1"></td></tr>
    </table>
</div>

<div id="TopMenus1_pnlAS9" style="z-index:99; position:absolute; display:none; left:615px; top:30px;">
    <table align="center" width="150" cellpadding="2" cellspacing="2" class='coolBar'>
        <tr><td height="3"></td></tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='<?php echo $Homedir?>/admin/BBSSinGo/index.php' class='mune123'>자료신고함</a>
                <?php
                $resultwebsoft = $db->query("SELECT * FROM BBS_Singo WHERE AdminSelect='1'");
                $rowswebsoft = $resultwebsoft->num_rows;
                if ($rowswebsoft) {
                    echo "(<font style='color:red; font:bold;'>$rowswebsoft</font>)";
                }
                ?>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='#' onclick="window.open('<?php echo $Homedir?>/event/admin.php', 'Tpage', 'width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" class='mune123'>처음페이지POP</a>
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='#' onclick="window.open('<?php echo $Homedir?>/log/login.php?go=admin_counter.php?counter=index', 'TpageLog', 'width=750,height=650,top=0,left=0,menubar=yes,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" class='mune123'>로그 프로그램</a>
            </td>
        </tr>
        <tr><td onmouseover="TopMenuHidden()" height="1"></td></tr>
    </table>
</div>

<div id="TopMenus1_pnlCom9" style="z-index:99; position:absolute; display:none; left:765px; top:30px;">
    <table align="center" width="150" cellpadding="2" cellspacing="2" class='coolBar'>
        <tr><td height="3"></td></tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                &nbsp;<img src='<?php echo $Homedir?>/admin/img/left_icon123.gif' width="4" height="7" align="absmiddle">&nbsp;
                <a href='#' onclick="window.open('<?php echo $Homedir?>/admin/AdminConfig.php?mode=modify', 'AdminConfig', 'width=350,height=150,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=no,toolbar=no');" class='mune123'>관리자 정보변경</a>
            </td>
        </tr>
        <tr><td onmouseover="TopMenuHidden()" height="1"></td></tr>
    </table>
</div>

<div id="TopMenus1_pnlTech9" style="z-index:99; position:absolute; display:none; left:639px; top:30px;">
    <table align="center" width="150" cellpadding="2" cellspacing="2" class='coolBar'>
        <tr><td height="3"></td></tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                메뉴 테스트..
            </td>
        </tr>
        <tr>
            <td onmouseover="this.style.background='#0b77ea';" onmouseout="this.style.background='';">
                메뉴 테스트..
            </td>
        </tr>
        <tr><td onmouseover="TopMenuHidden()" height="1"></td></tr>
    </table>
</div>

</body>
</html>
