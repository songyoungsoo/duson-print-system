<?php
declare(strict_types=1);

// DB 연결 (db.php 안에서 아래와 같이 mysqli 연결을 설정했다고 가정)
//   $db = new mysqli($host, $user, $password, $dataname);
//   $db->set_charset('utf8mb4');
include __DIR__ . '/../../db.php';
include __DIR__ . '/../config.php';
$db = new mysqli($host, $user, $password, $dataname);
$db->set_charset('utf8mb4');
if ($db->connect_error) {
	die("Connection failed: " . $db->connect_error);
}	
$AdminCode21       = $_REQUEST['AdminCode21'] ?? '';
$tabler            = $_REQUEST['tabler']    ?? '';
$nor               = $_REQUEST['nor']       ?? '';
$urlr              = $_REQUEST['urlr']      ?? '';
$count             = $_POST['count']        ?? '';
$rec               = $_POST['rec']          ?? '';
$Mlang_bbs_member  = $_POST['Mlang_bbs_member'] ?? '';
$date              = $_POST['date']         ?? '';
$file              = $_REQUEST['file']      ?? '';
$bbs               = $_REQUEST['bbs']       ?? '';
$no                = $nor;  // 내부적으로는 nor 사용

// ---------------------------------------------------------
// 1) AdminCode21 == 'form' : 수정 폼 출력
// ---------------------------------------------------------
if ($AdminCode21 === 'form') {
    // 제목 등 인클루드
    include __DIR__ . '/../title.php';

    // 레코드 조회 (prepared statement)
    $stmt = $db->prepare("SELECT Mlang_bbs_title, Mlang_bbs_count, Mlang_bbs_rec, Mlang_bbs_member, Mlang_date
                            FROM {$db->real_escape_string($tabler)}
                           WHERE Mlang_bbs_no = ?");
    $stmt->bind_param('i', $nor);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // XSS 방어
        $title  = htmlspecialchars($row['Mlang_bbs_title'], ENT_QUOTES);
        $cnt    = (int)$row['Mlang_bbs_count'];
        $recVal = (int)$row['Mlang_bbs_rec'];
        $mem    = htmlspecialchars($row['Mlang_bbs_member'], ENT_QUOTES);
        $dt     = htmlspecialchars($row['Mlang_date'], ENT_QUOTES);
        ?>
        <script>
        window.moveTo(screen.width/5, screen.height/5);
        self.resizeTo(600, 200);
        </script>

        <form name="FrmUserInfo" method="post" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
          <input type="hidden" name="AdminCode21" value="ok">
          <input type="hidden" name="tabler"      value="<?php echo  $tabler ?>">
          <input type="hidden" name="nor"         value="<?php echo  $nor ?>">
          <input type="hidden" name="urlr"        value="<?php echo  htmlspecialchars($urlr, ENT_QUOTES) ?>">

          <table border="0" align="center" width="100%" cellpadding="5" cellspacing="1">
            <tr>
              <td colspan="4" bgcolor="#6699CC">
                제목: <span style="font-weight:bold; color:#FFF;"><?php echo  $title ?></span>
              </td>
            </tr>
            <tr>
              <td bgcolor="#6699CC"><span style="font-weight:bold; color:#FFF;">카운터수:</span></td>
              <td bgcolor="#FFF">
                <input type="number" name="count" value="<?php echo  $cnt ?>" size="10">
              </td>
              <td bgcolor="#6699CC"><span style="font-weight:bold; color:#FFF;">추천수:</span></td>
              <td bgcolor="#FFF">
                <input type="number" name="rec" value="<?php echo  $recVal ?>" size="10">
              </td>
            </tr>
            <tr>
              <td bgcolor="#6699CC"><span style="font-weight:bold; color:#FFF;">등록인:</span></td>
              <td bgcolor="#FFF">
                <input type="text" name="Mlang_bbs_member" value="<?php echo  $mem ?>" size="10">
              </td>
              <td bgcolor="#6699CC"><span style="font-weight:bold; color:#FFF;">등록날짜:</span></td>
              <td bgcolor="#FFF">
                <input type="text" name="date" value="<?php echo  $dt ?>" size="20">
              </td>
            </tr>
            <tr>
              <td align="center" colspan="4">
                * 등록 날짜는 예) <?php echo  date('Y-m-d') ?> 형식으로 입력해 주세요.
                <button type="submit">수정합니다.</button>
              </td>
            </tr>
          </table>
        </form>
        <?php
    } else {
        echo <<<JS
<script>
  alert('수정하려는 자료가 이미 삭제되어 있습니다. 페이지를 새로고침합니다.');
  opener.parent.location.reload();
  window.self.close();
</script>
JS;
    }
    $stmt->close();
    $db->close();
    exit;
}

// ---------------------------------------------------------
// 2) AdminCode21 == 'ok' : 수정 처리
// ---------------------------------------------------------
if ($AdminCode21 === 'ok') {
    $stmt = $db->prepare("UPDATE {$db->real_escape_string($tabler)}
                              SET Mlang_bbs_count  = ?,
                                  Mlang_bbs_rec    = ?,
                                  Mlang_bbs_member = ?,
                                  Mlang_date       = ?
                            WHERE Mlang_bbs_no     = ?");
    $stmt->bind_param('iissi', $count, $rec, $Mlang_bbs_member, $date, $nor);
    $success = $stmt->execute();

    if (! $success) {
        echo <<<JS
<script>
  alert('DB 접속 에러입니다!');
  history.back();
</script>
JS;
        exit;
    }

    $stmt->close();
    $db->close();
    // 완료 메시지
    $escapedUrl = $urlr !== '' ? "opener.parent.location=\"{$urlr}\";" : "opener.parent.location.reload();";
    echo <<<HTML
<html><script>
  alert('테이블명: {$tabler} 의 {$nor} 번 자료를 정상적으로 수정했습니다.');
  {$escapedUrl}
  window.self.close();
</script></html>
HTML;
    exit;
}

// ---------------------------------------------------------
// 3) file=ok : 업로드 파일 삭제 처리
// ---------------------------------------------------------
if ($file === 'ok') {
    $uploadDir = __DIR__ . "/../../results/upload/{$tabler}/{$nor}";
    if (is_dir($uploadDir)) {
        foreach (scandir($uploadDir) as $fn) {
            if ($fn === '.' || $fn === '..') continue;
            @unlink("{$uploadDir}/{$fn}");
        }
        @rmdir($uploadDir);
    }
}

// ---------------------------------------------------------
// 4) bbs=del 또는 일반 delete 처리
// ---------------------------------------------------------

if ($bbs === 'del') {
    $delStmt = $db->prepare("DELETE FROM {$db->real_escape_string($tabler)} WHERE Mlang_bbs_no = ?");
    $delStmt->bind_param('i', $nor);
} else {
    $delStmt = $db->prepare("DELETE FROM {$db->real_escape_string($tabler)} WHERE no = ?");
    $delStmt->bind_param('i', $nor);
}
$delStmt->execute();
$delStmt->close();

// BBSTOP 관련 추가 기능
$topStmt = $db->prepare("SELECT no FROM BBS_TOP WHERE BBS_Table = ? AND BBS_No = ?");
$topStmt->bind_param('si', $tabler, $nor);
$topStmt->execute();
$topRes = $topStmt->get_result();
if ($topRow = $topRes->fetch_assoc()) {
    $delTop = $db->prepare("DELETE FROM BBS_TOP WHERE no = ?");
    $delTop->bind_param('i', $topRow['no']);
    $delTop->execute();
    $delTop->close();
}
$topStmt->close();

$db->close();

// 삭제 완료 메시지
$escapedUrl2 = $urlr !== '' ? "opener.parent.location=\"{$urlr}\";" : "opener.parent.location.reload();";
echo <<<HTML
<html><script>
  alert('테이블명: {$tabler} 의 {$nor} 번 자료를 정상적으로 삭제했습니다.');
  {$escapedUrl2}
  window.self.close();
</script></html>
HTML;
exit;
