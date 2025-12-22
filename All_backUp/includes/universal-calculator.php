<?php
/**
 * 범용 계산기 템플릿 시스템
 * 9개 품목을 하나의 템플릿으로 관리
 * 계산 로직은 각 품목별 JS 유지
 */

class UniversalCalculator {
    
    // 품목별 설정
    private $configs = [
        'flier' => [
            'title' => '전단지 견적 계산',
            'icon' => '📄',
            'grid' => '2x3',
            'fields' => [
                ['label' => '인쇄색상', 'name' => 'color', 'options' => ['4도', '단도']],
                ['label' => '종이중량', 'name' => 'weight', 'options' => ['80g', '100g', '120g']],
                ['label' => '종이규격', 'name' => 'size', 'options' => ['A4', 'A5', 'A6']],
                ['label' => '인쇄면', 'name' => 'side', 'options' => ['단면', '양면']],
                ['label' => '수량', 'name' => 'quantity', 'options' => ['100', '200', '500', '1000']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰']]
            ]
        ],
        'namecard' => [
            'title' => '명함 견적 계산',
            'icon' => '💳',
            'grid' => '2x3',
            'fields' => [
                ['label' => '종류', 'name' => 'type', 'options' => ['일반', '고급', '수입지']],
                ['label' => '재질', 'name' => 'material', 'options' => ['백상지', '아트지', '수입지']],
                ['label' => '인쇄면', 'name' => 'side', 'options' => ['단면', '양면']],
                ['label' => '수량', 'name' => 'quantity', 'options' => ['200', '500', '1000']],
                ['label' => '코팅', 'name' => 'coating', 'options' => ['무코팅', '유광', '무광']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰']]
            ]
        ],
        'poster' => [
            'title' => '포스터 견적 계산',
            'icon' => '🎨',
            'grid' => '2x4',
            'fields' => [
                ['label' => '구분', 'name' => 'category', 'options' => ['실내용', '실외용']],
                ['label' => '종이종류', 'name' => 'paper', 'options' => ['아트지', '합성지', '현수막천']],
                ['label' => '종이규격', 'name' => 'size', 'options' => ['A0', 'A1', 'A2', 'A3']],
                ['label' => '인쇄면', 'name' => 'side', 'options' => ['단면', '양면']],
                ['label' => '코팅', 'name' => 'coating', 'options' => ['무코팅', '유광', '무광', 'UV']],
                ['label' => '수량', 'name' => 'quantity', 'options' => ['1', '10', '50', '100']],
                ['label' => '후가공', 'name' => 'finish', 'options' => ['없음', '거치대', '액자']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰']]
            ]
        ],
        'sticker' => [
            'title' => '스티커 견적 계산',
            'icon' => '🏷️',
            'grid' => '2x4',
            'fields' => [
                ['label' => '형태', 'name' => 'shape', 'options' => ['사각', '원형', '타원', '특수']],
                ['label' => '재질', 'name' => 'material', 'options' => ['아트지', '유포지', '투명', '은무']],
                ['label' => '크기', 'name' => 'size', 'options' => ['소형', '중형', '대형', '특대']],
                ['label' => '코팅', 'name' => 'coating', 'options' => ['무코팅', '유광', '무광']],
                ['label' => '칼선', 'name' => 'cutting', 'options' => ['일반', '하프칼', '완전칼']],
                ['label' => '수량', 'name' => 'quantity', 'options' => ['100', '500', '1000', '5000']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰'], 'full_width' => true]
            ]
        ],
        'envelope' => [
            'title' => '봉투 견적 계산',
            'icon' => '✉️',
            'grid' => '2x3',
            'fields' => [
                ['label' => '종류', 'name' => 'type', 'options' => ['일반', '창봉투', '각대봉투']],
                ['label' => '규격', 'name' => 'size', 'options' => ['소', '중', '대', '특대']],
                ['label' => '용지', 'name' => 'paper', 'options' => ['백상지', '크라프트', '색지']],
                ['label' => '인쇄', 'name' => 'print', 'options' => ['무인쇄', '1도', '2도', '4도']],
                ['label' => '수량', 'name' => 'quantity', 'options' => ['500', '1000', '2000', '5000']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰']]
            ]
        ],
        'ncr' => [
            'title' => 'NCR/전표 견적 계산',
            'icon' => '📋',
            'grid' => '2x4',
            'fields' => [
                ['label' => '종류', 'name' => 'type', 'options' => ['2장복사', '3장복사', '4장복사']],
                ['label' => '규격', 'name' => 'size', 'options' => ['A4', 'A5', 'B5', '특수']],
                ['label' => '제본', 'name' => 'binding', 'options' => ['50매', '100매', '200매']],
                ['label' => '천공', 'name' => 'punch', 'options' => ['없음', '2공', '4공']],
                ['label' => '넘버링', 'name' => 'numbering', 'options' => ['없음', '있음']],
                ['label' => '수량', 'name' => 'quantity', 'options' => ['10권', '20권', '50권', '100권']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰'], 'full_width' => true]
            ]
        ],
        'book' => [
            'title' => '제본/책자 견적 계산',
            'icon' => '📚',
            'grid' => '2x4',
            'fields' => [
                ['label' => '제본방식', 'name' => 'binding', 'options' => ['중철', '무선', '스프링', '하드커버']],
                ['label' => '규격', 'name' => 'size', 'options' => ['A4', 'A5', 'B5', '특수']],
                ['label' => '표지용지', 'name' => 'cover', 'options' => ['아트지', '스노우지', '랑데뷰']],
                ['label' => '내지용지', 'name' => 'inner', 'options' => ['백상지', '모조지', '아트지']],
                ['label' => '페이지수', 'name' => 'pages', 'options' => ['~50p', '~100p', '~200p', '200p+']],
                ['label' => '인쇄색상', 'name' => 'color', 'options' => ['흑백', '2도', '4도']],
                ['label' => '수량', 'name' => 'quantity', 'options' => ['10', '50', '100', '500']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰']]
            ]
        ],
        'catalog' => [
            'title' => '카다록 견적 계산',
            'icon' => '📖',
            'grid' => '3x3',
            'fields' => [
                ['label' => '형태', 'name' => 'type', 'options' => ['2단접지', '3단접지', '맵접지', '관음접지']],
                ['label' => '규격', 'name' => 'size', 'options' => ['A3', 'A4', 'B4', '특수']],
                ['label' => '용지', 'name' => 'paper', 'options' => ['아트지', '스노우지', '랑데뷰']],
                ['label' => '인쇄면', 'name' => 'side', 'options' => ['단면', '양면']],
                ['label' => '코팅', 'name' => 'coating', 'options' => ['무코팅', '유광', '무광', '부분UV']],
                ['label' => '후가공', 'name' => 'finish', 'options' => ['없음', '오시', '미싱', '톰슨']],
                ['label' => '수량', 'name' => 'quantity', 'options' => ['100', '500', '1000', '5000']],
                ['label' => '특수가공', 'name' => 'special', 'options' => ['없음', '금박', '은박', '형압']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰']]
            ]
        ],
        'banner' => [
            'title' => '현수막/배너 견적 계산',
            'icon' => '🚩',
            'grid' => '2x4',
            'fields' => [
                ['label' => '종류', 'name' => 'type', 'options' => ['일반현수막', '미니배너', 'X배너', '롤업배너']],
                ['label' => '재질', 'name' => 'material', 'options' => ['일반천', '고급천', '메쉬천']],
                ['label' => '가로크기', 'name' => 'width', 'options' => ['~1m', '~3m', '~5m', '5m+']],
                ['label' => '세로크기', 'name' => 'height', 'options' => ['~1m', '~2m', '~3m', '3m+']],
                ['label' => '인쇄면', 'name' => 'side', 'options' => ['단면', '양면']],
                ['label' => '후가공', 'name' => 'finish', 'options' => ['열재단', '레이저재단']],
                ['label' => '거치대', 'name' => 'stand', 'options' => ['없음', 'X배너', '롤업', '거치대']],
                ['label' => '편집디자인', 'name' => 'design', 'options' => ['셀프편집', '디자인의뢰']]
            ]
        ]
    ];
    
    /**
     * 품목별 계산기 렌더링
     */
    public function render($product_type, $options = []) {
        if (!isset($this->configs[$product_type])) {
            return "<!-- 품목 설정을 찾을 수 없습니다: $product_type -->";
        }
        
        $config = $this->configs[$product_type];
        $field_count = count($config['fields']);
        $grid_class = $this->getGridClass($config['grid']);
        
        ob_start();
        ?>
        <div class="universal-calculator-card" data-product="<?php echo $product_type; ?>">
            <!-- 헤더 -->
            <div class="calc-header">
                <h2><?php echo $config['icon']; ?> <?php echo $config['title']; ?></h2>
            </div>
            
            <!-- 계산기 본문 -->
            <div class="calc-body">
                <form id="<?php echo $product_type; ?>Form" class="calc-grid <?php echo $grid_class; ?> field-count-<?php echo $field_count; ?>">
                    <?php foreach($config['fields'] as $field): ?>
                    <div class="calc-field <?php echo isset($field['full_width']) ? 'full-width' : ''; ?>">
                        <label class="calc-label"><?php echo $field['label']; ?></label>
                        <select name="<?php echo $field['name']; ?>" class="calc-select" data-product="<?php echo $product_type; ?>" data-field="<?php echo $field['name']; ?>">
                            <option value="">선택하세요</option>
                            <?php foreach($field['options'] as $option): ?>
                            <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- 가격 결과 -->
                    <div class="calc-result">
                        <div class="result-title">예상 견적</div>
                        <div class="result-price" id="<?php echo $product_type; ?>Price">0원</div>
                        <div class="result-note">VAT 별도, 배송비 별도</div>
                    </div>
                    
                    <!-- 액션 버튼 -->
                    <div class="calc-actions">
                        <button type="button" class="calc-btn upload" onclick="uploadFile('<?php echo $product_type; ?>')">
                            📎 파일 업로드
                        </button>
                        <button type="button" class="calc-btn order" onclick="submitOrder('<?php echo $product_type; ?>')">
                            🛒 주문하기
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 그리드 클래스 결정
     */
    private function getGridClass($grid) {
        switch($grid) {
            case '2x3': return 'grid-2x3';
            case '2x4': return 'grid-2x4';
            case '3x3': return 'grid-3x3';
            default: return 'grid-2x3';
        }
    }
    
    /**
     * 품목 목록 반환
     */
    public function getProductList() {
        $products = [];
        foreach($this->configs as $key => $config) {
            $products[] = [
                'key' => $key,
                'title' => $config['title'],
                'icon' => $config['icon']
            ];
        }
        return $products;
    }
}

// 전역 인스턴스 생성
$universalCalculator = new UniversalCalculator();
?>