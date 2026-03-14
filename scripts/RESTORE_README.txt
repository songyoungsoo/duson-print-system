============================================================
  두손기획인쇄 — 재난 복구 가이드
  Duson Planning Print System — Disaster Recovery Guide
============================================================

※ 이 문서는 사이트가 완전히 망가졌을 때,
   처음부터 다시 만드는 방법을 설명합니다.
   개발자가 아니어도 따라할 수 있습니다.

============================================================
  준비물
============================================================

1. 새 서버 (Ubuntu 22.04 이상 권장)
   - Plesk 또는 일반 Ubuntu 서버
   - 최소 10GB 디스크 여유

2. NAS 접속 정보 (둘 중 하나만 있으면 됩니다)
   - 1차 NAS: dsp1830.ipdisk.co.kr (admin / 1830)
   - 2차 NAS: sknas205.ipdisk.co.kr (sknas205 / sknas205204203)

3. 복원 암호
   → "duson_dr_2026_" + 원래 서버의 hostname
   → 예: duson_dr_2026_DESKTOP-ABC123
   → 모르면 NAS의 manifest.json에서 hostname 확인 가능

============================================================
  복원 순서 (5단계)
============================================================

【1단계】 NAS에서 복원 파일 다운로드
─────────────────────────────────────
  FTP 프로그램 (FileZilla 등)으로 NAS 접속:
    주소: dsp1830.ipdisk.co.kr (또는 sknas205.ipdisk.co.kr)
    사용자: admin (또는 sknas205)
    비밀번호: 1830 (또는 sknas205204203)

  다운로드할 폴더:
    /HDD2/share/disaster_recovery/latest/
    (sknas205: /HDD1/duson260118/disaster_recovery/latest/)

  이 폴더에 있는 파일 전체를 서버의 /tmp/dr/ 에 넣기

【2단계】 복원 스크립트 실행
─────────────────────────────────────
  서버 터미널에서:

    cd /tmp/dr
    chmod +x disaster_restore.sh
    sudo ./disaster_restore.sh --password "복원암호" --source ./

  → 자동으로 PHP, MySQL 설치 + DB + 소스코드 + 설정파일 복원

【3단계】 교정이미지 복원 (NAS에서)
─────────────────────────────────────
  교정이미지(고객 교정 파일)는 용량이 크므로 별도 동기화:

    sudo apt install lftp
    lftp -c "
      open -u admin,1830 ftp://dsp1830.ipdisk.co.kr
      mirror /HDD2/share/mlangorder_printauto/upload/ \
             /var/www/html/mlangorder_printauto/upload/
    "

  → 약 1.8GB, 네트워크에 따라 30분~2시간

【4단계】 도메인/SSL 설정
─────────────────────────────────────
  Plesk 서버인 경우:
    - Plesk 패널에서 dsp114.com 도메인 추가
    - 문서 루트를 /var/www/html 로 설정
    - Let's Encrypt SSL 활성화

  일반 서버인 경우:
    sudo apt install certbot python3-certbot-apache
    sudo certbot --apache -d dsp114.com

【5단계】 확인
─────────────────────────────────────
  □ 사이트 접속 확인: https://dsp114.com/
  □ 관리자 로그인 확인
  □ 상품 페이지 확인 (명함, 전단지 등)
  □ 결제 테스트 (inicis_config.php → INICIS_TEST_MODE=true 먼저!)
  □ NAS 동기화 cron 동작 확인: crontab -l

============================================================
  긴급 연락처
============================================================

  고객센터: 02-2632-1830
  GitHub: github.com/songyoungsoo
  이메일: yeongsu32@gmail.com

============================================================
  파일 목록 설명
============================================================

  db_full_YYYYMMDD.sql.gz       전체 데이터베이스 (145개 테이블)
  source_code_YYYYMMDD.tar.gz   전체 소스코드
  credentials_YYYYMMDD.tar.gz.enc  인증파일 (암호화됨)
  htaccess_YYYYMMDD.tar.gz      .htaccess 보안 규칙
  server_config_YYYYMMDD.tar.gz 서버 설정 (Apache, PHP, cron)
  imgfolder_recent_YYYYMMDD.tar.gz  고객 원고 (최근 1개월)
  manifest.json                 체크섬, 크기, 버전 정보
  disaster_restore.sh           자동 복원 스크립트
  RESTORE_README.txt            이 파일

============================================================
  서버 사양 참고
============================================================

  PHP 버전: 8.2 (프로덕션과 동일하게)
  MySQL: 5.7 이상
  Apache: 2.4 이상
  OS: Ubuntu 22.04+ 또는 Debian 12+
  디스크: 최소 10GB (교정이미지 포함 시 15GB)
  메모리: 최소 2GB

============================================================
  최종 업데이트: 2026-03-13
============================================================
