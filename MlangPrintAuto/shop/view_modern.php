<?php 
/**
 * 스티커 주문 페이지 (공통 인클루드 사용 버전)
 * 경로: MlangPrintAuto/shop/view_modern_new.php
 */

session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 공통 인증 처리
include "../../includes/auth.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 페이지 설정
$page_title = '🏷️ 두손기획인쇄 - 프리미엄 스티커 주문';
$current_page = 'sticker';

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 공통 헤더 포함
include "../../includes/header.php";

// 네비게이션 포함
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';

// 견적 표 CSS 추가
echo '<link rel="stylesheet" href="../../includes/css/quote-table.css">';

// 스티커 통합 디자인 시스템 CSS 오버레이 적용 (성능 최적화된 minified 버전)
echo '<link rel="stylesheet" href="../../css/unified-sticker-overlay.min.css">';

// 갤러리 모달 CSS 추가 + 포스터 방식 스타일
echo '<style>

/* =================================================================== */
/* 포스터 방식 갤러리 시스템 스타일 (성공한 전단지/명함 방식) */
/* =================================================================== */

/* 스티커 갤러리 전용 스타일 */
.sticker-gallery {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    border: 1px solid rgba(255, 255, 255, 0.9);
}

/* 스티커 갤러리 제목 색상 조정 (스티커 브랜드 색상 - 보라) */
.sticker-gallery .card-title {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
}

/* 메인 뷰어 스타일 */
.main-viewer {
    width: 100%;
    height: 300px;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 15px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.main-viewer:hover {
    border-color: #667eea;
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

.zoom-box {
    width: 100%;
    height: 100%;
    transition: all 0.3s ease;
    background-repeat: no-repeat;
    background-size: contain;
    background-position: center;
}

/* 썸네일 스타일 */
.proof-thumbs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin: 15px 0;
}

.proof-thumbs .thumb {
    width: 80px;
    height: 60px;
    border: 2px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
    position: relative;
}

.proof-thumbs .thumb:hover {
    border-color: #667eea;
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.proof-thumbs .thumb.active {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transform: scale(1.05);
}

.proof-thumbs .thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.proof-thumbs .thumb:hover img {
    transform: scale(1.1);
}

/* 더보기 버튼 스타일 */
.btn-more-gallery {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.2);
}

.btn-more-gallery:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6b3fa0 100%);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    transform: translateY(-2px);
}

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
    max-width: 1200px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease-out;
}

.gallery-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.gallery-modal-body .gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.gallery-modal-body .gallery-grid img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.gallery-modal-body .gallery-grid img:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    background: linear-gradient(135deg, #5a6fd8 0%, #6b3fa0 100%);
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
    color: #667eea;
    border: 2px solid #667eea;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 40px;
}

.pagination-number:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}

.pagination-number.active {
    background: #667eea;
    color: white;
    font-weight: bold;
}

@media (max-width: 768px) {
    .gallery-modal-content {
        width: 95%;
        max-height: 85vh;
    }
    
    .gallery-modal-body .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }
    
    .gallery-modal-body .gallery-grid img {
        height: 120px;
    }
    
    .pagination-controls {
        flex-direction: column;
        gap: 15px;
    }
    
    .pagination-btn {
        min-width: 100px;
    }
}
</style>';
?>

<div class="container">
    <!-- 스티커 샘플 갤러리 (통합 갤러리 시스템) -->
    <?php
    include_product_gallery('sticker', ['mainSize' => [500, 400]]);
    ?>

    <!-- 주문 폼 -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📝 스티커 주문 옵션 선택</h2>
            <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
        </div>
        
        <form id="orderForm" method="post">
            <input type="hidden" name="no" value="<?php echo htmlspecialchars($no ?? '', ENT_QUOTES, 'UTF-8')?>">
            <input type="hidden" name="action" value="calculate">
            <!-- 선택된 샘플 이미지 정보 -->
            <input type="hidden" name="sample_image_src" id="hiddenSampleImageSrc" value="">
            <input type="hidden" name="sample_image_filename" id="hiddenSampleImageFilename" value="">
            <input type="hidden" name="sample_selected_at" id="hiddenSampleSelectedAt" value="">
            
            <table class="order-form-table">
                <tbody>
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📄</span>
                                <span>1. 재질 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="jong" class="form-control-modern">
                                <option value="jil 아트유광">✨ 아트지유광 (90g)</option>
                                <option value="jil 아트무광코팅">🌟 아트지무광코팅 (90g)</option>
                                <option value="jil 아트비코팅">💫 아트지비코팅 (90g)</option>
                                <option value="cka 초강접아트유광">⚡ 초강접아트유광 (90g)</option>
                                <option value="cka 초강접아트비코팅">⚡ 초강접아트비코팅 (90g)</option>
                                <option value="jsp 유포지">📄 유포지 (80g)</option>
                                <option value="jsp 투명스티커">🔍 투명스티커</option>
                                <option value="jsp 홀로그램">🌈 홀로그램</option>
                                <option value="jsp 크라프트">🌿 크라프트지</option>
                            </select>
                            <small class="help-text">재질에 따라 스티커의 느낌과 내구성이 달라집니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📏</span>
                                <span>2. 크기 설정</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <div class="size-inputs" style="display: flex; align-items: center; gap: 1rem;">
                                <div class="size-input-inline">
                                    <label class="size-label" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">가로 (mm):</label>
                                    <input type="number" name="garo" class="form-control-inline" placeholder="예: 100" max="560" required 
                                           style="width: 120px; padding: 12px; font-size: 1.1rem; border: 2px solid #ddd; border-radius: 8px; text-align: center; font-weight: 600;"
                                           oninput="validateSizeOnInput(this, '가로')"
                                           onblur="validateSizeInput(this, '가로')">
                                </div>
                                <span class="size-multiply" style="font-size: 1.5rem; font-weight: bold; color: #666; margin: 0 0.5rem;">×</span>
                                <div class="size-input-inline">
                                    <label class="size-label" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">세로 (mm):</label>
                                    <input type="number" name="sero" class="form-control-inline" placeholder="예: 100" max="560" required 
                                           style="width: 120px; padding: 12px; font-size: 1.1rem; border: 2px solid #ddd; border-radius: 8px; text-align: center; font-weight: 600;"
                                           oninput="validateSizeOnInput(this, '세로')"
                                           onblur="validateSizeInput(this, '세로')">
                                </div>
                            </div>
                            <small class="help-text">최대 560mm까지 제작 가능합니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📦</span>
                                <span>3. 수량 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="mesu" class="form-control-modern">
                                <option value="500">500매</option>
                                <option value="1000" selected>1,000매 (추천)</option>
                                <option value="2000">2,000매</option>
                                <option value="3000">3,000매</option>
                                <option value="5000">5,000매</option>
                                <option value="10000">10,000매</option>
                                <option value="20000">20,000매</option>
                                <option value="30000">30,000매 (대량할인)</option>
                            </select>
                            <small class="help-text">수량이 많을수록 단가가 저렴해집니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">✏️</span>
                                <span>4. 편집비</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="uhyung" class="form-control-modern">
                                <option value="0">인쇄만 (파일 준비완료)</option>
                                <option value="10000">기본 편집 (+10,000원)</option>
                                <option value="30000">고급 편집 (+30,000원)</option>
                            </select>
                            <small class="help-text">디자인 파일이 없으시면 편집 서비스를 이용해주세요</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">🔲</span>
                                <span>5. 모양 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="domusong" class="form-control-modern">
                                <option value="00000 사각">⬜ 사각형 (기본)</option>
                                <option value="00001 원형">⭕ 원형</option>
                                <option value="00002 타원">🥚 타원형</option>
                                <option value="00003 별모양">⭐ 별모양</option>
                                <option value="00004 하트">❤️ 하트</option>
                                <option value="00005 다각형">🔷 다각형</option>
                            </select>
                            <small class="help-text">모양에 따라 추가 작업비가 발생할 수 있습니다</small>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div style="text-align: center; margin: 3rem 0;">
                <button type="button" onclick="calculatePrice()" class="btn-calculate">
                    💰 실시간 가격 계산하기
                </button>
            </div>
        </form>
    </div>
    
    <!-- 가격 계산 결과 -->
    <div id="priceSection" class="price-result" style="display: none;">
        <h3 style="margin-bottom: 1rem; font-size: 1.3rem;">💎 견적 결과</h3>
        
        <!-- 견적 결과 표 -->
        <table class="quote-table" id="priceTable">
            <thead>
                <tr>
                    <th>항목</th>
                    <th>내용</th>
                    <th>금액</th>
                </tr>
            </thead>
            <tbody>
                <!-- 옵션 정보 행들 -->
                <tr>
                    <td>재질</td>
                    <td id="selectedMaterial">-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>크기</td>
                    <td id="selectedSize">-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>수량</td>
                    <td id="selectedQuantity">-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>편집비</td>
                    <td id="selectedEdit">-</td>
                    <td id="editPrice">0원</td>
                </tr>
                <tr>
                    <td>모양</td>
                    <td id="selectedShape">-</td>
                    <td>-</td>
                </tr>
                
                <!-- 가격 정보 행들 -->
                <tr class="price-row">
                    <td>인쇄비</td>
                    <td>-</td>
                    <td id="printPrice">0원</td>
                </tr>
                
                <!-- 합계 행들 -->
                <tr class="total-row">
                    <td><strong>합계 (부가세 별도)</strong></td>
                    <td>-</td>
                    <td><strong id="priceAmount">0원</strong></td>
                </tr>
                <tr class="vat-row">
                    <td><strong>총 금액 (부가세 포함)</strong></td>
                    <td>-</td>
                    <td><strong id="priceVat">0원</strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- 가격 계산 후 다음 단계 안내 -->
        <div style="margin: 1rem 0; padding: 1rem; background: #e8f5e8; border: 1px solid #28a745; border-radius: 8px;">
            <h4 style="color: #155724; margin-bottom: 0.5rem; font-size: 1rem; line-height: 1.3;">
                📋 다음 단계: 디자인 파일 업로드 & 주문하기
            </h4>
            
            <?php
            // 스티커용 업로드 컴포넌트 설정 (높이 줄임)
            $uploadComponent = new FileUploadComponent([
                'product_type' => 'sticker',
                'max_file_size' => 10 * 1024 * 1024, // 10MB
                'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                'multiple' => true,
                'drag_drop' => true,
                'show_progress' => true,
                'auto_upload' => true,
                'delete_enabled' => true,
                'compact_mode' => true, // 높이 줄이기
                'custom_messages' => [
                    'title' => '📎 디자인 파일 업로드',
                    'drop_text' => '파일을 드래그하거나 클릭하여 선택하세요',
                    'format_text' => 'JPG, PNG, PDF (최대 10MB)'
                ]
            ]);
            
            // 컴포넌트 렌더링
            echo $uploadComponent->render();
            ?>
            
            <div class="price-action-buttons" style="margin-top: 1.5rem; text-align: center; padding: 1rem 0;">
                <button onclick="addToBasket()" class="btn btn-success" style="
                    display: inline-block;
                    padding: 12px 24px;
                    margin-right: 1rem;
                    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
                ">
                    🛒 장바구니에 담기
                </button>
                <button onclick="directOrder()" class="btn btn-primary" style="
                    display: inline-block;
                    padding: 12px 24px;
                    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
                ">
                    📋 바로 주문하기
                </button>
            </div>
            
            <div style="margin-top: 0.5rem; text-align: center; color: #6c757d; font-size: 0.8rem; line-height: 1.2;">
                💡 팁: 디자인 파일 없이도 주문 가능합니다!
            </div>
        </div>
    </div>
    
    <!-- 업로드 컴포넌트 컴팩트 스타일 -->
    <style>
    /* 가격 섹션 전체 최적화 */
    #priceSection {
        margin-top: 1rem !important;
    }
    
    #priceSection .quote-table {
        font-size: 0.9rem !important;
    }
    
    #priceSection .quote-table th,
    #priceSection .quote-table td {
        padding: 0.5rem !important;
        line-height: 1.2 !important;
    }
    
    /* 다음 단계 섹션 컴팩트화 */
    #priceSection > div {
        margin: 1rem 0 !important;
        padding: 1rem !important;
    }
    
    /* 업로드 컴포넌트 최소화 */
    #priceSection .file-upload-component .upload-section {
        padding: 0.5rem !important;
        margin: 0.3rem 0 !important;
        background: #f8f9fa !important;
    }
    
    #priceSection .file-upload-component .upload-area {
        min-height: 45px !important;
        max-height: 45px !important;
        padding: 0.3rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    #priceSection .file-upload-component h4 {
        margin-bottom: 0.2rem !important;
        font-size: 0.85rem !important;
        color: #495057 !important;
    }
    
    #priceSection .file-upload-component .upload-text {
        font-size: 0.75rem !important;
        margin: 0 !important;
        line-height: 1.1 !important;
    }
    
    #priceSection .file-upload-component .format-info {
        font-size: 0.65rem !important;
        margin-top: 0.2rem !important;
        color: #6c757d !important;
    }
    
    /* 버튼 호버 효과 */
    #priceSection .price-action-buttons button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
    }
    
    /* 다음 단계 섹션 강조 */
    #priceSection .price-action-buttons {
        border-top: 1px solid #28a745;
        margin-top: 0.8rem !important;
        padding: 0.8rem 0 !important;
        min-height: 60px !important;
    }
    
    #priceSection .price-action-buttons button {
        padding: 10px 20px !important;
        font-size: 0.95rem !important;
        margin-right: 0.8rem !important;
    }
    
    /* 크기 입력 필드 스타일 개선 */
    input[name="garo"], input[name="sero"] {
        transition: all 0.3s ease !important;
    }
    
    input[name="garo"]:hover, input[name="sero"]:hover {
        border-color: #007bff !important;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2) !important;
    }
    
    input[name="garo"]:focus, input[name="sero"]:focus {
        border-color: #007bff !important;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25) !important;
        outline: none !important;
    }
    
    /* 모바일 반응형 */
    @media (max-width: 768px) {
        #priceSection .price-action-buttons button {
            display: block !important;
            width: 100% !important;
            margin: 0.5rem 0 !important;
            padding: 15px 20px !important;
        }
        
        #priceSection .file-upload-component .upload-area {
            min-height: 50px !important;
            max-height: 50px !important;
        }
        
        /* 모바일에서 크기 입력 필드 */
        .size-inputs {
            flex-direction: column !important;
            gap: 1rem !important;
            text-align: center !important;
        }
        
        input[name="garo"], input[name="sero"] {
            width: 150px !important;
            padding: 15px !important;
            font-size: 1.2rem !important;
        }
    }
    
    /* 스티커 샘플 갤러리 스타일 */
    .gallery-container {
        padding: 1.5rem;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .gallery-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        background: white;
    }
    
    .gallery-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .gallery-item img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        display: block;
    }
    
    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: flex-end;
        padding: 1rem;
    }
    
    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }
    
    .gallery-hint {
        width: 100%;
        text-align: center;
        padding: 1rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: white;
        text-shadow: 0 1px 3px rgba(0,0,0,0.7);
    }
    
    /* 이미지 확대 모달 */
    .image-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 2rem;
    }
    
    .image-modal-content {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    
    .image-modal-header {
        padding: 1rem 1.5rem;
        background: #2c3e50;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .image-modal-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }
    
    .modal-close-btn {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        transition: background 0.2s ease;
    }
    
    .modal-close-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .image-modal img {
        display: block;
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
    }
    
    .image-modal-footer {
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        text-align: center;
    }
    
    .btn-modal-select {
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 6px;
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-modal-select:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
    }
    
    /* 반응형 디자인 */
    @media (max-width: 768px) {
        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.8rem;
        }
        
        .gallery-item img {
            height: 120px;
        }
        
        .selected-sample-item {
            flex-direction: column;
            text-align: center;
        }
        
        .selected-sample-item img {
            width: 100px;
            height: 100px;
        }
        
        .image-modal-content {
            margin: 1rem;
        }
        
        .image-modal img {
            max-height: 60vh;
        }
    }
    
    /* 스티커 큰 금액 표시 (VAT 제외 공급가) - 마케팅 전략 */
    table#priceTable #priceAmount,
    #priceAmount {
        font-family: 'Noto Sans KR', sans-serif !important;
        font-size: 2.8rem !important;
        font-weight: 900 !important;
        color: #28a745 !important;
        text-shadow: 0 4px 8px rgba(40, 167, 69, 0.5) !important;
        letter-spacing: -1px !important;
        display: inline-block !important;
        background: linear-gradient(145deg, #d4edda, #c3e6cb) !important;
        padding: 12px 20px !important;
        border-radius: 12px !important;
        border: 3px solid #20c997 !important;
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3) !important;
        transform: scale(1.05) !important;
        animation: pulseGreen 2s infinite !important;
    }
    
    @keyframes pulseGreen {
        0%, 100% { 
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
            transform: scale(1.05);
        }
        50% { 
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            transform: scale(1.08);
        }
    }
    
    .total-row {
        background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-left: 4px solid #28a745 !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.1) !important;
    }
    
    .total-row td {
        padding: 20px !important;
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    .total-row td:last-child {
        background: rgba(40, 167, 69, 0.05) !important;
        border-radius: 0 8px 8px 0 !important;
    }
    
    /* VAT 포함 금액은 작게 표시 */
    .vat-row td {
        padding: 10px !important;
        font-size: 0.9rem !important;
        color: #6c757d !important;
    }
    
    @media (max-width: 768px) {
        #priceAmount {
            font-size: 2rem !important;
            padding: 6px 12px !important;
        }
        .total-row td {
            padding: 15px !important;
        }
    }
    </style>

</div>

<script>
// 전역 변수
let currentModal = null;

// 입력 중 실시간 검증 함수 (2자리부터 검증)
function validateSizeOnInput(input, type) {
    const value = input.value;
    const max = 560;
    
    // 첫 번째 숫자는 허용 (1~9)
    if (value.length === 1) {
        console.log(`✅ ${type} 첫 번째 숫자 입력 허용: ${value}`);
        // 스타일 초기화
        input.style.borderColor = '#ddd';
        input.style.backgroundColor = '';
        input.style.boxShadow = '';
        return true;
    }
    
    // 두 번째 숫자부터 검증
    if (value.length >= 2) {
        const numValue = parseInt(value);
        
        console.log(`🔍 ${type} 실시간 검증 (${value.length}자리): ${value} → ${numValue}`);
        
        if (isNaN(numValue) || numValue > max) {
            console.log(`❌ ${type} 실시간 검증 실패: ${numValue}mm (최대 ${max}mm)`);
            
            // 경고창 표시
            alert(`${type} 크기는 ${max}mm 이하로 입력해주세요.\n현재 입력값: ${value}mm`);
            
            // 입력 필드 스타일 변경 (에러 표시)
            input.style.borderColor = '#dc3545';
            input.style.backgroundColor = '#fff5f5';
            input.style.boxShadow = '0 0 5px rgba(220, 53, 69, 0.3)';
            
            // 마지막 문자 제거 (잘못된 입력 취소)
            setTimeout(() => {
                input.value = value.substring(0, value.length - 1);
                input.focus();
            }, 100);
            
            return false;
        } else {
            console.log(`✅ ${type} 실시간 검증 성공: ${numValue}mm`);
            
            // 유효한 값인 경우 스타일 변경
            input.style.borderColor = '#28a745';
            input.style.backgroundColor = '#f8fff8';
            input.style.boxShadow = '0 0 5px rgba(40, 167, 69, 0.2)';
        }
    }
    
    return true;
}

// 크기 입력 검증 함수 (blur 이벤트 시 호출)
function validateSizeInput(input, type) {
    // 입력값이 없으면 검증하지 않음
    if (!input.value || input.value.trim() === '') {
        // 빈 값일 때는 원래 스타일로 복원
        input.style.borderColor = '#ddd';
        input.style.backgroundColor = '';
        input.style.boxShadow = '';
        return true;
    }
    
    const value = parseInt(input.value);
    const max = 560;
    
    console.log(`🔍 ${type} 최종 검증: ${input.value} → ${value}`);
    
    // 숫자가 아니거나 최대값 초과 시
    if (isNaN(value) || value > max) {
        console.log(`❌ ${type} 최종 검증 실패: ${value}mm (최대 ${max}mm)`);
        
        // 경고창 표시
        alert(`${type} 크기는 ${max}mm 이하로 입력해주세요.\n현재 입력값: ${input.value}mm`);
        
        // 입력 필드 스타일 변경 (에러 표시)
        input.style.borderColor = '#dc3545';
        input.style.backgroundColor = '#fff5f5';
        input.style.boxShadow = '0 0 5px rgba(220, 53, 69, 0.3)';
        
        // 포커스 다시 이동
        setTimeout(() => {
            input.focus();
            input.select();
        }, 100);
        
        return false;
    } else {
        console.log(`✅ ${type} 최종 검증 성공: ${value}mm`);
        
        // 유효한 값인 경우 스타일 복원
        input.style.borderColor = '#ddd';
        input.style.backgroundColor = '';
        input.style.boxShadow = '';
    }
    
    return true;
}

// =================================================================
// 🎯 성공한 API 방식 + 포스터 호버링 시스템 (전단지에서 성공한 방식)
// =================================================================

// 포스터 방식 호버링 전역 변수
let stickerCurrentX = 50, stickerTargetX = 50;
let stickerCurrentY = 50, stickerTargetY = 50;  
let stickerCurrentSize = 100, stickerTargetSize = 100;

// 성공했던 API 방식으로 스티커 갤러리 로드
async function loadStickerGallery() {
    try {
        console.log('🔍 API에서 스티커 이미지 로드 시작');
        
        // 성공적으로 작동했던 API 엔드포인트 사용
        const response = await fetch('/api/get_real_orders_portfolio.php?category=sticker&per_page=4', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📊 API 응답 데이터:', data);
        
        if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
            console.log(`✅ ${data.data.length}개 스티커 이미지 발견!`);
            renderStickerGallery(data.data);
        } else {
            console.warn('⚠️ API에서 유효한 데이터를 받지 못함:', data);
            showStickerPlaceholderImages();
        }
    } catch (error) {
        console.error('❌ API 호출 실패:', error);
        showStickerPlaceholderImages();
    }
}

// 갤러리 렌더링 (성공한 전단지 구조와 동일)
function renderStickerGallery(images) {
    console.log('🎨 포스터 방식 스티커 갤러리 렌더링 시작, 이미지 수:', images.length);
    
    const zoomBox = document.getElementById('posterZoomBox');
    const thumbsContainer = document.getElementById('proofThumbs');
    
    if (!zoomBox || !thumbsContainer) {
        console.error('❌ 갤러리 요소를 찾을 수 없음:', {
            zoomBox: !!zoomBox,
            thumbsContainer: !!thumbsContainer
        });
        return;
    }
    
    // 이미지 데이터 검증
    const validImages = images.filter(img => img && img.path && img.path.trim());
    if (validImages.length === 0) {
        console.warn('⚠️ 유효한 이미지가 없음');
        showStickerPlaceholderImages();
        return;
    }
    
    // 포스터 방식: 첫 번째 이미지를 backgroundImage로 설정
    const firstImage = validImages[0];
    zoomBox.style.backgroundImage = `url("${firstImage.path}")`;
    zoomBox.style.backgroundSize = 'contain';
    zoomBox.style.backgroundPosition = '50% 50%';
    
    // 썸네일 생성 (포스터 방식으로 수정)
    thumbsContainer.innerHTML = validImages.map((img, index) => {
        const title = img.title || `스티커 샘플 ${index + 1}`;
        const isActive = index === 0;
        
        return `
            <div class="thumb ${isActive ? 'active' : ''}" 
                 data-img="${img.path.replace(/"/g, '&quot;')}" 
                 data-index="${index}"
                 role="listitem"
                 tabindex="0"
                 aria-label="${title.replace(/"/g, '&quot;')}"
                 aria-selected="${isActive}"
                 onclick="selectStickerThumb(this)"
                 onkeypress="handleStickerThumbKeypress(event, this)">
                <img src="${img.path.replace(/"/g, '&quot;')}" 
                     alt="${title.replace(/"/g, '&quot;')}"
                     loading="lazy"
                     onerror="handleStickerImageError(this)">
            </div>
        `;
    }).join('');
    
    console.log(`✅ 포스터 방식 스티커 갤러리 렌더링 완료 - ${validImages.length}개 이미지`);
    
    // 포스터 방식 호버링 시스템 초기화
    initializeStickerPosterHover();
}

// 썸네일 선택 함수 (포스터 방식으로 수정)
function selectStickerThumb(thumbElement) {
    if (!thumbElement) return;
    
    console.log('👆 포스터 방식 스티커 썸네일 선택:', thumbElement.getAttribute('data-index'));
    
    // 모든 썸네일에서 active 클래스 제거
    document.querySelectorAll('.proof-thumbs .thumb').forEach(function(item) {
        item.classList.remove('active');
        item.setAttribute('aria-selected', 'false');
    });
    
    // 선택한 썸네일에 active 클래스 추가
    thumbElement.classList.add('active');
    thumbElement.setAttribute('aria-selected', 'true');
    
    // 포스터 방식: backgroundImage로 교체
    const imageUrl = thumbElement.getAttribute('data-img');
    const zoomBox = document.getElementById('posterZoomBox');
    
    if (zoomBox && imageUrl) {
        zoomBox.style.backgroundImage = `url("${imageUrl}")`;
        zoomBox.style.backgroundSize = 'contain';
        zoomBox.style.backgroundPosition = '50% 50%';
        
        // 포스터 방식 변수 초기화
        stickerCurrentX = stickerTargetX = 50;
        stickerCurrentY = stickerTargetY = 50;
        stickerCurrentSize = stickerTargetSize = 100;
        
        console.log('🖼️ 포스터 방식 스티커 이미지 교체 완료:', imageUrl);
    }
}

// 키보드 접근성을 위한 키 이벤트 처리
function handleStickerThumbKeypress(event, thumbElement) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        selectStickerThumb(thumbElement);
    }
}

// 이미지 로드 에러 처리
function handleStickerImageError(imgElement) {
    console.warn('⚠️ 스티커 이미지 로드 실패:', imgElement.src);
    imgElement.src = 'https://via.placeholder.com/400x300?text=스티커+이미지+로드+실패&color=999';
    imgElement.alt = '이미지를 불러올 수 없습니다';
}

// 플레이스홀더 이미지 표시
function showStickerPlaceholderImages() {
    console.log('📷 스티커 플레이스홀더 이미지 표시');
    
    const zoomBox = document.getElementById('posterZoomBox');
    const thumbsContainer = document.getElementById('proofThumbs');
    
    if (zoomBox) {
        zoomBox.style.backgroundImage = 'url(https://via.placeholder.com/900x600?text=스티커+샘플+준비중&color=999)';
        zoomBox.style.backgroundSize = 'contain';
        zoomBox.style.backgroundPosition = '50% 50%';
    }
    
    if (thumbsContainer) {
        thumbsContainer.innerHTML = Array.from({length: 4}, (_, index) => `
            <div class="thumb ${index === 0 ? 'active' : ''}"
                 data-img="https://via.placeholder.com/200x150?text=샘플${index + 1}&color=ccc"
                 data-index="${index}"
                 onclick="selectStickerThumb(this)">
                <img src="https://via.placeholder.com/200x150?text=샘플${index + 1}&color=ccc" 
                     alt="스티커 샘플 ${index + 1}" loading="lazy">
            </div>
        `).join('');
    }
}

// 포스터 방식 호버링 시스템 초기화
function initializeStickerPosterHover() {
    console.log('🎯 포스터 방식 스티커 호버링 시스템 초기화');
    
    const viewport = document.getElementById('proofLargeViewport');
    
    if (!viewport) {
        console.warn('⚠️ 뷰포트를 찾을 수 없음');
        return false;
    }
    
    // 기존 이벤트 리스너 모두 제거 (완전 초기화)
    const newViewport = viewport.cloneNode(true);
    viewport.parentNode.replaceChild(newViewport, viewport);
    
    // 새로운 요소 참조
    const refreshedViewport = document.getElementById('proofLargeViewport');
    
    if (!refreshedViewport) {
        console.error('❌ 뷰포트 재참조 실패');
        return false;
    }
    
    // 줌박스 참조
    const zoomBox = document.getElementById('posterZoomBox');
    
    if (!zoomBox) {
        console.error('❌ 줌박스를 찾을 수 없음');
        return false;
    }
    
    // 전역 변수 초기화
    stickerCurrentX = stickerTargetX = 50;
    stickerCurrentY = stickerTargetY = 50;
    stickerCurrentSize = stickerTargetSize = 100;
    
    // 호버링 이벤트 설정
    setupStickerHoverEvents(zoomBox);
    
    // 애니메이션 루프 시작
    startStickerAnimation(zoomBox);
    
    console.log('✅ 스티커 포스터 방식 호버링 설정 완료');
    return true;
}

// 호버 이벤트 설정
function setupStickerHoverEvents(zoomBox) {
    console.log('🎯 스티커 포스터 방식 호버링 초기화');
    
    // 마우스 움직임 추적 (포스터 방식 동일)
    zoomBox.addEventListener('mousemove', function(e) {
        const rect = zoomBox.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        stickerTargetX = x;
        stickerTargetY = y;
        stickerTargetSize = 135; // 1.35배 확대
    });
    
    // 마우스 벗어날 때 초기화 (핵심!)
    zoomBox.addEventListener('mouseleave', function() {
        stickerTargetX = 50;
        stickerTargetY = 50;
        stickerTargetSize = 100;
        console.log('👋 스티커 포스터 방식 호버 초기화');
    });
}

// 부드러운 애니메이션 루프
function startStickerAnimation(zoomBox) {
    function animate() {
        // 부드러운 보간
        stickerCurrentX += (stickerTargetX - stickerCurrentX) * 0.1;
        stickerCurrentY += (stickerTargetY - stickerCurrentY) * 0.1;
        stickerCurrentSize += (stickerTargetSize - stickerCurrentSize) * 0.1;
        
        // 스타일 적용
        zoomBox.style.backgroundSize = `${stickerCurrentSize}%`;
        zoomBox.style.backgroundPosition = `${stickerCurrentX}% ${stickerCurrentY}%`;
        
        requestAnimationFrame(animate);
    }
    animate();
}

// 페이지 로드 시 스티커 갤러리 초기화
document.addEventListener('DOMContentLoaded', function() {
    console.log('스티커 페이지 초기화 완료 - 성공한 API 방식 갤러리');
    loadStickerGallery();
});

// =================================================================
// 기존 코드 (이미지 모달 등)
// =================================================================

// 샘플 이미지 크게 보기 함수
function viewLargeImage(imageSrc, imageTitle) {
    // 모달 생성
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="image-modal-content">
            <div class="image-modal-header">
                <h3 class="image-modal-title">${imageTitle}</h3>
                <button type="button" class="modal-close-btn" onclick="closeImageModal()">&times;</button>
            </div>
            <div class="image-modal-body">
                <img src="${imageSrc}" alt="${imageTitle}" loading="lazy" onclick="closeImageModal()" style="cursor: pointer;">
            </div>
            <div class="image-modal-footer">
                <p style="color: #666; margin: 0; text-align: center;">💡 이미지를 클릭하면 닫힙니다</p>
            </div>
        </div>
    `;
    
    // 모달을 문서에 추가
    document.body.appendChild(modal);
    currentModal = modal;
    
    // ESC 키로 닫기
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeImageModal();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && currentModal) {
            closeImageModal();
        }
    });
    
    // 모달 애니메이션
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}


// 이미지 모달 닫기
function closeImageModal() {
    if (currentModal) {
        currentModal.style.opacity = '0';
        setTimeout(() => {
            if (currentModal && currentModal.parentNode) {
                currentModal.parentNode.removeChild(currentModal);
            }
            currentModal = null;
        }, 300);
    }
}


// 알림 메시지 표시 함수
function showNotification(message, type = 'info') {
    // 기존 알림 제거
    const existingNotification = document.querySelector('.sample-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // 새 알림 생성
    const notification = document.createElement('div');
    notification.className = `sample-notification ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    `;
    
    // 타입별 스타일
    switch(type) {
        case 'success':
            notification.style.background = 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)';
            break;
        case 'error':
            notification.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
            break;
        case 'warning':
            notification.style.background = 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)';
            break;
        default:
            notification.style.background = 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // 애니메이션
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // 자동 제거
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// 선택된 옵션들을 업데이트하는 함수
function updateSelectedOptions() {
    const form = document.getElementById('orderForm');
    
    // 재질
    const materialSelect = form.querySelector('select[name="jong"]');
    if (materialSelect.selectedIndex >= 0) {
        document.getElementById('selectedMaterial').textContent = 
            materialSelect.options[materialSelect.selectedIndex].text;
    }
    
    // 크기
    const garo = form.querySelector('input[name="garo"]').value;
    const sero = form.querySelector('input[name="sero"]').value;
    if (garo && sero) {
        document.getElementById('selectedSize').textContent = `${garo}mm × ${sero}mm`;
    }
    
    // 수량
    const quantitySelect = form.querySelector('select[name="mesu"]');
    if (quantitySelect.selectedIndex >= 0) {
        document.getElementById('selectedQuantity').textContent = 
            quantitySelect.options[quantitySelect.selectedIndex].text;
    }
    
    // 편집비
    const editSelect = form.querySelector('select[name="uhyung"]');
    if (editSelect.selectedIndex >= 0) {
        const editText = editSelect.options[editSelect.selectedIndex].text;
        document.getElementById('selectedEdit').textContent = editText;
        
        // 편집비 금액 표시
        const editValue = editSelect.value;
        if (editValue > 0) {
            document.getElementById('editPrice').textContent = 
                new Intl.NumberFormat('ko-KR').format(editValue) + '원';
        } else {
            document.getElementById('editPrice').textContent = '0원';
        }
    }
    
    // 모양
    const shapeSelect = form.querySelector('select[name="domusong"]');
    if (shapeSelect.selectedIndex >= 0) {
        document.getElementById('selectedShape').textContent = 
            shapeSelect.options[shapeSelect.selectedIndex].text;
    }
}

// 가격 계산 함수
function calculatePrice() {
    const formData = new FormData(document.getElementById('orderForm'));
    const calculateBtn = document.querySelector('.btn-calculate');
    
    // 필수 입력값 검증
    const garo = parseInt(formData.get('garo'));
    const sero = parseInt(formData.get('sero'));
    const mesu = formData.get('mesu');
    
    // 프론트엔드에서 먼저 검증
    if (!garo || !sero) {
        alert('가로와 세로 크기를 입력해주세요.');
        return;
    }
    
    if (garo > 560) {
        alert('가로 크기는 560mm 이하로 입력해주세요.');
        document.querySelector('input[name="garo"]').focus();
        return;
    }
    
    if (sero > 560) {
        alert('세로 크기는 560mm 이하로 입력해주세요.');
        document.querySelector('input[name="sero"]').focus();
        return;
    }
    
    if (!mesu) {
        alert('수량을 선택해주세요.');
        return;
    }
    
    // 버튼 상태 변경 (계산 중)
    const originalText = calculateBtn.textContent;
    calculateBtn.textContent = '💰 가격 계산 중...';
    calculateBtn.disabled = true;
    
    // 선택된 옵션들 업데이트
    updateSelectedOptions();
    
    fetch('calculate_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 인쇄비 계산 (총액에서 편집비 제외)
            const totalPrice = parseInt(data.price.replace(/,/g, ''));
            const editPrice = parseInt(document.getElementById('editPrice').textContent.replace(/[^0-9]/g, '')) || 0;
            const printPrice = totalPrice - editPrice;
            
            document.getElementById('printPrice').textContent = 
                new Intl.NumberFormat('ko-KR').format(printPrice) + '원';
            // VAT 제외 공급가를 큰 글씨로 표시 (마케팅 전략)
            const supplyPrice = parseInt(data.price.replace(/,/g, ''));
            document.getElementById('priceAmount').textContent = new Intl.NumberFormat('ko-KR').format(supplyPrice) + '원';
            console.log('💰 스티커 큰 금액 표시 (VAT 제외):', supplyPrice + '원');
            document.getElementById('priceVat').textContent = data.price_vat + '원';
            
            // 가격 섹션 표시
            document.getElementById('priceSection').style.display = 'block';
            
            // 가격 섹션으로 스크롤
            document.getElementById('priceSection').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
            
        } else {
            alert('가격 계산 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('가격 계산 중 오류가 발생했습니다.');
    })
    .finally(() => {
        // 버튼 상태 복원
        calculateBtn.textContent = originalText;
        calculateBtn.disabled = false;
    });
}

// 장바구니 추가 함수
function addToBasket() {
    // 먼저 가격 계산을 수행
    const formData = new FormData(document.getElementById('orderForm'));
    
    // 필수 입력값 검증
    const jong = formData.get('jong');
    const garo = formData.get('garo');
    const sero = formData.get('sero');
    const mesu = formData.get('mesu');
    
    if (!jong || !garo || !sero || !mesu) {
        alert('모든 필수 옵션을 입력해주세요.');
        return;
    }
    
    // 선택된 옵션들 업데이트
    updateSelectedOptions();
    
    // 가격 계산 먼저 수행
    formData.append('action', 'calculate');
    
    fetch('calculate_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(priceData => {
        if (priceData.success) {
            // 가격 정보 표시
            const totalPrice = parseInt(priceData.price.replace(/,/g, ''));
            const editPrice = parseInt(document.getElementById('editPrice').textContent.replace(/[^0-9]/g, '')) || 0;
            const printPrice = totalPrice - editPrice;
            
            document.getElementById('printPrice').textContent = 
                new Intl.NumberFormat('ko-KR').format(printPrice) + '원';
            // VAT 제외 공급가를 큰 글씨로 표시 (마케팅 전략)
            const supplyPrice2 = parseInt(priceData.price.replace(/,/g, ''));
            document.getElementById('priceAmount').textContent = new Intl.NumberFormat('ko-KR').format(supplyPrice2) + '원';
            console.log('💰 스티커 큰 금액 표시 (VAT 제외) #2:', supplyPrice2 + '원');
            document.getElementById('priceVat').textContent = priceData.price_vat + '원';
            document.getElementById('priceSection').style.display = 'block';
            
            // 가격 계산 성공 시 장바구니에 추가
            const basketFormData = new FormData(document.getElementById('orderForm'));
            basketFormData.append('product_type', 'sticker');
            basketFormData.append('action', 'add_to_basket');
            basketFormData.append('st_price', priceData.price.replace(/,/g, ''));
            basketFormData.append('st_price_vat', priceData.price_vat.replace(/,/g, ''));
            
            
            return fetch('add_to_basket.php', {
                method: 'POST',
                body: basketFormData
            });
        } else {
            throw new Error('가격 계산 실패: ' + priceData.message);
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('장바구니에 추가되었습니다! 🛒');
            if (confirm('장바구니를 확인하시겠습니까?')) {
                window.location.href = 'cart.php';
            }
        } else {
            alert('장바구니 추가 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('장바구니 추가 중 오류가 발생했습니다: ' + error.message);
    });
}

// 바로 주문하기 함수 추가
function directOrder() {
    // 먼저 가격 계산을 수행
    const formData = new FormData(document.getElementById('orderForm'));
    
    // 필수 입력값 검증
    const jong = formData.get('jong');
    const garo = formData.get('garo');
    const sero = formData.get('sero');
    const mesu = formData.get('mesu');
    
    if (!jong || !garo || !sero || !mesu) {
        alert('모든 필수 옵션을 입력해주세요.');
        return;
    }
    
    // 선택된 옵션들 업데이트
    updateSelectedOptions();
    
    // 가격 계산 먼저 수행
    formData.append('action', 'calculate');
    
    fetch('calculate_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(priceData => {
        if (priceData.success) {
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
            params.set('price', priceData.price.replace(/,/g, ''));
            params.set('vat_price', priceData.price_vat.replace(/,/g, ''));
            
            
            // 주문 페이지로 이동
            window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
        } else {
            alert('가격 계산 중 오류가 발생했습니다: ' + priceData.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('가격 계산 중 오류가 발생했습니다: ' + error.message);
    });
}

// 컴포넌트화된 업로드 시스템이 자동으로 초기화됩니다.

// 스티커 갤러리 모달 함수들
let stickerCurrentPage = 1;
let stickerTotalPages = 1;

function openStickerGalleryModal() {
    const modal = document.getElementById('stickerGalleryModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        // 첫 페이지 로드
        loadStickerPage(1);
    }
}

function closeStickerGalleryModal() {
    const modal = document.getElementById('stickerGalleryModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// 스티커 갤러리 페이지 로드 함수
function loadStickerPage(page) {
    if (typeof page === 'string') {
        if (page === 'prev') {
            page = Math.max(1, stickerCurrentPage - 1);
        } else if (page === 'next') {
            page = Math.min(stickerTotalPages, stickerCurrentPage + 1);
        } else {
            page = parseInt(page);
        }
    }
    
    if (page === stickerCurrentPage) return;
    
    const gallery = document.getElementById('stickerGalleryModalGrid');
    if (!gallery) return;
    
    // 로딩 표시
    gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><div style="font-size: 1.5rem;">⏳</div><p>이미지를 불러오는 중...</p></div>';
    
    // API 호출
    fetch(`/api/get_real_orders_portfolio.php?category=sticker&all=true&page=${page}&per_page=12`)
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
                stickerCurrentPage = data.pagination.current_page;
                stickerTotalPages = data.pagination.total_pages;
                
                // 페이지네이션 UI 업데이트
                updateStickerPagination(data.pagination);
            } else {
                gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><p>이미지를 불러올 수 없습니다.</p></div>';
            }
        })
        .catch(error => {
            console.error('스티커 이미지 로드 오류:', error);
            gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><p>이미지 로드 중 오류가 발생했습니다.</p></div>';
        });
}

// 페이지네이션 UI 업데이트
function updateStickerPagination(pagination) {
    // 페이지 정보 업데이트
    const pageInfo = document.getElementById('stickerPageInfo');
    if (pageInfo) {
        pageInfo.textContent = `페이지 ${pagination.current_page} / ${pagination.total_pages} (총 ${pagination.total_count}개)`;
    }
    
    // 버튼 상태 업데이트
    const prevBtn = document.getElementById('stickerPrevBtn');
    const nextBtn = document.getElementById('stickerNextBtn');
    
    if (prevBtn) {
        prevBtn.disabled = !pagination.has_prev;
    }
    if (nextBtn) {
        nextBtn.disabled = !pagination.has_next;
    }
    
    // 페이지 번호 버튼 생성
    const pageNumbers = document.getElementById('stickerPageNumbers');
    if (pageNumbers) {
        pageNumbers.innerHTML = '';
        
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = 'pagination-number' + (i === pagination.current_page ? ' active' : '');
            pageBtn.textContent = i;
            pageBtn.onclick = () => loadStickerPage(i);
            pageNumbers.appendChild(pageBtn);
        }
    }
    
    // 페이지네이션 섹션 표시
    const paginationSection = document.getElementById('stickerPagination');
    if (paginationSection) {
        paginationSection.style.display = pagination.total_pages > 1 ? 'block' : 'none';
    }
}

// 더보기 버튼 표시 확인 함수
function checkStickerMoreButton() {
    // PHP에서 생성된 이미지 수 확인
    const galleryItems = document.querySelectorAll('#stickerGallery .gallery-item');
    const totalImages = galleryItems.length;
    
    // 실제 포트폴리오 이미지 수가 4개 이상이면 더보기 버튼 표시
    if (totalImages >= 4) {
        // 전체 이미지 수 확인을 위해 포트폴리오 디렉토리 체크
        fetch('/api/get_real_orders_portfolio.php?category=sticker&per_page=1')
            .then(response => response.text())
            .then(html => {
                // 이미지 파일 확장자 패턴으로 대략적인 파일 수 추정
                const imageMatches = html.match(/\.(jpg|jpeg|png|gif|bmp)/gi);
                const estimatedCount = imageMatches ? imageMatches.length : 0;
                
                if (estimatedCount > 4) {
                    const moreButton = document.querySelector('.gallery-more-button');
                    if (moreButton) {
                        moreButton.style.display = 'block';
                    }
                }
            })
            .catch(() => {
                // 오류 시 기본적으로 더보기 버튼 표시
                const moreButton = document.querySelector('.gallery-more-button');
                if (moreButton) {
                    moreButton.style.display = 'block';
                }
            });
    }
}

// 페이지 로드 시 더보기 버튼 확인
document.addEventListener('DOMContentLoaded', function() {
    checkStickerMoreButton();
});
</script>


<?php
// 로그인 모달 포함
include "../../includes/login_modal.php";


// 공통 푸터 포함
include "../../includes/footer.php";
?>