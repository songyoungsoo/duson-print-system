# Duson Print System - Claude Code Configuration

프로젝트별 Claude Code 설정 및 커스텀 스킬

## MCP 서버 구성

`.claude/mcp.json`에서 다음 MCP 서버들이 활성화되어 있습니다:

### 🧠 sequential-thinking
복잡한 PHP/MySQL 디버깅과 추론에 사용
- 다단계 논리 분석
- 버그 원인 추적
- 아키텍처 설계 검토

### 📚 context7
PHP, MySQL, JavaScript 문서 및 패턴 참조
- 프레임워크 문서 조회
- 베스트 프랙티스 검색
- 코드 패턴 참조

**사용 시 필요**: `CONTEXT7_API_KEY` 환경변수 설정

### 🔄 morphllm
대량 코드 변환 및 패턴 적용
- 여러 PHP 파일 동시 수정
- 일관된 패턴 적용
- 대규모 리팩토링

### 📋 shrimp-task-manager
복잡한 기능 개발 시 작업 계획 및 관리
- 작업 분해 및 추적
- 의존성 관리
- 진행상황 모니터링

### 🎭 playwright
브라우저 자동화 및 E2E 테스트
- 실제 브라우저 환경 테스트
- 사용자 워크플로우 검증
- 스크린샷 및 에러 캡처

### 📄 doc-ops
문서 처리 및 변환 (PDF, DOCX, XLSX, PPTX)
- 문서 형식 변환 (PDF ↔ DOCX ↔ HTML ↔ Markdown)
- 텍스트 추출 및 분석
- 웹 스크래핑 및 문서 자동화

### 📋 mcp-pdf
PDF 파일 전문 처리
- PDF 병합/분할
- 텍스트 추출
- 폼 필드 처리
- 전자 서명 관리

## 커스텀 스킬

`.claude/skills/` 디렉토리의 프로젝트 전용 스킬들:

### /plan - 기능 계획 수립 📋
TDD 기반 단계별 기능 개발 계획 생성

```bash
# 사용법
/plan

# 또는
"전단지 할인 기능 개발 계획 수립해줘"
```

**기능**:
- 3-7단계 phase 기반 계획
- Red-Green-Refactor TDD 워크플로우
- 품질 게이트 체크리스트
- 단계별 롤백 전략
- 테스트 커버리지 목표 설정
- 리스크 평가 및 완화 전략
- `docs/plans/PLAN_<feature>.md` 생성

**계획 크기**:
- Small (2-3 phases, 3-6시간)
- Medium (4-5 phases, 8-15시간)
- Large (6-7 phases, 15-25시간)

### /e2e - E2E 브라우저 테스트 🎭
Playwright를 이용한 실제 브라우저 테스트

```bash
# 사용법
/e2e

# 또는
"전단지 페이지 E2E 테스트 실행해줘"
```

**테스트 시나리오**:
- 제품 페이지 워크플로우 (옵션 선택 → 가격 계산 → 장바구니)
- 주문 제출 플로우 (장바구니 → 체크아웃 → 완료)
- 파일 업로드 시스템
- 갤러리 인터랙션
- 관리자 패널 기능

**에러 감지**:
- 콘솔 에러 모니터링
- 네트워크 실패 추적
- 실패 시 스크린샷 캡처
- DOM 상태 검증

### /deploy - FTP 배포
파일을 dsp1830.shop 스테이징 서버로 업로드

```bash
# 사용법
/deploy

# 또는 Claude에게
"이 파일 스테이징에 배포해줘"
```

**기능**:
- curl을 이용한 FTP 업로드
- 디렉토리 자동 생성
- 업로드 후 테스트 URL 제공

### /check-bindings - bind_param 검증 🔴
mysqli_stmt_bind_param 정확성 검증 (데이터 무결성 핵심!)

```bash
# 사용법
/check-bindings

# 또는
"bind_param 검증해줘"
```

**검증 항목**:
- SQL 쿼리의 `?` 개수
- 타입 문자열 길이
- 실제 파라미터 개수
- 3가지 모두 일치하는지 확인

### /test - 제품 페이지 테스트
제품 페이지 E2E 기능 테스트

```bash
# 사용법
/test

# 또는
"전단지 페이지 테스트해줘"
```

**테스트 항목**:
- 파일 구조 확인
- 데이터베이스 테이블 확인
- 가격 계산 AJAX 테스트
- 장바구니 추가 테스트
- 데이터 저장 검증

### /backup - DB 백업
MySQL 데이터베이스 백업

```bash
# 사용법
/backup

# 또는
"데이터베이스 백업해줘"
```

**옵션**:
- 전체 백업
- 특정 테이블만
- 제품 테이블만

**백업 위치**: `/var/www/html/backups/{YYYYMMDD}/`

### /commit - Git 커밋
표준화된 형식으로 Git 커밋

```bash
# 사용법
/commit

# 또는
"변경사항 커밋해줘"
```

**커밋 메시지 형식**:
```
{type}({scope}): {description}

🤖 Generated with Claude Code
Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

**타입**:
- feat: 새 기능
- fix: 버그 수정
- refactor: 리팩토링
- style: 스타일 변경
- docs: 문서
- perf: 성능 개선

## 환경 변수

MCP 서버 사용을 위해 필요한 환경변수:

```bash
# Context7 API (선택)
export CONTEXT7_API_KEY="your-api-key-here"
```

## 사용 예시

### 1. 새 기능 개발 워크플로우 (TDD)
```bash
# 1. 상세 계획 수립
/plan
"전단지 페이지에 할인 쿠폰 기능 추가 계획해줘"
# → docs/plans/PLAN_discount-coupon.md 생성

# 2. Phase 1: 테스트 작성 (RED)
"할인 계산 테스트 케이스 작성해줘"

# 3. Phase 2: 구현 (GREEN)
"테스트 통과하도록 할인 로직 구현해줘"

# 4. Phase 3: 리팩토링 (REFACTOR)
"코드 개선하면서 테스트 유지해줘"

# 5. bind_param 검증
/check-bindings

# 6. E2E 테스트
/e2e
"할인 쿠폰 적용 E2E 테스트 실행해줘"

# 7. 커밋
/commit

# 8. 배포
/deploy
```

### 2. 버그 수정 워크플로우
```bash
# 1. 복잡한 버그 분석 (sequential-thinking)
"장바구니에 가격이 0으로 저장되는 문제 분석해줘"

# 2. bind_param 검증
/check-bindings

# 3. 수정 후 테스트
/test

# 4. 커밋
/commit
```

### 3. 대량 리팩토링
```bash
# 1. DB 백업
/backup

# 2. morphllm으로 패턴 적용
"모든 제품 페이지에 새 갤러리 시스템 적용해줘"

# 3. 전체 테스트
"모든 제품 페이지 테스트해줘"

# 4. 커밋
/commit
```

## 디버깅

MCP 서버 동작 확인:
```bash
claude --debug
echo "/mcp" | claude --debug
```

스킬 목록 확인:
```bash
ls -la .claude/skills/
```

## 참고 문서

- 프로젝트 문서: `CLAUDE_DOCS/`
- 전체 가이드: `CLAUDE.md`
- MCP 설치: `CLAUDE_DOCS/05_DEVELOPMENT/MCP_Installation_Guide.md`

---

*Last Updated: 2025-12-24*
*MCP Servers: 7 active (sequential-thinking, context7, morphllm, shrimp-task-manager, playwright, doc-ops, mcp-pdf)*
*Custom Skills: 7 (/plan, /e2e, /deploy, /check-bindings, /test, /backup, /commit)*
*Plugins: 4 installed (php-lsp, code-review, commit-commands, security-guidance)*
