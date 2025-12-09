# 상품관리 시스템 V2.0 개발 로드맵

## 🎯 V2.0 비전

**"품목별 전문화된 관리 시스템"**

V1.0의 통합 관리에서 진화하여, 각 품목의 특성에 맞는 전문화된 관리 도구 제공

## 📋 V1.0 vs V2.0 비교

| 구분 | V1.0 (통합형) | V2.0 (전문화형) |
|------|---------------|-----------------|
| **데이터 소스** | product_prices 통합 테이블 | 각 품목별 원본 테이블 |
| **관리 방식** | 범용 CRUD | 품목별 특화 기능 |
| **UI/UX** | 공통 인터페이스 | 품목별 맞춤 UI |
| **검증 규칙** | 일반적 검증 | 품목별 비즈니스 규칙 |
| **확장성** | 제한적 | 무제한 확장 |

## 🏗️ V2.0 아키텍처 설계

### 1. **디렉토리 구조**
```
admin/product-manager-v2/
├── core/                    # 공통 기능
│   ├── BaseProductManager.php
│   ├── DatabaseManager.php
│   └── ValidationEngine.php
├── products/                # 품목별 전문 관리
│   ├── namecard/           # 명함 관리
│   ├── flyer/              # 전단지 관리
│   ├── envelope/           # 봉투 관리
│   ├── cadarok/            # 카다록 관리
│   ├── littleprint/        # 포스터 관리
│   ├── merchandisebond/    # 상품권 관리
│   ├── msticker/           # 자석스티커 관리
│   └── ncrflambeau/        # NCR양식 관리
├── shared/                 # 공유 리소스
│   ├── css/
│   ├── js/
│   └── components/
└── api/                    # RESTful API
    ├── v2/
    └── legacy/             # V1.0 호환
```

### 2. **데이터 연동 전략**
```php
// 각 품목별 원본 테이블 직접 연동
$productTables = [
    'namecard' => 'mlangprintauto_namecard',
    'flyer' => 'mlangprintauto_inserted',
    'envelope' => 'mlangprintauto_envelope',
    'cadarok' => 'mlangprintauto_cadarok',
    'littleprint' => 'mlangprintauto_littleprint',
    'merchandisebond' => 'mlangprintauto_merchandisebond',
    'msticker' => 'mlangprintauto_msticker',
    'ncrflambeau' => 'mlangprintauto_ncrflambeau'
];
```

## 📅 개발 단계

### **Phase 1: 기반 구축 (2주)**
- [ ] 공통 BaseProductManager 클래스 개발
- [ ] 품목별 디렉토리 구조 생성
- [ ] V1.0 → V2.0 마이그레이션 도구 개발
- [ ] 공통 컴포넌트 라이브러리 구축

### **Phase 2: 우선순위 품목 개발 (3주)**
- [ ] **명함 관리** (우선순위 1) - 복잡한 재질 옵션
- [ ] **전단지 관리** (우선순위 2) - 최대 데이터량
- [ ] **봉투 관리** (우선순위 3) - 특수 규격 관리

### **Phase 3: 나머지 품목 개발 (3주)**
- [ ] 카다록, 포스터, 상품권 관리
- [ ] 자석스티커, NCR양식 관리
- [ ] 크로스 브라우저 테스트

### **Phase 4: 통합 및 최적화 (2주)**
- [ ] 성능 최적화
- [ ] 보안 강화
- [ ] 사용자 교육 및 문서화
- [ ] 프로덕션 배포

## 🔧 품목별 특화 기능

### **1. 명함 관리 (namecard)**
```php
특화 기능:
- 재질별 가격 매트릭스 관리
- 프리미엄 옵션 (박, 넘버링, 미싱, 귀돌이, 오시)
- 재질 계층 구조 관리 (BigNo 시스템)
- 수량별 단가 계산기

고유 필드:
- style (재질 코드)
- Section (세부 재질)
- POtype (단면/양면)
- money (기본 가격)
- DesignMoney (디자인 가격)
```

### **2. 전단지 관리 (flyer/inserted)**
```php
특화 기능:
- 크기별 가격 관리 (A2, A3, A4, 4절, 8절, 16절)
- 용지 종류별 분류
- 인쇄 색상 옵션
- 후가공 옵션 (코팅, 접지, 오시)

고유 필드:
- MY_type (인쇄 색상)
- MY_Fsd (용지 종류)
- PN_type (용지 규격)
- quantity (수량)
```

### **3. 봉투 관리 (envelope)**
```php
특화 기능:
- 규격별 가격 관리
- 창봉투/일반봉투 구분
- 양면테이프 옵션 가격 계산
- 대량 주문 할인 체계

고유 필드:
- style (봉투 종류)
- Section (규격)
- 양면테이프 가격 계산 로직
```

## 🎨 UI/UX 전문화

### **공통 디자인 시스템**
- V1.0에서 검증된 체크박스 정렬 시스템 활용
- 일관된 색상 팔레트 및 타이포그래피
- 반응형 디자인 기반

### **품목별 맞춤 인터페이스**
```
명함: 재질 선택 중심의 매트릭스 뷰
전단지: 크기별 가격 비교 테이블
봉투: 규격별 시각적 가이드
카다록: 고급 옵션 중심 UI
```

## 📊 데이터 마이그레이션 전략

### **1. 양방향 동기화**
```php
// V1.0 통합 테이블 ↔ V2.0 개별 테이블
class DataSynchronizer {
    public function syncV1ToV2($productCode) {
        // product_prices → mlangprintauto_[product]
    }

    public function syncV2ToV1($productCode) {
        // mlangprintauto_[product] → product_prices
    }
}
```

### **2. 점진적 마이그레이션**
1. **Phase 1**: V1.0과 V2.0 병존
2. **Phase 2**: 품목별 순차 전환
3. **Phase 3**: V1.0 레거시 모드 전환
4. **Phase 4**: V2.0 완전 전환

## 🔐 보안 및 성능

### **보안 강화**
- JWT 기반 API 인증
- 역할 기반 접근 제어 (RBAC)
- 입력 데이터 검증 강화
- SQL 인젝션 방지 개선

### **성능 최적화**
- 품목별 데이터베이스 연결 풀링
- Redis 캐싱 시스템 도입
- 이미지 최적화 및 CDN 연동
- 지연 로딩 및 페이지네이션 개선

## 🧪 테스트 전략

### **단위 테스트**
```php
// 각 품목별 매니저 클래스 테스트
tests/
├── NamecardManagerTest.php
├── FlyerManagerTest.php
└── EnvelopeManagerTest.php
```

### **통합 테스트**
- V1.0 → V2.0 데이터 일관성 검증
- API 엔드포인트 응답 시간 테스트
- 동시 사용자 부하 테스트

### **사용자 테스트**
- 관리자 워크플로우 시나리오 테스트
- 각 품목별 실제 업무 프로세스 검증

## 📈 성공 지표

### **기술적 지표**
- [ ] 응답 시간 50% 개선 (vs V1.0)
- [ ] 코드 재사용률 80% 이상
- [ ] 테스트 커버리지 90% 이상
- [ ] 버그 발생률 V1.0 대비 50% 감소

### **비즈니스 지표**
- [ ] 관리자 작업 효율성 70% 향상
- [ ] 신규 품목 추가 시간 80% 단축
- [ ] 데이터 정확도 99% 이상 유지
- [ ] 사용자 만족도 95% 이상

## 🔄 V1.0과의 호환성

### **레거시 지원**
```php
// V1.0 API 호환성 유지
class LegacyApiAdapter {
    public function convertV1RequestToV2($request) {
        // V1.0 요청을 V2.0 포맷으로 변환
    }

    public function convertV2ResponseToV1($response) {
        // V2.0 응답을 V1.0 포맷으로 변환
    }
}
```

### **점진적 업그레이드**
- V1.0 사용자들이 원할 때 V2.0으로 전환
- 병존 기간 중 데이터 일관성 보장
- 필요시 V1.0으로 롤백 가능

## 📚 문서화 계획

### **개발자 문서**
- [ ] API 명세서 (OpenAPI 3.0)
- [ ] 아키텍처 문서
- [ ] 코딩 컨벤션 가이드
- [ ] 배포 가이드

### **사용자 문서**
- [ ] 품목별 사용자 매뉴얼
- [ ] 마이그레이션 가이드
- [ ] FAQ 및 트러블슈팅
- [ ] 비디오 튜토리얼

## 🎯 결론

V2.0은 V1.0의 성공적인 통합 경험을 바탕으로, 각 품목의 고유한 특성을 살린 전문화된 관리 시스템으로 발전합니다.

### **핵심 가치**
1. **전문성**: 품목별 특화된 관리 기능
2. **확장성**: 새로운 품목 쉬운 추가
3. **안정성**: V1.0에서 검증된 기술 기반
4. **연속성**: 기존 데이터와 워크플로우 보존

**V2.0은 두손기획인쇄의 상품 관리를 한 단계 더 발전시킬 것입니다.**

---

**로드맵 버전**: 1.0
**최종 업데이트**: 2025-01-28
**예상 완료**: 2025년 3월
**우선순위**: 🔥 높음