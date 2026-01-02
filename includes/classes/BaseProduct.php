<?php
/**
 * BaseProduct - 모든 제품의 기본 추상 클래스
 *
 * 9개 제품의 공통 필드와 메서드를 정의합니다.
 * 각 제품은 이 클래스를 상속받아 구현합니다.
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

abstract class BaseProduct {
    // 공통 필드
    protected $db;                    // 데이터베이스 연결
    protected $session_id;            // 세션 ID
    protected $product_type;          // 제품 타입 (inserted, namecard, etc.)
    protected $st_price;              // 기본 가격
    protected $st_price_vat;          // VAT 포함 가격
    protected $uploaded_files;        // 업로드된 파일 (JSON)
    protected $ImgFolder;             // 이미지 폴더 경로
    protected $ThingCate;             // 카테고리 코드

    /**
     * 생성자
     *
     * @param mysqli $db 데이터베이스 연결
     * @param string $product_type 제품 타입
     */
    public function __construct($db, $product_type) {
        $this->db = $db;
        $this->product_type = $product_type;
        $this->session_id = session_id();
    }

    /**
     * 가격 계산 (추상 메서드 - 각 제품에서 구현)
     *
     * @return array ['total_price' => int, 'vat_price' => int]
     */
    abstract public function calculatePrice();

    /**
     * 장바구니 데이터 생성 (추상 메서드)
     *
     * @return array 장바구니 저장용 데이터
     */
    abstract public function getCartData();

    /**
     * 주문 데이터 생성 (추상 메서드)
     *
     * @return array 주문 저장용 데이터
     */
    abstract public function getOrderData();

    /**
     * 파일 업로드 처리
     *
     * @param array $files $_FILES 배열
     * @return array ['files' => array, 'img_folder' => string, 'thing_cate' => string]
     */
    public function uploadFiles($files) {
        require_once __DIR__ . '/../StandardUploadHandler.php';

        try {
            $result = StandardUploadHandler::processUpload($this->product_type, $files);

            $this->uploaded_files = json_encode($result['files'], JSON_UNESCAPED_UNICODE);
            $this->ImgFolder = $result['img_folder'];
            $this->ThingCate = $result['thing_cate'];

            return $result;
        } catch (Exception $e) {
            error_log("File upload error for {$this->product_type}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 장바구니에 추가
     *
     * @return bool 성공 여부
     */
    public function addToCart() {
        $cartData = $this->getCartData();

        // shop_temp 테이블에 저장
        $fields = array_keys($cartData);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO shop_temp (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($this->db));
            return false;
        }

        // 타입 문자열 생성
        $types = '';
        $values = [];
        foreach ($cartData as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }

        // bind_param 동적 호출
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            error_log("Execute failed: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
        return $result;
    }

    /**
     * 데이터 유효성 검증
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate() {
        $errors = [];

        // 공통 필수 필드 검증
        if (empty($this->session_id)) {
            $errors[] = '세션 ID가 없습니다.';
        }

        if (empty($this->product_type)) {
            $errors[] = '제품 타입이 지정되지 않았습니다.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * 가격 반올림 (원 단위)
     *
     * @param float $price 가격
     * @return int 반올림된 가격
     */
    protected function roundPrice($price) {
        return (int)round($price);
    }

    /**
     * VAT 계산 (10%)
     *
     * @param int $price 기본 가격
     * @return int VAT 포함 가격
     */
    protected function calculateVAT($price) {
        return $this->roundPrice($price * 1.1);
    }

    /**
     * 세션 ID 설정
     *
     * @param string $session_id
     */
    public function setSessionId($session_id) {
        $this->session_id = $session_id;
    }

    /**
     * 가격 설정
     *
     * @param int $price 기본 가격
     * @param int $vat_price VAT 포함 가격
     */
    public function setPrice($price, $vat_price) {
        $this->st_price = $price;
        $this->st_price_vat = $vat_price;
    }

    /**
     * 제품 타입 반환
     *
     * @return string
     */
    public function getProductType() {
        return $this->product_type;
    }
}
