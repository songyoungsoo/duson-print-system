# 팝업 관리 시스템

관리자 대시보드에서 레이어 팝업을 등록·관리하고, 고객 사이트에 자동으로 표시하는 시스템.

## 구조

```
관리자: /dashboard/popups/         → 팝업 등록/수정/삭제/활성 토글
API:    /dashboard/api/popups.php  → CRUD (list, create, update, delete, toggle)
DB:     site_popups 테이블          → 자동 생성 (첫 API 호출 시)
이미지: /ImgFolder/popups/          → 업로드된 팝업 이미지
프론트: /includes/popup_layer.php   → 고객 사이트 레이어 팝업 렌더링
```

## DB 스키마 (site_popups)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | INT AUTO_INCREMENT | PK |
| title | VARCHAR(200) | 팝업 제목 (선택) |
| image_path | VARCHAR(500) | 이미지 경로 (/ImgFolder/popups/...) |
| link_url | VARCHAR(500) | 클릭 시 이동 URL |
| link_target | VARCHAR(10) | _blank 또는 _self |
| start_date | DATE | 표시 시작일 |
| end_date | DATE | 표시 종료일 |
| is_active | TINYINT(1) | 활성 여부 (0/1) |
| hide_option | VARCHAR(20) | today/week/month |
| sort_order | INT | 정렬순서 (낮을수록 먼저) |

## 팝업 표시 로직 (popup_layer.php)

1. `site_popups`에서 `is_active=1 AND CURDATE() BETWEEN start_date AND end_date` 조회
2. 쿠키 `popup_hide_{id}` 체크 → 설정된 팝업은 스킵
3. **여러 팝업 동시 표시**: 오버레이 1개 안에 카드 겹쳐 배치 (24px 오프셋)
4. 각 팝업 독립적으로 닫기 가능, 모든 팝업 닫으면 오버레이 자동 제거
5. "안보기" 버튼 → 쿠키 설정 (today=1일, week=7일, month=30일)

## 관리자 UI 기능 (dashboard/popups/)

- 이미지 드래그&드롭 업로드 (JPG/PNG/GIF/WEBP, 최대 5MB)
- 제목, 링크 URL, 시작일/종료일, 닫기 옵션, 정렬순서 설정
- 상태 배지: 표시중(녹색), 예약됨(파란색), 종료(회색), 비활성(회색)
- 활성/비활성 토글 스위치
- 수정/삭제

## 파일 목록

| 파일 | 용도 |
|------|------|
| `dashboard/popups/index.php` | 관리자 팝업 관리 UI |
| `dashboard/api/popups.php` | 팝업 CRUD API |
| `includes/popup_layer.php` | 고객 사이트 레이어 팝업 |
| `includes/welcome_popup.php` | (레거시) 기존 하드코딩 팝업 — 교체됨 |

## 주의사항

- `index.php` 1388행에서 `popup_layer.php`를 include (기존 `welcome_popup.php` 교체)
- `popup_layer.php`는 `$db` 변수가 필요 → `mysqli_close($db)` 전에 include해야 함
- 이미지 파일명: `popup_YYYYMMDD_HHMMSS_랜덤6자.확장자`
- 팝업 삭제 시 이미지 파일도 함께 삭제됨
