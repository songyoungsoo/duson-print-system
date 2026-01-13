# MCP 빠른 활성화 가이드

## 현재 상태

**글로벌 활성 MCP 서버**:
- ✅ `context7` - 문서/라이브러리 검색
- ✅ `brave-search` - 웹 검색

**비활성화된 서버** (필요시 활성화):
- ⏸️ `sequential-thinking` - 복잡한 분석/추론
- ⏸️ `magic` - React/Vue UI 컴포넌트
- ⏸️ `playwright` - 브라우저 테스팅
- ⏸️ `serena` - 세션 메모리 관리
- ⏸️ `morphllm-fast-apply` - 빠른 코드 편집
- ⏸️ `tavily` - 웹 검색 (brave와 중복)

---

## 빠른 활성화 명령어

### 1. Sequential Thinking (복잡한 분석)
**사용자 요청**: `sequential thinking 켜줘`

```bash
# ~/.config/Claude/claude_desktop_config.json의 mcpServers에 추가
"sequential-thinking": {
  "type": "stdio",
  "command": "npx",
  "args": ["@modelcontextprotocol/server-sequential-thinking"],
  "env": {}
}
```

### 2. Magic (UI 컴포넌트)
**사용자 요청**: `magic 켜줘`

```bash
"magic": {
  "type": "stdio",
  "command": "npx",
  "args": ["@21st-dev/magic@latest"],
  "env": {}
}
```

### 3. Playwright (브라우저 테스팅)
**사용자 요청**: `playwright 켜줘`

```bash
"playwright": {
  "type": "stdio",
  "command": "npx",
  "args": ["@playwright/mcp@latest"],
  "env": {}
}
```

### 4. Serena (메모리 관리)
**사용자 요청**: `serena 켜줘`

```bash
"serena": {
  "type": "stdio",
  "command": "/home/ysung/.local/bin/uvx",
  "args": ["--from", "git+https://github.com/oraios/serena", "serena-mcp-server"],
  "env": {}
}
```

### 5. Morphllm (빠른 코드 편집)
**사용자 요청**: `morphllm 켜줘`

```bash
"morphllm-fast-apply": {
  "type": "stdio",
  "command": "npx",
  "args": ["@morphllm/mcp-server-fast-apply"],
  "env": {}
}
```

---

## 전체 복원 (모두 활성화)

**사용자 요청**: `mcp 전부 켜줘`

```bash
cp ~/.config/Claude/claude_desktop_config.json.backup ~/.config/Claude/claude_desktop_config.json
```

---

## 비활성화 (현재 상태로)

**사용자 요청**: `mcp 최소화해줘` 또는 `mcp 꺼줘`

```bash
# context7 + brave-search만 유지 (현재 상태)
```

---

## 설정 파일 위치

- **글로벌 설정**: `~/.config/Claude/claude_desktop_config.json`
- **백업 파일**: `~/.config/Claude/claude_desktop_config.json.backup`
- **비활성화 목록**: `~/.config/Claude/DISABLED_mcp_servers.json`
- **프로젝트 설정**: `.mcp.json` (현재 비어있음)

---

## 재시작 필요

MCP 설정 변경 후 **Claude Code 재시작** 필수:
1. Claude Code 종료 (Ctrl+C)
2. 재시작: `claude-code` 또는 `cc`
3. 확인: `/context`

---

**Last Updated**: 2025-01-03
**Default State**: context7 + brave-search (최소 구성)
