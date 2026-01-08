# cart.php 파일 보호 시스템

## ⚠️ 중요 경고

이 파일은 **5~6번 수정 후 자동으로 원점 복귀되는 문제**가 있었습니다.

### 근본 원인
- `cart_backup_20251216.php` 파일이 자동으로 복원됨
- 백업 파일에는 전단지 "X연 (Y매)" 표시 로직이 없음

### 해결 방법

1. **백업 파일 이동**
   ```bash
   # 오래된 백업은 archive/ 폴더로 이동됨
   ls archive/
   ```

2. **보호된 백업 사용**
   ```bash
   # 올바른 버전은 backups/ 폴더에 보관
   ls backups/cart_protected_*.php
   ```

3. **파일 감시**
   ```bash
   # 수동으로 파일이 변경되었는지 확인
   ./monitor_cart.sh
   ```

4. **복원 방법** (만약 다시 문제가 발생하면)
   ```bash
   cp backups/cart_protected_20251217_021153.php cart.php
   git checkout mlangprintauto/shop/cart.php
   ```

### 올바른 수정 절차

1. **Git에서 확인**
   ```bash
   git status mlangprintauto/shop/cart.php
   ```

2. **수정 후 즉시 커밋**
   ```bash
   git add mlangprintauto/shop/cart.php
   git commit -m "fix: cart.php 수정"
   git push origin main
   ```

3. **프로덕션 배포**
   ```bash
   curl -T cart.php -u "dsp1830:ds701018" "ftp://dsp1830.shop/mlangprintauto/shop/cart.php"
   ```

4. **확인**
   ```bash
   ./monitor_cart.sh
   ```

### 체크섬
현재 올바른 버전: `fc4bb242853caa17e28c5c381f629514`

### Git 커밋
- [57aff6b] fix: 장바구니 전단지 수량 표시 통일 - X연 (Y매) 형식

### 마지막 수정
- 날짜: 2025-12-17 02:07
- 작업: 전단지/리플렛 수량을 "X연 (Y매)" 형식으로 표시
- 파일 크기: 75K
