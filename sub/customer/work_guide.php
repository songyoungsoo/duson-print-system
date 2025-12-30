<?php
/**
 * 작업가이드
 * 인쇄 파일 제작 가이드
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 공통 헤더 포함
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>작업가이드 - 두손기획인쇄 고객센터</title>

    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="/css/customer-center.css">
    <style>
        /* 콘텐츠 영역 폭 제한 */
        .customer-content {
            max-width: 900px;
        }
        .spec-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
        }

        .spec-table th,
        .spec-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }

        .spec-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            width: 30%;
        }

        .spec-table td {
            color: #666;
        }

        .spec-table tr:hover {
            background: #f8f9fa;
        }

        .visual-guide {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }

        .guide-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #e0e0e0;
        }

        .dimension-box {
            display: inline-block;
            padding: 40px 60px;
            background: #fff;
            border: 3px dashed #2196F3;
            border-radius: 8px;
            position: relative;
            margin: 30px 0;
        }

        .dimension-label {
            position: absolute;
            background: #2196F3;
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .dimension-label.top {
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        .dimension-label.right {
            right: -15px;
            top: 50%;
            transform: translateY(-50%);
        }

        .dimension-label.bleed {
            background: #ff9800;
        }

        .dimension-label.safe {
            background: #4caf50;
        }

        .checklist {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .checklist h4 {
            margin: 0 0 15px 0;
            color: #2e7d32;
        }

        .checklist ul {
            margin: 0;
            padding-left: 20px;
        }

        .checklist li {
            color: #2e7d32;
            margin: 8px 0;
        }

        .error-examples {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .error-card {
            background: #fff;
            border: 2px solid #f44336;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .error-card h4 {
            color: #f44336;
            margin: 0 0 15px 0;
        }

        .error-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .software-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .software-card {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            transition: all 0.2s;
        }

        .software-card:hover {
            border-color: #2196F3;
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
        }

        .software-card.recommended {
            border-color: #4caf50;
            background: #e8f5e9;
        }

        .software-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .software-name {
            font-size: 18px;
            font-weight: 600;
            margin: 10px 0;
            color: #333;
        }

        .software-desc {
            font-size: 14px;
            color: #666;
        }

        .download-section {
            background: #e3f2fd;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }

        .download-btn {
            display: inline-block;
            padding: 15px 40px;
            background: #2196F3;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px;
            transition: all 0.2s;
        }

        .download-btn:hover {
            background: #1976D2;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }
    </style>
</head>
<body>
    <div class="customer-center-container">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/customer_sidebar.php'; ?>

        <main class="customer-content">
            <div class="breadcrumb">
                <a href="/">홈</a> &gt; <a href="/sub/customer/">고객센터</a> &gt; <span>작업가이드</span>
            </div>

            <div class="content-header">
                <h1>📐 작업가이드</h1>
                <p class="subtitle">완벽한 인쇄를 위한 파일 제작 가이드</p>
            </div>

            <div class="content-body">
                <!-- 기본 원칙 -->
                <section class="guide-section">
                    <h2 class="section-title">✅ 인쇄 파일 제작 3대 원칙</h2>
                    <div class="section-content">
                        <div class="checklist">
                            <h4>1️⃣ 색상 모드: CMYK</h4>
                            <ul>
                                <li>RGB 모드는 모니터용, CMYK는 인쇄용입니다</li>
                                <li>RGB로 작업 시 인쇄 시 색상이 어둡게 변합니다</li>
                                <li>Illustrator/Photoshop에서 CMYK 모드로 작업하세요</li>
                            </ul>
                        </div>

                        <div class="checklist">
                            <h4>2️⃣ 해상도: 300dpi 이상</h4>
                            <ul>
                                <li>웹용 이미지(72dpi)는 인쇄 시 흐릿합니다</li>
                                <li>사진, 이미지는 반드시 300dpi 이상으로 준비</li>
                                <li>벡터 파일(AI)은 해상도 제한 없음</li>
                            </ul>
                        </div>

                        <div class="checklist">
                            <h4>3️⃣ 텍스트: 외곽선(Outline) 처리</h4>
                            <ul>
                                <li>폰트를 외곽선으로 변환하지 않으면 글자가 깨집니다</li>
                                <li>Illustrator: 선택 → 서체 → 외곽선 만들기</li>
                                <li>폰트 변환 후 저장 전 백업 파일 보관 권장</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 재단선과 도련 -->
                <section class="guide-section">
                    <h2 class="section-title">📏 재단선과 도련(Bleed)</h2>
                    <div class="section-content">
                        <div class="visual-guide">
                            <h3>인쇄 영역 구성</h3>
                            <div class="dimension-box">
                                <div class="dimension-label top bleed">도련 3mm</div>
                                <div class="dimension-label right">재단선</div>
                                <div style="padding: 20px; border: 2px solid #4caf50; background: rgba(76, 175, 80, 0.1);">
                                    <div class="dimension-label safe">안전영역 3mm</div>
                                    <div style="padding: 30px; text-align: center;">
                                        <strong style="font-size: 18px;">주요 내용 배치 영역</strong><br>
                                        <span style="font-size: 14px; color: #666;">텍스트, 로고 등 중요 요소</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="spec-table">
                            <tr>
                                <th>재단선 (Trim)</th>
                                <td>
                                    <strong>실제 제작 사이즈</strong><br>
                                    예: 명함 90x50mm, A4 전단지 210x297mm<br>
                                    최종 인쇄물의 정확한 크기
                                </td>
                            </tr>
                            <tr>
                                <th>도련 (Bleed)</th>
                                <td>
                                    <strong>재단선 밖 여유 영역: 상하좌우 전단지 각 1.5mm<br>
                                    명함은 각 1mm</strong><br>
                                    배경, 이미지는 도련까지 확장 필수<br>
                                    재단 오차 방지용 (흰 여백 방지)
                                </td>
                            </tr>
                            <tr>
                                <th>안전영역 (Safe Area)</th>
                                <td>
                                    <strong>재단선 안쪽 3mm</strong><br>
                                    텍스트, 로고 등 중요 요소 배치 금지 영역<br>
                                    재단 시 잘릴 수 있음
                                </td>
                            </tr>
                        </table>

                        <div class="warning-box">
                            <h4>⚠️ 주의사항</h4>
                            <ul>
                                <li>명함 제작 시: 92x52mm (재단선 90x50mm + 도련 2mm)</li>
                                <li>A4 전단지: 213x300mm (재단선 210x297mm + 도련 3mm)</li>
                                <li>스티커 제작시: 96x56mm (재단선 90x50mm + 도련 6mm)</li>
                                <li>배경색이 있는 경우 반드시 도련까지 확장</li>
                                <li>중요한 텍스트는 재단선에서 최소 5mm 안쪽 배치</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 품목별 가이드 -->
                <section class="guide-section">
                    <h2 class="section-title">📦 품목별 제작 가이드</h2>
                    <div class="section-content">
                        <h3>명함 (90x50mm)</h3>
                        <table class="spec-table">
                            <tr>
                                <th>작업 사이즈</th>
                                <td>92x52mm (도련 포함)</td>
                            </tr>
                            <tr>
                                <th>재단선</th>
                                <td>90x50mm</td>
                            </tr>
                            <tr>
                                <th>권장 용지</th>
                                <td>스노우지 250g, 랑데뷰 250g</td>
                            </tr>
                            <tr>
                                <th>색상 모드</th>
                                <td>CMYK</td>
                            </tr>
                            <tr>
                                <th>해상도</th>
                                <td>300dpi 이상</td>
                            </tr>
                            <tr>
                                <th>파일 형식</th>
                                <td>AI, PDF (권장), JPG/PNG (300dpi)</td>
                            </tr>
                        </table>

                        <h3>전단지 (A4)</h3>
                        <table class="spec-table">
                            <tr>
                                <th>작업 사이즈</th>
                                <td>213x300mm (도련 포함)</td>
                            </tr>
                            <tr>
                                <th>재단선</th>
                                <td>210x297mm (A4)</td>
                            </tr>
                            <tr>
                                <th>권장 용지</th>
                                <td>아트지 150g, 스노우지 150g</td>
                            </tr>
                            <tr>
                                <th>색상 모드</th>
                                <td>CMYK</td>
                            </tr>
                            <tr>
                                <th>해상도</th>
                                <td>300dpi 이상</td>
                            </tr>
                            <tr>
                                <th>후가공</th>
                                <td>단면/양면, 코팅(유광/무광)</td>
                            </tr>
                        </table>

                        <h3>스티커</h3>
                        <table class="spec-table">
                            <tr>
                                <th>작업 사이즈</th>
                                <td>원하는 사이즈 + 도련 3mm</td>
                            </tr>
                            <tr>
                                <th>재단선</th>
                                <td>칼선(재단선) 레이어 별도 제작</td>
                            </tr>
                            <tr>
                                <th>권장 용지</th>
                                <td>아트지 (유광/무광), 투명 PET</td>
                            </tr>
                            <tr>
                                <th>칼선 설정</th>
                                <td>별도 레이어, 선 색상: M100%, 선 두께: 0.1pt</td>
                            </tr>
                            <tr>
                                <th>특이사항</th>
                                <td>복잡한 형태는 별도 문의</td>
                            </tr>
                        </table>
                    </div>
                </section>

                <!-- 권장 소프트웨어 -->
                <section class="guide-section">
                    <h2 class="section-title">💻 권장 디자인 소프트웨어</h2>
                    <div class="section-content">
                        <div class="software-grid">
                            <div class="software-card recommended">
                                <div class="software-icon">🎨</div>
                                <div class="software-name">Adobe Illustrator</div>
                                <div class="software-desc">
                                    벡터 기반 디자인<br>
                                    명함, 전단지, 로고<br>
                                    <strong style="color: #4caf50;">최우선 권장</strong>
                                </div>
                            </div>

                            <div class="software-card recommended">
                                <div class="software-icon">🖼️</div>
                                <div class="software-name">Adobe Photoshop</div>
                                <div class="software-desc">
                                    이미지 편집<br>
                                    사진 보정, 합성<br>
                                    <strong style="color: #4caf50;">이미지 작업 권장</strong>
                                </div>
                            </div>

                            <div class="software-card">
                                <div class="software-icon">📄</div>
                                <div class="software-name">Adobe InDesign</div>
                                <div class="software-desc">
                                    편집 디자인<br>
                                    책자, 카탈로그<br>
                                    페이지 레이아웃
                                </div>
                            </div>

                            <div class="software-card">
                                <div class="software-icon">⚠️</div>
                                <div class="software-name">한글(HWP)</div>
                                <div class="software-desc">
                                    PDF 변환 필요<br>
                                    품질 저하 가능
                                </div>
                            </div>

                            <div class="software-card">
                                <div class="software-icon">⚠️</div>
                                <div class="software-name">MS Word/PPT</div>
                                <div class="software-desc">
                                    PDF 변환 필요<br>
                                    폰트/이미지 깨짐
                                </div>
                            </div>

                            <div class="software-card">
                                <div class="software-icon">🆓</div>
                                <div class="software-name">무료 툴</div>
                                <div class="software-desc">
                                    Canva, Pixlr 등<br>
                                    <strong style="color: #ff9800;">주의 필요</strong><br>
                                    PDF 또는 JPG 300dpi
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 자주 발생하는 오류 -->
                <section class="guide-section">
                    <h2 class="section-title">❌ 자주 발생하는 파일 오류</h2>
                    <div class="section-content">
                        <div class="error-examples">
                            <div class="error-card">
                                <div class="error-icon">🌈</div>
                                <h4>RGB 색상 모드</h4>
                                <p>인쇄 시 색상이 어둡고 탁해집니다</p>
                                <p><strong>해결:</strong> CMYK로 변경</p>
                            </div>

                            <div class="error-card">
                                <div class="error-icon">🔍</div>
                                <h4>저해상도 이미지</h4>
                                <p>72dpi 웹 이미지는 흐릿하게 인쇄</p>
                                <p><strong>해결:</strong> 300dpi 이상</p>
                            </div>

                            <div class="error-card">
                                <div class="error-icon">🔤</div>
                                <h4>폰트 미변환</h4>
                                <p>글자가 깨지거나 다른 폰트로 변경</p>
                                <p><strong>해결:</strong> 외곽선 처리</p>
                            </div>

                            <div class="error-card">
                                <div class="error-icon">✂️</div>
                                <h4>도련 미설정</h4>
                                <p>재단 시 흰 여백 발생</p>
                                <p><strong>해결:</strong> 품목별 mm 도련 확인</p>
                            </div>

                            <div class="error-card">
                                <div class="error-icon">⚠️</div>
                                <h4>안전영역 침범</h4>
                                <p>중요 텍스트가 잘림</p>
                                <p><strong>해결:</strong> 재단선 안쪽 3mm</p>
                            </div>

                            <div class="error-card">
                                <div class="error-icon">🖤</div>
                                <h4>4도 블랙 사용</h4>
                                <p>K100% 대신 CMYK 혼합 블랙</p>
                                <p><strong>해결:</strong> K100% 단일 블랙</p>
                            </div>
                        </div>

                        <div class="warning-box">
                            <h4>⚠️ 파일 제출 전 최종 체크리스트</h4>
                            <ul>
                                <li>✅ 색상 모드: CMYK 확인</li>
                                <li>✅ 해상도: 300dpi 이상</li>
                                <li>✅ 폰트: 외곽선 변환 완료</li>
                                <li>✅ 도련: 상하좌우 품목별 mm 설정</li>
                                <li>✅ 안전영역: 중요 요소 재단선 안쪽 3mm 이상</li>
                                <li>✅ 파일 형식: AI 또는 PDF (권장)</li>
                                <li>✅ 파일 크기: 100MB 이하</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 관련 링크 -->
                <div class="related-links">
                    <h3>더 궁금하신 사항이 있으신가요?</h3>
                    <div class="link-buttons">
                        <a href="/sub/customer/work_rules.php" class="btn-secondary">인쇄작업규약</a>
                        <a href="/sub/customer/faq.php" class="btn-secondary">자주하는 질문</a>
                        <a href="/sub/customer/inquiry.php" class="btn-primary">1:1 문의하기</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/customer-center.js"></script>
</body>
</html>
