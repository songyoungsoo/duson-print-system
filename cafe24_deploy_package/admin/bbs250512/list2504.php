<?php
// 게시판의 필드들.... Mlang_${table}_bbs //////////////////////////////////////////////////////////////////////////////////////
//Mlang_bbs_no mediumint(12) unsigned NOT NULL auto_increment, // 게시글 번호
//Mlang_bbs_member varchar(100) NOT NULL default '',                     // 등록인
//Mlang_bbs_title text,                                                                          // 제목
//Mlang_bbs_style varchar(100) NOT NULL default 'br',                       // 문서형식
//Mlang_bbs_connent text,                                                                  // 내용
//Mlang_bbs_link text,                                                                          // 링크 파일
//Mlang_bbs_file text,                                                                           // 업로드 파일
//Mlang_bbs_pass varchar(100) NOT NULL default '',                          // 비밀번호
//Mlang_bbs_count int(12) NOT NULL default '0',                                  // 카운터
//Mlang_bbs_rec int(12) NOT NULL default '0',                                     // 추천                        
//Mlang_bbs_secret varchar(100) NOT NULL default 'yes',                  // 글의 공개, 비공개
//Mlang_bbs_reply int(12) NOT NULL default '0',                                  // 몇번글인가 (답변글일경우) 의 여부 0 은 본글을의미           
//Mlang_date date NOT NULL default '0000-00-00',                              // 등록날짜
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// 게시판 관리 필드들.  Mlang_BBS_Admin ////////////////////////////////////////////////////////////////////////////////////
//  no       : 게시판 번호
//  title      : 게시판 제목
//  id        : 게시판 ID
//  pass    : 게시판 비밀번호
//  header  : 윗 html 내용
//  footer   : 아래 html 내용
//  header_include  : 윗 INCLUDE 파일
//  footer_include   : 아래 INCLUDE 파일    
//  file_select  : 파일을 받을 건가의 선택여부
//  link_select  : 링크을 할 건가의 선택여부
//  recnum : 한페이지당 출력수
//  lnum    : 페이지이동 메뉴수
//  cutlen  :  제목글자수 끊기
//  New_Article   : 새글표시 유지기간
//  date_select    : 등록일 출력여부
//  name_select   : 이름 출력여부
//  count_select   : 조회수 출력여부
//  recommendation_select   : 추천수 출력여부
//  secret_select   : 공개 비공개 출력여부
//  write_select     : 쓰기 권한 - member(회원들), guest(아무나), admin(관리자만)
//  date : 게시판을 만든날짜
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<?php
// $db = new mysqli("host", "user", "password", "dataname");
$db = mysqli_connect($host, $user, $password, $dataname);
if ($db->connect_errno) {
    die("DB 연결 실패: " . $db->connect_error);
}
$db->set_charset("utf8");

$PHP_SELF = htmlspecialchars($_SERVER["PHP_SELF"]);
?>
<head>
<script>
function TypeCheck (s, spc) {
    for (let i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i+1)) < 0) return false;
    }
    return true;
}
function BbsAdminCheckField() {
    const f = document.BbsAdmin;
    const ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    if (f.skin.value === "0") return alert("SKIN 선택"), false;
    if (!f.title.value.trim()) return alert("제목 입력"), false;
    if (!f.table.value.trim() || !TypeCheck(f.table.value, ALPHA)) return alert("유효한 테이블명 입력(영문/숫자)"), false;
    if (f.table.value.length < 2 || f.table.value.length > 20) return alert("테이블명 길이 오류"), false;
    if (!f.pass.value.trim() || !TypeCheck(f.pass.value, ALPHA)) return alert("비밀번호 오류"), false;
    if (f.pass.value.length < 4 || f.pass.value.length > 20) return alert("비밀번호 길이 오류"), false;
    return true;
}
function clearField(field){ if (field.value === field.defaultValue) field.value = ""; }
function checkField(field){ if (!field.value) field.value = field.defaultValue; }
function BBS_Admin_Del(id){
    if (confirm("삭제 확인")) {
        location.href = './bbs/delete.php?id=' + encodeURIComponent(id);
    }
}
function BbsAdminSearchCheckField(){
    const f = document.BbsAdminSearch;
    if (!f.search.value.trim()) return alert("검색할 게시판의 제목이나 테이블명을 입력해주세요...!!"), false;
    return true;
}
</script>
</head>
<body>
<form name='BbsAdmin' method='post' onsubmit='return BbsAdminCheckField()' action='<?php echo  $PHP_SELF ?>'>
<input type='hidden' name='mode' value='submit'>
<fieldset>
    <legend>게시판 생성</legend>
    <?php $BbsAdminCateUrl = ".."; include "./bbs/BbsAdminCate.php"; ?>
    제목: <input type='text' name='title' value='게시판타이틀(제목)' size='20' maxlength='100' onfocus='clearField(this)' onblur='checkField(this)'>
    테이블: <input type='text' name='table' value='테이블명(영문/숫자)' size='20' maxlength='20' onfocus='clearField(this)' onblur='checkField(this)'>
    비밀번호: <input type='text' name='pass' value='게시판비밀번호' size='14' maxlength='20' onfocus='clearField(this)' onblur='checkField(this)'>
    <input type='submit' value='새게시판 생성'>
</fieldset>
</form>

<form name='BbsAdminSearch' method='post' onsubmit='return BbsAdminSearchCheckField()' action='<?php echo  $PHP_SELF ?>'>
<input type='hidden' name='mode' value='list'>
<fieldset>
    <legend>게시판 검색</legend>
    <select name='bbs_cate'>
        <option value='title'>타이틀(제목)</option>
        <option value='id'>테이블명</option>
    </select>
    <input type='text' name='search' size='18' onfocus='clearField(this)' onblur='checkField(this)'>
    <input type='submit' value='검색'>
</fieldset>
</form>
<?php
$table = " Mlang_BBS_Admin";
$bbs_cate = $_POST['bbs_cate'] ?? 'title';
$search = $_POST['search'] ?? '';
$offset = $_GET['offset'] ?? 0;
$listcut = 15;

$search_sql = $search ? "WHERE $bbs_cate LIKE ?" : "";
$sql = "SELECT * FROM $table $search_sql ORDER BY no DESC LIMIT ?, ?";
$stmt = $db->prepare($sql);
if ($search) {
    $param = "%$search%";
    $stmt->bind_param("sii", $param, $offset, $listcut);
} else {
    $stmt->bind_param("ii", $offset, $listcut);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;
?>

<table border="1" width="100%" cellpadding="5" cellspacing="2">
<tr>
    <th>제목</th><th>SKIN</th><th>테이블</th><th>비밀번호</th><th>생성일</th><th>자료수</th><th>관리</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><a href="<?php echo  $Homedir ?>/bbs/bbs.php?table=<?php echo  htmlspecialchars($row['id']) ?>&mode=list" target="_blank"><?php echo  htmlspecialchars($row['title']) ?></a></td>
    <td><?php echo  htmlspecialchars($row['skin']) ?></td>
    <td><?php echo  htmlspecialchars($row['id']) ?></td>
    <td><?php echo  htmlspecialchars($row['pass']) ?></td>
    <td><?php echo  htmlspecialchars($row['date']) ?></td>
    <td>
        <?php
        $table_name = "Mlang_" . $row['id'] . "_bbs";
        $count = $db->query("SELECT COUNT(*) AS cnt FROM $table_name")->fetch_assoc()['cnt'] ?? 0;
        echo $count;
        ?>
    </td>
    <td>
        <button onclick="window.open('./bbs/AdminModify.php?code=start&no=<?php echo  $row['no'] ?>')">설정</button>
        <button onclick="BBS_Admin_Del('<?php echo  htmlspecialchars($row['id']) ?>')">삭제</button>
        <button onclick="window.open('./bbs/dump.php?TableName=<?php echo  $table_name ?>')">빽업</button>
    </td>
</tr>
<?php endwhile; ?>
</table>
<?php
$result_count = $db->query("SELECT COUNT(*) as cnt FROM $table");
$total = $result_count->fetch_assoc()['cnt'];
$pagecut = 10;
$one_bbs = $listcut * $pagecut;
$start_offset = intval($offset / $one_bbs) * $one_bbs;
$end_offset = intval($total / $one_bbs) * $one_bbs;
$start_page = intval($start_offset / $listcut) + 1;
$end_page = ($total % $listcut > 0) ? intval($total / $listcut) + 1 : intval($total / $listcut);

if ($start_offset != 0) {
    $apoffset = $start_offset - $one_bbs;
    echo "<a href='$PHP_SELF?offset=$apoffset&mode=list&bbs_cate=$bbs_cate&search=$search'>...[이전]</a> ";
}
for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
    $newoffset = ($i - 1) * $listcut;
    if ($i > $end_page) break;
    echo $offset == $newoffset ? " [$i] " : "<a href='$PHP_SELF?offset=$newoffset&mode=list&bbs_cate=$bbs_cate&search=$search'>[$i]</a> ";
}
if ($start_offset != $end_offset) {
    $nextoffset = $start_offset + $one_bbs;
    echo "<a href='$PHP_SELF?offset=$nextoffset&mode=list&bbs_cate=$bbs_cate&search=$search'>[다음]...</a>";
}
echo " 총목록갯수: $end_page 개";
?>
