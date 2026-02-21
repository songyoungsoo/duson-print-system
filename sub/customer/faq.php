<?php
/**
 * 자주하는 질문 (FAQ)
 * 카테고리별 FAQ 제공
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FAQ 데이터 (실제로는 DB에서 가져와야 함)
$faqs = [
    'order' => [
        [
            'question' => '회원가입 없이 주문 가능한가요?',
            'answer' => '네, 비회원으로도 주문 가능합니다. 다만 회원 가입 시 적립금 혜택, 주문 내역 조회 등의 편의 기능을 이용하실 수 있습니다.'
        ],
        [
            'question' => '결제 방법은 어떤 것이 있나요?',
            'answer' => '무통장입금, 카드결제, 실시간 계좌이체가 가능합니다. 공공기관 고객의 경우 후불 결제도 협의 가능합니다.'
        ],
        [
            'question' => '세금계산서 발행이 가능한가요?',
            'answer' => '네, 사업자 회원의 경우 세금계산서 발행이 가능합니다. 주문 시 세금계산서 발행을 선택하시고, 사업자 정보를 입력해주세요.'
        ],
        [
            'question' => '주문 후 취소나 변경이 가능한가요?',
            'answer' => '제작 시작 전(입금 대기 상태)에는 취소 및 변경이 가능합니다. 제작이 시작된 후에는 취소 및 변경이 불가능하오니 주문 시 신중하게 확인해주세요.'
        ]
    ],
    'file' => [
        [
            'question' => '어떤 파일 형식을 업로드할 수 있나요?',
            'answer' => 'AI, PDF 파일을 권장하며, JPG, PNG, HWP, 워드, 엑셀, 파워포인트 파일도 업로드 가능합니다.'
        ],
        [
            'question' => '파일 용량 제한이 있나요?',
            'answer' => '파일당 최대 10MB까지 업로드 가능합니다. 그 이상의 파일은 웹하드 id:duson1830 pw:1830 / 이메일 dsp1830@naver.com 또는 고객센터로 문의해주세요.'
        ],
        [
            'question' => '파일 수정은 어떻게 하나요?',
            'answer' => '입금 전까지는 마이페이지에서 파일을 다시 업로드할 수 있습니다. 입금 후에는 고객센터(1688-2384 / 02-2632-1830)로 연락주세요.'
        ],
        [
            'question' => '폰트가 깨져서 나올 수 있나요?',
            'answer' => '폰트를 아웃라인 처리하지 않으면 다른 폰트로 대체될 수 있습니다. AI 파일의 경우 반드시 폰트를 아웃라인 처리해주세요.'
        ]
    ],
    'quality' => [
        [
            'question' => '제작 기간은 얼마나 걸리나요?',
            'answer' => '일반적으로 입금 확인 후 2~3일 소요됩니다. 제품과 수량에 따라 다를 수 있으며, 정확한 출고일은 제품 페이지에서 확인하실 수 있습니다.'
        ],
        [
            'question' => '샘플 확인이 가능한가요?',
            'answer' => '대량 주문의 경우 샘플 제작이 가능합니다. 별도 비용이 발생하며, 고객센터로 문의해주세요.'
        ],
        [
            'question' => '색상 차이가 있을 수 있나요?',
            'answer' => '모니터와 인쇄물은 색 표현 방식이 다르기 때문에 약간의 색상 차이가 발생할 수 있습니다. CMYK 모드로 작업하시면 색상 차이를 최소화할 수 있습니다.'
        ],
        [
            'question' => '재작업이나 환불이 가능한가요?',
            'answer' => '인쇄 품질 불량이나 당사 실수의 경우 100% 재작업 또는 환불 처리됩니다. 단, 고객 파일 오류나 색상 차이는 제외됩니다.'
        ]
    ],
    'delivery' => [
        [
            'question' => '배송비는 얼마인가요?',
            'answer' => '택배는 착불이 원칙이며 기본 3,000원, 무게와 부피에 따라 추가 비용이 발생합니다. 전단지(약 22kg)의 경우 6,000원, 제주/도서산간 지역은 추가 비용이 발생합니다.'
        ],
        [
            'question' => '배송 기간은 얼마나 걸리나요?',
            'answer' => '서울/경기 지역은 1~2일, 지방은 2~3일, 제주/도서산간은 3~5일 소요됩니다. 출고 후 택배사 사정에 따라 지연될 수 있습니다.'
        ],
        [
            'question' => '배송 추적은 어떻게 하나요?',
            'answer' => '전화로 문의 하시면 송장번호를 알려드리며, 출고 시 문자로 송장번호가 발송됩니다. 마이페이지에서도 송장번호를 확인하실 수 있으며, 택배사 사이트에서 배송 조회가 가능합니다. 두손기획인쇄는 기본적으로 로젠택배를 이용하고 있습니다.'
        ],
        [
            'question' => '직접 수령이 가능한가요?',
            'answer' => '네, 가능합니다. 주문 시 배송 방법에서 "직접 수령"을 선택해주시고, 방문 전 고객센터로 연락주세요.'
        ]
    ],
    'refund' => [
        [
            'question' => '교환 및 환불 정책은 어떻게 되나요?',
            'answer' => '인쇄물 특성상 고객 변심에 의한 교환/환불은 불가합니다. 제품 불량이나 오배송의 경우에만 교환/환불이 가능합니다.'
        ],
        [
            'question' => '불량 제품은 어떻게 처리하나요?',
            'answer' => '제품 수령 후 7일 이내에 불량 사진과 함께 고객센터로 연락주세요. 확인 후 재작업 또는 환불 처리해드립니다.'
        ],
        [
            'question' => '일부만 불량인 경우는 어떻게 하나요?',
            'answer' => '불량 수량만큼 재작업 또는 협의하여 부분 환불 처리해드립니다.'
        ]
    ],
    'etc' => [
        [
            'question' => '디자인 작업도 해주시나요?',
            'answer' => '네, 디자인 작업도 가능합니다. 견적 문의 게시판이나 고객센터로 문의해주시면 상담해드립니다.'
        ],
        [
            'question' => '대량 주문 할인이 있나요?',
            'answer' => '대량 주문 시 별도 할인이 가능합니다. 견적 문의를 통해 상담받으실 수 있습니다.'
        ],
        [
            'question' => '영업시간은 어떻게 되나요?',
            'answer' => '평일 오전 9시~오후 6시, 토요일 오전 9시~오후 1시입니다. 일요일 및 공휴일은 휴무입니다.'
        ]
    ]
];

$categories = [
    'order' => ['name' => '주문/결제', 'icon' => '🛒'],
    'file' => ['name' => '파일 업로드', 'icon' => '📁'],
    'quality' => ['name' => '제작/품질', 'icon' => '⚙️'],
    'delivery' => ['name' => '배송', 'icon' => '🚚'],
    'refund' => ['name' => '교환/환불', 'icon' => '↩️'],
    'etc' => ['name' => '기타', 'icon' => '💬']
];

// 공통 헤더 포함
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>자주하는 질문 - 두손기획인쇄 고객센터</title>

    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="/css/customer-center.css">
    <style>
        /* 콘텐츠 영역 폭 제한 */
        .customer-content {
            max-width: 900px;
        }
    </style>
</head>
<body>
    <div class="customer-center-container">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/customer_sidebar.php'; ?>

        <main class="customer-content">
            <div class="breadcrumb">
                <a href="/">홈</a> &gt; <a href="/sub/customer/">고객센터</a> &gt; <span>자주하는 질문</span>
            </div>

            <div class="content-header">
                <h1>❓ 자주하는 질문</h1>
                <p class="subtitle">두손기획인쇄 이용 시 자주 묻는 질문과 답변입니다</p>
            </div>

            <div class="content-body">
                <!-- 검색 기능 -->
                <div class="faq-search-box">
                    <input type="text" id="faqSearch" class="faq-search-input" placeholder="🔍 궁금한 내용을 검색하세요...">
                </div>

                <!-- 카테고리 탭 -->
                <div class="faq-categories">
                    <button class="category-tab active" data-category="all">
                        전체
                    </button>
                    <?php foreach ($categories as $key => $cat): ?>
                        <button class="category-tab" data-category="<?php echo $key; ?>">
                            <span class="tab-icon"><?php echo $cat['icon']; ?></span>
                            <?php echo $cat['name']; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- FAQ 목록 -->
                <div class="faq-list">
                    <?php foreach ($categories as $catKey => $category): ?>
                        <?php foreach ($faqs[$catKey] as $index => $faq): ?>
                            <div class="faq-item" data-category="<?php echo $catKey; ?>">
                                <div class="faq-question">
                                    <span class="category-badge"><?php echo $category['icon']; ?> <?php echo $category['name']; ?></span>
                                    <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                                    <div class="faq-feedback">
                                        <p>이 답변이 도움이 되셨나요?</p>
                                        <button class="feedback-btn helpful" data-faq-id="<?php echo $catKey . '-' . $index; ?>">
                                            👍 도움됨
                                        </button>
                                        <button class="feedback-btn not-helpful" data-faq-id="<?php echo $catKey . '-' . $index; ?>">
                                            👎 도움안됨
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>

                <!-- 추가 문의 안내 -->
                <div class="faq-help-section">
                    <h2>원하는 답변을 찾지 못하셨나요?</h2>
                    <p>고객센터로 직접 문의해주시면 친절하게 답변드리겠습니다.</p>
                    <div class="help-buttons">
                        <a href="/sub/customer/inquiry.php" class="btn-primary">1:1 문의하기</a>
                        <a href="tel:1688-2384 / 02-2632-1830" class="btn-secondary">📞 1688-2384 / 02-2632-1830</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/customer-center.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
