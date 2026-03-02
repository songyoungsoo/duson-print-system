# 📋 정보수집 에이전트 (Collector Agent)

## 역할
사용자가 입력한 사업체 정보를 구조화된 JSON brief로 정리한다.
**LLM 호출 없음** — 순수 데이터 정리/검증만 수행한다.

## 파이프라인 위치
```
[1] Collector(0s) → [2] Copywriter(~15s) → [3] Designer(~15s) → [4] Prompter(~120s)
```

## 입력
사용자 폼 데이터 (HTML form POST):

| 필드 | POST name | 필수 | 설명 | 예시 |
|------|-----------|------|------|------|
| 상호명 | `business_name` | ✅ | 사업체 이름 | "맛나분식" |
| 업종 | `industry` | ✅ | 드롭다운 선택 | "한식" |
| 업종 상세 | `industry_detail` | ❌ | 자유입력 | "분식/떡볶이 전문점" |
| 캐치프레이즈 | `catchphrase` | ❌ | 한 줄 슬로건 | "엄마 손맛 그대로" |
| 전화번호 | `phone` | ✅ | 대표 연락처 | "02-1234-5678" |
| 주소 | `address` | ✅ | 사업장 주소 | "서울시 마포구 합정동 123" |
| 영업시간 | `hours` | ❌ | 영업시간 | "매일 10:00~22:00" |
| 메뉴/서비스 | `menu_items` | ✅ | 줄바꿈 구분 목록 | "떡볶이 4,000원\n순대 5,000원" |
| 특장점 | `features` | ❌ | 줄바꿈 구분 3가지 | "30년 전통\n직접 만든 어묵\n매일 신선한 재료" |
| 프로모션 문구 | `promo_text` | ❌ | 할인/이벤트 문구 | "오픈 기념 전 메뉴 10% 할인" |
| 분위기 키워드 | `mood` | ❌ | 드롭다운 선택 | "따뜻한" / "모던한" / "고급스러운" |
| 로고 이미지 | `logo_file` | ❌ | 업로드 파일 | logo.png |

## 처리 로직

### 1. 필수 필드 검증
```
IF business_name 빈값 → 에러: "상호명을 입력해주세요"
IF industry 빈값 → 에러: "업종을 선택해주세요"
IF phone 빈값 → 에러: "전화번호를 입력해주세요"
IF address 빈값 → 에러: "주소를 입력해주세요"
IF menu_items 빈값 → 에러: "메뉴 또는 서비스를 1개 이상 입력해주세요"
```

### 2. 메뉴 파싱
```
menu_items 텍스트를 줄바꿈(\n)으로 분리
각 줄에서 가격 패턴 추출: /(\d{1,3}(,\d{3})*)\s*원/
→ { name: "떡볶이", price: "4,000원" }
가격 없으면 price: null
최대 12개 항목 (초과 시 12개까지만)
```

### 3. 특장점 파싱
```
features 텍스트를 줄바꿈(\n)으로 분리
최대 3개 항목 (초과 시 3개까지만)
빈값이면 업종별 기본값 사용:
  한식: ["정성 가득한 손맛", "신선한 국내산 재료", "넉넉한 양"]
  일식: ["정통 일본식 조리법", "엄선된 신선 재료", "깔끔한 플레이팅"]
  중식: ["정통 화덕 조리", "풍성한 양", "빠른 서비스"]
  양식/카페: ["프리미엄 원두/재료", "아늑한 공간", "감각적인 메뉴"]
  치킨: ["바삭한 식감", "특제 소스", "빠른 배달"]
  학원: ["체계적인 커리큘럼", "소수 정예 수업", "검증된 강사진"]
  피트니스: ["최신 운동 장비", "1:1 맞춤 프로그램", "쾌적한 시설"]
  뷰티: ["고급 제품 사용", "섬세한 시술", "프라이빗한 공간"]
  일반: ["최고의 품질", "합리적인 가격", "친절한 서비스"]
```

### 4. 업종 코드 매핑
```
industry 값 → industry_code 변환:
  "한식" → "korean"
  "일식" → "japanese"
  "중식" → "chinese"
  "양식" → "western"
  "카페" → "cafe"
  "치킨" → "chicken"
  "학원" → "academy"
  "피트니스" → "fitness"
  "뷰티" → "beauty"
  기타 → "general"
```

### 5. 캐치프레이즈 기본값
```
빈값이면 업종별 기본값:
  korean: "정성을 담은 한 그릇"
  japanese: "신선함을 그대로"
  chinese: "정통 그 맛, 그대로"
  western/cafe: "특별한 한 잔, 특별한 공간"
  chicken: "바삭함의 정석"
  academy: "꿈을 향한 첫걸음"
  fitness: "더 강한 나를 만나다"
  beauty: "아름다움이 시작되는 곳"
  general: "믿고 찾는 우리 가게"
```

## 출력: `flyer_brief.json`

```json
{
  "business": {
    "name": "맛나분식",
    "industry": "한식",
    "industry_code": "korean",
    "industry_detail": "분식/떡볶이 전문점",
    "catchphrase": "엄마 손맛 그대로",
    "phone": "02-1234-5678",
    "address": "서울시 마포구 합정동 123",
    "hours": "매일 10:00~22:00",
    "mood": "따뜻한",
    "has_logo": false
  },
  "menu": [
    { "name": "떡볶이", "price": "4,000원" },
    { "name": "순대", "price": "5,000원" },
    { "name": "튀김 모듬", "price": "6,000원" },
    { "name": "라볶이", "price": "5,500원" },
    { "name": "김밥", "price": "3,500원" },
    { "name": "우동", "price": "4,500원" }
  ],
  "features": [
    "30년 전통의 맛",
    "직접 만든 어묵",
    "매일 신선한 재료"
  ],
  "promo": {
    "text": "오픈 기념 전 메뉴 10% 할인",
    "has_promo": true
  },
  "sections": ["hero", "menu", "promo_contact", "features", "gallery", "location"],
  "meta": {
    "created_at": "2026-03-02T12:00:00+09:00",
    "pipeline_version": "1.0",
    "image_size": "794x1123"
  }
}
```

## JSON 스키마 정의 (TypeScript 형식)

```typescript
interface FlyerBrief {
  business: {
    name: string;              // 상호명 (필수)
    industry: string;          // 업종 한국어 (필수)
    industry_code: string;     // 업종 코드 (자동 매핑)
    industry_detail: string;   // 업종 상세 (선택)
    catchphrase: string;       // 캐치프레이즈 (기본값 있음)
    phone: string;             // 전화번호 (필수)
    address: string;           // 주소 (필수)
    hours: string;             // 영업시간 (선택, 기본 "")
    mood: string;              // 분위기 키워드 (선택)
    has_logo: boolean;         // 로고 업로드 여부
  };
  menu: Array<{
    name: string;              // 메뉴/서비스명
    price: string | null;      // 가격 (없으면 null)
  }>;                          // 최대 12개
  features: string[];          // 특장점 3개 (기본값 있음)
  promo: {
    text: string;              // 프로모션 문구
    has_promo: boolean;        // 프로모션 유무
  };
  sections: string[];          // 고정: 6개 섹션 ID
  meta: {
    created_at: string;        // ISO 8601
    pipeline_version: string;  // "1.0"
    image_size: string;        // "794x1123"
  };
}
```

## 에러 처리
- 필수 필드 누락 → `{ "error": true, "message": "..." }` 반환
- 메뉴 항목 0개 → `{ "error": true, "message": "메뉴를 1개 이상 입력해주세요" }` 반환
- 전화번호 형식 비정상 → 경고 표시하되 진행 허용
- 처리 시간: 0초 (LLM 미사용, 동기 처리)
