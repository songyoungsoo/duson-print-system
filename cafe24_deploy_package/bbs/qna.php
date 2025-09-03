<?php 
// 세션이 이미 시작되었는지 확인 후 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE HTML>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>이제 무궁한 아이디어 - 디자인 및 프로젝트 관리 솔루션 제공</title>
    <style type="text/css">
        table {
            font-size: 12px;
        }
        a {
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
    <script type="text/javascript">
        function preloadImages() {
            var d = document;
            if (d.images) {
                if (!d.MM_p) d.MM_p = [];
                var i, j = d.MM_p.length, a = preloadImages.arguments;
                for (i = 0; i < a.length; i++)
                    if (a[i].indexOf("#") != 0) { 
                        d.MM_p[j] = new Image; 
                        d.MM_p[j++].src = a[i];
                    }
            }
        }

        function swapImgRestore() {
            var i, x, a = document.MM_sr;
            for (i = 0; a && i < a.length && (x = a[i]) && x.oSrc; i++) 
                x.src = x.oSrc;
        }

        function findObj(n, d) {
            var p, i, x;  
            if (!d) d = document; 
            if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
                d = parent.frames[n.substring(p + 1)].document; 
                n = n.substring(0, p);
            }
            if (!(x = d[n]) && d.all) x = d.all[n];
            for (i = 0; !x && i < d.forms.length; i++) 
                x = d.forms[i][n];
            for (i = 0; !x && d.layers && i < d.layers.length; i++) 
                x = findObj(n, d.layers[i].document);
            if (!x && d.getElementById) x = d.getElementById(n);
            return x;
        }

        function swapImage() {
            var i, j = 0, x, a = swapImage.arguments; 
            document.MM_sr = [];
            for (i = 0; i < (a.length - 2); i += 3)
                if ((x = findObj(a[i])) != null) {
                    document.MM_sr[j++] = x; 
                    if (!x.oSrc) x.oSrc = x.src; 
                    x.src = a[i + 2];
                }
        }
    </script>
</head>

<body background="../img/bg.gif" onload="preloadImages('../img/main_m1a.jpg','../img/main_m2a.jpg','../img/main_m3a.jpg','../img/main_m5a.jpg','../img/main_m6a.jpg','../img/main_m7a.jpg','../img/main_m8a.jpg','../img/main_m10a.jpg','../img/main_m11a.jpg')">
    <table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr> 
            <td width="990" valign="top">
                <!-- 상단 이미지 포함 -->
                <?php include $_SERVER['DOCUMENT_ROOT'] . "/top5.php"; ?>
                <!-- 상단 이미지 끝 -->
            </td>
        </tr>
        <tr> 
            <td height="10"></td>
        </tr>
    </table>

    <table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td width="160" height="1" valign="top">
                <p>
                    <!-- 좌측 메뉴 포함 -->
                    <?php include $_SERVER['DOCUMENT_ROOT'] . "/left.htm"; ?>
                    <!-- 좌측 메뉴 끝 -->
                </p>
            </td>
            <td width="9"><img src="/img/space.gif" width="9" height="9"></td>
            <td valign="top">
                <!-- 메인 콘텐츠 시작 -->
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="692" valign="top" align="center">
                            <!-- 버튼 링크 섹션 -->
                            <table width="692" border="0" cellspacing="0" cellpadding="0">
                                <tr> 
                                    <td><a href="leaflet.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image22','','../img/main_m10a.jpg',1)"><img src="../img/main_m10.jpg" name="Image22" width="77" height="32" border="0"></a></td>
                                    <td><a href="sticker.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image25','','../img/main_m7a.jpg',1)"><img src="../img/main_m7.jpg" name="Image25" width="77" height="32" border="0"></a></td>
                                    <td><a href="catalog.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image20','','../img/main_m2a.jpg',1)"><img src="../img/main_m2.jpg" name="Image20" width="77" height="32" border="0"></a></td>
                                    <td><a href="brochure.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image21','','../img/main_m3a.jpg',1)"><img src="../img/main_m3.jpg" name="Image21" width="77" height="32" border="0"></a></td>
                                    <td><a href="bookdesign.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image26','','../img/main_m8a.jpg',1)"><img src="../img/main_m8.jpg" name="Image26" width="77" height="32" border="0"></a></td>
                                    <td><a href="poster.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image27','','../img/main_m11a.jpg',1)"><img src="../img/main_m11.jpg" name="Image27" width="76" height="32" border="0"></a></td>
                                    <td><a href="namecard.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image23','','../img/main_m5a.jpg',1)"><img src="../img/main_m5.jpg" name="Image23" width="77" height="32" border="0"></a></td>
                                    <td><a href="envelope.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image24','','../img/main_m6a.jpg',1)"><img src="../img/main_m6.jpg" name="Image24" width="77" height="32" border="0"></a></td>              
                                    <td><a href="seosig.php" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Image19','','../img/main_m1a.jpg',1)"><img src="../img/main_m1.jpg" name="Image19" width="77" height="32" border="0"></a></td>
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
                        <td valign="top">
                            <p><img src="../img/t_qna.gif" width="692" height="59"></p>
                            <table width="692" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td height="60">
                                        <!-- 게시판 섹션 -->
                                        <?php
                                        // 디버깅을 위한 오류 표시 설정
                                        error_reporting(E_ALL);
                                        ini_set('display_errors', 1);
                                        
                                        // 게시판 설정
                                        $BBS_CODE = "qna";	
                                        $BbsDir = "../bbs/";
                                        $DbDir = "../";
                                        $table = $BBS_CODE;
                                        
                                        // 데이터베이스 연결 확인
                                        include_once("$DbDir/db.php");
                                        
                                        // 모드 확인
                                        $mode = isset($_GET['mode']) ? $_GET['mode'] : '';
                                        $no = isset($_GET['no']) ? intval($_GET['no']) : 0;
                                        
                                        // 게시판 관련 변수 직접 설정
                                        $BBS_ADMIN_title = "고객문의";
                                        $BBS_ADMIN_skin = "board";
                                        $BBS_ADMIN_td_width = "100%";
                                        $BBS_ADMIN_td_color1 = "#000000";
                                        $BBS_ADMIN_td_color2 = "#FFFFFF";
                                        $BBS_ADMIN_recnum = 15;
                                        $BBS_ADMIN_lnum = 8;
                                        $BBS_ADMIN_cutlen = 100;
                                        $BBS_ADMIN_New_Article = 3;
                                        $BBS_ADMIN_date_select = "yes";
                                        $BBS_ADMIN_name_select = "yes";
                                        $BBS_ADMIN_count_select = "yes";
                                        $BBS_ADMIN_recommendation_select = "yes";
                                        $BBS_ADMIN_secret_select = "yes";
                                        $BBS_ADMIN_write_select = "guest";
                                        
                                        // 모드에 따라 다른 처리
                                        if ($mode == 'view' && $no > 0) {
                                            // 조회수 증가
                                            mysqli_query($db, "UPDATE qna SET count = count + 1 WHERE no = $no");
                                            
                                            // 게시글 상세 정보 가져오기
                                            $view_result = mysqli_query($db, "SELECT * FROM qna WHERE no = $no");
                                            
                                            if ($view_result && mysqli_num_rows($view_result) > 0) {
                                                $view_row = mysqli_fetch_assoc($view_result);
                                                
                                                echo "<table border=0 align=center width=100% cellpadding='3' cellspacing='1' bgcolor='#CCCCCC'>";
                                                echo "<tr bgcolor='#FFFFFF'>";
                                                echo "<td width='120' align='center' bgcolor='#F2F2F2'><b>제목</b></td>";
                                                echo "<td>" . htmlspecialchars($view_row['title']) . "</td>";
                                                echo "</tr>";
                                                
                                                echo "<tr bgcolor='#FFFFFF'>";
                                                echo "<td align='center' bgcolor='#F2F2F2'><b>작성자</b></td>";
                                                echo "<td>" . htmlspecialchars($view_row['name']) . "</td>";
                                                echo "</tr>";
                                                
                                                echo "<tr bgcolor='#FFFFFF'>";
                                                echo "<td align='center' bgcolor='#F2F2F2'><b>작성일</b></td>";
                                                echo "<td>" . $view_row['date'] . "</td>";
                                                echo "</tr>";
                                                
                                                echo "<tr bgcolor='#FFFFFF'>";
                                                echo "<td align='center' bgcolor='#F2F2F2'><b>조회수</b></td>";
                                                echo "<td>" . $view_row['count'] . "</td>";
                                                echo "</tr>";
                                                
                                                echo "<tr bgcolor='#FFFFFF'>";
                                                echo "<td colspan='2' height='200' valign='top' style='padding:20px;'>";
                                                echo nl2br(htmlspecialchars($view_row['content']));
                                                echo "</td>";
                                                echo "</tr>";
                                                echo "</table>";
                                                
                                                echo "<p align='center'>";
                                                echo "<a href='qna.php'><img src='$BbsDir/img/list.gif' border=0 align=absmiddle></a> ";
                                                echo "<a href='qna.php?mode=write'><img src='$BbsDir/img/write.gif' border=0 align=absmiddle></a>";
                                                echo "</p>";
                                            } else {
                                                echo "<p align='center'><b>존재하지 않는 게시글입니다.</b></p>";
                                                echo "<p align='center'><a href='qna.php'>목록으로</a></p>";
                                            }
                                        } 
                                        else if ($mode == 'write') {
                                            // 글쓰기 폼
                                            echo "<form name='writeForm' method='post' action='qna.php?mode=write_ok'>";
                                            echo "<table border=0 align=center width=100% cellpadding='3' cellspacing='1' bgcolor='#CCCCCC'>";
                                            
                                            echo "<tr bgcolor='#FFFFFF'>";
                                            echo "<td width='120' align='center' bgcolor='#F2F2F2'><b>제목</b></td>";
                                            echo "<td><input type='text' name='title' size='50' style='width:100%'></td>";
                                            echo "</tr>";
                                            
                                            echo "<tr bgcolor='#FFFFFF'>";
                                            echo "<td align='center' bgcolor='#F2F2F2'><b>작성자</b></td>";
                                            echo "<td><input type='text' name='name' size='20'></td>";
                                            echo "</tr>";
                                            
                                            echo "<tr bgcolor='#FFFFFF'>";
                                            echo "<td align='center' bgcolor='#F2F2F2'><b>비밀번호</b></td>";
                                            echo "<td><input type='password' name='password' size='20'> (수정/삭제시 필요)</td>";
                                            echo "</tr>";
                                            
                                            echo "<tr bgcolor='#FFFFFF'>";
                                            echo "<td align='center' bgcolor='#F2F2F2'><b>내용</b></td>";
                                            echo "<td><textarea name='content' rows='15' style='width:100%'></textarea></td>";
                                            echo "</tr>";
                                            
                                            echo "<tr bgcolor='#FFFFFF'>";
                                            echo "<td colspan='2' align='center'>";
                                            echo "<input type='submit' value='등록'> ";
                                            echo "<input type='button' value='취소' onclick='history.back()'>";
                                            echo "</td>";
                                            echo "</tr>";
                                            
                                            echo "</table>";
                                            echo "</form>";
                                        }
                                        else if ($mode == 'write_ok') {
                                            // 글쓰기 처리
                                            $title = isset($_POST['title']) ? mysqli_real_escape_string($db, $_POST['title']) : '';
                                            $name = isset($_POST['name']) ? mysqli_real_escape_string($db, $_POST['name']) : '';
                                            $password = isset($_POST['password']) ? mysqli_real_escape_string($db, $_POST['password']) : '';
                                            $content = isset($_POST['content']) ? mysqli_real_escape_string($db, $_POST['content']) : '';
                                            
                                            if (empty($title) || empty($name) || empty($password)) {
                                                echo "<script>alert('제목, 작성자, 비밀번호는 필수 입력사항입니다.'); history.back();</script>";
                                            } else {
                                                $current_date = date('Y-m-d H:i:s');
                                                $insert_query = "INSERT INTO qna (title, name, password, content, date, count) VALUES ('$title', '$name', '$password', '$content', '$current_date', 0)";
                                                
                                                if (mysqli_query($db, $insert_query)) {
                                                    echo "<script>alert('게시글이 등록되었습니다.'); location.href='qna.php';</script>";
                                                } else {
                                                    echo "<script>alert('게시글 등록에 실패했습니다: " . mysqli_error($db) . "'); history.back();</script>";
                                                }
                                            }
                                        }
                                        else {
                                            // 게시판 목록 표시 (기본 모드)
                                            // 테이블 구조 확인 (디버깅 정보 제거)
                                            $describe_result = mysqli_query($db, "DESCRIBE qna");
                                            $columns_info = [];
                                            if ($describe_result) {
                                                while ($col = mysqli_fetch_assoc($describe_result)) {
                                                    $columns_info[$col['Field']] = $col['Type'];
                                                }
                                            }
                                            
                                            // 페이지네이션 설정
                                            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                                            $listcut = 15; // 한 페이지에 표시할 게시글 수
                                            $offset = ($page - 1) * $listcut;
                                            
                                            // 전체 게시글 수 확인
                                            $total_result = mysqli_query($db, "SELECT COUNT(*) as total FROM qna");
                                            $total_row = mysqli_fetch_assoc($total_result);
                                            $total_count = $total_row['total'];
                                            
                                            // 기본 정렬 없이 데이터 가져오기
                                            $result = mysqli_query($db, "SELECT * FROM qna LIMIT $offset, $listcut");
                                            
                                            if (!$result) {
                                                echo "<p style='color:red;'>게시판 데이터를 가져오는데 실패했습니다: " . mysqli_error($db) . "</p>";
                                            } else {
                                                echo "<table border=0 align=center width=100% cellpadding='5' cellspacing='1' style='word-break:break-all;'>";
                                                echo "<tr><td align=left><font style='font-size:9pt;'>(등록자료수: " . mysqli_num_rows($result) . ")</font></td></tr>";
                                                echo "</table>";
                                                
                                                echo "<table border=0 align=center width=100% cellpadding='5' cellspacing='0' bgcolor='#FFFFFF' style='word-break:break-all;'>";
                                                echo "<tr><td width=100% height=1 bgcolor='#000000' height=1 colspan=9></td></tr>";
                                                echo "<tr>";
                                                echo "<td align=center nowrap width=60><font style='font:bold;'>&nbsp;번호&nbsp;</font></td>";
                                                echo "<td align=center width=200><font style='font:bold;'>제목</font></td>";
                                                echo "<td align=center nowrap width=100><font style='font:bold;'>&nbsp;등록인&nbsp;</font></td>";
                                                echo "<td align=center nowrap width=60><font style='font:bold;'>&nbsp;조회수&nbsp;</font></td>";
                                                echo "<td align=center nowrap width=90><font style='font:bold;'>&nbsp;날짜&nbsp;</font></td>";
                                                echo "</tr>";
                                                echo "<tr><td width=100% height=1 bgcolor='#000000' height=1 colspan=9></td></tr>";
                                                
                                                if (mysqli_num_rows($result) > 0) {
                                                    // 첫 번째 행의 키를 확인하여 컬럼 이름 파악
                                                    $first_row = mysqli_fetch_assoc($result);
                                                    mysqli_data_seek($result, 0); // 결과셋 포인터를 처음으로 되돌림
                                                    
                                                    // 디버깅 정보 제거
                                                    
                                                    // 실제 데이터 출력
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo "<tr bgcolor='#FFFFFF'>";
                                                        
                                                        // 컬럼 이름 확인
                                                        $columns = array_keys($row);
                                                        
                                                        // ID 컬럼 (번호)
                                                        $id_column = isset($row['no']) ? 'no' : (isset($row['id']) ? 'id' : $columns[0]);
                                                        echo "<td nowrap width=60 align=center>" . $row[$id_column] . "</td>";
                                                        
                                                        // 제목 컬럼 - 이미지에서 보이는 필드명 확인
                                                        $title_column = isset($row['topic']) ? 'topic' : (isset($row['subject']) ? 'subject' : (isset($row['title']) ? 'title' : (isset($row['contents']) ? 'contents' : $columns[1])));
                                                        
                                                        // 제목 인코딩 처리
                                                        $title_text = $row[$title_column];
                                                        if (!mb_check_encoding($title_text, 'UTF-8')) {
                                                            $title_text = mb_convert_encoding($title_text, 'UTF-8', 'EUC-KR');
                                                        }
                                                        
                                                        echo "<td><a href='qna.php?mode=view&no=" . $row[$id_column] . "' class='bbs'>" . htmlspecialchars($title_text) . "</a></td>";
                                                        
                                                        // 작성자 컬럼
                                                        $name_column = isset($row['writer']) ? 'writer' : (isset($row['name']) ? 'name' : $columns[2]);
                                                        
                                                        // 작성자 인코딩 처리
                                                        $name_text = $row[$name_column];
                                                        if (!mb_check_encoding($name_text, 'UTF-8')) {
                                                            $name_text = mb_convert_encoding($name_text, 'UTF-8', 'EUC-KR');
                                                        }
                                                        
                                                        echo "<td align=center nowrap width=100 height=25>" . htmlspecialchars($name_text) . "</td>";
                                                        
                                                        // 조회수 컬럼
                                                        $count_column = isset($row['hit']) ? 'hit' : (isset($row['count']) ? 'count' : (isset($row['views']) ? 'views' : $columns[3]));
                                                        echo "<td align=center nowrap width=60>" . $row[$count_column] . "</td>";
                                                        
                                                        // 날짜 컬럼
                                                        $date_column = isset($row['regdate']) ? 'regdate' : (isset($row['date']) ? 'date' : (isset($row['wdate']) ? 'wdate' : $columns[4]));
                                                        
                                                        // 날짜 형식 확인 및 처리
                                                        $date_value = $row[$date_column];
                                                        if (strlen($date_value) > 10) {
                                                            // 날짜 형식이 YYYY-MM-DD HH:MM:SS 형태인 경우
                                                            $date_display = substr($date_value, 0, 10);
                                                        } else {
                                                            // 다른 형식의 날짜인 경우
                                                            $date_display = $date_value;
                                                        }
                                                        
                                                        echo "<td align=center nowrap width=90>" . $date_display . "</td>";
                                                        
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5' align='center'><b>등록 자료없음</b></td></tr>";
                                                }
                                                
                                                echo "</table>";
                                                
                                                // 페이지네이션 추가
                                                $total_pages = ceil($total_count / $listcut);
                                                $pagecut = 10; // 한 번에 표시할 페이지 링크 수
                                                
                                                echo "<p align='center'>";
                                                echo "<font style='font-size:10pt;'>";
                                                
                                                // 이전 페이지 그룹으로 이동
                                                $start_page = floor(($page - 1) / $pagecut) * $pagecut + 1;
                                                $end_page = min($start_page + $pagecut - 1, $total_pages);
                                                
                                                if ($start_page > 1) {
                                                    $prev_group = $start_page - 1;
                                                    echo "<a href='qna.php?page=$prev_group'><img src='$BbsDir/img/left.gif' border=0 align=absmiddle></a>&nbsp;";
                                                }
                                                
                                                // 페이지 번호 표시
                                                for ($i = $start_page; $i <= $end_page; $i++) {
                                                    if ($i == $page) {
                                                        echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
                                                    } else {
                                                        echo "&nbsp;<a href='qna.php?page=$i'>($i)</a>&nbsp;";
                                                    }
                                                }
                                                
                                                // 다음 페이지 그룹으로 이동
                                                if ($end_page < $total_pages) {
                                                    $next_group = $end_page + 1;
                                                    echo "&nbsp;<a href='qna.php?page=$next_group'><img src='$BbsDir/img/right.gif' border=0 align=absmiddle></a>";
                                                }
                                                
                                                echo "&nbsp;&nbsp;총목록갯수: $total_pages 개";
                                                echo "</font>";
                                                echo "</p>";
                                                
                                                echo "<p align='center'>";
                                                echo "<a href='qna.php?mode=write'><img src='$BbsDir/img/write.gif' border=0 align=absmiddle></a>";
                                                echo "</p>";
                                            }
                                        }
                                        
                                        // bbs.php 파일은 이미 직접 구현했으므로 포함하지 않음
                                        ?>
                                        <!-- 게시판 섹션 끝 -->
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table> 
                <br>
            </td>
            <td width="9">&nbsp;</td>
            <td width="120" valign="top">
                <!-- 우측 메뉴 포함 -->
                <?php include $_SERVER['DOCUMENT_ROOT'] . "/right.htm"; ?>
                <!-- 우측 메뉴 끝 -->
            </td>
        </tr>
    </table>
    <!-- 하단 섹션 포함 -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/bottom.htm"; ?>
    <!-- 하단 섹션 끝 -->
</body>
</html>
