#!/bin/bash
cd /var/www/html/admin
find . -name '*.php' -type f -print0 | xargs -0 sed -i 's/mysqli_affected_rows()/mysqli_affected_rows($db)/g'
echo "완료"
