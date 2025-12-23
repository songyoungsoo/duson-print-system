#!/bin/bash
# cart.php 파일 감시 스크립트
# 파일이 변경되면 올바른 버전인지 확인하고 알림

CART_FILE="/var/www/html/mlangprintauto/shop/cart.php"
CHECKSUM_FILE="/var/www/html/mlangprintauto/shop/backups/cart_checksum.txt"
PROTECTED_BACKUP="/var/www/html/mlangprintauto/shop/backups/cart_protected_20251217_021153.php"

# 현재 체크섬 확인
CURRENT_MD5=$(md5sum "$CART_FILE" | awk '{print $1}')
EXPECTED_MD5=$(cat "$CHECKSUM_FILE" | awk '{print $1}')

if [ "$CURRENT_MD5" != "$EXPECTED_MD5" ]; then
    echo "⚠️ WARNING: cart.php가 변경되었습니다!"
    echo "현재: $CURRENT_MD5"
    echo "예상: $EXPECTED_MD5"
    echo ""
    echo "변경 내용 확인:"
    diff "$PROTECTED_BACKUP" "$CART_FILE" | head -20
    echo ""
    echo "복원하려면: cp $PROTECTED_BACKUP $CART_FILE"
else
    echo "✅ cart.php 정상 - 변경 없음"
fi
