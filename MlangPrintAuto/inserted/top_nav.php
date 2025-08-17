<!-- 상단 내비게이션 -->
<table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr style="background-image: url('http://dsp114.com/img/top231205.gif');
  background-repeat: no-repeat;
  background-size: cover;
  width: 990px;
  height: 52px;">
    <td align="center" border="0">
      <?php 
      $session_file = $_SERVER['DOCUMENT_ROOT']."/session/index.php";
      if (file_exists($session_file)) {
        include $session_file;
      } else {
        // 세션 파일이 없을 경우 기본 로그인 링크 표시
        echo '<a href="/member/login.php" style="font-weight: bold;">로그인</a> | ';
      }
      ?>
      <a href="http://localhost" style="font-weight: bold;">HOME</a>|
      <a href="/sub/info.php" style="font-weight: bold;">회사소개</a>|
      <a href="/sub/leaflet.php" style="font-weight: bold;">포트폴리오</a>|
      <a href="/sub/estimate_auto.php" style="font-weight: bold;">견적안내</a>|
      <a href="/sub/checkboard.php" style="font-weight: bold;color: white; background-color: orange; padding: 7px;">교정보기</a>|
      <a href="/bbs/qna.php" style="font-weight: bold;">고객문의</a>|
    </td>
  </tr>
</table>

<!-- 로고 영역 -->
<table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td width="400" height="150"><img src="/img/11.jpg" width="400" height="150" /></td>
    <td width="590" height="150"><img src="/WEBSILDESIGN/swf/WEBSILDESIGN.gif" width="590" height="150" /></td>
  </tr>
  <tr> 
    <td height="10" colspan="2"></td>
  </tr>
</table>