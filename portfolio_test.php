<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>포트폴리오 게시판 테스트</title>
    <style>
        body {
            font-family: 'Noto Sans KR', Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .test-title {
            text-align: center;
            color: #333;
            border-bottom: 2px solid #4a6da7;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .test-link {
            display: block;
            padding: 15px 20px;
            margin: 10px 0;
            background-color: #4a6da7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s;
        }
        
        .test-link:hover {
            background-color: #3a5a8c;
        }
        
        .setup-link {
            background-color: #28a745;
        }
        
        .setup-link:hover {
            background-color: #218838;
        }
        
        .info-box {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #1976d2;
        }
        
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="test-title">📁 포트폴리오 게시판 테스트 페이지</h1>
        
        <div class="info-box">
            <strong>🎯 구현된 기능</strong><br>
            ✅ 현대적인 글쓰기 폼 인터페이스<br>
            ✅ 이미지 업로드 및 미리보기<br>
            ✅ 카테고리 분류 시스템<br>
            ✅ 보안이 강화된 파일 업로드<br>
            ✅ 반응형 디자인<br>
            ✅ 유효성 검사 및 보안 코드<br>
        </div>
        
        <div class="warning-box">
            <strong>⚠️ 주의사항</strong><br>
            • 먼저 <strong>'게시판 초기 설정'</strong>을 실행해주세요<br>
            • XAMPP가 실행중이고 MySQL이 작동하는지 확인해주세요<br>
            • 포트폴리오 게시판 테이블이 생성되어야 합니다<br>
        </div>
        
        <h3>🛠️ 설정 및 테스트</h3>
        
        <a href="bbs/portfolio_setup.php" class="test-link setup-link">
            1️⃣ 게시판 초기 설정 (필수 - 먼저 실행)
        </a>
        
        <a href="bbs/bbs.php?table=portfolio&mode=list" class="test-link">
            2️⃣ 포트폴리오 목록 보기
        </a>
        
        <a href="bbs/bbs.php?table=portfolio&mode=write" class="test-link">
            3️⃣ 새 포트폴리오 작성
        </a>
        
        <h3>📋 테스트 시나리오</h3>
        
        <div style="background: white; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
            <ol>
                <li><strong>초기 설정:</strong> "게시판 초기 설정" 링크 클릭 → 데이터베이스 테이블 생성</li>
                <li><strong>글쓰기 테스트:</strong> "새 포트폴리오 작성" → 모든 필드 입력 → 이미지 업로드 → 등록</li>
                <li><strong>목록 확인:</strong> "포트폴리오 목록 보기" → 등록한 글이 갤러리 형태로 표시되는지 확인</li>
                <li><strong>카테고리 테스트:</strong> 다양한 카테고리로 여러 게시글 등록 → 카테고리별 필터링 확인</li>
                <li><strong>수정/삭제 테스트:</strong> 등록한 게시글의 수정/삭제 기능 확인</li>
            </ol>
        </div>
        
        <h3>🔧 파일 구조</h3>
        <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;">
            <strong>구현된 파일들:</strong><br>
            📁 /bbs/skin/portfolio/<br>
            ├── 📄 list.php (갤러리 목록 - 개선됨)<br>
            ├── 📄 write.php (글쓰기 폼 - 완전히 새로 구현)<br>
            └── 📄 view.php (기존)<br><br>
            
            📁 /bbs/<br>
            ├── 📄 upload_secure.php (보안 강화된 업로드 - 새로 생성)<br>
            ├── 📄 portfolio_setup.php (설정 스크립트 - 새로 생성)<br>
            └── 📄 bbs.php (기존 - 수정 없음)<br><br>
            
            📁 /bbs/upload/portfolio/ (업로드 디렉토리 - 자동 생성)
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 12px;">
            <p>🤖 두손기획인쇄 포트폴리오 게시판 시스템 v1.0</p>
            <p>개발: Claude AI Assistant | 기존 시스템과 완전 호환</p>
        </div>
    </div>
</body>
</html>