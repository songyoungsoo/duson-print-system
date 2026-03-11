<?php
/**
 * QE_PriceCalculator — 견적엔진 독립 가격 계산 클래스
 * 경로: /includes/quote-engine/PriceCalculator.php
 *
 * 기존 PriceCalculationService.php 를 참조하지 않는 독립 모듈.
 * DB 연결($db)만 외부에서 주입받으며, 가격표 테이블은 SELECT-only.
 *
 * 지원 제품 9종:
 *   8종 — DB 테이블 조회 (mlangprintauto_*)
 *   1종 — 스티커: 수학 공식 (shop_d1~d4 요율)
 */

class QE_PriceCalculator
{
    /** @var mysqli */
    private $db;

    /**
     * 제품별 설정 (self-contained, 외부 의존 없음)
     *
     * table   — 가격표 테이블명 (null = 공식 기반)
     * unit    — 기본 단위
     * ttable  — mlangprintauto_transactioncate.Ttable 값
     * hasTree — TreeSelect 컬럼 사용 여부
     * hasPO   — POtype 컬럼 사용 여부
     * formula — true 면 테이블 조회 대신 공식 계산
     */
    private const PRODUCTS = [
        'namecard'        => ['name' => '명함',       'table' => 'mlangprintauto_namecard',        'unit' => '매', 'ttable' => 'NameCard',       'hasTree' => false, 'hasPO' => true],
        'inserted'        => ['name' => '전단지',     'table' => 'mlangprintauto_inserted',        'unit' => '연', 'ttable' => 'inserted',       'hasTree' => true,  'hasPO' => true],
        'envelope'        => ['name' => '봉투',       'table' => 'mlangprintauto_envelope',        'unit' => '매', 'ttable' => 'envelope',       'hasTree' => false, 'hasPO' => true],
        'littleprint'     => ['name' => '포스터',     'table' => 'mlangprintauto_littleprint',     'unit' => '매', 'ttable' => 'LittlePrint',    'hasTree' => true,  'hasPO' => true],
        'merchandisebond' => ['name' => '상품권',     'table' => 'mlangprintauto_merchandisebond', 'unit' => '매', 'ttable' => 'MerchandiseBond','hasTree' => false, 'hasPO' => true],
        'cadarok'         => ['name' => '카다록',     'table' => 'mlangprintauto_cadarok',         'unit' => '부', 'ttable' => 'Cadarok',        'hasTree' => true,  'hasPO' => true],
        'ncrflambeau'     => ['name' => 'NCR양식지',  'table' => 'mlangprintauto_ncrflambeau',     'unit' => '권', 'ttable' => 'NcrFlambeau',    'hasTree' => true,  'hasPO' => true],
        'msticker'        => ['name' => '자석스티커', 'table' => 'mlangprintauto_msticker',        'unit' => '매', 'ttable' => 'Msticker',       'hasTree' => false, 'hasPO' => false],
        'sticker'         => ['name' => '스티커',     'table' => null,                             'unit' => '매', 'ttable' => null,              'hasTree' => false, 'hasPO' => false, 'formula' => true],
    ];

    // ─── 생성자 ────────────────────────────────────────────────

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    // ═══════════════════════════════════════════════════════════
    // Public API
    // ═══════════════════════════════════════════════════════════

    /**
     * UI 용 제품 목록 반환
     * @return array [ ['code'=>'namecard','name'=>'명함','unit'=>'매'], ... ]
     */
    public function getProductList(): array
    {
        $list = [];
        foreach (self::PRODUCTS as $code => $cfg) {
            $list[] = [
                'code' => $code,
                'name' => $cfg['name'],
                'unit' => $cfg['unit'],
                'hasTree' => $cfg['hasTree'],
                'hasPO'   => $cfg['hasPO'],
                'isFormula' => !empty($cfg['formula']),
            ];
        }
        return $list;
    }

    /**
     * 캐스케이딩 드롭다운 옵션 조회
     *
     * @param string $product  제품코드 (inserted, namecard …)
     * @param int    $parentId 부모 ID (0 = 최상위)
     * @param string $lookup   부모 컬럼명 ('BigNo' | 'no')
     * @return array [ ['no'=>1,'title'=>'스노우지250g'], ... ]
     */
    public function getOptions(string $product, int $parentId = 0, string $lookup = 'BigNo'): array
    {
        if (!isset(self::PRODUCTS[$product]) || empty(self::PRODUCTS[$product]['ttable'])) {
            return [];
        }

        $ttable = self::PRODUCTS[$product]['ttable'];

        // lookup 컬럼 화이트리스트 (SQL injection 방지)
        $allowedLookups = ['BigNo', 'no'];
        if (!in_array($lookup, $allowedLookups, true)) {
            $lookup = 'BigNo';
        }

        $sql = "SELECT no, title FROM mlangprintauto_transactioncate
                WHERE Ttable = ? AND {$lookup} = ?
                ORDER BY TreeNo, no";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return [];

        mysqli_stmt_bind_param($stmt, 'si', $ttable, $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = [
                'no'    => (int)$row['no'],
                'title' => $row['title'],
            ];
        }

        mysqli_stmt_close($stmt);
        return $options;
    }

    /**
     * 가격표에서 수량 옵션 조회
     *
     * @param string $product 제품코드
     * @param array  $filters ['style'=>N, 'Section'=>N, 'TreeSelect'=>N, 'POtype'=>N]
     * @return array [ ['quantity'=>0.5,'label'=>'0.5'], ... ]
     */
    public function getQuantities(string $product, array $filters): array
    {
        if (!isset(self::PRODUCTS[$product]) || empty(self::PRODUCTS[$product]['table'])) {
            return [];
        }

        $table = self::PRODUCTS[$product]['table'];
        $cfg   = self::PRODUCTS[$product];

        // 동적 WHERE 조건
        $conditions = [];
        $types = '';
        $values = [];

        if (!empty($filters['style'])) {
            $conditions[] = 'style = ?';
            $types .= 'i';
            $values[] = (int)$filters['style'];
        }
        if (!empty($filters['Section'])) {
            $conditions[] = 'Section = ?';
            $types .= 'i';
            $values[] = (int)$filters['Section'];
        }
        if ($cfg['hasTree'] && !empty($filters['TreeSelect'])) {
            $conditions[] = 'TreeSelect = ?';
            $types .= 'i';
            $values[] = (int)$filters['TreeSelect'];
        }
        if ($cfg['hasPO'] && !empty($filters['POtype'])) {
            $conditions[] = 'POtype = ?';
            $types .= 's';
            $values[] = $filters['POtype'];
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

        $sql = "SELECT DISTINCT quantity FROM {$table} {$where} ORDER BY quantity ASC";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return [];

        if ($types !== '' && count($values) > 0) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $quantities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $qty = floatval($row['quantity']);
            $label = (floor($qty) == $qty) ? number_format($qty) : rtrim(rtrim(number_format($qty, 2), '0'), '.');
            $quantities[] = [
                'quantity' => $qty,
                'label'    => $label,
            ];
        }

        mysqli_stmt_close($stmt);
        return $quantities;
    }

    /**
     * 가격 계산 (통합 진입점)
     *
     * 8종 DB 제품: style, Section, quantity 기반 테이블 조회
     * 스티커(sticker): jong, garo, sero, mesu, uhyung, domusong 기반 공식 계산
     *
     * @param string $product 제품코드
     * @param array  $params  계산 파라미터
     * @return array 통일 결과 형식 (success, supply_price, vat, total, product_name, …)
     */
    public function calculate(string $product, array $params): array
    {
        if (!isset(self::PRODUCTS[$product])) {
            return ['success' => false, 'error' => "지원하지 않는 품목입니다: {$product}"];
        }

        $cfg = self::PRODUCTS[$product];

        // 스티커 — 공식 계산
        if (!empty($cfg['formula'])) {
            return $this->calculateStickerPrice($params);
        }

        // 나머지 8종 — 테이블 조회
        return $this->lookupTablePrice($product, $params, $cfg);
    }

    /**
     * 프리미엄 옵션 조회
     *
     * @param string $product 제품코드
     * @return array  옵션 그룹 [ 'option_name' => [ variants... ] ]
     */
    public function getPremiumOptions(string $product): array
    {
        $sql = "SELECT o.id AS option_id, o.option_name,
                       v.id AS variant_id, v.variant_name, v.pricing_config
                FROM premium_options o
                JOIN premium_option_variants v ON o.id = v.option_id
                WHERE o.product_type = ? AND o.is_active = 1 AND v.is_active = 1
                ORDER BY o.sort_order, v.display_order";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return [];

        mysqli_stmt_bind_param($stmt, 's', $product);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $grouped = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $optName = $row['option_name'];
            if (!isset($grouped[$optName])) {
                $grouped[$optName] = [
                    'option_id'   => (int)$row['option_id'],
                    'option_name' => $optName,
                    'variants'    => [],
                ];
            }
            $grouped[$optName]['variants'][] = [
                'variant_id'     => (int)$row['variant_id'],
                'variant_name'   => $row['variant_name'],
                'pricing_config' => json_decode($row['pricing_config'] ?? '{}', true),
            ];
        }

        mysqli_stmt_close($stmt);
        return array_values($grouped);
    }

    // ═══════════════════════════════════════════════════════════
    // Private — 테이블 조회 (8종)
    // ═══════════════════════════════════════════════════════════

    /**
     * DB 가격표에서 money / DesignMoney 조회
     */
    private function lookupTablePrice(string $product, array $params, array $cfg): array
    {
        $table = $cfg['table'];

        // --- 필수 파라미터 검증 ---
        if (empty($params['style'])) {
            return ['success' => false, 'error' => 'style(카테고리)을 선택하세요'];
        }
        if (empty($params['Section'])) {
            return ['success' => false, 'error' => 'Section(하위 카테고리)을 선택하세요'];
        }
        if (!isset($params['quantity']) || $params['quantity'] === '') {
            return ['success' => false, 'error' => '수량을 선택하세요'];
        }

        // --- WHERE 절 동적 구성 ---
        $conditions = [];
        $types = '';
        $values = [];

        // style (항상)
        $conditions[] = 'style = ?';
        $types .= 'i';
        $values[] = (int)$params['style'];

        // Section (항상)
        $conditions[] = 'Section = ?';
        $types .= 'i';
        $values[] = (int)$params['Section'];

        // TreeSelect (hasTree 인 제품만)
        if ($cfg['hasTree'] && !empty($params['TreeSelect'])) {
            $conditions[] = 'TreeSelect = ?';
            $types .= 'i';
            $values[] = (int)$params['TreeSelect'];
        }

        // quantity (float 비교)
        $qtyFloat = floatval($params['quantity']);
        $conditions[] = 'quantity = ?';
        $types .= 'd';
        $values[] = $qtyFloat;

        // POtype (선택적)
        if ($cfg['hasPO'] && !empty($params['POtype'])) {
            $conditions[] = 'POtype = ?';
            $types .= 's';
            $values[] = $params['POtype'];
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT money, DesignMoney FROM {$table} WHERE {$where} LIMIT 1";

        // --- bind_param 3단계 검증 ---
        $placeholderCount = substr_count($sql, '?');
        $typeCount = strlen($types);
        $valueCount = count($values);
        if ($placeholderCount !== $typeCount || $typeCount !== $valueCount) {
            return ['success' => false, 'error' => 'bind_param 불일치 내부오류'];
        }

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            return ['success' => false, 'error' => 'DB 쿼리 준비 실패'];
        }

        mysqli_stmt_bind_param($stmt, $types, ...$values);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row) {
            // POtype 제외 재시도
            if ($cfg['hasPO'] && !empty($params['POtype'])) {
                $paramsRetry = $params;
                unset($paramsRetry['POtype']);
                $cfgRetry = $cfg;
                $cfgRetry['hasPO'] = false;
                return $this->lookupTablePrice($product, $paramsRetry, $cfgRetry);
            }
            return ['success' => false, 'error' => '해당 조건의 가격 데이터를 찾을 수 없습니다'];
        }

        $money = (int)($row['money'] ?? 0);
        $designMoney = (int)($row['DesignMoney'] ?? 0);
        $designType = $params['ordertype'] ?? 'print';
        $supplyPrice = ($designType === 'design') ? $designMoney : $money;

        if ($supplyPrice <= 0) {
            return ['success' => false, 'error' => '가격 데이터가 0입니다. 관리자에게 문의하세요'];
        }

        $vat = (int)round($supplyPrice * 0.1);
        $total = $supplyPrice + $vat;
        $quantity = floatval($params['quantity']);
        $unitPrice = ($quantity > 0) ? (int)round($supplyPrice / $quantity) : 0;

        // 규격 텍스트 조합
        $spec = $this->buildTableSpec($product, $params, $cfg);

        return [
            'success'       => true,
            'supply_price'  => $supplyPrice,
            'design_price'  => $designMoney,
            'vat'           => $vat,
            'total'         => $total,
            'product_name'  => $cfg['name'],
            'specification' => $spec,
            'unit'          => $cfg['unit'],
            'quantity'      => $quantity,
            'unit_price'    => $unitPrice,
            'source_data'   => $params,
        ];
    }

    /**
     * 테이블 조회 제품의 규격 텍스트 생성
     * 카테고리 ID → 카테고리명 변환 포함
     */
    private function buildTableSpec(string $product, array $params, array $cfg): string
    {
        $parts = [];

        // style 카테고리명
        if (!empty($params['style'])) {
            $name = $this->getCateName((int)$params['style']);
            if ($name) $parts[] = $name;
        }

        // Section 카테고리명
        if (!empty($params['Section'])) {
            $name = $this->getCateName((int)$params['Section']);
            if ($name) $parts[] = $name;
        }

        // TreeSelect 카테고리명
        if ($cfg['hasTree'] && !empty($params['TreeSelect'])) {
            $name = $this->getCateName((int)$params['TreeSelect']);
            if ($name) $parts[] = $name;
        }

        // POtype
        if ($cfg['hasPO'] && !empty($params['POtype'])) {
            $parts[] = $params['POtype'];
        }

        // 수량
        $qty = floatval($params['quantity'] ?? 0);
        $qtyLabel = (floor($qty) == $qty) ? number_format($qty) : rtrim(rtrim(number_format($qty, 2), '0'), '.');
        $parts[] = $qtyLabel . $cfg['unit'];

        // 인쇄방식
        $ordertype = $params['ordertype'] ?? '';
        if ($ordertype === 'design') $parts[] = '디자인+인쇄';
        elseif ($ordertype === 'print') $parts[] = '인쇄만';

        return implode(' / ', $parts);
    }

    /**
     * mlangprintauto_transactioncate 에서 no → title 조회
     */
    private function getCateName(int $no): string
    {
        $stmt = mysqli_prepare($this->db, "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? LIMIT 1");
        if (!$stmt) return '';

        mysqli_stmt_bind_param($stmt, 'i', $no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $row['title'] ?? '';
    }

    // ═══════════════════════════════════════════════════════════
    // Private — 스티커 공식 계산
    // ═══════════════════════════════════════════════════════════

    /**
     * 스티커 가격 계산 (calculate_price_ajax.php 로직 1:1 복제)
     *
     * 입력 6개: jong(재질), garo(가로mm), sero(세로mm), mesu(수량),
     *           uhyung(디자인비), domusong(도무송 모양)
     */
    private function calculateStickerPrice(array $params): array
    {
        $jong     = trim($params['jong'] ?? '');
        $garo     = (int)($params['garo'] ?? 0);
        $sero     = (int)($params['sero'] ?? 0);
        $mesu     = (int)($params['mesu'] ?? 0);
        $uhyung   = (int)($params['uhyung'] ?? 0);
        $domusong = trim($params['domusong'] ?? '');

        // 검증
        if (empty($jong))  return ['success' => false, 'error' => '재질을 선택하세요'];
        if ($garo <= 0)    return ['success' => false, 'error' => '가로사이즈를 입력하세요'];
        if ($sero <= 0)    return ['success' => false, 'error' => '세로사이즈를 입력하세요'];
        if ($mesu <= 0)    return ['success' => false, 'error' => '수량을 입력하세요'];
        if ($garo > 590)   return ['success' => false, 'error' => '가로 590mm 이하만 가능'];
        if ($sero > 590)   return ['success' => false, 'error' => '세로 590mm 이하만 가능'];

        // 재질코드 3자리 (jil, jka, jsp, cka)
        $j1 = substr($jong, 0, 3);

        // 도무송 비용 숫자 (앞 5자리)
        $d1 = (int)substr($domusong, 0, 5);

        // 기본값
        $yoyo = 0.15;  // 요율
        $mg   = 7000;  // 관리비
        $ts   = 9;     // 톰슨비

        // ── 재질별 DB 요율 조회 (shop_d1~d4) ──
        $tableMap = [
            'jil' => 'shop_d1',
            'jka' => 'shop_d2',
            'jsp' => 'shop_d3',
            'cka' => 'shop_d4',
        ];

        if (isset($tableMap[$j1])) {
            $r = mysqli_query($this->db, "SELECT * FROM {$tableMap[$j1]} LIMIT 1");
            if ($r && ($data = mysqli_fetch_array($r))) {
                if ($mesu <= 1000)      { $yoyo = $data[0] ?? 0.15; $mg = 7000; }
                elseif ($mesu <= 4000)  { $yoyo = $data[1] ?? 0.14; $mg = 6500; }
                elseif ($mesu <= 5000)  { $yoyo = $data[2] ?? 0.13; $mg = 6500; }
                elseif ($mesu <= 9000)  { $yoyo = $data[3] ?? 0.12; $mg = 6000; }
                elseif ($mesu <= 10000) { $yoyo = $data[4] ?? 0.11; $mg = 5500; }
                elseif ($mesu <= 50000) { $yoyo = $data[5] ?? 0.10; $mg = 5000; }
                else                    { $yoyo = $data[6] ?? 0.09; $mg = 5000; }
            }
        }

        // 특수 재질 톰슨비
        if (in_array($j1, ['jsp', 'jka', 'cka'])) {
            $ts = 14;
        }

        // 도무송칼 크기 = 가로·세로 중 큰 값
        $d2 = max($garo, $sero);

        // 사이즈별 마진비율 (18,000mm² 기준)
        $gase = ($garo * $sero <= 18000) ? 1 : 1.25;

        // ── 도무송 비용 ──
        $d1_cost = 0;
        if ($d1 > 0) {
            if ($mesu == 500) {
                $d1_cost = (($d1 + ($d2 * 20)) * 900 / 1000) + (900 * $ts);
            } elseif ($mesu == 1000) {
                $d1_cost = (($d1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * $ts);
            } elseif ($mesu > 1000) {
                $d1_cost = (($d1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * ($ts / 9));
            }
        }

        // ── 특수용지 비용 ──
        $special = 0;
        if ($j1 == 'jsp') {
            $special = ($mesu == 500) ? (10000 * ($mesu + 400) / 1000) : (10000 * $mesu / 1000);
        } elseif ($j1 == 'jka') {
            $special = ($mesu == 500) ? (4000 * ($mesu + 400) / 1000) : (10000 * $mesu / 1000);
        } elseif ($j1 == 'cka') {
            $special = ($mesu == 500) ? (4000 * ($mesu + 400) / 1000) : (10000 * $mesu / 1000);
        }

        // ── 최종 가격 ──
        if ($mesu == 500) {
            $s_price  = (($garo + 4) * ($sero + 4) * ($mesu + 400)) * $yoyo + $special + $d1_cost;
            $st_price = round($s_price * $gase, -3) + $uhyung + ($mg * ($mesu + 400) / 1000);
        } else {
            $s_price  = (($garo + 4) * ($sero + 4) * $mesu) * $yoyo + $special + $d1_cost;
            $st_price = round($s_price * $gase, -3) + $uhyung + ($mg * $mesu / 1000);
        }

        $st_price = (int)$st_price;
        $vat      = (int)round($st_price * 0.1);
        $total    = (int)round($st_price * 1.1);
        $unitPrice = ($mesu > 0) ? (int)round($st_price / $mesu) : 0;

        return [
            'success'       => true,
            'supply_price'  => $st_price,
            'vat'           => $vat,
            'total'         => $total,
            'product_name'  => '스티커',
            'specification' => $this->formatStickerSpec($jong, $garo, $sero, $mesu, $domusong),
            'unit'          => '매',
            'quantity'      => $mesu,
            'unit_price'    => $unitPrice,
            'source_data'   => $params,
        ];
    }

    /**
     * 스티커 규격 텍스트 생성
     */
    private function formatStickerSpec(string $jong, int $garo, int $sero, int $mesu, string $domusong): string
    {
        $parts = [];

        // 재질명 (코드 3자 제거)
        if (strlen($jong) > 4) {
            $parts[] = '재질: ' . substr($jong, 4);
        }

        // 크기
        if ($garo > 0 && $sero > 0) {
            $parts[] = "크기: {$garo}×{$sero}mm";
        }

        // 도무송 모양 (숫자코드 제거)
        if (!empty($domusong)) {
            $shape = trim(substr($domusong, 6));
            if ($shape) $parts[] = "모양: {$shape}";
        }

        return implode(' / ', $parts);
    }
}
