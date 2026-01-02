<?php
/**
 * StickerProduct - 스티커 제품 클래스
 *
 * 스티커는 종/가로/세로 기반의 개별 계산 방식을 사용합니다.
 * BaseProduct를 직접 상속합니다.
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

require_once __DIR__ . '/BaseProduct.php';

class StickerProduct extends BaseProduct {
    // 스티커 전용 필드
    protected $jong;           // 종류
    protected $garo;           // 가로 (mm)
    protected $sero;           // 세로 (mm)
    protected $mesu;           // 매수
    protected $uhyung;         // 유형 (int)
    protected $domusong;       // 도무송

    // 추가 정보
    protected $work_memo;
    protected $customer_name;
    protected $customer_phone;
    protected $upload_method;

    /**
     * POST 데이터로부터 필드 설정
     *
     * @param array $postData $_POST 배열
     */
    public function setFromPost($postData) {
        $this->jong = $postData['jong'] ?? '';
        $this->garo = $postData['garo'] ?? '';
        $this->sero = $postData['sero'] ?? '';
        $this->mesu = $postData['mesu'] ?? '';
        $this->uhyung = (int)($postData['uhyung'] ?? 0);
        $this->domusong = $postData['domusong'] ?? '';

        // 추가 정보
        $this->work_memo = $postData['memo'] ?? $postData['work_memo'] ?? '';
        $this->customer_name = $postData['customerName'] ?? '';
        $this->customer_phone = $postData['customerPhone'] ?? '';
        $this->upload_method = $postData['upload_method'] ?? 'upload';

        // 가격 설정
        $price = (int)($postData['price'] ?? $postData['st_price'] ?? 0);
        $vat_price = (int)($postData['st_price_vat'] ?? $price);
        $this->setPrice($price, $vat_price);
    }

    /**
     * 가격 계산
     *
     * @return array ['total_price' => int, 'vat_price' => int]
     */
    public function calculatePrice() {
        // 스티커는 jong/garo/sero 조합으로 가격 계산
        // 실제 계산 로직은 calculate_price_ajax.php 참조
        // 여기서는 간단히 구현

        $table = "mlangprintauto_sticker";

        $query = "SELECT money
                  FROM {$table}
                  WHERE jong = ?
                  AND garo = ?
                  AND sero = ?
                  AND mesu = ?
                  LIMIT 1";

        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            throw new Exception("가격 조회 쿼리 실행 실패");
        }

        mysqli_stmt_bind_param($stmt, "ssss", $this->jong, $this->garo, $this->sero, $this->mesu);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row) {
            // 가격 정보가 없으면 기본값 반환
            throw new Exception("해당 조건의 가격 정보를 찾을 수 없습니다.");
        }

        $total_price = (int)$row['money'];
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
            'jong' => $this->jong,
            'garo' => $this->garo,
            'sero' => $this->sero,
            'mesu' => $this->mesu,
            'uhyung' => $this->uhyung,
            'domusong' => $this->domusong,
            'st_price' => $this->st_price,
            'st_price_vat' => $this->st_price_vat,
            'work_memo' => $this->work_memo,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'upload_method' => $this->upload_method,
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
            'jong' => $this->jong,
            'garo' => $this->garo,
            'sero' => $this->sero,
            'mesu' => $this->mesu,
            'uhyung' => $this->uhyung,
            'domusong' => $this->domusong,
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

        if ($this->jong) {
            $lines[] = "종류: {$this->jong}";
        }
        if ($this->garo && $this->sero) {
            $lines[] = "사이즈: {$this->garo}mm × {$this->sero}mm";
        }
        if ($this->mesu) {
            $lines[] = "수량: {$this->mesu}매";
        }
        if ($this->domusong) {
            $lines[] = "도무송: {$this->domusong}";
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

        // 스티커 필수 필드 검증
        if (empty($this->jong)) {
            $result['errors'][] = '종류를 선택해주세요.';
        }

        if (empty($this->garo)) {
            $result['errors'][] = '가로 크기를 입력해주세요.';
        }

        if (empty($this->sero)) {
            $result['errors'][] = '세로 크기를 입력해주세요.';
        }

        if (empty($this->mesu)) {
            $result['errors'][] = '수량을 입력해주세요.';
        }

        $result['valid'] = empty($result['errors']);
        return $result;
    }
}
