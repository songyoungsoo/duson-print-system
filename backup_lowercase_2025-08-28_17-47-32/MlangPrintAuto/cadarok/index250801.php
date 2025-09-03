<?php
session_start();
$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";

include "$HomeDir/db.php";
$page = $_GET['page'] ?? "cadarok"; // Changed to cadarok as per the request

$Ttable = $page;
include "../ConDb.php";
include "inc.php";
$GGTABLE = "MlangPrintAuto_transactionCate";

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

// 공통 인증 시스템
include "../includes/auth.php";

// 로그인 상태 확인
$is_logged_in = isLoggedIn();
$user_name = $is_logged_in ? getCurrentUser()['name'] : '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📖 두손기획인쇄 - 프리미엄 카다록 주문</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            height: 100%;
            overflow-x: hidden;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
        }
        
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content-wrapper {
            flex: 1;
            padding-bottom: 2rem;
        }
        
        html, body {
            scroll-behavior: smooth;
        }
        
        input, select, textarea, button {
            position: relative;
            z-index: 1;
        }
        
        .top-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
      
        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        .company-info h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .company-info p {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .contact-info {
            display: flex;
            gap: 30px;
        }
        
        .contact-card {
            text-align: right;
            padding: 15px 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .contact-card .label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .contact-card .value {
            font-weight: 700;
            font-size: 1.2rem;
            color: #3498db;
        }
        
        .nav-menu {
            background: white;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .nav-links {
            display: flex;
            justify-content: center;
            gap: 0;
            overflow-x: auto;
        }
        
        .nav-link {
            padding: 18px 25px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-link:hover {
            color: #3498db;
            border-bottom-color: #3498db;
            background: rgba(52, 152, 219, 0.05);
        }
        
        .nav-link.active {
            color: #3498db;
            border-bottom-color: #3498db;
            background: rgba(52, 152, 219, 0.1);
            font-weight: 700;
        }
       
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px 40px 20px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1.5rem;
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }
        
        .card-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .form-column .form-group:not(:last-child) {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: .5rem;
        }
       
        .form-control-modern {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-weight: 500;
        }
        
        .form-control-modern:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }
        
        .price-display {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }

        .price-display .price-item {
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .price-display .price-item .label {
            font-weight: 600;
            color: #495057;
        }

        .price-display .price-item .value {
            font-weight: 700;
            color: #2c3e50;
        }

        .price-display .total {
            border-top: 2px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.5rem;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
        }
        
        .user-info .value {
            color: #2ecc71 !important;
            font-weight: 700;
        }
        
        .login-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        
        .login-modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .login-modal-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            transform: scale(1.1);
            opacity: 0.8;
        }
     
        .login-modal-body {
            padding: 30px;
        }
        
        .login-tabs {
            display: flex;
            margin-bottom: 25px;
            border-radius: 10px;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        .login-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .login-tab.active {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .login-form {
            display: none;
        }
        
        .login-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }
        
        .form-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .form-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
        }
        
        .login-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
        }
        
        .login-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .login-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .modern-footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            margin-top: 2rem;
            border-top: 4px solid #3498db;
            position: relative;
            z-index: 1;
        }
     
        @media (max-width: 768px) {
            .header-content, .contact-info, .nav-links {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script type="text/javascript"><?php include "DbZip.php"; ?></script>

  <iframe name=Tcal frameborder=0 width=0 height=0></iframe>
  <iframe name=cal frameborder=0 width=0 height=0></iframe>
        // PHP 변수를 JavaScript로 전달
        var phpVars = {
            MultyUploadDir: "<?php echo $MultyUploadDir; ?>",
            log_url: "<?php echo $log_url; ?>",
            log_y: "<?php echo $log_y; ?>",
            log_md: "<?php echo $log_md; ?>",
            log_ip: "<?php echo $log_ip; ?>",
            log_time: "<?php echo $log_time; ?>",
            page: "<?php echo $page; ?>"
        };

        // 파일첨부 관련 함수들 (from LittlePrint)
        function small_window(url) {
          window.open(url, 'FileUpload', 'width=500,height=400,scrollbars=yes,resizable=yes');
        }

        function deleteSelectedItemsFromList(selectObj) {
          var i;
          for (i = selectObj.options.length - 1; i >= 0; i--) {
            if (selectObj.options[i].selected) {
              selectObj.options[i] = null;
            }
          }
        }

        function addToParentList(srcList) {
          var parentList = document.choiceForm.parentList; // Changed to choiceForm
          for (var i = 0; i < srcList.options.length; i++) {
            if (srcList.options[i] != null)
              parentList.options[parentList.options.length] = new Option(srcList.options[i].text, srcList.options[i].value);
          }
        }

        // Cadarok specific functions (modified to fit new structure)
        function CheckTotal(mode) {
          var f = document.choiceForm; // Keep choiceForm for now
          
          if (f.StyleForm.value == "" || f.SectionForm.value == "" || f.Order_PriceForm.value == "" || f.Total_PriceForm.value == "") {
            alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\n\n다시 실행 시켜 주십시요...!!");
            return false;
          }
          
          f.action = "/MlangOrder_PrintAuto/OnlineOrder.php?SubmitMode=" + mode;
          f.submit();
        }

        // Original calc functions from cadarok (restored iframe approach)
        function calc() {
          var asd = document.forms["choiceForm"];
          cal.document.location.href = 'price_cal.php?MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value + '&ordertype=' + asd.ordertype.value;
        }

        function calc_ok() {
          var asd = document.forms["choiceForm"];
          cal.document.location.href = 'price_cal.php?MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value + '&ordertype=' + asd.ordertype.value;
        }

        function calc_re() {
          setTimeout(function () {
            calc_ok();
          }, 100);
        }

        // 구분 선택 시 하위 항목들 업데이트 및 가격 계산 (from cadarok, adapted)
        function change_Field(val) {
          var f = document.choiceForm; // Keep choiceForm for now

          // 규격 옵션 업데이트
          var MY_Fsd = document.getElementById('MY_Fsd'); // Use getElementById
          MY_Fsd.options.length = 0;

          // AJAX로 규격 옵션 가져오기
          var xhr1 = new XMLHttpRequest();
          xhr1.onreadystatechange = function () {
            if (xhr1.readyState === 4 && xhr1.status === 200) {
              try {
                var options = JSON.parse(xhr1.responseText);
                MY_Fsd.add(new Option('규격을 선택하세요', '')); // Add default option
                for (var i = 0; i < options.length; i++) {
                  MY_Fsd.options[MY_Fsd.options.length] = new Option(options[i].title, options[i].no);
                }

                // 종이종류 옵션 업데이트
                updatePaperType(val);
              } catch (e) {
                console.error("규격 옵션 파싱 오류:", e);
                console.log("서버 응답:", xhr1.responseText);
              }
            }
          };
          xhr1.open("GET", "get_sizes.php?CV_no=" + val, true);
          xhr1.send();
        }

        // 종이종류 옵션 업데이트 (from cadarok, adapted)
        function updatePaperType(val) {
          var f = document.choiceForm; // Keep choiceForm for now
          var PN_type = document.getElementById('PN_type'); // Use getElementById
          PN_type.options.length = 0;

          // AJAX로 종이종류 옵션 가져오기
          var xhr2 = new XMLHttpRequest();
          xhr2.onreadystatechange = function () {
            if (xhr2.readyState === 4 && xhr2.status === 200) {
              try {
                var options = JSON.parse(xhr2.responseText);
                PN_type.add(new Option('종이종류를 선택하세요', '')); // Add default option
                for (var i = 0; i < options.length; i++) {
                  PN_type.options[PN_type.options.length] = new Option(options[i].title, options[i].no);
                }

                // 가격 계산 실행
                setTimeout(function () {
                  fetchPriceData(); // Call the new fetchPriceData
                }, 100);
              } catch (e) {
                console.error("종이종류 옵션 파싱 오류:", e);
                console.log("서버 응답:", xhr2.responseText);
              }
            }
          };
          xhr2.open("GET", "get_paper_types.php?CV_no=" + val, true);
          xhr2.send();
        }

        // New function to fetch price data using AJAX (combining LittlePrint's approach with cadarok's price_cal.php)
        function fetchPriceData() {
            var form = document.choiceForm;
            if (!form.MY_type.value || !form.MY_Fsd.value || !form.PN_type.value || !form.MY_amount.value || !form.ordertype.value) {
                clearPriceFields();
                return;
            }
            var params = {
                MY_type: form.MY_type.value,
                MY_Fsd: form.MY_Fsd.value,
                PN_type: form.PN_type.value,
                MY_amount: form.MY_amount.value,
                ordertype: form.ordertype.value
            };
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "price_cal.php", true); // Use cadarok's price_cal.php
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // Use form-urlencoded for price_cal.php
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        // price_cal.php returns HTML, not JSON. We need to parse the hidden fields.
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = xhr.responseText;
                        
                        var PriceForm = tempDiv.querySelector('input[name="PriceForm"]').value;
                        var DS_PriceForm = tempDiv.querySelector('input[name="DS_PriceForm"]').value;
                        var Order_PriceForm = tempDiv.querySelector('input[name="Order_PriceForm"]').value;
                        var VAT_PriceForm = tempDiv.querySelector('input[name="VAT_PriceForm"]').value;
                        var Total_PriceForm = tempDiv.querySelector('input[name="Total_PriceForm"]').value;
                        var StyleForm = tempDiv.querySelector('input[name="StyleForm"]').value;
                        var SectionForm = tempDiv.querySelector('input[name="SectionForm"]').value;
                        var QuantityForm = tempDiv.querySelector('input[name="QuantityForm"]').value;
                        var DesignForm = tempDiv.querySelector('input[name="DesignForm"]').value;

                        updatePriceFields({
                            Price: PriceForm,
                            DS_Price: DS_PriceForm,
                            Order_Price: Order_PriceForm,
                            PriceForm: PriceForm,
                            DS_PriceForm: DS_PriceForm,
                            Order_PriceForm: Order_PriceForm,
                            VAT_PriceForm: VAT_PriceForm,
                            Total_PriceForm: Total_PriceForm,
                            StyleForm: StyleForm,
                            SectionForm: SectionForm,
                            QuantityForm: QuantityForm,
                            DesignForm: DesignForm
                        });

                    } catch (e) {
                        console.error('가격 응답 처리 오류:', e, xhr.responseText);
                        clearPriceFields();
                    }
                }
            };
            // Convert params to URL-encoded string
            var encodedParams = Object.keys(params).map(function(key) {
                return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
            }).join('&');
            xhr.send(encodedParams);
        }

        function updatePriceFields(data) {
            var form = document.choiceForm;
            document.getElementById('print_price').textContent = data.Price + "원";
            document.getElementById('design_price').textContent = data.DS_Price + "원";
            document.getElementById('total_price').textContent = data.Order_Price + "원";

            form.PriceForm.value = data.PriceForm;
            form.DS_PriceForm.value = data.DS_PriceForm;
            form.Order_PriceForm.value = data.Order_PriceForm;
            form.VAT_PriceForm.value = data.VAT_PriceForm;
            form.Total_PriceForm.value = data.Total_PriceForm;
            form.StyleForm.value = data.StyleForm;
            form.SectionForm.value = data.SectionForm;
            form.QuantityForm.value = data.QuantityForm;
            form.DesignForm.value = data.DesignForm;
        }

        function clearPriceFields() {
            var form = document.choiceForm;
            var fields = ['PriceForm', 'DS_PriceForm', 'Order_PriceForm', 'VAT_PriceForm', 'Total_PriceForm', 'StyleForm', 'SectionForm', 'QuantityForm', 'DesignForm'];
            fields.forEach(field => { if(form[field]) form[field].value = ''; });
            document.getElementById('print_price').textContent = "0원";
            document.getElementById('design_price').textContent = "0원";
            document.getElementById('total_price').textContent = "0원";
        }

        function updateOptionsAndCalc() {
            var form = document.choiceForm;
            var styleValue = form.MY_type.value;
            change_Field(styleValue); // This will trigger updatePaperType and then fetchPriceData
        }

        window.onload = function() {
            // Initial call to update options and calculate price
            updateOptionsAndCalc();

            // Event listeners for cadarok's form elements
            document.choiceForm.MY_type.onchange = updateOptionsAndCalc;
            var recalcElements = ['MY_Fsd', 'PN_type', 'MY_amount', 'ordertype'];
            recalcElements.forEach(id => { 
                var element = document.getElementById(id); // Use getElementById
                if (element) {
                    element.onchange = calc_ok; // Use calc_ok to trigger iframe load
                }
            });

            // Listen for the iframe to load and update prices
            var calIframe = document.getElementsByName('cal')[0];
            if (calIframe) {
                calIframe.onload = function() {
                    try {
                        var iframeDoc = calIframe.contentDocument || calIframe.contentWindow.document;
                        var form = iframeDoc.forms['choiceForm'] || document.choiceForm; // Fallback to main form if iframe form not found

                        // Read values from the main form's hidden fields (updated by price_cal.php via iframe)
                        var printPrice = document.choiceForm.PriceForm.value;
                        var designPrice = document.choiceForm.DS_PriceForm.value;
                        var orderPrice = document.choiceForm.Order_PriceForm.value;

                        document.getElementById('print_price').textContent = printPrice ? printPrice + "원" : "0원";
                        document.getElementById('design_price').textContent = designPrice ? designPrice + "원" : "0원";
                        document.getElementById('total_price').textContent = orderPrice ? orderPrice + "원" : "0원";

                    } catch (e) {
                        console.error("Error reading from iframe or updating prices:", e);
                    }
                };
            }
        };

        // File upload functions from cadarok (will be integrated)
        function MlangWinExit() {
          if (document.choiceForm.OnunloadChick.value == "on") {
            window.open("<?php echo $MultyUploadDir; ?>/FileDelete.php?DirDelete=ok&Turi=<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>&Ty=<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>&Tmd=<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>&Tip=<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>&Ttime=<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>", "MlangWinExitsdf", "width=100,height=100,top=2000,left=2000,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes");
          }
        }
        window.onunload = MlangWinExit;
    </script>
</head>

<body>
    <div class="page-wrapper">
        <div class="main-content-wrapper">
            <!-- 상단 헤더 -->
            <div class="top-header">
                <div class="header-content">
                    <div class="logo-section">
                        <div class="logo-icon">🎨</div>
                        <div class="company-info">
                            <h1>두손기획인쇄</h1>
                            <p>프리미엄 카다록 주문</p>
                        </div>
                    </div>
                    <div class="contact-info">
                        <div class="contact-card">
                            <div class="label">📞 고객센터</div>
                            <div class="value">1688-2384</div>
                        </div>
                        <div class="contact-card">
                            <div class="label">⏰ 운영시간</div>
                            <div class="value">평일 09:00-18:00</div>
                        </div>
                        <?php if ($is_logged_in): ?>
                        <div class="contact-card user-info">
                            <div class="label">👤 환영합니다</div>
                            <div class="value"><?php echo htmlspecialchars($user_name); ?>님</div>
                            <form method="post" style="margin-top: 10px;">
                                <button type="submit" name="logout_action" class="logout-btn">로그아웃</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="contact-card login-card">
                            <button onclick="showLoginModal()" class="login-btn">🔐 로그인</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
           <!-- 네비게이션 메뉴 -->
            <div class="nav-menu">
                <div class="nav-content">
                    <div class="nav-links">
                        <a href="/mlangprintauto/inserted/index.php" class="nav-link">📄 전단지</a>
                        <a href="/shop/view_modern.php" class="nav-link">🏷️ 스티커</a>
                        <a href="/mlangprintauto/cadarok/index.php" class="nav-link active">📖 카다록</a>
                        <a href="/mlangprintauto/namecard/index.php" class="nav-link">📇 명함</a>
                        <a href="/mlangprintauto/merchandisebond/index.php" class="nav-link">🎫 상품권</a>
                        <a href="/mlangprintauto/envelope/index.php" class="nav-link">✉️ 봉투</a>
                        <a href="/mlangprintauto/littleprint/index.php" class="nav-link">🎨 포스터</a>
                        <a href="/shop/cart.php" class="nav-link cart">🛒 장바구니</a>
                    </div>
                </div>
            </div>

            <div class="container">
                <form name='choiceForm' method='post'> <!-- Kept choiceForm name for now -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">📝 카다록 주문 옵션 선택</h2>
                            <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
                        </div>
                        <div class="form-grid">
                            <div class="form-column">
                                <div class="form-group">
                                    <label for="MY_type">구분</label>
                                    <select id="MY_type" class="form-control-modern" name='MY_type'>
                                      <?php
                                      // Re-include db.php if it was closed in cadarok's original code
                                      // include "../../db.php"; // This might cause issues if db is already connected
                                      $Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC");
                                      $Cate_rows = mysqli_num_rows($Cate_result);
                                      if ($Cate_rows) {
                                        while ($Cate_row = mysqli_fetch_array($Cate_result)) {
                                          echo "<option value='" . htmlspecialchars($Cate_row['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($Cate_row['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                                        }
                                      }
                                      ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="MY_Fsd">규격</label>
                                    <select id="MY_Fsd" class="form-control-modern" name="MY_Fsd"><option value="">규격을 선택하세요</option></select>
                                </div>
                                <div class="form-group">
                                    <label for="PN_type">종이종류</label>
                                    <select id="PN_type" class="form-control-modern" name="PN_type"><option value="">종이종류를 선택하세요</option></select>
                                </div>
                                <div class="form-group">
                                    <label for="MY_amount">수량</label>
                                    <select id="MY_amount" class="form-control-modern" name="MY_amount">
                                      <option value='1000'>1000부</option>
                                      <option value='2000'>2000부</option>
                                      <option value='3000'>3000부</option>
                                      <option value='4000'>4000부</option>
                                      <option value='5000'>5000부</option>
                                      <option value='기타'>기타</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="ordertype">주문방법</label>
                                    <select id="ordertype" class="form-control-modern" name="ordertype">
                                      <option value='total'>디자인+인쇄</option>
                                      <option value='print'>인쇄만 의뢰</option>
                                      <option value='design'>디자인만 의뢰</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-column">
                                <div class="price-display">
                                    <div class="price-item">
                                        <span class="label">인쇄비</span>
                                        <span class="value" id="print_price">0원</span>
                                    </div>
                                    <div class="price-item">
                                        <span class="label">편집비</span>
                                        <span class="value" id="design_price">0원</span>
                                    </div>
                                    <div class="price-item total">
                                        <span class="label">총 금액 (VAT 별도)</span>
                                        <span class="value" id="total_price">0원</span>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 1.5rem;">
                                    <label>파일첨부</label>
                                    <select size="3" style="width:100%; height:80px;" name="parentList" multiple></select>
                                    <div style="margin-top: .5rem;">
                                        <input type="button" onClick="javascript:small_window('<?php echo $MultyUploadDir; ?>/FileUp.php?Turi=<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>&Ty=<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>&Tmd=<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>&Tip=<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>&Ttime=<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>&Mode=tt');" value="파일올리기">
                                        <input type="button" onclick="javascript:deleteSelectedItemsFromList(parentList);" value="삭제">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="textarea">기타사항</label>
                                    <textarea id="textarea" name="textarea" class="form-control-modern" rows="3"><?php echo htmlspecialchars($textarea ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="form-group">
                                     <input type="button" onClick="CheckTotal('OrderOne');" value="주문하기" class="form-submit">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Hidden Fields -->
                    <input type="hidden" name="OnunloadChick" value="on">
                    <input type='hidden' name='Turi' value='<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Ty' value='<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Tmd' value='<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Tip' value='<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Ttime' value='<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type="hidden" name="ImgFolder" value="<?php echo htmlspecialchars($log_url . "/" . $log_y . "/" . $log_md . "/" . $log_ip . "/" . $log_time, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type='hidden' name='OrderSytle' value='카다록'> <!-- Changed to 카다록 -->
                    <input type='hidden' name='StyleForm'>
                    <input type='hidden' name='SectionForm'>
                    <input type='hidden' name='QuantityForm'>
                    <input type='hidden' name='DesignForm'>
                    <input type='hidden' name='PriceForm'>
                    <input type='hidden' name='DS_PriceForm'>
                    <input type='hidden' name='Order_PriceForm'>
                    <input type='hidden' name='VAT_PriceForm'>
                    <input type='hidden' name='Total_PriceForm'>
                    <input type='hidden' name='page' value='<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>'>  
                </form>
            </div>
        </div>
     
        <div id="loginModal" class="login-modal">
            <div class="login-modal-content">
                <div class="login-modal-header">
                    <h2>🔐 로그인 / 회원가입</h2>
                    <span class="close-modal" onclick="hideLoginModal()">&times;</span>
                </div>
                <div class="login-modal-body">
                    <?php if (!empty($login_message)): ?>
                    <div class="login-message <?php echo (strpos($login_message, '성공') !== false || strpos($login_message, '완료') !== false) ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($login_message); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="login-tabs">
                        <button class="login-tab active" onclick="showLoginTab()">로그인</button>
                        <button class="login-tab" onclick="showRegisterTab()">회원가입</button>
                    </div>
                    
                    <form id="loginForm" class="login-form active" method="post">
                        <div class="form-group">
                            <label for="username">아이디</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">비밀번호</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login_action" class="form-submit">로그인</button>
                    </form>
                    
                    <form id="registerForm" class="login-form" method="post">
                        <div class="form-group">
                            <label for="reg_username">아이디 *</label>
                            <input type="text" id="reg_username" name="reg_username" required>
                        </div>
                        <div class="form-group">
                            <label for="reg_password">비밀번호 * (6자 이상)</label>
                            <input type="password" id="reg_password" name="reg_password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="reg_confirm_password">비밀번호 확인 *</label>
                            <input type="password" id="reg_confirm_password" name="reg_confirm_password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="reg_name">이름 *</label>
                            <input type="text" id="reg_name" name="reg_name" required>
                        </div>
                        <div class="form-group">
                            <label for="reg_email">이메일</label>
                            <input type="email" id="reg_email" name="reg_email">
                        </div>
                        <div class="form-group">
                            <label for="reg_phone">전화번호</label>
                            <input type="tel" id="reg_phone" name="reg_phone">
                        </div>
                        <button type="submit" name="register_action" class="form-submit">회원가입</button>
                    </form>
                </div>
            </div>
        </div>

        <footer class="modern-footer">
            <div style="max-width: 1200px; margin: 0 auto; padding: 3rem 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
                <div>
                    <h3 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">🖨️ 두손기획인쇄</h3>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📍 주소: 서울 영등포구 영등포로 36길9 송호빌딩 1층</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📞 전화: 1688-2384</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📠 팩스: 02-2632-1829</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📧 이메일: dsp1830@naver.com</p>
                </div>

                <div>
                    <h4 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">🎯 주요 서비스</h4>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📄 전단지 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🏷️ 스티커 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📇 명함 인쇄</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📖 카다록 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🎨 포스터 인쇄</p>
                </div>

                <div>
                    <h4 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">⏰ 운영 안내</h4>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">평일: 09:00 - 18:00</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">토요일: 09:00 - 15:00</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">일요일/공휴일: 휴무</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">점심시간: 12:00 - 13:00</p>
                </div>
            </div>
            
            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding: 2rem 20px; text-align: center; background: rgba(0,0,0,0.2);">
                <p style="color: #bdc3c7; font-size: 0.95rem;">© 2024 두손기획인쇄. All rights reserved. | 제작: Mlang (010-8946-7038)</p>
            </div>
        </footer>
    </div> 
    <script>
        function showLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
        }
        
        function hideLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }
        
        function showLoginTab() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
            document.querySelector('.login-tab.active').classList.remove('active');
            event.target.classList.add('active');
        }
        
        function showRegisterTab() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
            document.querySelector('.login-tab.active').classList.remove('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
<?php
if ($db) {
    mysqli_close($db);
}
?>