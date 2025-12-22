<?php
/**
 * 전단지 스타일 계산기 템플릿
 * 모든 페이지에서 include하여 사용
 */

function renderFlierCalculator($config = []) {
    // 기본 설정
    $defaults = [
        'title' => '전단지',
        'fields' => [
            ['label' => '인쇄색상', 'name' => 'color', 'options' => ['4도', '단도']],
            ['label' => '종이중량', 'name' => 'weight', 'options' => ['80g', '100g', '120g']],
            ['label' => '종이규격', 'name' => 'size', 'options' => ['A4', 'A5', 'A6']],
            ['label' => '인쇄면', 'name' => 'side', 'options' => ['단면', '양면']],
            ['label' => '수량', 'name' => 'quantity', 'options' => ['100장', '200장', '500장', '1000장']],
            ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰']]
        ]
    ];
    
    $config = array_merge($defaults, $config);
    ?>
    
    <div class="flier-card">
        <!-- 헤더 -->
        <div class="flier-header">
            <span><?php echo htmlspecialchars($config['title']); ?></span>
        </div>
        
        <!-- 계산기 본문 -->
        <div class="calculator-grid">
            <?php foreach($config['fields'] as $field): ?>
            <div class="field-group">
                <label class="field-label"><?php echo htmlspecialchars($field['label']); ?></label>
                <select name="<?php echo htmlspecialchars($field['name']); ?>" class="field-selector">
                    <option value="">선택하세요</option>
                    <?php foreach($field['options'] as $option): ?>
                    <option value="<?php echo htmlspecialchars($option); ?>">
                        <?php echo htmlspecialchars($option); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>
            
            <!-- 계산 결과 박스 -->
            <div class="result-box">
                <div class="result-title">예상 견적</div>
                <div class="result-value" id="calculated-price">0원</div>
            </div>
            
            <!-- 액션 버튼들 -->
            <button type="button" class="action-button upload-button" onclick="openFileUpload()">
                파일 업로드
            </button>
            <button type="button" class="action-button" onclick="submitOrder()">
                주문하기
            </button>
        </div>
    </div>
    
    <script>
    // 계산 로직
    document.querySelectorAll('.field-selector').forEach(selector => {
        selector.addEventListener('change', calculatePrice);
    });
    
    function calculatePrice() {
        // 여기에 가격 계산 로직 추가
        let price = 10000; // 기본가격
        
        // 각 필드값에 따른 가격 조정
        const quantity = document.querySelector('[name="quantity"]').value;
        if(quantity === '1000장') price *= 3;
        else if(quantity === '500장') price *= 2;
        
        document.getElementById('calculated-price').textContent = 
            new Intl.NumberFormat('ko-KR').format(price) + '원';
    }
    
    function openFileUpload() {
        // 파일 업로드 로직
        alert('파일 업로드 기능');
    }
    
    function submitOrder() {
        // 주문 제출 로직
        alert('주문하기 기능');
    }
    </script>
    <?php
}
?>