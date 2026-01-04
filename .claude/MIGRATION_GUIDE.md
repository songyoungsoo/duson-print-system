# CLAUDE.md 최적화 마이그레이션 가이드

---

## 🎯 목표

- **Before**: CLAUDE.md 57k tokens (전체 컨텍스트의 20%)
- **After**: CLAUDE_CORE.md 5k tokens (90% 감소)
- **절감**: 52k tokens (컨텍스트 26% 확보)

---

## 📁 새로운 구조

```
/var/www/html/
├── CLAUDE_CORE.md                    # 5k - 핵심만 (새로 생성됨)
├── CLAUDE.md.backup_20250103         # 57k - 기존 백업
└── .claude/
    ├── guides/
    │   ├── git-workflow.md           # 1.2k - Git 규칙
    │   ├── upload-system.md          # 1.4k - 파일 업로드
    │   └── gallery-system.md         # (작성 예정)
    └── changelog/
        ├── 2025-12.md                # 1.5k - 최신 변경사항
        └── 2025-11.md                # (작성 예정)
```

---

## 🔄 마이그레이션 절차

### 1단계: 백업 및 교체
```bash
# 기존 CLAUDE.md 백업
cp /var/www/html/CLAUDE.md /var/www/html/CLAUDE.md.backup_20250103

# Core 버전으로 교체
cp /var/www/html/CLAUDE_CORE.md /var/www/html/CLAUDE.md

# 검증
wc -w /var/www/html/CLAUDE.md
# Expected: ~1000 words (vs 11000 before)
```

### 2단계: 분리 파일 작성 (선택적)
```bash
# Git 규칙 확인 필요 시
cat /var/www/html/.claude/guides/git-workflow.md

# 업로드 시스템 확인 필요 시
cat /var/www/html/.claude/guides/upload-system.md

# 최신 변경사항 확인 필요 시
cat /var/www/html/.claude/changelog/2025-12.md
```

### 3단계: 세션 재시작
```bash
# Claude Code 재시작하여 새 CLAUDE.md 로드
# 컨텍스트 사용량 확인: 289k → 237k 예상
```

---

## 📋 참조 방법 (새 규칙)

### ❌ 이전 방식
```
모든 내용이 CLAUDE.md에 포함
→ 항상 57k tokens 로드됨
```

### ✅ 새 방식
```
# Core만 자동 로드 (5k)
CLAUDE.md

# 필요 시 명시적 요청
"Git 규칙을 확인해줘"
→ Read /var/www/html/.claude/guides/git-workflow.md

"최신 변경사항 보여줘"
→ Read /var/www/html/.claude/changelog/2025-12.md

"파일 업로드 시스템 설명해줘"
→ Read /var/www/html/.claude/guides/upload-system.md
```

---

## 🧪 검증 체크리스트

### Core CLAUDE.md 필수 내용 포함 확인
- [ ] Project Identity (두손기획)
- [ ] 환경 정보 (WSL2, PHP 7.4, MySQL)
- [ ] Critical Rules (bind_param, 테이블명 소문자)
- [ ] 11개 제품 코드
- [ ] 빠른 시작 (서버, Git, FTP)
- [ ] 상세 문서 링크

### 분리 파일 작성 완료 확인
- [x] git-workflow.md (1.2k)
- [x] upload-system.md (1.4k)
- [x] changelog/2025-12.md (1.5k)
- [ ] gallery-system.md (작성 예정)
- [ ] changelog/2025-11.md (작성 예정)

### 토큰 절감 확인
- [ ] Before: 289k tokens (145%)
- [ ] After: ~237k tokens (118%)
- [ ] 절감: 52k tokens (26%)

---

## 🔴 롤백 방법 (문제 발생 시)

```bash
# 기존 CLAUDE.md 복원
cp /var/www/html/CLAUDE.md.backup_20250103 /var/www/html/CLAUDE.md

# Claude Code 재시작
```

---

## 📝 추가 작업 (선택)

### gallery-system.md 작성 (추천)
```bash
# 갤러리 관련 내용 분리
# - Dual-Source Gallery System
# - proof_gallery.php 구조
# - 9개 카테고리 매핑
```

### changelog/2025-11.md 작성 (선택)
```bash
# 11월 변경사항 아카이브
# - 롤스티커 계산기
# - 갤러리 모달 수정
# - 도메인 자동 감지
```

---

*Migration Date: 2025-01-03*
*Created by: Claude Code Optimization*
