<?php
/**
 * CategoryBasedProduct - 카테고리 기반 표준형 제품 클래스
 *
 * 6개 표준형 제품에 사용됩니다:
 * - namecard (명함)
 * - envelope (봉투)
 * - cadarok (카다록)
 * - littleprint (포스터)
 * - merchandisebond (상품권)
 * - msticker (자석스티커)
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

require_once __DIR__ . '/BaseProduct.php';

class CategoryBasedProduct extends BaseProduct {
    // 표준형 제품 공통 필드
    protected $MY_type;              // 카테고리/종류
    protected $PN_type;              // 규격/사이즈 (Section)
    protected $MY_Fsd;               // 용지/재질
    protected $POtype;               // 인쇄면 (단면/양면)
    protected $MY_amount;            // 수량
    protected $ordertype;            // 작업방식 (자체디자인/전체디자인)
    protected $premium_options;      // 선택 옵션 (JSON)
    protected $premium_options_total; // 선택 옵션 합계
    protected $additional_options;   // 추가 옵션 (JSON)
    protected $additional_options_total; // 추가 옵션 합계

    // 한글명 필드 (표시용)
    protected $MY_type_name;
    protected $Section_name;
    protected $POtype_name;

    // 작업 메모
    protected $work_memo;
    protected $upload_method;

    /**
     * POST 데이터로부터 필드 설정
     *
     * @param array $postData $_POST 배열
     */
    public function setFromPost($postData) {
        $this->MY_type = $postData['MY_type'] ?? '';
        $this->PN_type = $postData['Section'] ?? $postData['PN_type'] ?? '';
        $this->MY_Fsd = $postData['MY_Fsd'] ?? '';
        $this->POtype = $postData['POtype'] ?? '';
        $this->MY_amount = $postData['MY_amount'] ?? '';
        $this->ordertype = $postData['ordertype'] ?? '';

        // 프리미엄 옵션 (JSON)
        $this->premium_options = $postData['premium_options'] ?? '{}';
        if (is_array($this->premium_options)) {
            $this->premium_options = json_encode($this->premium_options, JSON_UNESCAPED_UNICODE);
        }
        $this->premium_options_total = (int)($postData['premium_options_total'] ?? 0);

        // 추가 옵션 (JSON)
        $this->additional_options = $postData['additional_options'] ?? '{}';
        if (is_array($this->additional_options)) {
            $this->additional_options = json_encode($this->additional_options, JSON_UNESCAPED_UNICODE);
        }
        $this->additional_options_total = (int)($postData['additional_options_total'] ?? 0);

        // 한글명
        $this->MY_type_name = $postData['MY_type_name'] ?? '';
        $this->Section_name = $postData['Section_name'] ?? '';
        $this->POtype_name = $postData['POtype_name'] ?? '';

        // 기타
        $this->work_memo = $postData['work_memo'] ?? '';
        $this->upload_method = $postData['upload_method'] ?? 'direct';

        // 가격 설정
        $calculated_price = (int)($postData['calculated_price'] ?? 0);
        $calculated_vat_price = (int)($postData['calculated_vat_price'] ?? 0);
        $this->setPrice($calculated_price, $calculated_vat_price);
    }

    /**
     * 가격 계산
     *
     * @return array ['total_price' => int, 'vat_price' => int]
     */
    public function calculatePrice() {
        // 제품별 테이블명
        $table_map = [
            'namecard' => 'mlangprintauto_namecard',
            'envelope' => 'mlangprintauto_envelope',
            'cadarok' => 'mlangprintauto_cadarok',
            'littleprint' => 'mlangprintauto_littleprint',
            'merchandisebond' => 'mlangprintauto_merchandisebond',
            'msticker' => 'mlangprintauto_msticker'
        ];

        $table = $table_map[$this->product_type] ?? null;
        if (!$table) {
            throw new Exception("Unknown product type: {$this->product_type}");
        }

        // 기본 가격 조회
        $query = "SELECT money, DesignMoney
                  FROM {$table}
                  WHERE style = ?
                  AND Section = ?
                  AND POtype = ?
                  AND quantity = ?
                  LIMIT 1";

        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            // Fallback: POtype 없이 조회
            $query_fallback = "SELECT money, DesignMoney
                              FROM {$table}
                              WHERE style = ?
                              AND Section = ?
                              AND quantity = ?
                              LIMIT 1";

            $stmt = mysqli_prepare($this->db, $query_fallback);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $this->MY_type, $this->PN_type, $this->MY_amount);
            }
        } else {
            mysqli_stmt_bind_param($stmt, "ssss", $this->MY_type, $this->PN_type, $this->POtype, $this->MY_amount);
        }

        if (!$stmt) {
            throw new Exception("가격 조회 쿼리 실행 실패");
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row) {
            throw new Exception("해당 조건의 가격 정보를 찾을 수 없습니다.");
        }

        // 기본 가격 + 디자인 가격
        $base_price = (int)$row['money'];
        $design_price = ($this->ordertype === 'total') ? (int)$row['DesignMoney'] : 0;

        // 총 가격 = 기본가 + 디자인비 + 프리미엄 옵션 + 추가 옵션
        $total_price = $base_price + $design_price + $this->premium_options_total + $this->additional_options_total;

        // VAT 계산
        $vat_price = $this->calculateVAT($total_price);

        return [
            'total_price' => $this->roundPrice($total_price),
            'vat_price' => $vat_price
        ];
    }

    /**
     * 장바구니 데이터 생성
     *
     * @return array 장바구니 저장용 데이터
     */
    public function getCartData() {
        return [
            'session_id' => $this->session_id,
            'product_type' => $this->product_type,
            'MY_type' => $this->MY_type,
            'Section' => $this->PN_type,
            'POtype' => $this->POtype,
            'MY_amount' => $this->MY_amount,
            'ordertype' => $this->ordertype,
            'st_price' => $this->st_price,
            'st_price_vat' => $this->st_price_vat,
            'premium_options' => $this->premium_options,
            'premium_options_total' => $this->premium_options_total,
            'MY_type_name' => $this->MY_type_name,
            'Section_name' => $this->Section_name,
            'POtype_name' => $this->POtype_name,
            'work_memo' => $this->work_memo,
            'upload_method' => $this->upload_method,
            'uploaded_files' => $this->uploaded_files ?? '[]',
            'ThingCate' => $this->ThingCate ?? '',
            'ImgFolder' => $this->ImgFolder ?? ''
        ];
    }

    /**
     * 주문 데이터 생성
     *
     * @return array 주문 저장용 데이터
     */
    public function getOrderData() {
        // product_info JSON 생성
        $product_info = [
            'product_type' => $this->product_type,
            'MY_type' => $this->MY_type,
            'PN_type' => $this->PN_type,
            'POtype' => $this->POtype,
            'MY_amount' => $this->MY_amount,
            'ordertype' => $this->ordertype,
            'MY_type_name' => $this->MY_type_name,
            'Section_name' => $this->Section_name,
            'POtype_name' => $this->POtype_name,
            'premium_options' => json_decode($this->premium_options, true),
            'formatted_display' => $this->getFormattedDisplay()
        ];

        return [
            'product_type' => $this->product_type,
            'product_info' => json_encode($product_info, JSON_UNESCAPED_UNICODE),
            'st_price' => $this->st_price,
            'st_price_vat' => $this->st_price_vat,
            'uploaded_files' => $this->uploaded_files ?? '[]',
            'ImgFolder' => $this->ImgFolder ?? '',
            'ThingCate' => $this->ThingCate ?? ''
        ];
    }

    /**
     * 포맷된 제품 정보 표시
     *
     * @return string 사람이 읽을 수 있는 형태
     */
    protected function getFormattedDisplay() {
        $lines = [];

        if ($this->MY_type_name) {
            $lines[] = "종류: {$this->MY_type_name}";
        }
        if ($this->Section_name) {
            $lines[] = "규격: {$this->Section_name}";
        }
        if ($this->POtype_name) {
            $lines[] = "인쇄: {$this->POtype_name}";
        }
        if ($this->MY_amount) {
            $lines[] = "수량: {$this->MY_amount}매";
        }

        // 프리미엄 옵션
        $premium = json_decode($this->premium_options, true);
        if ($premium && !empty($premium)) {
            $premium_names = [];
            foreach ($premium as $key => $value) {
                if ($value) {
                    $premium_names[] = $this->translatePremiumOption($key);
                }
            }
            if (!empty($premium_names)) {
                $lines[] = "옵션: " . implode(', ', $premium_names);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * 프리미엄 옵션 키를 한글로 변환
     *
     * @param string $key 옵션 키
     * @return string 한글명
     */
    protected function translatePremiumOption($key) {
        $map = [
            'foil' => '박',
            'numbering' => '넘버링',
            'perforation' => '미싱',
            'rounding' => '귀돌이',
            'creasing' => '오시',
            'coating' => '코팅',
            'embossing' => '엠보싱'
        ];

        return $map[$key] ?? $key;
    }

    /**
     * 유효성 검증
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate() {
        $result = parent::validate();

        // 추가 검증
        if (empty($this->MY_type)) {
            $result['errors'][] = '종류를 선택해주세요.';
        }

        if (empty($this->PN_type)) {
            $result['errors'][] = '규격을 선택해주세요.';
        }

        if (empty($this->POtype)) {
            $result['errors'][] = '인쇄면을 선택해주세요.';
        }

        if (empty($this->MY_amount)) {
            $result['errors'][] = '수량을 입력해주세요.';
        }

        if (empty($this->ordertype)) {
            $result['errors'][] = '작업방식을 선택해주세요.';
        }

        $result['valid'] = empty($result['errors']);
        return $result;
    }
}
