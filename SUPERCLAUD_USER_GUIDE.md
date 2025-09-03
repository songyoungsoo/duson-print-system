# SuperClaude Framework 사용자 가이드

## 🎯 개요
SuperClaude Framework는 두손기획인쇄 시스템을 위한 지능형 자동화 프레임워크입니다. 14개의 전문 Agent와 22개의 슬래시 명령어를 통해 인쇄업무를 자동화할 수 있습니다.

## 🌟 주요 기능
- **14개 전문 Agent**: 주문관리, 품질관리, 재고관리, 생산계획 등
- **22개 슬래시 명령어**: `/sc:` 접두사로 시작하는 명령어 시스템
- **6가지 운영 모드**: Production, Order Management, Analysis 등
- **실시간 대시보드**: 웹 기반 모니터링 및 제어
- **RESTful API**: 외부 시스템 연동

---

## 🚀 빠른 시작

### 1. 시스템 확인
```
http://localhost/superclaud_test.php
```
- 모든 항목이 녹색 체크마크(✅)인지 확인
- 오류가 있으면 해결 후 진행

### 2. 대시보드 접속
```
http://localhost/superclaud_dashboard.php
```
- SuperClaude Framework 관리 인터페이스
- 실시간 Agent 상태 모니터링
- 명령어 실행 및 결과 확인

---

## 📊 대시보드 사용법

### 🎛️ **메인 화면 구성**

#### **상단 헤더**
- **Framework 상태**: 운영 중 / 버전 정보
- **통계 카드**: Agent 수, 명령어 수, 현재 모드, 가동시간

#### **왼쪽 패널 - Command Interface**
- **모드 선택**: Production, Orders, Analysis, Optimize
- **Quick Actions**: 자주 사용하는 명령어 버튼
- **명령어 입력창**: 직접 명령어 입력 및 실행

#### **오른쪽 패널 - Agent Status**
- **활성 Agent 목록**: 14개 Agent 상태 표시
- **Agent 상태**: Active, Placeholder 등

#### **하단 패널 - Recent Activity**
- **활동 로그**: 최근 실행된 작업 내역
- **실시간 업데이트**: 30초마다 자동 갱신

### 🎯 **모드별 사용법**

#### **Production Mode** 🏭
생산 중심의 체계적 작업 처리
```
추천 명령어:
- /sc:production-status    # 생산 현황
- /sc:quality-check        # 품질 검사
- /sc:inventory-status     # 재고 확인
```

#### **Order Management Mode** 📋
주문 생성부터 완료까지 전체 라이프사이클 관리
```
추천 명령어:
- /sc:order-create         # 새 주문 생성
- /sc:order-status         # 주문 상태 확인
- /sc:order-history        # 주문 이력
```

#### **Analysis Mode** 🔍
데이터 기반 의사결정 지원
```
추천 명령어:
- /sc:report-daily         # 일일 보고서
- /sc:analyze-performance  # 성능 분석
- /sc:report-monthly       # 월간 보고서
```

#### **Optimization Mode** ⚡
지속적 개선 프로세스
```
추천 명령어:
- /sc:optimize-workflow    # 워크플로우 최적화
- /sc:price-optimize       # 가격 최적화
- /sc:database-optimize    # 데이터베이스 최적화
```

---

## 💻 명령어 시스템

### 📝 **주문 관리 명령어**

#### `/sc:order-create` - 새 주문 생성
```bash
# 기본 사용법
/sc:order-create

# 실제 사용 예시 (대시보드에서)
1. 명령어 입력: /sc:order-create
2. Execute 버튼 클릭
3. 결과 확인
```

#### `/sc:order-status [주문번호]` - 주문 상태 확인
```javascript
// API로 사용하는 경우
fetch('/api/superclaud_api.php/commands', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        command: '/sc:order-status',
        params: {order_id: 'NC20250131001'}
    })
})
```

#### `/sc:order-modify [주문번호]` - 주문 수정
#### `/sc:order-cancel [주문번호]` - 주문 취소
#### `/sc:order-history [고객ID]` - 주문 이력

### 🏭 **생산 관리 명령어**

#### `/sc:production-start [주문번호]` - 생산 시작
#### `/sc:production-status` - 생산 현황 조회
```json
// 응답 예시
{
    "success": true,
    "data": {
        "total_jobs": 15,
        "completed_jobs": 12,
        "completion_rate": "80%",
        "avg_duration_minutes": 45.2
    }
}
```

#### `/sc:quality-check [작업ID]` - 품질 검사
#### `/sc:inventory-status` - 재고 현황

### 📊 **분석 & 보고 명령어**

#### `/sc:report-daily` - 일일 보고서
```bash
# 대시보드에서
1. Analysis 모드 선택
2. "Daily Report" 버튼 클릭
3. 또는 /sc:report-daily 입력 후 실행
```

#### `/sc:report-monthly` - 월간 보고서
#### `/sc:analyze-performance` - 성능 분석
#### `/sc:optimize-workflow` - 워크플로우 최적화

### 🔧 **시스템 관리 명령어**

#### `/sc:system-health` - 시스템 상태 점검
```json
// 응답 예시
{
    "success": true,
    "data": {
        "database": "healthy",
        "disk_space": "85% free",
        "memory_usage": "45%",
        "cpu_load": "normal"
    }
}
```

#### `/sc:backup-create` - 시스템 백업
#### `/sc:security-audit` - 보안 감사
#### `/sc:database-optimize` - 데이터베이스 최적화

---

## 🛠️ Agent 시스템

### 🤖 **주요 Agent 소개**

#### **PrintJobManager** 📋
- **기능**: 주문 생성, 수정, 라이프사이클 관리
- **사용법**: 주문 관련 모든 작업을 자동화
- **특징**: 지능형 주문 검증, 자동 완료시간 계산

#### **QualityControl** ✅
- **기능**: 품질 검사, 표준 준수 관리
- **사용법**: 생산된 제품의 품질을 자동으로 평가
- **특징**: 85% 이상 품질 점수 유지, 개선 권장사항 제공

#### **InventoryTracker** 📦
- **기능**: 재고 관리, 공급망 모니터링
- **사용법**: 용지, 잉크, 소모품 재고 실시간 추적
- **특징**: 임계 재고 자동 알림, 재주문 추천

#### **ProductionPlanner** 🏗️
- **기능**: 생산 일정, 리소스 배분
- **사용법**: 주문에 따른 최적 생산 계획 수립
- **특징**: 지능형 스케줄링, 병목 지점 해결

---

## 🌐 API 사용법

### 📡 **RESTful API 엔드포인트**

#### **기본 정보**
```
Base URL: http://localhost/api/superclaud_api.php
Content-Type: application/json
```

#### **GET 엔드포인트**

##### 프레임워크 상태 확인
```javascript
fetch('/api/superclaud_api.php/')
.then(response => response.json())
.then(data => console.log(data));
```

##### Agent 목록 조회
```javascript
fetch('/api/superclaud_api.php/agents')
.then(response => response.json())
.then(data => console.log(data.agents));
```

##### 재고 상태 확인
```javascript
fetch('/api/superclaud_api.php/inventory')
.then(response => response.json())
.then(data => console.log(data));
```

##### 생산 현황 조회
```javascript
fetch('/api/superclaud_api.php/production')
.then(response => response.json())
.then(data => console.log(data));
```

#### **POST 엔드포인트**

##### 명령어 실행
```javascript
fetch('/api/superclaud_api.php/commands', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        command: '/sc:system-health',
        params: {}
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

##### 새 주문 생성
```javascript
fetch('/api/superclaud_api.php/orders', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        product_type: 'namecard',
        customer: {
            name: '홍길동',
            phone: '010-1234-5678',
            email: 'hong@example.com'
        },
        specs: {
            paper_type: '프리미엄 아트지',
            quantity: 1000,
            sides: 'double'
        }
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

##### 품질 검사 실행
```javascript
fetch('/api/superclaud_api.php/quality-check', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        job_id: 'JOB001',
        check_type: 'premium'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## 💼 PHP에서 직접 사용

### 🔧 **기본 사용법**

#### Framework 초기화 (자동)
```php
<?php
// db.php가 포함되면 자동으로 초기화됩니다
include 'db.php';
include 'includes/superclaud_framework.php';

// 이미 $GLOBALS['superclaud']에 Framework 인스턴스가 생성됨
?>
```

#### 명령어 실행
```php
<?php
// 간단한 명령어 실행
$result = sc_execute('/sc:system-health');
echo json_encode($result);

// 매개변수가 있는 명령어
$result = sc_execute('/sc:order-create', [
    'product_type' => 'namecard',
    'quantity' => 1000
]);
?>
```

#### Agent 직접 사용
```php
<?php
// Agent 가져오기
$printManager = sc_agent('PrintJobManager');

if ($printManager) {
    // 새 주문 생성
    $order = $printManager->createOrder([
        'product_type' => 'poster',
        'customer' => [
            'name' => '김철수',
            'phone' => '010-9876-5432'
        ],
        'specs' => [
            'size' => 'A1',
            'paper_type' => '아트지',
            'quantity' => 100
        ]
    ]);
    
    echo "주문 생성 결과: " . json_encode($order);
}
?>
```

#### 모드 변경
```php
<?php
// 운영 모드 변경
sc_mode('analysis');  // 분석 모드로 변경
sc_mode('production'); // 생산 모드로 변경
?>
```

---

## 📋 실제 사용 시나리오

### 🎯 **시나리오 1: 명함 주문 처리**

#### **1단계: 대시보드에서**
1. **Order Management** 모드 선택
2. `/sc:order-create` 명령어 입력
3. **Execute** 버튼 클릭
4. 결과에서 주문 번호 확인

#### **2단계: 생산 계획**
1. **Production** 모드로 변경
2. `/sc:production-start [주문번호]` 입력
3. 생산 일정 자동 생성 확인

#### **3단계: 품질 관리**
1. `/sc:quality-check [작업ID]` 실행
2. 품질 점수 85% 이상 확인
3. 불합격 시 개선 권장사항 확인

### 🎯 **시나리오 2: 재고 관리**

#### **1단계: 재고 확인**
```javascript
// API로 재고 상태 확인
fetch('/api/superclaud_api.php/inventory')
.then(response => response.json())
.then(data => {
    console.log('재고 현황:', data);
    
    // 임계 재고 알림 확인
    if (data.alerts && data.alerts.length > 0) {
        alert('재고 부족 항목이 있습니다!');
    }
});
```

#### **2단계: 자동 재주문**
- 임계 재고 도달 시 자동으로 Task 생성
- 재주문 추천 알림 발송
- 구매 담당자에게 알림

### 🎯 **시나리오 3: 성과 분석**

#### **매일 오전 9시**
```php
<?php
// 자동화된 일일 보고서
$daily_report = sc_execute('/sc:report-daily');

// 이메일 발송 (예시)
if ($daily_report['success']) {
    $report_data = $daily_report['data'];
    
    // 관리자에게 이메일 발송
    mail('manager@dsp114.com', 
         '일일 운영 보고서', 
         json_encode($report_data, JSON_PRETTY_PRINT));
}
?>
```

#### **월말 분석**
```javascript
// 월간 성과 분석
fetch('/api/superclaud_api.php/reports/monthly?date=2025-01-31', {
    method: 'GET'
})
.then(response => response.json())
.then(data => {
    // 월간 보고서를 차트로 표시
    displayMonthlyChart(data);
});
```

---

## ⚠️ 주의사항 및 팁

### 🚨 **주의사항**

1. **데이터베이스 백업**
   - 정기적으로 `/sc:backup-create` 실행
   - 중요한 작업 전 백업 필수

2. **시스템 모니터링**
   - `/sc:system-health` 정기 실행
   - 디스크 용량 85% 이상 시 정리

3. **보안 관리**
   - `/sc:security-audit` 월 1회 실행
   - API 접근 로그 정기 확인

### 💡 **유용한 팁**

1. **명령어 단축키**
   - 대시보드에서 `Enter` 키로 빠른 실행
   - Quick Actions 버튼 활용

2. **모드 최적화**
   - 오전: Production Mode (생산 중심)
   - 오후: Order Management (주문 처리)
   - 저녁: Analysis Mode (분석 및 보고)

3. **API 활용**
   - 외부 시스템과 연동 시 API 사용
   - JSON 형태로 구조화된 데이터 제공

4. **자동화 활용**
   - 정기적인 작업은 cron으로 자동화
   - 임계값 설정으로 알림 자동화

---

## 🆘 문제 해결

### ❌ **자주 발생하는 오류**

#### **"Agent not found" 오류**
```
해결방법:
1. http://localhost/superclaud_test.php 실행
2. Agent Loading Test 항목 확인
3. Placeholder Agent로 표시되면 정상 (구현 예정)
```

#### **"Database connection failed" 오류**
```
해결방법:
1. XAMPP MySQL 서비스 실행 확인
2. db.php의 연결 정보 확인
3. 데이터베이스 duson1830 존재 확인
```

#### **명령어 실행 실패**
```
해결방법:
1. /sc:system-health 먼저 실행
2. 시스템 상태 확인
3. 오류 로그 확인 (C:/xampp/php/logs/)
```

### 🔧 **성능 최적화**

1. **데이터베이스 최적화**
   ```
   /sc:database-optimize
   ```

2. **캐시 정리**
   - 브라우저 캐시 정리
   - PHP OPcache 재시작

3. **로그 관리**
   - 오래된 로그 파일 정리
   - 로그 레벨 조정

---

## 📞 지원 및 문의

### 🎯 **추가 개발 요청**
- 새로운 Agent 구현
- 커스텀 명령어 추가
- 특화된 보고서 개발

### 📧 **기술 지원**
- 시스템 오류 문의
- 성능 최적화 상담
- 교육 및 워크샵

### 🚀 **향후 업데이트**
- AI 기반 예측 분석
- 고급 자동화 기능
- 모바일 앱 연동

---

*SuperClaude Framework v1.0*  
*© 2025 두손기획인쇄*  
*AI 기반 지능형 인쇄 관리 시스템*