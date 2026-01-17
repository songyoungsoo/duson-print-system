<?php
/**
 * 스티커 견적안내 시스템 - 우측 정렬 연습 페이지
 * Features: 우측 정렬 필드명, 15px 패딩, 그리드 레이아웃
 * Created: 2025년 1월 (연습용 페이지)
 */

// 보안 상수 정의 후 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 통합 갤러리 시스템 초기화
if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; }
if (function_exists("init_gallery_system")) { init_gallery_system("sticker"); }

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("스티커 견적안내 - 우측정렬 연습");

// 스티커 기본값 설정
$default_values = [
    'jong' => 'jil 아트유광', // 기본값: 아트지유광
    'garo' => '100', // 기본 가로 사이즈
    'sero' => '100', // 기본 세로 사이즈
    'mesu' => '1000', // 기본 수량
    'uhyung' => '0', // 기본값: 인쇄만
    'domusong' => '00000 사각' // 기본 모양
];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 공통 헤더 포함 -->
    <?php include "../../includes/header.php"; ?>
    
    <!-- 기존 CSS -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <link rel="stylesheet" href="../../assets/css/gallery.css">
    <link rel="stylesheet" href="../../css/compact-form.css">
    
    <!-- 우측 정렬 전용 CSS -->
    <style>
        /* 🎯 우측 정렬 그리드 폼 시스템 */
        .form-grid-right-align {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .form-field-right {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .form-label-right {
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #495057 !important;
            text-align: right !important;
            padding-right: 15px !important;
            margin-bottom: 4px !important;
            font-family: 'Noto Sans KR', sans-serif !important;
            background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
            padding: 8px 15px 8px 8px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        
        .form-select-right, .form-input-right {
            height: 36px !important;
            padding: 0 12px !important;
            border: 2px solid #dee2e6 !important;
            border-radius: 4px !important;
            font-size: 13px !important;
            color: #495057 !important;
            background: white !important;
            font-family: 'Noto Sans KR', sans-serif !important;
            transition: all 0.3s ease !important;
        }
        
        .form-select-right:focus, .form-input-right:focus {
            outline: none !important;
            border-color: #007bff !important;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1) !important;
        }
        
        .form-select-right:hover, .form-input-right:hover {
            border-color: #adb5bd !important;
        }
        
        /* 가로/세로 특별 처리 */
        .size-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .size-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .size-input {
            width: 100%;
            height: 36px;
            padding: 0 12px;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            font-size: 13px;
            color: #495057;
            background: white;
            font-family: 'Noto Sans KR', sans-serif;
        }
        
        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .form-grid-right-align {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 16px;
            }
            
            .size-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }
        
        /* 기존 가격 표시 및 버튼 스타일 유지 */
        .price-display {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f2 100%);
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        
        .price-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .price-amount {
            font-size: 28px;
            font-weight: bold;
            color: #4CAF50;
            margin: 8px 0;
        }
        
        .price-details {
            font-size: 12px;
            color: #777;
            margin-top: 8px;
        }
        
        .upload-order-button {
            text-align: center;
            margin: 20px 0;
        }
        
        /* .btn-upload-order → common-styles.css SSOT 사용 */
    </style>
</head>

<body>
    <!-- 공통 내비게이션 -->
    <?php include "../../includes/nav.php"; ?>

    <div class="container">
        <div class="row">
            <!-- 갤러리 섹션 -->
            <div class="col-md-6">
                <div class="gallery-section">
                    <h3 class="section-title">📸 스티커 포트폴리오</h3>
                    
                    <!-- 표준 갤러리 시스템 -->
                    <div class="product-gallery-standard">
                        <div class="lightbox-viewer">
                            <img id="mainImage" src="../../mlangprintauto/sticker_new/images/sticker_01.jpg" alt="스티커 미리보기">
                        </div>
                        
                        <div class="thumbnail-strip">
                            <img src="../../mlangprintauto/sticker_new/images/sticker_01.jpg" onclick="changeMainImage(this.src)" alt="스티커 샘플 1">
                            <img src="../../mlangprintauto/sticker_new/images/sticker_02.jpg" onclick="changeMainImage(this.src)" alt="스티커 샘플 2">
                            <img src="../../mlangprintauto/sticker_new/images/sticker_03.jpg" onclick="changeMainImage(this.src)" alt="스티커 샘플 3">
                            <img src="../../mlangprintauto/sticker_new/images/sticker_04.jpg" onclick="changeMainImage(this.src)" alt="스티커 샘플 4">
                            <img src="../../mlangprintauto/sticker_new/images/sticker_05.jpg" onclick="changeMainImage(this.src)" alt="스티커 샘플 5">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 계산기 섹션 -->
            <div class="col-md-6">
                <div class="calculator-section">
                    <h3 class="section-title">💰 스티커 견적 계산기 (우측 정렬)</h3>
                    
                    <form id="stickerForm" method="post">
                        <input type="hidden" name="no" value="">
                        <input type="hidden" name="action" value="calculate">
                        
                        <!-- 우측 정렬 그리드 폼 -->
                        <div class="form-grid-right-align">
                            <!-- 재질 선택 -->
                            <div class="form-field-right">
                                <label class="form-label-right" for="jong">재질</label>
                                <select name="jong" id="jong" class="form-select-right" onchange="calculatePrice()">
                                    <option value="jil 아트유광코팅">아트지유광코팅(90g)</option>
                                    <option value="jil 아트무광코팅">아트지무광코팅(90g)</option>
                                    <option value="jil 아트비코팅">아트지비코팅(90g)</option>
                                    <option value="jka 강접아트유광코팅">강접아트유광코팅(90g)</option>
                                    <option value="cka 초강접아트코팅">초강접아트유광코팅(90g)</option>
                                    <option value="cka 초강접아트비코팅">초강접아트비코팅(90g)</option>
                                    <option value="jsp 유포지">유포지(80g)</option>
                                    <option value="jsp 은데드롱">은데드롱(25g)</option>
                                    <option value="jsp 투명스티커">투명스티커(25g)</option>
                                    <option value="jil 모조비코팅">모조지비코팅(80g)</option>
                                    <option value="jsp 크라프트지">크라프트스티커(57g)</option>
                                    <option value="jsp 금지스티커">금지스티커-전화문의</option>
                                    <option value="jsp 금박스티커">금박스티커-전화문의</option>
                                    <option value="jsp 롤형스티커">롤스티커-전화문의</option>
                                </select>
                            </div>
                            
                            <!-- 가로/세로 사이즈 -->
                            <div class="size-row">
                                <div class="size-field">
                                    <label class="form-label-right" for="garo">가로</label>
                                    <input type="number" name="garo" id="garo" class="size-input" 
                                           placeholder="숫자입력" max="560" value="100"
                                           onblur="validateSize(this, '가로')" onchange="calculatePrice()">
                                </div>
                                <div class="size-field">
                                    <label class="form-label-right" for="sero">세로</label>
                                    <input type="number" name="sero" id="sero" class="size-input" 
                                           placeholder="숫자입력" max="560" value="100"
                                           onblur="validateSize(this, '세로')" onchange="calculatePrice()">
                                </div>
                            </div>
                            
                            <!-- 수량 선택 -->
                            <div class="form-field-right">
                                <label class="form-label-right" for="mesu">매수</label>
                                <select name="mesu" id="mesu" class="form-select-right" onchange="calculatePrice()">
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
                            </div>
                            
                            <!-- 편집비 -->
                            <div class="form-field-right">
                                <label class="form-label-right" for="uhyung">편집</label>
                                <select name="uhyung" id="uhyung" class="form-select-right" onchange="calculatePrice()">
                                    <option value="0" selected>인쇄만 (파일 준비완료)</option>
                                    <option value="10000">기본 편집 (+10,000원)</option>
                                    <option value="30000">고급 편집 (+30,000원)</option>
                                </select>
                            </div>
                            
                            <!-- 모양 선택 -->
                            <div class="form-field-right">
                                <label class="form-label-right" for="domusong">모양</label>
                                <select name="domusong" id="domusong" class="form-select-right" onchange="calculatePrice()">
                                    <option value="00000 사각" selected>기본사각형</option>
                                    <option value="08000 사각도무송" style="color: #dc3545; font-weight: bold;">사각도무송(50~60mm미만)</option>
                                    <option value="08000 귀돌">귀돌이(라운드)</option>
                                    <option value="08000 원형">원형</option>
                                    <option value="08000 타원">타원형</option>
                                    <option value="19000 복잡">모양도무송</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- 가격 표시 -->
                        <div class="price-display" id="priceDisplay">
                            <div class="price-label">견적 금액</div>
                            <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                            <div class="price-details" id="priceDetails">
                                모든 옵션을 선택하면 자동으로 계산됩니다
                            </div>
                        </div>

                        <!-- 파일 업로드 및 주문 버튼 -->
                        <div class="upload-order-button" id="uploadOrderButton" style="display: none;">
                            <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                                📎 파일 업로드 및 주문하기
                            </button>
                        </div>

                        <!-- 숨겨진 필드들 (계산 구조 보존) -->
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
    </div>

    <!-- 공통 푸터 -->
    <?php include "../../includes/footer.php"; ?>
    
    <!-- 기존 JavaScript 함수들 (계산 구조 보존) -->
    <script>
        // 갤러리 이미지 변경 함수
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }
        
        // 스티커 가격 계산 함수 (기존 로직 그대로 유지)
        function calculatePrice() {
            const jong = document.getElementById('jong').value;
            const garo = parseInt(document.getElementById('garo').value) || 0;
            const sero = parseInt(document.getElementById('sero').value) || 0;
            const mesu = parseInt(document.getElementById('mesu').value) || 0;
            const uhyung = parseInt(document.getElementById('uhyung').value) || 0;
            const domusong = document.getElementById('domusong').value;
            
            if (garo <= 0 || sero <= 0) {
                document.getElementById('priceAmount').textContent = '크기를 입력해주세요';
                document.getElementById('priceDetails').textContent = '가로와 세로 크기를 모두 입력해주세요';
                document.getElementById('uploadOrderButton').style.display = 'none';
                return;
            }
            
            // 기본 스티커 가격 계산 (예시)
            const area = garo * sero; // 면적
            const basePrice = Math.ceil(area * 0.05) * mesu; // 기본 단가
            
            // 재질별 추가 비용 (예시)
            let materialCost = 0;
            if (jong.includes('강접')) materialCost = basePrice * 0.2;
            else if (jong.includes('초강접')) materialCost = basePrice * 0.3;
            else if (jong.includes('투명')) materialCost = basePrice * 0.25;
            
            // 모양별 추가 비용
            let shapeCost = 0;
            const shapePrice = parseInt(domusong.split(' ')[0]);
            if (shapePrice > 0) {
                shapeCost = shapePrice;
            }
            
            const subtotal = basePrice + materialCost + shapeCost + uhyung;
            const vat = Math.round(subtotal * 0.1);
            const total = subtotal + vat;
            
            // 가격 표시
            document.getElementById('priceAmount').textContent = total.toLocaleString() + '원';
            document.getElementById('priceDetails').innerHTML = 
                `인쇄비: ${(basePrice + materialCost).toLocaleString()}원 | ` +
                `편집비: ${uhyung.toLocaleString()}원 | ` +
                `모양가공: ${shapeCost.toLocaleString()}원 | ` +
                `부가세: ${vat.toLocaleString()}원`;
            
            // 주문 버튼 표시
            document.getElementById('uploadOrderButton').style.display = 'block';
        }
        
        // 크기 유효성 검사 함수
        function validateSize(input, type) {
            const value = parseInt(input.value);
            if (value <= 0 || value > 560) {
                alert(type + ' 크기는 1~560mm 사이여야 합니다.');
                input.focus();
                return false;
            }
            calculatePrice();
            return true;
        }
        
        // 업로드 모달 함수 (예시)
        function openUploadModal() {
            alert('파일 업로드 기능은 실제 구현에서 모달 창으로 처리됩니다.');
        }
        
        // 페이지 로드 시 초기 계산
        window.onload = function() {
            calculatePrice();
        };
    </script>
</body>
</html>