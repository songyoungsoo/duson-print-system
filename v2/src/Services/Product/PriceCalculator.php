<?php
declare(strict_types=1);

namespace App\Services\Product;

use App\Core\Database;

class PriceCalculator
{
    private array $products;
    private $v1Service = null;
    private $legacyDb = null;
    
    public function __construct()
    {
        $this->products = require V2_ROOT . '/config/products.php';
        $this->initV1Service();
    }
    
    private function initV1Service(): void
    {
        global $db;
        
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(V2_ROOT);
        $v1ServicePath = $docRoot . '/includes/PriceCalculationService.php';
        $v1DbPath = $docRoot . '/db.php';
        
        if (!file_exists($v1DbPath)) {
            $v1DbPath = dirname(V2_ROOT) . '/db.php';
            $v1ServicePath = dirname(V2_ROOT) . '/includes/PriceCalculationService.php';
        }
        
        if (file_exists($v1ServicePath) && file_exists($v1DbPath)) {
            require_once $v1DbPath;
            require_once $v1ServicePath;
            
            if ($db) {
                $this->legacyDb = $db;
                $this->v1Service = new \PriceCalculationService($db);
            }
        }
    }
    
    public function calculate(string $type, array $params): array
    {
        if ($this->v1Service && $this->v1Service->hasProductConfig($type)) {
            return $this->calculateWithV1Service($type, $params);
        }
        
        return $this->calculateFallback($type, $params);
    }
    
    private function calculateWithV1Service(string $type, array $params): array
    {
        $v1Params = $this->mapToV1Params($type, $params);
        $result = $this->v1Service->calculate($type, $v1Params);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? '가격 계산 실패',
            ];
        }
        
        $data = $result['data'];
        
        // sticker_new uses st_price/st_price_vat, others use Price/Total_PriceForm
        $basePrice = $this->parsePrice(
            $data['base_price'] ?? $data['st_price'] ?? $data['PriceForm'] ?? $data['Price'] ?? 0
        );
        $designPrice = $this->parsePrice($data['design_price'] ?? $data['DS_Price'] ?? $data['DS_PriceForm'] ?? 0);
        
        $product = $this->products[$type] ?? [];
        $unitCode = $product['unit_code'] ?? 'E';
        $unitName = $product['unit_name'] ?? '개';
        
        // Support multiple quantity parameter names
        $quantityRaw = $params['quantity'] ?? $params['MY_amount'] ?? $params['mesu'] ?? 0;
        $quantity = is_numeric($quantityRaw) ? (float) $quantityRaw : 0;
        
        // 프리미엄 옵션 가격 계산 (V2 config 기반)
        $premiumResult = $this->calculatePremiumOptions($type, $params, (int) $quantity);
        $optionsPrice = $premiumResult['total'];
        $optionsDetails = $premiumResult['details'];
        
        // 총 공급가 계산 (기본가 + 디자인비 + 프리미엄 옵션)
        $supplyPrice = $basePrice + $designPrice + $optionsPrice;
        $vatPrice = (int) round($supplyPrice * 0.1);
        $totalPrice = $supplyPrice + $vatPrice;
        
        // Build quantity display based on product type
        $quantityDisplay = '';
        if ($unitCode === 'R' && isset($data['MY_amountRight'])) {
            // 전단지: "2,000장 (0.5연)" format
            $sheets = $data['MY_amountRight'];
            $reams = $data['QuantityForm'] ?? $quantityRaw;
            $quantityDisplay = "{$sheets} ({$reams}연)";
        } elseif (!empty($data['MY_amountRight'])) {
            $quantityDisplay = $data['MY_amountRight'];
        } elseif (!empty($data['QuantityForm'])) {
            $quantityDisplay = $data['QuantityForm'] . $unitName;
        } elseif ($quantity > 0) {
            $quantityDisplay = $this->formatQuantity((int) $quantity, $unitCode, $unitName);
        }
        
        return [
            'success' => true,
            'base_price' => $basePrice,
            'design_price' => $designPrice,
            'options_price' => $optionsPrice,
            'options_details' => $optionsDetails,
            'supply_price' => $supplyPrice,
            'vat_price' => $vatPrice,
            'total_price' => $totalPrice,
            'quantity' => $quantity,
            'quantity_display' => $quantityDisplay,
            'spec' => [
                'style' => $data['StyleForm'] ?? '',
                'section' => $data['SectionForm'] ?? '',
                'design' => $data['DesignForm'] ?? '',
            ],
            'formatted' => [
                'base' => number_format($basePrice) . '원',
                'design' => number_format($designPrice) . '원',
                'options' => number_format($optionsPrice) . '원',
                'supply' => number_format($supplyPrice) . '원',
                'vat' => number_format($vatPrice) . '원',
                'total' => number_format($totalPrice) . '원',
            ],
            'raw' => $data,
        ];
    }
    
    /**
     * 프리미엄 옵션 가격 계산
     * V1 로직: 기준수량 이하 → 기본가, 초과 → 기본가 + (초과수량 × 단가)
     */
    private function calculatePremiumOptions(string $type, array $params, int $quantity): array
    {
        $product = $this->products[$type] ?? [];
        $premiumConfig = $product['premium_options'] ?? [];
        
        if (empty($premiumConfig)) {
            return ['total' => 0, 'details' => []];
        }
        
        $total = 0;
        $details = [];
        
        // params에서 premium_options JSON 파싱
        $selectedOptions = [];
        if (isset($params['premium_options'])) {
            if (is_string($params['premium_options'])) {
                $selectedOptions = json_decode($params['premium_options'], true) ?? [];
            } elseif (is_array($params['premium_options'])) {
                $selectedOptions = $params['premium_options'];
            }
        }
        
        // 개별 옵션 파라미터도 확인 (foil_enabled, numbering_enabled 등)
        foreach ($premiumConfig as $key => $config) {
            $optionName = $config['name'] ?? $key;
            $enabledKey = $optionName . '_enabled';
            $typeKey = $optionName . '_type';
            
            $isEnabled = false;
            $selectedType = null;
            
            // 1. JSON premium_options에서 확인
            if (isset($selectedOptions[$optionName])) {
                $isEnabled = !empty($selectedOptions[$optionName]['enabled']);
                $selectedType = $selectedOptions[$optionName]['type'] ?? null;
            }
            
            // 2. 개별 파라미터에서 확인 (우선순위 높음)
            if (isset($params[$enabledKey])) {
                $isEnabled = filter_var($params[$enabledKey], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($params[$typeKey]) && !empty($params[$typeKey])) {
                $selectedType = $params[$typeKey];
            }
            
            if (!$isEnabled) {
                continue;
            }
            
            $price = $this->calculateSingleOptionPrice($config, $quantity, $selectedType);
            
            if ($price > 0) {
                $total += $price;
                $details[] = [
                    'name' => $config['label'] ?? $optionName,
                    'type' => $selectedType,
                    'price' => $price,
                ];
            }
        }
        
        return ['total' => $total, 'details' => $details];
    }
    
    /**
     * 개별 옵션 가격 계산
     */
    private function calculateSingleOptionPrice(array $config, int $quantity, ?string $selectedType = null): int
    {
        // 고정 가격 옵션
        if (isset($config['fixed_price'])) {
            return (int) $config['fixed_price'];
        }
        
        // 기본 가격 정보
        $baseQty = $config['base_qty'] ?? 500;
        $basePrice = $config['base_price'] ?? 0;
        $unitPrice = $config['unit_price'] ?? 0;
        
        // select 타입인 경우 선택된 옵션의 가격 정보 확인
        if ($config['type'] === 'select' && $selectedType && isset($config['options'])) {
            foreach ($config['options'] as $opt) {
                if ($opt['value'] === $selectedType) {
                    // 옵션별 오버라이드 가격이 있으면 사용
                    if (isset($opt['base_price'])) {
                        $basePrice = $opt['base_price'];
                    }
                    if (isset($opt['unit_price'])) {
                        $unitPrice = $opt['unit_price'];
                    }
                    // extra_per_1000 처리 (1000매당 추가 비용)
                    if (isset($opt['extra_per_1000']) && $opt['extra_per_1000'] > 0) {
                        $extraSets = max(0, ceil(($quantity - $baseQty) / 1000));
                        $basePrice += $extraSets * $opt['extra_per_1000'];
                    }
                    break;
                }
            }
        }
        
        // 가격 계산: 기준수량 이하 → 기본가, 초과 → 기본가 + (초과수량 × 단가)
        if ($quantity <= $baseQty) {
            return (int) $basePrice;
        } else {
            $additionalQty = $quantity - $baseQty;
            return (int) ($basePrice + ($additionalQty * $unitPrice));
        }
    }
    
    private function mapToV1Params(string $type, array $params): array
    {
        $v1Params = [];
        
        $paramMap = [
            'style_id' => ['MY_type', 'style', 'category'],
            'section_id' => ['PN_type', 'Section', 'size'],
            'tree_id' => ['MY_Fsd', 'TreeSelect', 'paper'],
            'quantity' => ['MY_amount', 'quantity', 'mesu'],
            'po_type' => ['POtype', 'potype', 'sides'],
            'design_type' => ['ordertype', 'design'],
            'material' => ['jong', 'material'],
            'width' => ['garo', 'width'],
            'height' => ['sero', 'height'],
            'frame_cost' => ['uhyung', 'frame'],
            'die_cut' => ['domusong', 'diecut'],
        ];
        
        foreach ($paramMap as $standard => $legacyKeys) {
            foreach ($legacyKeys as $key) {
                if (isset($params[$key]) && $params[$key] !== '') {
                    $v1Params[$standard] = $params[$key];
                    break;
                }
            }
        }
        
        // Map design_type values: 1=print (no design), 2=total (with design)
        if (isset($v1Params['design_type'])) {
            $designMap = [
                '1' => 'print',   // 직접 시안 보유 → 인쇄비만
                '2' => 'total',   // 디자인 의뢰 → 인쇄비 + 디자인비
                'print' => 'print',
                'design' => 'design',
                'total' => 'total',
            ];
            $v1Params['design_type'] = $designMap[$v1Params['design_type']] ?? 'total';
        }
        
        // Add remaining params, but SKIP legacy keys that were already mapped
        // to prevent V1's normalizeParams from overwriting our mapped values
        $mappedLegacyKeys = [];
        foreach ($paramMap as $legacyKeys) {
            foreach ($legacyKeys as $key) {
                $mappedLegacyKeys[$key] = true;
            }
        }
        
        foreach ($params as $key => $value) {
            if (!isset($v1Params[$key]) && !isset($mappedLegacyKeys[$key]) && $value !== '') {
                $v1Params[$key] = $value;
            }
        }
        
        return $v1Params;
    }
    
    private function parsePrice($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }
    
    private function calculateFallback(string $type, array $params): array
    {
        $quantity = (int) ($params['quantity'] ?? $params['MY_amount'] ?? 0);
        
        if ($quantity <= 0) {
            return [
                'success' => false,
                'error' => '수량을 선택해주세요.',
            ];
        }
        
        $basePrices = [
            'sticker_new' => 50,
            'inserted' => 30,
            'namecard' => 80,
            'envelope' => 100,
            'cadarok' => 200,
            'littleprint' => 500,
            'merchandisebond' => 150,
            'ncrflambeau' => 300,
            'msticker' => 70,
        ];
        
        $unitPrice = $basePrices[$type] ?? 100;
        
        $discount = 1.0;
        if ($quantity >= 10000) $discount = 0.7;
        elseif ($quantity >= 5000) $discount = 0.8;
        elseif ($quantity >= 1000) $discount = 0.9;
        
        $basePrice = (int) round($unitPrice * $quantity * $discount);
        $totalPrice = (int) round($basePrice * 1.1);
        $vatPrice = $totalPrice - $basePrice;
        
        $product = $this->products[$type] ?? [];
        $unitCode = $product['unit_code'] ?? 'E';
        $unitName = $product['unit_name'] ?? '개';
        
        return [
            'success' => true,
            'base_price' => $basePrice,
            'design_price' => 0,
            'options_price' => 0,
            'supply_price' => $basePrice,
            'vat_price' => $vatPrice,
            'total_price' => $totalPrice,
            'quantity' => $quantity,
            'quantity_display' => $this->formatQuantity($quantity, $unitCode, $unitName),
            'formatted' => [
                'base' => number_format($basePrice) . '원',
                'design' => '0원',
                'options' => '0원',
                'supply' => number_format($basePrice) . '원',
                'vat' => number_format($vatPrice) . '원',
                'total' => number_format($totalPrice) . '원',
            ],
        ];
    }
    
    private function formatQuantity(int $quantity, string $unitCode, string $unitName): string
    {
        if ($unitCode === 'R') {
            $ream = $quantity / 500;
            if ($ream >= 1 && $quantity % 500 === 0) {
                return number_format($ream) . '연 (' . number_format($quantity) . '매)';
            }
        }
        
        return number_format($quantity) . $unitName;
    }
    
    public function getProductConfig(string $type): array
    {
        return $this->products[$type] ?? [];
    }
    
    public function getDropdownOptions(string $type, string $level, array $parentValues = []): array
    {
        if (!$this->legacyDb) {
            return [];
        }
        
        $product = $this->products[$type] ?? null;
        if (!$product) {
            return [];
        }
        
        $uiType = $product['ui_type'] ?? 'dropdown_3level';
        
        if ($uiType === 'formula_input') {
            return $this->getFormulaOptions($type, $level);
        }
        
        return $this->getCategoryOptions($type, $level, $parentValues);
    }
    
    private function getFormulaOptions(string $type, string $level): array
    {
        if ($type !== 'sticker_new') {
            return [];
        }
        
        $product = $this->products[$type] ?? [];
        $uiConfig = $product['ui_config'] ?? [];
        
        if ($level === 'material') {
            $materials = $uiConfig['materials'] ?? [];
            $options = [];
            foreach ($materials as $mat) {
                $options[] = [
                    'id' => $mat['value'],
                    'title' => $mat['label'],
                    'value' => $mat['value'],
                ];
            }
            return $options;
        }
        
        return [];
    }
    
    private function getCategoryOptions(string $type, string $level, array $parentValues): array
    {
        $options = [];
        
        switch ($level) {
            case 'style':
            case 'level1':
                $options = $this->getLevel1Options($type);
                break;
                
            case 'TreeSelect':
            case 'level2':
                $parentId = $parentValues['style'] ?? $parentValues['level1'] ?? 0;
                $options = $this->getLevel2Options($type, (int) $parentId);
                break;
                
            case 'Section':
            case 'level3':
                $styleId = $parentValues['style'] ?? $parentValues['level1'] ?? 0;
                $treeId = $parentValues['TreeSelect'] ?? $parentValues['level2'] ?? 0;
                $options = $this->getLevel3Options($type, (int) $treeId, (int) $styleId);
                break;
                
            case 'quantity':
                $options = $this->getQuantityOptions($type, $parentValues);
                break;
        }
        
        return $options;
    }
    
    private function getLevel1Options(string $type): array
    {
        if (!$this->legacyDb) {
            return [];
        }
        
        $sql = "SELECT no, title FROM mlangprintauto_transactioncate 
                WHERE BigNo = 0 AND TreeNo = 0 AND Ttable = ? 
                ORDER BY no ASC";
        
        $stmt = mysqli_prepare($this->legacyDb, $sql);
        if (!$stmt) {
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, 's', $type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = [
                'id' => (int) $row['no'],
                'title' => $row['title'],
            ];
        }
        mysqli_stmt_close($stmt);
        
        return $options;
    }
    
    private function getLevel2Options(string $type, int $parentId): array
    {
        if ($parentId <= 0) {
            return [];
        }
        
        // littleprint: Level 2는 용지 선택 (BigNo=style)
        // 다른 제품: Level 2는 TreeNo=style
        $useBigNo = in_array($type, ['littleprint']);
        
        if ($useBigNo) {
            $sql = "SELECT no, title FROM mlangprintauto_transactioncate 
                    WHERE BigNo = ? AND Ttable = ? 
                    ORDER BY no ASC";
        } else {
            $sql = "SELECT no, title FROM mlangprintauto_transactioncate 
                    WHERE TreeNo = ? AND Ttable = ? 
                    ORDER BY no ASC";
        }
        
        $stmt = mysqli_prepare($this->legacyDb, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $parentId, $type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = [
                'id' => (int) $row['no'],
                'title' => $row['title'],
            ];
        }
        mysqli_stmt_close($stmt);
        
        return $options;
    }
    
    private function getLevel3Options(string $type, int $parentId, int $styleId = 0): array
    {
        if (!$this->legacyDb) {
            return [];
        }
        
        $lookupId = $styleId > 0 ? $styleId : $parentId;
        if ($lookupId <= 0) {
            return [];
        }
        
        // littleprint: Level 3는 규격 (TreeNo=style)
        // 다른 제품: Level 3는 BigNo=style
        $useTreeNo = in_array($type, ['littleprint']);
        
        if ($useTreeNo) {
            $sql = "SELECT no, title FROM mlangprintauto_transactioncate 
                    WHERE TreeNo = ? AND Ttable = ? 
                    ORDER BY no ASC";
        } else {
            $sql = "SELECT no, title FROM mlangprintauto_transactioncate 
                    WHERE BigNo = ? AND Ttable = ? 
                    ORDER BY no ASC";
        }
        
        $stmt = mysqli_prepare($this->legacyDb, $sql);
        if (!$stmt) {
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, 'is', $lookupId, $type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = [
                'id' => (int) $row['no'],
                'title' => $row['title'],
            ];
        }
        mysqli_stmt_close($stmt);
        
        return $options;
    }
    
    private function getQuantityOptions(string $type, array $parentValues): array
    {
        $styleId = $parentValues['style'] ?? $parentValues['level1'] ?? 0;
        $treeId = $parentValues['TreeSelect'] ?? $parentValues['level2'] ?? 0;
        $sectionId = $parentValues['Section'] ?? $parentValues['level3'] ?? 0;
        
        $tableName = "mlangprintauto_{$type}";
        
        $product = $this->products[$type] ?? [];
        $uiType = $product['ui_type'] ?? 'dropdown_3level';
        
        $conditions = [];
        $bindTypes = '';
        $bindValues = [];
        
        if ($styleId > 0) {
            $conditions[] = 'style = ?';
            $bindTypes .= 'i';
            $bindValues[] = (int) $styleId;
        }
        
        if ($uiType === 'dropdown_4level' && $treeId > 0) {
            $conditions[] = 'TreeSelect = ?';
            $bindTypes .= 'i';
            $bindValues[] = (int) $treeId;
        }
        
        if ($sectionId > 0) {
            $conditions[] = 'Section = ?';
            $bindTypes .= 'i';
            $bindValues[] = (int) $sectionId;
        }
        
        if (empty($conditions)) {
            return [];
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        // Check if table has quantityTwo column (only inserted/leaflet)
        $hasQuantityTwo = in_array($type, ['inserted', 'leaflet']);
        $selectCols = $hasQuantityTwo ? 'quantity, quantityTwo' : 'quantity';
        
        $sql = "SELECT DISTINCT {$selectCols} FROM {$tableName} 
                WHERE {$whereClause} 
                ORDER BY quantity ASC";
        
        $stmt = mysqli_prepare($this->legacyDb, $sql);
        if (!$stmt) {
            return [];
        }
        
        if (!empty($bindValues)) {
            mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindValues);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $qty = $row['quantity'];
            $qtyTwo = $row['quantityTwo'] ?? '';
            
            $displayText = !empty($qtyTwo) ? $qtyTwo : $this->formatQuantityOption($type, $qty);
            
            $options[] = [
                'id' => $qty,
                'value' => $qty,
                'title' => $displayText,
            ];
        }
        mysqli_stmt_close($stmt);
        
        return $options;
    }
    
    private function formatQuantityOption(string $type, $quantity): string
    {
        $product = $this->products[$type] ?? [];
        $unitCode = $product['unit_code'] ?? 'E';
        $unitName = $product['unit_name'] ?? '개';
        
        $qty = (float) $quantity;
        
        if ($unitCode === 'R') {
            if ($qty < 1) {
                $sheets = (int) ($qty * 500);
                return number_format($sheets) . '매';
            }
            $sheets = (int) ($qty * 500);
            return number_format($qty) . '연 (' . number_format($sheets) . '매)';
        }
        
        if (floor($qty) == $qty) {
            return number_format((int) $qty) . $unitName;
        }
        
        return rtrim(rtrim(number_format($qty, 2), '0'), '.') . $unitName;
    }
    
    public function getInitialDropdowns(string $type): array
    {
        $product = $this->products[$type] ?? null;
        if (!$product) {
            return [];
        }
        
        $uiType = $product['ui_type'] ?? 'dropdown_3level';
        
        if ($uiType === 'formula_input') {
            return [
                'material' => $this->getFormulaOptions($type, 'material'),
            ];
        }
        
        return [
            'style' => $this->getLevel1Options($type),
        ];
    }
}
