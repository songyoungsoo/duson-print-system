<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 페이지 설정
$page_title = '✉️ 두손기획인쇄 - 봉투 자동견적';
$current_page = 'envelope';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 공통 함수 및 설정
include "../../includes/functions.php";

// 세션 및 기본 설정
check_session();
check_db_connection($db);

// 로그 정보 생성
$log_info = generateLogInfo();

// 공통 인증 처리 포함
include "../../includes/auth.php";

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 공통 헤더 포함
include "../../includes/header.php";
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';
?>

<style>
/* 견적 결과 표 스타일 */
.quote-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quote-table th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 12px 15px;
    text-align: left;
    border-bottom: 2px solid #e9ecef;
    font-size: 0.95rem;
}

.quote-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
    color: #495057;
    font-size: 0.95rem;
}

.quote-table tbody tr:hover {
    background: #f8f9fa;
}

.quote-table .price-row {
    background: #f1f3f4;
}

.quote-table .price-row:hover {
    background: #e8eaed;
}

.quote-table .total-row {
    background: #e3f2fd;
    border-top: 2px solid #2196f3;
}

.quote-table .total-row:hover {
    background: #e3f2fd;
}

.quote-table .vat-row {
    background: #e8f5e8;
    border-top: 2px solid #4caf50;
}

.quote-table .vat-row:hover {
    background: #e8f5e8;
}

.quote-table .total-row td,
.quote-table .vat-row td {
    font-size: 1rem;
    font-weight: 600;
}

/* 가격 표시 색상 */
#printPrice, #designPrice {
    color: #2196f3;
    font-weight: 600;
}

#priceAmount {
    color: #2196f3;
    font-weight: 700;
}

#priceVat {
    color: #4caf50;
    font-weight: 700;
}

/* 봉투 페이지 전용 스타일 */
        
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .selection-panel {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        
        .selection-panel h3 {
            margin: 0 0 25px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 1rem;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .info-panel {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        
        .info-panel h3 {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .info-text {
            line-height: 1.6;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .calculate-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn-calculate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-calculate:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .price-section {
            display: none;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        .price-section h3 {
            margin: 0 0 25px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
            text-align: center;
        }
        
        .selected-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .option-item {
            text-align: center;
        }
        
        .option-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .option-value {
            font-weight: 600;
            color: #495057 !important;
            font-size: 1rem;
        }
        
        .selected-options .option-value {
            color: #495057 !important;
            background-color: transparent !important;
        }
        
        #selectedCategory,
        #selectedSize,
        #selectedColor,
        #selectedQuantity,
        #selectedDesign {
            color: #495057 !important;
            font-weight: 600;
        }
        
        .price-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .price-label {
            font-weight: 500;
            color: #495057;
        }
        
        .price-amount {
            font-weight: 700;
            font-size: 1.2rem;
            color: #667eea;
        }
        
        .total-price {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .total-price .price-amount {
            font-size: 2rem;
            color: white !important;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-action {
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #17a2b8;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .file-upload-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        .file-upload-section h4 {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
        }
        
        .file-list {
            min-height: 80px;
            background: white;
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .file-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .btn-file {
            padding: 10px 20px;
            font-size: 0.9rem;
            border: 1px solid #6c757d;
            background: white;
            color: #6c757d;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-file:hover {
            background: #6c757d;
            color: white;
        }
        
        .comment-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        .comment-section h4 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
        }
        
        .comment-section textarea {
            width: 100%;
            min-height: 100px;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .comment-section textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* 이미지 갤러리 스타일 - gallery3.php 방식 */
        .gallery-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* 확대 박스: 420px 높이 - 적응형 이미지 표시 */
        .zoom-box {
            width: 100%;
            height: 420px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-repeat: no-repeat;
            background-position: center center;
            background-size: contain; /* 이미지 전체가 보이도록 */
            background-color: #fff;
            will-change: background-position, background-size;
            cursor: crosshair;
            margin-bottom: 16px;
        }
        
        /* 썸네일 */
        .thumbnails {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .thumbnails img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .thumbnails img.active {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .thumbnails img:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        
        .thumbnail-container {
            margin-top: 15px;
        }
        
        .thumbnail-grid {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .thumbnail:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .thumbnail.active {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .gallery-loading,
        .gallery-error {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
        
        .gallery-error {
            color: #dc3545;
        }
        
        /* 라이트박스 스타일 */
        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .lightbox.active {
            opacity: 1;
            visibility: visible;
        }

        .lightbox-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .lightbox-content img {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            margin: 0 auto;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        .lightbox-caption {
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .lightbox-close:hover {
            background: rgba(0,0,0,0.8);
        }
        
        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .price-details {
                grid-template-columns: 1fr;
            }
            
            .selected-options {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
}

@media (max-width: 768px) {
    .form-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .price-details {
        grid-template-columns: 1fr;
    }
    
    .selected-options {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

            <div class="container">
                <!-- 주문 폼 -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">✉️ 봉투 자동견적</h2>
                        <p class="card-subtitle">다양한 종류의 봉투 견적을 쉽고 빠르게 확인하세요</p>
                    </div>
                    
                    <form id="envelopeForm" method="post">
            <div class="form-container">
                <!-- 선택 옵션 패널 -->
                <div class="selection-panel">
                    <h3>📋 옵션 선택</h3>
                    
                    <div class="form-group">
                        <label for="MY_type">구분</label>
                        <select name="MY_type" id="MY_type" required>
                            <option value="">구분을 선택해주세요</option>
                            <?php
                            $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'envelope');
                            foreach ($categories as $category) {
                                echo "<option value='" . safe_html($category['no']) . "'>" . safe_html($category['title']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="PN_type">종류</label>
                        <select name="PN_type" id="PN_type" required>
                            <option value="">먼저 구분을 선택해주세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="POtype">인쇄색상</label>
                        <select name="POtype" id="POtype" required>
                            <option value="">인쇄색상을 선택해주세요</option>
                            <option value='2'>마스터2도</option>
                            <option value='1'>마스터1도</option>
                            <option value='3'>칼라4도(옵셋)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="MY_amount">수량</label>
                        <select name="MY_amount" id="MY_amount" required>
                            <option value="">수량을 선택해주세요</option>
                            <option value='1000'>1000매</option>
                            <option value='2000'>2000매</option>
                            <option value='3000'>3000매</option>
                            <option value='4000'>4000매</option>
                            <option value='5000'>5000매</option>
                            <option value='6000'>6000매</option>
                            <option value='7000'>7000매</option>
                            <option value='8000'>8000매</option>
                            <option value='9000'>9000매</option>
                            <option value='10000'>10000매</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ordertype">편집디자인</label>
                        <select name="ordertype" id="ordertype" required>
                            <option value="">편집 방식을 선택해주세요</option>
                            <option value="total">디자인+인쇄</option>
                            <option value="print">인쇄만 의뢰</option>
                        </select>
                    </div>
                </div>

                <!-- 이미지 갤러리 패널 -->
                <div class="info-panel">
                    <h3>🖼️ 봉투 샘플</h3>
                    
                    <!-- 부드러운 확대 갤러리 (gallery3.php 방식) -->
                    <div class="gallery-container">
                        <div class="zoom-box" id="zoomBox">
                            <!-- 배경 이미지로 표시됩니다 -->
                        </div>
                        
                        <!-- 썸네일 이미지들 -->
                        <div class="thumbnails" id="thumbnailGrid">
                            <!-- 썸네일들이 여기에 동적으로 로드됩니다 -->
                        </div>
                    </div>
                    
                    <!-- 로딩 상태 -->
                    <div id="galleryLoading" class="gallery-loading">
                        <p>이미지를 불러오는 중...</p>
                    </div>
                    
                    <!-- 에러 상태 -->
                    <div id="galleryError" class="gallery-error" style="display: none;">
                        <p>이미지를 불러올 수 없습니다.</p>
                    </div>
                </div>
            </div>

            <div class="calculate-section">
                <button type="button" onclick="calculatePrice()" class="btn-calculate">
                    💰 견적 계산하기
                </button>
            </div>

            <!-- 가격 표시 섹션 -->
            <div id="priceSection" class="price-section">
                <h3>💰 견적 결과</h3>
                
                <!-- 견적 결과 표 -->
                <table class="quote-table">
                    <thead>
                        <tr>
                            <th>항목</th>
                            <th>내용</th>
                            <th>금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>구분</td>
                            <td id="selectedCategory">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>종류</td>
                            <td id="selectedSize">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>인쇄색상</td>
                            <td id="selectedColor">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>수량</td>
                            <td id="selectedQuantity">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>편집디자인</td>
                            <td id="selectedDesign">-</td>
                            <td>-</td>
                        </tr>
                        <tr class="price-row">
                            <td>인쇄비</td>
                            <td>-</td>
                            <td id="printPrice">0원</td>
                        </tr>
                        <tr class="price-row">
                            <td>디자인비</td>
                            <td>-</td>
                            <td id="designPrice">0원</td>
                        </tr>
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
                
                <div class="action-buttons">
                    <button type="button" onclick="addToBasket()" class="btn-action btn-primary">
                        🛒 장바구니에 담기
                    </button>
                    <button type="button" onclick="directOrder()" class="btn-action btn-secondary">
                        📋 바로 주문하기
                    </button>
                </div>
            </div>

            파일 업로드 섹션
            <!--
            <div class="file-upload-section">
                 <h4>📎 파일 첨부</h4>
                 <div class="file-list" id="fileList">
                     <p style="color: #6c757d; text-align: center; margin: 0;">
                         첨부된 파일이 없습니다.
                     </p>
                 </div>
                 <div class="file-buttons">
                     <button type="button" onclick="uploadFile()" class="btn-file">파일 업로드</button>
                     <button type="button" onclick="deleteSelectedFiles()" class="btn-file">선택 삭제</button>
                 </div>
            </div>
            -->

            <!-- 기타사항 섹션 -->
            <div class="comment-section">
                <h4>📝 기타사항</h4>
                <textarea name="comment" placeholder="추가 요청사항이나 문의사항을 입력해주세요..."></textarea>
            </div>

            <!-- 숨겨진 필드들 -->
            <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
            <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
            <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
            <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
            <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
            <input type="hidden" name="page" value="envelope">
                    </form>
                </div>
            </div>
        </div> <!-- main-content-wrapper 끝 -->   

<!-- 라이트박스 -->
<div id="image-lightbox" class="lightbox">
    <div class="lightbox-content">
        <img id="lightbox-image" src="" alt="">
        <div class="lightbox-caption" id="lightbox-caption"></div>
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    </div>
</div>
     
<?php
// 공통 로그인 모달 포함
include "../../includes/login_modal.php";
?>

<?php
// 공통 푸터 포함
include "../../includes/footer.php";
?>    

    <script>
    // PHP 변수를 JavaScript로 전달 (공통함수 활용)
    var phpVars = {
        MultyUploadDir: "../../PHPClass/MultyUpload",
        log_url: "<?php echo safe_html($log_info['url']); ?>",
        log_y: "<?php echo safe_html($log_info['y']); ?>",
        log_md: "<?php echo safe_html($log_info['md']); ?>",
        log_ip: "<?php echo safe_html($log_info['ip']); ?>",
        log_time: "<?php echo safe_html($log_info['time']); ?>",
        page: "envelope"
    };

    // 파일첨부 관련 함수들
    function uploadFile() {
        const url = `../../PHPClass/MultyUpload/FileUp.php?Turi=${phpVars.log_url}&Ty=${phpVars.log_y}&Tmd=${phpVars.log_md}&Tip=${phpVars.log_ip}&Ttime=${phpVars.log_time}&Mode=tt`;
        window.open(url, 'FileUpload', 'width=500,height=400,scrollbars=yes,resizable=yes');
    }

    function deleteSelectedFiles() {
        // 파일 삭제 로직 (기존 코드 참조)
        console.log('파일 삭제 기능');
    }
    
    // 로그인 메시지가 있으면 모달 자동 표시
    <?php if (!empty($login_message)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showLoginModal();
        <?php if (strpos($login_message, '성공') !== false): ?>
        setTimeout(hideLoginModal, 2000); // 로그인 성공 시 2초 후 자동 닫기
        <?php endif; ?>
    });
    <?php endif; ?>
    
    // 선택한 옵션 요약을 초기화하는 함수
    function resetSelectedOptions() {
        document.getElementById('selectedCategory').textContent = '-';
        document.getElementById('selectedSize').textContent = '-';
        document.getElementById('selectedColor').textContent = '-';
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.getElementById('envelopeForm');
        
        // 각 select 요소에서 선택된 옵션의 텍스트 가져오기
        const categorySelect = form.querySelector('select[name="MY_type"]');
        const sizeSelect = form.querySelector('select[name="PN_type"]');
        const colorSelect = form.querySelector('select[name="POtype"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        // 선택된 옵션의 텍스트 업데이트
        if (categorySelect.selectedIndex > 0) {
            document.getElementById('selectedCategory').textContent = 
                categorySelect.options[categorySelect.selectedIndex].text;
        }
        if (sizeSelect.selectedIndex > 0) {
            document.getElementById('selectedSize').textContent = 
                sizeSelect.options[sizeSelect.selectedIndex].text;
        }
        if (colorSelect.selectedIndex > 0) {
            document.getElementById('selectedColor').textContent = 
                colorSelect.options[colorSelect.selectedIndex].text;
        }
        if (quantitySelect.selectedIndex > 0) {
            document.getElementById('selectedQuantity').textContent = 
                quantitySelect.options[quantitySelect.selectedIndex].text;
        }
        if (designSelect.selectedIndex > 0) {
            document.getElementById('selectedDesign').textContent = 
                designSelect.options[designSelect.selectedIndex].text;
        }
    }
    
    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('envelopeForm');
        const formData = new FormData(form);
        
        // 필수 필드 검증
        if (!formData.get('MY_type') || !formData.get('PN_type') || !formData.get('POtype') || 
            !formData.get('MY_amount') || !formData.get('ordertype')) {
            alert('모든 옵션을 선택해주세요.');
            return;
        }
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        // AJAX로 실제 가격 계산
        const params = new URLSearchParams({
            MY_type: formData.get('MY_type'),
            PN_type: formData.get('PN_type'),
            POtype: formData.get('POtype'),
            MY_amount: formData.get('MY_amount'),
            ordertype: formData.get('ordertype')
        });
        
        fetch('price_cal_ajax.php?' + params.toString())
        .then(response => response.json())
        .then(response => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                const priceData = response.data;
                
                // 선택한 옵션들 업데이트
                updateSelectedOptions(formData);
                
                // 가격 정보 표시
                document.getElementById('printPrice').textContent = format_number(priceData.base_price) + '원';
                document.getElementById('designPrice').textContent = format_number(priceData.design_price) + '원';
                document.getElementById('priceAmount').textContent = format_number(priceData.total_price) + '원';
                document.getElementById('priceVat').textContent = format_number(Math.round(priceData.total_with_vat)) + '원';
                
                // 가격 섹션 표시
                document.getElementById('priceSection').style.display = 'block';
                
                // 부드럽게 스크롤
                document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
                
                // 전역 변수에 가격 정보 저장 (장바구니 추가용)
                window.currentPriceData = priceData;
                
            } else {
                alert(response.message || '가격 계산 중 오류가 발생했습니다.');
                document.getElementById('priceSection').style.display = 'none';
            }
        })
        .catch(error => {
            button.innerHTML = originalText;
            button.disabled = false;
            console.error('가격 계산 오류:', error);
            alert('가격 계산 중 오류가 발생했습니다.');
        });
    }
    
    // 장바구니에 추가하는 함수
    function addToBasket() {
        // 가격 계산이 먼저 되었는지 확인
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('envelopeForm');
        const formData = new FormData(form);
        
        // 가격 정보 추가
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.total_price));
        formData.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        formData.set('product_type', 'envelope');
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 추가중...';
        button.disabled = true;
        
        // AJAX로 장바구니에 추가
        fetch('add_to_basket.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(response => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                alert('장바구니에 추가되었습니다! 🛒');
                
                // 장바구니 확인 여부 묻기
                if (confirm('장바구니를 확인하시겠습니까?')) {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                } else {
                    // 폼 초기화하고 계속 쇼핑
                    document.getElementById('envelopeForm').reset();
                    document.getElementById('priceSection').style.display = 'none';
                    window.currentPriceData = null;
                }
            } else {
                alert('장바구니 추가 중 오류가 발생했습니다: ' + response.message);
            }
        })
        .catch(error => {
            button.innerHTML = originalText;
            button.disabled = false;
            console.error('Error:', error);
            alert('장바구니 추가 중 오류가 발생했습니다.');
        });
    }
    
    // 바로 주문하기 함수
    function directOrder() {
        // 가격 계산이 먼저 되었는지 확인
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('envelopeForm');
        const formData = new FormData(form);
        
        // 주문 정보를 URL 파라미터로 구성
        const params = new URLSearchParams();
        params.set('direct_order', '1');
        params.set('product_type', 'envelope');
        params.set('MY_type', formData.get('MY_type'));
        params.set('PN_type', formData.get('PN_type'));
        params.set('POtype', formData.get('POtype'));
        params.set('MY_amount', formData.get('MY_amount'));
        params.set('ordertype', formData.get('ordertype'));
        params.set('price', Math.round(window.currentPriceData.total_price));
        params.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        
        // 선택된 옵션 텍스트도 전달
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        const colorSelect = document.querySelector('select[name="POtype"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        const designSelect = document.querySelector('select[name="ordertype"]');
        
        params.set('category_text', categorySelect.options[categorySelect.selectedIndex].text);
        params.set('size_text', sizeSelect.options[sizeSelect.selectedIndex].text);
        params.set('color_text', colorSelect.options[colorSelect.selectedIndex].text);
        params.set('quantity_text', quantitySelect.options[quantitySelect.selectedIndex].text);
        params.set('design_text', designSelect.options[designSelect.selectedIndex].text);
        
        // 주문 페이지로 이동
        window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 구분 변경 시 종류 동적 업데이트
    function changeCategoryType(categoryNo) {
        console.log('구분 변경:', categoryNo);
        
        // 종류 업데이트
        updateTypes(categoryNo);
    }
    
    function updateTypes(categoryNo) {
        const typeSelect = document.querySelector('select[name="PN_type"]');
        
        fetch(`get_envelope_types.php?CV_no=${categoryNo}&page=envelope`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            typeSelect.innerHTML = '';
            
            if (!response.success || !response.data) {
                typeSelect.innerHTML = '<option value="">종류 정보가 없습니다</option>';
                console.error('종류 로드 실패:', response.message);
                return;
            }
            
            // 기본 옵션 추가
            typeSelect.innerHTML = '<option value="">종류를 선택해주세요</option>';
            
            // 새 옵션 추가
            response.data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                typeSelect.appendChild(optionElement);
            });
            
            console.log('종류 업데이트 완료:', response.data.length, '개');
        })
        .catch(error => {
            console.error('종류 업데이트 오류:', error);
            typeSelect.innerHTML = '<option value="">종류 로드 오류</option>';
        });
    }
    
    // 숫자 포맷팅 함수
    function format_number(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // 이미지 갤러리 관련 전역 변수
    let galleryImages = [];
    let currentImageIndex = 0;
    
    // 이미지 갤러리 로드 함수
    function loadImageGallery() {
        console.log('갤러리 로드 시작');
        
        const loadingElement = document.getElementById('galleryLoading');
        const errorElement = document.getElementById('galleryError');
        const thumbnailGrid = document.getElementById('thumbnailGrid');
        
        console.log('DOM 요소들:', { loadingElement, errorElement, thumbnailGrid });
        
        // 로딩 표시
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        console.log('fetch 시작');
        fetch('get_envelope_images.php')
        .then(response => {
            console.log('fetch 응답:', response);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(response => {
            console.log('JSON 응답:', response);
            
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            if (response.success && response.data && response.data.length > 0) {
                galleryImages = response.data;
                currentImageIndex = 0;
                
                console.log('이미지 데이터:', galleryImages);
                
                // 메인 이미지 설정
                updateMainImage(0);
                
                // 썸네일 생성
                createThumbnails();
                
                console.log('갤러리 로드 완료');
            } else {
                console.log('이미지 데이터 없음 또는 오류:', response);
                showGalleryError('표시할 이미지가 없습니다.');
            }
        })
        .catch(error => {
            console.error('갤러리 로드 오류:', error);
            
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            showGalleryError('이미지를 불러오는 중 오류가 발생했습니다: ' + error.message);
        });
    }
    
    // 갤러리 줌 기능 초기화
    let targetX = 50, targetY = 50;
    let currentX = 50, currentY = 50;
    let targetSize = 100, currentSize = 100;
    let currentImageType = 'large';
    
    function initGalleryZoom() {
        const zoomBox = document.getElementById('zoomBox');
        
        if (!zoomBox) return;
        
        // 마우스 이동 → 목표 포지션 & 사이즈 설정
        zoomBox.addEventListener('mousemove', e => {
            const { width, height, left, top } = zoomBox.getBoundingClientRect();
            const xPct = (e.clientX - left) / width * 100;
            const yPct = (e.clientY - top) / height * 100;
            targetX = xPct;
            targetY = yPct;
            
            if (currentImageType === 'small') {
                targetSize = 130;
            } else {
                targetSize = 150;
            }
        });
        
        // 마우스 이탈 → 원상태로 복원
        zoomBox.addEventListener('mouseleave', () => {
            targetX = 50;
            targetY = 50;
            targetSize = 100;
        });
        
        console.log('갤러리 줌 기능 초기화 완료');
    }
    
    // 메인 이미지 업데이트 함수
    function updateMainImage(index) {
        if (!galleryImages || galleryImages.length === 0) return;
        
        const image = galleryImages[index];
        const zoomBox = document.getElementById('zoomBox');
        
        console.log('메인 이미지 업데이트:', image);
        
        // 이미지 크기 분석 후 적응형 표시
        analyzeImageSize(image.path, function(backgroundSize) {
            zoomBox.style.backgroundImage = `url('${image.path}')`;
            zoomBox.style.backgroundSize = backgroundSize;
            originalBackgroundSize = backgroundSize;
            
            console.log('이미지 적용 완료:', {
                path: image.path,
                backgroundSize: backgroundSize
            });
        });
        
        currentImageIndex = index;
    }
    
    // 썸네일 생성 함수
    function createThumbnails() {
        const thumbnailGrid = document.getElementById('thumbnailGrid');
        thumbnailGrid.innerHTML = '';
        
        galleryImages.forEach((image, index) => {
            const thumbnail = document.createElement('img');
            thumbnail.src = image.thumbnail;
            thumbnail.alt = image.title;
            thumbnail.className = index === 0 ? 'active' : '';
            thumbnail.title = image.title;
            thumbnail.dataset.src = image.path;
            
            thumbnail.addEventListener('click', () => {
                const allThumbs = thumbnailGrid.querySelectorAll('img');
                allThumbs.forEach(thumb => thumb.classList.remove('active'));
                thumbnail.classList.add('active');
                
                updateMainImage(index);
            });
            
            thumbnailGrid.appendChild(thumbnail);
        });
        
        console.log('썸네일 생성 완료');
    }
    
    // 갤러리 에러 표시 함수
    function showGalleryError(message) {
        const errorElement = document.getElementById('galleryError');
        if (errorElement) {
            errorElement.querySelector('p').textContent = message;
            errorElement.style.display = 'block';
        }
    }
    
    // 이미지 크기 분석 함수
    function analyzeImageSize(imagePath, callback) {
        const img = new Image();
        img.onload = function() {
            const containerHeight = 420;
            const containerWidth = document.getElementById('zoomBox').getBoundingClientRect().width;
            
            let backgroundSize;
            
            if (this.naturalHeight <= containerHeight && this.naturalWidth <= containerWidth) {
                backgroundSize = `${this.naturalWidth}px ${this.naturalHeight}px`;
                currentImageType = 'small';
                console.log('1:1 크기로 표시:', backgroundSize);
            } else {
                backgroundSize = 'contain';
                currentImageType = 'large';
                console.log('전체 비율 맞춤으로 표시: contain');
            }
            
            callback(backgroundSize);
        };
        img.onerror = function() {
            console.log('이미지 로드 실패, 기본 contain 사용');
            currentImageType = 'large';
            callback('contain');
        };
        img.src = imagePath;
    }
    
    // 라이트박스 함수들
    function openLightbox(imageSrc, caption) {
        document.getElementById('lightbox-image').src = imageSrc;
        document.getElementById('lightbox-caption').textContent = caption || '';
        document.getElementById('image-lightbox').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeLightbox() {
        document.getElementById('image-lightbox').classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // 부드러운 애니메이션
    let originalBackgroundSize = 'contain';
    
    function animateZoom() {
        const zoomBox = document.getElementById('zoomBox');
        if (!zoomBox) return;
        
        const speed = 0.08;
        currentX += (targetX - currentX) * speed;
        currentY += (targetY - currentY) * speed;
        currentSize += (targetSize - currentSize) * speed;
        
        if (currentSize !== 100) {
            const scalePercent = currentSize / 100;
            
            if (originalBackgroundSize.includes('px')) {
                const [width, height] = originalBackgroundSize.split(' ');
                const newWidth = parseFloat(width) * scalePercent;
                const newHeight = parseFloat(height) * scalePercent;
                zoomBox.style.backgroundSize = `${newWidth}px ${newHeight}px`;
            } else {
                zoomBox.style.backgroundSize = 'cover';
            }
        } else {
            zoomBox.style.backgroundSize = originalBackgroundSize;
        }
        
        zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
        requestAnimationFrame(animateZoom);
    }
    
    // 입력값 변경 시 실시간 유효성 검사
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('change', function() {
            if (this.checkValidity()) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e74c3c';
            }
        });
    });
    
    // 페이지 로드 시 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // 드롭다운 변경 이벤트 리스너 추가
        const categorySelect = document.querySelector('select[name="MY_type"]');
        
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                changeCategoryType(this.value);
            });
        }
        
        // 이미지 갤러리 로드
        loadImageGallery();
        
        // 갤러리 줌 기능 초기화
        initGalleryZoom();
        
        // 애니메이션 시작
        animateZoom();
        
        // 라이트박스 이벤트 설정
        const lightboxImage = document.getElementById('lightbox-image');
        const imageLightbox = document.getElementById('image-lightbox');
        
        if (lightboxImage && imageLightbox) {
            // 라이트박스 이미지 클릭 시 닫기
            lightboxImage.addEventListener('click', closeLightbox);
            
            // 라이트박스 배경 클릭 시 닫기
            imageLightbox.addEventListener('click', function(e) {
                if (e.target.id === 'image-lightbox') {
                    closeLightbox();
                }
            });
        }
        
        // 초기 옵션 선택 시 종류 업데이트
        setTimeout(function() {
            if (categorySelect.value) {
                changeCategoryType(categorySelect.value);
            }
        }, 500);
    });
    </script>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>
