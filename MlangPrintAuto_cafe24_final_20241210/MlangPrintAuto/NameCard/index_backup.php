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
$page_title = generate_page_title("명함 견적안내");

// 기본값 설정 (데이터베이스에서 가져오기)
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 명함 종류 가져오기 (일반명함(쿠폰) 우선)
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='NameCard' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%일반명함%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 명함 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                      WHERE Ttable='NameCard' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (500매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM MlangPrintAuto_NameCard 
                          WHERE style='" . $type_row['no'] . "' AND Section='" . $section_row['no'] . "' 
                          ORDER BY CASE WHEN quantity='500' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
                          LIMIT 1";
        $quantity_result = mysqli_query($db, $quantity_query);
        if ($quantity_result && ($quantity_row = mysqli_fetch_assoc($quantity_result))) {
            $default_values['MY_amount'] = $quantity_row['quantity'];
        }
    }
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
        
        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .thumbnail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .zoom-box {
                height: 300px;
            }
            
            /* 모바일에서 표 스타일 조정 */
            .quote-table th,
            .quote-table td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }
            
            .quote-table th:first-child,
            .quote-table td:first-child {
                width: 25%;
            }
            
            .quote-table th:nth-child(2),
            .quote-table td:nth-child(2) {
                width: 45%;
            }
            
            .quote-table th:last-child,
            .quote-table td:last-child {
                width: 30%;
                text-align: right;
            }
        }
        
        /* 이미지 갤러리 스타일 - gallery3.php 방식 */
        .image-gallery-section {
            margin-top: 30px;
            /* padding-top: 20px; */
            /* border-top: 1px solid #e9ecef; */
        }
        
        .image-gallery-section h4 {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
        }
        
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
        
        .gallery-loading, .gallery-error {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            font-style: italic;
        }
        
        .gallery-error {
            color: #dc3545;
        }
    </style>
    
    <!-- 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    
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
            <h1>💳 명함 견적안내</h1>
            <p>다양한 재질의 명함을 쉽고 빠르게 주문하세요</p>
        </div>

        <form id="namecardForm" method="post">
            <div class="form-container">
                <!-- 선택 옵션 패널 -->
                <div class="selection-panel">
                    <h3>📋 옵션 선택</h3>
                    
                    <div class="form-group">
                        <label for="MY_type">명함 종류</label>
                        <select name="MY_type" id="MY_type" required>
                            <option value="">명함 종류를 선택해주세요</option>
                            <?php
                            $categories = getCategoryOptions($db, "MlangPrintAuto_transactionCate', 'NameCard');
                            foreach ($categories as $category) {
                                $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="Section">명함 재질</label>
                        <select name="Section" id="Section" required>
                            <option value="">먼저 명함 종류를 선택해주세요</option>
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
                        <label for="MY_amount">수량</label>
                        <select name="MY_amount" id="MY_amount" required>
                            <option value="">먼저 명함 종류를 선택해주세요</option>
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

                <!-- 정보 패널 -->
                <div class="info-panel">
                    <!-- <h3>ℹ️ 명함 안내</h3> -->
                    <!-- 명함 안내 텍스트 (주석 처리)
                    <div class="info-text">
                        <p><strong>명함 특징:</strong></p>
                        <ul>
                            <li>일반지, 고급지, 특수지, 카드 등 다양한 재질</li>
                            <li>귀도리, 박, 형압 등 다양한 후가공 가능</li>
                            <li>최소 200매부터 주문 가능 (일부 품목 상이)</li>
                        </ul>
                        
                        <p><strong>제작 기간:</strong></p>
                        <ul>
                            <li>일반 명함: 1-2일</li>
                            <li>후가공 추가 시: 2-3일 추가</li>
                        </ul>
                        
                        <p><strong>고객센터:</strong> 02-2632-1830</p>
                    </div>
                    -->
                    
                    <!-- 이미지 갤러리 섹션 추가 -->
                    <div class="image-gallery-section">
                        <h4>🖼️ 명함 샘플</h4>
                        
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
                            <td>명함 종류</td>
                            <td id="selectedType">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>명함 재질</td>
                            <td id="selectedPaper">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>인쇄면</td>
                            <td id="selectedSide">-</td>
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

            <!-- 파일 업로드 섹션 - 견적 계산 후에만 표시 -->
            <div id="fileUploadSection" style="display: none;">
                <?php
                // 명함용 업로드 컴포넌트 설정
                $uploadComponent = new FileUploadComponent([
                    'product_type' => 'namecard',
                    'max_file_size' => 5 * 1024 * 1024, // 5MB
                    'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                    'multiple' => true,
                    'drag_drop' => true,
                    'show_progress' => true,
                    'auto_upload' => true,
                    'delete_enabled' => true,
                    'custom_messages' => [
                        'title' => '명함 디자인 파일 업로드',
                        'drop_text' => '명함 디자인 파일을 여기로 드래그하거나 클릭하여 선택하세요',
                        'format_text' => '지원 형식: JPG, PNG, PDF (최대 5MB)'
                    ]
                ]);
                
                // 컴포넌트 렌더링
                echo $uploadComponent->render();
                ?>
            </div>

            <!-- 기타사항 섹션 - 견적 계산 후에만 표시 -->
            <div id="commentSection" class="comment-section" style="display: none;">
                <h4>📝 기타사항</h4>
                <textarea name="comment" placeholder="추가 요청사항이나 문의사항을 입력해주세요..."></textarea>
            </div>

            <!-- 숨겨진 필드들 -->
            <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
            <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
            <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
            <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
            <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
            <input type="hidden" name="page" value="NameCard">
        </form>
    </div> <!-- main-content-wrapper 끝 -->   
     
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
        page: "NameCard"
    };

    // 이미지 갤러리 관련 변수들
    let galleryImages = [];
    let currentImageIndex = 0;
    
    // 갤러리 줌 기능 초기화 - 적응형 이미지 표시 및 확대
    let targetX = 50, targetY = 50;
    let currentX = 50, currentY = 50;
    let targetSize = 100, currentSize = 100;
    let currentImageDimensions = { width: 0, height: 0 };
    let currentImageType = 'large'; // 'small' 또는 'large'
    let originalBackgroundSize = 'contain'; // 원래 배경 크기 저장
    
    
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
        document.getElementById('selectedType').textContent = '-';
        document.getElementById('selectedPaper').textContent = '-';
        document.getElementById('selectedSide').textContent = '-';
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션과 파일 업로드, 기타사항 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
        document.getElementById('fileUploadSection').style.display = 'none';
        document.getElementById('commentSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.getElementById('namecardForm');
        
        const typeSelect = form.querySelector('select[name="MY_type"]');
        const paperSelect = form.querySelector('select[name="Section"]');
        const sideSelect = form.querySelector('select[name="POtype"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        if (typeSelect.selectedIndex > 0) {
            document.getElementById('selectedType').textContent = typeSelect.options[typeSelect.selectedIndex].text;
        }
        if (paperSelect.selectedIndex > 0) {
            document.getElementById('selectedPaper').textContent = paperSelect.options[paperSelect.selectedIndex].text;
        }
        if (sideSelect.selectedIndex > 0) {
            document.getElementById('selectedSide').textContent = sideSelect.options[sideSelect.selectedIndex].text;
        }
        if (quantitySelect.selectedIndex > 0) {
            document.getElementById('selectedQuantity').textContent = quantitySelect.options[quantitySelect.selectedIndex].text;
        }
        if (designSelect.selectedIndex > 0) {
            document.getElementById('selectedDesign').textContent = designSelect.options[designSelect.selectedIndex].text;
        }
    }
    
    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('namecardForm');
        const formData = new FormData(form);
        
        if (!formData.get('MY_type') || !formData.get('Section') || !formData.get('POtype') || !formData.get('MY_amount') || !formData.get('ordertype')) {
            alert('모든 옵션을 선택해주세요.');
            return;
        }
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        const params = new URLSearchParams(new FormData(form));
        
        fetch('calculate_price_ajax.php?' + params.toString())
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
                
                // 파일 업로드와 기타사항 섹션 표시
                document.getElementById('fileUploadSection').style.display = 'block';
                document.getElementById('commentSection').style.display = 'block';
                
                window.currentPriceData = priceData;
            } else {
                alert(response.message || '가격 계산 중 오류가 발생했습니다.');
                document.getElementById('priceSection').style.display = 'none';
                document.getElementById('fileUploadSection').style.display = 'none';
                document.getElementById('commentSection').style.display = 'none';
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
        
        const form = document.getElementById('namecardForm');
        const formData = new FormData(form);
        
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.total_price));
        formData.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        formData.set('product_type', 'namecard');

        // 공통 파일 업로드 컴포넌트에서 관리하는 파일 정보 추가
        if (window.uploadedFiles && window.uploadedFiles.length > 0) {
            const fileInfoArray = window.uploadedFiles.map(file => ({
                original_name: file.original_name,
                saved_name: file.saved_name,
                upload_path: file.upload_path,
                file_size: file.file_size,
                file_type: file.file_type
            }));
            formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
        } else {
            formData.set('uploaded_files_info', '[]');
        }
        
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
        
        const form = document.getElementById('namecardForm');
        const params = new URLSearchParams(new FormData(form));
        params.set('direct_order', '1');
        params.set('product_type', 'namecard');
        params.set('price', Math.round(window.currentPriceData.total_price));
        params.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        
        // 선택된 옵션 텍스트 전달 (통합 주문 페이지 필드명에 맞춤)
        params.set('type_text', form.querySelector('select[name="MY_type"]').options[form.querySelector('select[name="MY_type"]').selectedIndex].text);
        params.set('paper_text', form.querySelector('select[name="Section"]').options[form.querySelector('select[name="Section"]').selectedIndex].text);
        params.set('sides_text', form.querySelector('select[name="POtype"]').options[form.querySelector('select[name="POtype"]').selectedIndex].text);
        params.set('quantity_text', form.querySelector('select[name="MY_amount"]').options[form.querySelector('select[name="MY_amount"]').selectedIndex].text);
        params.set('design_text', form.querySelector('select[name="ordertype"]').options[form.querySelector('select[name="ordertype"]').selectedIndex].text);
        
        // 추가 필드들도 전달
        params.set('NC_type', form.querySelector('select[name="MY_type"]').value);
        params.set('NC_paper', form.querySelector('select[name="Section"]').value);
        params.set('NC_amount', form.querySelector('select[name="MY_amount"]').value);
        params.set('NC_sides', form.querySelector('select[name="POtype"]').value);
        params.set('NC_comment', form.querySelector('textarea[name="comment"]').value || '');
        
        // 디버그: 전송되는 데이터 확인
        console.log('명함 바로주문 데이터:', Object.fromEntries(params));
        
        window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 옵션 업데이트 함수
    function updateSelectWithOptions(selectElement, options, defaultOptionText) {
        selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
        if (options) {
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value || option.no;
                optionElement.textContent = option.text || option.title;
                selectElement.appendChild(optionElement);
            });
        }
    }

    // 숫자 포맷팅 함수
    function format_number(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // 페이지 로드 시 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // 모던 파일 업로드 초기화
        if (typeof initModernFileUpload === 'function') {
            initModernFileUpload();
        }

        const typeSelect = document.getElementById('MY_type');
        const paperSelect = document.getElementById('Section');
        const sideSelect = document.getElementById('POtype');
        const quantitySelect = document.getElementById('MY_amount');

        // 페이지 로드 시 기본값이 선택되어 있으면 자동으로 하위 옵션들 로드
        if (typeSelect.value) {
            loadPaperTypes(typeSelect.value);
        }

        // 명함 재질 로드 함수
        function loadPaperTypes(style) {
            if (!style) {
                console.log('재질 로드: style 파라미터 없음');
                return;
            }

            console.log('재질 로드 시작:', style);
            const url = `get_paper_types.php?style=${style}`;
            console.log('재질 AJAX 요청:', url);

            fetch(url)
                .then(response => {
                    console.log('재질 응답 상태:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('재질 응답 데이터:', data);
                    if (data.success) {
                        updateSelectWithOptions(paperSelect, data.data, '명함 재질을 선택해주세요');
                        console.log('재질 옵션 업데이트 완료:', data.data.length + '개');
                        
                        // 기본값이 있으면 선택하고 수량 로드
                        <?php if (!empty($default_values['Section'])): ?>
                        paperSelect.value = '<?php echo $default_values['Section']; ?>';
                        console.log('기본 재질 선택:', '<?php echo $default_values['Section']; ?>');
                        if (paperSelect.value && sideSelect.value) {
                            console.log('기본값 설정 후 수량 로드 호출');
                            loadQuantities();
                        }
                        <?php endif; ?>
                    } else {
                        console.error('재질 로드 실패:', data.message);
                        alert('재질 로드 실패: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('재질 로드 오류:', error);
                    alert('재질 로드 중 오류가 발생했습니다.');
                });
        }

        // 드롭다운 초기화 함수
        function resetSelect(selectElement, defaultText) {
            selectElement.innerHTML = `<option value="">${defaultText}</option>`;
        }

        // 1. 명함 종류 변경 시 -> 명함 재질 로드
        typeSelect.addEventListener('change', function() {
            const style = this.value;
            console.log('명함 종류 변경:', style);
            
            resetSelect(paperSelect, '재질을 선택해주세요');
            resetSelect(quantitySelect, '수량을 선택해주세요');
            resetSelectedOptions();

            if (!style) {
                console.log('명함 종류가 선택되지 않음');
                return;
            }

            const url = `get_paper_types.php?style=${style}`;
            console.log('재질 AJAX 요청 (종류 변경):', url);

            fetch(url)
                .then(response => {
                    console.log('재질 응답 상태 (종류 변경):', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('재질 응답 데이터 (종류 변경):', data);
                    if (data.success) {
                        updateSelectWithOptions(paperSelect, data.data, '명함 재질을 선택해주세요');
                        console.log('재질 옵션 업데이트 완료 (종류 변경):', data.data.length + '개');
                    } else {
                        console.error('재질 로드 실패 (종류 변경):', data.message);
                        alert('재질 로드 실패: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('재질 로드 오류 (종류 변경):', error);
                    alert('재질 로드 중 오류가 발생했습니다.');
                });
        });

        // 2. 명함 재질 또는 인쇄면 변경 시 -> 수량 로드
        function loadQuantities() {
            const style = typeSelect.value;
            const section = paperSelect.value;
            const potype = sideSelect.value;

            console.log('수량 로드 시작:', { style, section, potype });

            resetSelect(quantitySelect, '수량을 선택해주세요');
            resetSelectedOptions();

            if (!style || !section || !potype) {
                console.log('필수 파라미터 누락으로 수량 로드 중단');
                return;
            }

            const url = `get_quantities.php?style=${style}&section=${section}&potype=${potype}`;
            console.log('수량 AJAX 요청:', url);

            fetch(url)
                .then(response => {
                    console.log('수량 응답 상태:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('수량 응답 데이터:', data);
                    if (data.success) {
                        updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                        console.log('수량 옵션 업데이트 완료:', data.data.length + '개');
                        
                        // 기본값이 있으면 선택
                        <?php if (!empty($default_values['MY_amount'])): ?>
                        quantitySelect.value = '<?php echo $default_values['MY_amount']; ?>';
                        console.log('기본 수량 선택:', '<?php echo $default_values['MY_amount']; ?>');
                        <?php endif; ?>
                    } else {
                        console.error('수량 로드 실패:', data.message);
                        alert('수량 로드 실패: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('수량 로드 오류:', error);
                    alert('수량 로드 중 오류가 발생했습니다.');
                });
        }

        paperSelect.addEventListener('change', loadQuantities);
        sideSelect.addEventListener('change', loadQuantities);
        
        // 수량이나 편집방식 변경 시 가격 초기화
        quantitySelect.addEventListener('change', resetSelectedOptions);
        document.getElementById('ordertype').addEventListener('change', resetSelectedOptions);
        
        // 이미지 갤러리 초기화
        loadImageGallery();
        initGalleryZoom();
        animate();
    });
    
    // === 이미지 갤러리 함수들 ===
    
    // 이미지 갤러리 로드
    function loadImageGallery() {
        const loadingElement = document.getElementById('galleryLoading');
        const errorElement = document.getElementById('galleryError');
        
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        fetch('get_namecard_images.php')
        .then(response => response.json())
        .then(response => {
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            if (response.success && response.data.length > 0) {
                galleryImages = response.data;
                createThumbnails();
                console.log('명함 갤러리 로드 완료:', response.count + '개 이미지');
            } else {
                showGalleryError('명함 샘플 이미지가 없습니다.');
            }
        })
        .catch(error => {
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            showGalleryError('이미지를 불러오는 중 오류가 발생했습니다: ' + error.message);
        });
    }
    
    // 갤러리 오류 표시
    function showGalleryError(message) {
        const errorElement = document.getElementById('galleryError');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }
    
    // 갤러리 줌 기능 초기화
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
    
    // 부드러운 애니메이션 루프
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
    
    // 메인 이미지 업데이트 함수
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
            
            // 썸네일 클릭 이벤트
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
        
        console.log('썸네일 생성 완료:', galleryImages.length + '개');
    }
    
    // 썸네일 active 상태 업데이트
    function updateThumbnailActive(activeIndex) {
        const thumbnails = document.querySelectorAll('#thumbnailGrid img');
        thumbnails.forEach((thumb, index) => {
            if (index === activeIndex) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }
    </script>

<?php
// 데이터베이스 연결 종료
if ($db) {
    mysqli_close($db);
}
?>
</body>
</html>
