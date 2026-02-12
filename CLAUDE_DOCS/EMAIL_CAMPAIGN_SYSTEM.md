# 이메일 캠페인 시스템 상세 기록

> 작성일: 2026-02-12
> 작업 세션: 2026-02-12 (WYSIWYG 에디터 추가 완료)

## 1. 프로젝트 배경

### 목적
- 두손기획인쇄 회원 328명에게 새 홈페이지(dsp114.co.kr) 오픈 안내
- 구정 인사 + 2월 23일 공식 오픈 안내 2단계 발송 계획
- 향후 지속적 회원 커뮤니케이션을 위한 이메일 발송 인프라 구축

### 발송 계획
| 단계 | 날짜 | 내용 |
|------|------|------|
| 1차 | 2/13 (구정 전) | 설날 인사 + 새 홈페이지 미리 안내 |
| 2차 | 2/23 (오픈일) | 새 홈페이지 정식 오픈 안내 |

---

## 2. 회원 데이터 분석

### 총 회원 수: 334명
- **유효 수신자**: 328명 (고유 이메일 기준)
- 제외: admin 1명, test 1명, 중복 이메일 4명

### 이메일 도메인 분포
| 도메인 | 회원 수 | 비율 |
|--------|---------|------|
| naver.com | 193 | 58.8% |
| hanmail.net | 37 | 11.3% |
| gmail.com | 28 | 8.5% |
| daum.net | 14 | 4.3% |
| nate.com | 12 | 3.7% |
| hotmail.com | 6 | 1.8% |
| 기타 | 38 | 11.6% |

### 오타 이메일 (수정 필요)
| 현재 이메일 | 추정 올바른 도메인 |
|-------------|-------------------|
| `*@nate.ocm` | `nate.com` |
| `*@naver.vom` | `naver.com` |
| `*@naver.coml` | `naver.com` |
| `*@naver.co.kr` | `naver.com` (naver.co.kr는 존재하지 않음) |

### 로그인 현황
- **로그인 이력 있음**: 39명 (11.9%)
- **미로그인**: 289명 (88.1%) — 구 사이트에서 마이그레이션된 회원
- 봇 계정: 0건 (탐지 안 됨)

---

## 3. 네이버 SMTP 제한 조사

### 현재 설정
- 호스트: `smtp.naver.com:465/ssl`
- 계정: `dsp1830@naver.com`
- 앱 비밀번호: `2CP3P5BTS83Y` (2025-11-19 이후 필수)

### 발송 제한
| 항목 | 제한 |
|------|------|
| 1회 수신자 | 최대 100명 |
| 일일 한도 | ~500통 (안전 기준) |
| 배치 간격 | 3초 이상 권장 |
| Gmail 수신 | 스팸 분류 가능성 있음 |

### 328명 발송 시 예상
- 배치 수: 4회 (100+100+100+28)
- 소요 시간: ~2분 (배치 간 3초 대기 포함)

---

## 4. 구 사이트 vs 신 사이트 비교

### dsp114.com (구)
- PHP 5.2, EUC-KR, HTML 4.01
- 고정 990px 레이아웃, 모바일 미지원
- jQuery 1.4.4, 프레임 기반 메뉴
- 결제: 없음 (전화/팩스 주문)

### dsp114.co.kr (신)
- PHP 7.4, UTF-8, HTML5
- 반응형 디자인, 모바일 지원
- Tailwind CSS, 현대적 JS
- KG이니시스 결제 연동
- 교정 갤러리, 견적서 시스템
- 관리자 대시보드

### 상세 비교 문서
- `CLAUDE_DOCS/dsp114_old_site_analysis.md` — 구 사이트 분석
- `CLAUDE_DOCS/dsp114_detailed_page_comparison.md` — 페이지별 상세 비교
- `CLAUDE_DOCS/dsp114_migration_summary.md` — 이전 요약

---

## 5. 시스템 구현 상세

### 파일 구조
```
dashboard/
├── email/
│   ├── index.php          ← 메인 UI (WYSIWYG 에디터 + 3탭)
│   └── uploads/           ← 이미지 업로드 저장 디렉토리
├── api/
│   └── email.php          ← API (12개 action)
└── includes/
    └── config.php         ← 사이드바 메뉴 (📧 이메일 발송 추가됨)
```

### DB 테이블 스키마

#### `email_campaigns`
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | INT AUTO_INCREMENT | PK |
| subject | VARCHAR(255) | 이메일 제목 |
| body_html | LONGTEXT | HTML 본문 |
| recipient_type | ENUM('all','filtered','manual') | 수신자 유형 |
| recipient_filter | JSON | 필터 조건 (filtered일 때) |
| recipient_emails | TEXT | 직접 입력 이메일 (manual일 때) |
| total_recipients | INT | 총 수신자 수 |
| sent_count | INT DEFAULT 0 | 성공 발송 수 |
| fail_count | INT DEFAULT 0 | 실패 수 |
| status | ENUM('draft','sending','completed','failed','cancelled') | 상태 |
| started_at | DATETIME | 발송 시작 |
| completed_at | DATETIME | 발송 완료 |
| created_by | VARCHAR(50) | 생성자 |
| created_at | TIMESTAMP | 생성일 |
| updated_at | TIMESTAMP | 수정일 |

#### `email_send_log`
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | INT AUTO_INCREMENT | PK |
| campaign_id | INT | FK → email_campaigns.id |
| recipient_email | VARCHAR(255) | 수신 이메일 |
| recipient_name | VARCHAR(100) | 수신자 이름 |
| status | ENUM('pending','sent','failed') | 발송 상태 |
| error_message | TEXT | 실패 시 에러 메시지 |
| sent_at | DATETIME | 발송 시각 |

#### `email_templates`
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | INT AUTO_INCREMENT | PK |
| name | VARCHAR(100) | 템플릿 이름 |
| subject | VARCHAR(255) | 이메일 제목 |
| body_html | LONGTEXT | HTML 본문 |
| created_at | TIMESTAMP | 생성일 |
| updated_at | TIMESTAMP | 수정일 |

### WYSIWYG 에디터 구현

#### 3가지 모드
1. **편집기 (wysiwyg)**: `contenteditable` div + 서식 도구모음
2. **HTML편집 (html)**: raw textarea (기존 방식)
3. **미리보기 (preview)**: 렌더링된 HTML

#### 도구모음 버튼
| 버튼 | 명령 | 설명 |
|------|------|------|
| B | bold | 굵게 |
| I | italic | 기울임 |
| U | underline | 밑줄 |
| H1 | formatBlock h1 | 제목1 |
| H2 | formatBlock h2 | 제목2 |
| P | formatBlock p | 본문 |
| 🔗 | createLink | 링크 삽입 (URL 프롬프트) |
| 📷 | upload_image API | 이미지 업로드 + img 태그 삽입 |
| • 목록 | insertUnorderedList | 글머리 기호 목록 |
| 1. 목록 | insertOrderedList | 번호 목록 |
| ─ | insertHorizontalRule | 구분선 |
| 색 | foreColor | 글자색 (color picker) |
| ✕서식 | removeFormat | 서식 제거 |

#### 모드 전환 동기화
- wysiwyg → html: `textarea.value = wysiwygDiv.innerHTML`
- html → wysiwyg: `wysiwygDiv.innerHTML = textarea.value`
- 발송/저장 시: `getEmailBody()` 헬퍼가 자동 동기화

### 이미지 업로드 흐름
```
📷 클릭 → hidden file input 트리거
→ 파일 선택 (5MB, JPG/PNG/GIF/WebP)
→ FormData POST /dashboard/api/email.php?action=upload_image
→ 서버: /dashboard/email/uploads/{timestamp}_{random}.{ext} 저장
→ 응답: { url: "http://localhost/dashboard/email/uploads/..." }
→ WYSIWYG: execCommand('insertHTML', '<img src="...">')
→ HTML모드: textarea에 img 태그 삽입
```

### 기본 템플릿 2개 (DB에 INSERT 완료)

#### 1. 설날 인사
- 2026 구정 인사말
- 새 홈페이지 미리 안내
- dsp114.co.kr 링크

#### 2. 새 홈페이지 오픈
- 2월 23일 정식 오픈 안내
- 주요 변경사항 목록
- 특별 할인 안내 (빈칸 — 사장님이 채울 예정)

### 이메일 푸터 (고정)
```html
<p style="...">
두손기획인쇄 | 서울특별시 영등포구 영등포로 36길9 송호빌딩 1층 두손기획인쇄 | Tel. 02-2632-1830
</p>
<p style="...">
본 메일은 두손기획인쇄 회원님께 발송됩니다. 수신을 원하지 않으시면 <a href="#">여기</a>를 클릭해주세요.
</p>
```

---

## 6. 미완료 작업

### 즉시 필요
- [ ] 프로덕션 배포 (dsp114.co.kr FTP → `/httpdocs/dashboard/email/`, `/httpdocs/dashboard/api/email.php`)
- [ ] 프로덕션 DB에 3개 테이블 생성 (email_campaigns, email_send_log, email_templates)
- [ ] 프로덕션 DB에 기본 템플릿 2개 INSERT
- [ ] 오타 이메일 4건 수정 (users 테이블)

### 발송 전 확인
- [ ] dsp1830@naver.com으로 테스트 발송 확인
- [ ] 이미지 업로드 동작 확인
- [ ] 배너 이미지 제작 (사장님 직접)

### 향후 개선
- [ ] Gmail SMTP 이중 발송 (네이버 스팸 우회)
- [ ] 수신 거부 기능 구현 (unsubscribe)
- [ ] 발송 예약 기능
- [ ] 이메일 열람 추적 (tracking pixel)
