<?php
/**
 * 택배비 및 박스 수량 계산 헬퍼 함수
 * PHP 5.2 호환 / EUC-KR
 *
 * delivery_rules_config.php의 규칙을 기반으로 자동 계산합니다.
 */

/**
 * 제품 타입과 상세 정보를 기반으로 규칙 키를 결정
 *
 * @param string $type 제품 타입 (Type 필드)
 * @param string $type_1 제품 상세 (Type_1 필드)
 * @return string 규칙 키 (예: 'namecard', 'inserted_90g_a4')
 */
function getDeliveryRuleKey($type, $type_1) {
    // 명함
    if (preg_match('/NameCard/i', $type)) {
        return 'namecard';
    }

    // 상품권
    if (preg_match('/MerchandiseBond/i', $type)) {
        return 'merchandisebond';
    }

    // 전단지 - 90g아트지 A4
    if (preg_match('/a4/i', $type_1) && preg_match('/90g/i', $type_1)) {
        return 'inserted_90g_a4';
    }

    // 전단지 - B5(16절)
    if (preg_match('/16/i', $type_1) || preg_match('/b5/i', $type_1)) {
        return 'inserted_b5_16';
    }

    // 스티커
    if (preg_match('/sticker/i', $type)) {
        return 'sticker';
    }

    // 봉투
    if (preg_match('/envelope/i', $type)) {
        return 'envelope';
    }

    // 기본 규칙
    return 'default';
}

/**
 * 수량을 기반으로 박스 수와 택배비 계산
 *
 * @param array $rules 규칙 배열
 * @param int $quantity 수량
 * @return array
 */
function calculateDelivery($rules, $quantity) {
    foreach ($rules as $rule) {
        if ($quantity >= $rule['min'] && $quantity <= $rule['max']) {
            return array(
                'box' => $rule['box'],
                'price' => $rule['price'],
                'label' => isset($rule['label']) ? $rule['label'] : ''
            );
        }
    }

    // 매칭되는 규칙이 없으면 기본값 반환
    return array('box' => 1, 'price' => 3000, 'label' => '기본');
}

/**
 * Type_1 필드에서 수량 추출 (전단지의 경우)
 *
 * @param string $type_1 제품 상세 정보
 * @return int 수량 (추출 실패 시 1000 반환)
 */
function extractQuantityFromType($type_1) {
    // "0.5연" → 500
    if (preg_match('/0\.5/', $type_1)) {
        return 500;
    }

    // "1연" → 1000
    if (preg_match('/1\s*/', $type_1)) {
        // 먼저 숫자+연 패턴 체크
        if (preg_match('/(\d+)/', $type_1, $matches)) {
            $num = intval($matches[1]);
            // 연 단위라면 1000 곱함
            if (strpos($type_1, '연') !== false && $num < 100) {
                return $num * 1000;
            }
            // 매 단위라면 그대로
            if (strpos($type_1, '매') !== false) {
                return $num;
            }
            return $num;
        }
    }

    // 숫자만 있는 경우
    if (preg_match('/(\d+)/', $type_1, $matches)) {
        return intval($matches[1]);
    }

    // 기본값
    return 1000;
}

/**
 * 주문 데이터를 기반으로 택배비와 박스 수 계산 (메인 함수)
 *
 * @param array $orderData 주문 데이터 (DB row)
 * @param array $deliveryRules 설정 파일에서 로드한 규칙
 * @return array
 */
function getDeliveryInfo($orderData, $deliveryRules) {
    $type = isset($orderData['Type']) ? $orderData['Type'] : '';
    $type_1 = isset($orderData['Type_1']) ? $orderData['Type_1'] : '';

    // 1. 규칙 키 결정
    $ruleKey = getDeliveryRuleKey($type, $type_1);

    // 2. 수량 추출 (Type_1에서 또는 기본값)
    $quantity = extractQuantityFromType($type_1);

    // 3. 해당 규칙 가져오기
    $rules = isset($deliveryRules[$ruleKey]) ? $deliveryRules[$ruleKey] : $deliveryRules['default'];

    // 4. 택배비 계산
    $result = calculateDelivery($rules, $quantity);
    $result['rule_key'] = $ruleKey;
    $result['quantity'] = $quantity;

    return $result;
}
?>
