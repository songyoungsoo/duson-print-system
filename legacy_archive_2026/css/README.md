# Legacy CSS Archive 2026

## 아카이브 날짜
2026-01-14

## 아카이브 사유
CSS 아키텍처 재구조화 (SP - Style Protocol) 수립 과정에서 미사용/중복 CSS 파일 격리

## 포함된 파일 (15개, 5,629줄)

### 제품별 미참조 CSS
| 파일 | 줄 수 | 원래 위치 |
|------|-------|----------|
| `leaflet-compact.css` | 595 | `/mlangprintauto/inserted/css/` |
| `cadarok-extracted.css` | 574 | `/mlangprintauto/cadarok/css/` |
| `cadarok-inline-extracted.css` | 545 | `/mlangprintauto/cadarok/css/` |
| `sticker-clean-test.css` | 549 | `/mlangprintauto/sticker_new/` |
| `sticker_new-inline-extracted.css` | 147 | `/mlangprintauto/sticker_new/css/` |
| `quote-table.css` | 103 | `/mlangprintauto/sticker_new/` |
| `merchandisebond-inline-extracted.css` | 507 | `/mlangprintauto/merchandisebond/css/` |
| `ncrflambeau-compact.css` | 453 | `/mlangprintauto/ncrflambeau/css/` |
| `msticker-inline-extracted.css` | 437 | `/mlangprintauto/msticker/css/` |
| `envelope-inline-extracted.css` | 299 | `/mlangprintauto/envelope/css/` |
| `littleprint-inline-extracted.css` | 172 | `/mlangprintauto/littleprint/css/` |

### 레거시/기타
| 파일 | 줄 수 | 원래 위치 |
|------|-------|----------|
| `lastboard.css` | 229 | `/mlangprintauto/css/` |
| `gloxinia.css` | 193 | `/mlangprintauto/css/` |
| `calculator_modal.css` | 168 | `/mlangprintauto/quote/includes/` |
| `style - 복사본.css` | 59 | `/member/css/` |

## 복원 방법

필요시 원래 위치로 복사:
```bash
cp legacy_archive_2026/css/mlangprintauto/[product]/css/[file].css mlangprintauto/[product]/css/
```

## 주의사항

- 이 파일들은 현재 어떤 PHP 파일에서도 참조되지 않음
- `*-extracted.css` 파일들은 이전 인라인 스타일 추출 시도의 잔재
- `*-test.css` 파일은 테스트용으로 프로덕션에 포함되면 안 됨
- 새로운 SP(Style Protocol) 체계에서는 3계층 구조 사용 (base.css, product_layout.css, admin_custom.css)

---
*Archived as part of CSS Architecture Restructuring (SP Protocol)*
*Style Protocol Version: 1.0*
