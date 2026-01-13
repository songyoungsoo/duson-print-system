# VS Code에서 Claude Code 실행 가이드

## 설정 완료 항목

✅ VS Code Tasks 설정 (`.vscode/tasks.json`)
✅ VS Code 터미널 프로필 설정 (`.vscode/settings.json`)
✅ 데스크탑 배치 파일 업데이트 (`duson-code.bat`)

---

## 실행 방법

### 방법 1: VS Code Tasks (권장)

**단축키**: `Ctrl+Shift+P` → `Tasks: Run Task`

**사용 가능한 작업**:
1. **Claude Code (Skip Permissions)** - Claude Code만 실행
2. **Start Apache & MySQL** - 서버만 시작
3. **Stop Apache & MySQL** - 서버만 중지
4. **Start Development Environment** - 서버 + Claude Code 동시 실행

**빠른 실행**:
```
Ctrl+Shift+P → "run task" 입력 → "Claude Code (Skip Permissions)" 선택
```

---

### 방법 2: VS Code 터미널 프로필

1. VS Code에서 새 터미널 열기: `Ctrl+Shift+` ` (백틱)
2. 터미널 드롭다운 클릭 (오른쪽 + 버튼 옆)
3. **"Claude Code"** 프로필 선택
4. 자동으로 `--dangerously-skip-permissions` 모드로 실행

---

### 방법 3: 데스크탑 배치 파일

Windows 데스크탑에서:
- **duson-code.bat** 더블클릭
- 자동으로 서버 시작 + Claude Code 실행 (skip-permissions 모드)

---

## --dangerously-skip-permissions 모드란?

**기능**: 모든 도구 실행 시 사용자 승인 없이 자동 실행

**장점**:
- ✅ 빠른 작업 흐름
- ✅ 반복 작업 시 승인 스킵
- ✅ 자동화 가능

**주의사항**:
- ⚠️ 모든 파일 읽기/쓰기 자동 승인
- ⚠️ 모든 bash 명령 자동 실행
- ⚠️ 신뢰할 수 있는 프로젝트에서만 사용
- ⚠️ 프로덕션 환경에서는 사용 금지

---

## 터미널 단축키 설정 (선택사항)

`.vscode/keybindings.json` 생성:
```json
[
  {
    "key": "ctrl+shift+c",
    "command": "workbench.action.tasks.runTask",
    "args": "Claude Code (Skip Permissions)"
  }
]
```

이후 `Ctrl+Shift+C`로 Claude Code 바로 실행

---

## 문제 해결

### Q: "claude-code: command not found"
```bash
# WSL에서 확인
which claude-code
# 또는
which cc
```

### Q: sudo 비밀번호 계속 물어봄
```bash
# WSL에서 실행
sudo visudo
# 아래 줄 추가
ysung ALL=(ALL) NOPASSWD: /usr/sbin/service apache2 start
ysung ALL=(ALL) NOPASSWD: /usr/sbin/service mysql start
```

### Q: VS Code에서 작업이 안 보임
- VS Code 재시작
- `.vscode/tasks.json` 파일 열어보기
- `Ctrl+Shift+P` → "Tasks: Run Task" 확인

---

## 환경 변수 (자동 설정됨)

`.vscode/settings.json`에 추가된 환경 변수:
```json
"terminal.integrated.env.linux": {
  "CLAUDE_SKIP_PERMISSIONS": "true"
}
```

---

**Last Updated**: 2025-01-03
**Default Mode**: `--dangerously-skip-permissions` (개발 환경)
