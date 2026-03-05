<?php
/**
 * 봉투 상세페이지 - Photorealistic Edition
 * 사용자가 제공한 포토리얼리스틱 이미지 활용
 * Created: 2026-03-05
 */

// 공통 인증 및 설정
include "../../includes/auth.php";
require_once __DIR__ . '/../../includes/mode_helper.php';

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 방문자 추적
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 봉투 데이터 가져오기
$type_id = isset($_GET['type']) ? intval($_GET['type']) : 466; // 기본값: 대봉투
$section_id = isset($_GET['section']) ? intval($_GET['section']) : 0;

// 봉투 종류 정보
$type_query = "SELECT no, title, description FROM mlangprintauto_transactioncate
               WHERE Ttable='Envelope' AND no='" . intval($type_id) . "' LIMIT 1";
$type_result = mysqli_query($db, $type_query);
$type_data = $type_result ? mysqli_fetch_assoc($type_result) : null;

// 재질 정보
if ($section_id) {
    $section_query = "SELECT no, title FROM mlangprintauto_transactioncate
                      WHERE Ttable='Envelope' AND no='" . intval($section_id) . "' LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    $section_data = $section_result ? mysqli_fetch_assoc($section_result) : null;
} else {
    $section_data = null;
}

// 가격 정보 가져오기
$price_query = "SELECT price, quantity FROM mlangprintauto_envelope
                WHERE style='" . intval($type_id) . "' AND Section='" . intval($section_id) . "'
                ORDER BY quantity ASC LIMIT 1";
$price_result = mysqli_query($db, $price_query);
$price_data = $price_result ? mysqli_fetch_assoc($price_result) : null;

// 가격 계산 (부가세 포함)
$base_price = $price_data ? intval($price_data['price']) : 0;
$vat_price = intval($base_price * 1.1);

// 이미지 설정 (사용자가 제공한 포토리얼리스틱 이미지)
$photorealistic_image = "https://a.mktgcdn.com/p/oz9_kDwLFrbVvOL8jH3-f2m-weuSEDgGEKgmLd0Kbo0/1280x1600.jpg";

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>봉투 상세페이지 - 두손기획인쇄</title>
    <meta name="description" content="포토리얼리스틱 봉투 상세페이지 - 두손기획인쇄">
    <link rel="stylesheet" href="../../css/color-system-unified.css">

    <style>
        /* 포토리얼리스틱 스타일 */
        .photorealistic-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .photorealistic-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .photorealistic-title {
            font-size: 48px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .photorealistic-subtitle {
            font-size: 24px;
            color: #666;
        }

        .photorealistic-image-wrapper {
            position: relative;
            width: 100%;
            height: 600px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 50px;
        }

        .photorealistic-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .photorealistic-image:hover {
            transform: scale(1.05);
        }

        .photorealistic-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-bottom: 50px;
        }

        .photorealistic-info {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 20px;
        }

        .photorealistic-title-section {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #ff9800;
        }

        .photorealistic-description {
            font-size: 18px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 30px;
        }

        .photorealistic-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .photorealistic-features li {
            padding: 12px 0;
            border-bottom: 1px solid #ddd;
            font-size: 18px;
            display: flex;
            align-items: center;
        }

        .photorealistic-features li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-right: 15px;
            font-size: 20px;
        }

        .photorealistic-features li:last-child {
            border-bottom: none;
        }

        .photorealistic-price-card {
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            padding: 40px;
            border-radius: 20px;
            color: white;
        }

        .photorealistic-price-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .photorealistic-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .photorealistic-price-label {
            font-size: 20px;
            opacity: 0.9;
        }

        .photorealistic-price-value {
            font-size: 28px;
            font-weight: 700;
        }

        .photorealistic-price-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .photorealistic-price-total {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .photorealistic-price-total-label {
            font-size: 24px;
            font-weight: 600;
            opacity: 0.9;
        }

        .photorealistic-price-total-value {
            font-size: 48px;
            font-weight: 700;
            margin-top: 10px;
        }

        .photorealistic-action {
            text-align: center;
            margin-top: 50px;
        }

        .photorealistic-btn {
            display: inline-block;
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            color: white;
            padding: 18px 50px;
            font-size: 20px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(255, 152, 0, 0.4);
            transition: all 0.3s ease;
            margin: 10px;
        }

        .photorealistic-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 152, 0, 0.6);
        }

        .photorealistic-btn-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .photorealistic-btn-secondary:hover {
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }

        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .photorealistic-title {
                font-size: 32px;
            }

            .photorealistic-subtitle {
                font-size: 18px;
            }

            .photorealistic-image-wrapper {
                height: 400px;
            }

            .photorealistic-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .photorealistic-info,
            .photorealistic-price-card {
                padding: 30px;
            }

            .photorealistic-title-section {
                font-size: 28px;
            }

            .photorealistic-description {
                font-size: 16px;
            }

            .photorealistic-price-total-value {
                font-size: 36px;
            }

            .photorealistic-btn {
                padding: 15px 40px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="photorealistic-container">
        <!-- Header -->
        <div class="photorealistic-header">
            <h1 class="photorealistic-title">봉투 상세페이지</h1>
            <p class="photorealistic-subtitle">포토리얼리스틱 디자인으로 선보이는 프리미엄 봉투</p>
        </div>

        <!-- Photorealistic Image -->
        <div class="photorealistic-image-wrapper">
            <img src="<?php echo htmlspecialchars($photorealistic_image); ?>"
                 alt="봉투 포토리얼리스틱 이미지"
                 class="photorealistic-image"
                 loading="lazy">
        </div>

        <!-- Content Grid -->
        <div class="photorealistic-content">
            <!-- Product Info -->
            <div class="photorealistic-info">
                <h2 class="photorealistic-title-section">
                    <?php echo $type_data ? htmlspecialchars($type_data['title']) : '봉투'; ?>
                </h2>
                <p class="photorealistic-description">
                    <?php echo $type_data && $type_data['description'] ? htmlspecialchars($type_data['description']) : '프리미엄 봉투로 비즈니스 문서 발송에 완벽하게 대비하세요. 높은 품질의 재질과 디자인으로 업계 최고의 서비스를 제공합니다.'; ?>
                </p>
                <ul class="photorealistic-features">
                    <li>고품질 포토리얼리스틱 이미지</li>
                    <li>규격 및 비규격 모두 지원</li>
                    <li>로고 및 주소 인쇄 가능</li>
                    <li>빠른 제작 및 배송</li>
                    <li>합리적인 가격</li>
                </ul>
            </div>

            <!-- Price Card -->
            <div class="photorealistic-price-card">
                <h3 class="photorealistic-price-title">견적 안내</h3>

                <?php if ($section_data): ?>
                <div class="photorealistic-price-row">
                    <span class="photorealistic-price-label">재질</span>
                    <span class="photorealistic-price-value"><?php echo htmlspecialchars($section_data['title']); ?></span>
                </div>
                <?php endif; ?>

                <div class="photorealistic-price-row">
                    <span class="photorealistic-price-label">기본 수량</span>
                    <span class="photorealistic-price-value"><?php echo $price_data ? htmlspecialchars($price_data['quantity']) : '1매'; ?></span>
                </div>

                <div class="photorealistic-price-row">
                    <span class="photorealistic-price-label">기본가</span>
                    <span class="photorealistic-price-value"><?php echo number_format($base_price); ?>원</span>
                </div>

                <div class="photorealistic-price-row">
                    <span class="photorealistic-price-label">부가세 포함</span>
                    <span class="photorealistic-price-value"><?php echo number_format($vat_price); ?>원</span>
                </div>

                <div class="photorealistic-price-total">
                    <div class="photorealistic-price-total-label">총액</div>
                    <div class="photorealistic-price-total-value"><?php echo number_format($vat_price); ?>원</div>
                </div>

                <!-- Action Buttons -->
                <div class="photorealistic-action">
                    <a href="../../mlangprintauto/envelope/index.php?type=<?php echo $type_id; ?>"
                       class="photorealistic-btn">
                        견적 상세 보기
                    </a>
                    <a href="../../mlangprintauto/envelope/index.php?type=<?php echo $type_id; ?>&section=<?php echo $section_id; ?>"
                       class="photorealistic-btn photorealistic-btn-secondary">
                        주문하기
                    </a>
                </div>
            </div>
        </div>

        <!-- Additional Sections -->
        <div style="margin-top: 60px; padding: 40px; background: #f8f9fa; border-radius: 20px;">
            <h2 style="font-size: 32px; font-weight: 700; margin-bottom: 30px; text-align: center;">봉투 제작 FAQ</h2>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                <div>
                    <h3 style="font-size: 24px; font-weight: 600; margin-bottom: 15px; color: #ff9800;">제작 기간</h3>
                    <p style="font-size: 18px; line-height: 1.8; color: #333;">
                        일반적인 제작 기간은 4~5일이며, 긴급 주문의 경우 추가 비용으로 2~3일로 단축 가능합니다.
                    </p>
                </div>

                <div>
                    <h3 style="font-size: 24px; font-weight: 600; margin-bottom: 15px; color: #ff9800;">배송 방법</h3>
                    <p style="font-size: 18px; line-height: 1.8; color: #333;">
                        택배 착불 배송이며, 부가세는 별도입니다. 배송 기간은 2~3일 소요됩니다.
                    </p>
                </div>

                <div>
                    <h3 style="font-size: 24px; font-weight: 600; margin-bottom: 15px; color: #ff9800;">인쇄 옵션</h3>
                    <p style="font-size: 18px; line-height: 1.8; color: #333;">
                        단면/양면 인쇄 지원하며, 옵셋인쇄(고급인쇄)는 4도 인쇄시만 뚜껑 인쇄가 가능합니다.
                    </p>
                </div>

                <div>
                    <h3 style="font-size: 24px; font-weight: 600; margin-bottom: 15px; color: #ff9800;">편집비</h3>
                    <p style="font-size: 18px; line-height: 1.8; color: #333;">
                        직접 디자인해서 파일로 보낼 경우 편집비가 무료입니다. 로고 도안 별도 문의 가능합니다.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/bottom.htm"; ?>
</body>
</html>
