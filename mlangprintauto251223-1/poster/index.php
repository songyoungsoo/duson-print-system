<?php 
// 공통 함수 포함
include "../../includes/functions.php";

// 세션 및 기본 설정
$session_id = check_session();

// 데이터베이스 연결
include "../../db.php";

// 통합 갤러리 시스템
if (file_exists('../../includes/gallery_helper.php')) {
    include_once '../../includes/gallery_helper.php';
}
if (function_exists("init_gallery_system")) {
    init_gallery_system("littleprint");
}
check_db_connection($db);
$connect = $db;

// UTF-8 설정
mysqli_set_charset($connect, "utf8");

// 페이지 설정
$page_title = generate_page_title('포스터');
$current_page = 'poster';
$log_info = generateLogInfo();

// 포스터 관련 설정
$page = "LittlePrint";
$TABLE = "mlangprintauto_transactioncate";

// 공통함수를 사용하여 초기 데이터 로드

$categoryOptions = getCategoryOptions($connect, $GGTABLE, $page);
$firstCategoryNo = !empty($categoryOptions) ? $categoryOptions[0]['no'] : '590';
$paperTypeOptions = getPaperTypes($connect, $GGTABLE, $firstCategoryNo);
$paperSizeOptions = getPaperSizes($connect, $GGTABLE, $firstCategoryNo);
$quantityOptions = getQuantityOptions($connect);

// 공통 인증 처리 포함
include "../../includes/auth.php";
// 공통 헤더 포함
include "../../includes/header.php";
?>

<?php
// 통합 갤러리 시스템 에셋 포함
if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {
    include_gallery_assets();
}
?>

<link rel="stylesheet" href="../../css/upload-modal-common.css">
<link rel="stylesheet" href="../../css/poster.css">

<?php include "../../includes/nav.php"; ?>

            <div class="container">
                <!-- 포스터 갤러리 섹션 -->
                <section class="poster-gallery product-gallery-unified" aria-label="포스터 샘플 갤러리">
                    <?php
                    // 통합 갤러리 시스템 사용
                    if (function_exists("include_product_gallery")) {
                        include_product_gallery('littleprint');
                    }
                    ?>
                </section>
                <!-- 주문 폼 -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📝 포스터 주문 옵션 선택</h2>
                        <p class="card-subtitle">아래 옵션들을 순서대로 선택하신 후 가격을 확인해보세요</p>
                    </div>
                    
                    <form id="littleprintForm" method="post">
                        <input type="hidden" name="action" value="calculate">
                        
                        <table class="order-form-table">
                            <tbody>
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">🏷️</span>
                                            <span>1. 구분</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_type" class="form-control-modern" onchange="resetSelectedOptions(); changeCategoryType(this.value)">
                                            <?php foreach ($categoryOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                                <?php echo htmlspecialchars($option['title']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="help-text">포스터 종류를 선택해주세요</small>
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
                                        <select name="TreeSelect" class="form-control-modern" onchange="resetSelectedOptions(); updateQuantities()">
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
                                            <?php foreach ($paperSizeOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
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
                        
                        <div style="text-align: center; margin: 1.5rem 0;">
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
                                <span class="option-label">🏷️ 구분:</span>
                                <span id="selectedCategory" class="option-value">-</span>
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
    // PHP 변수를 JavaScript로 전달 (공통함수 활용)
    var phpVars = {
        MultyUploadDir: "../../PHPClass/MultyUpload",
        log_url: "<?php echo safe_html($log_info['url']); ?>",
        log_y: "<?php echo safe_html($log_info['y']); ?>",
        log_md: "<?php echo safe_html($log_info['md']); ?>",
        log_ip: "<?php echo safe_html($log_info['ip']); ?>",
        log_time: "<?php echo safe_html($log_info['time']); ?>",
        page: "LittlePrint"
    };

    // 파일첨부 관련 함수들
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
        var parentList = document.littleprintForm.parentList;
        for (var i = 0; i < srcList.options.length; i++) {
            if (srcList.options[i] != null) {
                parentList.options[parentList.options.length] = new Option(srcList.options[i].text, srcList.options[i].value);
            }
        }
    }
    
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
        document.getElementById('selectedCategory').textContent = '-';
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
        const form = document.getElementById('littleprintForm');
        
        // 각 select 요소에서 선택된 옵션의 텍스트 가져오기
        const categorySelect = form.querySelector('select[name="MY_type"]');
        const paperTypeSelect = form.querySelector('select[name="TreeSelect"]');
        const paperSizeSelect = form.querySelector('select[name="PN_type"]');
        const sidesSelect = form.querySelector('select[name="POtype"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        // 선택된 옵션의 텍스트 업데이트
        document.getElementById('selectedCategory').textContent = 
            categorySelect.options[categorySelect.selectedIndex].text;
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
        const form = document.getElementById('littleprintForm');
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
            TreeSelect: formData.get('TreeSelect'),
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
        
        const form = document.getElementById('littleprintForm');
        const formData = new FormData(form);
        
        // 가격 정보 추가
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.Order_PriceForm));
        formData.set('vat_price', Math.round(window.currentPriceData.Total_PriceForm));
        formData.set('product_type', 'poster');
        
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
                    window.location.href = '/mlangprintauto/shop/cart.php';
                } else {
                    // 폼 초기화하고 계속 쇼핑
                    document.getElementById('littleprintForm').reset();
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
        
        const form = document.getElementById('littleprintForm');
        const formData = new FormData(form);
        
        // 주문 정보를 URL 파라미터로 구성
        const params = new URLSearchParams();
        params.set('direct_order', '1');
        params.set('product_type', 'poster');
        params.set('MY_type', formData.get('MY_type'));
        params.set('TreeSelect', formData.get('TreeSelect'));
        params.set('PN_type', formData.get('PN_type'));
        params.set('POtype', formData.get('POtype'));
        params.set('MY_amount', formData.get('MY_amount'));
        params.set('ordertype', formData.get('ordertype'));
        params.set('price', Math.round(window.currentPriceData.Order_PriceForm));
        params.set('vat_price', Math.round(window.currentPriceData.Total_PriceForm));
        
        // 선택된 옵션 텍스트도 전달
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const paperTypeSelect = document.querySelector('select[name="TreeSelect"]');
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        const sidesSelect = document.querySelector('select[name="POtype"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        const designSelect = document.querySelector('select[name="ordertype"]');
        
        params.set('category_text', categorySelect.options[categorySelect.selectedIndex].text);
        params.set('paper_type_text', paperTypeSelect.options[paperTypeSelect.selectedIndex].text);
        params.set('paper_size_text', paperSizeSelect.options[paperSizeSelect.selectedIndex].text);
        params.set('sides_text', sidesSelect.options[sidesSelect.selectedIndex].text);
        params.set('quantity_text', quantitySelect.options[quantitySelect.selectedIndex].text);
        params.set('design_text', designSelect.options[designSelect.selectedIndex].text);
        
        // 주문 페이지로 이동
        window.location.href = '/mlangorder_printauto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 구분 변경 시 종이종류와 종이규격 동적 업데이트
    function changeCategoryType(categoryNo) {
        console.log('구분 변경:', categoryNo);
        
        // 종이종류 업데이트
        updatePaperTypes(categoryNo);
        
        // 종이규격 업데이트
        updatePaperSizes(categoryNo);
        
        // 수량 초기화
        clearQuantities();
    }
    
    function updatePaperTypes(categoryNo) {
        const paperTypeSelect = document.querySelector('select[name="TreeSelect"]');
        
        fetch(`get_paper_types.php?CV_no=${categoryNo}&page=LittlePrint`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            paperTypeSelect.innerHTML = '';
            
            if (!response.success || !response.data) {
                console.error('종이종류 로드 실패:', response.message);
                return;
            }
            
            // 새 옵션 추가
            response.data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                paperTypeSelect.appendChild(optionElement);
            });
            
            console.log('종이종류 업데이트 완료:', response.data.length, '개');
        })
        .catch(error => {
            console.error('종이종류 업데이트 오류:', error);
        });
    }
    
    function updatePaperSizes(categoryNo) {
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        
        fetch(`get_paper_sizes.php?CV_no=${categoryNo}&page=LittlePrint`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            paperSizeSelect.innerHTML = '';
            
            if (!response.success || !response.data) {
                console.error('종이규격 로드 실패:', response.message);
                return;
            }
            
            // 새 옵션 추가
            response.data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                paperSizeSelect.appendChild(optionElement);
            });
            
            console.log('종이규격 업데이트 완료:', response.data.length, '개');
            
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
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const paperTypeSelect = document.querySelector('select[name="TreeSelect"]');
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        
        const MY_type = categorySelect.value;
        const TreeSelect = paperTypeSelect.value;
        const PN_type = paperSizeSelect.value;
        
        if (!MY_type || !TreeSelect || !PN_type) {
            clearQuantities();
            return;
        }
        
        fetch(`get_quantities.php?style=${MY_type}&Section=${PN_type}&TreeSelect=${TreeSelect}`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            quantitySelect.innerHTML = '';
            
            if (!response.success || !response.data || response.data.length === 0) {
                quantitySelect.innerHTML = '<option value="">수량 정보가 없습니다</option>';
                console.log('수량 정보 없음:', response.message || '데이터 없음');
                return;
            }
            
            // 새 옵션 추가
            response.data.forEach((option, index) => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                if (index === 0) optionElement.selected = true; // 첫 번째 옵션 선택
                quantitySelect.appendChild(optionElement);
            });
            
            console.log('수량 업데이트 완료:', response.data.length, '개');
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
        // 드롭다운 변경 이벤트 리스너 추가
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const paperTypeSelect = document.querySelector('select[name="TreeSelect"]');
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                changeCategoryType(this.value);
            });
        }
        
        if (paperTypeSelect) {
            paperTypeSelect.addEventListener('change', function() {
                updateQuantities();
            });
        }
        
        if (paperSizeSelect) {
            paperSizeSelect.addEventListener('change', function() {
                updateQuantities();
            });
        }
        
        // 약간의 지연 후 초기 수량 업데이트 (다른 드롭다운이 로드된 후)
        setTimeout(function() {
            updateQuantities();
        }, 1000);
    });
    </script>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>