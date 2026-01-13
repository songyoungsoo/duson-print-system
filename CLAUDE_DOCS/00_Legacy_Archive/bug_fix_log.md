# 두손기획 버그 픽스 기록

## 2025-01-02

### checkboard.php 주소 필터 조건 제거

| 항목 | 내용 |
|------|------|
| **파일** | `sub/checkboard.php` |
| **커밋** | `360cd25` |
| **배포** | dsp1830.shop FTP 완료 |

**문제**:
- 교정보기(checkboard.php) 페이지에서 주소 정보가 없는 주문이 목록에 표시되지 않음
- 예: 주문 #84397 (zip, zip1 모두 빈값)

**원인**:
```php
// 라인 132 - 기존 코드
$where_conditions[] = "((zip IS NOT NULL AND zip != '') OR (zip1 IS NOT NULL AND zip1 != ''))";
```

**해결**:
```php
// 라인 132 - 수정 코드
$where_conditions[] = "1=1";
```

**관련 이슈**:
- dsp114.com에서 가져온 레거시 주문 데이터 중 주소가 비어있는 경우 있음
- 주문 #84397: date=2025-12-31, Type=유포지스티커, name=이수정

---
