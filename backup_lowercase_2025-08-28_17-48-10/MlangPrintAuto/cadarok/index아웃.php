<?php
// === 초기 설정 ===
$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";
$page = $_GET['page'] ?? "cadarok";
$allowed_pages = ['cadarok', 'inserted', 'envelope'];
if (!in_array($page, $allowed_pages)) die("잘못된 접근");
include "$HomeDir/db.php";

$log_url = str_replace("/", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y");
$log_md = date("md");
$log_ip = $_SERVER['REMOTE_ADDR'];
$log_time = time();

?><!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>카다록 견적안내</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body, table, input, select, textarea {
      font-family: 'Noto Sans KR', sans-serif;
      font-size: 12pt;
    }
    .input { width: 100%; height: 34px; border: 1px solid #ccc; }
    .label-text { font-weight: 500; margin-bottom: 5px; display: inline-block; }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    function updateOptions(val) {
  $.getJSON("get_sizes.php", { CV_no: val }, function(sizeData) {
    const fsd = $("select[name='MY_Fsd']").empty().append("<option value=''>규격 선택</option>");
    sizeData.forEach(opt => fsd.append(`<option value="${opt.no}">${opt.title}</option>`));
    if (sizeData.length > 0) {
      $("select[name='MY_Fsd']").val(sizeData[0].no);
    }

    // 종이종류는 규격이 채워진 다음에 불러와야 계산이 정확함
    $.getJSON("get_paper_types.php", { CV_no: val }, function(paperData) {
      const pn = $("select[name='PN_type']").empty().append("<option value=''>종이 선택</option>");
      paperData.forEach(opt => pn.append(`<option value="${opt.no}">${opt.title}</option>`));
      if (paperData.length > 0) {
        $("select[name='PN_type']").val(paperData[0].no);
      }

      // 모든 항목이 채워진 이후에 가격 계산 실행
      setTimeout(calcPrice, 300);
    });
  });
}

    function calcPrice() {
      const f = document.forms['choiceForm'];
      if (!f.MY_type.value || !f.MY_Fsd.value || !f.PN_type.value || !f.MY_amount.value || !f.ordertype.value) {
        return;
      }
      const params = $(f).serialize();
      $.ajax({
        url: "price_cal.php",
        method: "POST",
        data: params,
        dataType: "json",
        success: function(data) {
          $("#print_price").text(data.PriceForm + '원');
          $("#design_price").text(data.DS_PriceForm + '원');
          $("#total_price").text(data.Order_PriceForm + '원');
        },
        error: function(xhr, status, error) {
          console.error("가격 계산 오류:", status, error);
        }
      });
    }

    $(function() {
  const firstOption = $("select[name='MY_type'] option:nth-child(2)").val();
  if (firstOption) {
    $("select[name='MY_type']").val(firstOption);
    updateOptions(firstOption);
    setTimeout(calcPrice, 1000); // 옵션 불러온 후 계산 실행
  }

  $("select[name='MY_type']").change(function() {
    updateOptions(this.value);
    setTimeout(calcPrice, 500);
  });
  $("select[name='MY_Fsd'], select[name='PN_type'], select[name='MY_amount'], select[name='ordertype']").change(calcPrice);
});
  </script>
</head>
<body>
<form name="choiceForm" method="post" action="/MlangOrder_PrintAuto/OnlineOrder.php?SubmitMode=OrderOne">
  <label class="label-text">구분</label>
  <select name="MY_type" class="input">
    <option value="">선택하세요</option>
    <?php
    $res = mysqli_query($db, "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC");
    $first_no = null;
    while ($row = mysqli_fetch_assoc($res)) {
      if (!$first_no) $first_no = $row['no'];
      echo "<option value='{$row['no']}'>" . htmlspecialchars($row['title']) . "</option>";
    }
    ?>
  </select>

  <label class="label-text">규격</label>
  <select name="MY_Fsd" class="input"></select>

  <label class="label-text">종이종류</label>
  <select name="PN_type" class="input"></select>

  <label class="label-text">수량</label>
  <select name="MY_amount" class="input">
    <option value="">선택</option>
    <option value="1000">1000부</option>
    <option value="2000">2000부</option>
    <option value="3000">3000부</option>
    <option value="4000">4000부</option>
    <option value="5000">5000부</option>
    <option value="기타">기타</option>
  </select>

  <label class="label-text">주문방법</label>
  <select name="ordertype" class="input">
    <option value="">선택</option>
    <option value="total">디자인+인쇄</option>
    <option value="print">인쇄만</option>
  </select>

  <h3>가격 정보</h3>
  <p>인쇄비: <span id="print_price">0원</span></p>
  <p>편집비: <span id="design_price">0원</span></p>
  <p>총금액: <span id="total_price">0원</span></p>

  <hr>
  <h3>파일 첨부</h3>
<div>
  <select size="3" name="parentList" style="width:100%; font-size:10pt; color:#336666; font-weight:bold;" multiple></select><br>
  <input type="button" onclick="openUploadPopup();" value="파일올리기" style="width:80px; height:25px; font-size:11px;">
  <input type="button" onclick="deleteSelectedFiles();" value="삭제" style="width:80px; height:25px; font-size:11px;">
</div>
<script>
  function openUploadPopup() {
    const params = `Turi=<?php echo urlencode($log_url); ?>&Ty=<?php echo $log_y; ?>&Tmd=<?php echo $log_md; ?>&Tip=<?php echo $log_ip; ?>&Ttime=<?php echo $log_time; ?>`;
    const url = '<?php echo $MultyUploadDir; ?>/FileUp.php?' + params;
    const props = 'scrollbars=yes,resizable=yes,width=500,height=400';
    window.open(url, 'UploadPopup', props);
  }

  function deleteSelectedFiles() {
    const list = document.forms['choiceForm'].parentList;
    for (let i = list.options.length - 1; i >= 0; i--) {
      if (list.options[i].selected) {
        const fileName = list.options[i].text;
        const params = `FileDelete=ok&Turi=<?php echo urlencode($log_url); ?>&Ty=<?php echo $log_y; ?>&Tmd=<?php echo $log_md; ?>&Tip=<?php echo $log_ip; ?>&Ttime=<?php echo $log_time; ?>&FileName=` + encodeURIComponent(fileName);
        const url = '<?php echo $MultyUploadDir; ?>/FileDelete.php?' + params;
        window.open(url, '', 'width=100,height=100,top=2000,left=2000');
        list.remove(i);
      }
    }
  }
</script>

  <h3>기타 요청 사항</h3>
  <textarea name="memo" rows="5" style="width:100%;"></textarea>

  <br><br>
  <button type="submit">주문하기</button>
</form>
</body>
</html>
