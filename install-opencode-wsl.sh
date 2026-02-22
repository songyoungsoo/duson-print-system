#!/usr/bin/env bash
# ============================================================
# OpenCode + Oh-My-OpenCode WSL 원클릭 설치 스크립트
# 
# 사용법:  bash install-opencode-wsl.sh
# 
# 설치 항목:
#   1. 시스템 패키지 (curl, unzip, git)
#   2. Node.js 22 LTS (nvm 경유)
#   3. Bun (JavaScript runtime)
#   4. OpenCode CLI
#   5. Oh-My-OpenCode (에이전트 하네스)
#
# 구독 설정: Claude Pro/Max, Gemini, OpenCode Zen, Z.ai
# ============================================================

set -euo pipefail

# ── 색상 정의 ──────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# ── 유틸리티 함수 ──────────────────────────────────────────
info()    { echo -e "${BLUE}[INFO]${NC}  $*"; }
success() { echo -e "${GREEN}[✔]${NC}    $*"; }
warn()    { echo -e "${YELLOW}[⚠]${NC}    $*"; }
error()   { echo -e "${RED}[✖]${NC}    $*"; }
step()    { echo -e "\n${CYAN}${BOLD}━━━ $* ━━━${NC}\n"; }

# ── 환경 확인 ──────────────────────────────────────────────
if [[ ! -f /proc/version ]] || ! grep -qi 'microsoft\|wsl' /proc/version 2>/dev/null; then
    warn "WSL 환경이 아닌 것 같습니다. 계속 진행합니다..."
fi

# ── 1단계: 시스템 패키지 ───────────────────────────────────
step "1/5  시스템 패키지 확인 및 설치"

NEED_APT=false
for pkg in curl unzip git; do
    if ! command -v "$pkg" &>/dev/null; then
        warn "$pkg 미설치 → 설치 예정"
        NEED_APT=true
    else
        success "$pkg 이미 설치됨 ($(command -v $pkg))"
    fi
done

if $NEED_APT; then
    info "apt 패키지 설치 중..."
    sudo apt-get update -qq
    sudo apt-get install -y -qq curl unzip git ca-certificates
    success "시스템 패키지 설치 완료"
fi

# ── 2단계: Node.js (nvm 경유) ──────────────────────────────
step "2/5  Node.js 확인 및 설치 (v22 LTS)"

export NVM_DIR="${HOME}/.nvm"

# nvm 로드 (이미 설치된 경우)
if [[ -s "$NVM_DIR/nvm.sh" ]]; then
    source "$NVM_DIR/nvm.sh"
fi

install_node() {
    if ! command -v nvm &>/dev/null; then
        info "nvm 설치 중..."
        curl -fsSL https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
        export NVM_DIR="${HOME}/.nvm"
        source "$NVM_DIR/nvm.sh"
        success "nvm 설치 완료"
    fi

    info "Node.js 22 LTS 설치 중..."
    nvm install 22
    nvm use 22
    nvm alias default 22
    success "Node.js $(node --version) 설치 완료"
    success "npm $(npm --version) 사용 가능"
}

if command -v node &>/dev/null; then
    NODE_VER=$(node --version | sed 's/v//' | cut -d. -f1)
    if (( NODE_VER >= 18 )); then
        success "Node.js $(node --version) 이미 설치됨 ✓"
    else
        warn "Node.js $(node --version) → v18+ 필요. 업그레이드합니다..."
        install_node
    fi
else
    install_node
fi

# ── 3단계: Bun ─────────────────────────────────────────────
step "3/5  Bun 런타임 설치"

if command -v bun &>/dev/null; then
    success "Bun $(bun --version) 이미 설치됨 ✓"
else
    info "Bun 설치 중..."
    curl -fsSL https://bun.sh/install | bash

    # 현재 세션에 PATH 추가
    export BUN_INSTALL="${HOME}/.bun"
    export PATH="${BUN_INSTALL}/bin:${PATH}"

    if command -v bun &>/dev/null; then
        success "Bun $(bun --version) 설치 완료"
    else
        error "Bun 설치 실패. 수동 설치 필요: https://bun.sh"
        exit 1
    fi
fi

# ── 4단계: OpenCode CLI ───────────────────────────────────
step "4/5  OpenCode CLI 설치"

if command -v opencode &>/dev/null; then
    OC_VER=$(opencode --version 2>/dev/null || echo "unknown")
    success "OpenCode $OC_VER 이미 설치됨 ✓"
    info "최신 버전으로 업데이트 시도..."
    curl -fsSL https://opencode.ai/install | bash || true
else
    info "OpenCode 설치 중..."
    curl -fsSL https://opencode.ai/install | bash

    # PATH에 추가 (설치 위치 탐색)
    for p in "$HOME/.local/bin" "$HOME/.opencode/bin" "/usr/local/bin"; do
        if [[ -x "$p/opencode" ]]; then
            export PATH="$p:$PATH"
            break
        fi
    done

    if command -v opencode &>/dev/null; then
        success "OpenCode $(opencode --version 2>/dev/null) 설치 완료"
    else
        error "OpenCode 설치 후 PATH에서 찾을 수 없습니다."
        warn "터미널을 재시작하거나 'source ~/.bashrc' 후 다시 시도하세요."
        exit 1
    fi
fi

# ── 5단계: Oh-My-OpenCode ─────────────────────────────────
step "5/5  Oh-My-OpenCode 설치 및 구성"

info "구독 설정: Claude=yes, Gemini=yes, OpenCode-Zen=yes, Z.ai=yes"
info "oh-my-opencode 인스톨러 실행 중..."

bunx oh-my-opencode install \
    --no-tui \
    --claude=yes \
    --openai=no \
    --gemini=yes \
    --copilot=no \
    --opencode-zen=yes \
    --zai-coding-plan=yes

success "Oh-My-OpenCode 설치 완료"

# ── 설치 검증 ──────────────────────────────────────────────
step "설치 검증"

echo ""
echo -e "${BOLD}설치된 도구 버전:${NC}"
echo -e "  Node.js:   $(node --version 2>/dev/null || echo '미설치')"
echo -e "  npm:       $(npm --version 2>/dev/null || echo '미설치')"
echo -e "  Bun:       $(bun --version 2>/dev/null || echo '미설치')"
echo -e "  OpenCode:  $(opencode --version 2>/dev/null || echo '미설치')"
echo ""

# opencode.json 확인
OC_CONFIG="$HOME/.config/opencode/opencode.json"
if [[ -f "$OC_CONFIG" ]]; then
    if grep -q "oh-my-opencode" "$OC_CONFIG" 2>/dev/null; then
        success "opencode.json에 oh-my-opencode 플러그인 등록 확인 ✓"
    else
        warn "opencode.json에 oh-my-opencode가 등록되지 않았습니다."
        warn "수동 확인: cat $OC_CONFIG"
    fi
else
    warn "opencode.json 파일이 없습니다. opencode 첫 실행 시 생성될 수 있습니다."
fi

# ── PATH 영구 설정 ─────────────────────────────────────────
SHELL_RC="$HOME/.bashrc"
if [[ -n "${ZSH_VERSION:-}" ]] || [[ "$SHELL" == *zsh* ]]; then
    SHELL_RC="$HOME/.zshrc"
fi

PATH_ADDITIONS=""

# Bun PATH
if ! grep -q 'BUN_INSTALL' "$SHELL_RC" 2>/dev/null; then
    PATH_ADDITIONS+=$'\n# Bun\nexport BUN_INSTALL="$HOME/.bun"\nexport PATH="$BUN_INSTALL/bin:$PATH"\n'
fi

if [[ -n "$PATH_ADDITIONS" ]]; then
    echo "$PATH_ADDITIONS" >> "$SHELL_RC"
    info "PATH 설정이 $SHELL_RC에 추가되었습니다."
fi

# ── 완료 안내 ──────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}${BOLD}║         설치 완료! 🎉                                    ║${NC}"
echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BOLD}다음 단계 — 인증 설정 (필수):${NC}"
echo ""
echo -e "  ${CYAN}1. Claude 인증:${NC}"
echo -e "     ${BOLD}opencode auth login${NC}"
echo -e "     → Provider: Anthropic 선택"
echo -e "     → Login method: Claude Pro/Max 선택"
echo -e "     → 브라우저에서 OAuth 완료"
echo ""
echo -e "  ${CYAN}2. Google Gemini 인증 (Antigravity):${NC}"
echo -e "     ${BOLD}opencode auth login${NC}"
echo -e "     → Provider: Google 선택"
echo -e "     → Login method: OAuth with Google (Antigravity)"
echo -e "     → 브라우저에서 로그인 완료"
echo ""
echo -e "  ${CYAN}3. OpenCode Zen 인증:${NC}"
echo -e "     ${BOLD}opencode auth login${NC}"
echo -e "     → Provider: OpenCode Zen 선택"
echo ""
echo -e "  ${CYAN}4. Z.ai Coding Plan 인증:${NC}"
echo -e "     ${BOLD}opencode auth login${NC}"
echo -e "     → Provider: Z.ai 선택"
echo ""
echo -e "${BOLD}사용법:${NC}"
echo -e "  ${BOLD}cd /your/project && opencode${NC}  ← TUI 시작"
echo -e "  프롬프트에 ${YELLOW}ultrawork${NC} 또는 ${YELLOW}ulw${NC} 포함 → 자동 오케스트레이션"
echo -e "  ${YELLOW}Tab${NC} 키 → Prometheus(플래너) 모드 진입"
echo ""
echo -e "${YELLOW}⚠ 터미널을 재시작하거나 다음 명령 실행:${NC}"
echo -e "  ${BOLD}source $SHELL_RC${NC}"
echo ""
