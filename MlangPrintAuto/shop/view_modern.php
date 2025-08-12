<?php 
/**
 * 스티커 주문 페이지 (공통 인클루드 사용 버전)
 * 경로: MlangPrintAuto/shop/view_modern_new.php
 */

session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 공통 인증 처리
include "../../includes/auth.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 페이지 설정
$page_title = '🏷️ 두손기획인쇄 - 프리미엄 스티커 주문';
$current_page = 'sticker';

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 공통 헤더 포함
include "../../includes/header.php";

// 네비게이션 포함
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';

// 견적 표 CSS 추가
echo '<link rel="stylesheet" href="../../includes/css/quote-table.css">';
?>

<div class="container">
    <!-- 주문 폼 -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📝 스티커 주문 옵션 선택</h2>
            <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
        </div>
        
        <form id="orderForm" method="post">
            <input type="hidden" name="no" value="<?php echo htmlspecialchars($no ?? '', ENT_QUOTES, 'UTF-8')?>">
            <input type="hidden" name="action" value="calculate">
            
            <table class="order-form-table">
                <tbody>
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📄</span>
                                <span>1. 재질 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="jong" class="form-control-modern">
                                <option value="jil 아트유광">✨ 아트지유광 (90g)</option>
                                <option value="jil 아트무광코팅">🌟 아트지무광코팅 (90g)</option>
                                <option value="jil 아트비코팅">💫 아트지비코팅 (90g)</option>
                                <option value="cka 초강접아트유광">⚡ 초강접아트유광 (90g)</option>
                                <option value="cka 초강접아트비코팅">⚡ 초강접아트비코팅 (90g)</option>
                                <option value="jsp 유포지">📄 유포지 (80g)</option>
                                <option value="jsp 투명스티커">🔍 투명스티커</option>
                                <option value="jsp 홀로그램">🌈 홀로그램</option>
                                <option value="jsp 크라프트">🌿 크라프트지</option>
                            </select>
                            <small class="help-text">재질에 따라 스티커의 느낌과 내구성이 달라집니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📏</span>
                                <span>2. 크기 설정</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <div class="size-inputs" style="display: flex; align-items: center; gap: 1rem;">
                                <div class="size-input-inline">
                                    <label class="size-label" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">가로 (mm):</label>
                                    <input type="number" name="garo" class="form-control-inline" placeholder="예: 100" min="10" max="1000" required 
                                           style="width: 120px; padding: 12px; font-size: 1.1rem; border: 2px solid #ddd; border-radius: 8px; text-align: center; font-weight: 600;">
                                </div>
                                <span class="size-multiply" style="font-size: 1.5rem; font-weight: bold; color: #666; margin: 0 0.5rem;">×</span>
                                <div class="size-input-inline">
                                    <label class="size-label" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">세로 (mm):</label>
                                    <input type="number" name="sero" class="form-control-inline" placeholder="예: 100" min="10" max="1000" required 
                                           style="width: 120px; padding: 12px; font-size: 1.1rem; border: 2px solid #ddd; border-radius: 8px; text-align: center; font-weight: 600;">
                                </div>
                            </div>
                            <small class="help-text">최소 10mm, 최대 1000mm까지 제작 가능합니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📦</span>
                                <span>3. 수량 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="mesu" class="form-control-modern">
                                <option value="500">500매</option>
                                <option value="1000" selected>1,000매 (추천)</option>
                                <option value="2000">2,000매</option>
                                <option value="3000">3,000매</option>
                                <option value="5000">5,000매</option>
                                <option value="10000">10,000매</option>
                                <option value="20000">20,000매</option>
                                <option value="30000">30,000매 (대량할인)</option>
                            </select>
                            <small class="help-text">수량이 많을수록 단가가 저렴해집니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">✏️</span>
                                <span>4. 편집비</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="uhyung" class="form-control-modern">
                                <option value="0">인쇄만 (파일 준비완료)</option>
                                <option value="10000">기본 편집 (+10,000원)</option>
                                <option value="30000">고급 편집 (+30,000원)</option>
                            </select>
                            <small class="help-text">디자인 파일이 없으시면 편집 서비스를 이용해주세요</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">🔲</span>
                                <span>5. 모양 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="domusong" class="form-control-modern">
                                <option value="00000 사각">⬜ 사각형 (기본)</option>
                                <option value="00001 원형">⭕ 원형</option>
                                <option value="00002 타원">🥚 타원형</option>
                                <option value="00003 별모양">⭐ 별모양</option>
                                <option value="00004 하트">❤️ 하트</option>
                                <option value="00005 다각형">🔷 다각형</option>
                            </select>
                            <small class="help-text">모양에 따라 추가 작업비가 발생할 수 있습니다</small>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div style="text-align: center; margin: 3rem 0;">
                <button type="button" onclick="calculatePrice()" class="btn-calculate">
                    💰 실시간 가격 계산하기
                </button>
            </div>
        </form>
    </div>
    
    <!-- 가격 계산 결과 -->
    <div id="priceSection" class="price-result" style="display: none;">
        <h3 style="margin-bottom: 1rem; font-size: 1.3rem;">💎 견적 결과</h3>
        
        <!-- 견적 결과 표 -->
        <table class="quote-table">
            <thead>
                <tr>
                    <th>항목</th>
                    <th>내용</th>
                    <th>금액</th>
                </tr>
            </thead>
            <tbody>
                <!-- 옵션 정보 행들 -->
                <tr>
                    <td>재질</td>
                    <td id="selectedMaterial">-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>크기</td>
                    <td id="selectedSize">-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>수량</td>
                    <td id="selectedQuantity">-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>편집비</td>
                    <td id="selectedEdit">-</td>
                    <td id="editPrice">0원</td>
                </tr>
                <tr>
                    <td>모양</td>
                    <td id="selectedShape">-</td>
                    <td>-</td>
                </tr>
                
                <!-- 가격 정보 행들 -->
                <tr class="price-row">
                    <td>인쇄비</td>
                    <td>-</td>
                    <td id="printPrice">0원</td>
                </tr>
                
                <!-- 합계 행들 -->
                <tr class="total-row">
                    <td><strong>합계 (부가세 별도)</strong></td>
                    <td>-</td>
                    <td><strong id="priceAmount">0원</strong></td>
                </tr>
                <tr class="vat-row">
                    <td><strong>총 금액 (부가세 포함)</strong></td>
                    <td>-</td>
                    <td><strong id="priceVat">0원</strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- 가격 계산 후 다음 단계 안내 -->
        <div style="margin: 1rem 0; padding: 1rem; background: #e8f5e8; border: 1px solid #28a745; border-radius: 8px;">
            <h4 style="color: #155724; margin-bottom: 0.5rem; font-size: 1rem; line-height: 1.3;">
                📋 다음 단계: 디자인 파일 업로드 & 주문하기
            </h4>
            
            <?php
            // 스티커용 업로드 컴포넌트 설정 (높이 줄임)
            $uploadComponent = new FileUploadComponent([
                'product_type' => 'sticker',
                'max_file_size' => 10 * 1024 * 1024, // 10MB
                'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                'multiple' => true,
                'drag_drop' => true,
                'show_progress' => true,
                'auto_upload' => true,
                'delete_enabled' => true,
                'compact_mode' => true, // 높이 줄이기
                'custom_messages' => [
                    'title' => '📎 디자인 파일 업로드',
                    'drop_text' => '파일을 드래그하거나 클릭하여 선택하세요',
                    'format_text' => 'JPG, PNG, PDF (최대 10MB)'
                ]
            ]);
            
            // 컴포넌트 렌더링
            echo $uploadComponent->render();
            ?>
            
            <div class="price-action-buttons" style="margin-top: 1.5rem; text-align: center; padding: 1rem 0;">
                <button onclick="addToBasket()" class="btn btn-success" style="
                    display: inline-block;
                    padding: 12px 24px;
                    margin-right: 1rem;
                    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
                ">
                    🛒 장바구니에 담기
                </button>
                <button onclick="directOrder()" class="btn btn-primary" style="
                    display: inline-block;
                    padding: 12px 24px;
                    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
                ">
                    📋 바로 주문하기
                </button>
            </div>
            
            <div style="margin-top: 0.5rem; text-align: center; color: #6c757d; font-size: 0.8rem; line-height: 1.2;">
                💡 팁: 디자인 파일 없이도 주문 가능합니다!
            </div>
        </div>
    </div>
    
    <!-- 업로드 컴포넌트 컴팩트 스타일 -->
    <style>
    /* 가격 섹션 전체 최적화 */
    #priceSection {
        margin-top: 1rem !important;
    }
    
    #priceSection .quote-table {
        font-size: 0.9rem !important;
    }
    
    #priceSection .quote-table th,
    #priceSection .quote-table td {
        padding: 0.5rem !important;
        line-height: 1.2 !important;
    }
    
    /* 다음 단계 섹션 컴팩트화 */
    #priceSection > div {
        margin: 1rem 0 !important;
        padding: 1rem !important;
    }
    
    /* 업로드 컴포넌트 최소화 */
    #priceSection .file-upload-component .upload-section {
        padding: 0.5rem !important;
        margin: 0.3rem 0 !important;
        background: #f8f9fa !important;
    }
    
    #priceSection .file-upload-component .upload-area {
        min-height: 45px !important;
        max-height: 45px !important;
        padding: 0.3rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    #priceSection .file-upload-component h4 {
        margin-bottom: 0.2rem !important;
        font-size: 0.85rem !important;
        color: #495057 !important;
    }
    
    #priceSection .file-upload-component .upload-text {
        font-size: 0.75rem !important;
        margin: 0 !important;
        line-height: 1.1 !important;
    }
    
    #priceSection .file-upload-component .format-info {
        font-size: 0.65rem !important;
        margin-top: 0.2rem !important;
        color: #6c757d !important;
    }
    
    /* 버튼 호버 효과 */
    #priceSection .price-action-buttons button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
    }
    
    /* 다음 단계 섹션 강조 */
    #priceSection .price-action-buttons {
        border-top: 1px solid #28a745;
        margin-top: 0.8rem !important;
        padding: 0.8rem 0 !important;
        min-height: 60px !important;
    }
    
    #priceSection .price-action-buttons button {
        padding: 10px 20px !important;
        font-size: 0.95rem !important;
        margin-right: 0.8rem !important;
    }
    
    /* 크기 입력 필드 스타일 개선 */
    input[name="garo"], input[name="sero"] {
        transition: all 0.3s ease !important;
    }
    
    input[name="garo"]:hover, input[name="sero"]:hover {
        border-color: #007bff !important;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2) !important;
    }
    
    input[name="garo"]:focus, input[name="sero"]:focus {
        border-color: #007bff !important;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25) !important;
        outline: none !important;
    }
    
    /* 모바일 반응형 */
    @media (max-width: 768px) {
        #priceSection .price-action-buttons button {
            display: block !important;
            width: 100% !important;
            margin: 0.5rem 0 !important;
            padding: 15px 20px !important;
        }
        
        #priceSection .file-upload-component .upload-area {
            min-height: 50px !important;
            max-height: 50px !important;
        }
        
        /* 모바일에서 크기 입력 필드 */
        .size-inputs {
            flex-direction: column !important;
            gap: 1rem !important;
            text-align: center !important;
        }
        
        input[name="garo"], input[name="sero"] {
            width: 150px !important;
            padding: 15px !important;
            font-size: 1.2rem !important;
        }
    }
    </style>

</div>

<script>
// 선택된 옵션들을 업데이트하는 함수
function updateSelectedOptions() {
    const form = document.getElementById('orderForm');
    
    // 재질
    const materialSelect = form.querySelector('select[name="jong"]');
    if (materialSelect.selectedIndex >= 0) {
        document.getElementById('selectedMaterial').textContent = 
            materialSelect.options[materialSelect.selectedIndex].text;
    }
    
    // 크기
    const garo = form.querySelector('input[name="garo"]').value;
    const sero = form.querySelector('input[name="sero"]').value;
    if (garo && sero) {
        document.getElementById('selectedSize').textContent = `${garo}mm × ${sero}mm`;
    }
    
    // 수량
    const quantitySelect = form.querySelector('select[name="mesu"]');
    if (quantitySelect.selectedIndex >= 0) {
        document.getElementById('selectedQuantity').textContent = 
            quantitySelect.options[quantitySelect.selectedIndex].text;
    }
    
    // 편집비
    const editSelect = form.querySelector('select[name="uhyung"]');
    if (editSelect.selectedIndex >= 0) {
        const editText = editSelect.options[editSelect.selectedIndex].text;
        document.getElementById('selectedEdit').textContent = editText;
        
        // 편집비 금액 표시
        const editValue = editSelect.value;
        if (editValue > 0) {
            document.getElementById('editPrice').textContent = 
                new Intl.NumberFormat('ko-KR').format(editValue) + '원';
        } else {
            document.getElementById('editPrice').textContent = '0원';
        }
    }
    
    // 모양
    const shapeSelect = form.querySelector('select[name="domusong"]');
    if (shapeSelect.selectedIndex >= 0) {
        document.getElementById('selectedShape').textContent = 
            shapeSelect.options[shapeSelect.selectedIndex].text;
    }
}

// 가격 계산 함수
function calculatePrice() {
    const formData = new FormData(document.getElementById('orderForm'));
    const calculateBtn = document.querySelector('.btn-calculate');
    
    // 필수 입력값 검증
    const garo = formData.get('garo');
    const sero = formData.get('sero');
    const mesu = formData.get('mesu');
    
    if (!garo || !sero) {
        alert('가로와 세로 크기를 입력해주세요.');
        return;
    }
    
    if (!mesu) {
        alert('수량을 선택해주세요.');
        return;
    }
    
    // 버튼 상태 변경 (계산 중)
    const originalText = calculateBtn.textContent;
    calculateBtn.textContent = '💰 가격 계산 중...';
    calculateBtn.disabled = true;
    
    // 선택된 옵션들 업데이트
    updateSelectedOptions();
    
    fetch('calculate_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 인쇄비 계산 (총액에서 편집비 제외)
            const totalPrice = parseInt(data.price.replace(/,/g, ''));
            const editPrice = parseInt(document.getElementById('editPrice').textContent.replace(/[^0-9]/g, '')) || 0;
            const printPrice = totalPrice - editPrice;
            
            document.getElementById('printPrice').textContent = 
                new Intl.NumberFormat('ko-KR').format(printPrice) + '원';
            document.getElementById('priceAmount').textContent = data.price + '원';
            document.getElementById('priceVat').textContent = data.price_vat + '원';
            
            // 가격 섹션 표시
            document.getElementById('priceSection').style.display = 'block';
            
            // 가격 섹션으로 스크롤
            document.getElementById('priceSection').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
            
        } else {
            alert('가격 계산 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('가격 계산 중 오류가 발생했습니다.');
    })
    .finally(() => {
        // 버튼 상태 복원
        calculateBtn.textContent = originalText;
        calculateBtn.disabled = false;
    });
}

// 장바구니 추가 함수
function addToBasket() {
    // 먼저 가격 계산을 수행
    const formData = new FormData(document.getElementById('orderForm'));
    
    // 필수 입력값 검증
    const jong = formData.get('jong');
    const garo = formData.get('garo');
    const sero = formData.get('sero');
    const mesu = formData.get('mesu');
    
    if (!jong || !garo || !sero || !mesu) {
        alert('모든 필수 옵션을 입력해주세요.');
        return;
    }
    
    // 선택된 옵션들 업데이트
    updateSelectedOptions();
    
    // 가격 계산 먼저 수행
    formData.append('action', 'calculate');
    
    fetch('calculate_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(priceData => {
        if (priceData.success) {
            // 가격 정보 표시
            const totalPrice = parseInt(priceData.price.replace(/,/g, ''));
            const editPrice = parseInt(document.getElementById('editPrice').textContent.replace(/[^0-9]/g, '')) || 0;
            const printPrice = totalPrice - editPrice;
            
            document.getElementById('printPrice').textContent = 
                new Intl.NumberFormat('ko-KR').format(printPrice) + '원';
            document.getElementById('priceAmount').textContent = priceData.price + '원';
            document.getElementById('priceVat').textContent = priceData.price_vat + '원';
            document.getElementById('priceSection').style.display = 'block';
            
            // 가격 계산 성공 시 장바구니에 추가
            const basketFormData = new FormData(document.getElementById('orderForm'));
            basketFormData.append('product_type', 'sticker');
            basketFormData.append('action', 'add_to_basket');
            basketFormData.append('st_price', priceData.price.replace(/,/g, ''));
            basketFormData.append('st_price_vat', priceData.price_vat.replace(/,/g, ''));
            
            return fetch('add_to_basket.php', {
                method: 'POST',
                body: basketFormData
            });
        } else {
            throw new Error('가격 계산 실패: ' + priceData.message);
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('장바구니에 추가되었습니다! 🛒');
            if (confirm('장바구니를 확인하시겠습니까?')) {
                window.location.href = 'cart.php';
            }
        } else {
            alert('장바구니 추가 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('장바구니 추가 중 오류가 발생했습니다: ' + error.message);
    });
}

// 바로 주문하기 함수 추가
function directOrder() {
    // 먼저 가격 계산을 수행
    const formData = new FormData(document.getElementById('orderForm'));
    
    // 필수 입력값 검증
    const jong = formData.get('jong');
    const garo = formData.get('garo');
    const sero = formData.get('sero');
    const mesu = formData.get('mesu');
    
    if (!jong || !garo || !sero || !mesu) {
        alert('모든 필수 옵션을 입력해주세요.');
        return;
    }
    
    // 선택된 옵션들 업데이트
    updateSelectedOptions();
    
    // 가격 계산 먼저 수행
    formData.append('action', 'calculate');
    
    fetch('calculate_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(priceData => {
        if (priceData.success) {
            // 주문 정보를 URL 파라미터로 구성
            const params = new URLSearchParams();
            params.set('direct_order', '1');
            params.set('product_type', 'sticker');
            params.set('jong', formData.get('jong'));
            params.set('garo', formData.get('garo'));
            params.set('sero', formData.get('sero'));
            params.set('mesu', formData.get('mesu'));
            params.set('uhyung', formData.get('uhyung'));
            params.set('domusong', formData.get('domusong'));
            params.set('price', priceData.price.replace(/,/g, ''));
            params.set('vat_price', priceData.price_vat.replace(/,/g, ''));
            
            // 주문 페이지로 이동
            window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
        } else {
            alert('가격 계산 중 오류가 발생했습니다: ' + priceData.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('가격 계산 중 오류가 발생했습니다: ' + error.message);
    });
}

// 컴포넌트화된 업로드 시스템이 자동으로 초기화됩니다.
</script>

<?php
// 로그인 모달 포함
include "../../includes/login_modal.php";

// 공통 푸터 포함
include "../../includes/footer.php";
?>