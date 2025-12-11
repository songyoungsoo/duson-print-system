#!/bin/bash
# 매일 dsp114.com(EUC-KR) → 로컬(UTF-8) 주문 데이터 동기화
# crontab: 0 2 * * * /var/www/html/scripts/daily_sync_orders.sh >> /var/log/order_sync.log 2>&1

# 설정
REMOTE_HOST="dsp114.com"
REMOTE_USER="duson1830"
REMOTE_PASS="du1830"
REMOTE_DB="duson1830"

LOCAL_USER="dsp1830"
LOCAL_PASS="ds701018"
LOCAL_DB="dsp1830"

TEMP_DIR="/tmp/order_sync"
DATE=$(date +%Y%m%d_%H%M%S)

echo "=== 동기화 시작: $(date) ==="

# 임시 디렉토리 생성
mkdir -p $TEMP_DIR

# 1. 로컬 DB의 마지막 no 확인
LAST_NO=$(mysql -u$LOCAL_USER -p$LOCAL_PASS -N -e "SELECT COALESCE(MAX(no), 0) FROM mlangorder_printauto WHERE no < 90000;" $LOCAL_DB 2>/dev/null)
echo "로컬 마지막 no (90000 이하): $LAST_NO"

# 2. 원격 서버에서 새 데이터만 추출 (EUC-KR)
# mysqldump 사용 또는 PHP 스크립트로 추출
cat > $TEMP_DIR/export_new.php << 'PHPEOF'
<?php
// 원격 서버에서 실행할 PHP (EUC-KR 환경)
$last_no = isset($argv[1]) ? intval($argv[1]) : 0;
$db = mysql_connect("localhost", "duson1830", "du1830");
mysql_select_db("duson1830", $db);

$query = "SELECT * FROM MlangOrder_PrintAuto WHERE no > $last_no AND date >= '2024-01-01' ORDER BY no";
$result = mysql_query($query, $db);

$fields = array('no','Type','ImgFolder','Type_1','money_1','money_2','money_3','money_4','money_5',
                'name','email','zip','zip1','zip2','phone','Hendphone','delivery','bizname',
                'bank','bankname','cont','date','OrderStyle','ThingCate','pass','Gensu','Designer');

while ($row = mysql_fetch_assoc($result)) {
    $values = array();
    foreach ($fields as $f) {
        $val = isset($row[$f]) ? $row[$f] : '';
        $val = mysql_real_escape_string($val);
        $values[] = "'$val'";
    }
    echo "INSERT IGNORE INTO mlangorder_printauto (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ");\n";
}
mysql_close($db);
?>
PHPEOF

# 3. 원격 서버에서 데이터 추출 (SSH 또는 HTTP 방식)
# 방법 A: HTTP API 방식 (권장)
curl -s "http://$REMOTE_HOST/sync/export_orders.php?last_no=$LAST_NO&key=SYNC_SECRET_KEY" \
    -o $TEMP_DIR/new_orders_euckr.sql

# 4. EUC-KR → UTF-8 변환
iconv -f EUC-KR -t UTF-8 $TEMP_DIR/new_orders_euckr.sql > $TEMP_DIR/new_orders_utf8.sql 2>/dev/null

# 5. 로컬 DB에 삽입
if [ -s $TEMP_DIR/new_orders_utf8.sql ]; then
    BEFORE_COUNT=$(mysql -u$LOCAL_USER -p$LOCAL_PASS -N -e "SELECT COUNT(*) FROM mlangorder_printauto;" $LOCAL_DB 2>/dev/null)
    
    mysql -u$LOCAL_USER -p$LOCAL_PASS $LOCAL_DB < $TEMP_DIR/new_orders_utf8.sql 2>/dev/null
    
    AFTER_COUNT=$(mysql -u$LOCAL_USER -p$LOCAL_PASS -N -e "SELECT COUNT(*) FROM mlangorder_printauto;" $LOCAL_DB 2>/dev/null)
    
    NEW_RECORDS=$((AFTER_COUNT - BEFORE_COUNT))
    echo "새로 추가된 레코드: $NEW_RECORDS개"
else
    echo "새 데이터 없음"
fi

# 6. 임시 파일 정리
rm -rf $TEMP_DIR

echo "=== 동기화 완료: $(date) ==="
