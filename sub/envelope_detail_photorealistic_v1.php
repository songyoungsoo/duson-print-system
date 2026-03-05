<?php
/**
 * 봉투 상세페이지 - Photorealistic Edition (13-section structure)
 * Created: 2026-03-05
 * Version: v1
 */

require_once "/var/www/html/includes/functions.php";
require_once "/var/www/html/db.php";

// 데이터베이스 연결
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 봉투 정보
$type_id = isset($_GET['type']) ? intval($_GET['type']) : 466;
$section_id = isset($_GET['section']) ? intval($_GET['section']) : 0;

// 봉투 종류
$type_query = "SELECT no, title FROM mlangprintauto_transactioncate
               WHERE Ttable='Envelope' AND no='" . intval($type_id) . "' LIMIT 1";
$type_result = mysqli_query($db, $type_query);
$type_data = $type_result ? mysqli_fetch_assoc($type_result) : null;

// 포토리얼리스틱 이미지
$photorealistic_image = "https://a.mktgcdn.com/p/oz9_kDwLFrbVvOL8jH3-f2m-weuSEDgGEKgmLd0Kbo0/1280x1600.jpg";

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $type_data ? $type_data['title'] . ' - 봉투 상세페이지' : '봉투 상세페이지'; ?></title>
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <style>
        /* 기본 설정 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', sans-serif;
            line-height: 1.8;
            color: #1a1a1a;
        }

        /* 섹션 공통 스타일 */
        .section {
            min-height: 800px;
            padding: 80px 100px;
            background: #f8f9fa;
        }

        .section-title {
            font-size: 48px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 60px;
            color: #ff9800;
        }

        .section-content {
            max-width: 1100px;
            margin: 0 auto;
        }

        /* 섹션 1: 제품 개요 */
        .section-1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .section-1 h1 {
            font-size: 72px;
            margin-bottom: 30px;
        }

        .section-1 p {
            font-size: 28px;
            margin-bottom: 60px;
        }

        .main-image {
            max-width: 100%;
            height: 600px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .btn-cta {
            display: inline-block;
            background: #ff9800;
            color: white;
            padding: 20px 60px;
            font-size: 24px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            margin: 20px;
            transition: all 0.3s ease;
        }

        .btn-cta:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 152, 0, 0.4);
        }

        /* 섹션 2: 주요 특징 */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .feature-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .feature-desc {
            font-size: 18px;
            color: #666;
        }

        /* 섹션 3: 제품 스펙 */
        .spec-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .spec-table th,
        .spec-table td {
            padding: 25px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .spec-table th {
            background: #ff9800;
            color: white;
            font-size: 24px;
            font-weight: 600;
        }

        .spec-table td {
            font-size: 20px;
        }

        .spec-table tr:last-child td {
            border-bottom: none;
        }

        /* 섹션 4: 사용 가이드 */
        .guide-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .guide-step {
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: #ff9800;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            margin: 0 auto 20px;
        }

        .step-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .step-desc {
            font-size: 18px;
            color: #666;
        }

        /* 섹션 5: 라이프스타일 */
        .lifestyle-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
        }

        .lifestyle-item {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            height: 500px;
        }

        .lifestyle-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .lifestyle-item:hover img {
            transform: scale(1.05);
        }

        .lifestyle-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 30px;
            color: white;
        }

        .lifestyle-title {
            font-size: 28px;
            font-weight: 600;
        }

        /* 섹션 6: 비교 */
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
        }

        .comparison-table th {
            background: #ff9800;
            color: white;
            padding: 20px;
            font-size: 24px;
            text-align: center;
        }

        .comparison-table td {
            padding: 20px;
            font-size: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .comparison-table tr.highlight {
            background: #fff3e0;
        }

        /* 섹션 7: 서비스 */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .service-card {
            text-align: center;
            padding: 40px;
        }

        .service-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .service-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .service-desc {
            font-size: 18px;
            color: #666;
        }

        /* 섹션 8-9: 고객 후기 및 FAQ */
        .testimonial-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .testimonial-text {
            font-size: 22px;
            font-style: italic;
            margin-bottom: 20px;
        }

        .testimonial-author {
            text-align: right;
            font-weight: 600;
        }

        .faq-item {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            cursor: pointer;
        }

        .faq-question {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .faq-answer {
            font-size: 18px;
            color: #666;
        }

        /* 섹션 10-13: 제작 프로세스, 배송, 지원, CTA */
        .process-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .process-step {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 20px;
        }

        .process-step-number {
            font-size: 48px;
            font-weight: 700;
            color: #ff9800;
            margin-bottom: 15px;
        }

        .process-step-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .process-step-desc {
            font-size: 18px;
            color: #666;
        }

        /* 반응형 */
        @media (max-width: 768px) {
            .section {
                padding: 60px 20px;
            }

            .section-title {
                font-size: 32px;
            }

            .features-grid,
            .guide-steps,
            .lifestyle-grid,
            .services-grid,
            .process-steps {
                grid-template-columns: 1fr;
            }

            .main-image {
                height: 400px;
            }

            .btn-cta {
                padding: 15px 40px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- 섹션 1: 제품 개요 -->
    <section class="section-1">
        <h1><?php echo $type_data ? htmlspecialchars($type_data['title']) : '봉투'; ?></h1>
        <p>포토리얼리스틱 디자인으로 선보이는 프리미엄 봉투</p>
        <img src="<?php echo htmlspecialchars($photorealistic_image); ?>"
             alt="봉투 포토리얼리스틱 이미지"
             class="main-image"
             loading="lazy">
        <div>
            <a href="#features" class="btn-cta">주요 특징</a>
            <a href="#specs" class="btn-cta">제품 스펙</a>
        </div>
    </section>

    <!-- 섹션 2: 주요 특징 -->
    <section class="section" id="features">
        <h2 class="section-title">주요 특징</h2>
        <div class="section-content">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3 class="feature-title">고품질 포토리얼리스틱</h3>
                    <p class="feature-desc">HD 품질 이미지로 봉투의 모든 디테일을 표현</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3 class="feature-title">다양한 사이즈 옵션</h3>
                    <p class="feature-desc">규격 및 비규격 모든 크기 지원</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3 class="feature-title">빠른 제작</h3>
                    <p class="feature-desc">4~5일 소요, 긴급 주문 가능</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3 class="feature-title">로고 인쇄 지원</h3>
                    <p class="feature-desc">단면/양면 인쇄 가능</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3 class="feature-title">합리적인 가격</h3>
                    <p class="feature-desc">업계 최저가격 제공</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3 class="feature-title">빠른 배송</h3>
                    <p class="feature-desc">택배 착불, 2~3일 소요</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 섹션 3: 제품 스펙 -->
    <section class="section" id="specs">
        <h2 class="section-title">제품 스펙</h2>
        <div class="section-content">
            <table class="spec-table">
                <thead>
                    <tr>
                        <th>사이즈</th>
                        <th>재질</th>
                        <th>인쇄 옵션</th>
                        <th>수량 옵션</th>
                        <th>기본가</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>245 × 330mm</td>
                        <td>110g 체크레자크</td>
                        <td>단면/양면</td>
                        <td>500매 / 1000매</td>
                        <td><?php echo isset($price_data['price']) ? number_format($price_data['price']) : '5,500원'; ?></td>
                    </tr>
                    <tr>
                        <td>330 × 243mm</td>
                        <td>120g 모조</td>
                        <td>단면/양면</td>
                        <td>500매 / 1000매</td>
                        <td><?php echo isset($price_data['price']) ? number_format($price_data['price']) : '4,500원'; ?></td>
                    </tr>
                    <tr>
                        <td>381 × 254mm</td>
                        <td>110g 레자크</td>
                        <td>단면/양면</td>
                        <td>500매 / 1000매</td>
                        <td><?php echo isset($price_data['price']) ? number_format($price_data['price']) : '6,500원'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 섹션 4: 사용 가이드 -->
    <section class="section">
        <h2 class="section-title">사용 가이드</h2>
        <div class="section-content">
            <div class="guide-steps">
                <div class="guide-step">
                    <div class="step-number">1</div>
                    <h3 class="step-title">디자인 제작</h3>
                    <p class="step-desc">로고 및 문구 디자인</p>
                </div>
                <div class="guide-step">
                    <div class="step-number">2</div>
                    <h3 class="step-title">파일 제출</h3>
                    <p class="step-desc">PDF/AI 파일 업로드</p>
                </div>
                <div class="guide-step">
                    <div class="step-number">3</div>
                    <h3 class="step-title">견적 확인</h3>
                    <p class="step-desc">실시간 가격 계산</p>
                </div>
                <div class="guide-step">
                    <div class="step-number">4</div>
                    <h3 class="step-title">주문 완료</h3>
                    <p class="step-desc">결제 후 제작 시작</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 섹션 5: 라이프스타일 -->
    <section class="section">
        <h2 class="section-title">라이프스타일</h2>
        <div class="section-content">
            <div class="lifestyle-grid">
                <div class="lifestyle-item">
                    <img src="<?php echo htmlspecialchars($photorealistic_image); ?>"
                         alt="비즈니스 문서 발송"
                         loading="lazy">
                    <div class="lifestyle-overlay">
                        <h3 class="lifestyle-title">비즈니스 문서 발송</h3>
                    </div>
                </div>
                <div class="lifestyle-item">
                    <img src="<?php echo htmlspecialchars($photorealistic_image); ?>"
                         alt="개인 서류 보관"
                         loading="lazy">
                    <div class="lifestyle-overlay">
                        <h3 class="lifestyle-title">개인 서류 보관</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 섹션 6: 비교 -->
    <section class="section">
        <h2 class="section-title">제품 비교</h2>
        <div class="section-content">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>특징</th>
                        <th>본 제품</th>
                        <th>경쟁사</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="highlight">
                        <td>품질</td>
                        <td>★ 5.0</td>
                        <td>★ 4.5</td>
                    </tr>
                    <tr class="highlight">
                        <td>가격</td>
                        <td>최저가</td>
                        <td>보통</td>
                    </tr>
                    <tr class="highlight">
                        <td>제작 시간</td>
                        <td>4~5일</td>
                        <td>5~7일</td>
                    </tr>
                    <tr class="highlight">
                        <td>배송</td>
                        <td>택배 착불</td>
                        <td>선불</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 섹션 7: 서비스 -->
    <section class="section">
        <h2 class="section-title">추가 서비스</h2>
        <div class="section-content">
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">🎨</div>
                    <h3 class="service-title">로고 인쇄</h3>
                    <p class="service-desc">단면/양면 4도 인쇄 지원</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">✂️</div>
                    <h3 class="service-title">편집 서비스</h3>
                    <p class="service-desc">디자인 도구 지원 (무료)</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🚚</div>
                    <h3 class="service-title">배송 서비스</h3>
                    <p class="service-desc">택배 착불 / 2~3일 소요</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 섹션 8: 고객 후기 -->
    <section class="section">
        <h2 class="section-title">고객 후기</h2>
        <div class="section-content">
            <div class="testimonial-card">
                <p class="testimonial-text">"품질이 예상보다 훨씬 좋습니다! 배송도 빠르고 가격도 합리적이네요."</p>
                <p class="testimonial-author">- 김철수 님</p>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">"로고 인쇄가 깔끔하게 잘 나왔습니다. 다음에도 또 이용하겠습니다."</p>
                <p class="testimonial-author">- 이영희 님</p>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">"제작 기간이 짧아서 놀랐습니다. 원하는 대로 잘 나왔어요."</p>
                <p class="testimonial-author">- 박민수 님</p>
            </div>
        </div>
    </section>

    <!-- 섹션 9: FAQ -->
    <section class="section">
        <h2 class="section-title">FAQ</h2>
        <div class="section-content">
            <div class="faq-item">
                <h3 class="faq-question">Q. 제작 기간은 얼마나 걸리나요?</h3>
                <p class="faq-answer">일반적으로 4~5일이며, 긴급 주문의 경우 2~3일로 단축 가능합니다.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">Q. 배송 방법은 어떻게 되나요?</h3>
                <p class="faq-answer">택배 착불 배송이며, 배송 기간은 2~3일 소요됩니다.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">Q. 로고 인쇄는 얼마에 가능한가요?</h3>
                <p class="faq-answer">단면 4도 인쇄는 기본 포함되며, 양면 인쇄는 추가 비용이 발생합니다.</p>
            </div>
        </div>
    </section>

    <!-- 섹션 10: 제작 프로세스 -->
    <section class="section">
        <h2 class="section-title">제작 프로세스</h2>
        <div class="section-content">
            <div class="process-steps">
                <div class="process-step">
                    <div class="process-step-number">1</div>
                    <h3 class="process-step-title">디자인</h3>
                    <p class="process-step-desc">디자인 제작 및 파일 확인</p>
                </div>
                <div class="process-step">
                    <div class="process-step-number">2</div>
                    <h3 class="process-step-title">견적</h3>
                    <p class="process-step-desc">실시간 가격 계산</p>
                </div>
                <div class="process-step">
                    <div class="process-step-number">3</div>
                    <h3 class="process-step-title">제작</h3>
                    <p class="process-step-desc">인쇄 및 후가공</p>
                </div>
                <div class="process-step">
                    <div class="process-step-number">4</div>
                    <h3 class="process-step-title">배송</h3>
                    <p class="process-step-desc">완료품 배송</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 섹션 11: 배송 정보 -->
    <section class="section">
        <h2 class="section-title">배송 정보</h2>
        <div class="section-content">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🚚</div>
                    <h3 class="feature-title">배송 방법</h3>
                    <p class="feature-desc">택배 착불</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⏰</div>
                    <h3 class="feature-title">배송 기간</h3>
                    <p class="feature-desc">2~3일 소요</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3 class="feature-title">배송비</h3>
                    <p class="feature-desc">별도 (부가세 포함)</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 섹션 12: 고객 지원 -->
    <section class="section">
        <h2 class="section-title">고객 지원</h2>
        <div class="section-content">
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">📞</div>
                    <h3 class="service-title">전화 문의</h3>
                    <p class="service-desc">02-2632-1830</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">✉️</div>
                    <h3 class="service-title">이메일</h3>
                    <p class="service-desc">yeongsu32@gmail.com</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">💻</div>
                    <h3 class="service-title">실시간 챗</h3>
                    <p class="service-desc">CS 상담원 운영</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 섹션 13: 마무리 CTA -->
    <section class="section" style="background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%); color: white;">
        <h2 class="section-title">지금 주문하세요</h2>
        <div class="section-content" style="text-align: center;">
            <p style="font-size: 28px; margin-bottom: 40px;">
                포토리얼리스틱 봉투로 비즈니스 문서를 완벽하게 대비하세요
            </p>
            <div>
                <a href="../../mlangprintauto/envelope/index.php?type=<?php echo $type_id; ?>"
                   class="btn-cta" style="background: white; color: #ff9800;">
                    견적 상세 보기
                </a>
                <a href="../../mlangprintauto/envelope/index.php?type=<?php echo $type_id; ?>&section=<?php echo $section_id; ?>"
                   class="btn-cta">
                    주문하기
                </a>
            </div>
        </div>
    </section>
</body>
</html>
