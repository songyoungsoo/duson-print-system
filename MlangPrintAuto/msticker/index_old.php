<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 세션 및 기본 설정
check_session();
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 생성
$log_info = generateLogInfo();

// 로그인 처리
$login_message = '';
if ($_POST['login_action'] ?? '' === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $login_message = '로그인 성공! 환영합니다.';
    } else {
        $login_message = '아이디와 비밀번호를 입력해주세요.';
    }
}

// 페이지 제목 설정
$page_title = generate_page_title("자석스티커 견적안내");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 공통 CSS -->
    <link rel="stylesheet" href="../../css/common_style.css">
    
    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .main-content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .page-header p {
            margin: 10px 0 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .selection-panel {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        
        .selection-panel h3 {
            margin: 0 0 25px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 1rem;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .info-panel {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        
        .info-panel h3 {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .info-text {
            line-height: 1.6;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .calculate-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn-calculate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-calculate:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .price-section {
            display: none;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        .price-section h3 {
            margin: 0 0 25px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
            text-align: center;
        }
        
        .selected-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .option-item {
            text-align: center;
        }
        
        .option-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .option-value {
            font-weight: 600;
            color: #495057 !important;
            font-size: 1rem;
        }
        
        .selected-options .option-value {
            color: #495057 !important;
            background-color: transparent !important;
        }
        
        #selectedCategory,
        #selectedSize,
        #selectedQuantity,
        #selectedDesign {
            color: #495057 !important;
            font-weight: 600;
        }
        
        .price-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .price-label {
            font-weight: 500;
            color: #495057;
        }
        
        .price-amount {
            font-weight: 700;
            font-size: 1.2rem;
            color: #667eea;
        }
        
        .total-price {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .total-price .price-amount {
            font-size: 2rem;
            color: white !important;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-action {
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #17a2b8;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .file-upload-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        .file-upload-section h4 {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
        }
        
        .file-list {
            min-height: 80px;
            background: white;
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .file-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .btn-file {
            padding: 10px 20px;
            font-size: 0.9rem;
            border: 1px solid #6c757d;
            background: white;
            color: #6c757d;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-file:hover {
            background: #6c757d;
            color: white;
        }
        
        .comment-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        .comment-section h4 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
        }
        
        .comment-section textarea {
            width: 100%;
            min-height: 100px;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .comment-section textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .price-details {
                grid-template-columns: 1fr;
            }
            
            .selected-options {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php
    // 공통 헤더 포함
    include "../../includes/header.php";
    ?>

    <?php
    // 공통 네비게이션 포함
    include "../../includes/nav.php";
    ?>

    <div class="main-content-wrapper">
        <div class="page-header">
            <h1>🧲 자석스티커 견적안내</h1>
            <p>종이자석과 전체자석 스티커의 정확한 견적을 확인하세요</p>
        </div>

        <form id="mstickerForm" method="post">
            <div class="form-container">
                <!-- 선택 옵션 패널 -->
                <div class="selection-panel">
                    <h3>📋 옵션 선택</h3>
                    
                    <div class="form-group">
                        <label for="MY_type">종류</label>
                        <select name="MY_type" id="MY_type" required>
                            <option value="">종류를 선택해주세요</option>
                            <?php
                            $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'msticker');
                            foreach ($categories as $category) {
                                echo "<option value='" . safe_html($category['no']) . "'>" . safe_html($category['title']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="PN_type">규격</label>
                        <select name="PN_type" id="PN_type" required>
                            <option value="">먼저 종류를 선택해주세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="MY_amount">수량</label>
                        <select name="MY_amount" id="MY_amount" required>
                            <option value="">수량을 선택해주세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ordertype">편집디자인</label>
                        <select name="ordertype" id="ordertype" required>
                            <option value="">편집 방식을 선택해주세요</option>
                            <option value="total">디자인+인쇄</option>
                            <option value="print">인쇄만 의뢰</option>
                        </select>
                    </div>
                </div>

                <!-- 정보 패널 -->
                <div class="info-panel">
                    <h3>ℹ️ 자석스티커 안내</h3>
                    <div class="info-text">
                        <p><strong>자석스티커 특징:</strong></p>
                        <ul>
                            <li>종이자석: 후면에 작은 자석 부착</li>
                            <li>전체자석: 전면이 모두 자석 재질</li>
                            <li>다양한 규격 제작 가능</li>
                            <li>냉장고, 철제 표면에 부착 가능</li>
                        </ul>
                        
                        <p><strong>제작 기간:</strong></p>
                        <ul>
                            <li>일반 자석스티커: 2-3일</li>
                            <li>특수 규격: 3-4일</li>
                            <li>대량 주문: 별도 문의</li>
                        </ul>
                        
                        <p><strong>고객센터:</strong> 02-2632-1830</p>
                    </div>
                </div>
            </div>

            <div class="calculate-section">
                <button type="button" onclick="calculatePrice()" class="btn-calculate">
                    💰 견적 계산하기
                </button>
            </div>

            <!-- 가격 표시 섹션 -->
            <div id="priceSection" class="price-section">
                <h3>💰 견적 결과</h3>
                
                <!-- 선택된 옵션 요약 -->
                <div class="selected-options">
                    <div class="option-item">
                        <div class="option-label">종류</div>
                        <div class="option-value" id="selectedCategory">-</div>
                    </div>
                    <div class="option-item">
                        <div class="option-label">규격</div>
                        <div class="option-value" id="selectedSize">-</div>
                    </div>
                    <div class="option-item">
                        <div class="option-label">수량</div>
                        <div class="option-value" id="selectedQuantity">-</div>
                    </div>
                    <div class="option-item">
                        <div class="option-label">편집디자인</div>
                        <div class="option-value" id="selectedDesign">-</div>
                    </div>
                </div>

                <!-- 가격 상세 -->
                <div class="price-details">
                    <div class="price-item">
                        <span class="price-label">인쇄비</span>
                        <span class="price-amount" id="printPrice">0원</span>
                    </div>
                    <div class="price-item">
                        <span class="price-label">디자인비</span>
                        <span class="price-amount" id="designPrice">0원</span>
                    </div>
                </div>

                <div class="total-price">
                    <div>총 견적 금액</div>
                    <div class="price-amount" id="priceAmount">0원</div>
                    <div>부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700; color: white;">0원</span></div>
                </div>
                
                <div class="action-buttons">
                    <button type="button" onclick="addToBasket()" class="btn-action btn-primary">
                        🛒 장바구니에 담기
                    </button>
                    <button type="button" onclick="directOrder()" class="btn-action btn-secondary">
                        📋 바로 주문하기
                    </button>
                </div>
            </div>

            <!-- 파일 업로드 섹션 -->
            <div class="file-upload-section">
                <h4>📎 파일 첨부</h4>
                <div class="file-list" id="fileList">
                    <p style="color: #6c757d; text-align: center; margin: 0;">
                        첨부된 파일이 없습니다.
                    </p>
                </div>
                <div class="file-buttons">
                    <button type="button" onclick="uploadFile()" class="btn-file">파일 업로드</button>
                    <button type="button" onclick="deleteSelectedFiles()" class="btn-file">선택 삭제</button>
                </div>
            </div>

            <!-- 기타사항 섹션 -->
            <div class="comment-section">
                <h4>📝 기타사항</h4>
                <textarea name="comment" placeholder="추가 요청사항이나 문의사항을 입력해주세요..."></textarea>
            </div>

            <!-- 숨겨진 필드들 -->
            <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
            <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
            <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
            <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
            <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
            <input type="hidden" name="page" value="msticker">
        </form>
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
        page: "msticker"
    };

    // 파일첨부 관련 함수들
    function uploadFile() {
        const url = `../../PHPClass/MultyUpload/FileUp.php?Turi=${phpVars.log_url}&Ty=${phpVars.log_y}&Tmd=${phpVars.log_md}&Tip=${phpVars.log_ip}&Ttime=${phpVars.log_time}&Mode=tt`;
        window.open(url, 'FileUpload', 'width=500,height=400,scrollbars=yes,resizable=yes');
    }

    function deleteSelectedFiles() {
        // 파일 삭제 로직 (기존 코드 참조)
        console.log('파일 삭제 기능');
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
        document.getElementById('selectedSize').textContent = '-';
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.getElementById('mstickerForm');
        
        // 각 select 요소에서 선택된 옵션의 텍스트 가져오기
        const categorySelect = form.querySelector('select[name="MY_type"]');
        const sizeSelect = form.querySelector('select[name="PN_type"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        // 선택된 옵션의 텍스트 업데이트
        if (categorySelect.selectedIndex > 0) {
            document.getElementById('selectedCategory').textContent = 
                categorySelect.options[categorySelect.selectedIndex].text;
        }
        if (sizeSelect.selectedIndex > 0) {
            document.getElementById('selectedSize').textContent = 
                sizeSelect.options[sizeSelect.selectedIndex].text;
        }
        if (quantitySelect.selectedIndex > 0) {
            document.getElementById('selectedQuantity').textContent = 
                quantitySelect.options[quantitySelect.selectedIndex].text;
        }
        if (designSelect.selectedIndex > 0) {
            document.getElementById('selectedDesign').textContent = 
                designSelect.options[designSelect.selectedIndex].text;
        }
    }    

    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('mstickerForm');
        const formData = new FormData(form);
        
        // 필수 필드 검증
        if (!formData.get('MY_type') || !formData.get('PN_type') || 
            !formData.get('MY_amount') || !formData.get('ordertype')) {
            alert('모든 옵션을 선택해주세요.');
            return;
        }
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        // AJAX로 실제 가격 계산
        const params = new URLSearchParams({
            MY_type: formData.get('MY_type'),
            PN_type: formData.get('PN_type'),
            MY_amount: formData.get('MY_amount'),
            ordertype: formData.get('ordertype')
        });
        
        fetch('calculate_price_ajax.php?' + params.toString())
        .then(response => response.json())
        .then(response => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                const priceData = response.data;
                
                // 선택한 옵션들 업데이트
                updateSelectedOptions(formData);
                
                // 가격 정보 표시
                document.getElementById('printPrice').textContent = format_number(priceData.base_price) + '원';
                document.getElementById('designPrice').textContent = format_number(priceData.design_price) + '원';
                document.getElementById('priceAmount').textContent = format_number(priceData.total_price) + '원';
                document.getElementById('priceVat').textContent = format_number(Math.round(priceData.total_with_vat)) + '원';
                
                // 가격 섹션 표시
                document.getElementById('priceSection').style.display = 'block';
                
                // 부드럽게 스크롤
                document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
                
                // 전역 변수에 가격 정보 저장 (장바구니 추가용)
                window.currentPriceData = priceData;
                
            } else {
                alert(response.message || '가격 계산 중 오류가 발생했습니다.');
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
        
        const form = document.getElementById('mstickerForm');
        const formData = new FormData(form);
        
        // 가격 정보 추가
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.total_price));
        formData.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        formData.set('product_type', 'msticker');
        
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
        .then(response => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                alert('장바구니에 추가되었습니다! 🛒');
                
                // 장바구니 확인 여부 묻기
                if (confirm('장바구니를 확인하시겠습니까?')) {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                } else {
                    // 폼 초기화하고 계속 쇼핑
                    document.getElementById('mstickerForm').reset();
                    document.getElementById('priceSection').style.display = 'none';
                    window.currentPriceData = null;
                }
            } else {
                alert('장바구니 추가 중 오류가 발생했습니다: ' + response.message);
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
        
        const form = document.getElementById('mstickerForm');
        const formData = new FormData(form);
        
        // 주문 정보를 URL 파라미터로 구성
        const params = new URLSearchParams();
        params.set('direct_order', '1');
        params.set('product_type', 'msticker');
        params.set('MY_type', formData.get('MY_type'));
        params.set('PN_type', formData.get('PN_type'));
        params.set('MY_amount', formData.get('MY_amount'));
        params.set('ordertype', formData.get('ordertype'));
        params.set('price', Math.round(window.currentPriceData.total_price));
        params.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        
        // 선택된 옵션 텍스트도 전달
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        const designSelect = document.querySelector('select[name="ordertype"]');
        
        params.set('category_text', categorySelect.options[categorySelect.selectedIndex].text);
        params.set('size_text', sizeSelect.options[sizeSelect.selectedIndex].text);
        params.set('quantity_text', quantitySelect.options[quantitySelect.selectedIndex].text);
        params.set('design_text', designSelect.options[designSelect.selectedIndex].text);
        
        // 주문 페이지로 이동
        window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 종류 변경 시 규격 동적 업데이트
    function changeCategoryType(categoryNo) {
        console.log('종류 변경:', categoryNo);
        
        // 규격 업데이트
        updateSizes(categoryNo);
        
        // 수량 초기화
        clearQuantities();
    }
    
    function updateSizes(categoryNo) {
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        
        fetch(`get_sizes.php?CV_no=${categoryNo}&page=msticker`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            sizeSelect.innerHTML = '';
            
            if (!response.success || !response.data) {
                sizeSelect.innerHTML = '<option value="">규격 정보가 없습니다</option>';
                console.error('규격 로드 실패:', response.message);
                return;
            }
            
            // 기본 옵션 추가
            sizeSelect.innerHTML = '<option value="">규격을 선택해주세요</option>';
            
            // 새 옵션 추가
            response.data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                sizeSelect.appendChild(optionElement);
            });
            
            console.log('규격 업데이트 완료:', response.data.length, '개');
        })
        .catch(error => {
            console.error('규격 업데이트 오류:', error);
            sizeSelect.innerHTML = '<option value="">규격 로드 오류</option>';
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
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        
        const MY_type = categorySelect.value;
        const PN_type = sizeSelect.value;
        
        if (!MY_type || !PN_type) {
            clearQuantities();
            return;
        }
        
        fetch(`get_quantities.php?style=${MY_type}&Section=${PN_type}`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            quantitySelect.innerHTML = '';
            
            if (!response.success || !response.data || response.data.length === 0) {
                quantitySelect.innerHTML = '<option value="">수량 정보가 없습니다</option>';
                console.log('수량 정보 없음:', response.message || '데이터 없음');
                return;
            }
            
            // 기본 옵션 추가
            quantitySelect.innerHTML = '<option value="">수량을 선택해주세요</option>';
            
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
    
    // 숫자 포맷팅 함수
    function format_number(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
    
    // 페이지 로드 시 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // 드롭다운 변경 이벤트 리스너 추가
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                changeCategoryType(this.value);
            });
        }
        
        if (sizeSelect) {
            sizeSelect.addEventListener('change', function() {
                updateQuantities();
            });
        }
        
        // 초기 옵션 선택 시 규격 업데이트
        setTimeout(function() {
            if (categorySelect.value) {
                changeCategoryType(categorySelect.value);
            }
        }, 500);
    });
    </script>

<?php
// 데이터베이스 연결 종료
if ($db) {
    mysqli_close($db);
}
?>
</body>
</html>