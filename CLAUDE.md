# CLAUDE.md (CORE)

---

## 🏢 Project Identity

**Duson Planning Print System (두손기획인쇄)** - PHP 7.4 기반 인쇄 주문 관리 시스템

### 환경 정보
- **OS**: Linux (WSL2 Ubuntu) / Windows XAMPP
- **Web Server**: Apache 2.4+
- **PHP**: 7.4+
- **Database**: MySQL 5.7+ (utf8mb4)
- **Document Root**: `/var/www/html`
- **Domains**: localhost (dev) / dsp1830.shop (staging) / dsp1830.shop (prod)

### 긴급 접속 정보
```
관리자: duson1830 / du1830
DB: dsp1830 / ds701018
FTP: dsp1830 / ds701018
WSL sudo: 3305
GitHub: songyoungsoo / yeongsu32@gmail.com
```

---

## 🔴 CRITICAL RULES (절대 규칙)

### 1. bind_param 검증 (3번 검증 필수)
```php
// ❌ NEVER: 눈으로 대충 세기
mysqli_stmt_bind_param($stmt, "issss...", ...);

// ✅ ALWAYS: 3번 검증
$placeholder_count = substr_count($query, '?');  // 1
$type_count = strlen($type_string);             // 2
$var_count = 7; // 손으로 세기                   // 3

if ($placeholder_count === $type_count && $type_count === $var_count) {
    mysqli_stmt_bind_param($stmt, $type_string, ...);
}
```

### 2. Database 규칙
- **테이블명**: 항상 소문자 (`mlangprintauto_namecard`)
- **연결 변수**: `$db` (legacy는 `$conn = $db;` alias)
- **Character Set**: utf8mb4

### 3. quantity_display 검증 규칙 (필수)
```php
// ❌ NEVER: quantity_display를 단위 체크 없이 그대로 사용
$line2 = implode(' / ', [$spec_sides, $item['quantity_display'], $spec_design]);

// ✅ ALWAYS: 단위가 없으면 formatQuantity() 호출
$quantity_display = $item['quantity_display'] ?? '';

// 단위 체크: 매, 연, 부, 권, 개, 장
if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = $this->formatQuantity($item);
}

$line2 = implode(' / ', [$spec_sides, $quantity_display, $spec_design]);
```

**이유**:
- DB에 `quantity_display = "1"`처럼 단위 없이 저장될 수 있음
- `formatQuantity()`는 `MY_amount=1000` → "1,000매" 자동 변환
- 천 단위 변환 로직 포함 (봉투/명함: `MY_amount < 10`이면 ×1000)

**적용 위치**:
- `ProductSpecFormatter::formatStandardized()` (lines 71-83)
- `ProductSpecFormatter::buildLine2()` (lines 323-331)
- 모든 수량 표시 로직

### 4. 파일명 규칙
- **All lowercase**: `cateadmin_title.php` (NOT `CateAdmin_title.php`)
- **Includes**: 소문자 경로만 사용 (Linux case-sensitive)
- **No symlinks**: 실제 디렉토리만 사용

### 4. 환경 자동 감지
```php
// db.php가 자동 감지
- localhost → $admin_url = "http://localhost"
- dsp1830.shop → $admin_url = "http://dsp1830.shop"
- dsp1830.shop → $admin_url = "http://dsp1830.shop"
```

---

## 📦 11개 제품 코드

| Code | Name | Directory |
|------|------|-----------|
| inserted | 전단지 | mlangprintauto/inserted/ |
| namecard | 명함 | mlangprintauto/namecard/ |
| envelope | 봉투 | mlangprintauto/envelope/ |
| sticker | 스티커 | mlangprintauto/sticker_new/ |
| msticker | 자석스티커 | mlangprintauto/msticker/ |
| cadarok | 카다록 | mlangprintauto/cadarok/ |
| **littleprint** | **포스터** ⚠️ | mlangprintauto/littleprint/ |
| merchandisebond | 상품권 | mlangprintauto/merchandisebond/ |
| ncrflambeau | NCR양식 | mlangprintauto/ncrflambeau/ |
| leaflet | 리플렛 | mlangprintauto/leaflet/ |

⚠️ **AI 주의**: `littleprint` = 포스터 (레거시 코드명, 변경 금지)

---

## 🚀 빠른 시작

### 서버 시작
```bash
sudo service apache2 start
sudo service mysql start
http://localhost/
```

### Git 워크플로우 (자동 스테이징)
```bash
# Claude가 작업 완료 시 자동 수행
git add .

# 사용자 확인 후
git status
git commit -m "메시지"
git push origin main
```

### FTP 배포 (프로덕션)
```bash
curl -T "file.php" -u "dsp1830:ds701018" \
  "ftp://dsp1830.shop/path/file.php"
```

### 핵심 파일 위치
```
/var/www/html/
├── db.php                              # DB 연결 & 환경 자동 감지
├── config.env.php                      # 환경 설정
├── includes/
│   ├── auth.php                        # 인증 (8시간 세션)
│   ├── StandardUploadHandler.php      # 파일 업로드 표준
│   └── ImagePathResolver.php          # 파일 경로 해석
├── mlangprintauto/[product]/
│   ├── index.php                       # 제품 페이지
│   ├── add_to_basket.php              # 장바구니 API
│   └── calculate_price_ajax.php       # 가격 API
└── mlangorder_printauto/
    ├── ProcessOrder_unified.php        # 주문 처리
    └── OrderComplete_universal.php     # 주문 완료
```

---

## 📚 상세 문서 참조

**이 파일은 핵심만 포함합니다. 상세 내용은:**

| 주제 | 파일 |
|------|------|
| Git 규칙 | `.claude/guides/git-workflow.md` |
| 업로드 시스템 | `.claude/guides/upload-system.md` |
| 갤러리 시스템 | `.claude/guides/gallery-system.md` |
| Recent Fixes | `.claude/changelog/2025-12.md` |
| 비즈니스 규칙 | `~/.claude/skills/duson-print-rules/` |
| MCP 가이드 | `CLAUDE_DOCS/05_DEVELOPMENT/MCP_Installation_Guide.md` |
| 전체 문서 | `CLAUDE_DOCS/INDEX.md` |

---

## ⚠️ Common Pitfalls (자주 하는 실수)

1. ❌ bind_param 개수 불일치 → 주문자 이름 '0' 저장
2. ❌ 대문자 테이블명 사용 → SELECT 실패
3. ❌ 대문자 include 경로 → Linux에서 파일 못 찾음
4. ❌ number_format(0.5) → "1" 반올림 오류
5. ❌ `littleprint`를 `poster`로 변경 → 시스템 전체 오류
6. ❌ colgroup 개수 ≠ 실제 컬럼 개수 → 오른쪽 빈 공란 발생

---

*Core Version - Last Updated: 2026-01-07*
*Environment: WSL2 Ubuntu + Windows XAMPP*
*Full Docs: CLAUDE_DOCS/ | Changelog: .claude/changelog/*
