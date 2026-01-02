<?php
/**
 * NCRProduct - NCR양식 제품 클래스
 *
 * NCR양식은 완전히 별도의 구조를 가집니다.
 * BaseProduct를 직접 상속하며, 기존 로직을 최대한 유지합니다.
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

require_once __DIR__ . '/BaseProduct.php';

class NCRProduct extends BaseProduct {
    // NCR양식 전용 필드
    protected $MY_type;          // 카테고리
    protected $PN_type;          // 규격
    protected $MY_Fsd;           // 용지
    protected $POtype;           // 인쇄면
    protected $MY_amount;        // 수량
    protected $ordertype;        // 작업방식
    protected $MY_comment;       // 메모

    /**
     * POST 데이터로부터 필드 설정
     *
     * @param array $postData $_POST 배열
     */
    public function setFromPost($postData) {
        $this->MY_type = $postData['MY_type'] ?? '';
        $this->PN_type = $postData['PN_type'] ?? '';
        $this->MY_Fsd = $postData['MY_Fsd'] ?? '';
        $this->POtype = $postData['POtype'] ?? '';
        $this->MY_amount = $postData['MY_amount'] ?? '';
        $this->ordertype = $postData['ordertype'] ?? '';
        $this->MY_comment = $postData['MY_comment'] ?? '';

        // 가격 설정
        $price = (int)($postData['calculated_price'] ?? 0);
        $vat_price = (int)($postData['calculated_vat_price'] ?? 0);
        $this->setPrice($price, $vat_price);
    }

    /**
     * 가격 계산
     *
     * @return array ['total_price' => int, 'vat_price' => int]
     */
    public function calculatePrice() {
        // NCR양식 가격 조회
        $table = "mlangprintauto_ncrflambeau";

        $query = "SELECT money, DesignMoney
                  FROM {$table}
                  WHERE MY_type = ?
                  AND PN_type = ?
                  AND MY_Fsd = ?
                  AND POtype = ?
                  AND MY_amount = ?
                  LIMIT 1";

        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            throw new Exception("가격 조회 쿼리 실행 실패");
        }

        mysqli_stmt_bind_param($stmt, "sssss",
            $this->MY_type,
            $this->PN_type,
            $this->MY_Fsd,
            $this->POtype,
            $this->MY_amount
        );

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

        $total_price = $base_price + $design_price;
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
            'PN_type' => $this->PN_type,
            'MY_Fsd' => $this->MY_Fsd,
            'POtype' => $this->POtype,
            'MY_amount' => $this->MY_amount,
            'ordertype' => $this->ordertype,
            'MY_comment' => $this->MY_comment,
            'st_price' => $this->st_price,
            'st_price_vat' => $this->st_price_vat,
            'uploaded_files' => $this->uploaded_files ?? '[]',
            'ImgFolder' => $this->ImgFolder ?? '',
            'ThingCate' => $this->ThingCate ?? ''
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
            'MY_Fsd' => $this->MY_Fsd,
            'POtype' => $this->POtype,
            'MY_amount' => $this->MY_amount,
            'ordertype' => $this->ordertype,
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

        if ($this->MY_type) {
            $lines[] = "종류: {$this->MY_type}";
        }
        if ($this->PN_type) {
            $lines[] = "규격: {$this->PN_type}";
        }
        if ($this->MY_Fsd) {
            $lines[] = "용지: {$this->MY_Fsd}";
        }
        if ($this->POtype) {
            $lines[] = "인쇄: {$this->POtype}";
        }
        if ($this->MY_amount) {
            $lines[] = "수량: {$this->MY_amount}";
        }

        return implode("\n", $lines);
    }

    /**
     * 유효성 검증
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate() {
        $result = parent::validate();

        // NCR양식 필수 필드 검증
        if (empty($this->MY_type)) {
            $result['errors'][] = '종류를 선택해주세요.';
        }

        if (empty($this->PN_type)) {
            $result['errors'][] = '규격을 선택해주세요.';
        }

        if (empty($this->MY_amount)) {
            $result['errors'][] = '수량을 입력해주세요.';
        }

        $result['valid'] = empty($result['errors']);
        return $result;
    }
}
