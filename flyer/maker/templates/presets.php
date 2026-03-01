<?php
/**
 * 업종별 전단지 프리셋 정의
 * 각 업종의 컬러, 아이콘, 기본 텍스트, 레이아웃 힌트를 정의합니다.
 */

function getIndustryPresets(): array {
    return [
        // ===== 음식점 =====
        'restaurant_korean' => [
            'category' => '음식점',
            'label' => '한식',
            'icon' => '🍜',
            'colors' => ['primary' => '#C0392B', 'secondary' => '#E74C3C', 'accent' => '#F39C12', 'bg' => '#FFF8F0', 'text' => '#2C2C2C'],
            'defaultTagline' => '정성을 담은 한 상',
            'menuLabel' => '대표 메뉴',
            'featureHints' => ['직접 만든 양념', '국내산 재료', '넉넉한 인심'],
        ],
        'restaurant_japanese' => [
            'category' => '음식점',
            'label' => '일식',
            'icon' => '🍣',
            'colors' => ['primary' => '#1A1A2E', 'secondary' => '#16213E', 'accent' => '#E94560', 'bg' => '#FFF9F5', 'text' => '#1A1A2E'],
            'defaultTagline' => '장인의 손맛, 신선한 재료',
            'menuLabel' => '대표 메뉴',
            'featureHints' => ['매일 공수하는 신선 재료', '정통 일식 장인', '오마카세 코스'],
        ],
        'restaurant_chinese' => [
            'category' => '음식점',
            'label' => '중식',
            'icon' => '🥟',
            'colors' => ['primary' => '#B71C1C', 'secondary' => '#D32F2F', 'accent' => '#FFD600', 'bg' => '#FFF8E1', 'text' => '#212121'],
            'defaultTagline' => '불맛 가득, 정통 중화요리',
            'menuLabel' => '대표 메뉴',
            'featureHints' => ['화력 강한 웍 요리', '직접 만든 면', '30년 비법 소스'],
        ],
        'restaurant_western' => [
            'category' => '음식점',
            'label' => '양식/카페',
            'icon' => '🍝',
            'colors' => ['primary' => '#2E4057', 'secondary' => '#048A81', 'accent' => '#F4A261', 'bg' => '#FEFEFE', 'text' => '#2E4057'],
            'defaultTagline' => '특별한 한 끼, 일상의 여유',
            'menuLabel' => '메뉴',
            'featureHints' => ['핸드드립 커피', '수제 파스타', '프라이빗한 공간'],
        ],
        'restaurant_chicken' => [
            'category' => '음식점',
            'label' => '치킨/호프',
            'icon' => '🍗',
            'colors' => ['primary' => '#E65100', 'secondary' => '#FF8F00', 'accent' => '#FFC107', 'bg' => '#FFF3E0', 'text' => '#3E2723'],
            'defaultTagline' => '바삭한 한 입, 시원한 한 잔',
            'menuLabel' => '메뉴',
            'featureHints' => ['당일 도축 신선육', '바삭한 튀김', '빠른 배달'],
        ],

        // ===== 학원/교육 =====
        'academy_english' => [
            'category' => '학원',
            'label' => '영어',
            'icon' => '📚',
            'colors' => ['primary' => '#1565C0', 'secondary' => '#1976D2', 'accent' => '#FF9800', 'bg' => '#F5F9FF', 'text' => '#1A237E'],
            'defaultTagline' => '영어, 자신감으로 말하다',
            'menuLabel' => '프로그램',
            'featureHints' => ['원어민 강사', '소수정예 반편성', '레벨별 맞춤 커리큘럼'],
        ],
        'academy_math' => [
            'category' => '학원',
            'label' => '수학',
            'icon' => '🔢',
            'colors' => ['primary' => '#283593', 'secondary' => '#3949AB', 'accent' => '#00BCD4', 'bg' => '#F3F5FF', 'text' => '#1A237E'],
            'defaultTagline' => '수학의 기초부터 심화까지',
            'menuLabel' => '프로그램',
            'featureHints' => ['1:1 맞춤 진도', '유명 강사진', '성적 향상 보장'],
        ],
        'academy_general' => [
            'category' => '학원',
            'label' => '전과목/종합',
            'icon' => '🎓',
            'colors' => ['primary' => '#00695C', 'secondary' => '#00897B', 'accent' => '#FFC107', 'bg' => '#F0FFF4', 'text' => '#1B5E20'],
            'defaultTagline' => '내신부터 수능까지 한 곳에서',
            'menuLabel' => '과목/프로그램',
            'featureHints' => ['내신 관리 시스템', '자습실 완비', '학부모 상담'],
        ],

        // ===== 피트니스/스포츠 =====
        'fitness_gym' => [
            'category' => '피트니스',
            'label' => '헬스/피트니스',
            'icon' => '💪',
            'colors' => ['primary' => '#212121', 'secondary' => '#424242', 'accent' => '#F44336', 'bg' => '#FAFAFA', 'text' => '#212121'],
            'defaultTagline' => '나를 바꾸는 시간',
            'menuLabel' => '프로그램/가격',
            'featureHints' => ['최신 장비 완비', 'PT 전문 트레이너', '24시간 운영'],
        ],
        'fitness_golf' => [
            'category' => '피트니스',
            'label' => '골프연습장',
            'icon' => '⛳',
            'colors' => ['primary' => '#1B5E20', 'secondary' => '#2E7D32', 'accent' => '#FDD835', 'bg' => '#F1F8E9', 'text' => '#1B5E20'],
            'defaultTagline' => '나만의 골프 라운딩 파트너',
            'menuLabel' => '이용 안내',
            'featureHints' => ['실내 스크린골프', '프로 레슨', '넓은 타석'],
        ],
        'fitness_yoga' => [
            'category' => '피트니스',
            'label' => '요가/필라테스',
            'icon' => '🧘',
            'colors' => ['primary' => '#6A1B9A', 'secondary' => '#8E24AA', 'accent' => '#F48FB1', 'bg' => '#FFF3FC', 'text' => '#4A148C'],
            'defaultTagline' => '몸과 마음의 균형',
            'menuLabel' => '프로그램',
            'featureHints' => ['소수정예 수업', '체형 교정 전문', '초보자 환영'],
        ],

        // ===== 뷰티/건강 =====
        'beauty_hair' => [
            'category' => '뷰티',
            'label' => '미용실/헤어',
            'icon' => '💇',
            'colors' => ['primary' => '#880E4F', 'secondary' => '#AD1457', 'accent' => '#FF80AB', 'bg' => '#FFF0F5', 'text' => '#4A0025'],
            'defaultTagline' => '당신만의 스타일을 찾아드립니다',
            'menuLabel' => '시술 메뉴',
            'featureHints' => ['트렌디한 디자이너', '두피/모발 진단', '프리미엄 제품 사용'],
        ],
        'beauty_skin' => [
            'category' => '뷰티',
            'label' => '피부관리',
            'icon' => '✨',
            'colors' => ['primary' => '#F8BBD0', 'secondary' => '#F48FB1', 'accent' => '#CE93D8', 'bg' => '#FFFBFE', 'text' => '#5D4037'],
            'defaultTagline' => '건강한 피부, 아름다운 자신감',
            'menuLabel' => '관리 프로그램',
            'featureHints' => ['전문 피부 상담', '최신 장비 보유', '맞춤 관리 프로그램'],
        ],
        'beauty_massage' => [
            'category' => '뷰티',
            'label' => '마사지/스파',
            'icon' => '💆',
            'colors' => ['primary' => '#4E342E', 'secondary' => '#6D4C41', 'accent' => '#A5D6A7', 'bg' => '#F9FBE7', 'text' => '#3E2723'],
            'defaultTagline' => '지친 하루, 편안한 힐링',
            'menuLabel' => '코스 안내',
            'featureHints' => ['전문 테라피스트', '프라이빗 룸', '천연 아로마 오일'],
        ],

        // ===== 일반 =====
        'general_store' => [
            'category' => '일반',
            'label' => '일반 매장/기타',
            'icon' => '🏪',
            'colors' => ['primary' => '#1A237E', 'secondary' => '#283593', 'accent' => '#FF6F00', 'bg' => '#FFFFFF', 'text' => '#212121'],
            'defaultTagline' => '',
            'menuLabel' => '서비스/상품',
            'featureHints' => [],
        ],
    ];
}

/**
 * 카테고리별로 그룹핑
 */
function getPresetsGrouped(): array {
    $presets = getIndustryPresets();
    $grouped = [];
    foreach ($presets as $key => $preset) {
        $cat = $preset['category'];
        if (!isset($grouped[$cat])) {
            $grouped[$cat] = [];
        }
        $grouped[$cat][$key] = $preset;
    }
    return $grouped;
}

/**
 * 프리셋 키로 조회
 */
function getPreset(string $key): ?array {
    $presets = getIndustryPresets();
    return $presets[$key] ?? null;
}
