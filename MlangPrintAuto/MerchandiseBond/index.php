<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 세션 및 기본 설정
check_session();
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 생성
$log_info = generateLogInfo();

// 로그인 처리
$login_message = '';
if ($_POST['login_action'] ?? '' === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $login_message = '로그인 성공! 환영합니다.';
    } else {
        $login_message = '아이디와 비밀번호를 입력해주세요.';
    }
}

// 페이지 제목 설정
$page_title = generate_page_title("상품권/쿠폰 자동견적");

// 기본값 설정 (단계별로 하나씩)
$default_values = [
    'MY_type' => '',
    'MY_amount' => '',
    'POtype' => '1', // 기본값: 단면
    'PN_type' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 1단계: 첫 번째 상품권 종류 가져오기 (상품권 우선)
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='MerchandiseBond' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%상품권%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 공통 CSS -->
    <link rel="stylesheet" href="../../css/common_style.css">
    
    <!-- 견적 표 CSS 추가 -->
    <link rel="stylesheet" href="../../includes/css/quote-table.css">
    
    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .main-content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .page-header p {
            margin: 10px 0 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
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
        
        /* 기존 카드 형식 스타일들 (표 형식으로 교체됨) */
        /* 
        .selected-options, .price-details, .total-price 등의 스타일들은 
        quote-table.css의 표 형식으로 대체되었습니다.
        */
        
        /* 표 형식에서 선택된 옵션 값들의 색상 설정 */
        #selectedCategory,
        #selectedQuantity,
        #selectedSide,
        #selectedAfterProcess,
        #selectedDesign {
            color: #495057 !important;
            font-weight: 600;
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
            cursor: pointer;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background-color: rgba(0,0,0,0.5);
            border-radius: 50%;
        }

        .lightbox-close:hover {
            background-color: rgba(255,0,0,0.7);
        }

        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .thumbnail-grid {
                justify-content: center;
            }
            
            .thumbnail {
                width: 60px;
                height: 60px;
            }
            
            .main-image {
                height: 250px;
            }
        }
    </style>
    
    <!-- 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    
    <!-- jQuery 라이브러리 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- 업로드 컴포넌트 JavaScript 라이브러리 포함 -->
    <script src="../../includes/js/UniversalFileUpload.js"></script>
</head>
<body>
    <?php
    // 공통 헤더 포함
    include "../../includes/header.php";
    ?>

    <?php
    // 공통 네비게이션 포함
    include "../../includes/nav.php";
    ?>

    <div class="main-content-wrapper">
        <div class="page-header">
            <h1>🎫 상품권/쿠폰 자동견적</h1>
            <p>고품질 상품권과 쿠폰을 간편하게 주문하세요</p>
        </div>

        <form id="merchandisebondForm" method="post">
            <div class="form-container">
                <!-- 선택 옵션 패널 -->
                <div class="selection-panel">
                    <h3>📋 옵션 선택</h3>
                    
                    <div class="form-group">
                        <label for="MY_type">종류</label>
                        <select name="MY_type" id="MY_type" required>
                            <option value="">종류를 선택해주세요</option>
                            <?php
                            $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'MerchandiseBond');
                            foreach ($categories as $category) {
                                $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="MY_amount">수량</label>
                        <select name="MY_amount" id="MY_amount" required>
                            <option value="">먼저 종류를 선택해주세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="POtype">인쇄면</label>
                        <select name="POtype" id="POtype" required>
                            <option value="">인쇄면을 선택해주세요</option>
                            <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                            <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="PN_type">후가공</label>
                        <select name="PN_type" id="PN_type" required>
                            <option value="">먼저 종류, 수량, 인쇄면을 선택해주세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ordertype">편집디자인</label>
                        <select name="ordertype" id="ordertype" required>
                            <option value="">편집 방식을 선택해주세요</option>
                            <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                            <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                        </select>
                    </div>
                </div>

                <!-- 이미지 갤러리 패널 -->
                <div class="info-panel">
                    <h3>🖼️ 상품권/쿠폰 샘플</h3>
                    
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
                        <!-- 옵션 정보 행들 -->
                        <tr>
                            <td>상품권/쿠폰 종류</td>
                            <td id="selectedCategory">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>수량</td>
                            <td id="selectedQuantity">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>인쇄면</td>
                            <td id="selectedSide">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>후가공</td>
                            <td id="selectedAfterProcess">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>편집디자인</td>
                            <td id="selectedDesign">-</td>
                            <td>-</td>
                        </tr>
                        
                        <!-- 가격 정보 행들 -->
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
                
                <?php
                // 상품권용 업로드 컴포넌트 설정
                $uploadComponent = new FileUploadComponent([
                    'product_type' => 'merchandisebond',
                    'max_file_size' => 8 * 1024 * 1024, // 8MB
                    'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                    'multiple' => true,
                    'drag_drop' => true,
                    'show_progress' => true,
                    'auto_upload' => true,
                    'delete_enabled' => true,
                    'custom_messages' => [
                        'title' => '상품권/쿠폰 디자인 파일 업로드',
                        'drop_text' => '상품권/쿠폰 디자인 파일을 여기로 드래그하거나 클릭하여 선택하세요',
                        'format_text' => '지원 형식: JPG, PNG, PDF (최대 8MB)'
                    ]
                ]);
                
                // 컴포넌트 렌더링
                echo $uploadComponent->render();
                ?>
                
                <div class="action-buttons">
                    <button type="button" onclick="addToBasket()" class="btn-action btn-primary">
                        🛒 장바구니에 담기
                    </button>
                    <button type="button" onclick="directOrder()" class="btn-action btn-secondary">
                        📋 바로 주문하기
                    </button>
                </div>
            </div>

            <!-- 파일 업로드 섹션 -->
 <!--           <div class="file-upload-section">
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
            </div> -->

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
            <input type="hidden" name="page" value="MerchandiseBond">
        </form>
    </div> <!-- main-content-wrapper 끝 -->   

<!-- 라이트박스 HTML -->
<div id="image-lightbox" class="lightbox">
    <div class="lightbox-content">
        <img id="lightbox-image" src="" alt="">
        <div class="lightbox-caption" id="lightbox-caption"></div>
    </div>
    <div class="lightbox-close" onclick="closeLightbox()">×</div>
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
        page: "MerchandiseBond"
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
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedSide').textContent = '-';
        document.getElementById('selectedAfterProcess').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.getElementById('merchandisebondForm');
        
        const categorySelect = form.querySelector('select[name="MY_type"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const sideSelect = form.querySelector('select[name="POtype"]');
        const afterProcessSelect = form.querySelector('select[name="PN_type"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        if (categorySelect.selectedIndex > 0) {
            document.getElementById('selectedCategory').textContent = categorySelect.options[categorySelect.selectedIndex].text;
        }
        if (quantitySelect.selectedIndex > 0) {
            document.getElementById('selectedQuantity').textContent = quantitySelect.options[quantitySelect.selectedIndex].text;
        }
        if (sideSelect.selectedIndex > 0) {
            document.getElementById('selectedSide').textContent = sideSelect.options[sideSelect.selectedIndex].text;
        }
        if (afterProcessSelect.selectedIndex > 0) {
            document.getElementById('selectedAfterProcess').textContent = afterProcessSelect.options[afterProcessSelect.selectedIndex].text;
        }
        if (designSelect.selectedIndex > 0) {
            document.getElementById('selectedDesign').textContent = designSelect.options[designSelect.selectedIndex].text;
        }
    }
    
    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('merchandisebondForm');
        const formData = new FormData(form);
        
        if (!formData.get('MY_type') || !formData.get('MY_amount') || !formData.get('POtype') || !formData.get('PN_type') || !formData.get('ordertype')) {
            alert('모든 옵션을 선택해주세요.');
            return;
        }
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        const params = new URLSearchParams(new FormData(form));
        
        fetch('price_cal_ajax.php?' + params.toString())
        .then(response => response.json())
        .then(response => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                const priceData = response.data;
                updateSelectedOptions(formData);
                
                document.getElementById('printPrice').textContent = format_number(priceData.base_price) + '원';
                document.getElementById('designPrice').textContent = format_number(priceData.design_price) + '원';
                document.getElementById('priceAmount').textContent = format_number(priceData.total_price) + '원';
                document.getElementById('priceVat').textContent = format_number(Math.round(priceData.total_with_vat)) + '원';
                
                document.getElementById('priceSection').style.display = 'block';
                document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
                
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
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('merchandisebondForm');
        const formData = new FormData(form);
        
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.total_price));
        formData.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        formData.set('product_type', 'merchandisebond');
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 추가중...';
        button.disabled = true;
        
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
                if (confirm('장바구니를 확인하시겠습니까?')) {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                } else {
                    form.reset();
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
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('merchandisebondForm');
        const params = new URLSearchParams(new FormData(form));
        params.set('direct_order', '1');
        params.set('product_type', 'merchandisebond');
        params.set('price', Math.round(window.currentPriceData.total_price));
        params.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        
        // 선택된 옵션 텍스트 전달
        params.set('category_text', form.querySelector('select[name="MY_type"]').options[form.querySelector('select[name="MY_type"]').selectedIndex].text);
        params.set('quantity_text', form.querySelector('select[name="MY_amount"]').options[form.querySelector('select[name="MY_amount"]').selectedIndex].text);
        params.set('side_text', form.querySelector('select[name="POtype"]').options[form.querySelector('select[name="POtype"]').selectedIndex].text);
        params.set('after_process_text', form.querySelector('select[name="PN_type"]').options[form.querySelector('select[name="PN_type"]').selectedIndex].text);
        params.set('design_text', form.querySelector('select[name="ordertype"]').options[form.querySelector('select[name="ordertype"]').selectedIndex].text);
        
        window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 옵션 업데이트 함수
    function updateOptions(selectElement, url, params, defaultOptionText) {
        selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
        
        fetch(`${url}?${new URLSearchParams(params).toString()}`)
        .then(response => response.json())
        .then(response => {
            if (response.success && response.data) {
                response.data.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value || option.no;
                    optionElement.textContent = option.text || option.title;
                    selectElement.appendChild(optionElement);
                });
            } else {
                console.error('옵션 로드 실패:', response.message);
            }
        })
        .catch(error => {
            console.error('옵션 업데이트 오류:', error);
            selectElement.innerHTML = '<option value="">로드 오류</option>';
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
        const mainImageElement = document.getElementById('mainImage');
        const thumbnailGrid = document.getElementById('thumbnailGrid');
        
        console.log('DOM 요소들:', { loadingElement, errorElement, mainImageElement, thumbnailGrid });
        
        // 로딩 표시
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        console.log('fetch 시작');
        fetch('get_coupon_images.php')
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
                
                // 갤러리 표시 - info-panel은 이미 보임
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
    
    // 갤러리 줌 기능 초기화 - 적응형 이미지 표시 및 확대
    let targetX = 50, targetY = 50;
    let currentX = 50, currentY = 50;
    let targetSize = 100, currentSize = 100;
    let currentImageDimensions = { width: 0, height: 0 };
    let currentImageType = 'large'; // 'small' 또는 'large'
    
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
            
            // 이미지 타입에 따른 확대 비율 설정
            if (currentImageType === 'small') {
                targetSize = 130; // 작은 이미지: 1.3배 확대
            } else {
                targetSize = 150; // 큰 이미지: 1.5배 확대
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
    
    // 이미지 크기 분석 및 적응형 표시 설정
    function analyzeImageSize(imagePath, callback) {
        const img = new Image();
        img.onload = function() {
            const containerHeight = 420; // 컨테이너 높이
            const containerWidth = document.getElementById('zoomBox').getBoundingClientRect().width;
            
            currentImageDimensions.width = this.naturalWidth;
            currentImageDimensions.height = this.naturalHeight;
            
            let backgroundSize;
            
            // 이미지가 420px 높이보다 작고 비율이 적절하면 1:1 표시
            if (this.naturalHeight <= containerHeight && this.naturalWidth <= containerWidth) {
                backgroundSize = `${this.naturalWidth}px ${this.naturalHeight}px`;
                currentImageType = 'small'; // 작은 이미지로 분류
                console.log('1:1 크기로 표시 (1.3배 확대):', backgroundSize);
            } else {
                // 이미지가 크면 contain으로 전체 모양 보이게
                backgroundSize = 'contain';
                currentImageType = 'large'; // 큰 이미지로 분류
                console.log('전체 비율 맞춤으로 표시 (1.5배 확대): contain');
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
    
    // 부드러운 애니메이션 루프 - 적응형 크기 지원
    let originalBackgroundSize = 'contain'; // 원래 배경 크기 저장
    
    function animate() {
        const zoomBox = document.getElementById('zoomBox');
        if (!zoomBox) return;
        
        // lerp 계수: 0.15 → 부드러운 추적
        currentX += (targetX - currentX) * 0.15;
        currentY += (targetY - currentY) * 0.15;
        currentSize += (targetSize - currentSize) * 0.15;
        
        zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
        
        // 확대 시에는 항상 퍼센트 방식으로 처리
        if (currentSize > 100.1) { // 확대 중
            // 확대 시에는 이미지가 잘리도록 cover 방식 사용
            zoomBox.style.backgroundSize = `${currentSize}%`;
        } else { // 원래 크기로 복원 중
            // 원래 크기로 복원
            zoomBox.style.backgroundSize = originalBackgroundSize;
        }
        
        requestAnimationFrame(animate);
    }
    
    // 메인 이미지 업데이트 함수 - 적응형 표시 방식
    function updateMainImage(index) {
        if (galleryImages.length === 0) return;
        
        const zoomBox = document.getElementById('zoomBox');
        const image = galleryImages[index];
        
        console.log('메인 이미지 업데이트:', image);
        
        // 이미지 크기 분석 후 적응형 표시
        analyzeImageSize(image.path, function(backgroundSize) {
            // 배경 이미지 및 크기 설정
            zoomBox.style.backgroundImage = `url('${image.path}')`;
            zoomBox.style.backgroundSize = backgroundSize;
            
            // 원래 배경 크기 저장 (애니메이션에서 사용)
            originalBackgroundSize = backgroundSize;
            
            console.log('이미지 적용 완료:', {
                path: image.path,
                size: backgroundSize,
                dimensions: currentImageDimensions
            });
        });
        
        currentImageIndex = index;
        
        // 타겟 상태 초기화
        targetSize = 100;
        targetX = 50;
        targetY = 50;
        
        // 썸네일 active 상태 업데이트
        updateThumbnailActive(index);
    }
    
    // 제거됨: 복잡한 줌 기능 제거
    
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
            thumbnail.dataset.src = image.path; // gallery3.php 방식
            
            // 썸네일 클릭 이벤트 - gallery3.php 방식
            thumbnail.addEventListener('click', () => {
                // 모든 썸네일에서 active 클래스 제거
                const allThumbs = thumbnailGrid.querySelectorAll('img');
                allThumbs.forEach(t => t.classList.remove('active'));
                
                // 클릭된 썸네일에 active 클래스 추가
                thumbnail.classList.add('active');
                
                // 메인 이미지 업데이트
                updateMainImage(index);
            });
            
            thumbnailGrid.appendChild(thumbnail);
        });
        
        // 첫 번째 이미지로 초기화
        if (galleryImages.length > 0) {
            updateMainImage(0);
        }
    }
    
    // 썸네일 active 상태 업데이트
    function updateThumbnailActive(activeIndex) {
        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            if (index === activeIndex) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }
    
    // 갤러리 에러 표시 함수
    function showGalleryError(message) {
        const errorElement = document.getElementById('galleryError');
        errorElement.innerHTML = '<p>' + message + '</p>';
        errorElement.style.display = 'block';
        document.querySelector('.image-gallery-section').style.display = 'block';
    }
    
    // 라이트박스 열기 함수
    function openLightbox(imageSrc, caption) {
        document.getElementById('lightbox-image').src = imageSrc;
        document.getElementById('lightbox-caption').textContent = caption;
        document.getElementById('image-lightbox').classList.add('active');
        // 배경 스크롤 방지
        document.body.style.overflow = 'hidden';
    }
    
    // 라이트박스 닫기 함수
    function closeLightbox() {
        document.getElementById('image-lightbox').classList.remove('active');
        // 스크롤 다시 활성화
        document.body.style.overflow = 'auto';
    }
    
    // 페이지 로드 시 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // 이미지 갤러리 로드
        loadImageGallery();
        
        // 갤러리 줌 기능 초기화
        initGalleryZoom();
        
        // 애니메이션 루프 시작
        animate();
        
        // 드롭다운 초기화는 아래에서 처리
        
        // 라이트박스 이벤트 설정
        const lightboxImage = document.getElementById('lightbox-image');
        const imageLightbox = document.getElementById('image-lightbox');
        
        if (lightboxImage && imageLightbox) {
            // 라이트박스 이미지 클릭 시 닫기
            lightboxImage.addEventListener('click', function() {
                closeLightbox();
            });
            
            // 라이트박스 배경 클릭 시 닫기
            imageLightbox.addEventListener('click', function(e) {
                if (e.target.id === 'image-lightbox') {
                    closeLightbox();
                }
            });
            
            // ESC 키 누르면 라이트박스 닫기
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeLightbox();
                }
            });
        }
        
        // 옵션 업데이트 헬퍼 함수 (먼저 정의)
        function updateOptions(selectElement, endpoint, params, defaultText) {
            const queryString = new URLSearchParams(params).toString();
            const fullUrl = `${endpoint}?${queryString}`;
            
            console.log(`[모바일 디버그] ${endpoint} 요청 시작:`, fullUrl);
            selectElement.innerHTML = `<option value="">로딩중...</option>`;
            
            fetch(fullUrl)
                .then(response => {
                    console.log(`[모바일 디버그] ${endpoint} 응답 상태:`, response.status, response.statusText);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(`[모바일 디버그] ${endpoint} 데이터:`, data);
                    selectElement.innerHTML = `<option value="">${defaultText}</option>`;
                    
                    if (data.success && data.data && Array.isArray(data.data)) {
                        console.log(`[모바일 디버그] ${endpoint} 옵션 개수:`, data.data.length);
                        data.data.forEach(option => {
                            const optionElement = document.createElement('option');
                            optionElement.value = option.no || option.value;
                            optionElement.textContent = option.title || option.text;
                            selectElement.appendChild(optionElement);
                        });
                    } else {
                        console.warn(`[모바일 디버그] ${endpoint} 잘못된 데이터 형식:`, data);
                        selectElement.innerHTML = `<option value="">데이터 없음</option>`;
                    }
                })
                .catch(error => {
                    console.error(`[모바일 디버그] ${endpoint} 로드 오류:`, error);
                    selectElement.innerHTML = `<option value="">네트워크 오류: ${error.message}</option>`;
                    
                    // 모바일에서 네트워크 재시도 로직
                    if (navigator.onLine === false) {
                        selectElement.innerHTML = `<option value="">인터넷 연결 확인 필요</option>`;
                    }
                });
        }

        // 드롭다운 이벤트 핸들러 설정
        const typeSelect = document.querySelector('select[name="MY_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        const sideSelect = document.querySelector('select[name="POtype"]');
        const afterProcessSelect = document.querySelector('select[name="PN_type"]');

        // 종류 선택 시 이벤트
        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            console.log(`[모바일 디버그] 종류 변경:`, selectedType);

            if (selectedType) {
                // 모바일에서 지연 시간 추가 (네트워크 안정화)
                setTimeout(() => {
                    // 수량 옵션 로드
                    updateOptions(quantitySelect, 'get_merchandisebond_quantities.php', { MY_type: selectedType }, '수량을 선택해주세요');
                    // 후가공 옵션 로드  
                    updateOptions(afterProcessSelect, 'get_merchandisebond_after_process.php', { MY_type: selectedType }, '후가공을 선택해주세요');
                }, 100);
            } else {
                // 종류가 선택되지 않으면 모든 하위 드롭다운 초기화
                quantitySelect.innerHTML = '<option value="">먼저 종류를 선택해주세요</option>';
                sideSelect.innerHTML = '<option value="">먼저 종류를 선택해주세요</option>';
                afterProcessSelect.innerHTML = '<option value="">먼저 종류를 선택해주세요</option>';
            }
        });
        
        // 페이지 로드 시 기본값 처리 (함수 정의 후에 실행)
        console.log(`[모바일 디버그] 페이지 로드 시 종류 기본값:`, typeSelect.value);
        
        // 모바일에서 DOM 안정화를 위한 지연
        setTimeout(() => {
            if (typeSelect.value) {
                console.log(`[모바일 디버그] 기본값으로 옵션 로드 시작:`, typeSelect.value);
                // 자동으로 수량과 후가공 옵션 로드
                const selectedType = typeSelect.value;
                updateOptions(quantitySelect, 'get_merchandisebond_quantities.php', { MY_type: selectedType }, '수량을 선택해주세요');
                updateOptions(afterProcessSelect, 'get_merchandisebond_after_process.php', { MY_type: selectedType }, '후가공을 선택해주세요');
            } else {
                console.log(`[모바일 디버그] 기본값 없음 - 초기 상태 유지`);
            }
        }, 300);
        
        // 네트워크 상태 모니터링 (모바일 전용)
        if ('onLine' in navigator) {
            window.addEventListener('online', function() {
                console.log('[모바일 디버그] 네트워크 연결 복구됨');
                if (typeSelect.value) {
                    // 네트워크 복구 시 다시 로드 시도
                    const selectedType = typeSelect.value;
                    updateOptions(quantitySelect, 'get_merchandisebond_quantities.php', { MY_type: selectedType }, '수량을 선택해주세요');
                    updateOptions(afterProcessSelect, 'get_merchandisebond_after_process.php', { MY_type: selectedType }, '후가공을 선택해주세요');
                }
            });
            
            window.addEventListener('offline', function() {
                console.warn('[모바일 디버그] 네트워크 연결 끊어짐');
            });
        }
    });
    </script>

<?php
// 데이터베이스 연결 종료
if ($db) {
    mysqli_close($db);
}
?>
</body>
</html>
