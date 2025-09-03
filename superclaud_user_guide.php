<?php
/**
 * SuperClaude Framework User Guide
 * HTML version with proper Korean encoding
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperClaude Framework 사용자 가이드 - 두손기획인쇄</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', 'Apple SD Gothic Neo', 'Malgun Gothic', sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
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
        
        .content {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .toc {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .toc h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .toc ul {
            list-style: none;
            padding-left: 0;
        }
        
        .toc li {
            margin: 8px 0;
        }
        
        .toc a {
            color: #555;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: block;
        }
        
        .toc a:hover {
            background: #667eea;
            color: white;
        }
        
        h1, h2, h3, h4 {
            color: #333;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        h1 {
            font-size: 2.2rem;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        
        h2 {
            font-size: 1.8rem;
            color: #667eea;
        }
        
        h3 {
            font-size: 1.4rem;
            color: #764ba2;
        }
        
        h4 {
            font-size: 1.2rem;
        }
        
        p {
            margin: 15px 0;
            line-height: 1.7;
        }
        
        ul, ol {
            margin: 15px 0;
            padding-left: 30px;
        }
        
        li {
            margin: 5px 0;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #e91e63;
            font-size: 0.9em;
        }
        
        pre {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            overflow-x: auto;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        
        pre code {
            background: none;
            padding: 0;
            color: #333;
            font-size: 0.95em;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .feature-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #667eea;
        }
        
        .feature-card h4 {
            color: #667eea;
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .command-box {
            background: #1e1e1e;
            color: #f8f8f2;
            padding: 15px 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            position: relative;
        }
        
        .command-box::before {
            content: '💻';
            position: absolute;
            right: 10px;
            top: 10px;
            opacity: 0.7;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid;
        }
        
        .alert-info {
            background: #e3f2fd;
            border-left-color: #2196f3;
            color: #1565c0;
        }
        
        .alert-warning {
            background: #fff3e0;
            border-left-color: #ff9800;
            color: #e65100;
        }
        
        .alert-success {
            background: #e8f5e8;
            border-left-color: #4caf50;
            color: #2e7d32;
        }
        
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #667eea;
            color: white;
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .nav-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .title {
                font-size: 2rem;
            }
            
            .content {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 class="title">
                <i class="fas fa-rocket"></i> SuperClaude Framework
            </h1>
            <p class="subtitle">사용자 가이드 - 두손기획인쇄 지능형 자동화 시스템</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Table of Contents -->
            <div class="toc">
                <h3><i class="fas fa-list"></i> 목차</h3>
                <ul>
                    <li><a href="#overview">🎯 개요</a></li>
                    <li><a href="#features">🌟 주요 기능</a></li>
                    <li><a href="#quickstart">🚀 빠른 시작</a></li>
                    <li><a href="#dashboard">📊 대시보드 사용법</a></li>
                    <li><a href="#agents">🤖 Agent 시스템</a></li>
                    <li><a href="#commands">⚡ 명령어 시스템</a></li>
                    <li><a href="#modes">🎛️ 운영 모드</a></li>
                    <li><a href="#api">🔗 API 사용법</a></li>
                    <li><a href="#examples">💡 사용 예시</a></li>
                    <li><a href="#troubleshooting">🔧 문제해결</a></li>
                </ul>
            </div>

            <!-- Overview -->
            <section id="overview">
                <h1>🎯 개요</h1>
                <p>SuperClaude Framework는 두손기획인쇄 시스템을 위한 지능형 자동화 프레임워크입니다. 14개의 전문 Agent와 22개의 슬래시 명령어를 통해 인쇄업무를 자동화할 수 있습니다.</p>
                
                <div class="alert alert-info">
                    <strong>💡 핵심 개념:</strong> SuperClaude Framework는 인공지능 기반의 자동화 시스템으로, 복잡한 인쇄 업무를 단순한 명령어로 처리할 수 있게 해줍니다.
                </div>
            </section>

            <!-- Features -->
            <section id="features">
                <h1>🌟 주요 기능</h1>
                <div class="feature-grid">
                    <div class="feature-card">
                        <h4><i class="fas fa-robot"></i> 14개 전문 Agent</h4>
                        <p>주문관리, 품질관리, 재고관리, 생산계획, 고객서비스, 시스템분석, 데이터베이스최적화, 보안감사, 백업관리, 보고서생성, 가격계산, 워크플로우최적화, 컴플라이언스체크, 통합관리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4><i class="fas fa-terminal"></i> 22개 슬래시 명령어</h4>
                        <p><code>/sc:</code> 접두사로 시작하는 직관적인 명령어 시스템. 주문관리, 생산관리, 분석&리포팅, 시스템관리 카테고리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4><i class="fas fa-cogs"></i> 6가지 운영 모드</h4>
                        <p>Production, Order Management, Analysis, Quality Control, System Optimization, Emergency Response 모드</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4><i class="fas fa-chart-line"></i> 실시간 대시보드</h4>
                        <p>웹 기반 모니터링 및 제어 인터페이스. Agent 상태, 명령어 실행, 시스템 상태를 실시간으로 확인</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4><i class="fas fa-plug"></i> RESTful API</h4>
                        <p>외부 시스템 연동을 위한 완전한 REST API. JSON 형태의 응답으로 프로그래밍 방식 접근 가능</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4><i class="fas fa-shield-alt"></i> 안전한 운영</h4>
                        <p>자동 백업, 롤백 기능, 오류 복구, 보안 감사 등 안전한 운영을 위한 종합적인 보안 시스템</p>
                    </div>
                </div>
            </section>

            <!-- Quick Start -->
            <section id="quickstart">
                <h1>🚀 빠른 시작</h1>
                
                <h3>1. 시스템 확인</h3>
                <div class="command-box">http://localhost/superclaud_test.php</div>
                <ul>
                    <li>모든 항목이 녹색 체크마크(✅)인지 확인</li>
                    <li>오류가 있으면 해결 후 진행</li>
                </ul>
                
                <h3>2. 대시보드 접속</h3>
                <div class="command-box">http://localhost/superclaud_dashboard.php</div>
                <ul>
                    <li>SuperClaude Framework 관리 인터페이스</li>
                    <li>실시간 Agent 상태 모니터링</li>
                    <li>명령어 실행 및 결과 확인</li>
                </ul>
                
                <h3>3. 체험용 데모</h3>
                <div class="command-box">http://localhost/superclaud_quick_start.php</div>
                <ul>
                    <li>클릭 한 번으로 기능 체험</li>
                    <li>시스템 상태, 재고 현황, 생산 현황 등 확인</li>
                </ul>
                
                <div class="alert alert-success">
                    <strong>✅ 준비 완료!</strong> 모든 테스트가 통과하면 SuperClaude Framework를 사용할 준비가 되었습니다.
                </div>
            </section>

            <!-- Dashboard Usage -->
            <section id="dashboard">
                <h1>📊 대시보드 사용법</h1>
                
                <h2>🎛️ 메인 화면 구성</h2>
                
                <h3>상단 헤더</h3>
                <ul>
                    <li><strong>Framework 상태:</strong> 운영 중 / 버전 정보</li>
                    <li><strong>통계 카드:</strong> Agent 수, 명령어 수, 현재 모드, 가동시간</li>
                </ul>
                
                <h3>왼쪽 패널 - Command Interface</h3>
                <ul>
                    <li><strong>모드 선택:</strong> Production, Orders, Analysis, Optimize</li>
                    <li><strong>Quick Actions:</strong> 자주 사용하는 명령어 버튼</li>
                    <li><strong>명령어 입력창:</strong> 직접 명령어 입력 및 실행</li>
                </ul>
                
                <h3>오른쪽 패널 - Agent Status</h3>
                <ul>
                    <li><strong>활성 Agent 목록:</strong> 14개 Agent 상태 표시</li>
                    <li><strong>Agent 상태:</strong> Active, Placeholder 등</li>
                </ul>
                
                <h3>하단 패널 - System Monitor</h3>
                <ul>
                    <li><strong>실행 로그:</strong> 명령어 실행 기록</li>
                    <li><strong>시스템 메트릭:</strong> CPU, 메모리, 디스크 사용량</li>
                </ul>
                
                <div class="alert alert-info">
                    <strong>💡 팁:</strong> 대시보드 상단의 "모드 전환" 버튼으로 작업에 맞는 모드로 빠르게 변경할 수 있습니다.
                </div>
            </section>

            <!-- Agent System -->
            <section id="agents">
                <h1>🤖 Agent 시스템</h1>
                <p>SuperClaude Framework의 핵심인 14개 전문 Agent들은 각자의 역할을 담당하여 업무를 자동화합니다.</p>
                
                <h2>📋 Agent 목록 및 역할</h2>
                
                <div class="feature-grid">
                    <div class="feature-card">
                        <h4>🎯 PrintJobManager</h4>
                        <p>주문 생성 및 라이프사이클 관리, 주문 상태 추적, 배송 관리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🛡️ QualityControl</h4>
                        <p>품질 검사 및 표준 준수, 제품 품질 관리, 불량품 처리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>📦 InventoryTracker</h4>
                        <p>재료 재고 및 공급망 관리, 재고 임계점 알림, 발주 관리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🏭 ProductionPlanner</h4>
                        <p>생산 일정 및 자원 할당, 생산 계획 최적화, 용량 관리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>📞 CustomerService</h4>
                        <p>고객 커뮤니케이션 및 지원, 문의 처리, 만족도 관리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>📊 SystemAnalyzer</h4>
                        <p>시스템 성능 및 상태 모니터링, 병목 지점 분석, 성능 최적화</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🗄️ DatabaseOptimizer</h4>
                        <p>데이터베이스 성능 최적화, 쿼리 튜닝, 인덱스 관리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🔒 SecurityAuditor</h4>
                        <p>보안 감사 및 컴플라이언스, 취약점 스캔, 보안 정책 관리</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>💾 BackupManager</h4>
                        <p>데이터 백업 및 복구, 백업 스케줄 관리, 재해 복구</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>📈 ReportGenerator</h4>
                        <p>비즈니스 보고서 및 분석, 매출 분석, 성과 리포트</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>💰 PriceCalculator</h4>
                        <p>동적 가격 책정 및 비용 최적화, 수익성 분석, 가격 전략</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>⚡ WorkflowOptimizer</h4>
                        <p>프로세스 개선 및 효율성, 워크플로우 분석, 자동화 제안</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>✅ ComplianceChecker</h4>
                        <p>규제 컴플라이언스 모니터링, 법규 준수, 감사 대응</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🔗 IntegrationManager</h4>
                        <p>외부 시스템 통합, API 관리, 데이터 동기화</p>
                    </div>
                </div>
            </section>

            <!-- Command System -->
            <section id="commands">
                <h1>⚡ 명령어 시스템</h1>
                <p>모든 명령어는 <code>/sc:</code> 접두사로 시작하며, 4개 카테고리로 구분됩니다.</p>
                
                <h2>📋 주문 관리 명령어</h2>
                <div class="command-box">
/sc:order-create      # 새 주문 생성
/sc:order-status      # 주문 상태 확인
/sc:order-modify      # 기존 주문 수정
/sc:order-cancel      # 주문 취소
/sc:order-history     # 주문 기록 조회
                </div>
                
                <h2>🏭 생산 관리 명령어</h2>
                <div class="command-box">
/sc:production-start     # 생산 작업 시작
/sc:production-status    # 생산 상태 조회
/sc:production-schedule  # 생산 일정 관리
/sc:quality-check        # 품질 검사 실행
/sc:inventory-status     # 재고 상태 확인
                </div>
                
                <h2>📊 분석 & 리포팅 명령어</h2>
                <div class="command-box">
/sc:report-daily         # 일일 보고서 생성
/sc:report-monthly       # 월간 보고서 생성
/sc:analyze-performance  # 성능 메트릭 분석
/sc:optimize-workflow    # 워크플로우 최적화
/sc:price-optimize       # 가격 최적화
                </div>
                
                <h2>🔧 시스템 관리 명령어</h2>
                <div class="command-box">
/sc:system-health       # 시스템 상태 확인
/sc:backup-create       # 시스템 백업 생성
/sc:security-audit      # 보안 감사 실행
/sc:database-optimize   # 데이터베이스 최적화
/sc:integration-test    # 통합 테스트 실행
                </div>
                
                <div class="alert alert-info">
                    <strong>💡 사용법:</strong> 대시보드의 명령어 입력창에 원하는 명령어를 입력하거나, Quick Actions 버튼을 클릭하세요.
                </div>
            </section>

            <!-- Operating Modes -->
            <section id="modes">
                <h1>🎛️ 운영 모드</h1>
                <p>상황에 맞는 최적의 성능을 위해 6가지 운영 모드를 제공합니다.</p>
                
                <div class="feature-grid">
                    <div class="feature-card">
                        <h4>🏭 Production Mode</h4>
                        <p><strong>용도:</strong> 일반적인 생산 업무<br>
                        <strong>특징:</strong> 안정성 우선, 검증된 프로세스 사용<br>
                        <strong>Agent:</strong> PrintJobManager, ProductionPlanner 중심</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>📋 Order Management Mode</h4>
                        <p><strong>용도:</strong> 주문 처리 집중 모드<br>
                        <strong>특징:</strong> 주문 관련 Agent 활성화<br>
                        <strong>Agent:</strong> PrintJobManager, CustomerService 중심</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🔍 Analysis Mode</h4>
                        <p><strong>용도:</strong> 데이터 분석 및 리포팅<br>
                        <strong>특징:</strong> 분석 도구 최적화<br>
                        <strong>Agent:</strong> SystemAnalyzer, ReportGenerator 중심</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🛡️ Quality Control Mode</h4>
                        <p><strong>용도:</strong> 품질 관리 집중<br>
                        <strong>특징:</strong> 품질 검사 강화<br>
                        <strong>Agent:</strong> QualityControl, ComplianceChecker 중심</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>⚡ System Optimization Mode</h4>
                        <p><strong>용도:</strong> 시스템 성능 최적화<br>
                        <strong>특징:</strong> 성능 튜닝 및 최적화<br>
                        <strong>Agent:</strong> DatabaseOptimizer, WorkflowOptimizer 중심</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🚨 Emergency Response Mode</h4>
                        <p><strong>용도:</strong> 긴급 상황 대응<br>
                        <strong>특징:</strong> 빠른 복구 및 대응<br>
                        <strong>Agent:</strong> BackupManager, SecurityAuditor 중심</p>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <strong>⚠️ 주의:</strong> Emergency Response Mode는 시스템에 문제가 발생했을 때만 사용하세요.
                </div>
            </section>

            <!-- API Usage -->
            <section id="api">
                <h1>🔗 API 사용법</h1>
                <p>SuperClaude Framework는 완전한 RESTful API를 제공하여 외부 시스템에서 프로그래밍 방식으로 접근할 수 있습니다.</p>
                
                <h2>📍 기본 엔드포인트</h2>
                <div class="command-box">http://localhost/api/superclaud_api.php</div>
                
                <h2>📋 주요 API 엔드포인트</h2>
                
                <h3>GET 엔드포인트</h3>
                <pre><code>GET /api/superclaud_api.php/                    # Framework 상태
GET /api/superclaud_api.php/agents              # Agent 목록
GET /api/superclaud_api.php/commands            # 명령어 목록
GET /api/superclaud_api.php/tasks               # 작업 목록
GET /api/superclaud_api.php/inventory           # 재고 상태
GET /api/superclaud_api.php/production          # 생산 상태
GET /api/superclaud_api.php/health              # 시스템 상태</code></pre>
                
                <h3>POST 엔드포인트</h3>
                <pre><code>POST /api/superclaud_api.php/commands           # 명령어 실행
POST /api/superclaud_api.php/tasks              # 작업 생성
POST /api/superclaud_api.php/orders             # 주문 생성</code></pre>
                
                <h2>💻 API 사용 예시</h2>
                
                <h3>시스템 상태 확인</h3>
                <pre><code>// JavaScript 예시
fetch('/api/superclaud_api.php/health')
  .then(response => response.json())
  .then(data => {
    console.log('시스템 상태:', data);
  });</code></pre>
                
                <h3>명령어 실행</h3>
                <pre><code>// JavaScript 예시
fetch('/api/superclaud_api.php/commands', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    command: '/sc:system-health',
    params: {}
  })
})
.then(response => response.json())
.then(result => {
  console.log('명령어 실행 결과:', result);
});</code></pre>
            </section>

            <!-- Examples -->
            <section id="examples">
                <h1>💡 사용 예시</h1>
                
                <h2>🎯 일반적인 업무 시나리오</h2>
                
                <h3>1. 아침 업무 시작</h3>
                <div class="command-box">
# 시스템 상태 확인
/sc:system-health

# 어제 주문 현황 확인
/sc:order-history

# 재고 상태 확인
/sc:inventory-status
                </div>
                
                <h3>2. 새 주문 처리</h3>
                <div class="command-box">
# 새 명함 주문 생성
/sc:order-create

# 생산 일정에 추가
/sc:production-schedule

# 재료 소비 업데이트
/sc:inventory-status
                </div>
                
                <h3>3. 품질 검사</h3>
                <div class="command-box">
# 완료된 작업 품질 검사
/sc:quality-check

# 검사 결과 리포트
/sc:report-daily
                </div>
                
                <h3>4. 월말 정산</h3>
                <div class="command-box">
# 월간 매출 보고서
/sc:report-monthly

# 재고 현황 분석
/sc:analyze-performance

# 가격 최적화 검토
/sc:price-optimize
                </div>
                
                <div class="alert alert-success">
                    <strong>✨ 효율성 팁:</strong> 자주 사용하는 명령어 조합을 대시보드의 Quick Actions에 등록하여 한 번의 클릭으로 실행하세요.
                </div>
            </section>

            <!-- Troubleshooting -->
            <section id="troubleshooting">
                <h1>🔧 문제해결</h1>
                
                <h2>❓ 자주 묻는 질문</h2>
                
                <h3>Q: Agent가 응답하지 않을 때</h3>
                <div class="alert alert-warning">
                    <strong>해결방법:</strong>
                    <ol>
                        <li>대시보드에서 Agent 상태 확인</li>
                        <li><code>/sc:system-health</code> 실행</li>
                        <li>데이터베이스 연결 상태 확인</li>
                        <li>필요시 시스템 재시작</li>
                    </ol>
                </div>
                
                <h3>Q: 명령어 실행이 실패할 때</h3>
                <div class="alert alert-warning">
                    <strong>해결방법:</strong>
                    <ol>
                        <li>명령어 문법 확인 (반드시 <code>/sc:</code>로 시작)</li>
                        <li>필요한 Agent가 활성화되어 있는지 확인</li>
                        <li>시스템 로그에서 오류 메시지 확인</li>
                        <li>해당 모드에서 지원하는 명령어인지 확인</li>
                    </ol>
                </div>
                
                <h3>Q: 데이터베이스 연결 오류</h3>
                <div class="alert alert-warning">
                    <strong>해결방법:</strong>
                    <ol>
                        <li>MySQL 서비스 실행 상태 확인</li>
                        <li><code>db.php</code> 설정값 확인</li>
                        <li>데이터베이스 사용자 권한 확인</li>
                        <li><code>/sc:database-optimize</code> 실행</li>
                    </ol>
                </div>
                
                <h2>🆘 긴급 상황 대응</h2>
                
                <h3>시스템 복구</h3>
                <div class="command-box">
# Emergency Response Mode로 전환
모드 설정: Emergency Response

# 백업에서 복구
/sc:backup-create

# 시스템 무결성 검사
/sc:security-audit
                </div>
                
                <h3>연락처</h3>
                <div class="alert alert-info">
                    <strong>📞 기술 지원:</strong><br>
                    전화: 02-2632-1830, 1688-2384<br>
                    이메일: admin@dsp114.com<br>
                    주소: 서울 영등포구 영등포로 36길 9, 송호빌딩 1F
                </div>
            </section>

            <!-- Navigation Buttons -->
            <div class="nav-buttons">
                <a href="superclaud_test.php" class="nav-btn">
                    <i class="fas fa-check-circle"></i>
                    시스템 테스트
                </a>
                <a href="superclaud_dashboard.php" class="nav-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    관리 대시보드
                </a>
                <a href="superclaud_quick_start.php" class="nav-btn">
                    <i class="fas fa-play"></i>
                    체험용 데모
                </a>
                <a href="api/superclaud_api.php" class="nav-btn" target="_blank">
                    <i class="fas fa-code"></i>
                    API 문서
                </a>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Back to top functionality
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/hide back to top button
        window.addEventListener('scroll', () => {
            const backToTop = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
    </script>
</body>
</html>