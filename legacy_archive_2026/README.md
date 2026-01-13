# Legacy Archive 2026

이 디렉토리는 시스템 현대화 과정에서 격리된 레거시 파일들을 보관합니다.

## 아카이브 날짜
2026-01-14

## 포함된 파일

### admin/mlangprintauto/
| 파일명 | 원래 용도 | 대체 파일 |
|--------|---------|----------|
| `admin251113.php` | 관리자 페이지 (날짜 백업) | `admin.php` |
| `orderlist250925.php` | 주문 목록 (날짜 백업) | `orderlist.php` |
| `orderlist251113.php` | 주문 목록 (날짜 백업) | `orderlist.php` |
| `inserted/inserted_admin251108.php` | 전단지 관리 (날짜 백업) | `inserted_admin.php` |

## 복원 방법

파일 복원이 필요한 경우:
```bash
cp legacy_archive_2026/admin/mlangprintauto/[파일명] admin/mlangprintauto/
```

## 주의사항

- 이 폴더의 파일들은 현재 시스템과 호환되지 않을 수 있습니다
- 복원 전 반드시 백업을 수행하세요
- 새로운 SSOT 시스템(`QuantityFormatter`, `ProductSpecFormatter`)과 충돌 가능

---
*Archived as part of [공표] System Modernization Directive*
