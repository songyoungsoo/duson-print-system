<?php
/**
 * 통합 품목 관리 시스템
 * 모든 품목의 가격 정보를 하나의 인터페이스에서 관리
 */
session_start();
require_once dirname(dirname(__DIR__)) . '/duson/config/database.php';

// 관리자 권한 확인 (기존 admin 시스템과 연동)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('location: ../mlangprintauto/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>통합 품목 관리 시스템 - 두손기획인쇄</title>
    <link rel="stylesheet" href="css/admin-manager.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- 헤더 -->
        <header class="admin-header">
            <div class="header-content">
                <h1><i class="fas fa-cogs"></i> 통합 품목 관리 시스템</h1>
                <div class="header-actions">
                    <span class="user-info">관리자: <?= $_SESSION['admin_name'] ?? 'Admin' ?></span>
                    <button onclick="window.location.href='../mlangprintauto/index.php'" class="btn btn-secondary">
                        <i class="fas fa-home"></i> 메인 관리자
                    </button>
                </div>
            </div>
        </header>

        <!-- 필터 패널 -->
        <div class="filter-panel">
            <div class="filter-section">
                <h3><i class="fas fa-filter"></i> 검색 및 필터</h3>
                <div class="filter-row">
                    <!-- 품목 선택 -->
                    <div class="filter-group">
                        <label for="product-filter">품목</label>
                        <select id="product-filter" onchange="handleProductChange()">
                            <option value="">전체 품목</option>
                            <option value="namecard">명함</option>
                            <option value="flyer">전단지</option>
                            <option value="cadarok">카다록</option>
                            <option value="envelope">봉투</option>
                            <option value="littleprint">포스터</option>
                            <option value="merchandisebond">상품권/쿠폰</option>
                            <option value="msticker">자석스티커</option>
                            <option value="ncrflambeau">NCR양식</option>
                        </select>
                    </div>

                    <!-- 종류/색상 선택 -->
                    <div class="filter-group">
                        <label for="style-filter">종류/색상</label>
                        <select id="style-filter" onchange="handleStyleChange()" disabled>
                            <option value="">종류 선택</option>
                        </select>
                    </div>

                    <!-- 재질/규격 선택 -->
                    <div class="filter-group">
                        <label for="section-filter">재질/규격</label>
                        <select id="section-filter" onchange="handleSectionChange()" disabled>
                            <option value="">재질 선택</option>
                        </select>
                    </div>

                    <!-- 통합 검색 -->
                    <div class="filter-group search-group">
                        <label for="search-input">통합 검색</label>
                        <div class="search-box">
                            <input type="text" id="search-input" placeholder="가격, 수량, 품목명 등..." onkeyup="handleSearch(event)">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>

                    <!-- 액션 버튼 -->
                    <div class="filter-group action-group">
                        <button id="add-new-btn" onclick="showAddModal()" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 새 가격 추가
                        </button>
                        <button id="bulk-delete-btn" onclick="bulkDelete()" class="btn btn-danger" disabled>
                            <i class="fas fa-trash"></i> 선택 삭제
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 데이터 테이블 -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-info">
                    <span id="table-info">전체 데이터를 불러오는 중...</span>
                </div>
                <div class="table-controls">
                    <label for="page-size">페이지당:</label>
                    <select id="page-size" onchange="changePageSize()">
                        <option value="20">20개</option>
                        <option value="50">50개</option>
                        <option value="100">100개</option>
                    </select>
                </div>
            </div>

            <div class="table-wrapper">
                <table id="price-data-table" class="admin-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all" onchange="toggleSelectAll()"></th>
                            <th class="sortable" data-sort="id">ID <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="product_code">품목 <i class="fas fa-sort"></i></th>
                            <th>종류/색상</th>
                            <th>재질/규격</th>
                            <th class="sortable" data-sort="quantity">수량 <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="base_price">기본가격 <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="design_price">디자인가격 <i class="fas fa-sort"></i></th>
                            <th>인쇄면</th>
                            <th>트리선택</th>
                            <th>수량2</th>
                            <th>상태</th>
                            <th width="120">작업</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <tr>
                            <td colspan="13" class="loading-cell">
                                <i class="fas fa-spinner fa-spin"></i> 데이터를 불러오는 중...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 페이지네이션 -->
        <div class="pagination-container">
            <div class="pagination-info">
                <span id="pagination-info">페이지 정보를 불러오는 중...</span>
            </div>
            <div class="pagination" id="pagination">
                <!-- 동적 생성 -->
            </div>
        </div>
    </div>

    <!-- 추가/수정 모달 -->
    <div id="edit-modal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title"><i class="fas fa-edit"></i> 가격 정보 추가</h3>
                <button onclick="closeModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="price-form" onsubmit="submitForm(event)">
                    <input type="hidden" id="form-id" name="id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="form-product">품목 *</label>
                            <select id="form-product" name="product_code" required onchange="handleFormProductChange()">
                                <option value="">품목 선택</option>
                                <option value="namecard">명함</option>
                                <option value="flyer">전단지</option>
                                <option value="cadarok">카다록</option>
                                <option value="envelope">봉투</option>
                                <option value="littleprint">포스터</option>
                                <option value="merchandisebond">상품권/쿠폰</option>
                                <option value="msticker">자석스티커</option>
                                <option value="ncrflambeau">NCR양식</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="form-style">종류/색상 *</label>
                            <select id="form-style" name="style_code" required disabled>
                                <option value="">종류 선택</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="form-section">재질/규격 *</label>
                            <select id="form-section" name="section_code" required disabled>
                                <option value="">재질 선택</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="form-quantity">수량 *</label>
                            <input type="number" id="form-quantity" name="quantity" step="0.01" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="form-base-price">기본가격 *</label>
                            <input type="number" id="form-base-price" name="base_price" required>
                        </div>
                        <div class="form-group">
                            <label for="form-design-price">디자인가격</label>
                            <input type="number" id="form-design-price" name="design_price" value="0">
                        </div>
                    </div>

                    <!-- 선택적 필드들 (품목별 동적 표시) -->
                    <div class="form-row" id="optional-fields">
                        <div class="form-group" id="print-type-group">
                            <label for="form-print-type">인쇄면</label>
                            <select id="form-print-type" name="print_type">
                                <option value="1">단면</option>
                                <option value="2">양면</option>
                            </select>
                        </div>
                        <div class="form-group" id="tree-select-group" style="display: none;">
                            <label for="form-tree-select">트리선택</label>
                            <input type="text" id="form-tree-select" name="tree_select">
                        </div>
                        <div class="form-group" id="quantity-two-group" style="display: none;">
                            <label for="form-quantity-two">수량2</label>
                            <input type="number" id="form-quantity-two" name="quantity_two" step="0.01">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="closeModal()" class="btn btn-secondary">취소</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 알림 토스트 -->
    <div id="toast-container"></div>

    <!-- 스크립트 로드 -->
    <script src="js/product-config.js"></script>
    <script src="js/product-manager.js"></script>
    <script src="js/inline-edit.js"></script>
    <script src="js/modal-handler.js"></script>
</body>
</html>