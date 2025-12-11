<?php
/**
 * 홈페이지 이용방법
 * 첫 주문 고객을 위한 step-by-step 가이드
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// 관리자 권한 확인
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = mysqli_prepare($db, "SELECT level FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($user = mysqli_fetch_assoc($result)) {
        $is_admin = ($user['level'] >= 5);
    }
    mysqli_stmt_close($stmt);
}

// 공지사항 조회 (최근 5개, 중요 공지 우선)
$notices = [];
$query = "SELECT id, title, content, is_important, view_count, created_at
          FROM notices
          ORDER BY is_important DESC, created_at DESC
          LIMIT 5";
$result = mysqli_query($db, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notices[] = $row;
    }
}

// 공통 헤더 포함
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>홈페이지 이용방법 - 두손기획인쇄 고객센터</title>

    <!-- 공통 스타일 -->
    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="/css/customer-center.css">

    <style>
        /* 공지사항 섹션 스타일 */
        .notice-section {
            background: #f8f9fa;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .notice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid #4a90e2;
        }

        .notice-header h2 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
            font-weight: 600;
        }

        .notice-admin-btn {
            background: #4a90e2;
            color: white;
            padding: 6px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .notice-admin-btn:hover {
            background: #357abd;
        }

        .notice-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notice-item {
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            margin-bottom: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .notice-item.active {
            border-color: #4a90e2;
            box-shadow: 0 2px 8px rgba(74, 144, 226, 0.1);
        }

        .notice-title-row {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notice-title-row:hover {
            background: #f8f9fa;
        }

        .notice-item.active .notice-title-row {
            background: #f0f7ff;
        }

        .notice-badge {
            background: #ff4757;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 10px;
            white-space: nowrap;
        }

        .notice-title {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
            color: #2c3e50;
        }

        .notice-meta {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: #7f8c8d;
            margin-left: 12px;
        }

        .notice-content {
            display: none;
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #e1e8ed;
            font-size: 13px;
            line-height: 1.6;
            color: #555;
        }

        .notice-item.active .notice-content {
            display: block;
        }

        .notice-content p {
            margin: 0 0 10px 0;
        }

        .notice-content p:last-child {
            margin-bottom: 0;
        }

        .no-notices {
            text-align: center;
            padding: 30px;
            color: #95a5a6;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .notice-meta {
                display: none;
            }

            .notice-title {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="customer-center-container">
        <!-- 좌측 사이드바 -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/customer_sidebar.php'; ?>

        <!-- 메인 컨텐츠 -->
        <main class="customer-content">
            <div class="breadcrumb">
                <a href="/">홈</a> &gt; <a href="/sub/customer/">고객센터</a> &gt; <span>홈페이지 이용방법</span>
            </div>

            <div class="content-header">
                <h1>📖 홈페이지 이용방법</h1>
                <p class="subtitle">두손기획인쇄 온라인 인쇄 서비스 이용 가이드</p>
            </div>

            <div class="content-body">
                <!-- 공지사항 섹션 -->
                <section id="notice" class="notice-section">
                    <div class="notice-header">
                        <h2>📢 공지사항</h2>
                        <?php if ($is_admin): ?>
                            <a href="/sub/customer/notice_admin.php" class="notice-admin-btn">공지사항 관리</a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($notices)): ?>
                        <ul class="notice-list">
                            <?php foreach ($notices as $notice): ?>
                                <li class="notice-item" onclick="toggleNotice(<?php echo $notice['id']; ?>)">
                                    <div class="notice-title-row">
                                        <?php if ($notice['is_important']): ?>
                                            <span class="notice-badge">중요</span>
                                        <?php endif; ?>
                                        <div class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></div>
                                        <div class="notice-meta">
                                            <span>👁️ <?php echo number_format($notice['view_count']); ?></span>
                                            <span><?php echo date('Y-m-d', strtotime($notice['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="notice-content">
                                        <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="no-notices">등록된 공지사항이 없습니다.</div>
                    <?php endif; ?>
                </section>
                <!-- 회원가입 섹션 -->
                <section class="guide-section">
                    <h2 class="section-title">
                        <span class="step-number">1</span>
                        회원가입
                    </h2>
                    <div class="section-content">
                        <div class="info-box">
                            <p><strong>회원가입 없이도 주문 가능합니다!</strong></p>
                            <p>하지만 회원 가입 시 다양한 혜택을 받으실 수 있습니다.</p>
                        </div>

                        <h3>회원 가입 혜택</h3>
                        <ul class="benefit-list">
                            <li>✅ 주문 내역 조회 및 관리</li>
                            <li>✅ 배송지 저장 기능</li>
                            <li>✅ 빠른 재주문</li>
                        </ul>

                        <div class="action-box">
                            <a href="/member/join.php" class="btn-primary">회원가입 하러가기</a>
                        </div>
                    </div>
                </section>

                <!-- 주문 프로세스 섹션 -->
                <section class="guide-section">
                    <h2 class="section-title">
                        <span class="step-number">2</span>
                        주문 프로세스
                    </h2>
                    <div class="section-content">
                        <div class="process-flow">
                            <div class="process-step">
                                <div class="step-icon">🛍️</div>
                                <h3>제품 선택</h3>
                                <p>원하는 인쇄 제품을 선택합니다</p>
                                <ul>
                                    <li>전단지</li>
                                    <li>명함</li>
                                    <li>스티커</li>
                                    <li>봉투 등</li>
                                </ul>
                            </div>

                            <div class="arrow">→</div>

                            <div class="process-step">
                                <div class="step-icon">⚙️</div>
                                <h3>옵션 선택</h3>
                                <p>제품 옵션을 설정합니다</p>
                                <ul>
                                    <li>규격 (사이즈)</li>
                                    <li>수량</li>
                                    <li>용지 종류</li>
                                    <li>인쇄 색상</li>
                                    <li>추가 옵션</li>
                                </ul>
                            </div>

                            <div class="arrow">→</div>

                            <div class="process-step">
                                <div class="step-icon">📁</div>
                                <h3>파일 업로드</h3>
                                <p>인쇄할 디자인 파일을 업로드합니다</p>
                                <ul>
                                    <li>AI, PDF 권장</li>
                                    <li>JPG, PNG 가능</li>
                                    <li>최대 100MB</li>
                                </ul>
                            </div>

                            <div class="arrow">→</div>

                            <div class="process-step">
                                <div class="step-icon">🛒</div>
                                <h3>장바구니 확인</h3>
                                <p>주문 내용을 확인합니다</p>
                                <ul>
                                    <li>제품 정보</li>
                                    <li>수량</li>
                                    <li>가격</li>
                                    <li>추가 옵션</li>
                                </ul>
                            </div>

                            <div class="arrow">→</div>

                            <div class="process-step">
                                <div class="step-icon">💳</div>
                                <h3>결제</h3>
                                <p>결제 방법을 선택합니다</p>
                                <ul>
                                    <li>무통장입금</li>
                                    <li>카드결제</li>
                                    <li>계좌이체</li>
                                </ul>
                            </div>

                            <div class="arrow">→</div>

                            <div class="process-step">
                                <div class="step-icon">🎉</div>
                                <h3>제작 및 배송</h3>
                                <p>인쇄 및 배송이 진행됩니다</p>
                                <ul>
                                    <li>입금 확인</li>
                                    <li>인쇄 제작</li>
                                    <li>품질 검사</li>
                                    <li>배송 출고</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 파일 업로드 가이드 -->
                <section class="guide-section">
                    <h2 class="section-title">
                        <span class="step-number">3</span>
                        파일 업로드 가이드
                    </h2>
                    <div class="section-content">
                        <h3>지원 파일 형식</h3>
                        <div class="file-formats">
                            <div class="format-item recommended">
                                <span class="format-icon">⭐</span>
                                <strong>AI</strong>
                                <p>권장</p>
                            </div>
                            <div class="format-item recommended">
                                <span class="format-icon">⭐</span>
                                <strong>PDF</strong>
                                <p>권장</p>
                            </div>
                            <div class="format-item">
                                <span class="format-icon">✓</span>
                                <strong>JPG</strong>
                                <p>가능</p>
                            </div>
                            <div class="format-item">
                                <span class="format-icon">✓</span>
                                <strong>PNG</strong>
                                <p>가능</p>
                            </div>
                        </div>

                        <div class="warning-box">
                            <h4>⚠️ 파일 업로드 시 주의사항</h4>
                            <ul>
                                <li><strong>해상도:</strong> 300dpi 이상 권장</li>
                                <li><strong>색상 모드:</strong> CMYK 모드 (RGB는 색상 차이 발생 가능)</li>
                                <li><strong>파일 크기:</strong> 최대 100MB</li>
                                <li><strong>폰트:</strong> 폰트 아웃라인 처리 필수</li>
                                <li><strong>재단선:</strong> 도련 3mm 설정 권장</li>
                            </ul>
                        </div>

                        <div class="action-box">
                            <a href="/sub/customer/work_guide.php" class="btn-secondary">작업 가이드 자세히 보기</a>
                        </div>
                    </div>
                </section>

                <!-- 주문 확인 및 수정 -->
                <section class="guide-section">
                    <h2 class="section-title">
                        <span class="step-number">4</span>
                        주문 확인 및 수정
                    </h2>
                    <div class="section-content">
                        <h3>마이페이지에서 확인하기</h3>
                        <ol class="step-list">
                            <li>로그인 후 상단 메뉴에서 <strong>마이페이지</strong> 클릭</li>
                            <li><strong>주문내역</strong> 메뉴 선택</li>
                            <li>주문 상태 및 세부 정보 확인</li>
                        </ol>

                        <h3>주문 상태 안내</h3>
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th>상태</th>
                                    <th>설명</th>
                                    <th>취소/변경 가능 여부</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="status-badge pending">입금대기</span></td>
                                    <td>입금 확인 중</td>
                                    <td class="text-success">✓ 가능</td>
                                </tr>
                                <tr>
                                    <td><span class="status-badge processing">제작중</span></td>
                                    <td>인쇄 작업 진행 중</td>
                                    <td class="text-danger">✗ 불가</td>
                                </tr>
                                <tr>
                                    <td><span class="status-badge shipping">배송중</span></td>
                                    <td>택배 배송 중</td>
                                    <td class="text-danger">✗ 불가</td>
                                </tr>
                                <tr>
                                    <td><span class="status-badge completed">배송완료</span></td>
                                    <td>배송 완료</td>
                                    <td class="text-danger">✗ 불가</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="info-box">
                            <p><strong>주문 취소 및 변경</strong></p>
                            <p>제작 시작 전(입금대기 상태)에만 가능합니다.</p>
                            <p>고객센터(1688-2384 / 02-2632-1830)로 문의해주세요.</p>
                        </div>
                    </div>
                </section>

                <!-- 자주하는 질문 링크 -->
                <section class="guide-section related-links">
                    <h2>더 궁금하신 사항이 있으신가요?</h2>
                    <div class="link-cards">
                        <a href="/sub/customer/faq.php" class="link-card">
                            <span class="card-icon">❓</span>
                            <h3>자주하는 질문</h3>
                            <p>자주 묻는 질문과 답변을 확인하세요</p>
                        </a>
                        <a href="/sub/customer/inquiry.php" class="link-card">
                            <span class="card-icon">✉️</span>
                            <h3>1:1 문의하기</h3>
                            <p>궁금한 사항을 직접 문의하세요</p>
                        </a>
                        <a href="/sub/customer/work_guide.php" class="link-card">
                            <span class="card-icon">🎨</span>
                            <h3>작업 가이드</h3>
                            <p>제품별 디자인 작업 가이드를 확인하세요</p>
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="/js/customer-center.js"></script>
    <script>
        // 공지사항 토글 및 조회수 증가
        function toggleNotice(id) {
            const item = event.currentTarget;
            const wasActive = item.classList.contains('active');

            // 모든 공지사항 접기
            document.querySelectorAll('.notice-item').forEach(el => {
                el.classList.remove('active');
            });

            // 클릭한 공지사항 토글
            if (!wasActive) {
                item.classList.add('active');

                // 조회수 증가 (AJAX)
                fetch('/sub/customer/notice_view.php?id=' + id)
                    .then(response => response.text())
                    .catch(error => console.log('조회수 업데이트 실패:', error));
            }
        }

        // URL 해시 확인하여 공지사항 섹션으로 스크롤
        window.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#notice') {
                const noticeSection = document.getElementById('notice');
                if (noticeSection) {
                    // 부드럽게 스크롤
                    setTimeout(() => {
                        noticeSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

                        // 공지사항 섹션 하이라이트 효과
                        noticeSection.style.transition = 'background-color 0.5s ease';
                        noticeSection.style.backgroundColor = '#e3f2fd';

                        setTimeout(() => {
                            noticeSection.style.backgroundColor = '#f8f9fa';
                        }, 1500);

                        // 첫 번째 공지사항 자동으로 열기
                        const firstNotice = document.querySelector('.notice-item');
                        if (firstNotice) {
                            setTimeout(() => {
                                firstNotice.classList.add('active');
                            }, 800);
                        }
                    }, 100);
                }
            }
        });
    </script>
</body>
</html>
