<?php
/**
 * 두손기획 리디렉션 페이지
 * 과거 "두손기획"으로 검색하던 고객을 위한 SEO 페이지
 * 두손기획인쇄로 자동 이동
 */

// SEO 메타 태그
$page_title = '두손기획 - 두손기획인쇄 (Duson Planning Print)';
$description = '두손기획(두손기획인쇄) - 1998년 창립 이래 25년 이상 온라인 인쇄 전문 기업. 스티커, 전단지, 명함, 봉투, 포스터, 상품권, 카다록, NCR양식지, 자석스티커 인쇄';
$keywords = '두손기획, 두손기획인쇄, Duson Planning, 인쇄, 온라인인쇄, 스티커인쇄, 전단지인쇄, 명함인쇄';

// 3초 후 메인으로 자동 이동
$redirect_url = 'https://dsp114.com/';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- SEO 메타 태그 -->
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($keywords); ?>">
    <meta name="author" content="두손기획인쇄">
    <meta name="robots" content="index, follow">
    <meta name="naver-site-verification" content="3e4f42759e423f615c3ee556b0505710c6f465bc" />

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:url" content="https://dsp114.com/duson-planning.php">
    <meta property="og:type" content="website">

    <!-- 자동 리디렉션 -->
    <meta http-equiv="refresh" content="3;url=<?php echo htmlspecialchars($redirect_url); ?>">

    <!-- 폰트 -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
            text-align: center;
        }

        .logo {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .highlight {
            color: #667eea;
        }

        p {
            font-size: 1.1rem;
            color: #7f8c8d;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: left;
        }

        .info-box h3 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box li {
            padding: 8px 0;
            color: #555;
            font-size: 1rem;
        }

        .info-box li:before {
            content: "✓ ";
            color: #27ae60;
            font-weight: bold;
            margin-right: 8px;
        }

        .countdown {
            font-size: 1.5rem;
            color: #667eea;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9rem;
            color: #95a5a6;
        }

        @media (max-width: 480px) {
            .container {
                padding: 40px 25px;
            }

            h1 {
                font-size: 1.6rem;
            }

            .countdown {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🖨️</div>

        <h1><span class="highlight">두손기획</span>은 <strong>두손기획인쇄</strong>입니다</h1>

        <p>
            찾아주셔서 감사합니다.<br>
            <strong>두손기획</strong>은 현재 <strong>두손기획인쇄</strong>로 서비스를 제공하고 있습니다.
        </p>

        <div class="info-box">
            <h3>🏢 두손기획인쇄 소개</h3>
            <ul>
                <li>1998년 창립, 25년 이상 인쇄 전문 기업</li>
                <li>공장 직영으로 합리적인 가격 제공</li>
                <li>스티커, 전단지, 명함, 봉투, 포스터, 상품권, 카다록, NCR양식지, 자석스티커</li>
                <li>24시간 온라인 견적 및 주문 가능</li>
                <li>전국 익일 배송 (도서지방 제외)</li>
            </ul>
        </div>

        <div class="countdown">
            <span id="counter">3</span>초 후 자동으로 이동합니다...
        </div>

        <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="btn">
            바로 이동하기
        </a>

        <div class="footer">
            📞 전화: 1688-2384 | 이메일: dsp1830@naver.com
        </div>
    </div>

    <script>
        // 카운트다운
        let counter = 3;
        const counterElement = document.getElementById('counter');

        const countdown = setInterval(() => {
            counter--;
            if (counter > 0) {
                counterElement.textContent = counter;
            } else {
                clearInterval(countdown);
                counterElement.textContent = '이동 중...';
            }
        }, 1000);
    </script>
</body>
</html>
