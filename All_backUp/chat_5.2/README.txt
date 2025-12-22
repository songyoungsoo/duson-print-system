================================================================================
  채팅 시스템 PHP 5.2 / MySQL 4.0 호환 버전
  dsp114.com 서버용 (EUC-KR 인코딩)
================================================================================

■ 서버 정보
  - 서버: dsp114.com
  - DB 계정: duson1830 / du1830
  - FTP 계정: duson1830 / du1830
  - PHP 버전: 5.2
  - MySQL 버전: 4.0.27
  - 인코딩: EUC-KR (UTF-8 아님!)

■ 설치 순서

  1. 데이터베이스 생성
     - phpMyAdmin 2.3.3pl1 접속
     - 새 데이터베이스 생성 (또는 기존 DB 사용)
     - 문자셋: EUC-KR 선택

  2. 테이블 생성
     - create_chat_system_mysql40.sql 실행
     - 샘플 데이터 필요 시: insert_sample_data_mysql40.sql 실행

  3. 데이터베이스 연결 설정
     - /includes/db.php 또는 config.php 수정
     - DB 접속 정보 입력 (호스트, 사용자명, 비밀번호, DB명)
     - mysqli_set_charset($db, 'euckr'); 확인

  4. 파일 업로드
     - FTP로 chat_5.2 폴더 전체 업로드
     - 업로드 디렉토리 권한 설정 (chmod 755 또는 777)

  5. 테스트
     - demo.php 접속하여 채팅 테스트
     - admin_floating.php 접속하여 관리자 페이지 테스트

■ 파일 구조

  chat_5.2/
  ├── config.php                          PHP 설정 파일 (PHP 5.2 호환)
  ├── api.php                             채팅 API (PHP 5.2 호환)
  ├── admin_floating.php                  관리자 플로팅 인터페이스
  ├── admin_floating.js                   관리자 JavaScript
  ├── demo.php                            고객용 데모 페이지
  ├── chat.js                             고객용 JavaScript
  ├── chat.css                            채팅 스타일시트
  ├── create_chat_system_mysql40.sql      MySQL 4.0 호환 테이블 생성
  ├── insert_sample_data_mysql40.sql      샘플 데이터 (테스트용)
  └── README.txt                          이 파일

■ 주요 변경사항 (원본 대비)

  1. PHP 5.2 호환
     - ?? 연산자 → 삼항연산자 (isset() ? : )
     - session_status() → session_id() == ''
     - [] 배열 → array()
     - JSON_UNESCAPED_UNICODE 제거
     - finfo_open() → mime_content_type() 또는 getimagesize()
     - __DIR__ → dirname(__FILE__)

  2. MySQL 4.0 호환
     - FOREIGN KEY 제거 (참조 무결성은 PHP 레벨에서 처리)
     - ON UPDATE CURRENT_TIMESTAMP 제거 (PHP에서 UPDATE 시 수동 처리)
     - ENGINE=InnoDB → ENGINE=MyISAM
     - CHARSET=utf8mb4 → CHARSET=euckr

  3. 인코딩
     - 모든 파일: EUC-KR 인코딩
     - HTML meta charset: euc-kr
     - HTTP header: charset=euc-kr
     - MySQL charset: euckr

■ 체크리스트

  데이터베이스:
  [ ] MySQL 4.0.27 버전 확인
  [ ] EUC-KR 문자셋 설정
  [ ] create_chat_system_mysql40.sql 실행 성공
  [ ] chatrooms, chatparticipants, chatmessages, chatstaff, chatsettings 테이블 생성 확인

  PHP 설정:
  [ ] PHP 5.2 버전 확인
  [ ] config.php의 DB 접속 정보 수정
  [ ] mysqli_set_charset($db, 'euckr') 설정 확인
  [ ] 파일 업로드 디렉토리 권한 설정

  테스트:
  [ ] demo.php 접속하여 고객 채팅 테스트
  [ ] 메시지 전송 테스트
  [ ] admin_floating.php 접속하여 관리자 채팅 테스트
  [ ] 직원1, 직원2, 직원3 로그인 테스트
  [ ] 채팅방 목록 표시 확인
  [ ] 실시간 메시지 수신 확인

  한글 인코딩:
  [ ] 한글 메시지 입력/표시 정상 확인
  [ ] 한글 깨짐 없음 확인
  [ ] 채팅방 이름 한글 표시 정상

■ 주의사항

  1. 기존 chat 폴더와 혼동 금지
     - 기존 chat/ (UTF-8, 최신 PHP/MySQL)
     - 신규 chat_5.2/ (EUC-KR, PHP 5.2, MySQL 4.0)
     - 두 폴더는 완전히 독립적으로 운영

  2. 인코딩 문제
     - 파일 수정 시 반드시 EUC-KR 인코딩 유지
     - UTF-8로 저장하면 한글 깨짐 발생

  3. MySQL 4.0 제약사항
     - 외래키(FOREIGN KEY) 없음 → 데이터 삭제 시 수동 정리 필요
     - 자동 timestamp 업데이트 없음 → PHP에서 UPDATE 시 updatedat 수동 갱신
     - 서브쿼리 성능 낮음 → 복잡한 쿼리는 분리 실행 권장

  4. 호환성
     - 최신 브라우저에서는 정상 작동
     - IE 구버전 지원 불가 (ES6 사용)
     - 모바일 브라우저 지원

■ 문제 해결

  한글이 깨져요:
  → config.php의 mysqli_set_charset($db, 'euckr') 확인
  → 파일이 EUC-KR로 저장되었는지 확인
  → HTML meta charset이 euc-kr인지 확인

  채팅방 목록이 안 나와요:
  → admin_floating.php에서 직원 선택(staff1, staff2, staff3)
  → insert_sample_data_mysql40.sql 실행하여 샘플 데이터 생성
  → api.php의 DB 접속 확인

  메시지 전송이 안 돼요:
  → 브라우저 콘솔에서 에러 확인
  → api.php에서 error_reporting 확인
  → 파일 업로드 디렉토리 권한 확인 (../chat_uploads/)

  직원 로그인이 안 돼요:
  → chatstaff 테이블에 staff1, staff2, staff3 데이터 확인
  → create_chat_system_mysql40.sql에서 직원 데이터 INSERT 확인

■ 기술 지원

  문의: dsp1830.shop
  제작: 두손기획인쇄
  버전: PHP 5.2 / MySQL 4.0 호환 (2025-11-25)

================================================================================
