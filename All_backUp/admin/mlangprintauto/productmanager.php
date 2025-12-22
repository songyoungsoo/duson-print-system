<?php
/**
 * 품목 관리 시스템 - 메인 페이지
 * 두손기획인쇄 - 8개 품목 통합 CRUD 관리
 */

// DB 연결
require_once '../../db.php';
require_once 'includes/product_config.php';

// DB 연결 확인
if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

// 전체 품목 목록 가져오기
$products = ProductConfig::getAllProducts();

include "../title.php";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>품목 관리 시스템 - 두손기획인쇄</title>
    <link rel="stylesheet" href="css/product-manager.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        * {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #3498db;
        }
        .loading.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="product-manager-container">
        <!-- 1. 헤더 -->
        <div class="page-header">
            <h1>📦 품목 관리 시스템</h1>
            <p>8개 품목의 가격표를 통합 관리합니다</p>
        </div>

        <!-- 2. 품목 선택 섹션 -->
        <div class="product-selector-section">
            <h2>품목 선택</h2>
            <div class="product-buttons" id="productButtons">
                <?php foreach ($products as $product): ?>
                    <button
                        class="product-btn"
                        data-product="<?php echo htmlspecialchars($product['key']); ?>"
                        data-selectors="<?php echo $product['selectors']; ?>"
                        onclick="selectProduct('<?php echo htmlspecialchars($product['key']); ?>')">
                        <?php echo htmlspecialchars($product['name']); ?>
                        <small>(<?php echo $product['selectors']; ?>단계)</small>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. 필터링 섹션 -->
        <div class="filter-section" id="filterSection" style="display:none;">
            <h2>🔍 검색 필터</h2>
            <div class="filter-controls">
                <div class="filter-selectors" id="filterSelectors">
                    <!-- 동적 생성 -->
                </div>
                <div class="filter-buttons">
                    <button class="btn btn-primary" onclick="loadPriceTable()">
                        조회
                    </button>
                    <button class="btn btn-success" onclick="openCreateModal()">
                        ➕ 새 가격 추가
                    </button>
                    <button class="btn btn-secondary" onclick="resetFilters()">
                        초기화
                    </button>
                </div>
            </div>
        </div>

        <!-- 로딩 표시 -->
        <div class="loading" id="loading">
            <p>⏳ 데이터를 불러오는 중...</p>
        </div>

        <!-- 4. 가격표 그리드 -->
        <div class="price-table-section" id="priceTableSection" style="display:none;">
            <div class="table-header">
                <h2 id="tableTitle">가격표</h2>
                <div class="table-info" id="tableInfo">
                    총 <strong id="totalCount">0</strong>개 항목
                </div>
            </div>

            <div class="table-wrapper">
                <table class="price-table" id="priceTable">
                    <thead id="tableHead">
                        <!-- 동적 생성 -->
                    </thead>
                    <tbody id="tableBody">
                        <!-- 동적 생성 -->
                    </tbody>
                </table>
            </div>

            <!-- 페이지네이션 -->
            <div class="pagination-wrapper" id="paginationWrapper" style="display:none;">
                <div class="pagination" id="pagination">
                    <!-- 동적 생성 -->
                </div>
            </div>
        </div>
    </div>

    <!-- 5. CRUD 모달 -->
    <div id="crudModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">가격 추가</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="crudForm">
                    <input type="hidden" id="formAction" value="create">
                    <input type="hidden" id="formId" value="0">

                    <div class="form-group" id="formSelector1Group">
                        <label id="formSelector1Label">종류</label>
                        <select id="formSelector1" class="form-control" required>
                            <option value="">선택하세요</option>
                        </select>
                    </div>

                    <div class="form-group" id="formSelector2Group">
                        <label id="formSelector2Label">재질</label>
                        <select id="formSelector2" class="form-control" required>
                            <option value="">먼저 1단계를 선택하세요</option>
                        </select>
                    </div>

                    <div class="form-group" id="formSelector3Group" style="display:none;">
                        <label id="formSelector3Label">인쇄도수</label>
                        <select id="formSelector3" class="form-control">
                            <option value="">먼저 2단계를 선택하세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>수량</label>
                        <input type="text" id="formQuantity" class="form-control" placeholder="예: 500" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>금액</label>
                            <input type="number" id="formPriceSingle" class="form-control" placeholder="0" required>
                        </div>
                        <div class="form-group">
                            <label>편집비</label>
                            <input type="number" id="formPriceDouble" class="form-control" placeholder="0" required>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">저장</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">취소</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/product-manager.js?v=<?php echo time(); ?>"></script>
</body>
</html>
<?php mysqli_close($db); ?>
