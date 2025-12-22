<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>채팅 시스템 데모 - 두손기획인쇄</title>
    <link rel="stylesheet" href="chat.css">
    <style>
        body {
            font-family: 'Malgun Gothic', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .feature-card {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .feature-card h3 {
            margin-top: 0;
            color: #667eea;
        }
        .integration-code {
            background: #f4f4f4;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border-left: 4px solid #764ba2;
        }
        .integration-code h3 {
            margin-top: 0;
            color: #764ba2;
        }
        .integration-code pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .integration-code code {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 14px;
        }
        .notice {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <h1>🎉 채팅 시스템이 완성되었습니다!</h1>
        <p class="subtitle">우측 하단의 채팅 버튼을 클릭하여 테스트해보세요.</p>

        <div class="feature-grid">
            <div class="feature-card">
                <h3>💬 실시간 채팅</h3>
                <p>고객과 직원이 실시간으로 대화할 수 있습니다. 2초마다 자동으로 새 메시지를 확인합니다.</p>
            </div>

            <div class="feature-card">
                <h3>📸 이미지 전송</h3>
                <p>텍스트뿐만 아니라 이미지도 주고받을 수 있습니다. 최대 5MB까지 지원합니다.</p>
            </div>

            <div class="feature-card">
                <h3>👥 다중 직원 지원</h3>
                <p>직원 3명이 모두 같은 채팅방에서 고객과 대화할 수 있습니다.</p>
            </div>

            <div class="feature-card">
                <h3>💾 대화 저장</h3>
                <p>대화 내용을 텍스트 파일로 다운로드하여 보관할 수 있습니다.</p>
            </div>

            <div class="feature-card">
                <h3>🔔 읽지 않은 메시지</h3>
                <p>새 메시지가 도착하면 빨간색 뱃지로 알려줍니다.</p>
            </div>

            <div class="feature-card">
                <h3>📱 반응형 디자인</h3>
                <p>PC, 태블릿, 모바일 모든 기기에서 완벽하게 작동합니다.</p>
            </div>
        </div>

        <div class="integration-code">
            <h3>🔧 사이트에 적용하는 방법</h3>
            <p>기존 페이지의 &lt;/body&gt; 태그 직전에 다음 코드를 추가하세요:</p>
            <pre><code>&lt;!-- 채팅 시스템 --&gt;
&lt;link rel="stylesheet" href="/chat/chat.css"&gt;
&lt;script src="/chat/chat.js"&gt;&lt;/script&gt;</code></pre>
        </div>

        <div class="notice">
            <h4>📋 직원용 채팅 관리 페이지</h4>
            <p>직원들이 고객 채팅을 확인하고 응답하려면 별도의 관리 페이지가 필요합니다.</p>
            <p><strong>다음 단계:</strong> 직원용 채팅 관리 대시보드를 만들 수 있습니다.</p>
        </div>

        <div class="notice" style="margin-top: 15px; background: #fff3cd; border-left-color: #ffc107;">
            <h4>🎨 커스터마이징</h4>
            <p><strong>chat.css</strong> 파일에서 색상, 크기, 위치 등을 자유롭게 변경할 수 있습니다.</p>
            <ul>
                <li>채팅 버튼 위치: <code>.chat-widget { bottom: 20px; right: 20px; }</code></li>
                <li>메인 색상: <code>background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);</code></li>
                <li>채팅창 크기: <code>.chat-window { width: 380px; height: 550px; }</code></li>
            </ul>
        </div>
    </div>

    <!-- 채팅 시스템 -->
    <link rel="stylesheet" href="chat.css">
    <script src="chat.js"></script>
</body>
</html>
