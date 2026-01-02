<?php
/**
 * 전단지 픽셀 퍼펙트 템플릿
 * 전단지와 픽셀 단위로 동일한 레이아웃 제공
 * 계산 로직은 건드리지 않고 디자인만 적용
 */

function renderFlierPerfectCalculator($config = []) {
    // 기본 설정
    $defaults = [
        'title' => '견적 계산',
        'fields' => [
            ['label' => '인쇄색상', 'name' => 'color', 'id' => 'color'],
            ['label' => '종이중량', 'name' => 'weight', 'id' => 'weight'],
            ['label' => '종이규격', 'name' => 'size', 'id' => 'size'],
            ['label' => '인쇄면', 'name' => 'side', 'id' => 'side'],
            ['label' => '수량', 'name' => 'quantity', 'id' => 'quantity'],
            ['label' => '편집디자인', 'name' => 'design', 'id' => 'design']
        ],
        'show_result' => true,
        'show_buttons' => true,
        'form_id' => 'calculatorForm'
    ];
    
    $config = array_merge($defaults, $config);
    ?>
    
    <div class="flier-perfect-card">
        <!-- 헤더 (픽셀 퍼펙트) -->
        <div class="flier-perfect-header">
            <h2><?php echo htmlspecialchars($config['title']); ?></h2>
        </div>
        
        <!-- 계산기 본문 -->
        <div class="flier-perfect-body">
            <form id="<?php echo htmlspecialchars($config['form_id']); ?>" class="flier-perfect-grid">
                <?php foreach($config['fields'] as $field): ?>
                <div class="flier-perfect-field">
                    <label class="flier-perfect-label" for="<?php echo htmlspecialchars($field['id']); ?>">
                        <?php echo htmlspecialchars($field['label']); ?>
                    </label>
                    <select 
                        name="<?php echo htmlspecialchars($field['name']); ?>" 
                        id="<?php echo htmlspecialchars($field['id']); ?>"
                        class="flier-perfect-select">
                        <!-- 옵션은 기존 JavaScript가 동적으로 채움 -->
                    </select>
                </div>
                <?php endforeach; ?>
                
                <?php if($config['show_result']): ?>
                <!-- 결과 박스 (민트색) -->
                <div class="flier-perfect-result">
                    <div class="flier-perfect-result-title">예상 견적</div>
                    <div class="flier-perfect-result-value" id="resultPrice">0원</div>
                    <div class="flier-perfect-result-note">VAT 별도, 배송비 별도</div>
                </div>
                <?php endif; ?>
                
                <?php if($config['show_buttons']): ?>
                <!-- 액션 버튼들 -->
                <button type="button" class="flier-perfect-button upload" onclick="handleFileUpload()">
                    📎 파일 업로드
                </button>
                <button type="button" class="flier-perfect-button" onclick="handleOrderSubmit()">
                    🛒 주문하기
                </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <style>
    /* 기존 스타일 오버라이드를 위한 추가 규칙 */
    .flier-perfect-card * {
        box-sizing: border-box;
    }
    
    .flier-perfect-card form {
        margin: 0;
        padding: 0;
    }
    </style>
    
    <script>
    // 기존 함수가 없을 경우를 위한 폴백
    if (typeof handleFileUpload === 'undefined') {
        function handleFileUpload() {
            // 기존 파일 업로드 함수 호출 또는 기본 동작
            if (typeof openUploadModal !== 'undefined') {
                openUploadModal();
            } else {
                alert('파일 업로드 기능');
            }
        }
    }
    
    if (typeof handleOrderSubmit === 'undefined') {
        function handleOrderSubmit() {
            // 기존 주문 함수 호출 또는 기본 동작
            if (typeof submitOrder !== 'undefined') {
                submitOrder();
            } else {
                alert('주문하기 기능');
            }
        }
    }
    </script>
    <?php
}

// 기존 페이지에 CSS만 적용하는 함수
function applyFlierPerfectStyles() {
    echo '<link rel="stylesheet" href="/css/flier-pixel-perfect.css">';
}
?>