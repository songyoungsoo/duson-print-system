<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>디자인 비용 안내 - 두손기획인쇄</title>

    <!-- Noto Sans KR 폰트 -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #2d3748;
            line-height: 1.6;
            min-height: 100vh;
            font-size: 14px;
        }

        .container {
            max-width: 990px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            min-height: 100vh;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
            text-align: center;
            color: white;
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .page-subtitle {
            font-size: 1.1rem;
            font-weight: 400;
            opacity: 0.9;
        }

        .main-content {
            display: flex;
            min-height: calc(100vh - 200px);
        }

        .left-sidebar {
            width: 160px;
            background: #f8f9fa;
            border-right: 1px solid #e9ecef;
            padding: 1rem 0.5rem;
        }

        .content-area {
            flex: 1;
            padding: 2rem;
            background: white;
        }

        /* 우측 사이드바는 includes/right_sidebar.php에서 처리됨 */

        .navigation-bar {
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            display: flex;
            justify-content: center;
            padding: 0;
            border-bottom: 2px solid #e9ecef;
        }

        .nav-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            border: none;
            transition: all 0.3s ease;
            border-right: 1px solid rgba(255,255,255,0.2);
        }

        .nav-item:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .service-section {
            margin-bottom: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .service-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .service-content {
            padding: 1.5rem;
        }

        .price-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .price-table th {
            background: linear-gradient(135deg, #e6f3ff 0%, #b3d9ff 100%);
            color: #2d3748;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            border: 1px solid #cbd5e0;
        }

        .price-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #cbd5e0;
            background: #f8f9fa;
        }

        .price-highlight {
            color: #e53e3e;
            font-weight: 700;
        }

        .service-note {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #4a5568;
        }

        .logo-tiers {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .tier-card {
            background: #f0f8ff;
            border: 2px solid #87ceeb;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }

        .tier-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .tier-price {
            font-size: 1.3rem;
            font-weight: 900;
            color: #e53e3e;
            margin-bottom: 0.5rem;
        }

        .tier-description {
            font-size: 13px;
            color: #4a5568;
        }

        .flyer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .flyer-size {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
        }

        .flyer-size-title {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
            text-align: center;
            background: linear-gradient(135deg, #e6f3ff 0%, #b3d9ff 100%);
            padding: 8px;
            border-radius: 4px;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #cbd5e0;
        }

        .price-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }

            .left-sidebar, .right-sidebar {
                width: 100%;
                order: 2;
            }

            .content-area {
                order: 1;
                padding: 1rem;
            }

            .navigation-bar {
                flex-wrap: wrap;
            }

            .nav-item {
                font-size: 12px;
                padding: 8px 12px;
            }

            .logo-tiers {
                grid-template-columns: 1fr;
            }

            .flyer-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- 헤더 섹션 -->
        <div class="header-section">
            <h1 class="page-title">디자인 비용 안내</h1>
            <p class="page-subtitle">두손기획인쇄 | 전문 디자인 서비스</p>
        </div>

        <!-- 네비게이션 바 -->
        <div class="navigation-bar">
            <a href="seosig.htm" class="nav-item">서식</a>
            <a href="catalog.htm" class="nav-item">카탈로그</a>
            <a href="brochure.htm" class="nav-item">브로슈어</a>
            <a href="leaflet.htm" class="nav-item">전단지</a>
            <a href="poster.htm" class="nav-item">포스터</a>
            <a href="namecard.htm" class="nav-item">명함</a>
            <a href="envelope.htm" class="nav-item">봉투</a>
            <a href="sticker.htm" class="nav-item">스티커</a>
            <a href="bookdesign.htm" class="nav-item">북디자인</a>
        </div>

        <div class="main-content">
            <!-- 왼쪽 사이드바 -->
            <div class="left-sidebar">
                <!-- PHP include 영역 -->
            </div>

            <!-- 메인 콘텐츠 -->
            <div class="content-area">

                <!-- 서식 디자인 -->
                <div class="service-section">
                    <div class="service-header">📋 서식</div>
                    <div class="service-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>구분</th>
                                    <th>디자인비용</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>기본 서식</td>
                                    <td class="price-highlight">20,000원~</td>
                                </tr>
                                <tr>
                                    <td>복잡 서식</td>
                                    <td class="price-highlight">40,000원~</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="service-note">
                            ※ 간단한 작업 외의 경우 추가 비용이 발생할 수 있습니다.
                        </div>
                    </div>
                </div>

                <!-- 카탈로그 디자인 -->
                <div class="service-section">
                    <div class="service-header">📖 카탈로그</div>
                    <div class="service-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>구분</th>
                                    <th>사양</th>
                                    <th>가격</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>양면(6면)</td>
                                    <td>페이지당</td>
                                    <td class="price-highlight">240,000원~</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 브로슈어 디자인 -->
                <div class="service-section">
                    <div class="service-header">📰 브로슈어</div>
                    <div class="service-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>구분</th>
                                    <th>디자인비용</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2단 브로슈어</td>
                                    <td class="price-highlight">80,000원~</td>
                                </tr>
                                <tr>
                                    <td>3단 브로슈어</td>
                                    <td class="price-highlight">120,000원~</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 전단지 디자인 -->
                <div class="service-section">
                    <div class="service-header">📄 전단지</div>
                    <div class="service-content">
                        <div class="flyer-grid">
                            <div class="flyer-size">
                                <div class="flyer-size-title">A4/16절 사이즈</div>
                                <div class="price-item">
                                    <span>단면디자인</span>
                                    <span class="price-highlight">30,000원~</span>
                                </div>
                                <div class="price-item">
                                    <span>양면디자인</span>
                                    <span class="price-highlight">60,000원~</span>
                                </div>
                                <div class="price-item">
                                    <span>2단디자인</span>
                                    <span class="price-highlight">40,000원/P당~</span>
                                </div>
                                <div class="price-item">
                                    <span>3단디자인</span>
                                    <span class="price-highlight">50,000원/P당~</span>
                                </div>
                            </div>
                            <div class="flyer-size">
                                <div class="flyer-size-title">A3/8절 사이즈</div>
                                <div class="price-item">
                                    <span>단면디자인</span>
                                    <span class="price-highlight">60,000원~</span>
                                </div>
                                <div class="price-item">
                                    <span>양면디자인</span>
                                    <span class="price-highlight">100,000원~</span>
                                </div>
                                <div class="price-item" style="opacity: 0.5;">
                                    <span>2단디자인</span>
                                    <span>견적 문의</span>
                                </div>
                                <div class="price-item" style="opacity: 0.5;">
                                    <span>3단디자인</span>
                                    <span>견적 문의</span>
                                </div>
                            </div>
                            <div class="flyer-size">
                                <div class="flyer-size-title">A2/4절 사이즈</div>
                                <div class="price-item">
                                    <span>단면디자인</span>
                                    <span class="price-highlight">120,000원~</span>
                                </div>
                                <div class="price-item">
                                    <span>양면디자인</span>
                                    <span class="price-highlight">200,000원~</span>
                                </div>
                                <div class="price-item" style="opacity: 0.5;">
                                    <span>2단디자인</span>
                                    <span>견적 문의</span>
                                </div>
                                <div class="price-item" style="opacity: 0.5;">
                                    <span>3단디자인</span>
                                    <span>견적 문의</span>
                                </div>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <div class="service-note">
                                    ※ 일반작업 외의 경우 추가 비용이 발생할 수 있습니다. 시안 추가 /과도한 수정/누끼작업/포토샵이미지작업
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 포스터 디자인 -->
                <div class="service-section">
                    <div class="service-header">🖼 포스터</div>
                    <div class="service-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>사이즈</th>
                                    <th>디자인비용</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A2</td>
                                    <td class="price-highlight">150,000원~</td>
                                </tr>
                                <tr>
                                    <td>4절</td>
                                    <td class="price-highlight">100,000원~</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 명함 디자인 -->
                <div class="service-section">
                    <div class="service-header">💼 명함</div>
                    <div class="service-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>구분</th>
                                    <th>가격</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>단면디자인</td>
                                    <td class="price-highlight">8,000원~</td>
                                </tr>
                                <tr>
                                    <td>양면디자인</td>
                                    <td class="price-highlight">15,000원~</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="service-note">
                            ※ 간단한 작업 외의 경우 추가 비용이 발생할 수 있습니다. 시안 추가 /과도한 수정/일반적인 시간외 등
                        </div>
                    </div>
                </div>

                <!-- 봉투 디자인 -->
                <div class="service-section">
                    <div class="service-header">✉️ 봉투</div>
                    <div class="service-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>구분</th>
                                    <th>가격</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>단색1도</td>
                                    <td class="price-highlight">5,000원~</td>
                                </tr>
                                <tr>
                                    <td>칼라봉투</td>
                                    <td class="price-highlight">50,000원~</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="service-note">
                            ※ 간단한 작업 외의 경우 추가 비용이 발생할 수 있습니다. 시안 추가 /과도한 수정/일반적인 시간외 등
                        </div>
                    </div>
                </div>

                <!-- 스티커 디자인 -->
                <div class="service-section">
                    <div class="service-header">🏷 스티커</div>
                    <div class="service-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>구분</th>
                                    <th>디자인비용</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>스티커 디자인</td>
                                    <td class="price-highlight">50,000원~</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="service-note">
                            ※ 간단한 작업 외의 경우 추가 비용이 발생할 수 있습니다.
                        </div>
                    </div>
                </div>

                <!-- 북디자인 -->
                <div class="service-section">
                    <div class="service-header">📚 북디자인</div>
                    <div class="service-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>구분</th>
                                    <th>디자인비용</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>표지 디자인</td>
                                    <td class="price-highlight">150,000원~</td>
                                </tr>
                                <tr>
                                    <td>내지 디자인</td>
                                    <td class="price-highlight">5,000원/페이지~</td>
                                </tr>
                                <tr>
                                    <td>종합 패키지</td>
                                    <td class="price-highlight">300,000원~</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="service-note">
                            ※ 페이지 수와 복잡도에 따라 비용이 달라질 수 있습니다.
                        </div>
                    </div>
                </div>

            </div>

            <!-- 오른쪽 사이드바 - Include 방식으로 변경됨 -->
            <?php
            // 사이드바 옵션 설정 (필요시 조정 가능)
            $show_contact = true;   // 고객센터 표시
            $show_menu = true;      // 빠른메뉴 표시
            $show_bank = true;      // 입금안내 표시
            include '../includes/right_sidebar.php';
            ?>
        </div>
    </div>
</body>
</html>