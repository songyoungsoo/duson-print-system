<?php
/**
 * SuperClaude Framework Quick Start Demo
 * 실제 사용 예시와 데모를 제공합니다
 */

require_once 'includes/superclaud_framework.php';
$framework = $GLOBALS['superclaud'] ?? null;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperClaude Quick Start - 두손기획인쇄</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.2rem;
        }
        
        .demo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .demo-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .demo-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .demo-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .command-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .result-box {
            background: #e8f5e8;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            border: 1px solid #28a745;
        }
        
        .nav-links {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .nav-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .nav-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .nav-btn {
            background: white;
            color: #333;
            border: 2px solid #e0e0e0;
            padding: 15px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .nav-btn:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
        }
        
        .loading {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 class="title">
                <i class="fas fa-rocket"></i> SuperClaude Quick Start
            </h1>
            <p class="subtitle">실제 사용 예시와 데모 - 클릭 한 번으로 SuperClaude 체험하기!</p>
        </div>

        <!-- Demo Cards -->
        <div class="demo-grid">
            <!-- 시스템 상태 확인 -->
            <div class="demo-card">
                <h3 class="demo-title">
                    <i class="fas fa-heartbeat" style="color: #28a745;"></i>
                    시스템 상태 확인
                </h3>
                <p class="demo-description">
                    SuperClaude Framework의 전체적인 건강 상태를 확인합니다. 
                    데이터베이스, 메모리, CPU, 디스크 상태를 실시간으로 점검합니다.
                </p>
                <div class="command-box">
                    /sc:system-health
                </div>
                <button class="btn" onclick="executeDemo('system-health', this)">
                    <i class="fas fa-play"></i> 시스템 상태 확인 실행
                </button>
                <div class="result-box" id="result-system-health"></div>
            </div>

            <!-- 재고 현황 조회 -->
            <div class="demo-card">
                <h3 class="demo-title">
                    <i class="fas fa-boxes" style="color: #ffc107;"></i>
                    재고 현황 조회
                </h3>
                <p class="demo-description">
                    용지, 잉크, 소모품 등의 현재 재고 상황을 확인하고, 
                    임계 재고 알림을 받을 수 있습니다.
                </p>
                <div class="command-box">
                    /sc:inventory-status
                </div>
                <button class="btn" onclick="executeDemo('inventory-status', this)">
                    <i class="fas fa-search"></i> 재고 현황 확인 실행
                </button>
                <div class="result-box" id="result-inventory-status"></div>
            </div>

            <!-- 생산 현황 조회 -->
            <div class="demo-card">
                <h3 class="demo-title">
                    <i class="fas fa-industry" style="color: #17a2b8;"></i>
                    생산 현황 조회
                </h3>
                <p class="demo-description">
                    현재 진행 중인 생산 작업과 완료된 작업들의 상태를 
                    실시간으로 모니터링합니다.
                </p>
                <div class="command-box">
                    /sc:production-status
                </div>
                <button class="btn" onclick="executeDemo('production-status', this)">
                    <i class="fas fa-chart-line"></i> 생산 현황 확인 실행
                </button>
                <div class="result-box" id="result-production-status"></div>
            </div>

            <!-- 일일 보고서 생성 -->
            <div class="demo-card">
                <h3 class="demo-title">
                    <i class="fas fa-chart-bar" style="color: #6f42c1;"></i>
                    일일 보고서 생성
                </h3>
                <p class="demo-description">
                    오늘 하루의 주문 처리 현황, 매출, 완료율 등을 
                    종합한 일일 보고서를 자동으로 생성합니다.
                </p>
                <div class="command-box">
                    /sc:report-daily
                </div>
                <button class="btn" onclick="executeDemo('report-daily', this)">
                    <i class="fas fa-file-alt"></i> 일일 보고서 생성 실행
                </button>
                <div class="result-box" id="result-report-daily"></div>
            </div>

            <!-- 주문 생성 시뮬레이션 -->
            <div class="demo-card">
                <h3 class="demo-title">
                    <i class="fas fa-plus-circle" style="color: #e91e63;"></i>
                    주문 생성 데모
                </h3>
                <p class="demo-description">
                    새로운 명함 주문을 생성하는 과정을 시뮬레이션합니다. 
                    실제 주문은 생성되지 않으며 데모용입니다.
                </p>
                <div class="command-box">
                    /sc:order-create (데모 모드)
                </div>
                <button class="btn" onclick="executeDemo('order-demo', this)">
                    <i class="fas fa-shopping-cart"></i> 주문 생성 데모 실행
                </button>
                <div class="result-box" id="result-order-demo"></div>
            </div>

            <!-- Agent 상태 확인 -->
            <div class="demo-card">
                <h3 class="demo-title">
                    <i class="fas fa-robot" style="color: #fd7e14;"></i>
                    Agent 상태 확인
                </h3>
                <p class="demo-description">
                    14개의 전문 Agent들의 상태와 활동 내역을 확인합니다. 
                    각 Agent의 우선순위와 처리 상황을 볼 수 있습니다.
                </p>
                <div class="command-box">
                    Framework.getStats()
                </div>
                <button class="btn" onclick="executeDemo('agent-status', this)">
                    <i class="fas fa-cogs"></i> Agent 상태 확인 실행
                </button>
                <div class="result-box" id="result-agent-status"></div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav-links">
            <h3 class="nav-title">다음 단계로 이동하기</h3>
            <div class="nav-buttons">
                <a href="superclaud_test.php" class="nav-btn">
                    <i class="fas fa-check-circle"></i>
                    시스템 테스트
                </a>
                <a href="superclaud_dashboard.php" class="nav-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    관리 대시보드
                </a>
                <a href="superclaud_user_guide.php" class="nav-btn">
                    <i class="fas fa-book"></i>
                    사용자 가이드
                </a>
                <a href="api/superclaud_api.php" class="nav-btn" target="_blank">
                    <i class="fas fa-code"></i>
                    API 문서
                </a>
            </div>
        </div>
    </div>

    <script>
        async function executeDemo(demoType, button) {
            const resultBox = document.getElementById(`result-${demoType}`);
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner loading"></i> 실행 중...';
            button.disabled = true;
            resultBox.style.display = 'block';
            resultBox.innerHTML = '🔄 실행 중...';
            
            try {
                let command = '';
                let params = {};
                
                switch (demoType) {
                    case 'system-health':
                        // Special case for system health
                        const healthResponse = await fetch('/api/superclaud_api.php/health');
                        if (!healthResponse.ok) {
                            throw new Error(`HTTP error! status: ${healthResponse.status}`);
                        }
                        const healthData = await healthResponse.json();
                        displayResult(resultBox, healthData);
                        
                        // Restore button state
                        button.innerHTML = originalText;
                        button.disabled = false;
                        return;
                    case 'inventory-status':
                        // Special case for inventory status
                        const inventoryResponse = await fetch('/api/superclaud_api.php/inventory');
                        if (!inventoryResponse.ok) {
                            throw new Error(`HTTP error! status: ${inventoryResponse.status}`);
                        }
                        const inventoryData = await inventoryResponse.json();
                        displayResult(resultBox, inventoryData);
                        
                        // Restore button state
                        button.innerHTML = originalText;
                        button.disabled = false;
                        return;
                    case 'production-status':
                        // Special case for production status
                        const productionResponse = await fetch('/api/superclaud_api.php/production');
                        if (!productionResponse.ok) {
                            throw new Error(`HTTP error! status: ${productionResponse.status}`);
                        }
                        const productionData = await productionResponse.json();
                        displayResult(resultBox, productionData);
                        
                        // Restore button state
                        button.innerHTML = originalText;
                        button.disabled = false;
                        return;
                    case 'report-daily':
                        command = '/sc:report-daily';
                        break;
                    case 'order-demo':
                        command = '/sc:order-create';
                        params = {
                            demo: true,
                            product_type: 'namecard',
                            customer: {
                                name: '데모고객',
                                phone: '010-0000-0000'
                            },
                            specs: {
                                quantity: 1000,
                                paper_type: '프리미엄',
                                sides: 'double'
                            }
                        };
                        break;
                    case 'agent-status':
                        // Special case for agent status
                        const agentResponse = await fetch('/api/superclaud_api.php/');
                        if (!agentResponse.ok) {
                            throw new Error(`HTTP error! status: ${agentResponse.status}`);
                        }
                        const agentData = await agentResponse.json();
                        displayResult(resultBox, agentData);
                        
                        // Restore button state
                        button.innerHTML = originalText;
                        button.disabled = false;
                        return;
                }
                
                // Execute command via API
                const response = await fetch('/api/superclaud_api.php/commands', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        command: command,
                        params: params
                    })
                });
                
                const result = await response.json();
                displayResult(resultBox, result);
                
            } catch (error) {
                resultBox.innerHTML = `<span class="error">❌ 오류 발생: ${error.message}</span>`;
            } finally {
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        
        function displayResult(resultBox, data) {
            let html = '';
            
            if (data.success) {
                html += '<div class="success">✅ 실행 성공!</div><br>';
                
                if (data.stats) {
                    // Framework stats
                    html += '<strong>📊 Framework 통계:</strong><br>';
                    html += `• Agent 수: ${data.stats.agents_count}<br>`;
                    html += `• 명령어 수: ${data.stats.commands_count}<br>`;
                    html += `• 현재 모드: ${data.stats.current_mode}<br>`;
                    html += `• 가동시간: ${data.stats.uptime}<br><br>`;
                }
                
                if (data.data) {
                    html += '<strong>📋 실행 결과:</strong><br>';
                    
                    if (typeof data.data === 'object') {
                        for (const [key, value] of Object.entries(data.data)) {
                            html += `• ${key}: ${JSON.stringify(value)}<br>`;
                        }
                    } else {
                        html += `${data.data}<br>`;
                    }
                }
                
                if (data.message) {
                    html += `<br><strong>💬 메시지:</strong> ${data.message}`;
                }
                
                if (data.execution_time_ms) {
                    html += `<br><strong>⏱️ 실행시간:</strong> ${data.execution_time_ms}ms`;
                }
                
            } else {
                html = `<div class="error">❌ 실행 실패: ${data.error || data.message || '알 수 없는 오류'}</div>`;
            }
            
            resultBox.innerHTML = html;
        }
        
        // Show welcome message
        setTimeout(() => {
            alert('🎉 SuperClaude Framework Quick Start에 오신 것을 환영합니다!\n\n각 데모 카드의 버튼을 클릭하여 실제 기능을 체험해보세요.');
        }, 1000);
    </script>
</body>
</html>