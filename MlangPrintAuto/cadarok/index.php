<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 페이지 설정
$page_title = '📚 두손기획인쇄 - 프리미엄 카다록/리플렛 주문';
$current_page = 'cadarok';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 카다록 관련 설정
$page = "cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";
$MultyUploadDir = "../../PHPClass/MultyUpload";

// 로그 세부 정보
$log_url = str_replace("/", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y");
$log_md = date("md");
$log_ip = $_SERVER['REMOTE_ADDR'];
$log_time = time();

// 드롭다운 옵션을 가져오는 함수들
function getCategoryOptions($connect, $GGTABLE, $page) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
    return $options;
}

function getSizeOptions($connect, $GGTABLE, $category_no) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE BigNo='$category_no' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
    return $options;
}

function getPaperTypeOptions($connect, $GGTABLE, $category_no) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE TreeNo='$category_no' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
    return $options;
}

function getQuantityOptionsCadarok($connect) {
    $options = [];
    $TABLE = "MlangPrintAuto_cadarok";
    
    // 고유한 수량 옵션들을 가져오기
    $query = "SELECT DISTINCT quantity FROM $TABLE WHERE quantity IS NOT NULL ORDER BY CAST(quantity AS UNSIGNED) ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'quantity' => $row['quantity']
            ];
        }
    }
    return $options;
}

// 초기 옵션 데이터 가져오기
$categoryOptions = getCategoryOptions($connect, $GGTABLE, $page);
$firstCategoryNo = !empty($categoryOptions) ? $categoryOptions[0]['no'] : '1';
$sizeOptions = getSizeOptions($connect, $GGTABLE, $firstCategoryNo);
$paperTypeOptions = getPaperTypeOptions($connect, $GGTABLE, $firstCategoryNo);
$quantityOptions = getQuantityOptionsCadarok($connect);

// 공통 인증 처리 포함
include "../../includes/auth.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 공통 헤더 포함
include "../../includes/header.php";
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';
?>

            <div class="container">
                <!-- 주문 폼 -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📚 카다록/리플렛 주문 옵션 선택</h2>
                        <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
                    </div>
                    
                    <form name="choiceForm" method="post" action="order_process.php">
                        <input type="hidden" name="action" value="calculate">
                        
                        <!-- 가격 계산 결과를 저장할 hidden 필드들 -->
                        <input type="hidden" name="Price" value="">
                        <input type="hidden" name="DS_Price" value="">
                        <input type="hidden" name="Order_Price" value="">
                        <input type="hidden" name="PriceForm" value="">
                        <input type="hidden" name="DS_PriceForm" value="">
                        <input type="hidden" name="Order_PriceForm" value="">
                        <input type="hidden" name="VAT_PriceForm" value="">
                        <input type="hidden" name="Total_PriceForm" value="">
                        <input type="hidden" name="StyleForm" value="">
                        <input type="hidden" name="SectionForm" value="">
                        <input type="hidden" name="QuantityForm" value="">
                        <input type="hidden" name="DesignForm" value="">
                        <input type="hidden" name="OnunloadChick" value="off">
                        
                        <table class="order-form-table">
                            <tbody>
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📚</span>
                                            <span>1. 구분</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_type" id="MY_type" class="form-control-modern" onchange="change_Field(this.value)">
                                            <?php foreach ($categoryOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                                <?php echo htmlspecialchars($option['title']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="help-text">카다록 종류를 선택해주세요</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📏</span>
                                            <span>2. 규격</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_Fsd" id="MY_Fsd" class="form-control-modern" onchange="updatePaperType(this.value);">
                                            <?php foreach ($sizeOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                                <?php echo htmlspecialchars($option['title']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="help-text">카다록 규격을 선택해주세요</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📄</span>
                                            <span>3. 종이종류</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="PN_type" id="PN_type" class="form-control-modern">
                                            <?php foreach ($paperTypeOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                                <?php echo htmlspecialchars($option['title']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="help-text">용지 종류를 선택해주세요</small>
                                    </td>
                                </tr>  
                              
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📦</span>
                                            <span>4. 수량</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_amount" id="MY_amount" class="form-control-modern">
                                            <option value="1000">1000부</option>
                                            <option value="2000">2000부</option>
                                            <option value="3000">3000부</option>
                                            <option value="4000">4000부</option>
                                            <option value="5000">5000부</option>
                                            <option value="기타">기타</option>
                                        </select>
                                        <small class="help-text">필요한 수량을 선택해주세요</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">✏️</span>
                                            <span>5. 주문방법</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="ordertype" class="form-control-modern">
                                            <option value="print">인쇄만 의뢰</option>
                                            <option value="total">디자인+인쇄</option>
                                        </select>
                                        <small class="help-text">디자인 포함 여부를 선택해주세요</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- 가격 계산 버튼 -->
                        <div class="button-group" style="text-align: center; margin: 30px 0;">
                            <button type="button" class="btn-calculate" onclick="calc_ok()">
                                💰 가격 계산하기
                            </button>
                        </div>
                        
                        <!-- 가격 결과 표시 영역 -->
                        <div id="priceSection" class="price-result" style="display: none;">
                            <h3>💰 견적 결과</h3>
                            
                            <!-- 견적 결과 표 -->
                            <table class="quote-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <thead>
                                    <tr>
                                        <th style="background: #f8f9fa; color: #495057; font-weight: 600; padding: 12px 15px; text-align: left; border-bottom: 2px solid #e9ecef; font-size: 0.95rem;">항목</th>
                                        <th style="background: #f8f9fa; color: #495057; font-weight: 600; padding: 12px 15px; text-align: left; border-bottom: 2px solid #e9ecef; font-size: 0.95rem;">내용</th>
                                        <th style="background: #f8f9fa; color: #495057; font-weight: 600; padding: 12px 15px; text-align: left; border-bottom: 2px solid #e9ecef; font-size: 0.95rem;">금액</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- 옵션 정보 행들 -->
                                    <tr>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">구분</td>
                                        <td id="selectedCategory" style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem; font-weight: 600;">-</td>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">-</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">규격</td>
                                        <td id="selectedSize" style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem; font-weight: 600;">-</td>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">-</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">종이종류</td>
                                        <td id="selectedPaper" style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem; font-weight: 600;">-</td>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">-</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">수량</td>
                                        <td id="selectedQuantity" style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem; font-weight: 600;">-</td>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">-</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">주문방법</td>
                                        <td id="selectedOrder" style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem; font-weight: 600;">-</td>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">-</td>
                                    </tr>
                                    
                                    <!-- 가격 정보 행들 -->
                                    <tr style="background: #f1f3f4;">
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">인쇄비</td>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 0.95rem;">-</td>
                                        <td id="priceAmount" style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #2196f3; font-size: 0.95rem; font-weight: 600;">0원</td>
                                    </tr>
                                    
                                    <!-- 합계 행들 -->
                                    <tr style="background: #e8f5e8; border-top: 2px solid #4caf50;">
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 1rem; font-weight: 600;"><strong>총 금액 (부가세 포함)</strong></td>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 1rem; font-weight: 600;">-</td>
                                        <td id="priceVat" style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #4caf50; font-size: 1rem; font-weight: 700;"><strong>0원</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="action-buttons">
                                <button type="button" class="btn-action btn-primary" onclick="addToCart()">
                                    🛒 장바구니 담기
                                </button>
                                <button type="button" class="btn-action btn-secondary" onclick="proceedToOrder()">
                                    � 주문 하기
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- 파일 업로드 섹션 -->
                    <div class="upload-section" style="text-align: center; margin: 40px 0;">
                        <h3 style="text-align: center; margin-bottom: 15px;">📎 디자인 파일 업로드</h3>
                        <p class="upload-description" style="text-align: center; margin-bottom: 20px;">카다록 디자인 파일을 업로드해주세요. (JPG, PNG, PDF 파일 지원, 최대 25MB)</p>
                        
                        <?php
                        // 카다록용 파일 업로드 컴포넌트 설정
                        $uploadComponent = new FileUploadComponent([
                            'product_type' => 'cadarok',
                            'max_file_size' => 25 * 1024 * 1024, // 25MB
                            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'],
                            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip'],
                            'multiple' => true,
                            'drag_drop' => true,
                            'show_progress' => true,
                            'auto_upload' => true,
                            'delete_enabled' => true,
                            'custom_messages' => [
                                'title' => '카다록 디자인 파일 업로드',
                                'drop_text' => '파일을 드래그하거나 클릭하여 선택하세요',
                                'format_text' => '지원 형식: JPG, PNG, PDF, ZIP (최대 25MB)'
                            ]
                        ]);
                        
                        echo $uploadComponent->render();
                        ?>
                    </div>
                </div>
            </div>

<?php
// 공통 로그인 모달 포함
include "../../includes/login_modal.php";
?>

    <script>
    // 선택한 옵션 요약을 초기화하는 함수
    function resetSelectedOptions() {
        document.getElementById('selectedCategory').textContent = '-';
        document.getElementById('selectedSize').textContent = '-';
        document.getElementById('selectedPaper').textContent = '-';
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedOrder').textContent = '-';
        document.getElementById('priceSection').style.display = 'none';
    }

    // 선택된 옵션들을 업데이트하는 함수
    function updateSelectedOptions() {
        const form = document.forms['choiceForm'];
        
        const categorySelect = form.MY_type;
        const sizeSelect = form.MY_Fsd;
        const paperSelect = form.PN_type;
        const quantitySelect = form.MY_amount;
        const orderSelect = form.ordertype;
        
        if (categorySelect && categorySelect.selectedIndex >= 0 && categorySelect.options[categorySelect.selectedIndex]) {
            document.getElementById('selectedCategory').textContent = 
                categorySelect.options[categorySelect.selectedIndex].text;
        }
        
        if (sizeSelect && sizeSelect.selectedIndex >= 0 && sizeSelect.options[sizeSelect.selectedIndex]) {
            document.getElementById('selectedSize').textContent = 
                sizeSelect.options[sizeSelect.selectedIndex].text;
        }
        
        if (paperSelect && paperSelect.selectedIndex >= 0 && paperSelect.options[paperSelect.selectedIndex]) {
            document.getElementById('selectedPaper').textContent = 
                paperSelect.options[paperSelect.selectedIndex].text;
        }
        
        if (quantitySelect && quantitySelect.selectedIndex >= 0 && quantitySelect.options[quantitySelect.selectedIndex]) {
            document.getElementById('selectedQuantity').textContent = 
                quantitySelect.options[quantitySelect.selectedIndex].text;
        }
        
        if (orderSelect && orderSelect.selectedIndex >= 0 && orderSelect.options[orderSelect.selectedIndex]) {
            document.getElementById('selectedOrder').textContent = 
                orderSelect.options[orderSelect.selectedIndex].text;
        }
    }

    function MlangWinExit() {
      if (document.forms['choiceForm'].OnunloadChick.value == "on") {
        window.open("<?php echo $MultyUploadDir; ?>/FileDelete.php?DirDelete=ok&Turi=<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>&Ty=<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>&Tmd=<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>&Tip=<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>&Ttime=<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>", "MlangWinExitsdf", "width=100,height=100,top=2000,left=2000,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes");
      }
    }
    window.onunload = MlangWinExit;

    function calc_ok() {
      console.log('가격 계산 시작');
      var form = document.forms["choiceForm"];
      
      // AJAX로 가격 계산 요청
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          try {
            var response = JSON.parse(xhr.responseText);
            
            // 폼의 hidden 필드들 업데이트
            form.Price.value = response.PriceForm;
            form.DS_Price.value = response.DS_PriceForm;
            form.Order_Price.value = response.Order_PriceForm;
            form.PriceForm.value = response.PriceForm;
            form.DS_PriceForm.value = response.DS_PriceForm;
            form.Order_PriceForm.value = response.Order_PriceForm;
            form.VAT_PriceForm.value = response.VAT_PriceForm;
            form.Total_PriceForm.value = response.Total_PriceForm;
            form.StyleForm.value = response.StyleForm;
            form.SectionForm.value = response.SectionForm;
            form.QuantityForm.value = response.QuantityForm;
            form.DesignForm.value = response.DesignForm;
            
            // 화면에 가격 표시
            document.getElementById('priceAmount').textContent = 
              response.PriceForm ? parseInt(response.PriceForm).toLocaleString() + '원' : '0원';
            document.getElementById('priceVat').textContent = 
              response.Total_PriceForm ? parseInt(response.Total_PriceForm).toLocaleString() + '원' : '0원';
            
            // 선택된 옵션 요약 업데이트
            updateSelectedOptions();
            
            // 가격 섹션 표시
            document.getElementById('priceSection').style.display = 'block';
            document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
            
          } catch (e) {
            console.error("가격 계산 응답 파싱 오류:", e);
            console.log("서버 응답:", xhr.responseText);
          }
        }
      };
      
      // POST 방식으로 데이터 전송
      var formData = new FormData();
      formData.append('MY_type', form.MY_type.value);
      formData.append('PN_type', form.PN_type.value);
      formData.append('MY_Fsd', form.MY_Fsd.value);
      formData.append('MY_amount', form.MY_amount.value);
      formData.append('ordertype', form.ordertype.value);
      
      xhr.open("POST", "price_cal.php", true);
      xhr.send(formData);
    }

    function calc_re() {
      setTimeout(function () {
        calc_ok();
      }, 100);
    }

    // 구분 선택 시 하위 항목들 업데이트 및 가격 계산 (cadarok 기존 로직)
    function change_Field(val) {
      console.log("change_Field 호출됨, val:", val);
      var f = document.forms['choiceForm'];

      // 규격 옵션 업데이트
      var MY_Fsd = document.getElementById('MY_Fsd');
      MY_Fsd.options.length = 0;

      var xhr1 = new XMLHttpRequest();
      xhr1.onreadystatechange = function () {
        if (xhr1.readyState === 4 && xhr1.status === 200) {
          console.log("규격 서버 응답:", xhr1.responseText);
          try {
            var options = JSON.parse(xhr1.responseText);
            console.log("규격 옵션 개수:", options.length);
            for (var i = 0; i < options.length; i++) {
              MY_Fsd.options[MY_Fsd.options.length] = new Option(options[i].title, options[i].no);
            }
            // 첫 번째 규격을 자동 선택하고 종이종류 업데이트
            if (options.length > 0) {
              MY_Fsd.selectedIndex = 0;
              console.log("첫 번째 규격 선택됨:", options[0].title, "no:", options[0].no);
              updatePaperType(options[0].no);
            }
          } catch (e) {
            console.error("규격 옵션 파싱 오류:", e);
            console.log("서버 응답:", xhr1.responseText);
          }
        }
      };
      var url = "get_sizes.php?CV_no=" + val;
      console.log("규격 요청 URL:", url);
      xhr1.open("GET", url, true);
      xhr1.send();
    }

    // 종이종류 옵션 업데이트 (cadarok 기존 로직)
    function updatePaperType(val) {
      console.log("updatePaperType 호출됨, val:", val);
      var f = document.forms['choiceForm'];
      var PN_type = document.getElementById('PN_type');
      PN_type.options.length = 0;

      var xhr2 = new XMLHttpRequest();
      xhr2.onreadystatechange = function () {
        if (xhr2.readyState === 4 && xhr2.status === 200) {
          console.log("종이종류 서버 응답:", xhr2.responseText);
          try {
            var options = JSON.parse(xhr2.responseText);
            console.log("종이종류 옵션 개수:", options.length);
            for (var i = 0; i < options.length; i++) {
              PN_type.options[PN_type.options.length] = new Option(options[i].title, options[i].no);
            }
            // 첫 번째 종이종류를 자동 선택
            if (options.length > 0) {
              PN_type.selectedIndex = 0;
              console.log("첫 번째 종이종류 선택됨:", options[0].title);
            } else {
              console.log("종이종류 옵션이 없습니다.");
            }
          } catch (e) {
            console.error("종이종류 옵션 파싱 오류:", e);
            console.log("서버 응답:", xhr2.responseText);
          }
        }
      };
      var url = "get_paper_types.php?CV_no=" + val;
      console.log("종이종류 요청 URL:", url);
      xhr2.open("GET", url, true);
      xhr2.send();
    }

    // 장바구니 담기 함수
    function addToCart() {
        const form = document.forms['choiceForm'];
        
        // 가격 계산이 되었는지 확인
        if (!form.PriceForm.value) {
            alert('먼저 가격 계산을 해주세요.');
            return;
        }
        
        // 장바구니에 추가할 데이터 준비
        const cartData = {
            product_type: 'cadarok',
            MY_type: form.MY_type.value,
            MY_Fsd: form.MY_Fsd.value,
            PN_type: form.PN_type.value,
            MY_amount: form.MY_amount.value,
            ordertype: form.ordertype.value,
            st_price: form.PriceForm.value,
            st_price_vat: form.Total_PriceForm.value,
            MY_comment: '카다록/리플렛 주문'
        };
        
        // AJAX로 장바구니에 추가
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert('장바구니에 추가되었습니다.');
                            // 장바구니 페이지로 이동
                            window.location.href = '../shop/cart.php';
                        } else {
                            alert('장바구니 추가 실패: ' + (response.message || '알 수 없는 오류'));
                        }
                    } catch (e) {
                        console.error('응답 파싱 오류:', e);
                        alert('장바구니 추가 중 오류가 발생했습니다.');
                    }
                } else {
                    alert('서버 오류가 발생했습니다.');
                }
            }
        };
        
        // POST 방식으로 데이터 전송
        const formData = new FormData();
        for (const key in cartData) {
            formData.append(key, cartData[key]);
        }
        
        xhr.open('POST', '../shop/add_to_basket.php', true);
        xhr.send(formData);
    }
    
    // 주문하기 함수
    function proceedToOrder() {
        const form = document.forms['choiceForm'];
        
        // 가격 계산이 되었는지 확인
        if (!form.PriceForm.value) {
            alert('먼저 가격 계산을 해주세요.');
            return;
        }
        
        // 주문 데이터를 폼으로 전송
        const orderForm = document.createElement('form');
        orderForm.method = 'POST';
        orderForm.action = '../../MlangOrder_PrintAuto/OnlineOrder_unified.php';
        
        // 주문 데이터 준비
        const orderData = {
            product_type: 'cadarok',
            Type: '카다록/리플렛',
            MY_type: form.MY_type.value,
            MY_Fsd: form.MY_Fsd.value,
            PN_type: form.PN_type.value,
            MY_amount: form.MY_amount.value,
            ordertype: form.ordertype.value,
            Price: form.PriceForm.value,
            DS_Price: form.DS_PriceForm.value,
            Order_Price: form.Order_PriceForm.value,
            VAT_Price: form.VAT_PriceForm.value,
            Total_Price: form.Total_PriceForm.value,
            // 선택된 옵션 텍스트들
            selected_category: document.getElementById('selectedCategory').textContent,
            selected_size: document.getElementById('selectedSize').textContent,
            selected_paper: document.getElementById('selectedPaper').textContent,
            selected_quantity: document.getElementById('selectedQuantity').textContent,
            selected_order: document.getElementById('selectedOrder').textContent
        };
        
        // 폼에 hidden 필드들 추가
        for (const key in orderData) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = orderData[key];
            orderForm.appendChild(input);
        }
        
        // 폼을 body에 추가하고 제출
        document.body.appendChild(orderForm);
        orderForm.submit();
    }

    // 페이지 로드 시 초기화 및 이벤트 리스너 설정
    document.addEventListener('DOMContentLoaded', function() {
        // 초기 옵션 로드 (가격 계산은 버튼 클릭 시에만)
        var initialType = document.getElementById('MY_type').value;
        change_Field(initialType);

        // 입력값 변경 시 실시간 유효성 검사
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('change', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '#27ae60';
                } else {
                    this.style.borderColor = '#e74c3c';
                }
            });
        });

    });
    </script>

<?php
// 공통 푸터 포함
include "../../includes/footer.php";

if ($connect) {
    mysqli_close($connect);
}
?>