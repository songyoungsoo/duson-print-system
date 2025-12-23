// 전단지/리플렛 계산기 전용 JavaScript 함수들
function CheckTotal(mode){
  var f = document.choiceForm;
  if (f.StyleForm.value == "") {
    alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\\n\\n다시 실행 시켜 주십시요...!!");
    return false;
  }
  if (f.SectionForm.value == "") {
    alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\\n\\n다시 실행 시켜 주십시요...!!");
    return false;
  }
  if (f.Order_PriceForm.value == "") {
    alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\\n\\n다시 실행 시켜 주십시요...!!");
    return false;
  }
  if (f.Total_PriceForm.value == "") {
    alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\\n\\n다시 실행 시켜 주십시요...!!");
    return false;
  }
  f.action = "/mlangorder_printauto/OnlineOrder.php?SubmitMode=" + mode;
  f.submit(); 
}

// 기존 iframe 방식 계산 (호환성 유지)
function calc(){
  var asd = document.forms["choiceForm"];
  cal.document.location.href = 'price_cal.php?MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value + '&ordertype=' + asd.ordertype.value + '&POtype=' + asd.POtype.value;
}

function calc_ok() {
  var asd = document.forms["choiceForm"];
  cal.document.location.href = 'price_cal.php?MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value + '&ordertype=' + asd.ordertype.value + '&POtype=' + asd.POtype.value;
}

// 새로운 AJAX 기반 계산 함수들
function calc_ajax() {
  console.log("AJAX 기반 가격 계산 시작");
  calculatePriceAjax();
}

function calc_ok_ajax() {
  console.log("AJAX 기반 자동 가격 계산 시작");
  calculatePriceAjax();
}

function calculatePriceAjax() {
  var form = document.forms["choiceForm"];
  var params = new URLSearchParams({
    MY_type: form.MY_type.value,
    PN_type: form.PN_type.value,
    MY_Fsd: form.MY_Fsd.value,
    MY_amount: form.MY_amount.value,
    ordertype: form.ordertype.value,
    POtype: form.POtype.value
  });

  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      console.log("가격 계산 AJAX 응답 상태:", xhr.status);
      if (xhr.status === 200) {
        try {
          console.log("가격 계산 서버 응답:", xhr.responseText);
          var response = JSON.parse(xhr.responseText);
          console.log("파싱된 가격 계산 응답:", response);
          
          if (response.success) {
            var data = response.data;
            // 화면에 표시되는 가격 정보 업데이트
            form.Price.value = data.Price;
            form.DS_Price.value = data.DS_Price;
            form.Order_Price.value = data.Order_Price;
            
            // 숨겨진 폼 필드 업데이트
            form.PriceForm.value = data.PriceForm;
            form.DS_PriceForm.value = data.DS_PriceForm;
            form.Order_PriceForm.value = data.Order_PriceForm;
            form.VAT_PriceForm.value = data.VAT_PriceForm;
            form.Total_PriceForm.value = data.Total_PriceForm;
            form.StyleForm.value = data.StyleForm;
            form.SectionForm.value = data.SectionForm;
            form.QuantityForm.value = data.QuantityForm;
            form.DesignForm.value = data.DesignForm;
            form.MY_amountRight.value = data.MY_amountRight;

            // window.currentPriceData 설정 (장바구니 추가용)
            // 추가 옵션 가격 포함
            var additionalOptionsTotal = 0;
            var additionalOptionsField = document.getElementById('additional_options_total');
            if (additionalOptionsField) {
                additionalOptionsTotal = parseInt(additionalOptionsField.value) || 0;
            }

            window.currentPriceData = {
                total_price: parseInt(data.Order_Price) || 0,
                vat_price: parseInt(data.Total_PriceForm) || 0,
                additional_options_total: additionalOptionsTotal
            };
            console.log("window.currentPriceData 설정 (추가 옵션 포함):", window.currentPriceData);

            console.log("가격 정보 업데이트 완료");
          } else {
            console.error("가격 계산 실패:", response.error);
            alert(response.error.message);
            clearPriceFields();
          }
        } catch (e) {
          console.error("가격 계산 응답 파싱 오류:", e);
          console.log("서버 응답:", xhr.responseText);
          alert("가격 계산 중 오류가 발생했습니다.");
          clearPriceFields();
        }
      } else {
        console.error("가격 계산 AJAX 요청 실패:", xhr.status, xhr.statusText);
        alert("가격 계산 요청이 실패했습니다.");
        clearPriceFields();
      }
    }
  };
  
  xhr.open("GET", "calculate_price_ajax.php?" + params.toString(), true);
  xhr.send();
}

function clearPriceFields() {
  var form = document.forms["choiceForm"];
  form.Price.value = '';
  form.DS_Price.value = '';
  form.Order_Price.value = '';
  form.PriceForm.value = '';
  form.DS_PriceForm.value = '';
  form.Order_PriceForm.value = '';
  form.VAT_PriceForm.value = '';
  form.Total_PriceForm.value = '';
  form.StyleForm.value = '';
  form.SectionForm.value = '';
  form.QuantityForm.value = '';
  form.DesignForm.value = '';
}

// 인쇄색상 변경 시 종이종류와 종이규격 동적 업데이트
function change_Field(val) {
  console.log("change_Field 호출됨, val:", val);
  var f = document.choiceForm;
  
  // 종이종류 옵션 업데이트
  var MY_Fsd = f.MY_Fsd;
  MY_Fsd.options.length = 0;

  // 종이규격 옵션 업데이트
  var PN_type = f.PN_type;
  PN_type.options.length = 0;

  // AJAX로 종이종류 옵션 가져오기
  var xhr1 = new XMLHttpRequest();
  xhr1.onreadystatechange = function () {
    if (xhr1.readyState === 4) {
      console.log("종이종류 AJAX 응답 상태:", xhr1.status);
      if (xhr1.status === 200) {
        try {
          console.log("종이종류 서버 응답:", xhr1.responseText);
          var options = JSON.parse(xhr1.responseText);
          console.log("파싱된 종이종류 옵션:", options);

          // 🔧 첫 번째 옵션: "종이종류를 선택해주세요"
          MY_Fsd.options[0] = new Option("종이종류를 선택해주세요", "");

          for (var i = 0; i < options.length; i++) {
            MY_Fsd.options[i + 1] = new Option(options[i].title, options[i].no);
          }

          // 🔧 FIX: 첫 번째 실제 옵션을 선택 (인덱스 1)
          if (options.length > 0) {
            MY_Fsd.selectedIndex = 1;
            console.log("종이종류 첫 번째 옵션 선택됨:", options[0].title);
          }

          // 종이규격 옵션 가져오기
          updatePaperSizes(val);
        } catch (e) {
          console.error("종이종류 옵션 파싱 오류:", e);
          console.log("서버 응답:", xhr1.responseText);
        }
      } else {
        console.error("종이종류 AJAX 요청 실패:", xhr1.status, xhr1.statusText);
      }
    }
  };
  
  xhr1.open("GET", "get_paper_types.php?CV_no=" + val + "&page=" + phpVars.page, true);
  xhr1.send();
}

// 종이규격 옵션 업데이트
function updatePaperSizes(color_type) {
  console.log("updatePaperSizes 호출됨, color_type:", color_type);
  var f = document.choiceForm;
  var PN_type = f.PN_type;

  // AJAX로 종이규격 옵션 가져오기
  var xhr2 = new XMLHttpRequest();
  xhr2.onreadystatechange = function () {
    if (xhr2.readyState === 4) {
      console.log("종이규격 AJAX 응답 상태:", xhr2.status);
      if (xhr2.status === 200) {
        try {
          console.log("종이규격 서버 응답:", xhr2.responseText);
          var sizes = JSON.parse(xhr2.responseText);
          console.log("파싱된 종이규격 옵션:", sizes);
          
          // 종이규격 옵션 업데이트
          for (var i = 0; i < sizes.length; i++) {
            PN_type.options[i] = new Option(sizes[i].title, sizes[i].no);
          }
          
          // 가격 계산 실행
          setTimeout(function () {
            calc_ok_ajax();
          }, 100);
        } catch (e) {
          console.error("종이규격 옵션 파싱 오류:", e);
          console.log("서버 응답:", xhr2.responseText);
        }
      } else {
        console.error("종이규격 AJAX 요청 실패:", xhr2.status, xhr2.statusText);
      }
    }
  };
  
  xhr2.open("GET", "get_paper_sizes.php?CV_no=" + color_type + "&page=" + phpVars.page, true);
  xhr2.send();
}

// 옵션 변경 시 자동 가격 계산
function calc_re() {
  setTimeout(function () {
    calc_ok_ajax();
  }, 100);
}

// 파일 업로드 관련 함수들
function small_window(myurl) {
  var props = 'scrollbars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=400,height=200';
  var fullUrl = myurl.includes(phpVars.MultyUploadDir + "/") ? myurl : phpVars.MultyUploadDir + "/" + myurl;
  window.open(fullUrl + "&Mode=tt", "Add_from_Src_to_Dest", props);
}

function addUploadedFileToParent(fileName) {
  var parentList = document.getElementsByName("parentList")[0];
  if (!parentList) {
    console.warn("❌ parentList가 존재하지 않습니다.");
    return;
  }
  var newOption = new Option(fileName, fileName);
  parentList.add(newOption);
}

function addToParentList(sourceList) {
  var destinationList = document.getElementsByName("parentList")[0];
  destinationList.innerHTML = "";
  
  for (var i = 0; i < sourceList.options.length; i++) {
    if (sourceList.options[i] !== null) {
      var newOption = new Option(sourceList.options[i].text, sourceList.options[i].value);
      destinationList.add(newOption);
    }
  }
}

function selectList(sourceList) {
  for (var i = 0; i < sourceList.options.length; i++) {
    if (sourceList.options[i] !== null) {
      sourceList.options[i].selected = true;
    }
  }
  return true;
}

function deleteSelectedItemsFromList(sourceList) {
  var maxCnt = sourceList.options.length;
  for (var i = maxCnt - 1; i >= 0; i--) { 
    if ((sourceList.options[i] != null) && (sourceList.options[i].selected == true)) {
      var fileName = encodeURIComponent(sourceList.options[i].value);
      
      window.open(
        phpVars.MultyUploadDir + "/FileDelete.php?FileDelete=ok&Turi=" + phpVars.log_url + 
        "&Ty=" + phpVars.log_y + "&Tmd=" + phpVars.log_md + "&Tip=" + phpVars.log_ip + 
        "&Ttime=" + phpVars.log_time + "&FileName=" + fileName, 
        "", 
        "scrollbars=no,resizable=no,width=100,height=100,top=1500,left=1500"
      );
      
      sourceList.remove(i);
    }
  }
}

function FormCheckField() {
  var f = document.choiceForm;
  var winopts = "width=780,height=590,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
  var popup = window.open('', 'MlangMulty' + phpVars.log_y + phpVars.log_md + phpVars.log_time, winopts);
  popup.focus();
}

function MlangWinExit() {
  if (document.choiceForm.OnunloadChick.value == "on") {
    window.open(
      phpVars.MultyUploadDir + "/FileDelete.php?DirDelete=ok&Turi=" + phpVars.log_url + 
      "&Ty=" + phpVars.log_y + "&Tmd=" + phpVars.log_md + "&Tip=" + phpVars.log_ip + 
      "&Ttime=" + phpVars.log_time, 
      "MlangWinExitsdf", 
      "width=100,height=100,top=2000,left=2000,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes"
    );
  }
}

// 페이지 로드 시 초기화
window.onload = function() {
  var parentList = document.getElementsByName("parentList")[0];
  if (!parentList || parentList.options.length === 0) {
    console.warn("❌ parentList가 비어있음, 파일 추가 필요");
  }

  // 🔧 FIX: 페이지 로드 시에는 옵션 재로드 하지 않음
  // HTML에 이미 100g아트지가 첫 번째로 selected 설정되어 있음
  setTimeout(function () {
    console.log("✅ 초기화: 옵션 재로드 안 함 (HTML 기본값 유지)");
    // 종이종류(MY_Fsd)는 HTML에서 이미 100g아트지가 selected
    // 옵션 재로드하지 않고 그대로 유지
  }, 100);
};

window.onunload = MlangWinExit;