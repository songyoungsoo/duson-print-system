<?php
$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";

// 데이터베이스 연결
include "db.php";

// 기본 페이지 설정
$page = $_GET['page'] ?? "NameCard";

// 필요한 변수 설정
$GGTABLE = "MlangPrintAuto_transactionCate";

// 로그 정보
$log_url = str_replace("/", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y");
$log_md = date("md");
$log_ip = $_SERVER['REMOTE_ADDR'];
$log_time = time();

// 전역 $db 변수 확인
global $db;
if (!$db) {
  die("Database connection error: " . mysqli_connect_error());
}
?>

<head>
  <script language="JavaScript" type="text/JavaScript">
function MM_reloadPage(init) { 
    if (init == true) with (navigator) {
        if ((appName == "Netscape") && (parseInt(appVersion) == 4)) {
            document.MM_pgW = innerWidth;
            document.MM_pgH = innerHeight;
            onresize = MM_reloadPage;
        }
    } else if (innerWidth != document.MM_pgW || innerHeight != document.MM_pgH) {
        location.reload();
    }
}
MM_reloadPage(true);

function MM_findObj(n, d) {
    var p,i,x;  
    if(!d) d=document;
    if((p=n.indexOf("?"))>0&&parent.frames.length) {
        d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);
    }
    if(!(x=d[n])&&d.all) x=d.all[n];
    for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
    for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
    if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_showHideLayers() { 
    var i,p,v,obj,args=MM_showHideLayers.arguments;
    for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) {
        v=args[i+2];
        if (obj.style) {
            obj=obj.style;
            v=(v=='show')?'visible':(v=='hide')?'hidden':v;
        }
        obj.visibility=v;
    }
}
</script>  

<style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap');

    body,
    table,
    tr,
    td,
    select,
    input,
    textarea,
    button {
      font-family: 'Noto Sans KR', sans-serif;
      font-size: 12pt;
    }

    .input {
      font-size: 12pt;
      background-color: #FFFFFF;
      color: #336699;
      line-height: 130%;
      width: 100%;
      height: 34px;
      border: 1px solid #ccc;
      box-sizing: border-box;
      font-family: 'Noto Sans KR', sans-serif;
    }

    .inputOk {
      font-size: 12pt;
      background-color: #FFFFFF;
      color: #429EB2;
      border-style: solid;
      height: 34px;
      border: 1px solid #ccc;
      font-weight: bold;
      width: 100%;
      box-sizing: border-box;
      font-family: 'Noto Sans KR', sans-serif;
    }

    .Td1 {
      font-size: 11pt;
      background-color: #EBEBEB;
      color: #336699;
      font-family: 'Noto Sans KR', sans-serif;
    }

    .Td2 {
      font-size: 11pt;
      color: #232323;
      font-family: 'Noto Sans KR', sans-serif;
    }

    .style3 {
      color: #33CCFF;
    }

    .style4 {
      color: #FF0000;
    }

    select {
      width: 100%;
      height: 34px;
      border: 1px solid #ccc;
      box-sizing: border-box;
      font-size: 12pt;
      background-color: #FFFFFF;
      color: #336699;
      font-family: 'Noto Sans KR', sans-serif;
    }

    .label-text {
      display: inline-block;
      margin-bottom: 5px;
      font-family: 'Noto Sans KR', sans-serif;
      font-weight: 500;
      font-size: 12pt;
    }
  </style>

  <script>
    function CheckTotal(mode) {
      var f = document.namecardForm;

      if (f.NC_StyleForm.value == "" || f.NC_SectionForm.value == "" || f.NC_Order_PriceForm.value == "" || f.NC_Total_PriceForm.value == "") {
        alert("명함 주문/견적문의를 실행하기 위하여 오류가 있습니다.\n\n다시 실행시켜 주십시요...!!");
        return false;
      }

      f.action = "/MlangOrder_PrintAuto/OnlineOrder.php?SubmitMode=" + mode;
      f.submit();
    }

    // 명함 가격 계산 함수들
    function nc_calc() {
      var form = document.forms["namecardForm"];
      var potype = form.NC_sides ? form.NC_sides.value : '1';
      nc_cal.document.location.href = 'namecard_price_cal.php?NC_type=' + form.NC_type.value + 
                                      '&NC_paper=' + form.NC_paper.value + 
                                      '&NC_amount=' + form.NC_amount.value + 
                                      '&POtype=' + potype + '&ordertype=' + form.ordertype.value;
    }

    function nc_calc_ok() {
      var form = document.forms["namecardForm"];
      var potype = form.NC_sides ? form.NC_sides.value : '1';
      var url = 'namecard_price_cal.php?NC_type=' + form.NC_type.value + 
                '&NC_paper=' + form.NC_paper.value + 
                '&NC_amount=' + form.NC_amount.value + 
                '&POtype=' + potype + '&ordertype=' + form.ordertype.value;
      console.log("명함 가격 계산 URL:", url);
      nc_cal.document.location.href = url;
    }

    function nc_calc_re() {
      setTimeout(function () {
        nc_calc_ok();
      }, 100);
    }

    // 명함 종류 선택 시 용지 옵션과 수량 옵션 업데이트
    function change_NameCard_Field(val) {
      console.log("change_NameCard_Field 호출됨, val:", val);
      var f = document.namecardForm;

      // 용지 옵션 업데이트
      var NC_paper = f.NC_paper;
      NC_paper.options.length = 0;

      // 수량 옵션 업데이트
      var NC_amount = f.NC_amount;
      NC_amount.options.length = 0;

      // AJAX로 용지 옵션 가져오기
      var xhr1 = new XMLHttpRequest();
      xhr1.onreadystatechange = function () {
        if (xhr1.readyState === 4) {
          console.log("명함 용지 AJAX 응답 상태:", xhr1.status);
          if (xhr1.status === 200) {
            try {
              console.log("명함 용지 서버 응답:", xhr1.responseText);
              var options = JSON.parse(xhr1.responseText);
              console.log("파싱된 명함 용지 옵션:", options);
              for (var i = 0; i < options.length; i++) {
                NC_paper.options[i] = new Option(options[i].title, options[i].no);
              }

              // 수량 옵션 가져오기
              updateNameCardQuantities(val);
            } catch (e) {
              console.error("명함 용지 옵션 파싱 오류:", e);
              console.log("서버 응답:", xhr1.responseText);
            }
          } else {
            console.error("명함 용지 AJAX 요청 실패:", xhr1.status, xhr1.statusText);
          }
        }
      };
      xhr1.open("GET", "get_namecard_types.php?CV_no=" + val, true);
      xhr1.send();
    }

    // 명함 종류에 따른 수량 옵션 업데이트
    function updateNameCardQuantities(namecard_type) {
      console.log("updateNameCardQuantities 호출됨, namecard_type:", namecard_type);
      var f = document.namecardForm;
      var NC_amount = f.NC_amount;
      var NC_paper = f.NC_paper;

      // AJAX로 수량 옵션 가져오기 (용지 정보도 함께 전송)
      var xhr2 = new XMLHttpRequest();
      xhr2.onreadystatechange = function () {
        if (xhr2.readyState === 4) {
          console.log("명함 수량 AJAX 응답 상태:", xhr2.status);
          if (xhr2.status === 200) {
            try {
              console.log("명함 수량 서버 응답:", xhr2.responseText);
              var quantities = JSON.parse(xhr2.responseText);
              console.log("파싱된 명함 수량 옵션:", quantities);
              
              // 수량 옵션 업데이트
              for (var i = 0; i < quantities.length; i++) {
                NC_amount.options[i] = new Option(quantities[i].text, quantities[i].value);
              }

              // 양면/단면 옵션 업데이트
              updateNameCardSides();
            } catch (e) {
              console.error("명함 수량 옵션 파싱 오류:", e);
              console.log("서버 응답:", xhr2.responseText);
            }
          } else {
            console.error("명함 수량 AJAX 요청 실패:", xhr2.status, xhr2.statusText);
          }
        }
      };
      
      var paper_value = NC_paper.options.length > 0 ? NC_paper.value : '';
      xhr2.open("GET", "get_namecard_quantities.php?NC_type=" + namecard_type + "&NC_paper=" + paper_value, true);
      xhr2.send();
    }

    // 양면/단면 옵션 업데이트
    function updateNameCardSides() {
      console.log("updateNameCardSides 호출됨");
      var f = document.namecardForm;
      var NC_type = f.NC_type;
      var NC_paper = f.NC_paper;
      var NC_amount = f.NC_amount;
      var NC_sides = f.NC_sides;

      if (!NC_type.value || !NC_paper.value || !NC_amount.value) {
        console.log("필수 값이 없어서 양면/단면 옵션 업데이트 건너뜀");
        return;
      }

      // 양면/단면 옵션 초기화
      NC_sides.options.length = 0;

      // AJAX로 양면/단면 옵션 가져오기
      var xhr3 = new XMLHttpRequest();
      xhr3.onreadystatechange = function () {
        if (xhr3.readyState === 4) {
          console.log("명함 양면/단면 AJAX 응답 상태:", xhr3.status);
          if (xhr3.status === 200) {
            try {
              console.log("명함 양면/단면 서버 응답:", xhr3.responseText);
              var sides = JSON.parse(xhr3.responseText);
              console.log("파싱된 명함 양면/단면 옵션:", sides);
              
              // 양면/단면 옵션 업데이트
              for (var i = 0; i < sides.length; i++) {
                NC_sides.options[i] = new Option(sides[i].text, sides[i].value);
              }

              // 가격 계산 실행
              setTimeout(function () {
                nc_calc_ok();
              }, 100);
            } catch (e) {
              console.error("명함 양면/단면 옵션 파싱 오류:", e);
              console.log("서버 응답:", xhr3.responseText);
            }
          } else {
            console.error("명함 양면/단면 AJAX 요청 실패:", xhr3.status, xhr3.statusText);
          }
        }
      };
      xhr3.open("GET", "get_namecard_sides.php?NC_type=" + NC_type.value + "&NC_paper=" + NC_paper.value + "&NC_amount=" + NC_amount.value, true);
      xhr3.send();
    }
  </script>

  <iframe name=nc_cal frameborder=0 width=0 height=0></iframe>

  <!----------------- 명함 주문 박스 시작 -------------------->
  <table width="692" bgcolor="#CCCCCC" border="0" align="center" cellpadding="10" cellspacing="1">
    <tr>
      <td bgcolor="#FFFFFF">
        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
          <form name='namecardForm' method='post'>
            <tr>
              <td height="5" align="center"></td>
            </tr>
            <tr>
              <td align="center" valign="top">
                <!------------------------------------------명함 select 메뉴----------------------------------------->
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td width="45%" align="left" valign="top">
                      <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
                        <tr>
                          <td align="left" width="15%" style="padding-top:10px;">
                            <li><span class="label-text">명함 종류</span>
                          </td>
                          <td width="45%" bgcolor="#FFFFFF" style="padding-top:10px;">
                            <select class="input" name='NC_type' onchange='change_NameCard_Field(this.value)'>
                              <?php
                              $Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC");
                              $Cate_rows = mysqli_num_rows($Cate_result);
                              if ($Cate_rows) {
                                while ($Cate_row = mysqli_fetch_array($Cate_result)) {
                                  echo "<option value='" . htmlspecialchars($Cate_row['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($Cate_row['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                                }
                              }
                              ?>
                            </select>
                          </td>
                          <td width="40%" rowspan="4" align="left" valign="top" style="padding-left:15px;">
                            옆의 항목을 선택하시면 고객님께서 원하는 방식으로<br>
                            자동견적 금액을 보실 수 있습니다.<br><br>
                            <b>바로 주문을 하시려면 주문하기를 클릭하세요.</b><br><br>
                            두손기획-고객센터: 02-2632-1830
                          </td>
                        </tr>

                        <?php
                        $result_CV = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC LIMIT 0, 1");
                        $row_CV = mysqli_fetch_array($result_CV);
                        $CV_no = htmlspecialchars($row_CV['no'], ENT_QUOTES, 'UTF-8');
                        ?>

                        <tr>
                          <td align="left">
                            <li><span class="label-text">용지 종류</span>
                          </td>
                          <td bgcolor="#FFFFFF">
                            <select name="NC_paper" onchange="nc_calc_re();">
                              <?php
                              $result_CV_Two = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE BigNo='$CV_no' ORDER BY no ASC");
                              if (mysqli_num_rows($result_CV_Two)) {
                                while ($row_CV_Two = mysqli_fetch_array($result_CV_Two)) {
                                  echo "<option value='" . htmlspecialchars($row_CV_Two['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row_CV_Two['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                                }
                              }
                              ?>
                            </select>
                          </td>
                        </tr>
                        
                        <tr>
                          <td align="left">
                            <li><span class="label-text">수량</span>
                          </td>
                          <td bgcolor="#FFFFFF">
                            <select name="NC_amount" onchange="updateNameCardSides();">
                              <!-- 수량 옵션은 JavaScript에서 동적으로 로드됩니다 -->
                            </select>
                          </td>
                        </tr>
                        
                        <tr>
                          <td align="left">
                            <li><span class="label-text">인쇄면</span>
                          </td>
                          <td bgcolor="#FFFFFF">
                            <select name="NC_sides" onchange="nc_calc_ok();">
                              <!-- 인쇄면 옵션은 JavaScript에서 동적으로 로드됩니다 -->
                            </select>
                          </td>
                        </tr>
                        
                        <tr>
                          <td align="left">
                            <li><span class="label-text">주문방법</span>
                          </td>
                          <td bgcolor="#FFFFFF">
                            <select name="ordertype" onChange="nc_calc_ok();">
                              <option value='total'>디자인+인쇄</option>
                              <option value='print'>인쇄만 의뢰</option>
                              <option value='design'>디자인만 의뢰</option>
                            </select>
                          </td>
                        </tr>
                      </table>
                      <!------------------------------------------명함 select메뉴끝----------------------------------------->
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td height="5" align="center"></td>
            </tr>
            <tr>
              <td height="1" colspan="3" background="/images/dot_2.gif"></td>
            </tr>
            <tr>
              <td height="5" align="center"></td>
            </tr>       
            <tr>
              <td>
                <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#e4e4e4">
                  <tr>
                    <td width="45%" align="left" valign="top" bgcolor="#FFFFFF">
                      <table width="100%" border="0" cellspacing="0" cellpadding="3">
                        <tr>
                          <td width="172" align="center">
                            <a href="javascript:nc_calc();">
                              <img src="/images/estimate.gif" width="99" height="31" border=0>
                            </a>
                          </td>
                        </tr>
                        <tr>
                          <td height="5" align="center"></td>
                        </tr>

                        <!-- 숨겨진 폼 필드들 -->
                        <input type="hidden" name="OnunloadChick" value="on">
                        <input type='hidden' name='Turi' value='<?= htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8') ?>'>
                        <input type='hidden' name='Ty' value='<?= htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8') ?>'>
                        <input type='hidden' name='Tmd' value='<?= htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8') ?>'>
                        <input type='hidden' name='Tip' value='<?= htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8') ?>'>
                        <input type='hidden' name='Ttime' value='<?= htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8') ?>'>
                        <input type="hidden" name="ImgFolder" value="<?= htmlspecialchars($log_url . "/" . $log_y . "/" . $log_md . "/" . $log_ip . "/" . $log_time, ENT_QUOTES, 'UTF-8') ?>">

                        <input type='hidden' name='OrderSytle' value='명함'>
                        <input type='hidden' name='NC_StyleForm'>
                        <input type='hidden' name='NC_SectionForm'>
                        <input type='hidden' name='NC_QuantityForm'>
                        <input type='hidden' name='NC_DesignForm'>
                        <input type='hidden' name='NC_PriceForm'>
                        <input type='hidden' name='NC_DS_PriceForm'>
                        <input type='hidden' name='NC_Order_PriceForm'>
                        <input type='hidden' name='NC_VAT_PriceForm'>
                        <input type='hidden' name='NC_Total_PriceForm'>
                        <input type='hidden' name='page' value='<?= htmlspecialchars($page, ENT_QUOTES, 'UTF-8') ?>'>  
                      
                        <tr>
                          <td align="center">
                            <!------   명함 가격 결과 보여주기 시작 -------------->
                            <table border="0" cellspacing="5" cellpadding="5" align="left" width="100%">
                              <tr>
                                <td align="left" width="80px" style="padding-top:10px;">
                                  <li><span class="label-text">인쇄비</span>
                                </td>
                                <td>
                                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td width="90%"><input type="text" name='NC_Price' readonly
                                          style='width:100%; height:30px; font-weight:bold; text-align:right; border:1px solid #ccc; box-sizing:border-box;'>
                                      </td>
                                      <td width="10%" align="left" style="padding-left:5px;">원</td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="padding-top:10px;">
                                  <li><span class="label-text">편집비</span>
                                </td>
                                <td>
                                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td width="90%"><input type="text" name='NC_DS_Price' readonly
                                          style='width:100%; height:30px; font-weight:bold; text-align:right; border:1px solid #ccc; box-sizing:border-box;'>
                                      </td>
                                      <td width="10%" align="left" style="padding-left:5px;">원</td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="padding-top:10px;">
                                  <li><span class="label-text">금액</span>
                                </td>
                                <td>
                                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td width="90%"><input type="text" name='NC_Order_Price' readonly
                                          style='width:100%; height:30px; font-weight:bold; text-align:right; border:1px solid #ccc; box-sizing:border-box;'>
                                      </td>
                                      <td width="10%" align="left" style="padding-left:5px;">원</td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                            <!------   명함 가격 결과 보여주기 끝 -------------->
                          </td>
                        </tr>
                        <tr>
                          <td align="center" class="radi">세금별도. 배송비는 착불입니다.</td>
                        </tr>
                        <tr>
                          <td align="center">
                            <input type="image" onClick="javascript:return CheckTotal('OrderOne');" src="/images/sub3_img_17.gif" width="99" height="31" border="0">
                            &nbsp;&nbsp;
                            <input type="image" onClick="javascript:return CheckTotal('EstimateOne');" src="/images/sub3_img_18.gif" width="99" height="31" border="0">
                          </td>
                        </tr>
                      </table>
                    </td>

                    <td width="458" align="left" valign="top" bgcolor="#FFFFFF" style="padding: 20px;">
                      <h3>명함 제작 안내</h3>
                      <p>• 고품질 명함을 합리적인 가격으로 제작해드립니다.</p>
                      <p>• 다양한 용지와 후가공 옵션을 선택하실 수 있습니다.</p>
                      <p>• 디자인부터 인쇄까지 원스톱 서비스를 제공합니다.</p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </form>
        </table>
      </td>
    </tr>
  </table>

  <script>
    // 페이지 로드 시 모든 옵션을 DB에서 동적으로 로드
    window.onload = function () {
      setTimeout(function () {
        console.log("명함 초기 옵션 로딩 시작");
        var form = document.forms["namecardForm"];
        if (form && form.NC_type.value) {
          // 첫 번째 명함 종류 값으로 모든 하위 옵션 동적 로드
          change_NameCard_Field(form.NC_type.value);
        } else {
          console.error("명함 폼 요소를 찾을 수 없습니다.");
        }
      }, 500);
    };
  </script>
</head>