<?php
/**
 * 스티커 견적안내 시스템 - 명함 시스템 기반 적용
 * Features: 포트폴리오 갤러리, 수식 기반 실시간 가격 계산, 드래그 업로드
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 */

// 보안 상수 정의 후 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 방문자 추적 시스템
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';

// 통합 갤러리 시스템
if (file_exists('../../includes/gallery_helper.php')) {
    include_once '../../includes/gallery_helper.php';
}
if (function_exists("init_gallery_system")) {
    init_gallery_system("sticker");
}

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("스티커 견적안내 - 프리미엄");
$current_page = 'sticker'; // 네비게이션 활성화를 위한 페이지 식별자

require_once __DIR__ . '/../../includes/mode_helper.php';
$body_class = $quotationBodyClass;

// 스티커 기본값 설정
$default_values = [
    'jong' => 'jil 아트유광코팅',
    'garo' => '100',
    'sero' => '100',
    'mesu' => '1000',
    'uhyung' => '0',
    'domusong' => '00000 사각'
];

// URL 파라미터로 재질 사전 선택 (네비 드롭다운에서 진입 시)
if (isset($_GET['jong']) && !empty($_GET['jong'])) {
    $url_jong = trim($_GET['jong']);
    $valid_jong = [
        'jil 아트유광코팅', 'jil 아트무광코팅', 'jil 아트비코팅',
        'jka 강접아트유광코팅', 'cka 초강접아트코팅', 'cka 초강접아트비코팅',
        'jsp 유포지', 'jsp 은데드롱', 'jsp 투명스티커', 'jil 모조비코팅',
        'jsp 크라프트지', 'jsp 금지스티커', 'jsp 금박스티커', 'jsp 롤형스티커'
    ];
    if (in_array($url_jong, $valid_jong)) {
        $default_values['jong'] = $url_jong;
    }
}

// 스티커용 기본 설정은 하드코딩으로 처리 (수식 기반 계산이므로 DB 조회 불필요)
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <!-- 🎨 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>스티커 제작 | 스티커 인쇄 - 두손기획인쇄</title>
    <meta name="description" content="스티커 인쇄 전문 두손기획인쇄. 라벨 스티커, 원형·사각·모양 스티커 맞춤 제작. 소량 100매부터 대량까지. 실시간 견적 확인, 빠른 배송.">
    <meta name="keywords" content="스티커 인쇄, 스티커 제작, 라벨 스티커, 원형 스티커, 맞춤 스티커, 스티커 가격">
    <link rel="canonical" href="https://dsp114.com/mlangprintauto/sticker_new/">
    <meta property="og:type" content="website">
    <meta property="og:title" content="스티커 제작 | 스티커 인쇄 - 두손기획인쇄">
    <meta property="og:description" content="스티커 인쇄 전문. 라벨, 원형, 사각, 모양 스티커 맞춤 제작. 소량 100매부터.">
    <meta property="og:url" content="https://dsp114.com/mlangprintauto/sticker_new/">
    <meta property="og:image" content="https://dsp114.com/ImgFolder/og-image.png">
    <meta property="og:site_name" content="두손기획인쇄">


    <!-- 스티커 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/sticker-compact.css?v=<?php echo filemtime(__DIR__ . '/../../css/sticker-compact.css'); ?>">

    <!-- 🎨 브랜드 디자인 시스템 CSS -->
    <link rel="stylesheet" href="../../css/brand-design-system.css">

    <!-- 🆕 Duson 통합 갤러리 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">

    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 추가 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <?php
    // 통합 갤러리 시스템 에셋 포함
    if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {
        include_gallery_assets();
    }
    ?>

    <!-- 스티커 전용 JavaScript - 인라인 스크립트로 대체되어 별도 파일 불필요 -->
    <!-- <script src="../../js/sticker.js" defer></script> -->

    <!-- 스티커 가로/세로 input 전용 스타일 -->
    
    
    <!-- 세션 ID 및 스티커 기본값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-jong" content="<?php echo htmlspecialchars($default_values['jong']); ?>">
    <meta name="default-garo" content="<?php echo htmlspecialchars($default_values['garo']); ?>">
    <meta name="default-sero" content="<?php echo htmlspecialchars($default_values['sero']); ?>">
    <meta name="default-mesu" content="<?php echo htmlspecialchars($default_values['mesu']); ?>">

    <!-- 🎯 공통 레이아웃 CSS (product-layout.css가 기본 구조 제공) -->
    <link rel="stylesheet" href="../../css/product-layout.css?v=<?php echo filemtime(__DIR__ . '/../../css/product-layout.css'); ?>">

    <!-- 스티커 전용 스타일 (공통 스타일을 덮어쓰지 않음) -->
    <link rel="stylesheet" href="../../css/sticker-inline-styles.css">

    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/upload-modal-common.css?v=<?php echo time(); ?>">

    <!-- 📱 견적서 모달 모드 공통 CSS (전 제품 공통) -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">
    <link rel="stylesheet" href="../../css/quote-gauge.css">

    <!-- 재질보기 버튼 및 모달 스타일 -->
    <style>
        /* 재질보기 버튼 스타일 */
        .btn-material-guide {
            padding: 6px 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 8px;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }

        .btn-material-guide:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .btn-material-guide:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }

        /* AI 템플릿 다운로드 버튼 스타일 */
        .btn-ai-download {
            padding: 10px 20px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 8px rgba(255, 107, 53, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-ai-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
            background: linear-gradient(135deg, #f7931e 0%, #ff6b35 100%);
        }

        .btn-ai-download:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(255, 107, 53, 0.3);
        }

        .btn-ai-download svg {
            stroke: white;
        }

        /* 재질 안내 모달 스타일 */
        .material-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .material-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }

        .material-modal-content {
            position: relative;
            background: white;
            border-radius: 12px;
            max-width: 700px;
            max-height: 90vh;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .material-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .material-modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .material-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 2rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .material-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .material-modal-body {
            padding: 0;
            max-height: calc(90vh - 80px);
            overflow-y: auto;
        }

        .material-modal-body img {
            display: block;
            width: 100%;
            height: auto;
        }

        /* 모바일 최적화 */
        @media (max-width: 768px) {
            .material-modal-content {
                width: 95%;
                max-width: 95%;
            }

            .material-modal-header h2 {
                font-size: 1.2rem;
            }

            .btn-material-guide {
                font-size: 0.75rem;
                padding: 4px 8px;
                margin-left: 4px;
            }
        }
    </style>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/product_schema.php'; echo_product_schema('sticker_new'); ?>
</head>
<body class="sticker-page<?php echo $body_class; ?>">
<?php if (!$is_quotation_mode && !$is_admin_quote_mode): ?>
<?php include "../../includes/header-ui.php"; ?>
<?php include "../../includes/nav.php"; ?>
<?php endif; ?>

    <div class="product-container">

<?php if (!$is_quotation_mode && !$is_admin_quote_mode): ?>
        <div class="page-title">
            <h1>🏷️ 스티커 견적 안내</h1>
        </div>
<?php endif; ?>

        <!-- 컴팩트 2단 그리드 레이아웃 -->
        <div class="product-content">
<?php if (!$is_quotation_mode && !$is_admin_quote_mode): ?>
            <!-- 좌측: 갤러리 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" style="position: relative;">
                <!-- 실시간 사이즈 미리보기 캔버스 (플로팅 오버레이) -->
                <div id="sizePreviewContainer" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 500px; height: 400px; background: rgba(255,255,255,0.98); border-radius: 8px; overflow: hidden; display: none; z-index: 100; box-shadow: 0 4px 20px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.12); border: 1px solid rgba(0,0,0,0.06);">
                    <canvas id="sizePreviewCanvas" width="500" height="400" style="display: block;"></canvas>
                    <div style="position: absolute; top: 8px; left: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 6px; font-size: 11px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        <div style="font-weight: 600; margin-bottom: 2px;">📐 실시간 미리보기</div>
                        <div id="previewDimensions" style="font-size: 10px; opacity: 0.9;">가로 × 세로를 입력하세요</div>
                    </div>
                    <button onclick="hideSizePreview()" style="position: absolute; top: 8px; right: 10px; background: rgba(0,0,0,0.5); color: white; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center;">✕</button>
                </div>


                <!-- 템플릿 다운로드 버튼 섹션 (컴팩트) -->
                <div id="templateDownloadButtons" style="display: none; margin: 8px auto; max-width: 800px; padding: 8px 12px; background: linear-gradient(135deg, #fafbfc 0%, #f5f6f7 100%); border-radius: 6px; border: 1px solid #e1e4e8;">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 10px; color: #586069; margin-right: 4px;">📥 템플릿 다운로드</span>
                        <button type="button" onclick="downloadSVGTemplate()" style="background: linear-gradient(135deg, #28a745 0%, #22a244 100%); color: white; padding: 5px 10px; border-radius: 4px; font-size: 10px; cursor: pointer; font-weight: 500; border: none; box-shadow: 0 1px 3px rgba(27,31,35,0.12); transition: all 0.2s;">
                            📄 SVG
                        </button>
                        <button type="button" onclick="downloadAITemplateFromPreview()" style="background: linear-gradient(135deg, #f66a0a 0%, #e85d00 100%); color: white; padding: 5px 10px; border-radius: 4px; font-size: 10px; cursor: pointer; font-weight: 500; border: none; box-shadow: 0 1px 3px rgba(27,31,35,0.12); transition: all 0.2s;">
                            🎨 AI
                        </button>
                        <button type="button" onclick="downloadCanvasSnapshot()" style="background: linear-gradient(135deg, #6f42c1 0%, #643ab0 100%); color: white; padding: 5px 10px; border-radius: 4px; font-size: 10px; cursor: pointer; font-weight: 500; border: none; box-shadow: 0 1px 3px rgba(27,31,35,0.12); transition: all 0.2s;">
                            🖼️ PNG
                        </button>
                        <span style="font-size: 9px; color: #6a737d; margin-left: 4px;">💡 AI/SVG는 영어, PNG는 한글 참조</span>
                    </div>
                    <div id="downloadProgressBar" style="display: none; margin-top: 6px;">
                        <div style="background: #e1e4e8; border-radius: 3px; height: 6px; overflow: hidden;">
                            <div id="downloadProgress" style="background: linear-gradient(135deg, #0366d6 0%, #0256c7 100%); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                        </div>
                        <div style="font-size: 9px; color: #6a737d; margin-top: 4px; text-align: center;">다운로드 준비 중...</div>
                    </div>
                </div>
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'sticker';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
                ?>
            </section>
<?php endif; ?>

            <!-- 우측: 계산기 -->
            <aside class="product-calculator">
                <form id="stickerForm" method="post">
                    <input type="hidden" name="no" value="">
                    <input type="hidden" name="action" value="calculate">
                    
                    <!-- 한 줄 레이아웃 폼 -->
                    <div class="inline-form-container">
                        <!-- 재질 선택 -->
                        <div class="inline-form-row">
                            <span class="inline-label">재질</span>
                            <?php
                            $sticker_materials = [
                                'jil 아트유광코팅' => '아트지유광-90g',
                                'jil 아트무광코팅' => '아트지무광-90g',
                                'jil 아트비코팅' => '아트지비코팅-90g',
                                'jka 강접아트유광코팅' => '강접아트유광-90g',
                                'cka 초강접아트코팅' => '초강접아트유광-90g',
                                'cka 초강접아트비코팅' => '초강접아트비코팅-90g',
                                'jsp 유포지' => '유포지-80g',
                                'jsp 은데드롱' => '은데드롱-25g',
                                'jsp 투명스티커' => '투명스티커-25g',
                                'jil 모조비코팅' => '모조지비코팅-80g',
                                'jsp 크라프트지' => '크라프트스티커-57g',
                                'jsp 금지스티커' => '금지스티커-전화문의',
                                'jsp 금박스티커' => '금박스티커-전화문의',
                                'jsp 롤형스티커' => '롤스티커-전화문의',
                            ];
                            ?>
                            <select name="jong" id="jong" class="inline-select" onchange="calculatePrice()">
                                <?php foreach ($sticker_materials as $val => $label): ?>
                                <option value="<?php echo htmlspecialchars($val); ?>"<?php echo ($default_values['jong'] === $val) ? ' selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn-material-guide" onclick="openMaterialGuide()">📋 재질보기</button>
                            <span class="inline-note">금지/금박/롤 전화문의</span>
                        </div>

                        <!-- 가로 -->
                        <div class="inline-form-row">
                            <span class="inline-label">가로</span>
                            <div class="tooltip-container">
                                <input type="number" name="garo" id="garo" class="inline-input dimmed" placeholder="숫자입력" max="560" value=""
                                       onblur="validateSize(this, '가로');" onchange="calculatePrice()" oninput="updateSizePreview()">
                                <div class="tooltip" id="garoTooltip">mm단위로 입력하세요</div>
                            </div>
                            <span class="inline-note">※5mm단위 이하 도무송</span>
                        </div>

                        <!-- 세로 -->
                        <div class="inline-form-row">
                            <span class="inline-label">세로</span>
                            <div class="tooltip-container">
                                <input type="number" name="sero" id="sero" class="inline-input dimmed" placeholder="숫자입력" max="560" value=""
                                       onblur="validateSize(this, '세로');" onchange="calculatePrice()" oninput="updateSizePreview()">
                            </div>
                            <span onclick="downloadStickerTemplate()" style="display: inline-block; background: #ff9500; color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px; cursor: pointer; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 4px rgba(255, 149, 0, 0.3);">📥 작업 템플릿 다운로드</span>
                        </div>

                        <!-- 매수 -->
                        <div class="inline-form-row">
                            <span class="inline-label">매수</span>
                            <select name="mesu" id="mesu" class="inline-select" onchange="calculatePrice()">
                                <option value="500">500매</option>
                                <option value="1000" selected>1000매</option>
                                <option value="2000">2000매</option>
                                <option value="3000">3000매</option>
                                <option value="4000">4000매</option>
                                <option value="5000">5000매</option>
                                <option value="6000">6000매</option>
                                <option value="7000">7000매</option>
                                <option value="8000">8000매</option>
                                <option value="9000">9000매</option>
                                <option value="10000">10000매</option>
                                <option value="20000">20000매</option>
                                <option value="30000">30000매</option>
                                <option value="40000">40000매</option>
                                <option value="50000">50000매</option>
                            </select>
                            <span class="inline-note">10,000매이상 별도 견적 ※ 후지칼선 선택시 별도 비용</span>
                        </div>

                        <!-- 편집 -->
                        <div class="inline-form-row">
                            <span class="inline-label">편집</span>
                            <select name="uhyung" id="uhyung" class="inline-select" onchange="calculatePrice()">
                                <option value="0" selected>인쇄만</option>
                                <option value="10000">기본 편집 (+10,000원)</option>
                                <option value="30000">고급 편집 (+30,000원)</option>
                            </select>
                            <span class="inline-note">단순 작업 외 난이도에 따라 비용 협의</span>
                        </div>

                        <!-- 모양 -->
                        <div class="inline-form-row">
                            <span class="inline-label">모양</span>
                            <select name="domusong" id="domusong" class="inline-select" onchange="resetShapeAndPreview();">
                                <option value="00000 사각" selected>기본사각</option>
                                <option value="08000 사각도무송">사각도무송</option>
                                <option value="08000 귀돌">귀돌이(라운드)</option>
                                <option value="08000 원형">원형</option>
                                <option value="08000 타원">타원형</option>
                                <option value="19000 복잡">모양도무송</option>
                            </select>
                            <span class="inline-note">도무송 시 좌우상하밀림 현상 있습니다 (오차 1mm 이상)</span>
                        </div>

                        <!-- 주문건수 -->
                        <div class="inline-form-row">
                            <span class="inline-label">주문건수</span>
                            <select name="order_count" id="order_count" class="inline-select" onchange="updateOrderCountDisplay()">
                                <option value="1" selected>1건 (기본)</option>
                                <option value="2">2건</option>
                                <option value="3">3건</option>
                                <option value="4">4건</option>
                                <option value="5">5건</option>
                                <option value="6">6건</option>
                                <option value="7">7건</option>
                                <option value="8">8건</option>
                                <option value="9">9건</option>
                                <option value="10">10건</option>
                            </select>
                            <span class="inline-note">같은 스펙으로 여러 건 주문 (건별 디자인 가능)</span>
                        </div>
                    </div>
                    
                    <!-- 명함 방식의 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <?php include __DIR__ . '/../../includes/action_buttons.php'; ?>

                    <!-- 숨겨진 필드들 -->
                    <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
                    <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
                    <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
                    <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
                    <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
                    <input type="hidden" name="page" value="Sticker">
                </form>
            </aside>
        </div>
    </div>

    <!-- 파일 업로드 모달 (통합 컴포넌트) -->
    <?php include "../../includes/upload_modal.php"; ?>
    <script src="../../includes/upload_modal.js?v=<?php echo time(); ?>"></script>
    <script>
        // 공통 openUploadModal 참조를 별도 script 블록에서 저장 (인라인 function 호이스팅 회피)
        window._commonOpenUploadModal = window.openUploadModal;
    </script>

    <?php include "../../includes/login_modal.php"; ?>

<?php if (!$is_quotation_mode && !$is_admin_quote_mode): ?>
    <!-- AI 생성 상세페이지 (기존 설명 위에 표시) -->
    <?php $detail_page_product = 'sticker_new'; include __DIR__ . "/../../_detail_page/detail_page_loader.php"; ?>
    <!-- 스티커 상세 설명 섹션 -->
    <div class="sticker-detail-combined">
        <?php include "explane_sticker.php"; ?>
    </div>
<?php endif; ?>

    <!-- 통일된 갤러리 팝업은 JavaScript로 동적 생성됩니다 -->


    <!-- 스티커 전용 추가 스타일 (카다록 색상 적용) -->
    <!-- 스티커 전용 인라인 스타일 (계산로직 제외) -->


    <script>
        // PHP 변수를 JavaScript로 전달
        window.phpVars = {
            MultyUploadDir: "../../PHPClass/MultyUpload",
            log_url: "<?php echo safe_html($log_info['url']); ?>",
            log_y: "<?php echo safe_html($log_info['y']); ?>",
            log_md: "<?php echo safe_html($log_info['md']); ?>",
            log_ip: "<?php echo safe_html($log_info['ip']); ?>",
            log_time: "<?php echo safe_html($log_info['time']); ?>",
            page: "Sticker",
            isLoggedIn: <?php echo isset($is_logged_in) && $is_logged_in ? 'true' : 'false'; ?>,
            userName: "<?php echo isset($user_name) && $user_name ? addslashes($user_name) : ''; ?>",
            defaultValues: {
                jong: "<?php echo safe_html($default_values['jong']); ?>",
                garo: "<?php echo safe_html($default_values['garo']); ?>",
                sero: "<?php echo safe_html($default_values['sero']); ?>",
                mesu: "<?php echo safe_html($default_values['mesu']); ?>",
                uhyung: "<?php echo safe_html($default_values['uhyung']); ?>",
                domusong: "<?php echo safe_html($default_values['domusong']); ?>"
            }
        };

        // 명함 방식의 파일 업로드 및 자동 가격 계산 시스템
        
        // 파일 업로드 — 공통 upload_modal.js 사용 (window.uploadedFiles, window.selectedUploadMethod)
        // 스티커 전용: openUploadModal()에서 가로/세로/가격 검증 후 공통 모달 오픈
        // window._commonOpenUploadModal은 위의 별도 script 블록에서 설정됨
        
        // Debounce 함수 - 연속 이벤트 제어
        let calculationTimeout = null;
        let isCalculating = false;

        // calculatePrice alias - onchange 핸들러 호환성을 위해
        function calculatePrice() {
            debouncedCalculatePrice();
        }

        function debouncedCalculatePrice(event) {
            console.log('Debounced calculation triggered by:', event?.target?.name || 'unknown');
            
            // 이미 계산 중이면 스킵
            if (isCalculating) {
                console.log('Skipping - calculation already in progress');
                return;
            }
            
            clearTimeout(calculationTimeout);
            calculationTimeout = setTimeout(() => {
                isCalculating = true;
                autoCalculatePrice();
                setTimeout(() => {
                    isCalculating = false;
                }, 100);
            }, 150);
        }
        
        // 모든 옵션이 선택되었는지 확인하는 함수
        function areAllOptionsSelected() {
            const form = document.getElementById('stickerForm');
            const jong = form.querySelector('select[name="jong"]').value;
            const garo = parseInt(form.querySelector('input[name="garo"]').value) || 0;
            const sero = parseInt(form.querySelector('input[name="sero"]').value) || 0;
            const mesu = form.querySelector('select[name="mesu"]').value;
            const uhyung = form.querySelector('select[name="uhyung"]').value;
            const domusong = form.querySelector('select[name="domusong"]').value;
            
            // 모든 필수 옵션과 크기값이 유효한지 확인
            return jong && garo > 0 && sero > 0 && mesu && uhyung !== '' && domusong;
        }

        // 가격 표시를 업데이트하는 함수 (공급가격 중심 표시)
        function updatePriceDisplay(priceData) {
            const priceDisplay = document.getElementById('priceDisplay');
            const priceAmount = document.getElementById('priceAmount');
            const priceDetails = document.getElementById('priceDetails');
            const uploadButton = document.getElementById('uploadOrderButton');  // 일반 모드에만 존재

            // 필수 DOM 요소 존재 확인 (uploadButton은 선택적 - 견적서 모드에는 없음)
            if (!priceDisplay || !priceAmount || !priceDetails) {
                console.error('Required DOM elements not found (priceDisplay, priceAmount, priceDetails)');
                return;
            }
            
            if (priceData && priceData.success) {
                console.log('Updating price display with success data - Supply price focus');
                
                // 편집비 계산
                const formData = new FormData(document.getElementById('stickerForm'));
                const editFee = parseInt(formData.get('uhyung')) || 0;
                const supplyPriceNum = parseInt(priceData.price.replace(/,/g, ''));
                const printPrice = supplyPriceNum - editFee;
                
                // 공급가격을 큰 글씨로 표시 (VAT 제외) - 마케팅 전략
                priceAmount.textContent = priceData.price + '원';
                console.log('Large display price (Supply price without VAT):', priceData.price + '원');
                
                // 상세 내역 표시 - 한 행으로 표시, VAT는 적색과 큰 글씨, 중앙정렬
                const orderCount = parseInt(document.getElementById('order_count')?.value) || 1;
                let priceHtml = `
                    <div style="font-size: 0.8rem; margin-top: 6px; line-height: 1.4; color: #6c757d; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; justify-content: center;">
                        <span>인쇄비: ${new Intl.NumberFormat('ko-KR').format(printPrice)}원</span>
                        ${editFee > 0 ? `<span>편집비: ${new Intl.NumberFormat('ko-KR').format(editFee)}원</span>` : ''}
                        <span>공급가격: ${priceData.price}원</span>
                        <span>부가세 포함: <span style="color: #dc3545; font-size: 1rem;">${priceData.price_vat}원</span></span>
                    </div>
                `;
                if (orderCount > 1) {
                    const vatPriceNum = parseInt(priceData.price_vat.replace(/,/g, ''));
                    const totalWithCount = vatPriceNum * orderCount;
                    priceHtml += `
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 2px solid #e0e0e0; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; justify-content: center;">
                        <span style="font-weight: 700; color: #1E4E79;">📋 주문건수: ${orderCount}건</span>
                        <span style="font-weight: 700; color: #d63384; font-size: 1.1rem;">💰 총 예상금액: ${new Intl.NumberFormat('ko-KR').format(totalWithCount)}원</span>
                    </div>
                    <div style="font-size: 11px; color: #6c757d; margin-top: 4px; text-align: center;">
                        (같은 스펙 ${orderCount}건, 건당 ${priceData.price_vat}원)
                    </div>
                    `;
                }
                priceDetails.innerHTML = priceHtml;
                
                // 가격 표시 영역을 calculated 상태로 변경
                priceDisplay.classList.add('calculated');

                // 업로드/주문 버튼 표시 (일반 모드에만 존재)
                if (uploadButton) {
                    uploadButton.style.display = 'block';
                }
                
                // 세션에 가격 정보 + 규격 정보 저장 (장바구니/주문/견적서용)
                // ✅ 규격 정보도 함께 저장 (견적서 적용 시 buildStickerSpecification() 대신 사용)
                const jong = document.getElementById('jong');
                const domusong = document.getElementById('domusong');
                const uhyungSelect = document.getElementById('uhyung');
                const garo = document.getElementById('garo');
                const sero = document.getElementById('sero');
                const mesu = document.getElementById('mesu');

                window.currentPriceData = {
                    ...priceData,
                    // 규격 정보 추가 (DOM에서 직접 읽기)
                    specData: {
                        jong: jong?.selectedOptions[0]?.text || '',
                        garo: garo?.value || '',
                        sero: sero?.value || '',
                        mesu: mesu?.value || '',
                        uhyung: uhyungSelect?.selectedOptions[0]?.text || '',
                        domusong: domusong?.selectedOptions[0]?.text || ''
                    }
                };
                console.log('Price and specification data saved:', window.currentPriceData);
                
                // ✅ 플로팅 견적서(Quote Gauge) UI 업데이트 트리거
                if (typeof updateQfPricing === 'function') {
                    updateQfPricing();
                }
                
                // 견적서 모드일 때 견적서 적용 버튼 표시
                const applyBtn = document.getElementById('applyBtn');
                if (applyBtn) {
                    console.log('✅ 견적서 모드: 견적서 적용 버튼 표시');
                    applyBtn.style.display = 'block';
                }
                
            } else {
                console.log('Resetting price display - no valid data');
                priceAmount.textContent = '견적 계산 필요';
                priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
                priceDisplay.classList.remove('calculated');
                uploadButton.style.display = 'none';
                window.currentPriceData = null;
            }
        }

        // 주문건수 변경 시 총액 표시 업데이트
        function updateOrderCountDisplay() {
            if (window.currentPriceData) {
                updatePriceDisplay(window.currentPriceData);
            }
        }

        // 가격 표시 초기화 함수 (명함 방식)
        function resetPriceDisplay() {
            const priceAmount = document.getElementById('priceAmount');
            const priceDetails = document.getElementById('priceDetails');
            const priceDisplay = document.getElementById('priceDisplay');
            const uploadButton = document.getElementById('uploadOrderButton');  // 일반 모드에만 존재

            if (priceAmount) priceAmount.textContent = '견적 계산 필요';
            if (priceDetails) priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
            if (priceDisplay) priceDisplay.classList.remove('calculated');

            // 업로드 버튼 숨김 (일반 모드에만 존재)
            if (uploadButton) {
                uploadButton.style.display = 'none';
            }

            window.currentPriceData = null;
        }

        // 자동 가격 계산 함수 (명함 방식)
        function autoCalculatePrice(skipValidation = false) {
            console.log('Auto calculation triggered (skipValidation:', skipValidation, ')');

            if (!skipValidation && !areAllOptionsSelected()) {
                console.log('Not all options selected - checking details:');
                // 각 옵션 상태 확인
                const form = document.getElementById('stickerForm');
                const jong = form.querySelector('select[name="jong"]').value;
                const garo = parseInt(form.querySelector('input[name="garo"]').value) || 0;
                const sero = parseInt(form.querySelector('input[name="sero"]').value) || 0;
                const mesu = form.querySelector('select[name="mesu"]').value;
                const uhyung = form.querySelector('select[name="uhyung"]').value;
                const domusong = form.querySelector('select[name="domusong"]').value;

                console.log('Options status:', {jong, garo, sero, mesu, uhyung, domusong});

                // 옵션이 부족할 때만 가격 초기화 (명함 방식과 동일)
                resetPriceDisplay();
                return Promise.reject('Not all options selected');
            }

            console.log('All options selected, calculating...');
            const formData = new FormData(document.getElementById('stickerForm'));

            // 디버깅: 전송되는 데이터 확인
            console.log('Sending form data:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }

            console.log('Fetching: ./calculate_price_ajax.php');
            return fetch('./calculate_price_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Price data received:', data);
                if (data.success) {
                    console.log('Calculation successful, updating display');
                    updatePriceDisplay(data);
                    return data;
                } else {
                    console.error('Calculation failed:', data.message);
                    resetPriceDisplay();
                    throw new Error(data.message || 'Calculation failed');
                }
            })
            .catch(error => {
                console.error('Price calculation error:', error);
                resetPriceDisplay();
                throw error;
            });
        }
        
        // 크기 검증 및 자동 사각도무송 선택 함수
        function checkSizeAndAutoSelect() {
            const garoInput = document.querySelector('input[name="garo"]');
            const seroInput = document.querySelector('input[name="sero"]');
            const domusongSelect = document.querySelector('select[name="domusong"]');
            
            if (!garoInput || !seroInput || !domusongSelect) return;
            
            const garo = parseInt(garoInput.value) || 0;
            const sero = parseInt(seroInput.value) || 0;
            
            // 49mm 이하 체크 (가로 또는 세로 중 하나라도) - 경고창 제거, 자동 선택만
            if (garo <= 49 || sero <= 49) {
                if (domusongSelect.value === "00000 사각") {
                    domusongSelect.value = "08000 사각도무송";
                    
                    // 적색 클래스 추가
                    domusongSelect.classList.add('domusong-selected');
                    
                    // 3번 반짝이는 효과 추가
                    domusongSelect.classList.add('domusong-blink');
                    setTimeout(() => {
                        domusongSelect.classList.remove('domusong-blink');
                    }, 1800);
                    
                    // 시각적 하이라이트 효과
                    domusongSelect.style.backgroundColor = '#fffbdd';
                    domusongSelect.style.border = '2px solid #ff9800';
                    setTimeout(() => {
                        domusongSelect.style.backgroundColor = '';
                        domusongSelect.style.border = '';
                    }, 2000);
                }
                return;
            } else {
                // 49mm 초과일 때 자동으로 사각도무송에서 일반 사각형으로 되돌리기
                if (domusongSelect.value === "08000 사각도무송") {
                    domusongSelect.value = "00000 사각";

                    // 적색 클래스 제거
                    domusongSelect.classList.remove('domusong-selected');

                    // 초기화 시각적 효과
                    domusongSelect.style.backgroundColor = '#e8f5e8';
                    domusongSelect.style.border = '2px solid #28a745';
                    setTimeout(() => {
                        domusongSelect.style.backgroundColor = '';
                        domusongSelect.style.border = '';
                    }, 1500);
                }
            }

            // 원형/타원형 선택 규칙 적용
            updateCircleEllipseOptions();
        }

        /**
         * 원형/타원형 선택 규칙 함수
         * - 가로 ≠ 세로: 원형 비활성화 (타원형만 가능)
         * - 가로 = 세로: 타원형 비활성화 (원형만 가능)
         */
        function updateCircleEllipseOptions() {
            const garoInput = document.querySelector('input[name="garo"]');
            const seroInput = document.querySelector('input[name="sero"]');
            const domusongSelect = document.querySelector('select[name="domusong"]');

            if (!garoInput || !seroInput || !domusongSelect) return;

            const garo = parseFloat(garoInput.value) || 0;
            const sero = parseFloat(seroInput.value) || 0;

            // 옵션 요소들 찾기
            const circleOption = domusongSelect.querySelector('option[value="08000 원형"]');
            const ellipseOption = domusongSelect.querySelector('option[value="08000 타원"]');

            if (!circleOption || !ellipseOption) return;

            // 가로/세로가 입력되지 않은 경우 모두 활성화
            if (garo <= 0 || sero <= 0) {
                circleOption.disabled = false;
                ellipseOption.disabled = false;
                circleOption.textContent = '원형';
                ellipseOption.textContent = '타원형';
                return;
            }

            if (garo === sero) {
                // 가로 = 세로: 원형만 가능, 타원형 비활성화
                circleOption.disabled = false;
                circleOption.textContent = '원형';
                ellipseOption.disabled = true;
                ellipseOption.textContent = '타원형 (가로≠세로 필요)';

                // 현재 타원형 선택 중이면 원형으로 변경
                if (domusongSelect.value === '08000 타원') {
                    domusongSelect.value = '08000 원형';
                    showShapeChangeToast('⚪ 가로=세로이므로 원형으로 변경되었습니다');
                }
            } else {
                // 가로 ≠ 세로: 타원형만 가능, 원형 비활성화
                circleOption.disabled = true;
                circleOption.textContent = '원형 (가로=세로 필요)';
                ellipseOption.disabled = false;
                ellipseOption.textContent = '타원형';

                // 현재 원형 선택 중이면 타원형으로 변경
                if (domusongSelect.value === '08000 원형') {
                    domusongSelect.value = '08000 타원';
                    showShapeChangeToast('⚫ 가로≠세로이므로 타원형으로 변경되었습니다');
                }
            }
        }

        // 옵션 변경 시 자동 계산 이벤트 리스너 등록
        function initAutoCalculation() {
            const form = document.getElementById('stickerForm');
            
            // 가로/세로 입력 요소에 크기 검증 이벤트 추가
            const garoInput = form.querySelector('input[name="garo"]');
            const seroInput = form.querySelector('input[name="sero"]');
            
            if (garoInput) {
                const garoTooltip = document.getElementById('garoTooltip');

                // 초기 툴팁 표시
                if (garoTooltip) {
                    setTimeout(() => {
                        garoTooltip.classList.add('show');
                    }, 500);
                }

                garoInput.addEventListener('input', function() {
                    // 디밍 해제 및 툴팁 숨김
                    this.classList.remove('dimmed');
                    if (garoTooltip) {
                        garoTooltip.classList.remove('show');
                    }
                    checkSizeAndAutoSelect();
                    debouncedCalculatePrice();
                });
                garoInput.addEventListener('change', function() {
                    this.classList.remove('dimmed');
                    if (garoTooltip) {
                        garoTooltip.classList.remove('show');
                    }
                    checkSizeAndAutoSelect();
                    debouncedCalculatePrice();
                });
                garoInput.addEventListener('focus', function() {
                    this.classList.remove('dimmed');
                    if (garoTooltip) {
                        garoTooltip.classList.remove('show');
                    }
                });
            }

            if (seroInput) {
                const seroTooltip = document.getElementById('seroTooltip');

                // 초기 툴팁 표시
                if (seroTooltip) {
                    setTimeout(() => {
                        seroTooltip.classList.add('show');
                    }, 700);
                }

                seroInput.addEventListener('input', function() {
                    // 디밍 해제 및 툴팁 숨김
                    this.classList.remove('dimmed');
                    if (seroTooltip) {
                        seroTooltip.classList.remove('show');
                    }
                    checkSizeAndAutoSelect();
                    debouncedCalculatePrice();
                });
                seroInput.addEventListener('change', function() {
                    this.classList.remove('dimmed');
                    if (seroTooltip) {
                        seroTooltip.classList.remove('show');
                    }
                    checkSizeAndAutoSelect();
                    debouncedCalculatePrice();
                });
                seroInput.addEventListener('focus', function() {
                    this.classList.remove('dimmed');
                    if (seroTooltip) {
                        seroTooltip.classList.remove('show');
                    }
                });
            }
            
            // 나머지 입력 요소에 기본 이벤트 리스너 추가
            const otherInputs = form.querySelectorAll('select:not([name="domusong"]), input[type="number"]:not([name="garo"]):not([name="sero"])');
            otherInputs.forEach(input => {
                input.addEventListener('change', debouncedCalculatePrice);
                if (input.type === 'number') {
                    input.addEventListener('input', debouncedCalculatePrice);
                }
            });
            
            // 모양 선택은 별도 처리 (자동 변경 방지)
            const domusongSelect = form.querySelector('select[name="domusong"]');
            if (domusongSelect) {
                domusongSelect.addEventListener('change', function() {
                    // 사각도무송 선택 시 적색 클래스 추가/제거
                    if (this.value === "08000 사각도무송") {
                        this.classList.add('domusong-selected');
                    } else {
                        this.classList.remove('domusong-selected');
                    }
                    debouncedCalculatePrice();
                });
            }
            
            // 초기 계산을 위해 기본값 설정 (사용자에게는 안보임)
            setTimeout(() => {
                console.log('🚀 Setting default values for initial calculation');

                // 기본값 설정 (계산용)
                if (garoInput && !garoInput.value) {
                    garoInput.value = '100';
                    console.log('  garo set to 100');
                }
                if (seroInput && !seroInput.value) {
                    seroInput.value = '100';
                    console.log('  sero set to 100');
                }

                // ✅ 초기 계산 실행 (검증 건너뜀) 및 완료 대기 (Promise 방식)
                autoCalculatePrice(true)  // skipValidation = true
                    .then(() => {
                        console.log('✅ Initial calculation successful');
                        // 계산 완료 후 입력값 지우기 (사용자가 변경하지 않았을 때만)
                        if (garoInput && garoInput.value === '100') {
                            garoInput.value = '';
                            console.log('  garo cleared');
                        }
                        if (seroInput && seroInput.value === '100') {
                            seroInput.value = '';
                            console.log('  sero cleared');
                        }
                        console.log('✨ Initial calculation complete, input fields cleared');
                    })
                    .catch(error => {
                        console.error('❌ Initial calculation failed:', error);
                        // 계산 실패 시에도 입력값 지우기
                        if (garoInput) garoInput.value = '';
                        if (seroInput) seroInput.value = '';
                    });
            }, 300);  // DOM 완전 로드 대기: 100ms → 300ms
        }

        // 장바구니 추가 함수 (명함 완성 시스템 적용)
        function addToBasketFromModal() {
            if (!window.currentPriceData) {
                showUserMessage('먼저 가격을 계산해주세요.', 'warning');
                return;
            }
            
            // 로딩 상태 표시
            const cartButton = document.querySelector('.btn-cart');
            if (!cartButton) return;
            
            const originalText = cartButton.innerHTML;
            cartButton.innerHTML = '🔄 저장 중...';
            cartButton.disabled = true;
            cartButton.style.opacity = '0.7';
            
            const form = document.getElementById('stickerForm');
            const workMemoElement = document.getElementById('modalWorkMemo');
            const workMemo = workMemoElement ? workMemoElement.value : '';
            
            if (!form) {
                restoreButton(cartButton, originalText);
                showUserMessage('양식을 찾을 수 없습니다.', 'error');
                return;
            }
            
            const formData = new FormData(form);
            
            // 기본 주문 정보 (스티커 전용)
            formData.set('action', 'add_to_basket');
            formData.set('st_price', window.currentPriceData.price.replace(/,/g, ''));
            formData.set('st_price_vat', window.currentPriceData.price_vat.replace(/,/g, ''));
            formData.set('product_type', 'sticker');

            // 스티커 전용 추가 정보
            formData.set('work_memo', workMemo);
            formData.set('upload_method', window.selectedUploadMethod || 'upload');
            
            // 업로드된 파일들 추가 (공통 upload_modal.js의 window.uploadedFiles 사용)
            if (window.uploadedFiles && window.uploadedFiles.length > 0) {
                window.uploadedFiles.forEach((fileObj, index) => {
                    formData.append(`uploaded_files[${index}]`, fileObj.file);
                });
                
                // 파일 정보 JSON
                const fileInfoArray = window.uploadedFiles.map(fileObj => ({
                    name: fileObj.name,
                    size: fileObj.size,
                    type: fileObj.type
                }));
                formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
            }
            
            fetch('./add_to_basket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text(); // 먼저 text로 받아서 확인
            })
            .then(text => {
                console.log('Raw response:', text);
                
                try {
                    const response = JSON.parse(text);
                    console.log('Parsed response:', response);
                    
                    if (response.success) {
                        // 모달 닫기
                        closeUploadModal();

                        // 바로 장바구니 페이지로 이동 (alert 없이)
                        window.location.href = '/mlangprintauto/shop/cart.php';

                    } else {
                        restoreButton(cartButton, originalText);
                        showUserMessage('장바구니 저장 중 오류가 발생했습니다: ' + response.message, 'error');
                    }
                } catch (parseError) {
                    restoreButton(cartButton, originalText);
                    console.error('JSON Parse Error:', parseError);
                    showUserMessage('서버 응답 처리 중 오류가 발생했습니다.', 'error');
                }
            })
            .catch(error => {
                restoreButton(cartButton, originalText);
                console.error('Fetch Error:', error);
                showUserMessage('장바구니 저장 중 네트워크 오류가 발생했습니다: ' + error.message, 'error');
            });
        }

        // 바로 주문하기 함수 (명함 방식)
        function directOrder() {
            if (!window.currentPriceData) {
                alert('먼저 가격을 계산해주세요.');
                return;
            }
            
            const formData = new FormData(document.getElementById('stickerForm'));
            
            // 주문 정보를 URL 파라미터로 구성
            const params = new URLSearchParams();
            params.set('direct_order', '1');
            params.set('product_type', 'sticker');
            params.set('jong', formData.get('jong'));
            params.set('garo', formData.get('garo'));
            params.set('sero', formData.get('sero'));
            params.set('mesu', formData.get('mesu'));
            params.set('uhyung', formData.get('uhyung'));
            params.set('domusong', formData.get('domusong'));
            params.set('price', window.currentPriceData.price.replace(/,/g, ''));
            params.set('vat_price', window.currentPriceData.price_vat.replace(/,/g, ''));
            
            // 주문 페이지로 이동
            window.location.href = '/mlangorder_printauto/OnlineOrder_unified.php?' + params.toString();
        }
        
        // 명함 시스템의 유틸리티 함수들
        function restoreButton(button, originalText) {
            button.innerHTML = originalText;
            button.disabled = false;
            button.style.opacity = '1';
        }
        
        function showUserMessage(message, type = 'info') {
            // 토스트 메시지 구현 (간단한 alert 대신 사용)
            alert(message); // 향후 토스트 메시지로 교체 예정
        }
        
        // 스티커 전용 openUploadModal — 가로/세로/가격 검증 후 공통 모달 오픈
        function openUploadModal() {
            const garoInput = document.getElementById('garo');
            const seroInput = document.getElementById('sero');
            const garo = parseInt(garoInput?.value) || 0;
            const sero = parseInt(seroInput?.value) || 0;

            if (garo <= 0 || sero <= 0) {
                alert('가로와 세로 크기를 입력해주세요.');
                if (garo <= 0 && garoInput) {
                    garoInput.focus();
                } else if (sero <= 0 && seroInput) {
                    seroInput.focus();
                }
                return;
            }

            if (!window.currentPriceData) {
                showUserMessage('먼저 가격을 계산해주세요.', 'warning');
                return;
            }

            // 공통 upload_modal.js의 openUploadModal 호출
            if (typeof window._commonOpenUploadModal === 'function') {
                window._commonOpenUploadModal();
            }
        }
        
        function initializeModalFileUpload() {
            const dropzone = document.getElementById('modalUploadDropzone');
            const fileInput = document.getElementById('modalFileInput');
            
            if (!dropzone || !fileInput) return;
            
            console.log('파일 업로드 모달 초기화 시작');
            
            // 드롭존 클릭 이벤트 - 한 번만 등록
            dropzone.addEventListener('click', function() {
                console.log('드롭존 클릭됨');
                fileInput.click();
            });
            
            // 파일 입력 변경 이벤트 - 한 번만 등록
            fileInput.addEventListener('change', function(e) {
                console.log('파일 선택됨:', e.target.files.length + '개');
                handleFileSelect(e);
            });
            
            // 드래그 앤 드롭 이벤트들
            dropzone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
            
            dropzone.addEventListener('dragleave', function() {
                dropzone.classList.remove('dragover');
            });
            
            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                const files = Array.from(e.dataTransfer.files);
                console.log('드롭된 파일:', files.length + '개');
                handleFiles(files);
            });
            
            console.log('파일 업로드 모달 초기화 완료');
        }
        
        function handleFileSelect(e) {
            console.log('handleFileSelect 호출됨');
            const files = Array.from(e.target.files);
            console.log('선택된 파일 수:', files.length);
            
            // 파일 입력값 리셋하여 같은 파일 재선택 가능하게 함
            e.target.value = '';
            
            handleFiles(files);
        }
        
        function handleFiles(files) {
            const validTypes = ['.jpg', '.jpeg', '.png', '.pdf', '.ai', '.eps', '.psd'];
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            files.forEach(file => {
                const extension = '.' + file.name.split('.').pop().toLowerCase();
                
                if (!validTypes.includes(extension)) {
                    showUserMessage(`지원하지 않는 파일 형식입니다: ${file.name}\\n지원 형식: JPG, PNG, PDF, AI, EPS, PSD`, 'error');
                    return;
                }
                
                if (file.size > maxSize) {
                    showUserMessage(`파일 크기가 너무 큽니다: ${file.name}\\n최대 10MB까지 업로드 가능합니다.`, 'error');
                    return;
                }
                
                // 업로드된 파일 목록에 추가
                const fileObj = {
                    id: Date.now() + Math.random(),
                    file: file,
                    name: file.name,
                    size: formatFileSize(file.size),
                    type: extension
                };
                
                uploadedFiles.push(fileObj);
                updateModalFileList();
            });
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function updateModalFileList() {
            const uploadedFilesDiv = document.getElementById('modalUploadedFiles');
            const fileList = document.getElementById('modalFileList');
            
            if (!uploadedFilesDiv || !fileList) return;
            
            if (uploadedFiles.length === 0) {
                uploadedFilesDiv.style.display = 'none';
                return;
            }
            
            uploadedFilesDiv.style.display = 'block';
            fileList.innerHTML = '';
            
            uploadedFiles.forEach(fileObj => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div class="file-info">
                        <span class="file-icon">${getFileIcon(fileObj.type)}</span>
                        <div class="file-details">
                            <div class="file-name">${fileObj.name}</div>
                            <div class="file-size">${fileObj.size}</div>
                        </div>
                    </div>
                    <button class="file-remove" onclick="removeFile('${fileObj.id}')">삭제</button>
                `;
                fileList.appendChild(fileItem);
            });
        }
        
        function getFileIcon(extension) {
            switch(extension.toLowerCase()) {
                case '.jpg':
                case '.jpeg':
                case '.png': return '🖼️';
                case '.pdf': return '📄';
                case '.ai': return '🎨';
                case '.eps': return '🎨';
                case '.psd': return '🎨';
                default: return '📁';
            }
        }
        
        function removeFile(fileId) {
            uploadedFiles = uploadedFiles.filter(f => f.id != fileId);
            updateModalFileList();
        }
        
        function closeUploadModal() {
            const modal = document.getElementById('uploadModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                
                // 업로드된 파일 초기화
                uploadedFiles = [];
                updateModalFileList();
                
                // 파일 입력 초기화
                const fileInput = document.getElementById('modalFileInput');
                if (fileInput) {
                    fileInput.value = '';
                }
                
                const workMemo = document.getElementById('modalWorkMemo');
                if (workMemo) {
                    workMemo.value = '';
                }
                
                console.log('모달 닫힘 - 모든 상태 초기화 완료');
            }
        }
        
        function selectUploadMethod(method) {
            const buttons = document.querySelectorAll('.btn-upload-method');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }
        
        // 페이지 로드 시 자동 계산 및 갤러리 초기화
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded - Starting initialization');

            // 자동 계산 초기화
            initAutoCalculation();

            // 원형/타원형 선택 규칙 초기화 (기본값 100×100이므로 타원형 비활성화)
            updateCircleEllipseOptions();
        });
        
        
        // CommonGallery 시스템 사용 - 갤러리 변수 제거됨
        
        // CommonGallery 시스템이 자동으로 갤러리 초기화 처리

        // 공통 갤러리 팝업 함수 사용 (common-gallery-popup.js)
        // openGalleryPopup(category) 함수를 사용하세요
        // 하위 호환성을 위한 별칭
        const openProofPopup = window.openGalleryPopup;
        
        // 갤러리 모달 열기 (페이지네이션 지원) - 기존 코드 유지
        let stickerNewCurrentPage = 1;
        let stickerNewTotalPages = 1;
        
        function openGalleryModal() {
            // 이제 openProofPopup을 사용하므로 이 함수는 사용하지 않음
            openProofPopup('스티커');
        }
        
        // 갤러리 모달 닫기
        function closeGalleryModal() {
            const modal = document.getElementById('galleryModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // 스티커 뉴 갤러리 페이지 로드 함수
        function loadStickerNewPage(page) {
            if (typeof page === 'string') {
                if (page === 'prev') {
                    page = Math.max(1, stickerNewCurrentPage - 1);
                } else if (page === 'next') {
                    page = Math.min(stickerNewTotalPages, stickerNewCurrentPage + 1);
                } else {
                    page = parseInt(page);
                }
            }
            
            if (page === stickerNewCurrentPage) return;
            
            const gallery = document.getElementById('galleryModalGrid');
            if (!gallery) return;
            
            // 로딩 표시
            gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><div style="font-size: 1.5rem;">⏳</div><p>이미지를 불러오는 중...</p></div>';
            
            // API 호출
            fetch(`get_sticker_new_images.php?all=true&page=${page}&per_page=12`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // 갤러리 업데이트
                        gallery.innerHTML = '';
                        data.data.forEach(image => {
                            const img = document.createElement('img');
                            img.src = image.path;
                            img.alt = image.title;
                            img.onclick = () => viewLargeImage(image.path, image.title);
                            gallery.appendChild(img);
                        });
                        
                        // 페이지네이션 정보 업데이트
                        stickerNewCurrentPage = data.pagination.current_page;
                        stickerNewTotalPages = data.pagination.total_pages;
                        
                        // 페이지네이션 UI 업데이트
                        updateStickerNewPagination(data.pagination);
                    } else {
                        gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><p>이미지를 불러올 수 없습니다.</p></div>';
                    }
                })
                .catch(error => {
                    console.error('스티커 뉴 이미지 로드 오류:', error);
                    gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><p>이미지 로드 중 오류가 발생했습니다.</p></div>';
                });
        }
        
        // 페이지네이션 UI 업데이트
        function updateStickerNewPagination(pagination) {
            // 페이지 정보 업데이트
            const pageInfo = document.getElementById('stickerNewPageInfo');
            if (pageInfo) {
                pageInfo.textContent = `페이지 ${pagination.current_page} / ${pagination.total_pages} (총 ${pagination.total_count}개)`;
            }
            
            // 버튼 상태 업데이트
            const prevBtn = document.getElementById('stickerNewPrevBtn');
            const nextBtn = document.getElementById('stickerNewNextBtn');
            
            if (prevBtn) {
                prevBtn.disabled = !pagination.has_prev;
            }
            if (nextBtn) {
                nextBtn.disabled = !pagination.has_next;
            }
            
            // 페이지 번호 버튼 생성
            const pageNumbers = document.getElementById('stickerNewPageNumbers');
            if (pageNumbers) {
                pageNumbers.innerHTML = '';
                
                const startPage = Math.max(1, pagination.current_page - 2);
                const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
                
                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.className = 'pagination-number' + (i === pagination.current_page ? ' active' : '');
                    pageBtn.textContent = i;
                    pageBtn.onclick = () => loadStickerNewPage(i);
                    pageNumbers.appendChild(pageBtn);
                }
            }
            
            // 페이지네이션 섹션 표시
            const paginationSection = document.getElementById('stickerNewPagination');
            if (paginationSection) {
                paginationSection.style.display = pagination.total_pages > 1 ? 'block' : 'none';
            }
        }
        
        // 전체 갤러리 렌더링
        function renderFullGallery(images, container) {
            container.innerHTML = '';
            
            images.forEach((image, index) => {
                const img = document.createElement('img');
                img.src = image.thumbnail || image.path;
                img.alt = image.title || `스티커 샘플 ${index + 1}`;
                img.onclick = function() {
                    openLightbox(image.path || image.src);
                };
                container.appendChild(img);
            });
        }
        
        // 포스터 방식 갤러리 렌더링 (성공한 방식 적용)
        function renderStickerGallery(images, container) {
            console.log('🎨 스티커 갤러리 렌더링 시작:', images.length + '개');
            
            const galleryHTML = `
                <div class="lightbox-viewer zoom-box" id="stickerZoomBox"></div>
                <div class="thumbnail-grid" id="stickerThumbnailGrid"></div>
            `;
            
            container.innerHTML = galleryHTML;
            
            const zoomBox = document.getElementById('stickerZoomBox');
            const thumbnailGrid = document.getElementById('stickerThumbnailGrid');
            
            if (!zoomBox || !thumbnailGrid) {
                console.error('갤러리 DOM 요소 생성 실패');
                return;
            }
            
            // 썸네일 그리드 생성 (4개 이미지)
            images.forEach((image, index) => {
                const thumbnail = document.createElement('img');
                thumbnail.src = image.image_path;
                thumbnail.alt = image.title || `스티커 작품 ${index + 1}`;
                thumbnail.className = index === 0 ? 'active' : '';
                
                // 포스터 방식 이미지 선택 이벤트
                thumbnail.onclick = function() { 
                    selectStickerImage(this, image.image_path, image.title); 
                };
                
                thumbnailGrid.appendChild(thumbnail);
            });
            
            // 첫 번째 이미지 자동 선택 (포스터 방식)
            if (images.length > 0) {
                const firstImage = images[0];
                setStickerMainImage(firstImage.image_path);
                initializeStickerZoom();
                
                console.log('첫 번째 스티커 이미지 설정:', firstImage.title);
            }
            
            console.log('스티커 갤러리 렌더링 완료');
        }
        
        // 포스터 방식 이미지 선택 (성공한 방식)
        function selectStickerImage(thumb, imagePath, title) {
            console.log('🖼️ 스티커 이미지 선택:', title);
            
            // 모든 썸네일 비활성화 (스티커 전용 선택자)
            document.querySelectorAll('#stickerThumbnailGrid img').forEach(img => {
                img.classList.remove('active');
            });
            
            // 선택된 썸네일 활성화
            thumb.classList.add('active');
            
            // 메인 이미지 설정 (포스터 방식)
            setStickerMainImage(imagePath);
        }
        
        // 포스터 방식 메인 이미지 설정 (배경이미지 방식)
        function setStickerMainImage(imagePath) {
            const zoomBox = document.getElementById('stickerZoomBox');
            if (!zoomBox) {
                console.error('스티커 줌박스 요소를 찾을 수 없음');
                return;
            }
            
            console.log('🖼️ 스티커 메인 이미지 설정:', imagePath);
            
            // 포스터와 동일한 background-image 방식 적용
            zoomBox.style.backgroundImage = `url('${imagePath}')`;
            zoomBox.style.backgroundSize = 'contain';
            zoomBox.style.backgroundPosition = 'center';
            zoomBox.style.backgroundRepeat = 'no-repeat';
            zoomBox.style.cursor = 'zoom-in';
        }
        
        function analyzeAndAdaptImage(imagePath) {
            const img = new Image();
            img.onload = function() {
                const aspectRatio = this.width / this.height;
                const isSmall = this.width < 300 && this.height < 300;
                
                const zoomBox = document.getElementById('zoomBox');
                if (!zoomBox) return;
                
                currentImageType = isSmall ? 'small' : 'large';
                
                if (isSmall) {
                    // 작은 이미지: 최대 크기로 표시
                    originalBackgroundSize = '100%';
                    zoomBox.style.backgroundSize = '100%';
                } else if (aspectRatio > 1.5) {
                    // 가로가 긴 이미지
                    originalBackgroundSize = 'contain';
                    zoomBox.style.backgroundSize = 'contain';
                } else if (aspectRatio < 0.67) {
                    // 세로가 긴 이미지
                    originalBackgroundSize = 'contain';
                    zoomBox.style.backgroundSize = 'contain';
                } else {
                    // 일반 이미지
                    originalBackgroundSize = 'contain';
                    zoomBox.style.backgroundSize = 'contain';
                }
                
                console.log(`이미지 분석: ${this.width}x${this.height}, 종횡비: ${aspectRatio.toFixed(2)}, 타입: ${currentImageType}`);
            };
            img.src = imagePath;
        }
        
        // 포스터 방식 줌 시스템 초기화 (성공한 방식)
        function initializeStickerZoom() {
            const zoomBox = document.getElementById('stickerZoomBox');
            if (!zoomBox) {
                console.error('스티커 줌박스 초기화 실패');
                return;
            }
            
            console.log('🔍 스티커 줌 시스템 초기화 시작');
            
            // DOM 완전 재생성으로 이벤트 중복 방지 (포스터 방식)
            const newZoomBox = zoomBox.cloneNode(true);
            zoomBox.parentNode.replaceChild(newZoomBox, zoomBox);
            
            // 포스터 방식 마우스 이벤트 (마우스 트래킹)
            newZoomBox.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                // 실시간 배경 위치 변경 (포스터 호버 효과)
                this.style.backgroundSize = '200%'; // 확대
                this.style.backgroundPosition = `${x}% ${y}%`;
            });
            
            // 마우스 나가기시 원상복구
            newZoomBox.addEventListener('mouseleave', function() {
                this.style.backgroundSize = 'contain';
                this.style.backgroundPosition = 'center';
            });
            
            // 클릭으로 라이트박스 열기 (포스터 방식)
            newZoomBox.addEventListener('click', function() {
                const bgImage = this.style.backgroundImage;
                if (bgImage) {
                    const imageUrl = bgImage.slice(5, -2); // url(' ') 제거
                    openStickerLightbox(imageUrl);
                }
            });
            
            console.log('스티커 줌 시스템 초기화 완료 (포스터 방식)');
        }
        
        function animate() {
            const ease = 0.15; // 부드러운 애니메이션
            
            currentX += (targetX - currentX) * ease;
            currentY += (targetY - currentY) * ease;
            currentSize += (targetSize - currentSize) * ease;
            
            const zoomBox = document.getElementById('zoomBox');
            if (zoomBox) {
                zoomBox.style.backgroundSize = `${currentSize}%`;
                zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
            }
            
            // 애니메이션 계속
            if (Math.abs(targetX - currentX) > 0.1 || 
                Math.abs(targetY - currentY) > 0.1 || 
                Math.abs(targetSize - currentSize) > 0.1) {
                animationId = requestAnimationFrame(animate);
            } else {
                animationId = null;
            }
        }
        
        // 포스터 방식 라이트박스 (성공한 방식)
        function openStickerLightbox(imagePath) {
            console.log('🔍 스티커 라이트박스 열기:', imagePath);
            
            if (typeof EnhancedImageLightbox !== 'undefined') {
                const lightbox = new EnhancedImageLightbox({
                    closeOnImageClick: true,
                    showNavigation: false,
                    showCaption: true,
                    enableKeyboard: true,
                    zoomEnabled: true
                });
                
                lightbox.open([{
                    src: imagePath,
                    title: '🏷️ 스티커 작품 확대보기',
                    description: '실제 고객 주문으로 제작된 스티커입니다. 클릭하면 닫힙니다.'
                }]);
                
                console.log('스티커 라이트박스 열림');
            } else {
                console.warn('EnhancedImageLightbox 라이브러리 없음, 기본 새창 열기');
                window.open(imagePath, '_blank');
            }
        }

        // 통일된 갤러리 팝업 전역 변수
        let unifiedStickerGallery = null;

        // 통일된 갤러리 팝업 초기화 함수
        function initializePopupGallery() {
            console.log('스티커 통일된 갤러리 시스템 초기화');
            
            // 통일된 갤러리 팝업 인스턴스 생성
            unifiedStickerGallery = new UnifiedGalleryPopup({
                category: 'sticker',
                apiUrl: '/api/get_real_orders_portfolio.php',
                title: '스티커 전체 갤러리',
                icon: '🏷️',
                perPage: 18 // 6×3 그리드
            });
            
            // 전역 함수로 등록 (HTML onclick에서 사용)
            window.openGalleryModal = function() {
                console.log('📸 통일된 스티커 갤러리 팝업 열기');
                if (unifiedStickerGallery) {
                    unifiedStickerGallery.open();
                }
            };
            
            // 전역 변수에도 등록 (페이지네이션에서 사용)
            window[`unifiedGalleryPopup_sticker`] = unifiedStickerGallery;
            
            console.log('✨ 스티커 통일된 갤러리 시스템 초기화 완료');
        }

        // 재질 안내 모달 열기
        function openMaterialGuide() {
            const modal = document.getElementById('materialGuideModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        // 재질 안내 모달 닫기
        function closeMaterialGuide() {
            const modal = document.getElementById('materialGuideModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // ESC 키로 모달 닫기
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMaterialGuide();
            }
        });

        // 단순한 크기 검증 함수 (필드를 떠날 때만 실행)
        function validateSize(input, type) {
            // 입력값이 없으면 검증하지 않음
            if (!input.value || input.value.trim() === '') {
                input.style.borderColor = '#ddd';
                input.style.backgroundColor = '';
                return true;
            }
            
            const value = parseInt(input.value);
            const max = 560;
            
            // 560mm 초과 시만 검증
            if (isNaN(value) || value > max) {
                alert(`${type} 크기는 ${max}mm 이하로 입력해주세요.\n현재 입력값: ${input.value}mm`);
                
                // 에러 스타일 적용
                input.style.borderColor = '#dc3545';
                input.style.backgroundColor = '#fff5f5';
                
                // 포커스 복귀
                setTimeout(() => {
                    input.focus();
                    input.select();
                }, 100);
                
                return false;
            } else {
                // 정상 스타일 복원
                input.style.borderColor = '#ddd';
                input.style.backgroundColor = '';
            }
            
            return true;
        }
    </script>

    <!-- jQuery 라이브러리 (폼 검증 스크립트에서 필요) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script type="text/javascript">
    (function($) {
      $(function() {
        var $form = $('form[action$="basket_post.php"]');
        var $garo = $('input[name="garo"]');
        var $sero = $('input[name="sero"]');
        var $domu = $('select[name="domusong"]');

        function toNumber(v) {
          if (v == null) return null;
          var s = $.trim(String(v)).replace(/[^\d.]/g, '');
          if (s === '') return null;
          var n = parseFloat(s);
          return isNaN(n) ? null : n;
        }

        // from: 'blur' | 'submit' | (그 외: 방어용)
        function applyRules(from) {
          var w = toNumber($garo.val());
          var h = toNumber($sero.val());

          // 현재 포커스된 엘리먼트
          var active = document.activeElement;

          // 1) 50mm 미만이면 도무송 자동 선택 (경고 없음)
          if ((w != null && w < 50) || (h != null && h < 50)) {
            $domu.val('08000 사각도무송'); // 값 정확히 일치
          }

          // 2) 10mm 미만이면 경고 대상
          var tooSmallTarget = null;
          if (w != null && w < 10) tooSmallTarget = $garo;
          if (h != null && h < 10) tooSmallTarget = tooSmallTarget || $sero;

          // ⚠️ 방어 로직: 입력 중(해당 칸이 여전히 포커스)에는 경고 금지
          var isEditing =
            active === $garo.get(0) || active === $sero.get(0);

          if (tooSmallTarget) {
            // blur/submit에서만 경고 허용, 그리고 편집 중이면 경고 금지
            var allowAlert = (from === 'blur' || from === 'submit') && !isEditing;

            if (allowAlert) {
              alert('별도견적을 요청하세요 문의 1688-2384');
            }

            // 제출 단계에서는 차단, blur 단계에서는 안내만 수행(제출 아님)
            if (from === 'submit') {
              setTimeout(function(){ tooSmallTarget.focus(); }, 0);
              return { ok: false };
            }
          }

          return { ok: true };
        }

        // 각 칸을 떠날 때만 검사 (입력 도중에는 검사 X)
        $garo.on('blur', function(){ applyRules('blur'); });
        $sero.on('blur', function(){ applyRules('blur'); });

        // 제출 직전 최종 검사 (blur를 건너뛴 경우 대비)
        $form.on('submit', function(e) {
          var result = applyRules('submit');
          if (!result.ok) {
            e.preventDefault();
            return false;
          }
        });

        // ✋ 혹시 기존에 걸려 있던 input/keyup 검사들이 있으면,
        // 우리 로직이 경고를 막아주지만, 완전 차단하려면 아래처럼 제거도 고려:
        // $garo.off('input keyup change');
        // $sero.off('input keyup change');
      });
    })(jQuery);
    </script>

    <!-- 통합 갤러리 JavaScript 포함 -->
    <script src="../../js/common-gallery-popup.js"></script>

    <!-- 스티커 장바구니 스크립트 -->
    <script>
        // 스티커 전용 장바구니 추가 함수 (통합 모달 패턴)
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
            console.log("스티커 장바구니 추가 시작");

            if (!window.currentPriceData) {
                console.error("가격 계산이 필요합니다");
                if (onError) onError("먼저 가격을 계산해주세요.");
                return;
            }

            const formData = new FormData();
            formData.append("action", "add_to_basket");
            formData.append("product_type", "sticker_new");
            formData.append("MY_type", document.getElementById("MY_type").value);
            formData.append("Section", document.getElementById("Section").value);
            formData.append("POtype", document.getElementById("POtype").value);
            formData.append("MY_amount", document.getElementById("MY_amount").value);
            formData.append("ordertype", document.getElementById("ordertype").value);
            formData.append("order_count", document.getElementById("order_count")?.value || "1");
            formData.append("price", Math.round(window.currentPriceData.total_price));
            formData.append("vat_price", Math.round(window.currentPriceData.vat_price));

            const workMemo = document.getElementById("modalWorkMemo");
            if (workMemo) formData.append("work_memo", workMemo.value);

            formData.append("upload_method", window.selectedUploadMethod || "upload");

            if (uploadedFiles && uploadedFiles.length > 0) {
                uploadedFiles.forEach((file, index) => {
                    formData.append("uploaded_files[" + index + "]", file);
                });
            }

            fetch("add_to_basket.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (onSuccess) onSuccess(data);
                } else {
                    if (onError) onError(data.message);
                }
            })
            .catch(error => {
                console.error("장바구니 추가 오류:", error);
                if (onError) onError("네트워크 오류가 발생했습니다.");
            });
        };

        /**
         * 스티커 맞춤 템플릿 다운로드 - 플로팅 미리보기 + 다운로드 버튼 표시
         * 사용자가 입력한 가로/세로 사이즈와 도무송 선택에 따라 템플릿 미리보기
         */
        function downloadStickerTemplate() {
            // 가로/세로 값 가져오기
            const garoInput = document.getElementById('garo');
            const seroInput = document.getElementById('sero');
            const domusongSelect = document.getElementById('domusong');

            if (!garoInput || !seroInput || !domusongSelect) {
                alert('입력 필드를 찾을 수 없습니다.');
                return;
            }

            const garo = parseInt(garoInput.value);
            const sero = parseInt(seroInput.value);

            // 입력값 검증
            if (!garo || !sero || garo <= 0 || sero <= 0) {
                alert('가로와 세로 사이즈를 먼저 입력해주세요.\n\n예시: 가로 80mm, 세로 100mm');
                garoInput.focus();
                return;
            }

            // 최대값 검증
            if (garo > 560 || sero > 560) {
                alert('가로/세로 최대 크기는 560mm입니다.');
                return;
            }

            // 플로팅 미리보기 표시 (shape 그리기)
            updateSizePreview();

            // 다운로드 버튼 표시
            const downloadButtons = document.getElementById('templateDownloadButtons');
            if (downloadButtons) {
                downloadButtons.style.display = 'block';
            }

            // 플로팅 컨테이너 표시
            const container = document.getElementById('sizePreviewContainer');
            if (container) {
                container.style.display = 'block';
            }

            console.log('스티커 템플릿 미리보기:', {
                가로: garo,
                세로: sero
            });
        }

        /**
         * SVG 템플릿 다운로드 (캔버스에서 SVG 생성)
         */
        function downloadSVGTemplate() {
            const garoInput = document.getElementById('garo');
            const seroInput = document.getElementById('sero');
            const domusongSelect = document.getElementById('domusong');

            const garo = parseInt(garoInput.value) || 50;
            const sero = parseInt(seroInput.value) || 50;

            // 도무송 모양 결정
            const domusongValue = domusongSelect ? domusongSelect.value : '';
            let shapeType = 'rectangle';
            let cornerRadius = 0;

            if (domusongValue.includes('원형')) {
                shapeType = 'circle';
            } else if (domusongValue.includes('타원')) {
                shapeType = 'ellipse';
            } else if (domusongValue.includes('귀돌')) {
                shapeType = 'rounded';
                cornerRadius = Math.min(garo, sero) * 0.15;
            }

            // 로딩바 표시
            showDownloadProgress();

            // SVG 생성
            const bleed = 3;  // 여유선 +3mm
            const safety = 2; // 안전선 -2mm
            const padding = 30; // 캔버스 여백 (중앙 정렬용)

            const contentWidth = garo + (bleed * 2);
            const contentHeight = sero + (bleed * 2);
            const svgWidth = contentWidth + (padding * 2);
            const svgHeight = contentHeight + (padding * 2) + 25; // 범례 공간

            // 모양 한글명
            const shapeNamesKo = {
                'rectangle': '사각형',
                'rounded': '귀돌이',
                'circle': '원형',
                'ellipse': '타원형'
            };
            const shapeNameKo = shapeNamesKo[shapeType] || '사각형';

            let svgContent = '<' + '?xml version="1.0" encoding="UTF-8"?>' + `
<svg xmlns="http://www.w3.org/2000/svg" width="${svgWidth}mm" height="${svgHeight}mm" viewBox="0 0 ${svgWidth} ${svgHeight}">
  <title>스티커 템플릿 ${garo}x${sero}mm</title>
  <desc>두손기획 스티커 재단선 템플릿 - 여유선(파랑), 재단선(검정 점선), 안전선(분홍)</desc>

  <!-- 배경 -->
  <rect x="0" y="0" width="${svgWidth}" height="${svgHeight}" fill="white"/>

  <!-- 중앙 정렬 그룹 -->
  <g transform="translate(${padding}, ${padding})">
    <!-- 여유선 +${bleed}mm - 파랑 -->
    ${generateSVGShape(shapeType, contentWidth, contentHeight, 0, 0, cornerRadius + bleed, '#00B4FF', '0.5', 'none')}

    <!-- 재단선 - 검정 점선 -->
    ${generateSVGShape(shapeType, garo, sero, bleed, bleed, cornerRadius, '#000000', '0.8', '3,2')}

    <!-- 안전선 -${safety}mm - 분홍 -->
    ${generateSVGShape(shapeType, garo - (safety * 2), sero - (safety * 2), bleed + safety, bleed + safety, Math.max(0, cornerRadius - safety), '#FF0066', '0.5', 'none')}

    <!-- 중앙 치수 표시 -->
    <g transform="translate(${contentWidth / 2}, ${contentHeight / 2})" font-family="GulimChe, Gulim, Arial, sans-serif" text-anchor="middle">
      <text y="-5" fill="#000000" font-size="6" font-weight="bold">재단선: ${garo}mm x ${sero}mm</text>
      <text y="7" fill="#666666" font-size="5">작업영역: ${garo + bleed * 2}mm x ${sero + bleed * 2}mm</text>
    </g>
  </g>

  <!-- 범례 (하단 중앙) -->
  <g transform="translate(${svgWidth / 2}, ${svgHeight - 20})" font-family="GulimChe, Gulim, Arial, sans-serif" text-anchor="middle" font-size="3.5">
    <text y="0" fill="#333" font-weight="bold">두손기획 스티커 템플릿 ${garo}x${sero}mm (${shapeNameKo})</text>
    <g transform="translate(-60, 10)">
      <line x1="0" y1="0" x2="10" y2="0" stroke="#00B4FF" stroke-width="0.8"/>
      <text x="12" y="1" fill="#666" text-anchor="start">여유선 +${bleed}mm</text>
    </g>
    <g transform="translate(0, 10)">
      <line x1="0" y1="0" x2="10" y2="0" stroke="#000" stroke-width="0.8" stroke-dasharray="2,1"/>
      <text x="12" y="1" fill="#666" text-anchor="start">재단선</text>
    </g>
    <g transform="translate(50, 10)">
      <line x1="0" y1="0" x2="10" y2="0" stroke="#FF0066" stroke-width="0.8"/>
      <text x="12" y="1" fill="#666" text-anchor="start">안전선 -${safety}mm</text>
    </g>
  </g>
</svg>`;

            // 다운로드
            setTimeout(() => {
                const blob = new Blob([svgContent], { type: 'image/svg+xml' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `sticker_${garo}x${sero}mm_template.svg`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);

                hideDownloadProgress();
            }, 500);
        }

        /**
         * SVG 도형 생성 헬퍼
         */
        function generateSVGShape(type, width, height, x, y, radius, stroke, strokeWidth, dashArray) {
            const dashAttr = dashArray !== 'none' ? ` stroke-dasharray="${dashArray}"` : '';

            if (type === 'circle') {
                const r = Math.min(width, height) / 2;
                const cx = x + width / 2;
                const cy = y + height / 2;
                return `<circle cx="${cx}" cy="${cy}" r="${r}" fill="none" stroke="${stroke}" stroke-width="${strokeWidth}"${dashAttr}/>`;
            } else if (type === 'ellipse') {
                const rx = width / 2;
                const ry = height / 2;
                const cx = x + rx;
                const cy = y + ry;
                return `<ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="none" stroke="${stroke}" stroke-width="${strokeWidth}"${dashAttr}/>`;
            } else if (type === 'rounded' && radius > 0) {
                return `<rect x="${x}" y="${y}" width="${width}" height="${height}" rx="${radius}" ry="${radius}" fill="none" stroke="${stroke}" stroke-width="${strokeWidth}"${dashAttr}/>`;
            } else {
                return `<rect x="${x}" y="${y}" width="${width}" height="${height}" fill="none" stroke="${stroke}" stroke-width="${strokeWidth}"${dashAttr}/>`;
            }
        }

        /**
         * AI 템플릿 다운로드 (플로팅 미리보기에서)
         */
        function downloadAITemplateFromPreview() {
            const garoInput = document.getElementById('garo');
            const seroInput = document.getElementById('sero');
            const domusongSelect = document.getElementById('domusong');

            const garo = parseInt(garoInput.value) || 50;
            const sero = parseInt(seroInput.value) || 50;

            // 도무송 모양 결정
            const domusongValue = domusongSelect ? domusongSelect.value : '';
            let shapeType = 'rectangle';
            let cornerRadius = 0;

            if (domusongValue.includes('원형')) {
                shapeType = 'circle';
            } else if (domusongValue.includes('타원')) {
                shapeType = 'ellipse';
            } else if (domusongValue.includes('귀돌')) {
                shapeType = 'rounded';
                cornerRadius = Math.min(garo, sero) * 0.15;
            }

            // 로딩바 표시
            showDownloadProgress();

            // AI 파일 다운로드
            const url = `download_ai.php?garo=${garo}&sero=${sero}&shape=${shapeType}&corner=${cornerRadius}`;

            console.log('AI 템플릿 다운로드:', { garo, sero, shapeType, cornerRadius, url });

            // <a> 태그 클릭으로 다운로드 (더 안정적인 방법)
            setTimeout(() => {
                const link = document.createElement('a');
                link.href = url;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();

                setTimeout(() => {
                    document.body.removeChild(link);
                    hideDownloadProgress();
                }, 1000);
            }, 300);
        }

        /**
         * 다운로드 진행바 표시
         */
        function showDownloadProgress() {
            const progressBar = document.getElementById('downloadProgressBar');
            const progress = document.getElementById('downloadProgress');
            if (progressBar && progress) {
                progressBar.style.display = 'block';
                progress.style.width = '0%';

                // 애니메이션
                setTimeout(() => progress.style.width = '30%', 100);
                setTimeout(() => progress.style.width = '60%', 300);
                setTimeout(() => progress.style.width = '90%', 600);
            }
        }

        /**
         * 다운로드 진행바 숨기기 및 버튼 숨기기
         */
        function hideDownloadProgress() {
            const progressBar = document.getElementById('downloadProgressBar');
            const progress = document.getElementById('downloadProgress');
            const downloadButtons = document.getElementById('templateDownloadButtons');

            if (progress) {
                progress.style.width = '100%';
            }

            setTimeout(() => {
                if (progressBar) progressBar.style.display = 'none';
                if (downloadButtons) downloadButtons.style.display = 'none';
                if (progress) progress.style.width = '0%';
            }, 500);
        }

        /**
         * 캔버스 스냅샷 다운로드 (한글 가이드 PNG)
         * AI/SVG는 영어로 되어있어서 한글 참조용 이미지 제공
         */
        function downloadCanvasSnapshot() {
            const canvas = document.getElementById('sizePreviewCanvas');
            if (!canvas) {
                alert('미리보기 캔버스를 찾을 수 없습니다. 먼저 가로/세로 크기를 입력해주세요.');
                return;
            }

            // 현재 크기 값 가져오기
            const garo = parseFloat(document.getElementById('garo')?.value) || 0;
            const sero = parseFloat(document.getElementById('sero')?.value) || 0;

            if (garo <= 0 || sero <= 0) {
                alert('가로/세로 크기를 먼저 입력해주세요.');
                return;
            }

            // 모양 유형 가져오기
            const shapeType = document.getElementById('uhyung')?.value || 'rectangle';
            const shapeNames = {
                'rectangle': '사각형',
                'rounded': '귀돌이',
                'circle': '원형',
                'ellipse': '타원형'
            };
            const shapeName = shapeNames[shapeType] || '사각형';

            showDownloadProgress();

            try {
                // 캔버스를 PNG로 변환
                const dataUrl = canvas.toDataURL('image/png');

                // 다운로드 링크 생성
                const link = document.createElement('a');
                link.download = `스티커_${garo}x${sero}mm_${shapeName}_가이드.png`;
                link.href = dataUrl;
                link.click();

                console.log('한글 가이드 PNG 다운로드:', { garo, sero, shapeName });
            } catch (error) {
                console.error('캔버스 스냅샷 다운로드 오류:', error);
                alert('이미지 다운로드 중 오류가 발생했습니다.');
            }

            hideDownloadProgress();
        }

        /**
         * 모양 자동 변경 토스트 알림
         * @param {string} message - 표시할 메시지
         * @param {string} type - 'warning' (기본) 또는 'info'
         */
        function showShapeChangeToast(message, type = 'warning') {
            // 기존 토스트 제거
            const existingToast = document.querySelector('.shape-change-toast');
            if (existingToast) {
                existingToast.remove();
            }

            // 토스트 요소 생성
            const toast = document.createElement('div');
            toast.className = 'shape-change-toast';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                padding: 12px 24px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: toastSlideIn 0.3s ease-out;
                ${type === 'info'
                    ? 'background: #E3F2FD; color: #1565C0; border: 1px solid #90CAF9;'
                    : 'background: #FFF3E0; color: #E65100; border: 1px solid #FFCC80;'}
            `;
            toast.textContent = message;

            // 스타일 애니메이션 추가
            if (!document.querySelector('#toastAnimStyle')) {
                const style = document.createElement('style');
                style.id = 'toastAnimStyle';
                style.textContent = `
                    @keyframes toastSlideIn {
                        from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
                        to { opacity: 1; transform: translateX(-50%) translateY(0); }
                    }
                    @keyframes toastSlideOut {
                        from { opacity: 1; transform: translateX(-50%) translateY(0); }
                        to { opacity: 0; transform: translateX(-50%) translateY(-20px); }
                    }
                `;
                document.head.appendChild(style);
            }

            document.body.appendChild(toast);

            // 3초 후 자동 숨김
            setTimeout(() => {
                toast.style.animation = 'toastSlideOut 0.3s ease-out forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        /**
         * 모양 변경 시 입력된 가로/세로 값을 유지하고 미리보기 업데이트
         * 가로/세로 값은 그대로 두고 새로운 모양으로 미리보기만 변경
         * 가로/세로/모양 세 가지를 하나로 인식하여 자동 트리거
         * (updateSizePreview에서 미리보기 + 다운로드 버튼 모두 처리)
         */
        function resetShapeAndPreview() {
            // 가로/세로 값은 유지 (리셋하지 않음)
            // 가격 계산 후 미리보기 업데이트 (미리보기 + 다운로드 버튼 자동 표시)
            calculatePrice();
            updateSizePreview();
        }

        /**
         * 실시간 사이즈 미리보기 업데이트
         * 가로/세로 입력 시 갤러리 위에 플로팅 오버레이로 표시
         */
        function updateSizePreview() {
            const garoInput = document.getElementById('garo');
            const seroInput = document.getElementById('sero');
            const canvas = document.getElementById('sizePreviewCanvas');
            const container = document.getElementById('sizePreviewContainer');
            const dimensionsText = document.getElementById('previewDimensions');

            if (!garoInput || !seroInput || !canvas || !container) return;

            let garo = parseInt(garoInput.value) || 0;
            let sero = parseInt(seroInput.value) || 0;

            // 모양 선택 값 먼저 가져오기
            const domusongSelect = document.getElementById('domusong');
            const domusongValue = domusongSelect ? domusongSelect.value : '00000 사각';

            // 모양별 기본 크기 설정 (미리보기용 - 모양 구분이 명확하도록)
            // 가로/세로가 없을 때 모양에 따라 기본값 설정
            if (garo <= 0 || sero <= 0) {
                if (domusongValue.includes('타원')) {
                    // 타원형: 70x50mm로 명확하게 구분
                    garo = 70;
                    sero = 50;
                } else if (domusongValue.includes('원형')) {
                    // 원형: 50x50mm (정원)
                    garo = 50;
                    sero = 50;
                } else {
                    // 기본사각, 사각도무송, 귀돌이, 복잡: 50x50mm
                    garo = 50;
                    sero = 50;
                }
            }

            // 가로/세로가 유효하면 항상 미리보기 표시 (모양 상관없이)
            // "작업 템플릿 다운로드" 버튼 없이 바로 미리보기
            if (garo > 0 && sero > 0 && garo <= 560 && sero <= 560) {
                // 플로팅 오버레이로 캔버스 표시 (갤러리는 그대로 유지)
                container.style.display = 'block';

                // 다운로드 버튼도 함께 표시 (가로/세로/모양 하나로 인식)
                const downloadButtons = document.getElementById('templateDownloadButtons');
                if (downloadButtons) {
                    downloadButtons.style.display = 'block';
                }

                console.log('미리보기 자동 표시 (작업 템플릿 버튼 생략):', {
                    가로: garo,
                    세로: sero,
                    모양: domusongValue
                });

                // 캔버스 설정
                const ctx = canvas.getContext('2d');
                const canvasWidth = 500;
                const canvasHeight = 400;

                // 캔버스 초기화
                ctx.clearRect(0, 0, canvasWidth, canvasHeight);

                // 모양 타입 판별 (귀돌이, 원형, 타원형, 모양도무송)
                // 주의: 원형/타원형 선택 규칙은 updateCircleEllipseOptions()에서 처리됨
                let shapeType = 'rect'; // 기본 사각형
                if (domusongValue.includes('귀돌')) {
                    shapeType = 'rounded';
                } else if (domusongValue.includes('원형')) {
                    // 원형은 가로=세로일 때만 선택 가능 (규칙에 의해 자동 관리됨)
                    shapeType = 'circle';
                } else if (domusongValue.includes('타원')) {
                    // 타원형은 가로≠세로일 때만 선택 가능 (규칙에 의해 자동 관리됨)
                    shapeType = 'ellipse';
                } else if (domusongValue.includes('복잡')) {
                    shapeType = 'complex'; // 모양도무송
                }

                // 스티커 사양
                const bleed = 3;  // 여유선 +3mm
                const safe = 2;   // 안전선 -2mm

                // 실제 크기 계산 (mm 단위)
                const trimWidth = garo;
                const trimHeight = sero;
                const bleedWidth = trimWidth + (bleed * 2);
                const bleedHeight = trimHeight + (bleed * 2);
                const safeWidth = trimWidth - (safe * 2);
                const safeHeight = trimHeight - (safe * 2);

                // CSS 표준 변환 비율 (1mm = 3.78px)
                const MM_TO_PX = 3.78;

                // mm를 px로 변환
                const bleedWidthPx = bleedWidth * MM_TO_PX;
                const bleedHeightPx = bleedHeight * MM_TO_PX;
                const trimWidthPx = trimWidth * MM_TO_PX;
                const trimHeightPx = trimHeight * MM_TO_PX;
                const safeWidthPx = safeWidth * MM_TO_PX;
                const safeHeightPx = safeHeight * MM_TO_PX;

                // 캔버스에 맞게 비례 스케일링 (40px 여백)
                const padding = 40;
                const availableWidth = canvasWidth - (padding * 2);
                const availableHeight = canvasHeight - (padding * 2);
                // 실제 크기가 캔버스에 맞으면 1:1 스케일 유지, 클 경우만 비례 축소
                const scale = Math.min(1, Math.min(availableWidth / bleedWidthPx, availableHeight / bleedHeightPx));

                // 스케일된 크기 (px 단위에 scale 적용)
                const scaledBleedWidth = bleedWidthPx * scale;
                const scaledBleedHeight = bleedHeightPx * scale;
                const scaledTrimWidth = trimWidthPx * scale;
                const scaledTrimHeight = trimHeightPx * scale;
                const scaledSafeWidth = safeWidthPx * scale;
                const scaledSafeHeight = safeHeightPx * scale;

                // 중앙 배치 계산
                const centerX = canvasWidth / 2;
                const centerY = canvasHeight / 2;
                const bleedX = centerX - (scaledBleedWidth / 2);
                const bleedY = centerY - (scaledBleedHeight / 2);
                const trimX = centerX - (scaledTrimWidth / 2);
                const trimY = centerY - (scaledTrimHeight / 2);
                const safeX = centerX - (scaledSafeWidth / 2);
                const safeY = centerY - (scaledSafeHeight / 2);

                // 모양별 도형 그리기 함수
                function drawShape(ctx, x, y, width, height, type, cornerRadius = 0) {
                    ctx.beginPath();
                    if (type === 'rect' || type === 'rounded') {
                        if (type === 'rounded' && cornerRadius > 0) {
                            // 둥근 모서리 사각형
                            const r = Math.min(cornerRadius, width / 2, height / 2);
                            ctx.moveTo(x + r, y);
                            ctx.lineTo(x + width - r, y);
                            ctx.quadraticCurveTo(x + width, y, x + width, y + r);
                            ctx.lineTo(x + width, y + height - r);
                            ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
                            ctx.lineTo(x + r, y + height);
                            ctx.quadraticCurveTo(x, y + height, x, y + height - r);
                            ctx.lineTo(x, y + r);
                            ctx.quadraticCurveTo(x, y, x + r, y);
                        } else {
                            // 기본 사각형
                            ctx.rect(x, y, width, height);
                        }
                    } else if (type === 'circle') {
                        // 원형 (가로세로 중 작은 값 기준)
                        const radius = Math.min(width, height) / 2;
                        const cx = x + width / 2;
                        const cy = y + height / 2;
                        ctx.arc(cx, cy, radius, 0, Math.PI * 2);
                    } else if (type === 'ellipse') {
                        // 타원형
                        const cx = x + width / 2;
                        const cy = y + height / 2;
                        ctx.ellipse(cx, cy, width / 2, height / 2, 0, 0, Math.PI * 2);
                    } else if (type === 'complex') {
                        // 모양도무송 - 물결선 테두리
                        const cx = x + width / 2;
                        const cy = y + height / 2;
                        const waveCount = 12;
                        const waveDepth = Math.min(width, height) * 0.08;
                        const rx = width / 2;
                        const ry = height / 2;

                        ctx.moveTo(cx + rx, cy);
                        for (let i = 0; i <= waveCount * 4; i++) {
                            const angle = (i / (waveCount * 4)) * Math.PI * 2;
                            const wave = Math.sin(angle * waveCount) * waveDepth;
                            const px = cx + (rx + wave) * Math.cos(angle);
                            const py = cy + (ry + wave) * Math.sin(angle);
                            ctx.lineTo(px, py);
                        }
                    }
                    ctx.closePath();
                    ctx.stroke();
                }

                // 모양도무송(복잡) 특별 캔버스 그리기
                function drawComplexShapePreview(ctx, centerX, centerY, width, height) {
                    // 배경 영역 (연한 핑크)
                    ctx.fillStyle = 'rgba(233, 30, 99, 0.05)';
                    ctx.fillRect(centerX - width/2 - 20, centerY - height/2 - 20, width + 40, height + 40);

                    // 물결선 테두리
                    ctx.strokeStyle = '#E91E63';
                    ctx.lineWidth = 3;
                    ctx.setLineDash([8, 4]);

                    const waveCount = 10;
                    const waveDepth = Math.min(width, height) * 0.06;
                    const rx = width / 2;
                    const ry = height / 2;

                    ctx.beginPath();
                    for (let i = 0; i <= waveCount * 4; i++) {
                        const angle = (i / (waveCount * 4)) * Math.PI * 2;
                        const wave = Math.sin(angle * waveCount) * waveDepth;
                        const px = centerX + (rx + wave) * Math.cos(angle);
                        const py = centerY + (ry + wave) * Math.sin(angle);
                        if (i === 0) ctx.moveTo(px, py);
                        else ctx.lineTo(px, py);
                    }
                    ctx.closePath();
                    ctx.stroke();

                    // 고양이 얼굴 아이콘
                    ctx.setLineDash([]);
                    const iconSize = Math.min(width, height) * 0.4;
                    const iconY = centerY - 15;

                    // 고양이 얼굴 (원)
                    ctx.fillStyle = '#E91E63';
                    ctx.beginPath();
                    ctx.arc(centerX, iconY, iconSize * 0.35, 0, Math.PI * 2);
                    ctx.fill();

                    // 귀 (삼각형)
                    ctx.beginPath();
                    ctx.moveTo(centerX - iconSize * 0.28, iconY - iconSize * 0.15);
                    ctx.lineTo(centerX - iconSize * 0.15, iconY - iconSize * 0.45);
                    ctx.lineTo(centerX - iconSize * 0.02, iconY - iconSize * 0.2);
                    ctx.fill();

                    ctx.beginPath();
                    ctx.moveTo(centerX + iconSize * 0.28, iconY - iconSize * 0.15);
                    ctx.lineTo(centerX + iconSize * 0.15, iconY - iconSize * 0.45);
                    ctx.lineTo(centerX + iconSize * 0.02, iconY - iconSize * 0.2);
                    ctx.fill();

                    // 눈 (흰색 원)
                    ctx.fillStyle = '#fff';
                    ctx.beginPath();
                    ctx.arc(centerX - iconSize * 0.12, iconY - iconSize * 0.05, iconSize * 0.08, 0, Math.PI * 2);
                    ctx.arc(centerX + iconSize * 0.12, iconY - iconSize * 0.05, iconSize * 0.08, 0, Math.PI * 2);
                    ctx.fill();

                    // 코 (작은 삼각형)
                    ctx.fillStyle = '#fff';
                    ctx.beginPath();
                    ctx.moveTo(centerX, iconY + iconSize * 0.05);
                    ctx.lineTo(centerX - iconSize * 0.05, iconY + iconSize * 0.15);
                    ctx.lineTo(centerX + iconSize * 0.05, iconY + iconSize * 0.15);
                    ctx.fill();

                    // 안내 텍스트
                    ctx.fillStyle = '#E91E63';
                    ctx.font = 'bold 16px "Noto Sans KR", sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText('🐱 모양도무송', centerX, centerY + iconSize * 0.5 + 20);

                    ctx.font = '13px "Noto Sans KR", sans-serif';
                    ctx.fillStyle = '#666';
                    ctx.fillText('라인 전화문의', centerX, centerY + iconSize * 0.5 + 42);

                    ctx.fillStyle = '#E91E63';
                    ctx.font = 'bold 14px "Noto Sans KR", sans-serif';
                    ctx.fillText('📞 02-2632-1830', centerX, centerY + iconSize * 0.5 + 62);
                }

                // 귀돌이 라운드 반경 계산 (70×70mm 기준 3mm → 비례 계산)
                const baseSize = 70; // mm 기준
                const baseRadius = 3; // mm 기준 라운드
                const avgSize = (garo + sero) / 2;
                const cornerRadiusMm = (avgSize / baseSize) * baseRadius;
                const cornerRadiusPx = cornerRadiusMm * MM_TO_PX * scale;

                // 모양도무송(complex)일 때는 특별 캔버스 그리기
                if (shapeType === 'complex') {
                    drawComplexShapePreview(ctx, centerX, centerY, scaledTrimWidth, scaledTrimHeight);
                } else {
                    // 1. 여유선 (오렌지 점선, 가장 바깥)
                    ctx.strokeStyle = '#FF8C00';
                    ctx.lineWidth = 1;
                    ctx.setLineDash([3, 3]);
                    drawShape(ctx, bleedX, bleedY, scaledBleedWidth, scaledBleedHeight, shapeType, cornerRadiusPx * 1.1);

                    // 2. 재단선 (검정 실선)
                    ctx.strokeStyle = '#000000';
                    ctx.lineWidth = 2;
                    ctx.setLineDash([]);
                    drawShape(ctx, trimX, trimY, scaledTrimWidth, scaledTrimHeight, shapeType, cornerRadiusPx);

                    // 3. 안전선 (청색 점선, 가장 안쪽)
                    ctx.strokeStyle = '#0000FF';
                    ctx.lineWidth = 1;
                    ctx.setLineDash([3, 3]);
                    drawShape(ctx, safeX, safeY, scaledSafeWidth, scaledSafeHeight, shapeType, cornerRadiusPx * 0.9);

                    // 라벨 표시
                    ctx.setLineDash([]);
                    ctx.font = '11px "Noto Sans KR", sans-serif';
                    ctx.textAlign = 'center';

                    // 재단선 라벨 (중앙)
                    ctx.fillStyle = '#000000';
                    ctx.font = 'bold 14px "Noto Sans KR", sans-serif';
                    ctx.fillText(`재단선 ${garo}×${sero}mm`, centerX, centerY);

                    // 안전선 라벨 (하단 안쪽 점선 바로 위)
                    ctx.font = '11px "Noto Sans KR", sans-serif';
                    ctx.fillStyle = '#0000FF';
                    ctx.fillText(`안전선 -${safe}mm`, centerX, safeY + scaledSafeHeight - 6);

                    // 여유선 라벨 (하단 바깥쪽 점선 가까이)
                    ctx.fillStyle = '#FF8C00';
                    ctx.fillText(`여유선 +${bleed}mm`, centerX, bleedY + scaledBleedHeight + 10);
                }

                // 모양 이름 매핑
                const shapeNames = {
                    'rect': '사각형',
                    'rounded': '귀돌이',
                    'circle': '원형',
                    'ellipse': '타원형',
                    'complex': '모양도무송'
                };
                const shapeName = shapeNames[shapeType] || '사각형';

                // 모양 배지 표시 (좌상단) - 현재 선택된 모양을 명확하게 표시
                const badgeColors = {
                    'rect': { bg: '#333', text: '#fff' },
                    'rounded': { bg: '#4CAF50', text: '#fff' },
                    'circle': { bg: '#2196F3', text: '#fff' },
                    'ellipse': { bg: '#9C27B0', text: '#fff' },
                    'complex': { bg: '#E91E63', text: '#fff' }
                };
                const badgeColor = badgeColors[shapeType] || badgeColors['rect'];

                ctx.font = 'bold 12px "Noto Sans KR", sans-serif';
                const badgeText = `모양: ${shapeName}`;
                const badgeWidth = ctx.measureText(badgeText).width + 16;
                const badgeHeight = 24;
                const badgeX = 10;
                const badgeY = 10;

                // 배지 배경 (roundRect 폴백 포함)
                ctx.fillStyle = badgeColor.bg;
                ctx.beginPath();
                if (ctx.roundRect) {
                    ctx.roundRect(badgeX, badgeY, badgeWidth, badgeHeight, 4);
                } else {
                    // 구형 브라우저 폴백
                    const r = 4;
                    ctx.moveTo(badgeX + r, badgeY);
                    ctx.lineTo(badgeX + badgeWidth - r, badgeY);
                    ctx.quadraticCurveTo(badgeX + badgeWidth, badgeY, badgeX + badgeWidth, badgeY + r);
                    ctx.lineTo(badgeX + badgeWidth, badgeY + badgeHeight - r);
                    ctx.quadraticCurveTo(badgeX + badgeWidth, badgeY + badgeHeight, badgeX + badgeWidth - r, badgeY + badgeHeight);
                    ctx.lineTo(badgeX + r, badgeY + badgeHeight);
                    ctx.quadraticCurveTo(badgeX, badgeY + badgeHeight, badgeX, badgeY + badgeHeight - r);
                    ctx.lineTo(badgeX, badgeY + r);
                    ctx.quadraticCurveTo(badgeX, badgeY, badgeX + r, badgeY);
                    ctx.closePath();
                }
                ctx.fill();

                // 배지 텍스트
                ctx.fillStyle = badgeColor.text;
                ctx.textAlign = 'left';
                ctx.fillText(badgeText, badgeX + 8, badgeY + 16);

                // 차원 정보 업데이트 (모양 포함)
                if (dimensionsText) {
                    dimensionsText.innerHTML = `<strong>${garo}×${sero}mm</strong> / ${shapeName}`;
                }

                // AI 다운로드 버튼 표시 여부 업데이트
                updateAIDownloadVisibility();
            } else {
                // 입력값이 없어도 미리보기는 유지 (닫기 버튼으로만 숨김)
                // 플로팅 미리보기가 한번 표시되면 파일 업로드/주문 전까지 계속 표시
                // AI 다운로드 버튼만 업데이트
                updateAIDownloadVisibility();
            }
        }

        /**
         * 사이즈 미리보기 숨기기 (입력 완료 시 또는 닫기 버튼 클릭 시)
         */
        function hideSizePreview() {
            const container = document.getElementById('sizePreviewContainer');
            if (container) container.style.display = 'none';
            // 다운로드 버튼도 함께 숨기기
            const downloadButtons = document.getElementById('templateDownloadButtons');
            if (downloadButtons) downloadButtons.style.display = 'none';
            // 갤러리는 항상 표시 상태 유지 (플로팅 오버레이 방식이므로 별도 처리 불필요)
        }

        /**
         * AI 도무송 템플릿 다운로드 (Adobe Illustrator 호환)
         */
        function downloadAITemplate() {
            const garoInput = document.getElementById('garo');
            const seroInput = document.getElementById('sero');
            const domusongSelect = document.getElementById('domusong');

            if (!garoInput || !seroInput || !domusongSelect) {
                alert('크기 입력란을 찾을 수 없습니다.');
                return;
            }

            const garo = parseInt(garoInput.value) || 0;
            const sero = parseInt(seroInput.value) || 0;
            const domusongValue = domusongSelect.value;

            // 크기 유효성 검사
            if (garo < 5 || garo > 500 || sero < 5 || sero > 500) {
                alert('가로/세로 크기를 5~500mm 범위로 입력해주세요.');
                return;
            }

            // 모양 타입 결정
            let shapeType = 'rectangle';
            let cornerRadius = 0;

            if (domusongValue.includes('귀돌')) {
                shapeType = 'rounded';
                cornerRadius = Math.min(garo, sero) * 0.1; // 기본 귀돌이 반경 10%
            } else if (domusongValue.includes('원형')) {
                shapeType = 'circle';
            } else if (domusongValue.includes('타원')) {
                shapeType = 'ellipse';
            }

            // 다운로드 URL 생성
            const url = `download_ai.php?garo=${garo}&sero=${sero}&shape=${shapeType}&corner=${cornerRadius}`;

            console.log('AI 템플릿 다운로드:', { garo, sero, shapeType, cornerRadius, url });

            // 다운로드 실행
            window.location.href = url;
        }

        /**
         * AI 다운로드 섹션 표시/숨김 업데이트
         * 도무송 선택 시에만 다운로드 버튼 표시
         */
        function updateAIDownloadVisibility() {
            const domusongSelect = document.getElementById('domusong');
            const aiDownloadSection = document.getElementById('aiDownloadSection');
            const garoInput = document.getElementById('garo');
            const seroInput = document.getElementById('sero');

            if (!domusongSelect || !aiDownloadSection) return;

            const domusongValue = domusongSelect.value;
            const garo = parseInt(garoInput?.value) || 0;
            const sero = parseInt(seroInput?.value) || 0;

            // 도무송 옵션이 선택되고 크기가 입력된 경우에만 표시
            const isDomusongSelected = domusongValue.includes('도무송') ||
                                       domusongValue.includes('귀돌') ||
                                       domusongValue.includes('원형') ||
                                       domusongValue.includes('타원') ||
                                       domusongValue.includes('복잡');
            const hasSizeInput = garo > 0 && sero > 0;

            if (isDomusongSelected && hasSizeInput) {
                aiDownloadSection.style.display = 'block';
            } else {
                aiDownloadSection.style.display = 'none';
            }
        }

        /**
         * 견적서에 데이터 전송 (스티커 전용)
         */
        window.sendToQuotation = function() {
            console.log('📤 [TUNNEL 2/5] "✅ 견적서에 적용" 버튼 클릭됨');

            // window.currentPriceData 또는 로컬 currentPriceData 변수 확인
            const priceData = window.currentPriceData || (typeof currentPriceData !== 'undefined' ? currentPriceData : null);
            console.log('📊 가격 데이터 확인:', priceData);

            // 가격 계산 확인
            if (!priceData || !priceData.price) {
                console.error('❌ 가격 데이터 없음');
                alert('먼저 견적 계산을 해주세요. "견적 계산" 버튼을 눌러주세요.');
                return;
            }

            console.log('✅ 계산된 가격 데이터:', priceData);

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '📝 견적서에 입력 중...';

            try {
                // 폼에서 제품 스펙 정보 수집 (스티커 전용)
                const jongSelect = document.getElementById('jong');
                const garoInput = document.getElementById('garo');
                const seroInput = document.getElementById('sero');
                const mesuSelect = document.getElementById('mesu');
                const uhyungSelect = document.getElementById('uhyung');
                const domusongSelect = document.getElementById('domusong');

                // 선택된 옵션의 텍스트 추출
                const jongText = jongSelect ? jongSelect.options[jongSelect.selectedIndex].text : '';
                const garoValue = garoInput ? garoInput.value : '';
                const seroValue = seroInput ? seroInput.value : '';
                const mesuText = mesuSelect ? mesuSelect.options[mesuSelect.selectedIndex].text : '';
                const uhyungText = uhyungSelect ? uhyungSelect.options[uhyungSelect.selectedIndex].text : '';
                const domusongText = domusongSelect ? domusongSelect.options[domusongSelect.selectedIndex].text : '';

                // 규격 문자열 생성
                const specification = `${jongText} / ${garoValue}×${seroValue}mm / ${mesuText} / ${uhyungText} / ${domusongText}`.trim();

                // 수량 값 추출
                const quantityValue = parseInt(mesuSelect.value) || 1000;

                // 가격에서 쉼표 제거하고 숫자로 변환
                const supplyPrice = parseInt(priceData.price.replace(/,/g, '')) || 0;
                const vatPrice = parseInt(priceData.price_vat.replace(/,/g, '')) || 0;

                // 견적서 폼에 전달할 데이터 구조
                const quotationData = {
                    product_name: '스티커',
                    product_type: 'sticker',
                    specification: specification,
                    quantity: quantityValue,
                    mesu: mesuSelect ? mesuSelect.value : '',  // ✅ mesu를 최상위로 추가!
                    unit: '매',
                    supply_price: supplyPrice,
                    vat_price: vatPrice,

                    // 원본 스펙 데이터 (quotation_temp 저장용)
                    jong: jongSelect ? jongSelect.value : '',
                    garo: garoValue,
                    sero: seroValue,
                    uhyung: uhyungSelect ? uhyungSelect.value : '',
                    domusong: domusongSelect ? domusongSelect.value : '',

                    // 원본 계산 데이터도 포함 (디버깅용)
                    _debug: {
                        calculated_price: window.currentPriceData
                    }
                };

                console.log('📨 [TUNNEL 3/5] 견적서 데이터 전송:', quotationData);

                // 부모 창으로 데이터 전송 (calculator_modal.js의 handlePriceData가 수신)
                window.parent.postMessage({
                    type: 'CALCULATOR_PRICE_DATA',
                    payload: quotationData
                }, window.location.origin);

                // 성공 피드백
                btn.innerHTML = '✅ 견적서에 적용됨!';
                btn.style.background = '#28a745';

                console.log('✅ [TUNNEL 5/5] 견적서 폼 입력 완료 - 모달은 자동으로 닫힙니다');

            } catch (error) {
                console.error('❌ 견적서 데이터 전송 실패:', error);
                alert('견적서 적용 중 오류가 발생했습니다: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.style.background = '#217346';
            }
        };

        // 견적서 모드일 때 가격 계산 후 2단계 버튼 표시
        document.addEventListener('DOMContentLoaded', function() {
            // 견적서 모드 버튼 표시는 가격 계산 성공 시 자동으로 처리됨
        });
    </script>

    </div> <!-- product-container 끝 -->

    <!-- 재질 안내 모달 -->
    <div id="materialGuideModal" class="material-modal" style="display: none;">
        <div class="material-modal-overlay" onclick="closeMaterialGuide()"></div>
        <div class="material-modal-content">
            <div class="material-modal-header">
                <h2>📋 스티커 재질 안내</h2>
                <button class="material-modal-close" onclick="closeMaterialGuide()">&times;</button>
            </div>
            <div class="material-modal-body">
                <img src="../../shop/img/stickermaterial.jpg" alt="스티커 재질 안내" style="width: 100%; height: auto;">
            </div>
        </div>
    </div>

<?php /* db close moved after footer */ ?>

    <!-- 견적서 모달 공통 JavaScript -->
    <script src="../../js/quotation-modal-common.js"></script>

<?php if ($is_quotation_mode): ?>
    <!-- 견적서 모드: add_to_basket.php로 직접 저장 후 postMessage로 모달 닫기 -->
    <script>
    // applyToQuotation() 함수를 재정의하여 add_to_basket.php?mode=quotation 사용
    window.applyToQuotation = function() {
        console.log('🚀 [스티커 견적서] applyToQuotation() 호출 - 새 로직 사용');

        // 1. 필수 필드 검증
        const jong = document.getElementById('jong')?.value;
        const garo = document.getElementById('garo')?.value;
        const sero = document.getElementById('sero')?.value;
        const mesu = document.getElementById('mesu')?.value;

        if (!jong || !garo || !sero || !mesu) {
            alert('모든 필수 옵션을 선택해주세요.');
            console.error('❌ 필수 필드 누락');
            return;
        }

        // 2. 가격 계산 확인 및 자동 계산
        if (!window.currentPriceData || !window.currentPriceData.price) {
            console.log('⚠️ 가격 데이터 없음 - 자동 계산 시도');

            // 자동으로 가격 계산 실행 (스티커는 autoCalculatePrice 사용)
            if (typeof window.autoCalculatePrice === 'function') {
                console.log('📞 autoCalculatePrice() 자동 호출');
                window.autoCalculatePrice();

                // 계산 완료 대기 (최대 3초)
                let attempts = 0;
                const maxAttempts = 30;

                const waitForPrice = setInterval(() => {
                    attempts++;

                    if (window.currentPriceData && window.currentPriceData.price) {
                        // 계산 완료
                        clearInterval(waitForPrice);
                        console.log('✅ 자동 가격 계산 완료');
                        // 재귀 호출로 다시 시도
                        window.applyToQuotation();
                    } else if (attempts >= maxAttempts) {
                        // 타임아웃
                        clearInterval(waitForPrice);
                        alert('가격 계산에 실패했습니다. 옵션을 다시 확인해주세요.');
                        console.error('❌ 가격 계산 타임아웃');
                    }
                }, 100);

                return; // 비동기 처리 대기
            } else if (typeof window.calculatePrice === 'function') {
                // 폴백: calculatePrice() 시도
                console.log('📞 calculatePrice() 자동 호출 (폴백)');
                window.calculatePrice();

                // 동일한 대기 로직
                let attempts = 0;
                const maxAttempts = 30;

                const waitForPrice = setInterval(() => {
                    attempts++;

                    if (window.currentPriceData && window.currentPriceData.price) {
                        clearInterval(waitForPrice);
                        console.log('✅ 자동 가격 계산 완료');
                        window.applyToQuotation();
                    } else if (attempts >= maxAttempts) {
                        clearInterval(waitForPrice);
                        alert('가격 계산에 실패했습니다. 옵션을 다시 확인해주세요.');
                        console.error('❌ 가격 계산 타임아웃');
                    }
                }, 100);

                return;
            } else {
                alert('가격 계산 함수를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
                console.error('❌ 가격 계산 함수 없음 (autoCalculatePrice, calculatePrice 둘 다 없음)');
                return;
            }
        }

        // 3. FormData 구성 (기존 add_to_basket 로직 재사용)
        const form = document.getElementById('stickerForm');
        const formData = new FormData(form);

        // 가격에서 콤마 제거
        const priceStr = window.currentPriceData.price.toString().replace(/,/g, '');
        const priceVatStr = window.currentPriceData.price_vat.toString().replace(/,/g, '');
        const supplyPrice = parseInt(priceStr) || 0;
        const totalWithVat = parseInt(priceVatStr) || supplyPrice;

        // 필수 데이터 추가
        formData.set('action', 'add_to_basket');
        formData.set('st_price', supplyPrice);
        formData.set('st_price_vat', totalWithVat);
        formData.set('product_type', 'sticker');

        // 수량 표시 (quantity_display) 추가 - 드롭다운 텍스트
        const mesuSelect = document.getElementById('mesu');
        let quantityDisplay = mesu;
        if (mesuSelect && mesuSelect.selectedOptions[0]) {
            quantityDisplay = mesuSelect.selectedOptions[0].text;
            formData.set('quantity_display', quantityDisplay);
            console.log('📋 quantity_display:', quantityDisplay);
        }

        // 업로드된 파일 정보 추가 (StandardUploadHandler와 동일한 형식)
        if (window.uploadedFiles && window.uploadedFiles.length > 0) {
            const fileInfoArray = window.uploadedFiles.map(file => ({
                name: file.name,
                size: file.size,
                path: file.path
            }));
            formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
        }

        console.log('📤 [스티커 견적서] add_to_basket.php?mode=quotation 호출');

        // 4. add_to_basket.php?mode=quotation 호출
        fetch('./add_to_basket.php?mode=quotation', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ [스티커 견적서] 응답:', data);

            if (data.success) {
                // 부모 창에 postMessage로 모달 닫기 요청 (calculator_modal.js가 처리)
                if (window.parent && window.parent !== window) {
                    console.log('📨 [스티커 견적서] CALCULATOR_CLOSE_MODAL 메시지 전송');
                    window.parent.postMessage({
                        type: 'CALCULATOR_CLOSE_MODAL'
                    }, window.location.origin);
                } else {
                    // 부모 창이 없으면 직접 알림
                    alert('견적서에 추가되었습니다!');
                    console.warn('⚠️ 부모 창 없음 (모달이 아님)');
                }
            } else {
                alert('견적서 추가 실패: ' + (data.message || '알 수 없는 오류'));
                console.error('❌ 견적서 추가 실패:', data);
            }
        })
        .catch(error => {
            console.error('❌ [스티커 견적서] 네트워크 오류:', error);
            alert('견적서 추가 중 오류가 발생했습니다: ' + error.message);
        });
    };

    console.log('✅ [스티커 견적서] applyToQuotation() 재정의 완료');
    </script>
<?php endif; ?>

<?php if ($is_admin_quote_mode): ?>
    <!-- 관리자 견적서 모달용 applyToQuotation 함수 -->
    <script>
    /**
     * 견적서에 스티커 품목 추가
     * calculator_modal.js가 ADMIN_QUOTE_ITEM_ADDED 메시지를 수신
     */
    window.applyToQuotation = function() {
        console.log('🚀 [관리자 견적서-스티커] applyToQuotation() 호출');

        // 1. 필수 필드 검증
        const jong = document.getElementById('jong')?.value;
        const garo = document.getElementById('garo')?.value;
        const sero = document.getElementById('sero')?.value;
        const mesu = document.getElementById('mesu')?.value;

        if (!jong || !garo || !sero || !mesu) {
            alert('모든 필수 옵션을 선택해주세요.');
            return;
        }

        // 2. 가격 확인 및 자동 계산
        if (!window.currentPriceData || !window.currentPriceData.price) {
            console.log('⚠️ 가격 데이터 없음 - 자동 계산 시도');
            if (typeof window.autoCalculatePrice === 'function') {
                window.autoCalculatePrice();
                let attempts = 0;
                const waitForPrice = setInterval(() => {
                    attempts++;
                    if (window.currentPriceData && window.currentPriceData.price) {
                        clearInterval(waitForPrice);
                        console.log('✅ 가격 계산 완료');
                        window.applyToQuotation();
                    } else if (attempts >= 30) {
                        clearInterval(waitForPrice);
                        alert('가격 계산에 실패했습니다.');
                    }
                }, 100);
                return;
            }
            alert('가격을 먼저 계산해주세요.');
            return;
        }

        // 공급가액 계산 (VAT 미포함)
        const priceStr = window.currentPriceData.price.toString().replace(/,/g, '');
        const supplyPrice = parseInt(priceStr) || 0;

        if (supplyPrice <= 0) {
            alert('유효한 가격이 계산되지 않았습니다.');
            return;
        }

        // 3. 사양 텍스트 생성 (2줄 형식)
        const jongText = document.getElementById('jong')?.options[document.getElementById('jong').selectedIndex]?.text || '';
        const uhyungEl = document.getElementById('uhyung');
        const uhyungText = uhyungEl?.options[uhyungEl.selectedIndex]?.text || '';
        const domusongEl = document.getElementById('domusong');
        const domusongText = domusongEl?.options[domusongEl.selectedIndex]?.text || '';

        // 1줄: 종류 / 규격(가로x세로)
        const line1 = `${jongText} / ${garo}x${sero}mm`;

        // 2줄: 형태 / 도무송 (있는 경우만)
        let line2Parts = [];
        if (uhyungText && uhyungText !== '선택' && uhyungText !== '선택하세요') line2Parts.push(uhyungText);
        if (domusongText && domusongText !== '선택' && domusongText !== '선택하세요' && domusongText !== '없음') line2Parts.push(domusongText);
        const line2 = line2Parts.join(' / ');

        // 2줄 형식으로 결합 (줄바꿈 사용)
        const specification = line2 ? `${line1}\n${line2}` : line1;

        // 4. 수량 처리
        const mesuSelect = document.getElementById('mesu');
        const quantityDisplay = mesuSelect?.options[mesuSelect.selectedIndex]?.text || mesu;
        const quantity = parseInt(mesu) || 0;

        // 5. 페이로드 생성
        const payload = {
            product_type: 'sticker',
            product_name: '스티커',
            quantity: quantity,
            unit: '매',
            quantity_display: quantityDisplay,
            supply_price: supplyPrice,
            unit_price: quantity > 0 ? Math.round(supplyPrice / quantity) : 0,
            specification: specification,
            jong: jong,
            garo: garo,
            sero: sero,
            mesu: mesu,
            uhyung: document.getElementById('uhyung')?.value || '',
            domusong: document.getElementById('domusong')?.value || '',
            st_price: supplyPrice,
            st_price_vat: Math.round(supplyPrice * 1.1)
        };

        console.log('📤 [스티커] postMessage 전송:', payload);

        // 6. 부모 창으로 메시지 전송
        window.parent.postMessage({
            type: 'ADMIN_QUOTE_ITEM_ADDED',
            payload: payload
        }, window.location.origin);
    };

    console.log('✅ [관리자 견적서-스티커] applyToQuotation() 정의 완료');
    </script>
<?php endif; ?>

<?php if (!$is_quotation_mode && !$is_admin_quote_mode): ?>
    <?php
    // 견적 위젯 (모달 모드 제외)
    if (!$isQuotationMode && !$isAdminQuoteMode) {
        include __DIR__ . '/../../includes/quote_gauge.php';
        echo '<script src="/js/quote-gauge.js?v=' . time() . '"></script>';
    }
    // 공통 푸터 포함
    include "../../includes/footer.php";
    ?>
<?php else: ?>
    </body>
    </html>
<?php endif; ?>
<?php if (isset($db) && $db) { mysqli_close($db); } ?>