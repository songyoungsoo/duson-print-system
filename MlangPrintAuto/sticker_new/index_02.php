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

// 통합 갤러리 시스템 초기화
if (file_exists('../../includes/gallery_helper.php')) { if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; } }
if (function_exists("init_gallery_system")) { init_gallery_system("sticker"); }

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("스티커 견적안내 - 프리미엄");

// 스티커 기본값 설정
$default_values = [
    'jong' => 'jil 아트유광', // 기본값: 아트지유광
    'garo' => '100', // 기본 가로 사이즈
    'sero' => '100', // 기본 세로 사이즈
    'mesu' => '1000', // 기본 수량
    'uhyung' => '0', // 기본값: 인쇄만
    'domusong' => '00000 사각' // 기본 모양
];

// 스티커용 기본 설정은 하드코딩으로 처리 (수식 기반 계산이므로 DB 조회 불필요)
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 공통 헤더 포함 -->
    <?php include "../../includes/header.php"; ?>
    
    <!-- 스티커 컴팩트 페이지 전용 CSS (PROJECT_SUCCESS_REPORT.md 스펙) -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../assets/css/gallery.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    
    <!-- 스티커 전용 JavaScript -->
    <script src="../../js/sticker.js" defer></script>
    
    <!-- 스티커 가로/세로 input 전용 스타일 -->
    <style>
        /* 가로/세로 input에만 적용 */
        input#garo, input#sero {
            width: 80px !important;
            font-size: 1rem !important;
            color: #333 !important;
            height: auto !important;
            padding: 10px 8px !important;
            border: 2px solid #e9ecef !important;
            border-radius: 2px !important;
            box-sizing: border-box !important;
            font-weight: 500 !important;
            background: white !important;
            transition: all 0.3s ease !important;
        }
        
        input#garo:focus, input#sero:focus {
            outline: none !important;
            border-color: #3498db !important;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1) !important;
        }
        
        input#garo::placeholder, input#sero::placeholder {
            color: #ffc107 !important;
            opacity: 0.9 !important;
            font-weight: 400 !important;
            font-family: 'Noto Sans KR', sans-serif !important;
            font-size: 12px !important;
        }
        
        input#garo::-webkit-input-placeholder, input#sero::-webkit-input-placeholder {
            color: #ffc107 !important;
            opacity: 0.9 !important;
            font-weight: 400 !important;
            font-family: 'Noto Sans KR', sans-serif !important;
            font-size: 12px !important;
        }
        
        input#garo::-moz-placeholder, input#sero::-moz-placeholder {
            color: #ffc107 !important;
            opacity: 0.9 !important;
            font-weight: 400 !important;
            font-family: 'Noto Sans KR', sans-serif !important;
            font-size: 12px !important;
        }
        
        input#garo:-ms-input-placeholder, input#sero:-ms-input-placeholder {
            color: #ffc107 !important;
            opacity: 0.9 !important;
            font-weight: 400 !important;
            font-family: 'Noto Sans KR', sans-serif !important;
            font-size: 12px !important;
        }

        /* 한 줄 레이아웃 폼 스타일 */
        .inline-form-container {
            margin: 15px 0;
            padding: 0;
        }

        .inline-form-row {
            display: flex;
            align-items: center;
            margin: 4px 0;
            gap: 6px;
            min-height: 36px;
        }

        .inline-label {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #495057 !important;
            min-width: 40px;
            text-align: left;
            margin: 0;
            font-family: 'Noto Sans KR', sans-serif;
        }

        .inline-select {
            flex: 0 0 140px;
            height: 32px !important;
            padding: 0 8px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 4px !important;
            font-size: 14px !important;
            color: #495057 !important;
            background: white !important;
            font-family: 'Noto Sans KR', sans-serif !important;
        }

        .inline-input {
            flex: 0 0 100px;
            height: 32px !important;
            padding: 0 8px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 4px !important;
            font-size: 14px !important;
            color: #495057 !important;
            background: white !important;
            font-family: 'Noto Sans KR', sans-serif !important;
        }

        .inline-note {
            font-size: 11px !important;
            color: #dc3545 !important;
            margin: 0;
            flex: 1;
            font-family: 'Noto Sans KR', sans-serif;
        }

        .inline-select:focus, .inline-input:focus {
            outline: none !important;
            border-color: #007bff !important;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1) !important;
        }
    </style>
    
    <!-- 세션 ID 및 스티커 기본값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-jong" content="<?php echo htmlspecialchars($default_values['jong']); ?>">
    <meta name="default-garo" content="<?php echo htmlspecialchars($default_values['garo']); ?>">
    <meta name="default-sero" content="<?php echo htmlspecialchars($default_values['sero']); ?>">
    <meta name="default-mesu" content="<?php echo htmlspecialchars($default_values['mesu']); ?>">
</head>
<body>
    <?php include "../../includes/nav.php"; ?>

    <div class="compact-container">
    
    <style>
    /* 스티커를 명함과 동일한 크기로 조정 + 스크롤 방지 */
    .compact-container {
        max-width: 1200px !important;
        margin: 0 auto !important;
        padding: 10px 20px 20px 20px !important;
        background: white !important;
        border-radius: 15px !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
    }
    
    .main-content {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 30px !important;
        min-height: 450px !important;
        max-width: 1200px !important;
        margin: 0 auto !important;
        align-items: start !important;
    }
    
    /* 계산기 섹션 스크롤 방지 조정 */
    .calculator-section {
        height: 450px !important;
        max-height: 450px !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    
    /* 테이블 전체 높이 조정 */
    .order-form-table {
        width: 100% !important;
        border-collapse: collapse !important;
        flex-grow: 1 !important;
        margin-bottom: 8px !important;
    }
    
    /* 테이블 행 높이 최소화 */
    .order-form-table tr {
        height: auto !important;
        min-height: 35px !important;
    }
    
    /* 셀 패딩 더 축소 */
    .label-cell, .input-cell {
        padding: 4px 8px !important;
        vertical-align: top !important;
    }
    
    /* 아이콘 라벨 컴팩트화 */
    .icon-label {
        font-size: 0.85rem !important;
        line-height: 1.2 !important;
        display: flex !important;
        align-items: center !important;
        gap: 5px !important;
    }
    
    .icon-label .icon {
        font-size: 0.9rem !important;
    }
    
    /* 폼 컨트롤 높이 축소 */
    .form-control-modern {
        padding: 4px 8px !important;
        font-size: 0.85rem !important;
        height: 32px !important;
        border-radius: 4px !important;
    }
    
    /* 크기 입력 필드 컴팩트화 */
    .size-inputs {
        margin: 0 !important;
    }
    
    input#garo, input#sero {
        width: 60px !important;
        height: 28px !important;
        padding: 4px 6px !important;
        font-size: 0.8rem !important;
    }
    
    .size-label {
        font-size: 0.8rem !important;
    }
    
    /* help-text 완전 제거 또는 최소화 */
    .help-text {
        font-size: 0.7rem !important;
        margin: 2px 0 0 0 !important;
        line-height: 1.1 !important;
    }
    
    /* 가격 표시 영역 컴팩트화 */
    .price-display {
        margin: 8px 0 !important;
        padding: 8px !important;
        flex-shrink: 0 !important;
    }
    
    .price-label {
        font-size: 0.8rem !important;
        margin-bottom: 4px !important;
    }
    
    .price-amount {
        font-size: 1rem !important;
        margin: 4px 0 !important;
    }
    
    .price-details {
        font-size: 0.7rem !important;
        margin-top: 4px !important;
    }
    
    /* 업로드 버튼 컴팩트화 */
    .upload-order-button {
        margin-top: 4px !important;
        margin-bottom: 0 !important;
        flex-shrink: 0 !important;
    }
    
    .btn-upload-order {
        padding: 8px 16px !important;
        font-size: 0.85rem !important;
        background: linear-gradient(135deg, #4CAF50, #66BB6A) !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        font-weight: bold !important;
        cursor: pointer !important;
        box-shadow: none !important;
        transition: all 0.3s ease !important;
    }
    
    .btn-upload-order:hover {
        background: linear-gradient(135deg, #45a049, #4CAF50) !important;
        box-shadow: none !important;
        transform: translateY(-1px) !important;
    }
    
    /* 파일 업로드 모달 타이트 스타일 조정 */
    .upload-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 700px !important;  /* 기존보다 축소 */
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);  /* 녹색으로 변경 */
        color: white;
        padding: 12px 16px !important;  /* 패딩 축소 */
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-title {
        margin: 0;
        font-size: 1.1rem !important;  /* 폰트 크기 축소 */
        font-weight: 600;
    }
    
    .modal-body {
        padding: 16px !important;  /* 패딩 축소 (기존 20px) */
        max-height: 60vh;
        overflow-y: auto;
    }
    
    .upload-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px !important;  /* 갭 축소 */
        margin-bottom: 16px !important;  /* 마진 축소 */
    }
    
    .upload-left, .upload-right {
        padding: 12px !important;  /* 패딩 축소 */
    }
    
    .upload-area {
        margin-bottom: 12px !important;  /* 마진 축소 */
    }
    
    .upload-dropzone {
        border: 2px dashed #4caf50;  /* 녹색 테두리 */
        border-radius: 8px;
        padding: 20px !important;  /* 패딩 축소 */
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .upload-dropzone:hover {
        background: #e8f5e9;  /* 녹색 호버 */
        border-color: #2e7d32;
    }
    
    .memo-textarea {
        width: 100%;
        height: 80px !important;  /* 높이 축소 */
        padding: 8px !important;  /* 패딩 축소 */
        border: 1px solid #ddd;
        border-radius: 6px;
        resize: none;
        font-size: 0.85rem !important;  /* 폰트 크기 축소 */
    }
    
    .upload-notice {
        margin-top: 12px !important;  /* 마진 축소 */
    }
    
    .notice-item {
        font-size: 0.8rem !important;  /* 폰트 크기 축소 */
        margin-bottom: 6px !important;  /* 마진 축소 */
        color: #666;
        line-height: 1.3;
    }
    
    .modal-footer {
        padding: 12px 16px !important;  /* 패딩 축소 */
        border-top: 1px solid #eee;
        background: #f8f9fa;
        display: flex;
        justify-content: center;
    }
    
    /* 장바구니 버튼 크기 50% 축소 */
    .modal-btn.btn-cart {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%) !important;  /* 녹색으로 변경 */
        color: white !important;
        border: none !important;
        padding: 8px 16px !important;  /* 패딩 50% 축소 (기존 16px 32px) */
        font-size: 0.85rem !important;  /* 폰트 크기 축소 */
        border-radius: 6px !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        font-weight: 600 !important;
        min-width: 120px !important;  /* 최소 너비 설정 */
    }
    
    .modal-btn.btn-cart:hover {
        background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3) !important;
    }
    </style>
    
        <div class="page-title">
            <h1>🏷️ 스티커 견적안내</h1>
            <p><!-- 프리미엄 스티커 제작 - 포트폴리오 갤러리 & 실시간 계산  --></p>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="main-content">
            <!-- 좌측: 통합 갤러리 섹션 -->
            <section class="sticker-gallery" aria-label="스티커 샘플 갤러리">
                <?php
                // 원클릭 갤러리 포함 (공통 헬퍼 사용)
                if (function_exists("include_product_gallery")) { include_product_gallery('sticker'); }
                ?>
            </section>

            <!-- 우측: view_modern.php 통합 계산기 시스템 -->
            <div class="calculator-section">
                <form id="stickerForm" method="post">
                    <input type="hidden" name="no" value="">
                    <input type="hidden" name="action" value="calculate">
                    
                    <!-- 한 줄 레이아웃 폼 -->
                    <div class="inline-form-container">
                        <!-- 재질 선택 -->
                        <div class="inline-form-row">
                            <span class="inline-label">재질</span>
                            <select name="jong" id="jong" class="inline-select" onchange="calculatePrice()">
                                <option value="jil 아트유광코팅" selected>아트지유광</option>
                                <option value="jil 아트무광코팅">아트지무광</option>
                                <option value="jil 아트비코팅">아트지비코팅</option>
                                <option value="jka 강접아트유광코팅">강접아트유광</option>
                                <option value="cka 초강접아트코팅">초강접아트유광</option>
                                <option value="cka 초강접아트비코팅">초강접아트비코팅</option>
                                <option value="jsp 유포지">유포지</option>
                                <option value="jsp 은데드롱">은데드롱</option>
                                <option value="jsp 투명스티커">투명스티커</option>
                                <option value="jil 모조비코팅">모조지비코팅</option>
                                <option value="jsp 크라프트지">크라프트스티커</option>
                                <option value="jsp 금지스티커">금지스티커-전화문의</option>
                                <option value="jsp 금박스티커">금박스티커-전화문의</option>
                                <option value="jsp 롤형스티커">롤스티커-전화문의</option>
                            </select>
                            <span class="inline-note">금지/금박/롤 전화문의</span>
                        </div>

                        <!-- 가로 -->
                        <div class="inline-form-row">
                            <span class="inline-label">가로</span>
                            <input type="number" name="garo" id="garo" class="inline-input" placeholder="숫자입력" max="560" value="100"
                                   onblur="validateSize(this, '가로')" onchange="calculatePrice()">
                            <span class="inline-note">※주문은 5mm단위 이하는 도무송 적용</span>
                        </div>

                        <!-- 세로 -->
                        <div class="inline-form-row">
                            <span class="inline-label">세로</span>
                            <input type="number" name="sero" id="sero" class="inline-input" placeholder="숫자입력" max="560" value="100"
                                   onblur="validateSize(this, '세로')" onchange="calculatePrice()">
                            <span class="inline-note">※가로, 세로가 50X60mm 이하는 도무송 적용</span>
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
                                <option value="60000">60000매</option>
                                <option value="70000">70000매</option>
                                <option value="80000">80000매</option>
                                <option value="90000">90000매</option>
                                <option value="100000">100000매</option>
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
                            <select name="domusong" id="domusong" class="inline-select" onchange="calculatePrice()">
                                <option value="00000 사각" selected>기본사각</option>
                                <option value="08000 사각도무송">사각도무송</option>
                                <option value="08000 귀돌">귀돌이(라운드)</option>
                                <option value="08000 원형">원형</option>
                                <option value="08000 타원">타원형</option>
                                <option value="19000 복잡">모양도무송</option>
                            </select>
                            <span class="inline-note">도무송 시 좌우상하밀림 현상 있습니다 (오차 1mm 이상)</span>
                        </div>
                    </div>
                    
                    <!-- 명함 방식의 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 명함 방식의 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton" style="display: none;">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            파일 업로드 및 주문하기
                        </button>
                    </div>

                    <!-- 숨겨진 필드들 -->
                    <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
                    <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
                    <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
                    <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
                    <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
                    <input type="hidden" name="page" value="Sticker">
                </form>
            </div>
        </div>
    </div>

    <!-- 파일 업로드 모달 (드래그 앤 드롭 및 고급 애니메이션) -->
    <div id="uploadModal" class="upload-modal" style="display: none;">
        <div class="modal-overlay" onclick="closeUploadModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">📎 스티커 파일첨부</h3>
                <button type="button" class="modal-close" onclick="closeUploadModal()">✕</button>
            </div>
            
            <div class="modal-body">
                <div class="upload-container">
                    <div class="upload-left">
                        <label class="upload-label" for="modalFileInput">파일첨부</label>
                        <div class="upload-buttons">
                            <button type="button" class="btn-upload-method active" onclick="selectUploadMethod('upload')">
                                파일업로드
                            </button>
                            <button type="button" class="btn-upload-method" onclick="selectUploadMethod('manual')" disabled>
                                10분만에 작품완료!
                            </button>
                        </div>
                        <div class="upload-area" id="modalUploadArea">
                            <div class="upload-dropzone" id="modalUploadDropzone">
                                <span class="upload-icon">📁</span>
                                <span class="upload-text">스티커 파일을 여기에 드래그하거나 클릭하세요</span>
                                <input type="file" id="modalFileInput" accept=".jpg,.jpeg,.png,.pdf,.ai,.eps,.psd" multiple hidden>
                            </div>
                            <div class="upload-info">
                                스티커 제작용 파일을 업로드해주세요. 특수문자(#,&,'&',*,%, 등)는 사용할 수 없습니다.
                            </div>
                        </div>
                    </div>
                    
                    <div class="upload-right">
                        <label class="upload-label">작업메모</label>
                        <textarea id="modalWorkMemo" class="memo-textarea" placeholder="스티커 제작 관련 요청사항을 입력해주세요.&#10;&#10;예시:&#10;- 색상을 더 선명하게 해주세요&#10;- 로고를 중앙에 배치&#10;- 배경을 투명하게 처리&#10;- 테두리 추가 요청"></textarea>
                        
                        <div class="upload-notice">
                            <div class="notice-item">📦 택배는 기본이 착불 원칙입니다</div>
                            <div class="notice-item">📋 당일 제작 시 전날 주문 완료 필요</div>
                        </div>
                    </div>
                </div>
                
                <div class="uploaded-files" id="modalUploadedFiles" style="display: none;">
                    <h5>📂 업로드된 파일</h5>
                    <div class="file-list" id="modalFileList"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-cart" onclick="addToBasketFromModal()">
                    🛒 장바구니에 저장
                </button>
            </div>
        </div>
    </div>

    <?php include "../../includes/login_modal.php"; ?>
    
    <!-- 통일된 갤러리 팝업은 JavaScript로 동적 생성됩니다 -->


    <!-- 스티커 전용 추가 스타일 (카다록 색상 적용) -->
    <style>
    /* 통일된 Primary 버튼 스타일 (전단지와 동일) */
    .btn-primary {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.25);
        width: 100%;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.35);
    }

    .btn-primary:active {
        transform: translateY(0);
    }
    
    /* page-title 컴팩트 버전 (1/2 높이) */
    .page-title {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        padding: 12px 0 !important;
        margin-bottom: 15px !important;
        border-radius: 10px !important;
    }
    
    .page-title h1 {
        color: white !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
        font-size: 1.6rem !important;
        font-weight: 700 !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }
    
    .page-title p {
        color: white !important;
        opacity: 0.9 !important;
        margin: 4px 0 0 0 !important;
        font-size: 0.85rem !important;
        line-height: 1.3 !important;
    }
    
    /* calculator-section 갤러리와 동일한 배경 및 그림자 효과 */
    .calculator-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 15px !important;
        padding: 25px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        position: relative !important;
        margin-top: 0 !important;
        align-self: start !important;
        height: 450px !important;
        min-height: 450px !important;
        overflow: auto !important;
    }
    
    /* calculator-header 통일된 헤더 디자인 (다른 페이지와 동일) */
    .calculator-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        color: white !important;
        padding: 10px 20px !important;
        margin: -25px -25px 0px -25px !important;
        border-radius: 15px 15px 0 0 !important;
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        text-align: center !important;
        box-shadow: 0 2px 10px rgba(255, 193, 7, 0.3) !important;
        line-height: 1.2 !important;
    }
    
    .calculator-header h3 {
        font-size: 1.1rem !important;        /* gallery-title과 동일 */
        font-weight: 600 !important;
        margin: 0 !important;
        color: white !important;
        line-height: 1.2 !important;
    }
    
    .calculator-subtitle {
        font-size: 0.85rem !important;
        margin: 0 !important;
        opacity: 0.9 !important;
    }
    
    /* 인라인 스타일 분리 */
    .compact-cell {
        padding-top: 0px !important;
        padding-bottom: 0px !important;
    }
    
    .size-inputs {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .size-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }
    
    .size-input-field {
        width: 120px;
        padding: 12px;
        font-size: 1.1rem;
        border: 2px solid #ddd;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
    }
    
    .size-multiply {
        font-size: 1.5rem;
        font-weight: bold;
        color: #666;
        margin: 0 0.5rem;
    }
    
    .help-text {
        color: #6c757d;
        font-weight: 500;
    }
    
    /* 전체 요소들을 더 타이트하게 */
    .order-form-table {
        margin: 0.5rem 0 !important;
    }
    
    /* price-display 컴팩트 버전 (2/3 높이) */
    .price-display {
        margin-bottom: 5px !important;
        padding: 8px 5px !important;
        border-radius: 8px !important;
    }
    
    .price-display .price-label {
        font-size: 0.9rem !important;
        color: #495057 !important;
        font-weight: 600 !important;
        margin-bottom: 4px !important;
        line-height: 1.2 !important;
    }
    
    .price-display .price-amount {
        font-size: 0.98rem !important;
        color: #28a745 !important;
        font-weight: 700 !important;
        margin-bottom: 6px !important;
        line-height: 1.1 !important;
        text-shadow: 0 2px 4px rgba(40, 167, 69, 0.3) !important;
    }
    
    .price-display .price-details {
        font-size: 0.8rem !important;
        color: #6c757d !important;
        line-height: 1.3 !important;
        margin: 0 !important;
    }
    
    .price-display.calculated {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        color: #495057 !important;
        border: 2px solid #28a745 !important;
        transform: scale(1.01) !important;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25) !important;
    }
    
    .upload-order-button {
        margin-top: 10px !important;
    }
    
    /* 스티커 전용 테이블 폼 스타일 개선 */
    .order-form-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
    }
    
    .order-form-table td {
        padding: 16px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .label-cell {
        width: 30%;
        background: #f8f9fa;
        vertical-align: top;
        font-weight: 600;
    }
    
    .input-cell {
        width: 70%;
        background: white;
    }
    
    .icon-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #495057;
        font-size: 0.95rem;
    }
    
    .icon-label .icon {
        font-size: 1.2rem;
    }
    
    .form-control-modern {
        width: 100%;
        padding: 6px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 0.9rem;
        background: white;
        transition: all 0.3s ease;
        font-family: inherit;
    }
    
    .form-control-modern:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        transform: translateY(-1px);
    }
    
    .form-control-modern:hover {
        border-color: #ced4da;
    }
    
    .help-text {
        display: block;
        margin-top: 0.5rem;
        color: #6c757d;
        font-size: 0.85rem;
        line-height: 1.3;
    }
    
    /* 메인 컨테이너 그리드 정렬 */
    .main-content {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 20px !important;
        align-items: start !important; /* 그리드 아이템들을 상단 정렬 */
    }

    /* 모바일 반응형 개선 */
    @media (max-width: 1024px) {
        .main-content {
            grid-template-columns: 1fr;
            gap: 25px;
        }
    }
    
    @media (max-width: 768px) {
        .order-form-table td {
            padding: 12px;
        }
        
        .label-cell, .input-cell {
            display: block;
            width: 100%;
        }
        
        .label-cell {
            padding-bottom: 8px;
            background: white;
            border-bottom: none;
        }
        
        .input-cell {
            padding-top: 0;
        }
        
        .size-inputs {
            flex-direction: column !important;
            gap: 1rem !important;
            text-align: center !important;
        }
        
        .form-control-inline {
            width: 150px !important;
            padding: 15px !important;
            font-size: 1.2rem !important;
        }
    }
    
    /* 갤러리 섹션 - 강화된 그림자 효과 */
    .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9);
        margin-top: 0 !important;
        align-self: start !important;
        height: 450px !important;
        min-height: 450px !important;
        overflow: auto !important;
    }
    
    .gallery-title {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 15px 20px;
        margin: -25px -25px 20px -25px;
        border-radius: 15px 15px 0 0;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 10px rgba(255, 193, 7, 0.3);
    }
    
    /* 라이트박스 뷰어 스타일 */
    .lightbox-viewer {
        width: 100%;
        height: 300px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        cursor: zoom-in;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
        position: relative;
        overflow: hidden;
    }
    
    .lightbox-viewer:hover {
        border-color: #667eea;
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    
    /* 썸네일 스트립 스타일 */
    .thumbnail-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 10px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .thumbnail-strip img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        opacity: 0.7;
    }
    
    .thumbnail-strip img:hover {
        opacity: 1;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }
    
    .thumbnail-strip img.active {
        opacity: 1;
        border-color: #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    /* 갤러리 로딩 상태 */
    #stickerGallery .loading {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1rem;
        background: white;
        border-radius: 12px;
        animation: pulse 2s infinite;
    }
    
    /* 갤러리 에러 상태 */
    #stickerGallery .error {
        text-align: center;
        padding: 40px 20px;
        color: #dc3545;
        background: #fff5f5;
        border: 1px solid #ffdddd;
        border-radius: 12px;
        font-size: 0.95rem;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    
    /* 메인 갤러리 줌박스 향상 */
    .zoom-box {
        transition: all 0.3s ease;
        border: 2px solid #e9ecef !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        position: relative;
        overflow: hidden;
    }
    
    .zoom-box:hover {
        border-color: #667eea !important;
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.15) !important;
        transform: translateY(-2px);
    }
    
    .zoom-box::before {
        content: '🔍 클릭하여 확대';
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        z-index: 5;
    }
    
    .zoom-box:hover::before {
        opacity: 1;
    }
    
    /* 썸네일 그리드 향상 */
    .thumbnail-grid img {
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .thumbnail-grid img:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        border-color: #667eea !important;
    }
    
    .thumbnail-grid img.active {
        border-color: #667eea !important;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        transform: translateY(-2px);
    }
    
    /* 샘플 더보기 버튼 스타일 */
    .btn-more-samples {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 14px 28px;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        min-width: 160px;
        position: relative;
        overflow: hidden;
    }
    
    .btn-more-samples::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .btn-more-samples:hover::before {
        left: 100%;
    }
    
    .btn-more-samples:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #5a6fd8 0%, #6b3fa0 100%);
    }
    
    .btn-more-samples:active {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    /* 로딩 및 에러 상태 향상 */
    .gallery-loading, .gallery-error {
        padding: 40px 20px;
        text-align: center;
        border-radius: 10px;
        margin: 20px 0;
    }
    
    .gallery-loading {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1565c0;
        animation: pulse 2s infinite;
    }
    
    .gallery-error {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: #c62828;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    /* =================================================================== */
    /* 더보기 버튼 스타일 */
    /* =================================================================== */
    .gallery-more-button {
        text-align: center;
        margin-top: 15px;
    }
    
    .btn-more-gallery {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(23, 162, 184, 0.2);
    }
    
    .btn-more-gallery:hover {
        background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        transform: translateY(-2px);
    }

    /* =================================================================== */
    /* 갤러리 모달 스타일 */
    /* =================================================================== */
    .gallery-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .gallery-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(3px);
    }
    
    .gallery-modal-content {
        position: relative;
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 1000px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideUp 0.3s ease-out;
    }
    
    .gallery-modal-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .gallery-modal-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .gallery-modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }
    
    .gallery-modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .gallery-modal-body {
        padding: 20px;
        max-height: 60vh;
        overflow-y: auto;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    /* 페이지네이션 스타일 */
    .gallery-pagination {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-top: 1px solid #dee2e6;
    }

    .pagination-info {
        text-align: center;
        margin-bottom: 15px;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pagination-btn {
        background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 80px;
    }

    .pagination-btn:hover:not(:disabled) {
        background: linear-gradient(135deg, #e0a806 0%, #e67e00 100%);
        transform: translateY(-2px);
    }

    .pagination-btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
    }

    .pagination-numbers {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .pagination-number {
        background: white;
        color: #ffc107;
        border: 2px solid #ffc107;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 40px;
    }

    .pagination-number:hover {
        background: #ffc107;
        color: white;
        transform: translateY(-2px);
    }

    .pagination-number.active {
        background: #ffc107;
        color: white;
        font-weight: bold;
    }
    
    .gallery-grid img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .gallery-grid img:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #ffc107;
    }
    
    @keyframes modalSlideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* 반응형 향상 */
    @media (max-width: 768px) {
        .gallery-section {
            padding: 20px;
            margin: 0 -10px;
            border-radius: 10px;
        }
        
        .gallery-title {
            margin: -20px -20px 15px -20px;
            padding: 12px 15px;
            font-size: 1rem;
        }
        
        .zoom-box::before {
            font-size: 0.7rem;
            padding: 6px 10px;
            top: 10px;
            left: 10px;
        }
        
        .btn-more-samples {
            padding: 12px 24px;
            font-size: 0.9rem;
            min-width: 140px;
        }
    }
    
    /* 사각도무송 선택 시 적색 표시 */
    select[name="domusong"] {
        color: #333;
    }
    
    select[name="domusong"]:has(option[value="08000 사각도무송"]:checked) {
        color: #dc3545 !important;
        font-weight: bold;
    }
    
    /* JavaScript로 동적 처리를 위한 클래스 */
    select[name="domusong"].domusong-selected {
        color: #dc3545 !important;
        font-weight: bold;
        font-size: 1.05em;
    }
    
    /* 반짝이는 애니메이션 */
    @keyframes blink3times {
        0%, 16.66%, 33.32%, 49.98%, 66.64%, 83.3% {
            opacity: 1;
        }
        8.33%, 24.99%, 41.65%, 58.31%, 74.97%, 91.63% {
            opacity: 0.3;
        }
        100% {
            opacity: 1;
        }
    }
    
    .domusong-blink {
        animation: blink3times 1.8s ease-in-out;
    }
    </style>

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
        
        // 파일 업로드 관련 전역 변수 (명함 방식)
        let uploadedFiles = [];
        let selectedUploadMethod = 'upload';
        let modalFileUploadInitialized = false;
        
        // Debounce 함수 - 연속 이벤트 제어
        let calculationTimeout = null;
        let isCalculating = false;
        
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
            const uploadButton = document.getElementById('uploadOrderButton');
            
            // DOM 요소 존재 확인
            if (!priceDisplay || !priceAmount || !priceDetails || !uploadButton) {
                console.error('Required DOM elements not found');
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
                priceDetails.innerHTML = `
                    <div style="font-size: 0.8rem; margin-top: 6px; line-height: 1.4; color: #6c757d; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; justify-content: center;">
                        <span>인쇄비: ${new Intl.NumberFormat('ko-KR').format(printPrice)}원</span>
                        ${editFee > 0 ? `<span>편집비: ${new Intl.NumberFormat('ko-KR').format(editFee)}원</span>` : ''}
                        <span>공급가격: ${priceData.price}원</span>
                        <span>부가세 포함: <span style="color: #dc3545; font-size: 1rem;">${priceData.price_vat}원</span></span>
                    </div>
                `;
                
                // 가격 표시 영역을 calculated 상태로 변경
                priceDisplay.classList.add('calculated');
                
                // 업로드/주문 버튼 표시
                uploadButton.style.display = 'block';
                
                // 세션에 가격 정보 저장 (장바구니/주문용)
                window.currentPriceData = priceData;
                console.log('Price display updated successfully - Supply price focus');
                
            } else {
                console.log('Resetting price display - no valid data');
                priceAmount.textContent = '견적 계산 필요';
                priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
                priceDisplay.classList.remove('calculated');
                uploadButton.style.display = 'none';
                window.currentPriceData = null;
            }
        }

        // 가격 표시 초기화 함수 (명함 방식)
        function resetPriceDisplay() {
            const priceAmount = document.getElementById('priceAmount');
            const priceDetails = document.getElementById('priceDetails');
            const priceDisplay = document.getElementById('priceDisplay');
            const uploadButton = document.getElementById('uploadOrderButton');
            
            if (priceAmount) priceAmount.textContent = '견적 계산 필요';
            if (priceDetails) priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
            if (priceDisplay) priceDisplay.classList.remove('calculated');
            if (uploadButton) uploadButton.style.display = 'none';
            
            window.currentPriceData = null;
        }

        // 자동 가격 계산 함수 (명함 방식)
        function autoCalculatePrice() {
            console.log('Auto calculation triggered'); // 디버깅
            
            if (!areAllOptionsSelected()) {
                console.log('Not all options selected - checking details:'); // 디버깅
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
                return;
            }
            
            console.log('All options selected, calculating...'); // 디버깅
            const formData = new FormData(document.getElementById('stickerForm'));
            
            // 디버깅: 전송되는 데이터 확인
            console.log('Sending form data:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            console.log('Fetching: ./calculate_price.php');
            fetch('./calculate_price.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response.status, response.statusText); // 디버깅
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Price data received:', data); // 디버깅
                if (data.success) {
                    console.log('Calculation successful, updating display');
                    updatePriceDisplay(data);
                } else {
                    console.error('Calculation failed:', data.message);
                    resetPriceDisplay();
                }
            })
            .catch(error => {
                console.error('Price calculation error:', error);
                resetPriceDisplay();
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
        }

        // 옵션 변경 시 자동 계산 이벤트 리스너 등록
        function initAutoCalculation() {
            const form = document.getElementById('stickerForm');
            
            // 가로/세로 입력 요소에 크기 검증 이벤트 추가
            const garoInput = form.querySelector('input[name="garo"]');
            const seroInput = form.querySelector('input[name="sero"]');
            
            if (garoInput) {
                garoInput.addEventListener('input', function() {
                    checkSizeAndAutoSelect();
                    debouncedCalculatePrice();
                });
                garoInput.addEventListener('change', function() {
                    checkSizeAndAutoSelect();
                    debouncedCalculatePrice();
                });
            }
            
            if (seroInput) {
                seroInput.addEventListener('input', function() {
                    checkSizeAndAutoSelect();
                    debouncedCalculatePrice();
                });
                seroInput.addEventListener('change', function() {
                    checkSizeAndAutoSelect();
                    debouncedCalculatePrice();
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
            
            // 초기 계산을 지연 실행 (DOM 완전 로드 후)
            setTimeout(() => {
                console.log('Delayed initial calculation');
                autoCalculatePrice();
            }, 100);
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
            formData.set('upload_method', selectedUploadMethod || 'upload');
            
            // 업로드된 파일들 추가 (명함 방식)
            if (typeof uploadedFiles !== 'undefined' && uploadedFiles.length > 0) {
                uploadedFiles.forEach((fileObj, index) => {
                    formData.append(`uploaded_files[${index}]`, fileObj.file);
                });
                
                // 파일 정보 JSON
                const fileInfoArray = uploadedFiles.map(fileObj => ({
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
                        
                        // 성공 메시지 표시
                        showUserMessage('장바구니에 저장되었습니다! 🛒', 'success');
                        
                        // 장바구니 페이지로 이동
                        setTimeout(() => {
                            window.location.href = '/MlangPrintAuto/shop/cart.php';
                        }, 1000);
                        
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
            window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
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
        
        // 파일업로드 모달 관련 함수들 (명함 완성 시스템)
        function openUploadModal() {
            if (!window.currentPriceData) {
                showUserMessage('먼저 가격을 계산해주세요.', 'warning');
                return;
            }
            
            const modal = document.getElementById('uploadModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                // 파일 업로드 한 번만 초기화
                if (!modalFileUploadInitialized) {
                    initializeModalFileUpload();
                    modalFileUploadInitialized = true;
                }
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
        });
        
        
        // 갤러리 관련 변수들
        let currentX = 50;
        let currentY = 50;
        let currentSize = 100;
        let targetX = 50;
        let targetY = 50;
        let targetSize = 100;
        let originalBackgroundSize = 'contain';
        let currentImageType = 'large'; // 'small' or 'large'
        let animationId = null;
        
        function initializeGallery() {
            const galleryContainer = document.getElementById('stickerGallery');
            if (!galleryContainer) return;
            
            console.log('스티커 갤러리 초기화 - API 방식 강제 적용');
            
            // 성공했던 API 방식 직접 호출 (GalleryLightbox 우회)
            loadStickerImages();
        }
        
        // 성공했던 API 방식으로 스티커 갤러리 로드 (전단지와 동일)
        async function loadStickerImages() {
            const galleryContainer = document.getElementById('stickerGallery');
            if (!galleryContainer) return;
            
            console.log('🏷️ 스티커 갤러리 로딩 시작 (API 방식)');
            galleryContainer.innerHTML = '<div class="loading">🏷️ 스티커 갤러리 로딩 중...</div>';
            
            try {
                // 실제 주문 데이터에서 스티커 이미지 가져오기 (랜덤 순서)
                const response = await fetch('/api/get_real_orders_portfolio.php?category=sticker&per_page=4');
                const data = await response.json();
                
                console.log('스티커 API 응답:', data);
                
                if (data.success && data.data && data.data.length > 0) {
                    // 성공한 포스터 방식 갤러리 렌더링 적용
                    renderStickerGallery(data.data, galleryContainer);
                    
                    // 더보기 버튼 항상 표시
                    const moreButton = document.querySelector('.gallery-more-button');
                    if (moreButton) {
                        moreButton.style.display = 'block';
                    }
                    
                    console.log('스티커 갤러리 로딩 성공:', data.data.length + '개');
                } else {
                    console.log('스티커 이미지 데이터가 없거나 실패:', data);
                    galleryContainer.innerHTML = '<div class="error">현재 표시할 스티커 샘플이 없습니다.<br>곧 새로운 작품들이 업데이트됩니다.</div>';
                }
            } catch (error) {
                console.error('스티커 갤러리 API 오류:', error);
                galleryContainer.innerHTML = '<div class="error">갤러리 로딩 중 오류가 발생했습니다.<br>잠시 후 다시 시도해주세요.</div>';
            }
        }

        // 통일된 팝업 열기 함수 (전단지와 동일한 시스템)
        function openProofPopup(category) {
            const popup = window.open('/popup/proof_gallery.php?cate=' + encodeURIComponent(category), 
                'proof_popup', 
                'width=1200,height=800,scrollbars=yes,resizable=yes,top=50,left=100');
            
            if (popup) {
                popup.focus();
            } else {
                alert('팝업이 차단되었습니다. 팝업 차단을 해제해주세요.');
            }
        }
        
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
                this.style.backgroundSize = '150%'; // 확대
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

    <?php
    // 갤러리 에셋 자동 포함
    if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {
        if (function_exists("include_gallery_assets")) { include_gallery_assets(); }
    }
    ?>

    <?php
    // 갤러리 모달과 JavaScript는 if (function_exists("include_product_gallery")) { include_product_gallery()에서 자동 포함됨
    ?>
    
    <?php include "../../includes/footer.php"; ?>

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>