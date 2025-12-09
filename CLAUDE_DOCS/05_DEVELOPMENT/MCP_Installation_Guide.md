# 🔌 MCP (Model Context Protocol) 설치 및 설정 가이드

## 📋 목차
1. [개요](#개요)
2. [공통 주의사항](#공통-주의사항)
3. [환경별 설정](#환경별-설정)
4. [설치 절차](#설치-절차)
5. [검증 절차](#검증-절차)
6. [설정 파일 관리](#설정-파일-관리)
7. [트러블슈팅](#트러블슈팅)

---

## 개요

**MCP (Model Context Protocol)**는 Claude Code에서 외부 도구 및 서비스와 통합할 수 있게 해주는 프로토콜입니다.

### 현재 환경
- **OS**: WSL2 Ubuntu on Windows
- **Working Directory**: `/var/www/html`
- **WSL sudo 패스워드**: `3305`

---

## 공통 주의사항

### 🔴 필수 확인 사항

1. **환경 확인**
   - 현재 OS 및 환경 파악 (Windows, Linux, macOS)
   - 터미널 환경 확인 (WSL, PowerShell, CMD)
   - 불확실하면 **사용자에게 반드시 질문**

2. **설치 전 검증**
   - Node.js가 PATH에 등록되어 있는지 확인
   - Node.js 버전 v18 이상 확인: `node -v`
   - npm/npx 사용 가능 여부 확인

3. **설치 범위**
   - **User 스코프**로 설치 및 적용
   - 요청받은 MCP만 설치 (기존 MCP 에러 무시)

4. **공식 문서 우선**
   - WebSearch로 공식 사이트 확인
   - Context7 MCP로 재확인 (사용 가능한 경우)
   - OS 및 환경에 맞는 설치법 적용

5. **검증 필수**
   - 설치 후 작동 여부 반드시 확인
   - Task 에이전트를 통한 디버그 모드 검증
   - `/mcp` 명령어로 실제 작동 확인

6. **API 키 관리**
   - 가상 API 키로 기본 설치
   - 올바른 API 키 입력 필요성 사용자에게 안내

7. **서버 의존성 인지**
   - MySQL MCP 등 특정 서버 구동 필요한 경우
   - 에러 발생해도 재설치하지 않음
   - 정상 구동 조건을 사용자에게 안내

---

## 환경별 설정

### WSL2 / Linux / macOS 환경

**설정 파일 위치**:
- **User 설정**: `~/.claude/`
- **Project 설정**: `{프로젝트 루트}/.claude/`

**경로 표기**:
- Unix 스타일 슬래시 사용: `/home/user/.claude/`
- JSON 내 이스케이프 불필요

**WSL sudo 패스워드**: `3305`

### Windows 네이티브 환경

**설정 파일 위치**:
- **User 설정**: `C:\Users\{사용자명}\.claude\`
- **Project 설정**: `{프로젝트 루트}\.claude\`

**⚠️ 중요 - 경로 표기**:
```json
// ❌ 잘못된 예
"command": "C:\Program Files\nodejs\node.exe"

// ✅ 올바른 예 (백슬래시 이스케이프)
"command": "C:\\Program Files\\nodejs\\node.exe"
```

**환경 변수 사용**:
```json
{
  "env": {
    "UV_DEPS_CACHE": "%TEMP%\\uvcache"
  }
}
```

---

## 설치 절차

### 1단계: mcp-installer를 통한 기본 설치

```bash
# mcp-installer 사용
mcp-installer
```

### 2단계: 설치 확인

```bash
# 설치 목록 확인
claude mcp list
```

### 3단계: 작동 여부 검증

```bash
# Task 에이전트로 디버그 모드 구동
# 최대 2분 관찰 후 디버그 메시지 확인
claude --debug

# /mcp 명령어로 실제 작동 확인
echo "/mcp" | claude --debug
```

### 4단계: 문제 발생 시 직접 설치

#### User 스코프 직접 설정

```bash
# YouTube MCP 예시
claude mcp add --scope user youtube-mcp \
  -e YOUTUBE_API_KEY=$YOUR_YT_API_KEY \
  -e YOUTUBE_TRANSCRIPT_LANG=ko \
  -- npx -y youtube-data-mcp-server
```

### 5단계: 재확인

```bash
# 설치 목록 확인
claude mcp list

# 디버그 모드로 작동 검증
claude --debug

# /mcp 명령어로 실제 확인
echo "/mcp" | claude --debug
```

### 6단계: 공식 방법으로 재설치

npm/npx 패키지를 찾을 수 없는 경우:

```bash
# npm 전역 설치 경로 확인
npm config get prefix

# 패키지 직접 설치 (npm/pip/uvx)
npm install -g [package-name]
```

uvx 명령어를 찾을 수 없는 경우:

```bash
# uv 설치 (Python 패키지 관리자)
curl -LsSf https://astral.sh/uv/install.sh | sh
```

### 7단계: 터미널 작동 확인 후 설정 파일 직접 수정

터미널에서 작동 성공 시, 성공한 인자 및 환경 변수를 활용해 JSON 설정 파일에 MCP 직접 설정

---

## 검증 절차

### ✅ 필수 검증 단계

**모든 설치/설정 후 다음을 반드시 실행**:

```bash
# 1. 설치 목록 확인
claude mcp list

# 2. 디버그 모드 구동 (2분 관찰)
claude --debug

# 3. MCP 작동 확인
echo "/mcp" | claude --debug
```

### 검증 성공 기준

- `claude mcp list`에 설치한 MCP 표시
- 디버그 모드에서 에러 메시지 없음
- `/mcp` 명령어로 도구 목록 정상 표시

---

## 설정 파일 관리

### 설정 파일 위치

**WSL/Linux/macOS**:
- User: `~/.claude/mcp_config.json`
- Project: `{프로젝트}/.claude/mcp_config.json`

**Windows**:
- User: `C:\Users\{사용자}\.claude\mcp_config.json`
- Project: `{프로젝트}\.claude\mcp_config.json`

### 설정 예시

#### 1. npx 사용 (기본)

```json
{
  "youtube-mcp": {
    "type": "stdio",
    "command": "npx",
    "args": ["-y", "youtube-data-mcp-server"],
    "env": {
      "YOUTUBE_API_KEY": "YOUR_API_KEY_HERE",
      "YOUTUBE_TRANSCRIPT_LANG": "ko"
    }
  }
}
```

#### 2. cmd.exe 래퍼 (Windows)

```json
{
  "mcpServers": {
    "mcp-installer": {
      "command": "cmd.exe",
      "args": ["/c", "npx", "-y", "@anaisbetts/mcp-installer"],
      "type": "stdio"
    }
  }
}
```

#### 3. PowerShell 사용 (Windows)

```json
{
  "command": "powershell.exe",
  "args": [
    "-NoLogo",
    "-NoProfile",
    "-Command",
    "npx -y @anaisbetts/mcp-installer"
  ]
}
```

#### 4. node 직접 지정

```json
{
  "command": "node",
  "args": [
    "%APPDATA%\\npm\\node_modules\\@anaisbetts\\mcp-installer\\dist\\index.js"
  ]
}
```

### args 배열 설계 체크리스트

1. **토큰 단위 분리**
   ```json
   // ✅ 올바른 예
   "args": ["/c", "npx", "-y", "pkg"]

   // ❌ 잘못된 예 (따옴표 처리 문제 가능)
   "args": ["/c", "npx -y pkg"]
   ```

2. **경로 포함 시 이스케이프** (Windows)
   ```json
   "args": ["C:\\tools\\mcp\\server.js"]
   ```

3. **환경변수 전달**
   ```json
   "env": {
     "UV_DEPS_CACHE": "%TEMP%\\uvcache"
   }
   ```

4. **타임아웃 조정**
   ```json
   "env": {
     "MCP_TIMEOUT": "10000"  // 10초
   }
   ```

---

## 트러블슈팅

### 문제 1: npm/npx를 찾을 수 없음

**해결책**:
```bash
# npm 전역 설치 경로 확인
npm config get prefix

# PATH에 npm 경로 추가
export PATH="$PATH:$(npm config get prefix)/bin"
```

### 문제 2: uvx 명령어를 찾을 수 없음

**해결책**:
```bash
# uv 설치
curl -LsSf https://astral.sh/uv/install.sh | sh

# PATH 업데이트
source ~/.bashrc
```

### 문제 3: MCP 서버 시작 실패

**확인 사항**:
1. Node.js 버전이 v18 이상인지 확인
2. 필요한 환경 변수가 설정되어 있는지 확인
3. API 키가 올바른지 확인
4. 타임아웃 값 증가 (`MCP_TIMEOUT`)

### 문제 4: JSON 설정 파일 오류

**확인 사항**:
1. Windows 경로는 백슬래시 이스케이프 (`\\`)
2. JSON 문법 검증 (쉼표, 중괄호)
3. 환경변수 포맷 확인

### 문제 5: MySQL MCP 에러

**해결책**:
- MySQL 서버가 구동 중인지 확인
- 재설치하지 말고 정상 구동 조건 안내
- 서버 실행: `sudo service mysql start` (WSL/Linux)

---

## 참고 자료

### 공식 문서
- [MCP Official Documentation](https://modelcontextprotocol.io/)
- [Claude Code Documentation](https://docs.claude.ai/code)

### 관련 파일
- 프로젝트 설정: `/var/www/html/.claude/`
- User 설정: `~/.claude/`

---

*Last Updated: 2025-10-08*
*Environment: WSL2 Ubuntu*
*Working Directory: /var/www/html*
