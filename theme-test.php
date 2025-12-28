<?php
/**
 * 테마 테스트 페이지
 *
 * 사용법:
 * - http://localhost/theme-test.php (기본 테마)
 * - http://localhost/theme-test.php?theme=excel (Excel 테마)
 * - http://localhost/theme-test.php?theme=print (인쇄업 테마)
 */

include_once __DIR__ . '/includes/theme_loader.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>테마 테스트 - 두손기획인쇄</title>

    <!-- 기본 스타일 -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Malgun Gothic', sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        .card h3 {
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        .btn-primary {
            background: #007bff;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-primary {
            background: #007bff;
            color: white;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .price-display {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .theme-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>

    <?php ThemeLoader::renderCSS(); ?>
</head>
<body <?php ThemeLoader::renderBodyAttributes(); ?>>
    <div class="container">
        <!-- 헤더 -->
        <div class="header">
            <h1>🎨 테마 시스템 테스트</h1>
            <div class="theme-info">
                <strong>현재 테마:</strong>
                <?php
                $theme = ThemeLoader::getTheme();
                $themeNames = [
                    'default' => '기본 테마',
                    'ms' => 'MS PRINTING 스타일'
                ];
                echo $themeNames[$theme] ?? '기본 테마';
                ?>
            </div>
        </div>

        <!-- 알림 -->
        <div class="alert alert-info">
            <strong>테마 전환:</strong> 우측 하단의 테마 스위처를 클릭하여 다른 테마로 전환할 수 있습니다.
        </div>

        <!-- 버튼 테스트 -->
        <div class="card">
            <h3>버튼 스타일</h3>
            <div>
                <button class="btn btn-primary">주문하기</button>
                <button class="btn btn-success">장바구니</button>
                <button class="btn btn-danger">삭제</button>
                <button class="btn btn-warning">수정</button>
            </div>
        </div>

        <!-- 콘텐츠 그리드 -->
        <div class="content-grid">
            <!-- 가격 표시 -->
            <div class="card">
                <h3>가격 정보</h3>
                <div class="price-display">
                    <span class="price-label">총 금액:</span>
                    <span class="price-value">125,000원</span>
                </div>
                <div class="price-display" style="font-size: 18px; color: #666;">
                    <span class="price-label">부가세:</span>
                    <span class="price-value">12,500원</span>
                </div>
            </div>

            <!-- 배지 -->
            <div class="card">
                <h3>상태 배지</h3>
                <div>
                    <span class="badge badge-primary">진행중</span>
                    <span class="badge badge-success">완료</span>
                </div>
            </div>

            <!-- 폼 -->
            <div class="card">
                <h3>입력 폼</h3>
                <div class="form-group">
                    <label>제품명</label>
                    <input type="text" placeholder="제품명을 입력하세요" value="전단지">
                </div>
                <div class="form-group">
                    <label>수량</label>
                    <select>
                        <option>100매</option>
                        <option>200매</option>
                        <option>500매</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 테이블 -->
        <div class="card">
            <h3>주문 내역 테이블</h3>
            <table>
                <thead>
                    <tr>
                        <th>주문번호</th>
                        <th>제품명</th>
                        <th>수량</th>
                        <th>단가</th>
                        <th>총액</th>
                        <th>상태</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>ORD-2025001</td>
                        <td>전단지 (A4, 양면)</td>
                        <td>500매</td>
                        <td>200원</td>
                        <td>100,000원</td>
                        <td><span class="badge badge-success">완료</span></td>
                    </tr>
                    <tr>
                        <td>ORD-2025002</td>
                        <td>명함 (90x50mm)</td>
                        <td>200매</td>
                        <td>50원</td>
                        <td>10,000원</td>
                        <td><span class="badge badge-primary">진행중</span></td>
                    </tr>
                    <tr>
                        <td>ORD-2025003</td>
                        <td>포스터 (A1)</td>
                        <td>10매</td>
                        <td>5,000원</td>
                        <td>50,000원</td>
                        <td><span class="badge badge-primary">진행중</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 테마 링크 -->
        <div class="card">
            <h3>테마 빠른 전환</h3>
            <p>URL 파라미터로 직접 전환:</p>
            <div style="margin-top: 10px;">
                <a href="?theme=default" class="btn btn-primary">🎨 기본 테마</a>
                <a href="?theme=ms" class="btn btn-success">💼 MS PRINTING 스타일</a>
            </div>
        </div>
    </div>

    <!-- 테마 스위처 렌더링 -->
    <?php ThemeLoader::renderSwitcher('bottom-right'); ?>

    <!-- JavaScript 헬퍼 -->
    <?php ThemeLoader::renderSwitcherJS(); ?>
</body>
</html>
