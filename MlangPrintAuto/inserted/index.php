<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 페이지 설정
$page_title = '📄 두손기획인쇄 - 프리미엄 전단지 주문';
$current_page = 'leaflet';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 전단지 관련 설정
$page = "inserted";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 드롭다운 옵션을 가져오는 함수들
function getColorOptions($connect, $GGTABLE, $page) {
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

function getPaperTypes($connect, $GGTABLE, $color_no) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE TreeNo='$color_no' ORDER BY no ASC";
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

function getPaperSizes($connect, $GGTABLE, $color_no) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE BigNo='$color_no' ORDER BY no ASC";
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

function getQuantityOptions($connect) {
    $options = [];
    $TABLE = "MlangPrintAuto_inserted";
    
    // 고유한 수량 옵션들을 가져오기 (quantity와 quantityTwo 함께)
    $query = "SELECT DISTINCT quantity, quantityTwo FROM $TABLE WHERE quantity IS NOT NULL AND quantityTwo IS NOT NULL ORDER BY CAST(quantity AS DECIMAL(10,1)) ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'quantity' => $row['quantity'],
                'quantityTwo' => $row['quantityTwo']
            ];
        }
    }
    return $options;
}



// 초기 옵션 데이터 가져오기
$colorOptions = getColorOptions($connect, $GGTABLE, $page);
$firstColorNo = !empty($colorOptions) ? $colorOptions[0]['no'] : '1';
$paperTypeOptions = getPaperTypes($connect, $GGTABLE, $firstColorNo);
$paperSizeOptions = getPaperSizes($connect, $GGTABLE, $firstColorNo);
$quantityOptions = getQuantityOptions($connect);

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
                        <h2 class="card-title">📝 전단지 주문 옵션 선택</h2>
                        <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
                    </div>
                    
                    <form id="orderForm" method="post">
                        <input type="hidden" name="action" value="calculate">
                        
                        <table class="order-form-table">
                            <tbody>
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">🎨</span>
                                            <span>1. 인쇄색상</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_type" class="form-control-modern" onchange="resetSelectedOptions(); changeColorType(this.value)">
                                            <?php foreach ($colorOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                                <?php echo htmlspecialchars($option['title']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="help-text">색상이 많을수록 생동감 있는 인쇄물이 완성됩니다</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📄</span>
                                            <span>2. 종이종류</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_Fsd" class="form-control-modern" onchange="resetSelectedOptions(); updateQuantities()">
                                            <?php foreach ($paperTypeOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                                <?php echo htmlspecialchars($option['title']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="help-text">용도에 맞는 종이를 선택해주세요</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📏</span>
                                            <span>3. 종이규격</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="PN_type" class="form-control-modern" onchange="resetSelectedOptions(); updateQuantities()">
                                            <?php 
                                            foreach ($paperSizeOptions as $option): 
                                                $isA4 = false;
                                                // A4(210x297) 정확히 찾기
                                                if (stripos($option['title'], 'A4') !== false && 
                                                    stripos($option['title'], '210') !== false && 
                                                    stripos($option['title'], '297') !== false) {
                                                    $isA4 = true;
                                                }
                                            ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>" 
                                                <?php echo $isA4 ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($option['title']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="help-text">배포 목적에 맞는 크기를 선택해주세요</small>
                                    </td>
                                </tr>  
                              
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">🔄</span>
                                            <span>4. 인쇄면</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="POtype" class="form-control-modern" onchange="resetSelectedOptions()">
                                            <option value="1" selected>단면 (앞면만)</option>
                                            <option value="2">양면 (앞뒤 모두)</option>
                                        </select>
                                        <small class="help-text">양면 인쇄 시 더 많은 정보를 담을 수 있습니다</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📦</span>
                                            <span>5. 수량</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_amount" class="form-control-modern" onchange="resetSelectedOptions()">
                                            <option value="">수량을 선택해주세요</option>
                                        </select>
                                        <small class="help-text">수량이 많을수록 단가가 저렴해집니다</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">✏️</span>
                                            <span>6. 디자인(편집)</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="ordertype" class="form-control-modern" onchange="resetSelectedOptions()">
                                            <option value="total">디자인+인쇄 (전체 의뢰)</option>
                                            <option value="print">인쇄만 의뢰 (파일 준비완료)</option>
                                        </select>
                                        <small class="help-text">디자인 파일이 없으시면 디자인+인쇄를 선택해주세요</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="text-align: center; margin: 1.5rem 0;"> <!-- 마진 절반으로 감소 -->
                            <button type="button" onclick="calculatePrice()" class="btn-calculate">
                                💰 실시간 가격 계산하기
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- 가격 계산 결과 -->
                <div id="priceSection" class="price-result">
                    <h3>💎 견적 결과</h3>
                    
                    <!-- 선택한 옵션 요약 -->
                    <div id="selectedOptions" class="selected-options">
                        <h4>📋 선택한 옵션</h4>
                        <div class="option-summary">
                            <div class="option-item">
                                <span class="option-label">🎨 인쇄색상:</span>
                                <span id="selectedColor" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">📄 종이종류:</span>
                                <span id="selectedPaperType" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">📏 종이규격:</span>
                                <span id="selectedPaperSize" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">🔄 인쇄면:</span>
                                <span id="selectedSides" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">📦 수량:</span>
                                <span id="selectedQuantity" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">✏️ 디자인:</span>
                                <span id="selectedDesign" class="option-value">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="price-amount" id="priceAmount">0원</div>
                    <div>부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700;">0원</span></div>
                    
                    <?php
                    // 전단지용 업로드 컴포넌트 설정
                    $uploadComponent = new FileUploadComponent([
                        'product_type' => 'leaflet',
                        'max_file_size' => 15 * 1024 * 1024, // 15MB
                        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'],
                        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip'],
                        'multiple' => true,
                        'drag_drop' => true,
                        'show_progress' => true,
                        'auto_upload' => true,
                        'delete_enabled' => true,
                        'custom_messages' => [
                            'title' => '전단지 디자인 파일 업로드',
                            'drop_text' => '전단지 디자인 파일을 여기로 드래그하거나 클릭하여 선택하세요',
                            'format_text' => '지원 형식: JPG, PNG, PDF, ZIP (최대 15MB)'
                        ]
                    ]);
                    
                    // 컴포넌트 렌더링
                    echo $uploadComponent->render();
                    ?>
                    
                    <div class="action-buttons">
                        <button onclick="addToBasket()" class="btn-action btn-primary">
                            🛒 장바구니에 담기
                        </button>
                        <button onclick="directOrder()" class="btn-action btn-secondary">
                            📋 바로 주문하기
                        </button>
                    </div>
                </div>
            </div>
        </div> <!-- main-content-wrapper 끝 -->   
     
<?php
// 공통 로그인 모달 포함
include "../../includes/login_modal.php";
?>

<?php
// 공통 푸터 포함
include "../../includes/footer.php";
?>    

    <script>
    // 로그인 메시지가 있으면 모달 자동 표시
    <?php if (!empty($login_message)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showLoginModal();
        <?php if (strpos($login_message, '성공') !== false): ?>
        setTimeout(hideLoginModal, 2000); // 로그인 성공 시 2초 후 자동 닫기
        <?php endif; ?>
    });
    <?php endif; ?>
    
    // 선택한 옵션 요약을 초기화하는 함수
    function resetSelectedOptions() {
        document.getElementById('selectedColor').textContent = '-';
        document.getElementById('selectedPaperType').textContent = '-';
        document.getElementById('selectedPaperSize').textContent = '-';
        document.getElementById('selectedSides').textContent = '-';
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.getElementById('orderForm');
        
        // 각 select 요소에서 선택된 옵션의 텍스트 가져오기
        const colorSelect = form.querySelector('select[name="MY_type"]');
        const paperTypeSelect = form.querySelector('select[name="MY_Fsd"]');
        const paperSizeSelect = form.querySelector('select[name="PN_type"]');
        const sidesSelect = form.querySelector('select[name="POtype"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        // 선택된 옵션의 텍스트 업데이트
        document.getElementById('selectedColor').textContent = 
            colorSelect.options[colorSelect.selectedIndex].text;
        document.getElementById('selectedPaperType').textContent = 
            paperTypeSelect.options[paperTypeSelect.selectedIndex].text;
        document.getElementById('selectedPaperSize').textContent = 
            paperSizeSelect.options[paperSizeSelect.selectedIndex].text;
        document.getElementById('selectedSides').textContent = 
            sidesSelect.options[sidesSelect.selectedIndex].text;
        document.getElementById('selectedQuantity').textContent = 
            quantitySelect.options[quantitySelect.selectedIndex].text;
        document.getElementById('selectedDesign').textContent = 
            designSelect.options[designSelect.selectedIndex].text;
    }
    
    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        // AJAX로 실제 가격 계산
        const params = new URLSearchParams({
            MY_type: formData.get('MY_type'),
            PN_type: formData.get('PN_type'),
            MY_Fsd: formData.get('MY_Fsd'),
            MY_amount: formData.get('MY_amount'),
            ordertype: formData.get('ordertype'),
            POtype: formData.get('POtype')
        });
        
        fetch('calculate_price_ajax.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (data.success) {
                const priceData = data.data;
                
                // 선택한 옵션들 업데이트
                updateSelectedOptions(formData);
                
                // 가격 정보 표시
                document.getElementById('priceAmount').textContent = priceData.Order_Price + '원';
                document.getElementById('priceVat').textContent = Math.round(priceData.Total_PriceForm).toLocaleString() + '원';
                
                // 가격 섹션 표시
                document.getElementById('priceSection').style.display = 'block';
                
                // 부드럽게 스크롤
                document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
                
                // 전역 변수에 가격 정보 저장 (장바구니 추가용)
                window.currentPriceData = priceData;
                
            } else {
                alert(data.error.message);
                document.getElementById('priceSection').style.display = 'none';
            }
        })
        .catch(error => {
            button.innerHTML = originalText;
            button.disabled = false;
            console.error('가격 계산 오류:', error);
            alert('가격 계산 중 오류가 발생했습니다.');
        });
    }
    
    // 장바구니에 추가하는 함수
    function addToBasket() {
        // 가격 계산이 먼저 되었는지 확인
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        // 가격 정보 추가
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.Order_PriceForm));
        formData.set('vat_price', Math.round(window.currentPriceData.Total_PriceForm));
        formData.set('product_type', 'leaflet');
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 추가중...';
        button.disabled = true;
        
        // AJAX로 장바구니에 추가
        fetch('add_to_basket.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (data.success) {
                alert('장바구니에 추가되었습니다! 🛒');
                
                // 장바구니 확인 여부 묻기
                if (confirm('장바구니를 확인하시겠습니까?')) {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                } else {
                    // 폼 초기화하고 계속 쇼핑
                    document.getElementById('orderForm').reset();
                    document.getElementById('priceSection').style.display = 'none';
                    window.currentPriceData = null;
                }
            } else {
                alert('장바구니 추가 중 오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            button.innerHTML = originalText;
            button.disabled = false;
            console.error('Error:', error);
            alert('장바구니 추가 중 오류가 발생했습니다.');
        });
    }
    
    // 바로 주문하기 함수
    function directOrder() {
        // 가격 계산이 먼저 되었는지 확인
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        // 주문 정보를 URL 파라미터로 구성
        const params = new URLSearchParams();
        params.set('direct_order', '1');
        params.set('product_type', 'leaflet');
        params.set('MY_type', formData.get('MY_type'));
        params.set('MY_Fsd', formData.get('MY_Fsd'));
        params.set('PN_type', formData.get('PN_type'));
        params.set('POtype', formData.get('POtype'));
        params.set('MY_amount', formData.get('MY_amount'));
        params.set('ordertype', formData.get('ordertype'));
        params.set('price', Math.round(window.currentPriceData.Order_PriceForm));
        params.set('vat_price', Math.round(window.currentPriceData.Total_PriceForm));
        
        // 선택된 옵션 텍스트도 전달
        const colorSelect = document.querySelector('select[name="MY_type"]');
        const paperTypeSelect = document.querySelector('select[name="MY_Fsd"]');
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        const sidesSelect = document.querySelector('select[name="POtype"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        const designSelect = document.querySelector('select[name="ordertype"]');
        
        params.set('color_text', colorSelect.options[colorSelect.selectedIndex].text);
        params.set('paper_type_text', paperTypeSelect.options[paperTypeSelect.selectedIndex].text);
        params.set('paper_size_text', paperSizeSelect.options[paperSizeSelect.selectedIndex].text);
        params.set('sides_text', sidesSelect.options[sidesSelect.selectedIndex].text);
        params.set('quantity_text', quantitySelect.options[quantitySelect.selectedIndex].text);
        params.set('design_text', designSelect.options[designSelect.selectedIndex].text);
        
        // 주문 페이지로 이동
        window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 인쇄색상 변경 시 종이종류와 종이규격 동적 업데이트
    function changeColorType(colorNo) {
        console.log('인쇄색상 변경:', colorNo);
        
        // 종이종류 업데이트
        updatePaperTypes(colorNo);
        
        // 종이규격 업데이트
        updatePaperSizes(colorNo);
        
        // 수량 초기화
        clearQuantities();
    }
    
    function updatePaperTypes(colorNo) {
        const paperTypeSelect = document.querySelector('select[name="MY_Fsd"]');
        
        fetch(`get_paper_types.php?CV_no=${colorNo}&page=inserted`)
        .then(response => response.json())
        .then(data => {
            // 기존 옵션 제거
            paperTypeSelect.innerHTML = '';
            
            // 새 옵션 추가
            data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                paperTypeSelect.appendChild(optionElement);
            });
            
            console.log('종이종류 업데이트 완료:', data.length, '개');
        })
        .catch(error => {
            console.error('종이종류 업데이트 오류:', error);
        });
    }
    
    function updatePaperSizes(colorNo) {
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        
        fetch(`get_paper_sizes.php?CV_no=${colorNo}&page=inserted`)
        .then(response => response.json())
        .then(data => {
            // 기존 옵션 제거
            paperSizeSelect.innerHTML = '';
            
            // 새 옵션 추가
            data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                paperSizeSelect.appendChild(optionElement);
            });
            
            console.log('종이규격 업데이트 완료:', data.length, '개');
            
            // 종이규격이 변경되면 수량도 업데이트
            updateQuantities();
        })
        .catch(error => {
            console.error('종이규격 업데이트 오류:', error);
        });
    }
    
    // 수량 초기화
    function clearQuantities() {
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        quantitySelect.innerHTML = '<option value="">수량을 선택해주세요</option>';
    }
    
    // 수량 업데이트
    function updateQuantities() {
        const colorSelect = document.querySelector('select[name="MY_type"]');
        const paperTypeSelect = document.querySelector('select[name="MY_Fsd"]');
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        
        const MY_type = colorSelect.value;
        const MY_Fsd = paperTypeSelect.value;
        const PN_type = paperSizeSelect.value;
        
        if (!MY_type || !MY_Fsd || !PN_type) {
            clearQuantities();
            return;
        }
        
        fetch(`get_quantities.php?MY_type=${MY_type}&PN_type=${PN_type}&MY_Fsd=${MY_Fsd}`)
        .then(response => response.json())
        .then(data => {
            // 기존 옵션 제거
            quantitySelect.innerHTML = '';
            
            if (data.length === 0) {
                quantitySelect.innerHTML = '<option value="">수량 정보가 없습니다</option>';
                return;
            }
            
            // 새 옵션 추가
            data.forEach((option, index) => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                if (index === 0) optionElement.selected = true; // 첫 번째 옵션 선택
                quantitySelect.appendChild(optionElement);
            });
            
            console.log('수량 업데이트 완료:', data.length, '개');
        })
        .catch(error => {
            console.error('수량 업데이트 오류:', error);
            quantitySelect.innerHTML = '<option value="">수량 로드 오류</option>';
        });
    }
    
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
    
    // 페이지 로드 시 초기 수량 로드
    document.addEventListener('DOMContentLoaded', function() {
        // 약간의 지연 후 수량 업데이트 (다른 드롭다운이 로드된 후)
        setTimeout(function() {
            updateQuantities();
        }, 500);
    });
    </script>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>