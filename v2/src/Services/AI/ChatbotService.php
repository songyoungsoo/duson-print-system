<?php
declare(strict_types=1);

namespace App\Services\AI;

class ChatbotService
{
    private string $apiKey;
    private string $model = 'gemini-2.0-flash';
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private $db = null;
    private array $products;
    
    // 제품별 단계 정의
    private array $productSteps = [
        'namecard' => [
            'label' => '명함',
            'steps' => ['style', 'section', 'quantity', 'side', 'design'],
            'stepLabels' => ['명함 종류', '용지', '수량', '인쇄면', '디자인'],
            'delivery' => '일반명함 익일 출고 / 오전판(AM 11:00까지) 접수시 당일 출고',
        ],
        'inserted' => [
            'label' => '전단지',
            'steps' => ['style', 'tree', 'section', 'quantity', 'side', 'design'],
            'stepLabels' => ['인쇄도수', '용지', '규격', '수량', '인쇄면', '디자인'],
            'delivery' => '시안확정 후 2~3일 출고',
        ],
        'sticker' => [
            'label' => '스티커',
            'steps' => ['material', 'size', 'quantity', 'domusong'],
            'stepLabels' => ['재질', '크기', '수량', '도무송'],
            'delivery' => '시안확정 후 3~4일 출고',
        ],
        'envelope' => [
            'label' => '봉투',
            'steps' => ['style', 'section', 'quantity', 'design'],
            'stepLabels' => ['봉투 종류', '규격', '수량', '디자인'],
            'delivery' => '시안확정 후 3~4일 출고',
        ],
        'cadarok' => [
            'label' => '카다록',
            'steps' => ['style', 'section', 'quantity', 'design'],
            'stepLabels' => ['종류', '규격/페이지', '수량', '디자인'],
            'delivery' => '시안확정 후 5~7일 출고',
        ],
        'littleprint' => [
            'label' => '포스터',
            'steps' => ['style', 'tree', 'section', 'quantity'],
            'stepLabels' => ['종류', '용지', '규격', '수량'],
            'delivery' => '시안확정 후 2~3일 출고',
        ],
        'merchandisebond' => [
            'label' => '상품권',
            'steps' => ['style', 'section', 'quantity', 'design'],
            'stepLabels' => ['종류', '옵션', '수량', '디자인'],
            'delivery' => '익일 출고(넘버링 등 옵션이 있을 경우 전화 문의 02-2632-1830)',
        ],
        'ncrflambeau' => [
            'label' => 'NCR양식지',
            'steps' => ['style', 'section', 'tree', 'quantity', 'design'],
            'stepLabels' => ['구분', '규격', '색상', '수량', '디자인'],
            'delivery' => '시안확정 후 5~7일 출고',
        ],
        'msticker' => [
            'label' => '자석스티커',
            'steps' => ['style', 'section', 'quantity'],
            'stepLabels' => ['종류', '규격', '수량'],
            'delivery' => '시안확정 후 5~7일 출고',
        ],
    ];
    
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
        $this->products = require V2_ROOT . '/config/products.php';
        $this->initDatabase();
        $this->initSession();
    }
    
    private function initDatabase(): void
    {
        $dbPath = dirname(V2_ROOT) . '/db.php';
        if (file_exists($dbPath)) {
            global $db;
            require_once $dbPath;
            $this->db = $db;
        }
    }
    
    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['chatbot'])) {
            $_SESSION['chatbot'] = [
                'product' => '',
                'step' => 0,
                'selections' => [],
                'selectionIds' => [],  // DB no 값 저장
            ];
        }
    }
    
    private function getState(): array
    {
        return $_SESSION['chatbot'];
    }
    
    private function setState(array $state): void
    {
        $_SESSION['chatbot'] = $state;
    }
    
    private function resetState(): void
    {
        $_SESSION['chatbot'] = [
            'product' => '',
            'step' => 0,
            'selections' => [],
            'selectionIds' => [],
        ];
    }
    
    public function chat(string $message, array $history = []): array
    {
        $state = $this->getState();
        
        // "다시", "처음", "리셋" → 초기화
        if (preg_match('/다시|처음|리셋|초기화|취소/u', $message)) {
            $this->resetState();
            return $this->getProductMenuResponse();
        }
        
        // 제품 감지: 이미 제품 선택 진행 중이면 숫자 매칭 비활성화 (옵션 선택과 충돌 방지)
        $inProgress = !empty($state['product']);
        $detectedProduct = $this->detectProduct($message, $history, $inProgress);
        
        if (!$inProgress) {
            // 제품 미선택 상태
            if (empty($detectedProduct)) {
                return $this->getProductMenuResponse();
            }
            $state['product'] = $detectedProduct;
            $state['step'] = 0;
            $state['selections'] = [];
            $state['selectionIds'] = [];
            $this->setState($state);
            
            return $this->askCurrentStep($state);
        }
        
        // 제품 선택 중인데 다른 제품 키워드(텍스트) 입력 → 제품 전환
        if (!empty($detectedProduct) && $detectedProduct !== $state['product']) {
            $state['product'] = $detectedProduct;
            $state['step'] = 0;
            $state['selections'] = [];
            $state['selectionIds'] = [];
            $this->setState($state);
            
            return $this->askCurrentStep($state);
        }
        
        // 제품 선택됨 → 현재 단계 처리
        return $this->processStepAnswer($state, $message);
    }
    
    /**
     * 품목 선택 메뉴
     */
    private function getProductMenuResponse(): array
    {
        $options = [];
        $i = 1;
        foreach ($this->productSteps as $key => $info) {
            $options[] = ['num' => $i, 'label' => $info['label']];
            $i++;
        }
        return ['success' => true, 'message' => "어떤 인쇄물 가격이 궁금하세요?", 'options' => $options];
    }
    
    /**
     * 현재 단계의 선택지 제시
     */
    private function askCurrentStep(array $state): array
    {
        $product = $state['product'];
        $config = $this->productSteps[$product];
        $stepIdx = $state['step'];
        $steps = $config['steps'];
        
        // 모든 단계 완료 → 가격 안내
        if ($stepIdx >= count($steps)) {
            return $this->showPrice($state);
        }
        
        $stepType = $steps[$stepIdx];
        $stepLabel = $config['stepLabels'][$stepIdx];
        $options = $this->getStepOptions($product, $stepType, $state);
        
        // 선택지가 1개뿐이면 자동 선택하고 다음 단계로
        if (count($options) === 1 && !in_array($stepType, ['side', 'design', 'quantity', 'size', 'domusong'])) {
            $state['selections'][$stepType] = $options[0]['title'];
            $state['selectionIds'][$stepType] = (int)$options[0]['no'];
            $state['step']++;
            $this->setState($state);
            return $this->askCurrentStep($state);
        }
        
        if (empty($options) && $stepType === 'quantity') {
            return $this->askQuantityFreeInput($stepLabel, $product, $state);
        }
        
        if (empty($options) && $stepType === 'size') {
            return ['success' => true, 'message' => "크기를 입력해주세요 (가로×세로 mm):\n예: 50×30, 100×100"];
        }
        
        if (empty($options)) {
            // 옵션 못 가져오면 건너뛰기
            $state['step']++;
            $state['selections'][$stepType] = '-';
            $state['selectionIds'][$stepType] = 0;
            $this->setState($state);
            return $this->askCurrentStep($state);
        }
        
        $result = ['success' => true];
        $particle = $this->getParticle($stepLabel, '을', '를');
        $result['message'] = "{$stepLabel}{$particle} 선택해주세요:";
        $optionList = [];
        foreach ($options as $i => $opt) {
            $optionList[] = ['num' => $i + 1, 'label' => $opt['title']];
        }
        $result['options'] = $optionList;
        
        // 용지 선택 단계면 paper_images 추가
        if ($stepType === 'section' && $product === 'namecard') {
            if ($this->containsPaperSelection(implode(',', array_column($options, 'title')))) {
                $result['paper_images'] = $this->getPaperImages();
            }
        }
        
        return $result;
    }
    
    /**
     * 수량 자유입력 안내
     */
    private function askQuantityFreeInput(string $label, string $product, array $state): array
    {
        $config = $this->productSteps[$product];
        $qtyOptions = $this->getQuantityOptions($product, $state);
        
        if (!empty($qtyOptions)) {
            // 세션에 수량 옵션 저장 (processQuantityStep에서 매칭용)
            $state['_quantityOptions'] = $qtyOptions;
            $this->setState($state);
            
            $p = $this->getParticle($label, '을', '를');
            $optionList = [];
            foreach ($qtyOptions as $i => $q) {
                $optionList[] = ['num' => $i + 1, 'label' => $q['display']];
            }
            return ['success' => true, 'message' => "{$label}{$p} 선택해주세요:", 'options' => $optionList];
        }
        
        $p = $this->getParticle($label, '을', '를');
        return ['success' => true, 'message' => "{$label}{$p} 입력해주세요:"];
    }
    
    /**
     * 사용자 답변 처리
     */
    private function processStepAnswer(array $state, string $message): array
    {
        $product = $state['product'];
        $config = $this->productSteps[$product];
        $stepIdx = $state['step'];
        $steps = $config['steps'];
        
        if ($stepIdx >= count($steps)) {
            // 이미 완료 → 새 문의 감지
            $newProduct = $this->detectProduct($message, []);
            if (!empty($newProduct)) {
                $this->resetState();
                $state = $this->getState();
                $state['product'] = $newProduct;
                $this->setState($state);
                return $this->askCurrentStep($state);
            }
            // 자유 질문 → AI 호출
            return $this->callAiForFreeQuestion($message);
        }
        
        $stepType = $steps[$stepIdx];
        $options = $this->getStepOptions($product, $stepType, $state);
        
        // 수량 단계 특수 처리
        if ($stepType === 'quantity') {
            return $this->processQuantityStep($state, $message, $product);
        }
        
        // 크기 입력 (스티커)
        if ($stepType === 'size') {
            $state['selections']['size'] = trim($message);
            $state['selectionIds']['size'] = 0;
            $state['step']++;
            $this->setState($state);
            return $this->askCurrentStep($state);
        }
        
        // 디자인 단계
        if ($stepType === 'design') {
            return $this->processDesignStep($state, $message);
        }
        
        // 인쇄면 단계 (명함)
        if ($stepType === 'side') {
            return $this->processSideStep($state, $message);
        }
        
        // 일반 선택지 매칭
        if (empty($options)) {
            $state['selections'][$stepType] = trim($message);
            $state['selectionIds'][$stepType] = 0;
            $state['step']++;
            $this->setState($state);
            return $this->askCurrentStep($state);
        }
        
        $matched = $this->matchOption($message, $options);
        if ($matched === null) {
            $optionList = [];
            foreach ($options as $i => $opt) {
                $optionList[] = ['num' => $i + 1, 'label' => $opt['title']];
            }
            return ['success' => true, 'message' => "선택지에서 골라주세요:", 'options' => $optionList];
        }
        
        $state['selections'][$stepType] = $matched['title'];
        $state['selectionIds'][$stepType] = (int)$matched['no'];
        $state['step']++;
        $this->setState($state);
        
        return $this->askCurrentStep($state);
    }
    
    /**
     * 수량 단계 처리
     */
    private function processQuantityStep(array $state, string $message, string $product): array
    {
        $config = $this->productSteps[$product];
        $stepType = $config['steps'][$state['step']];
        $msg = trim($message);
        
        // 세션에 저장된 수량 옵션에서 매칭 시도
        $qtyOptions = $state['_quantityOptions'] ?? [];
        $matched = null;
        
        if (!empty($qtyOptions)) {
            // 1) 번호 매칭: "1", "2", "3" ...
            if (preg_match('/^(\d+)$/', $msg, $m)) {
                $idx = (int)$m[1] - 1;
                if (isset($qtyOptions[$idx])) {
                    $matched = $qtyOptions[$idx];
                }
            }
            
            // 2) 텍스트 매칭: display 문자열 부분일치
            if ($matched === null) {
                foreach ($qtyOptions as $opt) {
                    if (mb_strpos($msg, $opt['display']) !== false || mb_strpos($opt['display'], $msg) !== false) {
                        $matched = $opt;
                        break;
                    }
                    // value만으로도 매칭 (예: "0.5" 입력)
                    if ($msg === $opt['value'] || $msg === $opt['value'] . $this->getUnit($product)) {
                        $matched = $opt;
                        break;
                    }
                }
            }
        }
        
        if ($matched !== null) {
            // 구조화된 데이터에서 정확한 값 사용
            $state['selections'][$stepType] = $matched['value'];
            $state['selections']['_quantityDisplay'] = $matched['display'];
            $state['selections']['_quantityTwo'] = $matched['quantityTwo'] ?? '';
            $state['selectionIds'][$stepType] = 0;
        } else {
            // 옵션 매칭 실패 → 숫자 직접 추출 (단위 앞 숫자만)
            if (preg_match('/^([\d.]+)/', $msg, $m)) {
                $num = $m[1];
            } else {
                $num = preg_replace('/[^0-9.]/', '', $msg);
            }
            if (empty($num)) {
                return ['success' => true, 'message' => "숫자로 입력해주세요:"];
            }
            $state['selections'][$stepType] = $num;
            $state['selections']['_quantityDisplay'] = '';
            $state['selections']['_quantityTwo'] = '';
            $state['selectionIds'][$stepType] = 0;
        }
        
        // 수량 옵션 임시 데이터 정리
        unset($state['_quantityOptions']);
        $state['step']++;
        $this->setState($state);
        
        return $this->askCurrentStep($state);
    }
    
    /**
     * 인쇄면 단계 처리
     */
    private function processSideStep(array $state, string $message): array
    {
        $msg = trim($message);
        if (preg_match('/양면|2|앞뒤/u', $msg)) {
            $side = '양면';
            $sideId = 2;
        } elseif (preg_match('/단면|1|앞면/u', $msg)) {
            $side = '단면';
            $sideId = 1;
        } else {
            return ['success' => true, 'message' => "인쇄면을 선택해주세요:", 'options' => [
                ['num' => 1, 'label' => '단면'],
                ['num' => 2, 'label' => '양면'],
            ]];
        }
        
        $state['selections']['side'] = $side;
        $state['selectionIds']['side'] = $sideId;
        $state['step']++;
        $this->setState($state);
        
        return $this->askCurrentStep($state);
    }
    
    /**
     * 디자인 단계 처리
     */
    private function processDesignStep(array $state, string $message): array
    {
        $msg = trim($message);
        if (preg_match('/있음|보유|1|시안/u', $msg)) {
            $design = '디자인 있음';
            $designId = 0;
        } elseif (preg_match('/의뢰|제작|2|새로/u', $msg)) {
            $design = '디자인 의뢰';
            $designId = 1;
        } else {
            return ['success' => true, 'message' => "디자인을 선택해주세요:", 'options' => [
                ['num' => 1, 'label' => '디자인 있음 (추가비용 없음)'],
                ['num' => 2, 'label' => '디자인 의뢰'],
            ]];
        }
        
        $state['selections']['design'] = $design;
        $state['selectionIds']['design'] = $designId;
        $state['step']++;
        $this->setState($state);
        
        return $this->askCurrentStep($state);
    }
    
    /**
     * DB에서 현재 단계 옵션 조회
     */
    private function getStepOptions(string $product, string $stepType, array $state): array
    {
        if (!$this->db) return [];
        
        $table = $this->getTableName($product);
        
        switch ($stepType) {
            case 'style':
            case 'material':
                return $this->getLevel1Options($table);
            
            case 'tree':
                $parentId = $state['selectionIds']['style'] ?? 0;
                return $this->getLevel2Options($table, $parentId);
            
            case 'section':
                $parentId = $state['selectionIds']['style'] ?? 0;
                return $this->getLevel3Options($table, $parentId);
            
            case 'side':
                return [
                    ['no' => 1, 'title' => '단면'],
                    ['no' => 2, 'title' => '양면'],
                ];
            
            case 'design':
                return [
                    ['no' => 0, 'title' => '디자인 있음 (추가비용 없음)'],
                    ['no' => 1, 'title' => '디자인 의뢰'],
                ];
            
            case 'quantity':
            case 'size':
            case 'domusong':
                return []; // 자유입력 또는 별도 처리
            
            default:
                return [];
        }
    }
    
    /**
     * 수량 선택지 조회 (구조화된 데이터 반환)
     * @return array [ ['value' => '0.5', 'display' => '0.5(2000매)연', 'quantityTwo' => '2000'], ... ]
     */
    private function getQuantityOptions(string $product, array $state): array
    {
        if (!$this->db) return [];
        
        $priceTable = $this->getPriceTableName($product);
        if (empty($priceTable)) return [];
        
        $styleId = $state['selectionIds']['style'] ?? 0;
        $sectionId = $state['selectionIds']['section'] ?? 0;
        
        $hasQtyTwo = ($product === 'inserted');
        $selectCols = $hasQtyTwo ? 'DISTINCT quantity, quantityTwo' : 'DISTINCT quantity';
        
        // 4단계 드롭다운 제품 (전단지, 포스터): TreeSelect(용지) 조건 필수
        $has4Level = in_array($product, ['inserted', 'littleprint']);
        if ($has4Level) {
            $treeId = $state['selectionIds']['tree'] ?? 0;
            $sql = "SELECT {$selectCols} FROM {$priceTable} WHERE style = ? AND TreeSelect = ? AND Section = ? ORDER BY quantity ASC";
            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) return [];
            mysqli_stmt_bind_param($stmt, 'iii', $styleId, $treeId, $sectionId);
        } else {
            $sql = "SELECT {$selectCols} FROM {$priceTable} WHERE style = ? AND Section = ? ORDER BY quantity ASC";
            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) return [];
            mysqli_stmt_bind_param($stmt, 'ii', $styleId, $sectionId);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $quantities = [];
        $seen = [];
        $unit = $this->getUnit($product);
        while ($row = mysqli_fetch_assoc($result)) {
            $qty = $row['quantity'];
            if (isset($seen[$qty])) continue;
            $seen[$qty] = true;
            
            $qtyTwo = '';
            if ($hasQtyTwo && !empty($row['quantityTwo'])) {
                $qtyTwo = $row['quantityTwo'];
                $qtyTwoFormatted = number_format((int)$qtyTwo);
                // 표시: "0.5(2,000매)연"
                $display = "{$qty}({$qtyTwoFormatted}매){$unit}";
            } else {
                $display = $qty . $unit;
            }
            
            $quantities[] = [
                'value' => (string)$qty,
                'display' => $display,
                'quantityTwo' => $qtyTwo,
            ];
        }
        mysqli_stmt_close($stmt);
        
        return $quantities;
    }
    
    /**
     * 가격 조회 및 표시
     */
    private function showPrice(array $state): array
    {
        $product = $state['product'];
        $config = $this->productSteps[$product];
        $sels = $state['selections'];
        $selIds = $state['selectionIds'];
        
        // 선택 요약
        $summary = [];
        foreach ($config['steps'] as $i => $step) {
            if (isset($sels[$step]) && $sels[$step] !== '-') {
                $val = $sels[$step];
                if ($step === 'quantity') {
                    // 저장된 display 문자열 우선 사용 (예: "0.5(2,000매)연")
                    $qtyDisplay = $sels['_quantityDisplay'] ?? '';
                    $val = !empty($qtyDisplay) ? $qtyDisplay : $val . $this->getUnit($product);
                }
                $summary[] = $val;
            }
        }
        $summaryText = implode(' / ', $summary);
        
        // DB에서 가격 조회
        $price = $this->lookupPrice($product, $selIds, $sels);
        
        if ($price !== null) {
            $priceVat = (int)round($price * 1.1);
            $lines = [
                "✅ {$config['label']} / {$summaryText}",
                "💰 총 " . number_format($priceVat) . "원 (VAT포함)",
                $config['delivery'],
            ];
        } else {
            $lines = [
                "✅ {$config['label']} / {$summaryText}",
                "정확한 견적은 전화(02-2632-1830)로 문의해주세요.",
            ];
        }
        
        $lines[] = "\n다른 제품도 궁금하시면 말씀해주세요!";
        
        // 대화 완료 → 상태 유지 (추가 질문 가능)
        return ['success' => true, 'message' => implode("\n", $lines)];
    }
    
    /**
     * DB에서 실제 가격 조회
     */
    private function lookupPrice(string $product, array $selIds, array $sels): ?int
    {
        if (!$this->db) return null;
        
        $priceTable = $this->getPriceTableName($product);
        if (empty($priceTable)) return null;
        
        $styleId = $selIds['style'] ?? 0;
        $sectionId = $selIds['section'] ?? ($selIds['tree'] ?? 0);
        $quantity = $sels['quantity'] ?? '0';
        $quantity = preg_replace('/[^0-9.]/', '', $quantity);
        
        // 4단계 드롭다운 제품 (전단지, 포스터): TreeSelect 조건 필수
        $has4Level = in_array($product, ['inserted', 'littleprint']);
        if ($has4Level) {
            $treeId = $selIds['tree'] ?? 0;
            $poType = ($selIds['side'] ?? 1) == 2 ? '2' : '1';
            $sql = "SELECT money, DesignMoney FROM {$priceTable} 
                    WHERE style = ? AND TreeSelect = ? AND Section = ? AND quantity = ? AND POtype = ?
                    LIMIT 1";
            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) return null;
            mysqli_stmt_bind_param($stmt, 'iiiss', $styleId, $treeId, $sectionId, $quantity, $poType);
        } elseif ($product === 'namecard') {
            $poType = ($selIds['side'] ?? 1) == 2 ? '2' : '1';
            $sql = "SELECT money, DesignMoney FROM {$priceTable} 
                    WHERE style = ? AND Section = ? AND quantity = ? AND POtype = ?
                    LIMIT 1";
            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) return null;
            mysqli_stmt_bind_param($stmt, 'iiss', $styleId, $sectionId, $quantity, $poType);
        } else {
            $sql = "SELECT money, DesignMoney FROM {$priceTable} 
                    WHERE style = ? AND Section = ? AND quantity = ?
                    LIMIT 1";
            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) return null;
            mysqli_stmt_bind_param($stmt, 'iis', $styleId, $sectionId, $quantity);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$row) return null;
        
        $printPrice = (int)$row['money'];
        $designPrice = (int)($row['DesignMoney'] ?? 0);
        
        // 디자인 의뢰시 디자인비 추가
        $hasDesign = ($selIds['design'] ?? 0) == 1;
        
        return $hasDesign ? ($printPrice + $designPrice) : $printPrice;
    }
    
    /**
     * 제품 → 가격 테이블명
     */
    private function getPriceTableName(string $product): string
    {
        $map = [
            'namecard'        => 'mlangprintauto_namecard',
            'inserted'        => 'mlangprintauto_inserted',
            'envelope'        => 'mlangprintauto_envelope',
            'cadarok'         => 'mlangprintauto_cadarok',
            'littleprint'     => 'mlangprintauto_littleprint',
            'merchandisebond' => 'mlangprintauto_merchandisebond',
            'ncrflambeau'     => 'mlangprintauto_ncrflambeau',
            'msticker'        => 'mlangprintauto_msticker',
        ];
        return $map[$product] ?? '';
    }
    
    /**
     * 제품 → transactioncate 테이블명
     */
    private function getTableName(string $product): string
    {
        $map = [
            'namecard'        => 'namecard',
            'inserted'        => 'inserted',
            'sticker'         => 'sticker',
            'envelope'        => 'envelope',
            'cadarok'         => 'cadarok',
            'littleprint'     => 'littleprint',
            'merchandisebond' => 'merchandisebond',
            'ncrflambeau'     => 'ncrflambeau',
            'msticker'        => 'msticker',
        ];
        return $map[$product] ?? $product;
    }
    
    /**
     * 제품별 단위
     */
    private function getUnit(string $product): string
    {
        $units = [
            'namecard' => '매', 'inserted' => '연', 'sticker' => '매',
            'envelope' => '매', 'cadarok' => '부', 'littleprint' => '매',
            'merchandisebond' => '매', 'ncrflambeau' => '권', 'msticker' => '매',
        ];
        return $units[$product] ?? '매';
    }
    
    /**
     * 한국어 조사 판별 (받침 유무: 을/를, 이/가, 은/는)
     */
    private function getParticle(string $text, string $withBatchim, string $withoutBatchim): string
    {
        $lastChar = mb_substr($text, -1);
        $code = mb_ord($lastChar);
        if ($code >= 0xAC00 && $code <= 0xD7A3) {
            return (($code - 0xAC00) % 28 === 0) ? $withoutBatchim : $withBatchim;
        }
        return $withBatchim;
    }
    
    /**
     * 사용자 입력과 옵션 매칭
     */
    private function matchOption(string $message, array $options): ?array
    {
        $msg = trim($message);
        
        // 번호 매칭 (1, 2, 3...)
        if (preg_match('/^(\d+)$/', $msg, $m)) {
            $idx = (int)$m[1] - 1;
            if (isset($options[$idx])) {
                return $options[$idx];
            }
        }
        
        // 텍스트 매칭 (부분 일치)
        foreach ($options as $opt) {
            if (mb_strpos($msg, $opt['title']) !== false || mb_strpos($opt['title'], $msg) !== false) {
                return $opt;
            }
        }
        
        return null;
    }
    
    /**
     * 대화에서 제품 키워드 감지
     * @param bool $skipNumberMatch true이면 숫자 매칭 건너뜀 (진행 중 옵션 선택과 충돌 방지)
     */
    private function detectProduct(string $message, array $history, bool $skipNumberMatch = false): string
    {
        $keywords = [
            'namecard'        => ['명함'],
            'inserted'        => ['전단지', '전단', '플라이어'],
            'sticker'         => ['스티커'],
            'envelope'        => ['봉투'],
            'cadarok'         => ['카다록', '카탈로그', '카달로그', '리플렛'],
            'littleprint'     => ['포스터', '소량인쇄', '소량'],
            'merchandisebond' => ['상품권'],
            'ncrflambeau'     => ['NCR', 'ncr', '양식지'],
            'msticker'        => ['자석스티커', '자석'],
        ];
        
        // 번호 매칭 (1~9) - 제품 선택 진행 중에는 건너뜀
        if (!$skipNumberMatch && preg_match('/^(\d)$/', trim($message), $m)) {
            $productKeys = array_keys($this->productSteps);
            $idx = (int)$m[1] - 1;
            if (isset($productKeys[$idx])) {
                return $productKeys[$idx];
            }
        }
        
        // 텍스트 키워드 매칭: 현재 메시지만 검사 (히스토리 포함 시 제품 메뉴의 "명함" 등이 오탐됨)
        foreach ($keywords as $product => $terms) {
            foreach ($terms as $term) {
                if (mb_strpos($message, $term) !== false) {
                    return $product;
                }
            }
        }
        
        // 현재 메시지에서 못 찾았고 & 진행 중이 아닐 때만 히스토리 검색 (초기 제품 감지용)
        if (!$skipNumberMatch && !empty($history)) {
            // 히스토리에서 사용자 메시지만 검색 (봇 응답의 제품 메뉴 텍스트 제외)
            foreach ($history as $msg) {
                if (($msg['role'] ?? '') !== 'user') continue;
                $userText = $msg['content'] ?? '';
                foreach ($keywords as $product => $terms) {
                    foreach ($terms as $term) {
                        if (mb_strpos($userText, $term) !== false) {
                            return $product;
                        }
                    }
                }
            }
        }
        
        return '';
    }
    
    /**
     * 자유 질문 → AI 호출 (최소 프롬프트)
     */
    private function callAiForFreeQuestion(string $message): array
    {
        if (empty($this->apiKey)) {
            return ['success' => true, 'message' => "자세한 문의는 전화(02-2632-1830)로 연락주세요!"];
        }
        
        $prompt = "두손기획인쇄 상담봇. 짧게 답변. 인쇄 관련 질문만 답변. 가격문의는 품목 선택 안내.";
        
        $data = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $message]]]
            ],
            'systemInstruction' => [
                'parts' => [['text' => $prompt]]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 200,
            ]
        ];
        
        $url = $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['success' => true, 'message' => "자세한 문의는 전화(02-2632-1830)로 연락주세요!"];
        }
        
        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if (empty($text)) {
            return ['success' => true, 'message' => "자세한 문의는 전화(02-2632-1830)로 연락주세요!"];
        }
        
        return ['success' => true, 'message' => trim($text)];
    }
    
    // ===== DB 조회 메서드 (기존 유지) =====
    
    private function getLevel1Options(string $table): array
    {
        $sql = "SELECT no, title FROM mlangprintauto_transactioncate 
                WHERE BigNo = 0 AND TreeNo = 0 AND Ttable = ? 
                ORDER BY no ASC";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return [];
        
        mysqli_stmt_bind_param($stmt, 's', $table);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        return $options;
    }
    
    private function getLevel2Options(string $table, int $parentId): array
    {
        $sql = "SELECT no, title FROM mlangprintauto_transactioncate 
                WHERE TreeNo = ? AND Ttable = ? 
                ORDER BY no ASC";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return [];
        
        mysqli_stmt_bind_param($stmt, 'is', $parentId, $table);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        return $options;
    }
    
    private function getLevel3Options(string $table, int $parentId): array
    {
        $sql = "SELECT no, title FROM mlangprintauto_transactioncate 
                WHERE BigNo = ? AND Ttable = ? 
                ORDER BY no ASC";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return [];
        
        mysqli_stmt_bind_param($stmt, 'is', $parentId, $table);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        return $options;
    }
    
    private function containsPaperSelection(string $content): bool
    {
        $keywords = ['누브', '라레', '랑데뷰', '머쉬멜로우', '스타드림', '스코틀랜드', '빌리지'];
        foreach ($keywords as $keyword) {
            if (mb_strpos($content, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function getPaperImages(): array
    {
        $paperImageMap = [
            '누브' => '누브.jpg',
            '라레' => '라레.jpg',
            '랑데뷰' => '랑데뷰.jpg',
            '머쉬멜로우' => '머쉬멜로우.jpg',
            '빌리지' => '빌리지.jpg',
            '스코틀랜드' => '스코틀랜드.jpg',
            '스타골드' => '스타골드.jpg',
            '스타드림' => '스타드림.jpg',
            '울트라화이트' => '울트라화이트.jpg',
            '유포지' => '유포지.jpg',
            '카멜레온' => '카멜레온.jpg',
            '컨셉' => '컨셉.jpg',
            '키칼라' => '키칼라.jpg',
            '키칼라아이스골드' => '키칼라아이스골드.jpg',
            '탄트' => '탄트.jpg',
            '팝셋' => '팝셋.jpg',
            '화인스노우' => '화인스노우.jpg',
        ];
        
        $basePath = '/ImgFolder/paper_texture/명함재질/';
        $images = [];
        
        foreach ($paperImageMap as $name => $file) {
            $images[] = [
                'name' => $name,
                'url' => $basePath . rawurlencode($file),
            ];
        }
        
        return $images;
    }
    
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) || $this->db !== null;
    }
    
    /**
     * Gemini 2.5 Flash TTS로 자연스러운 한국어 음성 생성
     * @return array ['success' => bool, 'audio' => base64 WAV string, 'error' => string]
     */
    public function textToSpeech(string $text): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'API 키가 설정되지 않았습니다.'];
        }
        
        if (empty(trim($text))) {
            return ['success' => false, 'error' => '텍스트가 비어있습니다.'];
        }
        
        // Gemini TTS 프롬프트: 빠르고 친절한 한국어 여성 상담원 톤
        $prompt = "다음 텍스트를 빠른 속도로, 밝고 활기찬 젊은 여성 상담원처럼 읽어주세요. 콜센터 상담원이 능숙하게 안내하듯 빠르지만 또렷하게: " . $text;
        
        $data = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'responseModalities' => ['AUDIO'],
                'speechConfig' => [
                    'voiceConfig' => [
                        'prebuiltVoiceConfig' => [
                            'voiceName' => 'Kore'  // 밝고 친근한 여성 음성
                        ]
                    ]
                ]
            ],
            'model' => 'gemini-2.5-flash-preview-tts',
        ];
        
        $url = $this->baseUrl . 'gemini-2.5-flash-preview-tts:generateContent?key=' . $this->apiKey;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if (!empty($curlError)) {
            return ['success' => false, 'error' => '네트워크 오류: ' . $curlError];
        }
        
        if ($httpCode !== 200) {
            error_log("Gemini TTS API error (HTTP {$httpCode}): " . substr($response, 0, 500));
            return ['success' => false, 'error' => "TTS API 오류 (HTTP {$httpCode})"];
        }
        
        $decoded = json_decode($response, true);
        $pcmBase64 = $decoded['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? '';
        
        if (empty($pcmBase64)) {
            return ['success' => false, 'error' => 'TTS 응답에 오디오 데이터가 없습니다.'];
        }
        
        // base64 PCM → WAV 변환 (24kHz, mono, 16-bit)
        $pcmData = base64_decode($pcmBase64);
        $wavData = $this->pcmToWav($pcmData, 24000, 1, 16);
        
        return [
            'success' => true,
            'audio' => base64_encode($wavData),
            'mimeType' => 'audio/wav',
        ];
    }
    
    /**
     * Raw PCM 데이터에 WAV 헤더 추가
     */
    private function pcmToWav(string $pcmData, int $sampleRate, int $channels, int $bitsPerSample): string
    {
        $dataSize = strlen($pcmData);
        $byteRate = $sampleRate * $channels * ($bitsPerSample / 8);
        $blockAlign = $channels * ($bitsPerSample / 8);
        $chunkSize = 36 + $dataSize;
        
        // WAV 헤더 (44 bytes)
        $header = pack('A4', 'RIFF');           // ChunkID
        $header .= pack('V', $chunkSize);       // ChunkSize
        $header .= pack('A4', 'WAVE');          // Format
        $header .= pack('A4', 'fmt ');          // Subchunk1ID
        $header .= pack('V', 16);              // Subchunk1Size (PCM)
        $header .= pack('v', 1);               // AudioFormat (PCM=1)
        $header .= pack('v', $channels);        // NumChannels
        $header .= pack('V', $sampleRate);      // SampleRate
        $header .= pack('V', (int)$byteRate);   // ByteRate
        $header .= pack('v', (int)$blockAlign);  // BlockAlign
        $header .= pack('v', $bitsPerSample);   // BitsPerSample
        $header .= pack('A4', 'data');          // Subchunk2ID
        $header .= pack('V', $dataSize);        // Subchunk2Size
        
        return $header . $pcmData;
    }
}
