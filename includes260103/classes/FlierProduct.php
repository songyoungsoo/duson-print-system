<?php
/**
 * FlierProduct - 전단지 제품 클래스
 *
 * CategoryBasedProduct를 상속하며, 추가로 mesu(매수) 필드를 관리합니다.
 * mesu는 연수(ream) 계산에 사용됩니다.
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

require_once __DIR__ . '/CategoryBasedProduct.php';

class FlierProduct extends CategoryBasedProduct {
    protected $mesu;  // 매수 (연수 계산용)

    /**
     * POST 데이터로부터 필드 설정 (부모 메서드 override)
     *
     * @param array $postData $_POST 배열
     */
    public function setFromPost($postData) {
        parent::setFromPost($postData);

        // mesu 추출 (MY_amountRight에서 숫자만 추출)
        $MY_amountRight = $postData['MY_amountRight'] ?? $postData['MY_amount'] ?? '';
        if (preg_match('/\d+/', $MY_amountRight, $matches)) {
            $this->mesu = (int)$matches[0];
        } else {
            $this->mesu = 0;
        }

        // 추가 옵션 처리 (전단지 특화)
        $this->additional_options = $postData['additional_options'] ?? '{}';
        if (is_array($this->additional_options)) {
            $this->additional_options = json_encode($this->additional_options, JSON_UNESCAPED_UNICODE);
        }
        $this->additional_options_total = (int)($postData['additional_options_total'] ?? 0);
    }

    /**
     * 장바구니 데이터 생성 (부모 메서드 override)
     *
     * @return array 장바구니 저장용 데이터
     */
    public function getCartData() {
        $data = parent::getCartData();

        // 전단지 특화 필드 추가
        $data['MY_Fsd'] = $this->MY_Fsd;
        $data['PN_type'] = $this->PN_type;
        $data['mesu'] = $this->mesu;
        $data['additional_options'] = $this->additional_options;
        $data['additional_options_total'] = $this->additional_options_total;

        return $data;
    }

    /**
     * 주문 데이터 생성 (부모 메서드 override)
     *
     * @return array 주문 저장용 데이터
     */
    public function getOrderData() {
        $data = parent::getOrderData();

        // product_info에 mesu 추가
        $product_info = json_decode($data['product_info'], true);
        $product_info['mesu'] = $this->mesu;
        $product_info['MY_Fsd'] = $this->MY_Fsd;
        $product_info['formatted_display'] = $this->getFormattedDisplay();

        $data['product_info'] = json_encode($product_info, JSON_UNESCAPED_UNICODE);

        return $data;
    }

    /**
     * 포맷된 제품 정보 표시 (부모 메서드 override)
     *
     * @return string 사람이 읽을 수 있는 형태
     */
    protected function getFormattedDisplay() {
        $lines = [];

        if ($this->MY_type_name) {
            $lines[] = "용지: {$this->MY_type_name}";
        }
        if ($this->Section_name) {
            $lines[] = "규격: {$this->Section_name}";
        }
        if ($this->MY_Fsd) {
            $lines[] = "사이즈: {$this->MY_Fsd}";
        }
        if ($this->POtype_name) {
            $lines[] = "인쇄: {$this->POtype_name}";
        }
        if ($this->mesu) {
            $lines[] = "매수: {$this->mesu}매";
        }
        if ($this->MY_amount) {
            $lines[] = "연수: {$this->MY_amount}연";
        }

        // 추가 옵션
        $additional = json_decode($this->additional_options, true);
        if ($additional && !empty($additional)) {
            $option_names = [];
            foreach ($additional as $key => $value) {
                if ($value) {
                    $option_names[] = $this->translateAdditionalOption($key);
                }
            }
            if (!empty($option_names)) {
                $lines[] = "추가옵션: " . implode(', ', $option_names);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * 추가 옵션 키를 한글로 변환
     *
     * @param string $key 옵션 키
     * @return string 한글명
     */
    protected function translateAdditionalOption($key) {
        $map = [
            'folding_2' => '2단 접지',
            'folding_3' => '3단 접지',
            'folding_4' => '4단 접지',
            'numbering' => '넘버링',
            'perforation' => '미싱',
            'binding' => '제본'
        ];

        return $map[$key] ?? $key;
    }

    /**
     * 유효성 검증 (부모 메서드 override)
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate() {
        $result = parent::validate();

        // mesu 검증
        if ($this->mesu <= 0) {
            $result['errors'][] = '매수를 올바르게 입력해주세요.';
        }

        $result['valid'] = empty($result['errors']);
        return $result;
    }

    /**
     * mesu 반환
     *
     * @return int
     */
    public function getMesu() {
        return $this->mesu;
    }
}
