# KB Knowledge Vault — 전체 문서 목록

## 목차

| # | 제목 | 카테고리 | 태그 |
|---|------|---------|------|
| 1 | OpenCode + Oh-My-OpenCode WSL 설치 가이드 | setup | opencode, oh-my-opencode, wsl, 설치, bun, nodejs |
| 2 | OpenCode 로컬 파일 위치 맵 | reference | opencode, 설정, 파일위치, config |
| 3 | sknas205.ipdisk.co.kr 나스주소 | reference | sknas205, 나스, 주소 |
| 4 | 네이버광고 대행사 | reference | 네이버, 파워링크, 광고, 대행사, 수수료, 직접관리, searchad |
| 5 | 두손기획인쇄 시스템 가이드 (AGENTS.md) | reference | 시스템, 가이드, AGENTS, 배포, 결제, 인증, 주문, 교정, 견적, 택배, 이메일, 챗봇, 영문, 마이그레이션 |
| 6 | 두손기획인쇄 마스터 사양서 (Master Spec v1.0) | reference | 마스터, 사양서, 스펙, 아키텍처, 데이터흐름, 제품, 가격계산, 스티커 |
| 7 | 품목별주소 | general | 품목 |
| 8 | dsp114.com | general | 도메인내용 |
| 9 | 도메인 스왑 완벽 가이드 (dsp114.com ↔ dsp114.co.kr) | workflow | domain, dns, migration, plesk, deployment, 도메인스왑, gabia, 가비아 |
| 10 | 챗붓의 API 사용량 | general | 사용량, API |
| 11 | 이니시스 네이버 메일서버 국민은행 에스크로 | general | 이니시스, 네이버 메일서버, 국민은행 에스크로 |
| 12 | 도메인 변경 2 | general | 도메인, 이니시스 |
| 13 | 롤백시 깃허브 | general | 도메인롤백 |
| 14 | KB에스크로 롤백문서 dsp114.com용 | general | KB에스크로, dsp114.com용 |
| 15 | 팝업관리 | general | 명절, 휴가, 공지 |
| 16 | 네이버url 요청 | general | 네이버검색 |
| 17 | 결제알림 서비스 | general | 알림, 결제, 서비스, 가입 |
| 18 | 카톡알림 방법 | general | 주문알림 |
| 19 | 네비게이션 내용 | general | 메뉴 |

---

## [1] OpenCode + Oh-My-OpenCode WSL 설치 가이드

**카테고리**: setup | **태그**: opencode, oh-my-opencode, wsl, 설치, bun, nodejs

# OpenCode + Oh-My-OpenCode WSL 설치 가이드

## 자동 설치 (원클릭)
```bash
bash /var/www/html/install-opencode-wsl.sh
```

## 수동 설치

### 1. Node.js 설치
```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Bun 설치
```bash
curl -fsSL https://bun.sh/install | bash
source ~/.bashrc
```

### 3. OpenCode 설치
```bash
curl -fsSL https://opencode.ai/install | bash
```

### 4. Oh-My-OpenCode 설치
```bash
bunx oh-my-opencode install --no-tui --claude=yes --openai=no --gemini=yes --copilot=no --opencode-zen=yes --zai-coding-plan=yes
```

---

## [2] OpenCode 로컬 파일 위치 맵

**카테고리**: reference | **태그**: opencode, 설정, 파일위치, config

# OpenCode 로컬 파일 위치 맵

## 핵심 설정 파일
| 파일 | 경로 | 용도 |
|------|------|------|
| opencode.json | ~/.config/opencode/opencode.json | 메인 설정 |
| oh-my-opencode.json | ~/.config/opencode/oh-my-opencode.json | 프레임워크 설정 |
| providers.json | ~/.config/opencode/providers.json | API 키/프로바이더 |
| CLAUDE.md | 프로젝트루트/CLAUDE.md | 프로젝트별 에이전트 지침 |
| AGENTS.md | 프로젝트루트/AGENTS.md | 시스템 가이드 |

---

## [3] sknas205.ipdisk.co.kr 나스주소

**카테고리**: reference | **태그**: sknas205, 나스, 주소

sknas205.ipdisk.co.kr sknas205/sknas205204203

---

## [4] 네이버광고 대행사

**카테고리**: reference | **태그**: 네이버, 파워링크, 광고, 대행사, 수수료, 직접관리, searchad

## 광고대행사가 하는 일

1. 키워드 선정/관리
2. 입찰가 조정
3. 광고 소재(문구) 작성
4. 월간 리포트 제공

→ 솔직히 말하면, 큰 기술이 필요한 작업이 아님
→ 네이버가 제공하는 관리 도구로 사업자가 직접 가능

---

## 대행사 수수료

보통 광고비의 10~20% (또는 월 고정비)

월 900만원 광고비 기준:
- 대행사 수수료 15% = 월 135만원 = 연 1,620만원

이 돈이 별도로 나가고 있는지 확인 필요:
1. 광고비에 포함된 건지 (900만 중 135만이 수수료)
2. 광고비와 별도인지 (900만 + 수수료 별도)

---

## 직접 관리 방법

1. https://searchad.naver.com 접속
2. 사업자 네이버 아이디로 로그인
3. "광고 관리" → 키워드, 입찰가, 예산 직접 설정

### 네이버가 제공하는 것
- 키워드 도구 (추천 키워드, 검색량, 경쟁도)
- 자동 입찰 기능
- 실시간 성과 대시보드
- 무료 교육 영상/가이드

---

## 확인할 것 — 광고 계정 소유권

⚠️ 대행사가 "자기 계정"으로 광고를 돌리고 있으면:
- 계정 이관 요청 필요 (광고 이력 포함)
- 거부하면 새 계정 만들어야 함 (이력 초기화)

사장님 명의 계정이면:
- 대행사 권한만 해제하면 끝

---

## 대행사에 먼저 물어볼 것

1. **광고 계정이 내 명의인가, 대행사 명의인가?**
2. **수수료가 얼마인가? (광고비 포함인지 별도인지)**
3. **계정 이관이 가능한가?**

---

## [5] 두손기획인쇄 시스템 가이드 (AGENTS.md)

**카테고리**: reference | **태그**: 시스템, 가이드, AGENTS, 배포, 결제, 인증, 주문, 교정, 견적, 택배, 이메일, 챗봇, 영문, 마이그레이션

> 📋 이 문서는 AGENTS.md 전체 내용의 사본입니다. 원본 참조: [AGENTS.md](/AGENTS.md)

---

## [6] 두손기획인쇄 마스터 사양서 (Master Spec v1.0)

**카테고리**: reference | **태그**: 마스터, 사양서, 스펙, 아키텍처, 데이터흐름, 제품, 가격계산, 스티커

> 📋 전체 내용은 DB knowledge_base ID:6에서 확인. 품목 매핑, DB 스키마, 비즈니스 로직, 데이터 흐름 등 시스템 역공학 기반 기술 명세서.

---

## [7] 품목별주소

**카테고리**: general | **태그**: 품목

9개 품목별 주문 페이지 경로

| # | 품목명 | 폴더명 | 경로 (URL) |
|---|--------|--------|-----------|
| 1 | 스티커 | sticker_new | /mlangprintauto/sticker_new/index.php |
| 2 | 전단지 | inserted | /mlangprintauto/inserted/index.php |
| 3 | 명함 | namecard | /mlangprintauto/namecard/index.php |
| 4 | 봉투 | envelope | /mlangprintauto/envelope/index.php |
| 5 | 포스터 | littleprint | /mlangprintauto/littleprint/index.php |
| 6 | 상품권 | merchandisebond | /mlangprintauto/merchandisebond/index.php |
| 7 | 자석스티커 | msticker | /mlangprintauto/msticker/index.php |
| 8 | 카다록 | cadarok | /mlangprintauto/cadarok/index.php |
| 9 | NCR양식지 | ncrflambeau | /mlangprintauto/ncrflambeau/index.php |

프로덕션: https://dsp114.com/mlangprintauto/{폴더명}/index.php

---

## [8] dsp114.com

**카테고리**: general | **태그**: 도메인내용

WHOIS 정보. Registry: Tucows, 만료일: 2027-01-21, NS: NS1.GABIWEB.CO.KR / NS2.GABIWEB.CO.KR

---

## [9] 도메인 스왑 완벽 가이드 (dsp114.com ↔ dsp114.co.kr)

**카테고리**: workflow | **태그**: domain, dns, migration, plesk, deployment, 도메인스왑, gabia, 가비아

> 📋 전체 내용은 DB knowledge_base ID:9에서 확인. DNS 변경, Plesk 도메인 추가, SSL, 코드 수정 30개+파일, 시나리오 A(DNS만)/B(전체교체) 가이드.

---

## [10] 챗붓의 API 사용량

**카테고리**: general | **태그**: 사용량, API

Gemini API 사용량 확인 URL:

| 용도 | URL |
|------|-----|
| API 사용량 대시보드 | https://console.cloud.google.com/apis/dashboard?project=936644337362 |
| Gemini API 할당량/쿼터 | https://console.cloud.google.com/apis/api/generativelanguage.googleapis.com/quotas?project=936644337362 |
| API 키 관리 | https://console.cloud.google.com/apis/credentials?project=936644337362 |
| 결제/비용 | https://console.cloud.google.com/billing/01711-6745-9396-2062 |
| AI Studio (간편 확인) | https://aistudio.google.com/apikey |

현재 설정: Tier 1 유료, 1,500 RPD, rate limiter 300회/일 + IP당 20회/일

---

## [11] 이니시스 네이버 메일서버 국민은행 에스크로

**카테고리**: general | **태그**: 이니시스, 네이버 메일서버, 국민은행 에스크로

호스팅회사: ns1.cmshom.co.kr / 175.119.156.249

도메인 변경 작업 목록:
| # | 할 일 | 누가 | 상태 |
|---|-------|-----|------|
| 1 | https://dsp114.co.kr 프로덕션 정상 작동 확인 | 사장님 | ❓ |
| 2 | 이니시스 전화 (1588-4954) — MID dsp1147479에 dsp114.com 도메인 추가 | 사장님 | ❓ |
| 3 | KB에스크로 전화 — dsp114.com 도메인 추가 | 사장님 | ❓ |
| 4 | 호스팅업체 전화 — DNS A레코드 변경 | 사장님 | ❓ |
| 5 | Plesk SSL 발급 | 사장님/호스팅 | ❓ |
| 6 | sitemap.xml 일괄 교체 (46개) | 저 | DNS 전환 후 |
| 7 | 301 리다이렉트 설정 | 저/Plesk | DNS 전환 후 |
| 8 | git merge 브랜치 → main | 저 | 전환 완료 후 |

---

## [12] 도메인 변경 2

**카테고리**: general | **태그**: 도메인, 이니시스

도메인 변경 체크리스트 + 호스팅업체 요청 메시지.
dsp114.com과 dsp114.co.kr 같이 사용 후 추후 co.kr 분리 계획.
코드는 https://dsp114.com으로 미리 변경 완료.

---

## [13] 롤백시 깃허브

**카테고리**: general | **태그**: 도메인롤백

- git revert -m 1 <merge-commit> → merge 전체를 하나의 커밋으로 되돌림
- merge 전 main의 현재 위치(e655489)를 기억해두면 git reset --hard e655489로도 원복 가능
- 커밋 8a49a857: dsp114.com 도메인 동적 감지 (11파일)

---

## [14] KB에스크로 롤백문서 dsp114.com용

**카테고리**: general | **태그**: KB에스크로, dsp114.com용

변경된 파일 2개: right.htm (라인 111), includes/footer.php (라인 85)
- 구: ef04cec95f1a7298f1f686bfe3159ade (dsp114.co.kr용)
- 신: eb30fbb0bc1da7fdcaf800c0bceebbff201111241043905 (dsp114.com용)

롤백 방법:
① HTML 주석에서 복원
② git checkout HEAD~1 -- right.htm includes/footer.php
③ mHValue 수동 교체

---

## [15] 팝업관리

**카테고리**: general | **태그**: 명절, 휴가, 공지

팝업 관리 시스템:
- dashboard/popups/index.php — 관리자 UI (이미지 드래그&드롭, CRUD, 상태 배지)
- includes/popup_layer.php — 고객 레이어 팝업 (쿠키 기반 "안보기")
- dashboard/api/popups.php — API
- site_popups 테이블

흐름: 관리자 등록 → API → 고객 사이트 표시 → 쿠키 "오늘/7일/30일 안보기"

---

## [16] 네이버url 요청

**카테고리**: general | **태그**: 네이버검색

발견된 문제 5가지:
1. sitemap.php가 http:// URL 생성 (🔴 치명적)
2. 37개 .htm 파일 접근 가능 + 리다이렉트 없음 (🔴 치명적)
3. .htaccess에 HTTP→HTTPS 리다이렉트 없음 (🟠)
4. 구 도메인 리다이렉트 없음 (🟠)
5. 네이버 서치어드바이저에 https:// 등록 필요 (🟡)

해결: sitemap https 수정, .htm 301 리다이렉트, 서치어드바이저 등록

---

## [17] 결제알림 서비스

**카테고리**: general | **태그**: 알림, 결제, 서비스, 가입

문자(SMS) 발송 서비스 비교:
| 서비스 | 건당 비용 | 특징 |
|--------|----------|------|
| 알리고 | SMS 8.4원, LMS 25원 | 가장 저렴, PHP 예제 풍부 |
| 솔라피 | SMS 9원, LMS 27원 | REST API 깔끔 |
| NHN Cloud | SMS 9.9원, LMS 30원 | 대기업 안정성 |
| 카카오 알림톡 | 건당 6.7원 | 수신율 최고 |

준비물: 발신번호 등록(02-2632-1830), API 키, 충전금
구현: inicis_return.php 결제완료 후 SMS 호출 추가

---

## [18] 카톡알림 방법

**카테고리**: general | **태그**: 주문알림

카카오 알림톡 = 전화번호만 있으면 발송 가능 (회원 불필요).
카카오톡 미설치 시 자동 SMS 대체 발송.

필요 절차:
1. 카카오톡 채널 개설 (✅ 이미 완료)
2. 알림톡 발송 대행사 가입 (솔라피 or 알리고)
3. 대행사에서 카카오 채널 연동
4. 메시지 템플릿 등록 → 카카오 검수 1~2일
5. API 키 발급 → 코드 구현

---

## [19] 네비게이션 내용

**카테고리**: general | **태그**: 메뉴

📋 재질/옵션 심플 메뉴 구조:

스티커/라벨: 일반(아트유광/무광/비코팅/모조), 강접(강접/초강접), 특수(유포지/은데드롱/투명/크라프트)
전단지/리플렛: 90g~300g 아트/스노우/모조
명함/쿠폰: 일반(칼라코팅/비코팅/화이트모조), 고급수입지(15종+), 카드(화이트/실버/은펄/금펄/누드/골드)
봉투: 소봉투(100모조/레자크), 자켓봉투, 대봉투(120모조/레자크/크라프트)
카다록: 24절3단~20페이지 중철
포스터: 소량 120~300g 아트/스노우/모조
양식지: 양식/NCR 2~4매/거래명세표/빌지
상품권: 인쇄만/홀로그램박/넘버링/미싱선
자석스티커: 종이자석(90x60~13x18cm), 전체자석(38x55~50x50mm)

---

마지막 업데이트: 2026-03-09
