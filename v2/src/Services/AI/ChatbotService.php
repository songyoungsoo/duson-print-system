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
    
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
        $this->products = require V2_ROOT . '/config/products.php';
        $this->initDatabase();
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
    
    public function chat(string $message, array $history = []): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'API 키가 설정되지 않았습니다.'];
        }
        
        $dropdownData = $this->getDropdownStructure();
        $systemPrompt = $this->buildSystemPrompt($dropdownData);
        
        $response = $this->callApi($systemPrompt, $message, $history);
        
        if (isset($response['error'])) {
            return $response;
        }
        
        return $this->parseResponse($response);
    }
    
    private function getDropdownStructure(): string
    {
        if (!$this->db) {
            return "데이터베이스 연결 없음";
        }
        
        $data = [];
        
        $data[] = "【품목 선택】";
        $data[] = "1. 명함 - 명함종류 → 용지 → 수량 → 인쇄면 → 디자인";
        $data[] = "2. 전단지 - 인쇄도수 → 용지 → 규격 → 수량 → 디자인";
        $data[] = "3. 스티커 - 재질 → 크기(가로×세로) → 수량 → 도무송";
        $data[] = "4. 봉투 - 봉투종류 → 규격 → 수량 → 디자인";
        $data[] = "5. 카다록 - 종류 → 규격/페이지 → 수량 → 디자인";
        $data[] = "6. 포스터 - 종류 → 용지 → 규격 → 수량";
        $data[] = "7. 상품권 - 종류 → 옵션 → 수량 → 디자인";
        $data[] = "8. NCR양식지 - 매수 → 규격 → 인쇄도수 → 수량 → 디자인";
        $data[] = "9. 자석스티커 - 종류 → 규격 → 수량";
        $data[] = "";
        
        $data[] = $this->getNamecardDropdowns();
        $data[] = $this->getInsertedDropdowns();
        $data[] = $this->getStickerDropdowns();
        $data[] = $this->getEnvelopeDropdowns();
        $data[] = $this->getCadarokDropdowns();
        $data[] = $this->getLittleprintDropdowns();
        $data[] = $this->getMerchandisebondDropdowns();
        $data[] = $this->getNcrDropdowns();
        $data[] = $this->getMstickerDropdowns();
        
        return implode("\n", array_filter($data));
    }
    
    private function getNamecardDropdowns(): string
    {
        $lines = ["【명함】"];
        
        $styles = $this->getLevel1Options('namecard');
        $lines[] = "1단계 - 명함종류: " . implode(', ', array_column($styles, 'title'));
        
        foreach ($styles as $style) {
            $sections = $this->getLevel3Options('namecard', (int)$style['no']);
            if (!empty($sections)) {
                $lines[] = "  └ {$style['title']} 선택시 용지: " . implode(', ', array_slice(array_column($sections, 'title'), 0, 8));
            }
        }
        
        $lines[] = "3단계 - 수량: 200, 500, 1000, 2000, 3000, 4000, 5000매 등";
        $lines[] = "4단계 - 인쇄면: 단면, 양면";
        $lines[] = "5단계 - 디자인: ";
        $lines[] = "  - 디자인 있음 (시안 보유): 0원";
        $lines[] = "  - 디자인 의뢰 (새로 제작): 단면 5,000원 / 양면 10,000원";
        
        $lines[] = "";
        $lines[] = $this->getNamecardPriceTable();
        
        return implode("\n", $lines);
    }
    
    private function getNamecardPriceTable(): string
    {
        $sql = "SELECT n.style, n.Section, n.quantity, n.money, n.DesignMoney, n.POtype,
                       s.title as style_name, sec.title as section_name
                FROM mlangprintauto_namecard n
                LEFT JOIN mlangprintauto_transactioncate s ON n.style = s.no
                LEFT JOIN mlangprintauto_transactioncate sec ON n.Section = sec.no
                ORDER BY n.style, n.Section, n.POtype, n.quantity
                LIMIT 500";
        
        $result = mysqli_query($this->db, $sql);
        if (!$result) return "";
        
        $prices = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $style = $row['style_name'] ?: $row['style'];
            $section = $row['section_name'] ?: $row['Section'];
            $poType = ($row['POtype'] == '2') ? '양면' : '단면';
            $qty = (int)$row['quantity'];
            $printPrice = (int)$row['money'];
            $designPrice = (int)$row['DesignMoney'];
            
            $priceNoDesign = (int)round($printPrice * 1.1);
            $priceWithDesign = (int)round(($printPrice + $designPrice) * 1.1);
            
            $key = "{$style}|{$section}|{$poType}";
            if (!isset($prices[$key])) $prices[$key] = [];
            $prices[$key][$qty] = [
                'no_design' => $priceNoDesign,
                'with_design' => $priceWithDesign,
                'design_fee' => $designPrice
            ];
        }
        
        $lines = ["[명함 가격표] (VAT포함)"];
        $lines[] = "※ 디자인 있음 = 시안 보유시 가격 / 디자인 의뢰 = 새로 제작시 가격";
        foreach ($prices as $key => $qtyPrices) {
            list($style, $section, $side) = explode('|', $key);
            $pList = [];
            foreach ($qtyPrices as $q => $p) {
                $pList[] = "{$q}매: 디자인있음 " . number_format($p['no_design']) . "원 / 의뢰시 " . number_format($p['with_design']) . "원";
            }
            $lines[] = "- {$style}/{$section}/{$side}:";
            foreach ($pList as $pl) {
                $lines[] = "  " . $pl;
            }
        }
        
        return implode("\n", array_slice($lines, 0, 80));
    }
    
    private function getInsertedDropdowns(): string
    {
        $lines = ["【전단지】"];
        
        $styles = $this->getLevel1Options('inserted');
        $lines[] = "1단계 - 인쇄도수: " . implode(', ', array_column($styles, 'title'));
        
        foreach (array_slice($styles, 0, 2) as $style) {
            $trees = $this->getLevel2Options('inserted', (int)$style['no']);
            if (!empty($trees)) {
                $lines[] = "  └ {$style['title']} 선택시 용지: " . implode(', ', array_slice(array_column($trees, 'title'), 0, 6));
            }
        }
        
        $lines[] = "3단계 - 규격: A3, A4, A5, A6, B4, B5, B6, 국2절, 국4절, 국8절, 국16절 등";
        $lines[] = "4단계 - 수량: 0.5연, 1연, 2연, 3연, 4연, 5연... (규격마다 1연당 매수가 다름)";
        $lines[] = "  ※ 규격별 1연당 매수:";
        $lines[] = "    - 국2절: 1연=1,000매 / A3: 1연=2,000매 / A4: 1연=4,000매";
        $lines[] = "    - A5: 1연=8,000매 / A6: 1연=16,000매 / B4: 1연=4,000매";
        $lines[] = "    - B5: 1연=8,000매 / B6: 1연=16,000매";
        $lines[] = "5단계 - 디자인:";
        $lines[] = "  - 디자인 있음 (시안 보유): 0원";
        $lines[] = "  - 디자인 의뢰: 규격에 따라 10,000원~30,000원";
        
        $lines[] = "";
        $lines[] = $this->getInsertedPriceTable();
        
        return implode("\n", $lines);
    }
    
    private function getInsertedPriceTable(): string
    {
        $sql = "SELECT i.style, i.TreeSelect, i.Section, i.quantity, i.quantityTwo, i.money, i.DesignMoney,
                       s.title as style_name, t.title as tree_name, sec.title as section_name
                FROM mlangprintauto_inserted i
                LEFT JOIN mlangprintauto_transactioncate s ON i.style = s.no
                LEFT JOIN mlangprintauto_transactioncate t ON i.TreeSelect = t.no
                LEFT JOIN mlangprintauto_transactioncate sec ON i.Section = sec.no
                ORDER BY i.style, i.TreeSelect, i.Section, i.quantity
                LIMIT 150";
        
        $result = mysqli_query($this->db, $sql);
        if (!$result) return "";
        
        $prices = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $style = $row['style_name'] ?: $row['style'];
            $tree = $row['tree_name'] ?: '';
            $section = $row['section_name'] ?: $row['Section'];
            // 연수와 매수 함께 표시
            $qty = $row['quantity'];
            $qtyTwo = $row['quantityTwo'];
            $qtyDisplay = $qtyTwo ? "{$qty}연({$qtyTwo}매)" : "{$qty}연";
            $total = (int)round(((int)$row['money'] + (int)($row['DesignMoney'] ?? 0)) * 1.1);
            
            $key = "{$style}|{$tree}|{$section}";
            if (!isset($prices[$key])) $prices[$key] = [];
            $prices[$key][$qtyDisplay] = number_format($total);
        }
        
        $lines = ["[전단지 가격표] (VAT포함)"];
        foreach ($prices as $key => $qtyPrices) {
            list($style, $tree, $section) = explode('|', $key);
            $label = trim("{$style} {$tree} {$section}");
            $pList = [];
            foreach ($qtyPrices as $q => $p) {
                $pList[] = "{$q}:{$p}원";
            }
            $lines[] = "- {$label}: " . implode(', ', array_slice($pList, 0, 4));
        }
        
        return implode("\n", array_slice($lines, 0, 20));
    }
    
    private function getStickerDropdowns(): string
    {
        $lines = ["【스티커】"];
        $lines[] = "1단계 - 재질: 아트지유광, 아트지무광, 아트지비코팅, 강접아트유광, 초강접아트, 유포지, 은데드롱, 투명스티커, 모조지비코팅, 크라프트";
        $lines[] = "2단계 - 크기: 가로(mm) × 세로(mm) 직접입력 (예: 50×30, 100×100)";
        $lines[] = "3단계 - 수량: 500, 1000, 2000, 3000, 5000, 10000매";
        $lines[] = "4단계 - 도무송: 기본사각(무료), 사각도무송(+8,000원), 귀돌이(+8,000원), 원형(+8,000원), 타원(+8,000원), 모양도무송(+19,000원)";
        $lines[] = "";
        $lines[] = "[스티커 가격 예시] (VAT포함)";
        $lines[] = "- 50×30mm 아트유광 1000매: 약 35,000~45,000원";
        $lines[] = "- 100×100mm 유포지 500매: 약 50,000~70,000원";
        $lines[] = "- 정확한 가격은 크기 입력 필요 (홈페이지 계산기 이용)";
        
        return implode("\n", $lines);
    }
    
    private function getEnvelopeDropdowns(): string
    {
        $lines = ["【봉투】"];
        
        $styles = $this->getLevel1Options('envelope');
        $lines[] = "1단계 - 봉투종류: " . implode(', ', array_column($styles, 'title'));
        
        foreach (array_slice($styles, 0, 2) as $style) {
            $sections = $this->getLevel3Options('envelope', (int)$style['no']);
            if (!empty($sections)) {
                $lines[] = "  └ {$style['title']} 선택시 규격: " . implode(', ', array_slice(array_column($sections, 'title'), 0, 5));
            }
        }
        
        $lines[] = "3단계 - 수량: 500, 1000, 2000, 3000매 등";
        $lines[] = "4단계 - 디자인:";
        $lines[] = "  - 디자인 있음 (시안 보유): 0원";
        $lines[] = "  - 디자인 의뢰: 10,000원~20,000원";
        
        $lines[] = "";
        $lines[] = $this->getEnvelopePriceTable();
        
        return implode("\n", $lines);
    }
    
    private function getEnvelopePriceTable(): string
    {
        $sql = "SELECT e.style, e.Section, e.quantity, e.money, e.DesignMoney,
                       s.title as style_name, sec.title as section_name
                FROM mlangprintauto_envelope e
                LEFT JOIN mlangprintauto_transactioncate s ON e.style = s.no
                LEFT JOIN mlangprintauto_transactioncate sec ON e.Section = sec.no
                ORDER BY e.style, e.Section, e.quantity
                LIMIT 100";
        
        $result = mysqli_query($this->db, $sql);
        if (!$result) return "";
        
        $prices = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $style = $row['style_name'] ?: $row['style'];
            $section = $row['section_name'] ?: $row['Section'];
            $qty = (int)$row['quantity'];
            $total = (int)round(((int)$row['money'] + (int)($row['DesignMoney'] ?? 0)) * 1.1);
            
            $key = "{$style}|{$section}";
            if (!isset($prices[$key])) $prices[$key] = [];
            $prices[$key][$qty] = number_format($total);
        }
        
        $lines = ["[봉투 가격표] (VAT포함)"];
        foreach ($prices as $key => $qtyPrices) {
            list($style, $section) = explode('|', $key);
            $pList = [];
            foreach ($qtyPrices as $q => $p) {
                $pList[] = "{$q}매:{$p}원";
            }
            $lines[] = "- {$style} {$section}: " . implode(', ', array_slice($pList, 0, 4));
        }
        
        return implode("\n", array_slice($lines, 0, 15));
    }
    
    private function getCadarokDropdowns(): string
    {
        $lines = ["【카다록/리플렛】"];
        
        $styles = $this->getLevel1Options('cadarok');
        $lines[] = "1단계 - 종류: " . implode(', ', array_column($styles, 'title'));
        
        foreach (array_slice($styles, 0, 2) as $style) {
            $sections = $this->getLevel3Options('cadarok', (int)$style['no']);
            if (!empty($sections)) {
                $lines[] = "  └ {$style['title']} 선택시: " . implode(', ', array_slice(array_column($sections, 'title'), 0, 5));
            }
        }
        
        $lines[] = "3단계 - 수량: 100, 200, 300, 500, 1000부 등";
        $lines[] = "4단계 - 디자인:";
        $lines[] = "  - 디자인 있음 (시안 보유): 0원";
        $lines[] = "  - 디자인 의뢰: 페이지 수에 따라 20,000원~100,000원";
        
        return implode("\n", $lines);
    }
    
    private function getLittleprintDropdowns(): string
    {
        $lines = ["【포스터/소량인쇄】"];
        
        $styles = $this->getLevel1Options('littleprint');
        $lines[] = "1단계 - 종류: " . implode(', ', array_column($styles, 'title'));
        $lines[] = "2단계 - 용지: 선택한 종류에 따라 다름";
        $lines[] = "3단계 - 규격: A1, A2, A3, A4, B1, B2 등";
        $lines[] = "4단계 - 수량: 1, 5, 10, 20, 50, 100장 등";
        
        return implode("\n", $lines);
    }
    
    private function getMerchandisebondDropdowns(): string
    {
        $lines = ["【상품권】"];
        
        $styles = $this->getLevel1Options('merchandisebond');
        $lines[] = "1단계 - 종류: " . implode(', ', array_column($styles, 'title'));
        $lines[] = "2단계 - 옵션: 선택한 종류에 따라 다름";
        $lines[] = "3단계 - 수량: 500, 1000, 2000, 3000매 등";
        $lines[] = "4단계 - 디자인:";
        $lines[] = "  - 디자인 있음 (시안 보유): 0원";
        $lines[] = "  - 디자인 의뢰: 15,000원~30,000원";
        $lines[] = "추가옵션: 박, 넘버링, 미싱, 귀돌이 (별도 비용)";
        
        return implode("\n", $lines);
    }
    
    private function getNcrDropdowns(): string
    {
        $lines = ["【NCR양식지】"];
        
        $styles = $this->getLevel1Options('ncrflambeau');
        $lines[] = "1단계 - 매수: " . implode(', ', array_column($styles, 'title'));
        $lines[] = "2단계 - 규격: A4, A5, B5 등";
        $lines[] = "3단계 - 인쇄도수: 1도, 2도, 4도 등";
        $lines[] = "4단계 - 수량: 5권, 10권, 20권, 30권 등";
        $lines[] = "5단계 - 디자인:";
        $lines[] = "  - 디자인 있음 (시안 보유): 0원";
        $lines[] = "  - 디자인 의뢰: 10,000원~20,000원";
        $lines[] = "추가옵션: 넘버링(+10,000원), 미싱(+10,000원)";
        
        return implode("\n", $lines);
    }
    
    private function getMstickerDropdowns(): string
    {
        $lines = ["【자석스티커】"];
        
        $styles = $this->getLevel1Options('msticker');
        $lines[] = "1단계 - 종류: " . implode(', ', array_column($styles, 'title'));
        $lines[] = "2단계 - 규격: 선택한 종류에 따라 다름";
        $lines[] = "3단계 - 수량: 100, 200, 300, 500, 1000매 등";
        
        return implode("\n", $lines);
    }
    
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
    
    private function buildSystemPrompt(string $dropdownData): string
    {
        return <<<PROMPT
당신은 "두손기획인쇄" 가격상담 챗봇입니다. 따뜻하고 친근하게 고객을 안내해주세요.

## 상담 철학
- 고객이 복잡한 인쇄 옵션을 쉽게 이해하도록 **한 단계씩 부드럽게 안내**
- 각 선택지가 왜 필요한지 **간단한 설명 추가**
- 전문 용어는 쉬운 말로 풀어서 설명

## 대화 흐름 예시 (명함) - 이런 느낌으로!

고객: "명함 가격 알려주세요"
챗봇: "명함 견적 도와드릴게요! 😊

먼저 **명함 종류**를 선택해주세요:
1. 일반명함(쿠폰) - 가장 많이 찾으시는 실속형
2. 고급수입지 - 특별한 질감의 프리미엄 용지
3. 카드명함(PET명함) - 플라스틱 재질로 내구성 좋음

어떤 스타일이 마음에 드세요?"

고객: "일반명함"
챗봇: "일반명함 좋은 선택이에요! 👍

다음은 **용지**를 골라볼까요?
1. 칼라코팅 - 광택이 있어 선명한 인쇄
2. 칼라비코팅 - 무광으로 고급스러운 느낌

용지에 따라 명함 분위기가 달라져요. 어떤 걸로 할까요?"

고객: "칼라코팅"
챗봇: "칼라코팅으로 할게요! ✨

이제 **수량**을 정해주세요:
- 200매, 500매, 1000매, 2000매...

처음 만드시면 500매 정도 추천드려요. 몇 매로 할까요?"

고객: "500매"
챗봇: "500매 알겠습니다!

**인쇄면**은 어떻게 할까요?
1. 단면 - 앞면만 인쇄
2. 양면 - 앞뒤 모두 인쇄

보통 연락처만 넣으시면 단면, 회사 소개도 넣으시면 양면을 많이 하세요."

고객: "단면"
챗봇: "마지막으로 **디자인** 확인할게요!

1. 디자인 있음 - 이미 시안 파일이 있으시면 (추가비용 없음)
2. 디자인 의뢰 - 저희가 새로 만들어드려요

시안 파일 있으세요, 아니면 디자인 의뢰하실까요?"

고객: "디자인 있음"
챗봇: "완벽해요! 정리해드릴게요 📋

✅ **일반명함 / 칼라코팅 / 500매 / 단면 / 디자인있음**

💰 **총 9,900원** (VAT 포함)

시안 확정 후 2~3일이면 제작 완료되고, 전국 무료배송 해드려요!
주문하시려면 홈페이지에서 진행해주세요 😊"

## 옵션 및 가격 데이터 (DB에서 가져온 실제 데이터)
{$dropdownData}

## 응답 규칙
1. **한 번에 한 가지만 물어보기** - 여러 옵션을 한꺼번에 묻지 않기
2. **선택지에 간단한 설명 추가** - "칼라코팅 - 광택이 있어 선명한 인쇄"
3. **고객 선택을 확인하며 진행** - "~로 할게요!"
4. **추천이나 팁 제공** - "처음이시면 500매 정도 추천드려요"
5. 모든 옵션 선택 완료시 → **가격표에서 정확한 가격 안내**
6. 가격은 항상 **VAT(부가세 10%) 포함** 금액으로 안내
7. 가격표에 없는 조합 → "정확한 견적은 홈페이지 계산기나 전화(000-0000-0000)로 문의해주세요"
8. 이모지 적절히 사용해서 친근하게!

## 옵션별 설명 가이드
- 일반명함: 가장 많이 찾는 실속형
- 고급수입지: 특별한 질감의 프리미엄 용지
- 카드명함: 플라스틱 재질, 내구성 좋음
- 칼라코팅: 광택, 선명한 인쇄
- 칼라비코팅: 무광, 고급스러운 느낌
- 단면: 앞면만 / 양면: 앞뒤 모두
- 디자인있음: 시안 파일 보유시 추가비용 없음
- 디자인의뢰: 새로 제작 (비용은 가격표 참고)

## 추가 안내 (마지막에 알려주기)
- 제작기간: 시안 확정 후 2~3일
- 배송: 전국 무료배송
- 추가옵션(박, 넘버링, 미싱 등)은 별도 문의
PROMPT;
    }
    
    private function callApi(string $systemPrompt, string $message, array $history): array
    {
        $url = $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        $contents = [];
        
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt . "\n\n---\n\n고객: " . $message]]
        ];
        
        foreach ($history as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]]
            ];
        }
        
        if (!empty($history)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]]
            ];
        }
        
        $data = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 600,
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return ['error' => '네트워크 오류: ' . $curlError];
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'API 오류 (HTTP ' . $httpCode . ')';
            return ['error' => $errorMsg];
        }
        
        return json_decode($response, true) ?? ['error' => '응답 파싱 실패'];
    }
    
    private function parseResponse(array $response): array
    {
        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if (empty($content)) {
            return ['error' => '응답이 비어있습니다.'];
        }
        
        $result = [
            'success' => true,
            'message' => trim($content),
        ];
        
        if ($this->containsPaperSelection($content)) {
            $result['paper_images'] = $this->getPaperImages();
        }
        
        return $result;
    }
    
    private function containsPaperSelection(string $content): bool
    {
        $keywords = ['용지를 선택', '용지 선택', '칼라코팅', '스노우화이트', '머쉬멜로우', '누브', '스타드림'];
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
        return !empty($this->apiKey);
    }
}
