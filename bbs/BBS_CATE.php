<?php
// 세션 시작 코드 제거 - 이미 상위 파일에서 세션을 시작했을 수 있음

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

// 변수 초기화
$PHP_SELF = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
$cate = isset($_GET['cate']) ? $_GET['cate'] : (isset($_POST['cate']) ? $_POST['cate'] : '');
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_POST['search']) ? $_POST['search'] : '');
$page = isset($_GET['page']) ? $_GET['page'] : 1;

$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    die("DB 연결 실패: " . mysqli_connect_error());
}

if ($CateCodeUgt == "1") {

    if ($BBS_ADMIN_cate) {
        echo "<select name='CATEGORY' onChange=\"BBS_CATE('parent',this,0)\"><option value='0' selected>▒ 선택 ▒▒</option>";
        $CATEGORY_LIST = preg_split("/:/", $BBS_ADMIN_cate);
        $AKJ = 0;
        while ($AKJ < sizeof($CATEGORY_LIST)) {

            if ($CATEGORY == $CATEGORY_LIST[$AKJ]) {
                echo "<option value='$PHP_SELF?CATEGORY={$CATEGORY_LIST[$AKJ]}&cate=$cate&search=$search&table=$table&mode=list&page=$page' selected style='font-size:10pt; background-color:#669966; color:#FFFFFF;'>{$CATEGORY_LIST[$AKJ]}</option>";
            } else {
                echo "<option value='$PHP_SELF?CATEGORY={$CATEGORY_LIST[$AKJ]}&cate=$cate&search=$search&table=$table&mode=list&page=$page'>{$CATEGORY_LIST[$AKJ]}</option>";
            }

            $AKJ++;
        }
        echo "<option value='$PHP_SELF?table=$table&mode=list&page=$page'>→ 전체자료보기</option>";
        echo "</select>\n";
    }

} ///////////////////////////////////////////////////////////////////////////////////////
if ($CateCodeUgt == "2") {

    if ($BBS_ADMIN_cate) {
        echo "<select name='TX_cate'><option value='0' selected>▒ 선택 ▒▒</option>";
        $CATEGORY_LIST = preg_split("/:/", $BBS_ADMIN_cate);
        $AKJ = 0;
        while ($AKJ < sizeof($CATEGORY_LIST)) {

            if ($BbsViewMlang_CATEGORY == $CATEGORY_LIST[$AKJ] || $CATEGORY == $CATEGORY_LIST[$AKJ]) {
                echo "<option value='{$CATEGORY_LIST[$AKJ]}' selected style='font-size:10pt; background-color:#669966; color:#FFFFFF;'>{$CATEGORY_LIST[$AKJ]}</option>";
            } else {
                echo "<option value='{$CATEGORY_LIST[$AKJ]}'>{$CATEGORY_LIST[$AKJ]}</option>";
            }

            $AKJ++;
        }
        echo "</select>\n";
    }

}
?>