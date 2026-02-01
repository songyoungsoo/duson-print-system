<?php
declare(strict_types=1);

// PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';
$TDsearch = $_GET['TDsearch'] ?? $_POST['TDsearch'] ?? '';
$TDsearchValue = $_GET['TDsearchValue'] ?? $_POST['TDsearchValue'] ?? '';
$offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;
$money = $_GET['money'] ?? $_POST['money'] ?? '';
$CountWW = $_GET['CountWW'] ?? $_POST['CountWW'] ?? '';
$s = $_GET['s'] ?? $_POST['s'] ?? '';
$cate = $_GET['cate'] ?? $_POST['cate'] ?? '';
$title_search = $_GET['title_search'] ?? $_POST['title_search'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';
$i = 0;

if($mode=="LevelModify"){
    include"../../db.php";
    include"../config.php";
    $code = intval($code);
    $no = intval($no);
    $stmt = $db->prepare("UPDATE users SET level=? WHERE id=?");
    $stmt->bind_param("ii", $code, $no);
    $stmt->execute();
    $stmt->close();
    mysqli_close($db);

    echo ("<script>
    alert('회원의 레벨을 조절하였습니다.');
    location.href='$PHP_SELF?offset=$offset&TDsearch=$TDsearch&TDsearchValue=$TDsearchValue';
    </script>");
    exit;
}

$M123="..";
include"../top.php";
?>

<link rel="stylesheet" href="css/member-list.css">

<script>
function Member_Admin_Del(no){
    if (confirm(no+'번 회원을 탈퇴처리 하시겠습니까?\n\n한번 삭제한 자료는 복구되지 않습니다.')) {
        var popup = window.open('admin.php?no='+no+'&mode=delete', '', 'width=400,height=50,top=2000,left=2000');
        popup.focus();
    }
}

function TDsearchCheckField(){
    var f = document.TDsearch;
    if (f.TDsearchValue.value.trim() === "") {
        alert("검색어를 입력해주세요");
        f.TDsearchValue.focus();
        return false;
    }
    return true;
}
</script>

<div class="member-container">
    <!-- Header -->
    <div class="member-header">
        <h1>👥 회원 관리</h1>
        <div class="member-stats">
             <?php
             include"../../db.php";
             $totalQuery = mysqli_query($db, "SELECT COUNT(*) as total FROM users");
             $totalRow = mysqli_fetch_assoc($totalQuery);
             $totalMembers = intval($totalRow['total']);
             ?>
             <span>총 회원: <?= number_format($totalMembers) ?>명</span>
         </div>
    </div>

    <!-- Toolbar -->
    <div class="member-toolbar">
        <form method='post' name='TDsearch' onsubmit='return TDsearchCheckField()' action='<?=$PHP_SELF?>' class="search-group">
            <label>검색:</label>
            <select name='TDsearch'>
                <option value='id' <?= $TDsearch == 'id' ? 'selected' : '' ?>>아이디</option>
                <option value='name' <?= $TDsearch == 'name' ? 'selected' : '' ?>>이름</option>
                <option value='email' <?= $TDsearch == 'email' ? 'selected' : '' ?>>이메일</option>
            </select>
            <input type='text' name='TDsearchValue' placeholder="검색어 입력" value="<?= htmlspecialchars($TDsearchValue) ?>">
            <button type='submit' class="btn btn--primary btn--sm">검색</button>
            <?php if($TDsearchValue): ?>
            <a href="<?=$PHP_SELF?>" class="btn btn--secondary btn--sm">초기화</a>
            <?php endif; ?>
        </form>

        <div class="sort-buttons">
             <button class="btn btn--secondary btn--sm" onclick="location.href='<?=$PHP_SELF?>?offset=<?=$offset?>&CountWW=login_count&s=desc'">방문순 ↓</button>
             <button class="btn btn--secondary btn--sm" onclick="location.href='<?=$PHP_SELF?>?offset=<?=$offset?>&CountWW=login_count&s=asc'">방문순 ↑</button>
         </div>
    </div>

    <!-- Table -->
    <div class="member-table-wrapper">
        <table class="member-table">
            <thead>
                 <tr>
                     <th>번호</th>
                     <th>아이디</th>
                     <th>이름</th>
                     <th>방문수</th>
                     <th>최종방문</th>
                     <th>가입일</th>
                     <th>레벨</th>
                     <th>관리</th>
                 </tr>
             </thead>
            <tbody>
<?php
$table = "users";

if($TDsearchValue){
    $TDsearch = mysqli_real_escape_string($db, $TDsearch);
    $TDsearchValue_esc = mysqli_real_escape_string($db, $TDsearchValue);
    
    if($TDsearch === 'id'){
        $searchColumn = 'username';
    } else {
        $searchColumn = $TDsearch;
    }
    
    $Mlang_query = "SELECT * FROM $table WHERE $searchColumn LIKE '%$TDsearchValue_esc%'";
} else {
    $Mlang_query = "SELECT * FROM $table";
}

$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);

$listcut = 30;
if(!$offset) $offset = 0;

if($CountWW){
    $CountWW = mysqli_real_escape_string($db, $CountWW);
    $s = mysqli_real_escape_string($db, $s);
    $result = mysqli_query($db, "$Mlang_query ORDER BY $CountWW $s LIMIT $offset,$listcut");
} else {
    $result = mysqli_query($db, "$Mlang_query ORDER BY id DESC LIMIT $offset,$listcut");
}

$rows = mysqli_num_rows($result);
if($rows){
    while($row = mysqli_fetch_array($result)){
         $levelClass = 'level-' . ($row['level'] ?? 5);
         $levelNames = [2 => '부운영자', 3 => '골드', 4 => '정회원', 5 => '일반'];
         $levelName = $levelNames[$row['level']] ?? '일반';

         $visitCount = intval($row['login_count'] ?? 0);
         $visitClass = $visitCount >= 100 ? 'high' : '';
?>
                 <tr>
                     <td class="col-no"><?= $row['id'] ?></td>
                     <td class="col-id">
                         <a href="#" onclick="window.open('MemberImail.php?no=<?=$row['id']?>&code=1', 'member_email','width=600,height=500'); return false;">
                             <?= htmlspecialchars($row['username']) ?>
                         </a>
                     </td>
                     <td class="col-name"><?= htmlspecialchars($row['name']) ?></td>
                     <td class="col-visit">
                         <span class="visit-badge <?= $visitClass ?>"><?= number_format($visitCount) ?></span>
                     </td>
                     <td class="col-date"><?= $row['last_login'] ? date('Y-m-d', strtotime($row['last_login'])) : '-' ?></td>
                     <td class="col-date"><?= $row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : '-' ?></td>
                     <td class="col-level">
                         <select class="level-select" onchange="location.href=this.value">
                             <option value='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=urlencode($TDsearchValue)?>&mode=LevelModify&code=2&no=<?=$row['id']?>' <?= $row['level']=="2" ? "selected" : "" ?>>Lv.2 부운영자</option>
                             <option value='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=urlencode($TDsearchValue)?>&mode=LevelModify&code=3&no=<?=$row['id']?>' <?= $row['level']=="3" ? "selected" : "" ?>>Lv.3 골드</option>
                             <option value='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=urlencode($TDsearchValue)?>&mode=LevelModify&code=4&no=<?=$row['id']?>' <?= $row['level']=="4" ? "selected" : "" ?>>Lv.4 정회원</option>
                             <option value='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=urlencode($TDsearchValue)?>&mode=LevelModify&code=5&no=<?=$row['id']?>' <?= $row['level']=="5" ? "selected" : "" ?>>Lv.5 일반</option>
                         </select>
                     </td>
                     <td class="col-actions">
                         <div class="action-buttons">
                             <button type='button' class="btn btn--primary btn--xs" onclick="window.open('admin.php?mode=view&no=<?=$row['id']?>', 'MemberView','width=650,height=600,scrollbars=yes');">정보</button>
                             <button type='button' class="btn btn--danger btn--xs" onclick="Member_Admin_Del('<?=$row['id']?>');">탈퇴</button>
                         </div>
                     </td>
                 </tr>
<?php
        $i++;
    }
} else {
    $emptyMessage = "등록된 회원이 없습니다.";
    if($TDsearchValue){
        $emptyMessage = "'$TDsearchValue' 검색 결과가 없습니다.";
    }
?>
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <p><?= $emptyMessage ?></p>
                        </div>
                    </td>
                </tr>
<?php
}
?>
            </tbody>
        </table>

<?php if($rows): ?>
        <!-- Pagination -->
        <div class="pagination-wrapper">
            <div class="pagination">
<?php
$mlang_pagego = "CountWW=$CountWW&s=$s&TDsearch=$TDsearch&TDsearchValue=" . urlencode($TDsearchValue);
$pagecut = 7;
$one_bbs = $listcut * $pagecut;
$start_offset = intval($offset / $one_bbs) * $one_bbs;
$end_offset = intval($recordsu / $one_bbs) * $one_bbs;
$start_page = intval($start_offset / $listcut) + 1;
$end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

if($start_offset != 0){
    $apoffset = $start_offset - $one_bbs;
    echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>« 이전</a>";
}

for($i = $start_page; $i < $start_page + $pagecut; $i++){
    $newoffset = ($i - 1) * $listcut;
    if($offset != $newoffset){
        echo "<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>$i</a>";
    } else {
        echo "<span class='active'>$i</span>";
    }
    if($i == $end_page) break;
}

if($start_offset != $end_offset){
    $nextoffset = $start_offset + $one_bbs;
    echo "<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>다음 »</a>";
}
?>
            </div>
            <div class="pagination-info">
                총 <strong><?= number_format($recordsu) ?></strong>명
            </div>
        </div>
<?php endif; ?>
    </div>
</div>

<?php
mysqli_close($db);
include"../down.php";
?>
