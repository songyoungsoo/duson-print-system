# 🎨 Nano Image Generator Skill 설치 완료

> **설치 일시**: 2025-12-28
> **설치 위치**: `~/.claude/skills/nano-image-generator/`
> **버전**: Latest from GitHub

---

## ✅ 설치 완료

**nano-image-generator** skill이 성공적으로 설치되었습니다!

### 📍 설치 위치
```
~/.claude/skills/nano-image-generator/
├── SKILL.md
└── scripts/
    └── generate_image.py
```

---

## 🎯 기능 소개

**nano-image-generator**는 Google의 **Gemini 3 Pro Preview API** (일명 "Nano Banana Pro")를 사용하여 고품질 이미지를 생성하는 skill입니다.

### 주요 기능
- 🎨 AI 이미지 생성 (아이콘, 로고, 배너, 일러스트 등)
- 📐 다양한 종횡비 지원 (1:1, 16:9, 9:16 등)
- 🖼️ 해상도 선택 (1K, 2K, 4K)
- 🎭 스타일 커스터마이징

### 사용 사례
✅ 앱 아이콘 생성
✅ 마케팅 배너 제작
✅ UI 그래픽 디자인
✅ 소셜 미디어 이미지
✅ 일러스트레이션
✅ 다이어그램

---

## 🔑 API 키 설정 (필수!)

nano-image-generator를 사용하려면 **Gemini API 키**가 필요합니다.

### 1. API 키 발급

🔗 **발급 사이트**: https://aistudio.google.com/apikey

1. 위 링크 접속
2. Google 계정으로 로그인
3. "Create API Key" 클릭
4. API 키 복사

### 2. 환경 변수 설정

#### Windows (CMD):
```cmd
setx GEMINI_API_KEY "your-api-key-here"
```

#### Windows (PowerShell):
```powershell
[System.Environment]::SetEnvironmentVariable('GEMINI_API_KEY', 'your-api-key-here', 'User')
```

#### Linux/Mac (Bash):
```bash
# ~/.bashrc 또는 ~/.zshrc에 추가
export GEMINI_API_KEY="your-api-key-here"

# 적용
source ~/.bashrc
```

#### WSL2 (현재 환경):
```bash
# ~/.bashrc에 추가
echo 'export GEMINI_API_KEY="your-api-key-here"' >> ~/.bashrc
source ~/.bashrc
```

---

## 🚀 사용 방법

### 기본 사용법

Skill은 자연어로 요청하면 자동 활성화됩니다:

```
"로봇 마스코트 아이콘 만들어줘"
"16:9 비율로 마케팅 배너 생성해줘"
"앱 로고 디자인해줘"
```

### 직접 스크립트 실행

```bash
cd ~/.claude/skills/nano-image-generator

python scripts/generate_image.py "A friendly robot mascot waving" \
  --output ./mascot.png
```

---

## 📐 옵션 가이드

### 종횡비 (--aspect, -a)

| 비율 | 용도 | 예시 |
|------|------|------|
| **1:1** | 정사각형 (아이콘, 로고) | 앱 아이콘, 프로필 사진 |
| **16:9** | 가로 와이드 (배너, 썸네일) | YouTube 썸네일, 웹사이트 배너 |
| **9:16** | 세로 (모바일, 스토리) | Instagram 스토리, 모바일 스크린 |
| **3:2** | 가로 (사진) | 일반 사진, 풍경 |
| **2:3** | 세로 (포스터) | 영화 포스터, 세로형 디자인 |
| **21:9** | 울트라 와이드 | 영화 스크린, 파노라마 |

### 해상도 (--size, -s)

| 크기 | 용도 |
|------|------|
| **1K** | 빠른 프리뷰, 웹용 |
| **2K** | 기본값, 대부분의 용도 |
| **4K** | 고품질 인쇄, 대형 출력 |

---

## 💡 실전 예제

### 예제 1: 앱 아이콘 생성
```bash
python scripts/generate_image.py \
  "Minimalist flat design app icon of a lightning bolt, purple gradient background, modern iOS style" \
  --output ./assets/app-icon.png \
  --aspect 1:1
```

**결과**: 정사각형 앱 아이콘 (iOS/Android 스타일)

### 예제 2: 웹사이트 히어로 배너
```bash
python scripts/generate_image.py \
  "Professional website hero banner for a productivity app, abstract geometric shapes, blue and white color scheme, modern and clean" \
  --output ./public/images/hero-banner.png \
  --aspect 16:9
```

**결과**: 16:9 비율 웹사이트 배너

### 예제 3: 고해상도 일러스트
```bash
python scripts/generate_image.py \
  "Detailed isometric illustration of a cozy home office setup with plants, warm lighting, digital art style" \
  --output ./assets/illustrations/office.png \
  --size 4K
```

**결과**: 4K 고해상도 아이소메트릭 일러스트

### 예제 4: 소셜 미디어 스토리
```bash
python scripts/generate_image.py \
  "Vibrant gradient background with floating geometric shapes, perfect for Instagram story, modern and energetic" \
  --output ./social/instagram-story.png \
  --aspect 9:16
```

**결과**: Instagram/TikTok 스토리용 세로 이미지

### 예제 5: 로고 디자인
```bash
python scripts/generate_image.py \
  "Clean modern tech company logo with abstract mountain shape, blue and silver colors, minimalist design" \
  --output ./branding/logo.png \
  --aspect 1:1 \
  --size 4K
```

**결과**: 고해상도 로고 (인쇄/디지털 겸용)

---

## ✍️ 효과적인 프롬프트 작성 팁

### 1. 구체적으로 작성
❌ **나쁜 예**: "an apple"
✅ **좋은 예**: "A red apple on a wooden table with soft natural lighting"

### 2. 스타일 명시
❌ **나쁜 예**: "a robot"
✅ **좋은 예**: "A robot in pixel art style" / "A photorealistic robot"

### 3. 용도 언급
❌ **나쁜 예**: "cute illustration"
✅ **좋은 예**: "Cute illustration for a children's book"

### 4. 구도 설명
❌ **나쁜 예**: "landscape"
✅ **좋은 예**: "Mountain landscape, rule of thirds composition, centered horizon"

### 5. 색상 팔레트 지정
❌ **나쁜 예**: "colorful background"
✅ **좋은 예**: "Background with blue, purple, and pink gradient"

### 6. 피해야 할 것
❌ 이미지 안에 텍스트 요청 (나중에 오버레이로 추가)
❌ 너무 복잡한 장면 (여러 요소는 단순하게)
❌ 모호한 표현 ("아름다운", "멋진" 등)

---

## 🎨 프롬프트 템플릿

### 아이콘/로고
```
"[스타일] app icon of [주제], [색상] background, [분위기] style"

예시:
"Minimalist flat design app icon of a coffee cup, warm brown gradient background, modern iOS style"
```

### 배너/헤더
```
"[용도] banner for [목적], [요소들], [색상 팔레트], [분위기]"

예시:
"Website hero banner for a fitness app, abstract running figures, blue and orange color scheme, energetic and dynamic"
```

### 일러스트
```
"[스타일] illustration of [장면], [분위기], [추가 세부사항]"

예시:
"Isometric illustration of a modern office workspace, warm and inviting, with plants and natural light"
```

### 배경/텍스처
```
"[종류] background with [패턴/요소], [색상], perfect for [용도]"

예시:
"Abstract gradient background with geometric shapes, purple and blue tones, perfect for presentation slides"
```

---

## 📁 파일 구조 권장사항

이미지를 생성할 때 적절한 위치에 저장하세요:

```
project/
├── assets/
│   ├── icons/           # 아이콘
│   ├── images/          # 일반 이미지
│   └── illustrations/   # 일러스트
├── public/
│   ├── images/          # 웹 공개 이미지
│   └── icons/           # 웹 아이콘
├── marketing/           # 마케팅 자료
│   ├── banners/
│   └── social/
└── generated/           # 임시 생성 이미지
```

---

## 🔧 문제 해결

### "GEMINI_API_KEY environment variable not set"
**해결**: API 키를 환경 변수로 설정하세요 (위 섹션 참조)

### "ModuleNotFoundError"
**해결**: Python 3이 설치되어 있는지 확인
```bash
python3 --version
```

### "API rate limit exceeded"
**해결**: Gemini API 무료 티어 제한을 확인하세요. 잠시 기다렸다가 재시도.

### 생성된 이미지 품질이 낮음
**해결**:
- 프롬프트를 더 구체적으로 작성
- `--size 4K` 옵션 사용
- 스타일과 색상을 명시

---

## 🎯 Claude Code와 함께 사용

### 자연어로 요청 (자동 활성화)

```
"로봇 마스코트 아이콘 생성해줘, 보라색 그라데이션 배경"
→ nano-image-generator skill 자동 활성화

"16:9 비율로 웹사이트 배너 만들어줘"
→ 자동으로 적절한 옵션 적용

"고해상도 로고 디자인해줘"
→ --size 4K 옵션 자동 사용
```

### 활성화 트리거 키워드

다음 표현을 사용하면 skill이 자동 활성화됩니다:
- "이미지 생성해줘"
- "그래픽 만들어줘"
- "아이콘 디자인해줘"
- "로고 만들어줘"
- "배너 생성해줘"
- "일러스트 그려줘"

---

## 📊 비교: Anthropic vs Nano Skills

| 특징 | Anthropic Skills | Nano Image Generator |
|------|------------------|---------------------|
| **문서 생성** | ✅ docx, pdf, pptx, xlsx | ❌ |
| **이미지 생성** | ❌ | ✅ Gemini 3 Pro |
| **디자인** | ✅ canvas-design (제한적) | ✅ AI 생성 |
| **비용** | Claude API | Gemini API (무료 티어 있음) |
| **품질** | 디자인 철학 기반 | AI 생성, 고해상도 |

**추천 사용법**:
- **구조화된 디자인, 포스터**: canvas-design (Anthropic)
- **실제 이미지, 아이콘, 로고**: nano-image-generator

---

## 🌟 활용 시나리오

### 시나리오 1: 모바일 앱 개발
```bash
# 1. 앱 아이콘
"앱 아이콘 생성: 번개 모양, 보라색 그라데이션"

# 2. 스플래시 스크린
"스플래시 화면용 배경, 9:16, 추상적 기하학 패턴"

# 3. 온보딩 일러스트
"온보딩 일러스트 3장: 웰컴, 기능소개, 시작하기"
```

### 시나리오 2: 마케팅 캠페인
```bash
# 1. 소셜 미디어 포스트
"Instagram 피드용 정사각형 이미지, 제품 론칭"

# 2. 이메일 배너
"이메일 헤더 배너, 16:9, 프로모션 분위기"

# 3. 광고 크리에이티브
"Facebook 광고용 이미지, 다양한 사이즈"
```

### 시나리오 3: 웹사이트 구축
```bash
# 1. 히어로 섹션
"웹사이트 히어로 배너, 현대적, 기술 느낌"

# 2. 기능 아이콘
"기능 설명 아이콘 6개, 미니멀, 일관된 스타일"

# 3. 배경 패턴
"섹션 배경 텍스처, 서브틀한 그라데이션"
```

---

## 📈 다음 단계

### 1. API 키 설정
```bash
export GEMINI_API_KEY="your-api-key-here"
```

### 2. 첫 이미지 생성 테스트
```bash
cd ~/.claude/skills/nano-image-generator
python scripts/generate_image.py "A cute robot waving hello" --output test.png
```

### 3. Claude Code와 통합
```
"테스트용 로봇 아이콘 만들어줘"
```

### 4. 프로젝트에 적용
실제 프로젝트에서 필요한 이미지를 AI로 생성하여 디자인 시간 단축!

---

## 🔗 추가 리소스

- **GitHub 저장소**: https://github.com/livelabs-ventures/nano-skills
- **Gemini API 문서**: https://ai.google.dev/gemini-api/docs
- **API 키 발급**: https://aistudio.google.com/apikey

---

## ✅ 설치 확인

```bash
✓ nano-image-generator skill 설치 완료
✓ Python 스크립트 정상 작동
✓ SKILL.md 파일 존재
✓ Claude Code 자동 인식 준비 완료
```

---

**🎉 축하합니다! nano-image-generator skill 설치가 완료되었습니다!**

이제 Claude Code에게 "이미지 생성해줘"라고 요청하면 자동으로 이 skill을 사용하여 고품질 AI 이미지를 만들 수 있습니다.

---

*Installation Report Generated: 2025-12-28*
*Skill Source: https://github.com/livelabs-ventures/nano-skills*
*Installation Path: ~/.claude/skills/nano-image-generator/*
