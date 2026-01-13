# Git Workflow Guide

---

## 📋 Git 저장소 규칙 (2025-12-10 확정)

### 🔴 핵심 원칙: 코드만 저장!

**GitHub 저장소**: https://github.com/songyoungsoo/duson-print-system

### 👤 Git 계정 정보

| 항목 | 값 |
|------|-----|
| **GitHub 저장소** | `git@github.com:songyoungsoo/duson-print-system.git` |
| **사용자명** | `songyoungsoo` |
| **이메일** | `yeongsu32@gmail.com` ✅ |
| **설정 파일** | `/var/www/html/.git/config` |

### 📦 .gitignore 규칙

| 항목 | 포함 여부 | 이유 |
|-----|---------|-----|
| **PHP 소스 코드** | ✅ 포함 | 핵심 코드 |
| **JavaScript/CSS** | ✅ 포함 | 프론트엔드 코드 |
| **설정 파일** | ✅ 포함 | 시스템 설정 |
| **문서 (md)** | ✅ 포함 | 개발 문서 |
| **이미지 (jpg, png, gif)** | ❌ 제외 | 대용량, 별도 관리 |
| **업로드 폴더** | ❌ 제외 | 사용자 데이터 |
| **SQL 덤프** | ❌ 제외 | 민감 정보/대용량 |

### 🤖 Claude 자동 Git 규칙 (필수!)

**⚠️ 모든 코딩 작업 완료 시 자동 수행:**
```bash
git add .
```

- 작업 끝나면 무조건 `git add .` 실행
- .gitignore가 대용량 파일 자동 제외
- 사용자 확인 없이 자동 스테이징 (커밋은 사용자 결정)

### ✅ Git 워크플로우

```bash
# 1. [자동] 작업 완료 후 스테이징 (Claude가 항상 수행)
git add .

# 2. 상태 확인
git status

# 3. 커밋 (사용자 요청 시)
git commit -m "설명"

# 4. 푸시
git push origin main
```

---

## 🔐 Git Safety Protocol

**NEVER:**
- ❌ Update git config
- ❌ Force push to main/master
- ❌ Skip hooks (--no-verify)
- ❌ Commit without user request

**ALWAYS:**
- ✅ Create feature branches for work
- ✅ Run `git status` at session start
- ✅ Verify before commit (`git diff`)
- ✅ Use descriptive commit messages

---

## 📝 Commit Message Format

```bash
git commit -m "$(cat <<'EOF'
Commit message here.

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
EOF
)"
```

---

*Loaded only when: Git operations needed*
