<?php
/**
 * 게시판 목록 스킨 - portfolio (갤러리형)
 * 
 * 변수 설명:
 * $BbsDir - 게시판 디렉토리 경로
 * $table - 게시판 테이블명
 * $BBS_ADMIN_* - 게시판 설정 변수들
 */

// 변수 초기화
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$PCode = isset($_GET['PCode']) ? $_GET['PCode'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchstring = isset($_GET['searchstring']) ? $_GET['searchstring'] : '';
$cate = isset($_GET['cate']) ? $_GET['cate'] : '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// 관리자 로그인 여부 확인
$is_admin = false;

// PHP_AUTH_USER와 PHP_AUTH_PW가 설정되어 있는지 확인
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    // 데이터베이스 연결
    $host = "localhost";
    $user = "duson1830";
    $password = "du1830";
    $dataname = "duson1830";
    $admin_db = @mysqli_connect($host, $user, $password, $dataname);
    
    if ($admin_db) {
        // 관리자 정보 가져오기
        $admin_query = "SELECT * FROM member WHERE no='1'";
        $admin_result = mysqli_query($admin_db, $admin_query);
        
        if ($admin_result && mysqli_num_rows($admin_result) > 0) {
            $admin_row = mysqli_fetch_array($admin_result);
            $adminid = $admin_row["id"];
            $adminpasswd = $admin_row["pass"];
            
            // 관리자 인증 확인
            if ($_SERVER['PHP_AUTH_USER'] == $adminid && $_SERVER['PHP_AUTH_PW'] == $adminpasswd) {
                $is_admin = true;
            }
        }
        
        mysqli_close($admin_db);
    }
}
?>

<html>
<head>
<title><?php echo $BBS_ADMIN_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="<?php echo $BbsDir; ?>/skin/<?php echo $BBS_ADMIN_skin; ?>/style.css" type="text/css">
<style>
/* 갤러리 스타일 */
.gallery-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-top: 20px;
}

.gallery-item {
    width: 24%;
    margin-bottom: 20px;
    text-align: center;
    position: relative;
}

.gallery-image-container {
    border: 1px solid #ddd;
    margin-bottom: 5px;
    height: 220px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f9f9f9;
}

.gallery-image {
    max-width: 100%;
    max-height: 100%;
    display: block;
}

.gallery-title {
    font-size: 12px;
    margin-top: 5px;
    height: 32px;
    overflow: hidden;
    text-align: center;
    word-break: keep-all;
}

.gallery-new {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: #ff6600;
    color: white;
    padding: 2px 5px;
    font-size: 10px;
    border-radius: 3px;
}

.gallery-secret {
    position: absolute;
    top: 5px;
    left: 5px;
    background-color: rgba(0,0,0,0.5);
    color: white;
    padding: 2px 5px;
    font-size: 10px;
    border-radius: 3px;
}

/* 라이트박스 스타일 */
.lightbox {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.lightbox.active {
    opacity: 1;
    visibility: visible;
}

.lightbox-content {
    max-width: 90%;
    max-height: 90%;
    position: relative;
}

.lightbox-content img {
    max-width: 100%;
    max-height: 80vh;
    display: block;
    margin: 0 auto;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
}

.lightbox-caption {
    color: white;
    text-align: center;
    padding: 10px;
    font-size: 16px;
    font-weight: bold;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    color: white;
    font-size: 30px;
    cursor: pointer;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    background-color: rgba(0,0,0,0.5);
    border-radius: 50%;
}

.lightbox-close:hover {
    background-color: rgba(255,0,0,0.7);
}

/* 페이징 스타일 */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination a, .pagination span {
    display: inline-block;
    padding: 5px 10px;
    margin: 0 3px;
    border: 1px solid #ddd;
    color: #333;
    text-decoration: none;
}

.pagination .current {
    background-color: #4a6da7;
    color: white;
    border-color: #4a6da7;
}

.pagination a:hover {
    background-color: #f5f5f5;
}

/* 검색 폼 스타일 */
.search-form {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
}

.search-form select, .search-form input[type="text"] {
    padding: 5px;
    border: 1px solid #ddd;
    margin-right: 5px;
}

.search-form input[type="submit"] {
    padding: 5px 10px;
    background-color: #4a6da7;
    color: white;
    border: none;
    cursor: pointer;
}

.search-form input[type="submit"]:hover {
    background-color: #3a5a8c;
}

/* 버튼 스타일 */
.write-button {
    display: inline-block;
    padding: 8px 15px;
    background-color: #4a6da7;
    color: white;
    text-decoration: none;
    border-radius: 3px;
    margin-top: 10px;
}

.write-button:hover {
    background-color: #3a5a8c;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .gallery-item {
        width: calc(50% - 15px);
    }
}

@media (max-width: 480px) {
    .gallery-item {
        width: 100%;
    }
}
</style>
<script language="JavaScript">
function clearField(field) {
    if (field.value == field.defaultValue) {
        field.value = "";
    }
}

function checkField(field) {
    if (!field.value) {
        field.value = field.defaultValue;
    }
}

function SearchCheckField() {
    var f = document.MlangSearch;
    if (f.searchstring.value == "") {
        alert("검색할 검색어 값을 입력해주세요");
        f.searchstring.focus();
        return false;
    }
}
</script>
</head>

<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="<?php echo $BBS_ADMIN_td_width; ?>" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td>
      <!------------------------------------------- 갤러리 시작----------------------------------------->
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td height="25" align="center" valign="bottom">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="250" align="left" valign="bottom">
                  <img src="<?php echo $BbsDir; ?>/skin/<?php echo $BBS_ADMIN_skin; ?>/img/board_tit.gif" width="175" height="30" alt="게시판">
                </td>
                <td align="right" valign="bottom">
                  <form name="MlangSearch" method="get" onsubmit="return SearchCheckField();" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="search-form">
                    <input type="hidden" name="table" value="<?php echo $table; ?>">
                    <input type="hidden" name="mode" value="list">
                    <input type="hidden" name="search" value="yes">
                    <select name="cate">
                      <option value="title">제목</option>
                      <option value="connent">내용</option>
                      <option value="member">등록인</option>
                    </select>
                    <input type="text" name="searchstring" size="12">
                    <input type="submit" value="검색">
                  </form>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td height="10"></td>
        </tr>
        <tr>
          <td>
            <!-- 카테고리 메뉴 -->
            <div class="category-menu">
              <style>
              .category-menu {
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
              }
              .category-menu ul {
                list-style: none;
                padding: 0;
                margin: 0;
                display: flex;
                flex-wrap: wrap;
              }
              .category-menu li {
                margin-right: 10px;
                margin-bottom: 5px;
              }
              .category-menu a {
                display: inline-block;
                padding: 5px 15px;
                background-color: #f5f5f5;
                border: 1px solid #ddd;
                border-radius: 3px;
                color: #333;
                text-decoration: none;
                font-size: 13px;
              }
              .category-menu a:hover {
                background-color: #e9e9e9;
              }
              .category-menu a.active {
                background-color: #4a6da7;
                color: white;
                border-color: #3a5a8c;
              }
              </style>
              <ul>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>" <?php echo empty($_GET['category']) ? 'class="active"' : ''; ?>>전체보기</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=inserted" <?php echo isset($_GET['category']) && $_GET['category'] == 'inserted' ? 'class="active"' : ''; ?>>전단지</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=leaflet" <?php echo isset($_GET['category']) && $_GET['category'] == 'leaflet' ? 'class="active"' : ''; ?>>리플렛</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=namecard" <?php echo isset($_GET['category']) && ($_GET['category'] == 'namecard' || $_GET['category'] == '명함') ? 'class="active"' : ''; ?>>명함</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=sticker" <?php echo isset($_GET['category']) && ($_GET['category'] == 'sticker' || $_GET['category'] == '스티커') ? 'class="active"' : ''; ?>>스티커</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=envelope" <?php echo isset($_GET['category']) && ($_GET['category'] == 'envelope' || $_GET['category'] == '봉투') ? 'class="active"' : ''; ?>>봉투</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=form" <?php echo isset($_GET['category']) && ($_GET['category'] == 'form' || $_GET['category'] == '양식지') ? 'class="active"' : ''; ?>>양식지</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=catalog" <?php echo isset($_GET['category']) && ($_GET['category'] == 'catalog' || $_GET['category'] == '카다로그') ? 'class="active"' : ''; ?>>카달로그</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=brochure" <?php echo isset($_GET['category']) && $_GET['category'] == 'brochure' ? 'class="active"' : ''; ?>>브로슈어</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=bookdesign" <?php echo isset($_GET['category']) && $_GET['category'] == 'bookdesign' ? 'class="active"' : ''; ?>>북디자인</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=poster" <?php echo isset($_GET['category']) && $_GET['category'] == 'poster' ? 'class="active"' : ''; ?>>포스터</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=coupon" <?php echo isset($_GET['category']) && $_GET['category'] == 'coupon' ? 'class="active"' : ''; ?>>쿠폰</a></li>
                <li><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?mode=list&table=<?php echo $table; ?>&category=logo" <?php echo isset($_GET['category']) && $_GET['category'] == 'logo' ? 'class="active"' : ''; ?>>심볼/로고</a></li>
              </ul>
            </div>
          </td>
        </tr>
        <tr>
          <td height="10"></td>
        </tr>
        <tr>
          <td>
        </tr>
        <tr>
          <td height="10"></td>
        </tr>
        <tr>
          <td>
            <?php
            // 데이터베이스 연결 설정
            $host = "localhost";
            $user = "duson1830";
            $password = "du1830";
            $dataname = "duson1830";
            
            // 새 연결 생성
            $local_db = @mysqli_connect($host, $user, $password, $dataname);
            if (!$local_db) {
                echo "<p style='color:red;'>데이터베이스 연결에 실패했습니다.</p>";
            } else {
                mysqli_query($local_db, "SET NAMES 'utf8'");
            
                // 검색 조건 설정
                $where = "";
                
                // 카테고리 필터링
                $category = isset($_GET['category']) ? $_GET['category'] : '';
                if (!empty($category)) {
                    // 카테고리 필드 확인 (실제 데이터베이스 구조에 맞게 수정 필요)
                    $possible_category_fields = ['Mlang_bbs_category', 'category', 'cate', 'Mlang_bbs_cate', 'CATEGORY'];
                    $category_field = 'CATEGORY'; // 기본값
                    
                    // 테이블 구조 확인
                    $table_info_query = "SHOW COLUMNS FROM Mlang_{$table}_bbs";
                    $table_info_result = mysqli_query($local_db, $table_info_query);
                    $category_field_exists = false;
                    
                    if ($table_info_result) {
                        while ($column = mysqli_fetch_array($table_info_result)) {
                            if (in_array($column['Field'], $possible_category_fields)) {
                                $category_field = $column['Field'];
                                $category_field_exists = true;
                                break;
                            }
                        }
                    }
                    
                    // 카테고리 매핑 파일 포함
                    include_once("$BbsDir/category_mapping.php");
                    
                    // 매핑된 카테고리 값 사용 (매핑이 없으면 원래 값 사용)
                    $db_category = isset($category_mapping[$category]) ? $category_mapping[$category] : $category;
                    
                    // 카테고리 필드가 존재하면 필터링 조건 추가
                    if ($category_field_exists) {
                        $where = " WHERE $category_field = '" . mysqli_real_escape_string($local_db, $db_category) . "'";
                    } else {
                        // 카테고리 필드가 없으면 제목에서 검색
                        $where = " WHERE Mlang_bbs_title LIKE '%" . mysqli_real_escape_string($local_db, $db_category) . "%'";
                    }
                }
                
                // 검색 조건 추가
                if($search == "yes") {
                    if (empty($where)) {
                        if($cate == "title") {
                            $where = " WHERE Mlang_bbs_title LIKE '%" . mysqli_real_escape_string($local_db, $searchstring) . "%'";
                        } else if($cate == "connent") {
                            $where = " WHERE Mlang_bbs_connent LIKE '%" . mysqli_real_escape_string($local_db, $searchstring) . "%'";
                        } else if($cate == "member") {
                            $where = " WHERE Mlang_bbs_member LIKE '%" . mysqli_real_escape_string($local_db, $searchstring) . "%'";
                        }
                    } else {
                        if($cate == "title") {
                            $where .= " AND Mlang_bbs_title LIKE '%" . mysqli_real_escape_string($local_db, $searchstring) . "%'";
                        } else if($cate == "connent") {
                            $where .= " AND Mlang_bbs_connent LIKE '%" . mysqli_real_escape_string($local_db, $searchstring) . "%'";
                        } else if($cate == "member") {
                            $where .= " AND Mlang_bbs_member LIKE '%" . mysqli_real_escape_string($local_db, $searchstring) . "%'";
                        }
                    }
                }
                
                // 전체 게시물 수 조회
                $query = "SELECT COUNT(*) AS cnt FROM Mlang_{$table}_bbs" . $where;
                $result = mysqli_query($local_db, $query);
                $row = mysqli_fetch_array($result);
                $total_record = $row ? $row['cnt'] : 0;
                
                // 페이징 설정
                $list = $BBS_ADMIN_recnum;
                $block = $BBS_ADMIN_lnum;
                $total_page = ceil($total_record / $list);
                $block_cnt = ceil($total_page / $block);
                $block_num = ceil($page / $block);
                $block_start = (($block_num - 1) * $block) + 1;
                $block_end = $block_start + $block - 1;
                
                if($block_end > $total_page) {
                    $block_end = $total_page;
                }
                
                $total_block = ceil($total_record / $list);
                $page_start = ($page - 1) * $list;
                
                // 게시물 목록 조회
                $query = "SELECT * FROM Mlang_{$table}_bbs" . $where . " ORDER BY Mlang_bbs_no DESC LIMIT $page_start, $list";
                $result = mysqli_query($local_db, $query);
                
                // 쿼리 실패 시 오류 처리
                if (!$result) {
                    echo '<div style="color:red; padding:20px;">
                        쿼리 실행 중 오류가 발생했습니다: ' . mysqli_error($local_db) . '<br>
                        쿼리: ' . $query . '
                    </div>';
                }
                
                echo '<div class="gallery-container">';
                
                if($result && mysqli_num_rows($result) > 0) {
                    $article_num = $total_record - (($page - 1) * $list);
                    
                    while($row = mysqli_fetch_array($result)) {
                        // 날짜 처리
                        $date = isset($row['Mlang_bbs_date']) ? substr($row['Mlang_bbs_date'], 0, 10) : date('Y-m-d');
                        $subject = isset($row['Mlang_bbs_title']) ? $row['Mlang_bbs_title'] : '제목 없음';
                        
                        // 제목 길이 제한
                        if(strlen($subject) > $BBS_ADMIN_cutlen) {
                            $subject = substr($subject, 0, $BBS_ADMIN_cutlen) . "...";
                        }
                        
                        // 새 글 표시
                        $new_icon = '';
                        if(isset($row['Mlang_bbs_date'])) {
                            $today = date("Y-m-d");
                            $registered = substr($row['Mlang_bbs_date'], 0, 10);
                            $time_lag = strtotime($today) - strtotime($registered);
                            $time_lag = floor($time_lag / 86400);
                            
                            if($time_lag <= $BBS_ADMIN_New_Article) {
                                $new_icon = "<span class='gallery-new'>NEW</span>";
                            }
                        }
                        
                        // 비밀글 처리
                        $secret_icon = '';
                        if(isset($row['Mlang_bbs_secret']) && $row['Mlang_bbs_secret'] == "yes") {
                            $secret_icon = "<span class='gallery-secret'>🔒</span>";
                        }
                        
                        // 이미지 경로 설정
                        $image_path = '';
                        
                        // 이미지 파일명 직접 지정 (테스트용)
                        $test_images = [
                            '0219_ghl.jpg',
                            '0313_poster_027_s.jpg',
                            '0729_catalog_005_s.jpg',
                            '1013_닥터클로명함2ck.jpg',
                            '1128_20080329131128355.bmp'
                        ];
                        
                        // 게시물 번호에 따라 이미지 선택 (일관성 유지)
                        $img_index = isset($row['Mlang_bbs_no']) ? ($row['Mlang_bbs_no'] % count($test_images)) : 0;
                        $selected_image = $test_images[$img_index];
                        
                        // 이미지 경로 설정 - 실제 게시물의 이미지 사용
                        $image_path = "";
                        
                        // 데이터베이스에서 이미지 파일명 필드 확인
                        $possible_image_fields = ['Mlang_bbs_file1', 'Mlang_bbs_file', 'file1', 'file', 'img', 'image'];
                        foreach ($possible_image_fields as $field) {
                            if (isset($row[$field]) && !empty($row[$field])) {
                                // 이미지 파일명이 있으면 해당 경로 사용
                                $image_path = "/bbs/upload/portfolio/" . $row[$field];
                                break;
                            }
                        }
                        
                        // 이미지 파일이 없으면 테스트 이미지 사용
                        if (empty($image_path)) {
                            // 게시물 번호에 따라 테스트 이미지 선택
                            $img_index = isset($row['Mlang_bbs_no']) ? ($row['Mlang_bbs_no'] % count($test_images)) : 0;
                            $image_path = "/bbs/upload/portfolio/" . $test_images[$img_index];
                        }
                        
                        // 대체 이미지 설정 (온라인 이미지)
                        $fallback_image = "https://via.placeholder.com/300x200?text=No+Image";
                        
                        // 갤러리 아이템 출력
                        echo '<div class="gallery-item">';
                        
                        // NEW 아이콘과 비밀글 아이콘
                        echo $new_icon;
                        echo $secret_icon;
                        
                        // 이미지 컨테이너
                        echo '<div class="gallery-image-container">';
                        echo '<img src="' . $image_path . '" alt="' . $subject . '" class="gallery-image" onclick="openLightbox(\'' . $image_path . '\', \'' . htmlspecialchars($subject, ENT_QUOTES) . '\')" style="cursor:pointer;" onerror="this.onerror=null; this.src=\'' . $fallback_image . '\'">';
                        echo '</div>';
                        
                        // 제목
                        echo '<div class="gallery-title">' . $subject . '</div>';
                        
                        // 관리자인 경우에만 수정/삭제 버튼 표시
                        if ($is_admin) {
                            echo '<div style="margin-top:5px;">';
                            echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?mode=write&amp;table=' . $table . '&amp;no=' . $row['Mlang_bbs_no'] . '&amp;page=' . $page . '&amp;PCode=' . $PCode . '" style="font-size:11px; color:#4a6da7; margin-right:5px;">수정</a>';
                            echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?mode=delete&amp;table=' . $table . '&amp;no=' . $row['Mlang_bbs_no'] . '&amp;page=' . $page . '&amp;PCode=' . $PCode . '" style="font-size:11px; color:#d9534f;" onclick="return confirm(\'정말 삭제하시겠습니까?\');">삭제</a>';
                            echo '</div>';
                        }
                        
                        echo '</div>'; // gallery-item
                        
                        $article_num--;
                    }
                } else {
                    echo '<div style="width:100%; text-align:center; padding:50px 0;">등록된 게시물이 없습니다.</div>';
                }
                
                echo '</div>'; // gallery-container
            }
            ?>
          </td>
        </tr>
        <tr>
          <td height="10"></td>
        </tr>
        <tr>
          <td align="center">
            <div class="pagination">
              <?php
              // 페이징 출력
              if($block_num > 1) {
                  $prev_page = ($block_num - 2) * $block + 1;
                  echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=list&table=$table&page=$prev_page&PCode=$PCode&search=$search&cate=$cate&searchstring=$searchstring'>이전</a> ";
              }
              
              for($i = $block_start; $i <= $block_end; $i++) {
                  if($page == $i) {
                      echo "<span class='current'>$i</span> ";
                  } else {
                      echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=list&table=$table&page=$i&PCode=$PCode&search=$search&cate=$cate&searchstring=$searchstring'>$i</a> ";
                  }
              }
              
              if($block_num < $block_cnt) {
                  $next_page = $block_num * $block + 1;
                  echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=list&table=$table&page=$next_page&PCode=$PCode&search=$search&cate=$cate&searchstring=$searchstring'>다음</a>";
              }
              ?>
            </div>
            <div style="margin-top:20px;">
              <?php
              // 모든 사용자가 글쓰기 가능하도록 변경
              echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?mode=write&amp;table=' . $table . '&amp;page=' . $page . '&amp;PCode=' . $PCode . '" class="write-button">글쓰기</a>';
              
              // 관리자인 경우 추가 관리 메뉴 표시
              if ($is_admin) {
                  echo ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?mode=admin&amp;table=' . $table . '" class="write-button" style="background-color:#d9534f;">관리자모드</a>';
              }
              ?>
            </div>
          </td>
        </tr>
      </table>
      <!------------------------------------------- 갤러리 끝----------------------------------------->
    </td>
  </tr>
</table>
<!-- 라이트박스 HTML -->
<div id="image-lightbox" class="lightbox">
  <div class="lightbox-content">
    <img id="lightbox-image" src="" alt="">
    <div class="lightbox-caption" id="lightbox-caption"></div>
  </div>
  <div class="lightbox-close" onclick="closeLightbox()">×</div>
</div>

<script>
// 라이트박스 열기 함수
function openLightbox(imageSrc, caption) {
  document.getElementById('lightbox-image').src = imageSrc;
  document.getElementById('lightbox-caption').textContent = caption;
  document.getElementById('image-lightbox').classList.add('active');
  // 배경 스크롤 방지
  document.body.style.overflow = 'hidden';
}

// 라이트박스 닫기 함수
function closeLightbox() {
  document.getElementById('image-lightbox').classList.remove('active');
  // 스크롤 다시 활성화
  document.body.style.overflow = 'auto';
}

// 이벤트 리스너 등록
document.addEventListener('DOMContentLoaded', function() {
  // 라이트박스 이미지 클릭 시 닫기
  document.getElementById('lightbox-image').addEventListener('click', function() {
    closeLightbox();
  });
  
  // 라이트박스 배경 클릭 시 닫기
  document.getElementById('image-lightbox').addEventListener('click', function(e) {
    if (e.target.id === 'image-lightbox') {
      closeLightbox();
    }
  });
  
  // ESC 키 누르면 라이트박스 닫기
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeLightbox();
    }
  });
});
</script>
</body>
</html>