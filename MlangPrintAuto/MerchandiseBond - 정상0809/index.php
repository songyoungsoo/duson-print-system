<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

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
$page_title = generate_page_title("상품권/쿠폰 자동견적");
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
        #selectedQuantity,
        #selectedSide,
        #selectedAfterProcess,
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
    
    <!-- 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    
    <!-- 업로드 컴포넌트 JavaScript 라이브러리 포함 -->
    <script src="../../includes/js/UniversalFileUpload.js"></script>
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
            <h1>🎫 상품권/쿠폰 자동견적</h1>
            <p>고품질 상품권과 쿠폰을 간편하게 주문하세요</p>
        </div>

        <form id="merchandisebondForm" method="post">
            <div class="form-container">
                <!-- 선택 옵션 패널 -->
                <div class="selection-panel">
                    <h3>📋 옵션 선택</h3>
                    
                    <div class="form-group">
                        <label for="MY_type">종류</label>
                        <select name="MY_type" id="MY_type" required>
                            <option value="">종류를 선택해주세요</option>
                            <?php
                            $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'MerchandiseBond');
                            foreach ($categories as $category) {
                                echo "<option value='" . safe_html($category['no']) . "'>" . safe_html($category['title']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="MY_amount">수량</label>
                        <select name="MY_amount" id="MY_amount" required>
                            <option value="">먼저 종류를 선택해주세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="POtype">인쇄면</label>
                        <select name="POtype" id="POtype" required>
                            <option value="">인쇄면을 선택해주세요</option>
                            <option value="1">단면</option>
                            <option value="2">양면</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="PN_type">후가공</label>
                        <select name="PN_type" id="PN_type" required>
                            <option value="">먼저 종류, 수량, 인쇄면을 선택해주세요</option>
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
                    <h3>ℹ️ 상품권/쿠폰 안내</h3>
                    <div class="info-text">
                        <p><strong>상품권 특징:</strong></p>
                        <ul>
                            <li>고급 용지 사용으로 품격있는 결과물</li>
                            <li>넘버링, 오시, 미싱 등 다양한 후가공</li>
                            <li>각종 이벤트, 할인권, 초대권으로 활용</li>
                        </ul>
                        
                        <p><strong>제작 기간:</strong></p>
                        <ul>
                            <li>일반 상품권: 2-3일</li>
                            <li>후가공 추가 시: 1-2일 추가</li>
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
                        <div class="option-label">수량</div>
                        <div class="option-value" id="selectedQuantity">-</div>
                    </div>
                    <div class="option-item">
                        <div class="option-label">인쇄면</div>
                        <div class="option-value" id="selectedSide">-</div>
                    </div>
                    <div class="option-item">
                        <div class="option-label">후가공</div>
                        <div class="option-value" id="selectedAfterProcess">-</div>
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
                
                <?php
                // 상품권용 업로드 컴포넌트 설정
                $uploadComponent = new FileUploadComponent([
                    'product_type' => 'merchandisebond',
                    'max_file_size' => 8 * 1024 * 1024, // 8MB
                    'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                    'multiple' => true,
                    'drag_drop' => true,
                    'show_progress' => true,
                    'auto_upload' => true,
                    'delete_enabled' => true,
                    'custom_messages' => [
                        'title' => '상품권/쿠폰 디자인 파일 업로드',
                        'drop_text' => '상품권/쿠폰 디자인 파일을 여기로 드래그하거나 클릭하여 선택하세요',
                        'format_text' => '지원 형식: JPG, PNG, PDF (최대 8MB)'
                    ]
                ]);
                
                // 컴포넌트 렌더링
                echo $uploadComponent->render();
                ?>
                
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
            <input type="hidden" name="page" value="MerchandiseBond">
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
        page: "MerchandiseBond"
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
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedSide').textContent = '-';
        document.getElementById('selectedAfterProcess').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.getElementById('merchandisebondForm');
        
        const categorySelect = form.querySelector('select[name="MY_type"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const sideSelect = form.querySelector('select[name="POtype"]');
        const afterProcessSelect = form.querySelector('select[name="PN_type"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        if (categorySelect.selectedIndex > 0) {
            document.getElementById('selectedCategory').textContent = categorySelect.options[categorySelect.selectedIndex].text;
        }
        if (quantitySelect.selectedIndex > 0) {
            document.getElementById('selectedQuantity').textContent = quantitySelect.options[quantitySelect.selectedIndex].text;
        }
        if (sideSelect.selectedIndex > 0) {
            document.getElementById('selectedSide').textContent = sideSelect.options[sideSelect.selectedIndex].text;
        }
        if (afterProcessSelect.selectedIndex > 0) {
            document.getElementById('selectedAfterProcess').textContent = afterProcessSelect.options[afterProcessSelect.selectedIndex].text;
        }
        if (designSelect.selectedIndex > 0) {
            document.getElementById('selectedDesign').textContent = designSelect.options[designSelect.selectedIndex].text;
        }
    }
    
    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('merchandisebondForm');
        const formData = new FormData(form);
        
        if (!formData.get('MY_type') || !formData.get('MY_amount') || !formData.get('POtype') || !formData.get('PN_type') || !formData.get('ordertype')) {
            alert('모든 옵션을 선택해주세요.');
            return;
        }
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        const params = new URLSearchParams(new FormData(form));
        
        fetch('price_cal_ajax.php?' + params.toString())
        .then(response => response.json())
        .then(response => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                const priceData = response.data;
                updateSelectedOptions(formData);
                
                document.getElementById('printPrice').textContent = format_number(priceData.base_price) + '원';
                document.getElementById('designPrice').textContent = format_number(priceData.design_price) + '원';
                document.getElementById('priceAmount').textContent = format_number(priceData.total_price) + '원';
                document.getElementById('priceVat').textContent = format_number(Math.round(priceData.total_with_vat)) + '원';
                
                document.getElementById('priceSection').style.display = 'block';
                document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
                
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
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('merchandisebondForm');
        const formData = new FormData(form);
        
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.total_price));
        formData.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        formData.set('product_type', 'merchandisebond');
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 추가중...';
        button.disabled = true;
        
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
                if (confirm('장바구니를 확인하시겠습니까?')) {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                } else {
                    form.reset();
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
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('merchandisebondForm');
        const params = new URLSearchParams(new FormData(form));
        params.set('direct_order', '1');
        params.set('product_type', 'merchandisebond');
        params.set('price', Math.round(window.currentPriceData.total_price));
        params.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        
        // 선택된 옵션 텍스트 전달
        params.set('category_text', form.querySelector('select[name="MY_type"]').options[form.querySelector('select[name="MY_type"]').selectedIndex].text);
        params.set('quantity_text', form.querySelector('select[name="MY_amount"]').options[form.querySelector('select[name="MY_amount"]').selectedIndex].text);
        params.set('side_text', form.querySelector('select[name="POtype"]').options[form.querySelector('select[name="POtype"]').selectedIndex].text);
        params.set('after_process_text', form.querySelector('select[name="PN_type"]').options[form.querySelector('select[name="PN_type"]').selectedIndex].text);
        params.set('design_text', form.querySelector('select[name="ordertype"]').options[form.querySelector('select[name="ordertype"]').selectedIndex].text);
        
        window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 옵션 업데이트 함수
    function updateOptions(selectElement, url, params, defaultOptionText) {
        selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
        
        fetch(`${url}?${new URLSearchParams(params).toString()}`)
        .then(response => response.json())
        .then(response => {
            if (response.success && response.data) {
                response.data.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value || option.no;
                    optionElement.textContent = option.text || option.title;
                    selectElement.appendChild(optionElement);
                });
            } else {
                console.error('옵션 로드 실패:', response.message);
            }
        })
        .catch(error => {
            console.error('옵션 업데이트 오류:', error);
            selectElement.innerHTML = '<option value="">로드 오류</option>';
        });
    }

    // 숫자 포맷팅 함수
    function format_number(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // 페이지 로드 시 초기화
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.querySelector('select[name="MY_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        const sideSelect = document.querySelector('select[name="POtype"]');
        const afterProcessSelect = document.querySelector('select[name="PN_type"]');

        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;

            if (selectedType) {
                updateOptions(quantitySelect, 'get_merchandisebond_quantities.php', { MY_type: selectedType }, '수량을 선택해주세요');
                updateOptions(afterProcessSelect, 'get_merchandisebond_after_process.php', { MY_type: selectedType }, '후가공을 선택해주세요');
            } else {
                // 종류가 선택되지 않으면 모든 하위 드롭다운 초기화
                quantitySelect.innerHTML = '<option value="">먼저 종류를 선택해주세요</option>';
                sideSelect.innerHTML = '<option value="">먼저 종류를 선택해주세요</option>';
                afterProcessSelect.innerHTML = '<option value="">먼저 종류를 선택해주세요</option>';
            }
        });
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
