<?php
session_start();

/*
// 보안 시스템 임시 비활성화 - 필요시 주석 해제
// Check authentication
if (!isset($_SESSION['checkboard_authenticated']) || $_SESSION['checkboard_authenticated'] !== true) {
    header('Location: checkboard_auth.php');
    exit;
}

// Check session timeout (8 hours)
if (isset($_SESSION['auth_timestamp']) && (time() - $_SESSION['auth_timestamp']) > 28800) {
    session_destroy();
    header('Location: checkboard_auth.php?timeout=1');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: checkboard_auth.php?logout=1');
    exit;
}

// Update last activity timestamp
$_SESSION['auth_timestamp'] = time();
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>두손기획 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다.</title>
  <meta http-equiv="Content-Type" content="text/html; ">
  <meta charset="utf-8">
  <style type="text/css">
    table {
      font-size: 12px;
      color: #666;
    }

    a:link {
      color: #333333;
      text-decoration: none;
    }

    a:hover {
      color: #666666;
      text-decoration: none;
    }

    a:visited {
      color: #666666;
      text-decoration: none;
    }
  </style>
  <script language="JavaScript" type="text/JavaScript">
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
  </script>
</head>

<body background="/img/bg.gif" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="MM_preloadImages('../img/main_m1a.jpg','../img/main_m2a.jpg','../img/main_m3a.jpg','../img/main_m5a.jpg','../img/main_m6a.jpg','../img/main_m7a.jpg','../img/main_m8a.jpg','../img/main_m10a.jpg','../img/main_m11a.jpg')">
  <table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr background="/img/bg.gif">
      <td width="990" valign="top">
        <!--메인 이미지 로고 시작 -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/top5.php" ?>
        <!-- 메인 이미지 로고  끝 -->
      </td>
    </tr>
    <tr>
      <td height="10"></td>
    </tr>
  </table>

  <!-- <map name="Map2">
    <area shape="rect" coords="4,7,162,127" href="#">
    <area shape="rect" coords="165,7,323,127" href="#">
    <area shape="rect" coords="4,133,162,253" href="#">
    <area shape="rect" coords="165,133,323,253" href="#">
    <area shape="rect" coords="326,7,484,127" href="#">
    <area shape="rect" coords="325,132,484,253" href="#">
    <area shape="rect" coords="487,7,645,127" href="#">
    <area shape="rect" coords="487,133,645,253" href="#">
  </map> -->
  <table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td width="160" height="1" valign="top">
        <p>
          <!--왼쪽 배너 메뉴  시작-->
          <?php include $_SERVER['DOCUMENT_ROOT'] . "/left.htm" ?>
          <!-- 왼쪽 배너 메뉴 끝 -->
        </p>
      </td>
      <td width="9"><img src="/img/space.gif" width="9" height="9"></td>
      <td valign="top">
        <!--본문 내용 시작-->
        <table border="0" cellpadding="0" cellspacing="0" align="center">
          <tr>
            <td width="692" valign="top">
              <table width="692" border="0" align="center" cellspacing="0" cellpadding="0">
                <tr>
                  <td><a href="leaflet.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image22','','../img/main_m10a.jpg',1)"><img src="../img/main_m10.jpg" name="Image22" width="77" height="32" border="0"></a></td>
                  <td><a href="sticker.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image25','','../img/main_m7a.jpg',1)"><img src="../img/main_m7.jpg" name="Image25" width="77" height="32" border="0"></a></td>
                  <td><a href="catalog.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image20','','../img/main_m2a.jpg',1)"><img src="../img/main_m2.jpg" name="Image20" width="77" height="32" border="0"></a></td>
                  <td><a href="brochure.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image21','','../img/main_m3a.jpg',1)"><img src="../img/main_m3.jpg" name="Image21" width="77" height="32" border="0"></a></td>
                  <td><a href="bookdesign.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image26','','../img/main_m8a.jpg',1)"><img src="../img/main_m8.jpg" name="Image26" width="77" height="32" border="0"></a></td>
                  <td><a href="poster.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image27','','../img/main_m11a.jpg',1)"><img src="../img/main_m11.jpg" name="Image27" width="76" height="32" border="0"></a></td>
                  <td><a href="namecard.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image23','','../img/main_m5a.jpg',1)"><img src="../img/main_m5.jpg" name="Image23" width="77" height="32" border="0"></a></td>
                  <td><a href="envelope.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image24','','../img/main_m6a.jpg',1)"><img src="../img/main_m6.jpg" name="Image24" width="77" height="32" border="0"></a></td>
                  <td><a href="seosig.php" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image19','','../img/main_m1a.jpg',1)"><img src="../img/main_m1.jpg" name="Image19" width="77" height="32" border="0"></a></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td height="1" valign="top" bgcolor="#D2D2D2"></td>
          </tr>
          <tr>
            <td valign="top">&nbsp;</td>
          </tr>
          <tr>
            <td valign="top"> <img src="../img/main_tt_checkboard.jpg" width="692" height="59"></td>
          </tr>
          <!--
          <tr>
            <td valign="top">
              <!-- Security Header (임시 비활성화) -->
              <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 10px; border-radius: 8px; margin: 10px 0; color: white; font-family: 'Noto Sans KR', sans-serif;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <div style="font-size: 14px; font-weight: 600;">
                    🔒 인증된 접근 | 세션 활성화 시간: <?= date('Y-m-d H:i:s', $_SESSION['auth_timestamp']) ?>
                  </div>
                  <div>
                    <a href="?logout=1" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 5px 15px; border-radius: 5px; font-size: 12px; font-weight: 500; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                      🚪 로그아웃
                    </a>
                  </div>
                </div>
              </div>
            </td>
          </tr>
          -->
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>
        <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
          <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
            <tr>
              <td>
              <?php
              $HomeDir = "..";
              include "$HomeDir/db.php";
              ?>
                <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                    <td><img src='/img/12345.gif' width=1 height=5></td>
                  </tr>
                </table>
                <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                    <!-------------- 내용 시작 --------------------------->
                    <td width=100% valign=top><table border=0 align=center width=100% cellpadding='8' cellspacing='3' background='/img/sian_top_line_back.jpg'>
                      <tr>
                        <td align=left><table border=0 cellpadding=2 cellspacing=0 width=100%>
                          <tr>
                            <form method='post' name='TDsearch' onSubmit='javascript:return TDsearchCheckField()' action='<?= $_SERVER["PHP_SELF"] ?>'>
                              <td align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>주문자명 or 업종별:</b>
                                <input type='hidden' name='TDsearch2' value='name'>
                                <input type='text' name='TDsearchValue' size='20'>
                                <input type='submit' value=' 검 색 '></td>
                            </form>
                            <td align=right><script>
              function MM_88jumpMenu(targ, selObj, restore) {
                eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
                if (restore) selObj.selectedIndex = 0;
              }
            </script>
                              <select name="select" onChange="MM_88jumpMenu('parent',this,0)">
                                <option value='<?php echo $_SERVER["PHP_SELF"]; ?>'>:::종류별로자료보기:::</option>
                                <?php
              include "../MlangPrintAuto/ConDb.php";
              if ($ConDb_A) {
                $OrderCate_LIST_script = explode(":", $ConDb_A);
                $k = 0;
                while ($k < sizeof($OrderCate_LIST_script)) {

                  if ($OrderCate == "$OrderCate_LIST_script[$k]") {
                    echo "<option value='" . $_SERVER["PHP_SELF"] . "?OrderCate=$OrderCate_LIST_script[$k]' selected style='background-color:#000000; color:#FFFFFF;'>$OrderCate_LIST_script[$k]</option>";
                  } else {
                    echo "<option value='" . $_SERVER["PHP_SELF"] . "?OrderCate=$OrderCate_LIST_script[$k]'>$OrderCate_LIST_script[$k]</option>";
                  }

                  $k++;
                }
              }
              ?>
                                <option value='<?php echo $_SERVER["PHP_SELF"]; ?>'>== 전체 자료보기 ==</option>
                              </select></td>
                          </tr>
                        </table></td>
                      </tr>
                    </table>
                      <br>
                      <!------------------------------------------- 리스트 시작----------------------------------------->
                      <table border=0 align=center width=100% cellpadding='0' cellspacing='0' style='word-break:break-all;'>
                        <tr>
                          <td align=center><img src='/img/box/A1_TopLeft.gif' width=15 height=31></td>
                          <td align=center background='/img/box/A1_TopBack.gif' width=70 valign=bottom><font style='font:bold; color:#3399FF;'>등록번호</font></td>
                          <td align=center background='/img/box/A1_TopBack.gif' width=139 valign=bottom><font style='font:bold; color:#3399FF;'>분류</font></td>
                          <td align=center background='/img/box/A1_TopBack.gif' width=200 valign=bottom><font style='font:bold; color:#3399FF;'>주문인성함</font></td>
                          <td align=center background='/img/box/A1_TopBack.gif' width=90 valign=bottom><font style='font:bold; color:#3399FF;'>담당자</font></td>
                          <td align=center background='/img/box/A1_TopBack.gif' width=100 valign=bottom><font style='font:bold; color:#3399FF;'>주문날짜</font></td>
                          <td align=center background='/img/box/A1_TopBack.gif' width=100 valign=bottom><font style='font:bold; color:#3399FF;'>처리</font></td>
                          <td align=center background='/img/box/A1_TopBack.gif' width=120 valign=bottom><font style='font:bold; color:#3399FF;'>시안</font></td>
                          <td align=center><img src='/img/box/A1_TopRight.gif' width=16 height=31></td>
                        </tr>
                        <tr>
                          <td background='/img/box/A1_CenterLeft.gif'></td>
                          <td bgcolor='#FFFFFF' height=8 colspan=7></td>
                          <td background='/img/box/A1_CenterRight.gif'></td>
                        </tr>
                        <tr>
                          <td background='/img/box/A1_CenterLeft.gif'></td>
                          <td bgcolor='#C6C6C6' height=2 colspan=7></td>
                          <td background='/img/box/A1_CenterRight.gif'></td>
                        </tr>
                        <?php
  include "../db.php";
  $table = "MlangOrder_PrintAuto";
  $TDsearch = isset($_POST['TDsearch']) ? $_POST['TDsearch'] : null;
$OrderCate = isset($_GET['OrderCate']) ? $_GET['OrderCate'] : null;
$OrderStyleYU9OK = isset($_GET['OrderStyleYU9OK']) ? $_GET['OrderStyleYU9OK'] : null;
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$CountWW = isset($CountWW) ? $CountWW : null;
$TDsearchValue = isset($_POST['TDsearchValue']) ? $_POST['TDsearchValue'] : null;

  if ($TDsearch) { //검색모드일때
    $Mlang_query = "select * from $table where $TDsearch like '%$TDsearchValue%'";
  } else if ($OrderCate) {
    $ToTitle = "$OrderCate";
    include "../MlangPrintAuto/ConDb.php";
    $ThingNoOkp = "$View_TtableB";
    $Mlang_query = "select * from $table where Type='$ThingNoOkp' or Type='$OrderCate'";  //두가지 타입을 모두 검색

  } else if ($OrderStyleYU9OK) {
    $Mlang_query = "select * from $table where OrderStyle='$OrderStyleYU9OK'";
  } else { // 일반모드 일때
    $Mlang_query = "select * from $table";
  }

  //echo $Mlang_query;

  $query = mysqli_query($db, $Mlang_query);
  $recordsu = mysqli_num_rows($query);
  $total = mysqli_affected_rows($db);

  $listcut = 15;  // 한 페이지당 보여줄 목록 게시물 수.
  if (!$offset) $offset = 0;

  if ($CountWW) {
    $result = mysqli_query($db, "$Mlang_query ORDER BY $CountWW $s LIMIT $offset, $listcut");
  } else {
    $result = mysqli_query($db, "$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
  }

  $rows = mysqli_num_rows($result);
  if ($rows) {
    while ($row = mysqli_fetch_array($result)) {
  ?>
                        <tr bgcolor='#FFFFFF'>
                          <td background='/img/box/A1_CenterLeft.gif'></td>
                          <td background='/img/box/A1_CenterBack.gif' height=32 align=center><?= $row['no'] ?></td>
                          <td background='/img/box/A1_CenterBack.gif' align=center><?php if ($row['Type'] == "inserted") { ?>
                            전단지
                            <?php } else if ($row['Type'] == "sticker") { ?>
                            스티카
                            <?php } else if ($row['Type'] == "NameCard") { ?>
                            명함
                            <?php } else if ($row['Type'] == "MerchandiseBond") { ?>
                            상품권
                            <?php } else if ($row['Type'] == "envelope") { ?>
                            봉투
                            <?php } else if ($row['Type'] == "NcrFlambeau") { ?>
                            양식지
                            <?php } else if ($row['Type'] == "cadarok") { ?>
                            카다로그
                            <?php } else if ($row['Type'] == "LittlePrint") { ?>
                            소량인쇄
                            <?php } else {
          echo ($row['Type']);
        } ?></td>
                          <td background='/img/box/A1_CenterBack.gif' align=center><font style='color:#38409B; font-size:10pt;'>
                            <?= htmlspecialchars($row['name']); ?>
                          </font></td>
                          <td background='/img/box/A1_CenterBack.gif' align=center><font style='color:#38409B; font-size:10pt;'>
                            <?= $row['Designer']; ?>
                          </font></td>
                          <td background='/img/box/A1_CenterBack.gif' align=center><?= substr($row['date'], 0, 10); ?></td>
                          <td background='/img/box/A1_CenterBack.gif' align=center><?php if ($row['OrderStyle'] == "2") { ?>
                            접수중..
                            <?php } ?>
                            <?php if ($row['OrderStyle'] == "3") { ?>
                            접수완료
                            <?php } ?>
                            <?php if ($row['OrderStyle'] == "4") { ?>
                            입금대기
                            <?php } ?>
                            <?php if ($row['OrderStyle'] == "5") { ?>
                            시안제작중
                            <?php } ?>
                            <?php if ($row['OrderStyle'] == "6") { ?>
                            시안
                            <?php } ?>
                            <?php if ($row['OrderStyle'] == "7") { ?>
                            교정
                            <?php } ?>
                            <?php if ($row['OrderStyle'] == "8") { ?>
                            작업완료
                            <?php } ?>
                            <?php if ($row['OrderStyle'] == "9") { ?>
                            작업중
                            <?php } ?>
                            <?php if ($row['OrderStyle'] == "10") { ?>
                            교정작업중
                            <?php } ?></td>
                          <td background='/img/box/A1_CenterBack.gif' align=center><a href='#' onClick="javascript:popup=window.open('/MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=<?= $row['no'] ?>', 'MViertWasd','width=900,height=400,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();"><img src='/img/button/sian.gif' border=0 align='absmiddle'></a></td>
                          <td background='/img/box/A1_CenterRight.gif'></td>
                        </tr>
                        <tr>
                          <td background='/img/box/A1_CenterLeft.gif'></td>
                          <td height=1 bgcolor='#A4D1FF' background='/img/left_menu_back_134ko.gif' colspan=7></td>
                          <td background='/img/box/A1_CenterRight.gif'></td>
                        </tr>
                        <?php
    }
  }
  ?>
                        <?php
$i = 1;
if ($rows) {
    while ($i < $rows) {
        $i = $i + 1;
    }
} else {
    if ($TDsearchValue) { // 회원 간단검색 TDsearch //  TDsearchValue
        echo "<tr><td colspan=10><p align=center><BR><BR>$TDsearch 로 검색되는 $TDsearchValue - 관련 검색 자료없음</p></td></tr>";
    } else if ($OrderCate) {
        echo "<tr><td colspan=10><p align=center><BR><BR>" . $OrderCate . "(으)로 검색되는 - 관련 검색 자료없음</p></td></tr>";
    } else {
        echo "<tr><td colspan=10><p align=center><BR><BR>등록 자료없음</p></td></tr>";
    }
}
?>
                        <tr>
                          <td align=center><img src='/img/box/A1_DownLeft.gif' width=15 height=12></td>
                          <td background='/img/box/A1_DownBack.gif' colspan=7></td>
                          <td align=center><img src='/img/box/A1_DownRight.gif' width=16 height=12></td>
                        </tr>
                      </table>
                      <p align='center'>
                        <?php
// Initialize $TDsearchValue if it's not set
$TDsearchValue = isset($_POST['TDsearchValue']) ? $_POST['TDsearchValue'] : null;

// Use $_SERVER['PHP_SELF'] instead of $PHP_SELF
$PHP_SELF = isset($_SERVER['PHP_SELF']) ? htmlspecialchars($_SERVER['PHP_SELF']) : '';

// Initialize $mlang_pagego with appropriate parameters
$mlang_pagego = ''; // Initialize it with an empty string or null
if ($TDsearchValue) {
    $mlang_pagego = "TDsearch=$TDsearch&TDsearchValue=$TDsearchValue";
} elseif ($OrderStyleYU9OK) {
    $mlang_pagego = "OrderStyleYU9OK=$OrderStyleYU9OK";
} elseif ($OrderCate) {
    $mlang_pagego = "OrderCate=$OrderCate";
}

if ($rows) {
    if ($TDsearchValue) {
        $mlang_pagego = "TDsearch=$TDsearch&TDsearchValue=$TDsearchValue"; // 필드속성들 전달값
    } else if ($OrderStyleYU9OK) {
        $mlang_pagego = "OrderStyleYU9OK=$OrderStyleYU9OK"; // 필드속성들 전달값
    } else if ($OrderCate) {
        $mlang_pagego = "OrderCate=$OrderCate"; // 필드속성들 전달값
    } else {
    }

    $pagecut = 7;  //한 장당 보여줄 페이지수 
    $one_bbs = $listcut * $pagecut;  //한 장당 실을 수 있는 목록(게시물)수 
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  //각 장에 처음 페이지의 $offset값. 
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;  //마지막 장의 첫페이지의 $offset값. 
    $start_page = intval($start_offset / $listcut) + 1; //각 장에 처음 페이지의 값. 
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>◀</a>";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;

        if ($offset != $newoffset) {
            echo "<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>[$i]</a>";
        } else {
            echo ("<font style='font:bold; color:green;'>[$i]</font>");
        }

        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>▶</a>";
    }
    echo " 총페이지  : $end_page 개";
}

mysqli_close($db);
?>
                      </p>
                      <!------------------------------------------- 리스트 끝-----------------------------------------></td>
                    <!-------------- 내용 끄읕 --------------------------->
                  </tr>
                </table></td>
              <td width="9">&nbsp;</td>
              <td width="120" valign="top"><!-- 오른쪽 배너 시작 -->
                <?php include $_SERVER['DOCUMENT_ROOT'] . "/right.htm" ?>
                <!-- 오른쪽 배너 끝 --></td>
            </tr>
          </table>
        
      </table>
      <!-- 하단부분 시작 -->
<?php include $_SERVER['DOCUMENT_ROOT'] . "/bottom.htm" ?>
<!-- 하단부분 끝 -->
</body>
</html>
