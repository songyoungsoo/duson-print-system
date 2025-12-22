<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin.php 버전 비교 테스트</title>
    <style>
        body {
            font-family: 'Noto Sans KR', Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .version-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px;
            background: #e9ecef;
        }
        .version-section {
            background: white;
            padding: 20px;
            min-height: 500px;
        }
        .version-title {
            background: #2c3e50;
            color: white;
            padding: 10px 15px;
            margin: -20px -20px 20px -20px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .test-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        .test-btn:hover {
            background: #2980b9;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .status.ok { background: #d4edda; color: #155724; }
        .status.missing { background: #f8d7da; color: #721c24; }
        .status.enhanced { background: #d1ecf1; color: #0c5460; }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .comparison-table th, .comparison-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .comparison-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .action-buttons {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .btn {
            padding: 12px 24px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 Admin.php 버전 비교 및 테스트</h1>
            <p>현재 개발중인 버전 vs admin_7.4 완성 버전</p>
        </div>

        <div class="version-grid">
            <div class="version-section">
                <div class="version-title">
                    <span>📝 현재 개발 버전 (Current)</span>
                    <a href="admin_current_original.php?mode=OrderView&no=1" target="_blank" class="test-btn">테스트하기</a>
                </div>
                <div class="file-info">
                    <strong>파일:</strong> admin_current_original.php<br>
                    <strong>크기:</strong> 23.5KB<br>
                    <strong>상태:</strong> <span class="status missing">구문 오류 있음</span>
                </div>
                <ul class="feature-list">
                    <li>기본 주문정보 보기 <span class="status missing">오류</span></li>
                    <li>파일 업로드 <span class="status missing">오류</span></li>
                    <li>교정/시안 관리 <span class="status missing">오류</span></li>
                    <li>품목별 추가 옵션 <span class="status missing">없음</span></li>
                    <li>코팅/접지/오시 옵션 <span class="status missing">없음</span></li>
                    <li>박/넘버링/미싱 옵션 <span class="status missing">없음</span></li>
                    <li>반응형 UI <span class="status missing">없음</span></li>
                    <li>보안 강화 <span class="status missing">없음</span></li>
                </ul>
            </div>

            <div class="version-section">
                <div class="version-title">
                    <span>✨ Admin_7.4 완성 버전</span>
                    <a href="admin_74_test.php?mode=OrderView&no=1" target="_blank" class="test-btn">테스트하기</a>
                </div>
                <div class="file-info">
                    <strong>파일:</strong> admin_74_test.php<br>
                    <strong>크기:</strong> 47.8KB<br>
                    <strong>상태:</strong> <span class="status ok">완전 동작</span>
                </div>
                <ul class="feature-list">
                    <li>기본 주문정보 보기 <span class="status ok">완전구현</span></li>
                    <li>파일 업로드 <span class="status enhanced">고급구현</span></li>
                    <li>교정/시안 관리 <span class="status enhanced">고급구현</span></li>
                    <li>품목별 추가 옵션 <span class="status enhanced">완전구현</span></li>
                    <li>코팅/접지/오시 옵션 <span class="status enhanced">완전구현</span></li>
                    <li>박/넘버링/미싱 옵션 <span class="status enhanced">완전구현</span></li>
                    <li>반응형 UI <span class="status enhanced">완전구현</span></li>
                    <li>보안 강화 <span class="status enhanced">완전구현</span></li>
                </ul>
            </div>
        </div>

        <table class="comparison-table">
            <tr>
                <th>기능</th>
                <th>현재 버전</th>
                <th>Admin_7.4</th>
                <th>개선사항</th>
            </tr>
            <tr>
                <td><strong>주문정보 보기</strong></td>
                <td>❌ 파싱 오류</td>
                <td>✅ 완전 동작</td>
                <td>구문 오류 수정, 모던 UI</td>
            </tr>
            <tr>
                <td><strong>추가 옵션 표시</strong></td>
                <td>❌ 없음</td>
                <td>✅ 완전 구현</td>
                <td>코팅, 접지, 오시, 박, 넘버링 등 모든 옵션</td>
            </tr>
            <tr>
                <td><strong>파일 업로드</strong></td>
                <td>❌ 기능 오류</td>
                <td>✅ 고급 구현</td>
                <td>보안 검증, 크기 제한, 파일명 정리</td>
            </tr>
            <tr>
                <td><strong>UI/UX</strong></td>
                <td>❌ 레거시</td>
                <td>✅ 모던</td>
                <td>반응형 디자인, 카드 레이아웃</td>
            </tr>
            <tr>
                <td><strong>보안</strong></td>
                <td>❌ 취약</td>
                <td>✅ 강화</td>
                <td>XSS 방지, SQL Injection 방지</td>
            </tr>
            <tr>
                <td><strong>PHP 7.4 호환성</strong></td>
                <td>❌ 비호환</td>
                <td>✅ 완전 호환</td>
                <td>Prepared statements, 현대적 문법</td>
            </tr>
        </table>

        <div class="action-buttons">
            <h3>🧪 테스트 액션</h3>
            <p>각 버전을 충분히 테스트한 후 교체를 결정하세요.</p>

            <a href="admin_current_original.php?mode=OrderView&no=1" target="_blank" class="btn btn-warning">
                현재 버전 테스트
            </a>
            <a href="admin_74_test.php?mode=OrderView&no=1" target="_blank" class="btn btn-primary">
                Admin_7.4 테스트
            </a>
            <a href="javascript:void(0)" onclick="replaceVersion()" class="btn btn-success">
                ✅ 교체 실행
            </a>
            <a href="javascript:void(0)" onclick="rollback()" class="btn btn-danger">
                🔄 롤백
            </a>
        </div>
    </div>

    <script>
        function replaceVersion() {
            if (confirm('admin_7.4 버전으로 교체하시겠습니까?\\n\\n⚠️ 현재 버전은 admin_rollback.php로 백업됩니다.')) {
                fetch('version_controller.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'replace' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ 성공적으로 교체되었습니다!');
                        location.reload();
                    } else {
                        alert('❌ 교체 실패: ' + data.message);
                    }
                });
            }
        }

        function rollback() {
            if (confirm('이전 버전으로 롤백하시겠습니까?')) {
                fetch('version_controller.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'rollback' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ 롤백 완료!');
                        location.reload();
                    } else {
                        alert('❌ 롤백 실패: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>