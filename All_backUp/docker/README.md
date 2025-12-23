# 🐳 두손기획인쇄 Docker 설치 가이드

## 빠른 시작 (한방 설치)

```bash
# 1. docker 폴더로 이동
cd docker

# 2. 환경변수 설정 (선택)
cp .env.example .env
# 필요시 .env 파일 수정

# 3. 실행
docker-compose up -d

# 4. 접속
# 사이트: http://localhost/
# 관리자: http://localhost/admin/
# 기본 계정: admin / admin123
```

## 상세 명령어

### 기본 실행
```bash
# 백그라운드 실행
docker-compose up -d

# 로그 확인하며 실행
docker-compose up

# 특정 서비스만 실행
docker-compose up -d web db
```

### phpMyAdmin 포함 실행
```bash
# phpMyAdmin 포함 (http://localhost:8080)
docker-compose --profile admin up -d
```

### 컨테이너 관리
```bash
# 상태 확인
docker-compose ps

# 로그 보기
docker-compose logs -f

# 웹 서버 로그만
docker-compose logs -f web

# 컨테이너 재시작
docker-compose restart

# 컨테이너 중지
docker-compose down

# 컨테이너 + 볼륨 삭제 (데이터 포함!)
docker-compose down -v
```

### 컨테이너 접속
```bash
# 웹 컨테이너 쉘 접속
docker exec -it duson_web bash

# DB 컨테이너 MySQL 접속
docker exec -it duson_db mysql -u dsp1830 -p
```

## 환경변수

`.env` 파일에서 설정 가능:

| 변수 | 기본값 | 설명 |
|------|--------|------|
| `DB_NAME` | dsp1830 | 데이터베이스 이름 |
| `DB_USER` | dsp1830 | DB 사용자 |
| `DB_PASS` | ds701018 | DB 비밀번호 |
| `COMPANY_NAME` | 두손기획인쇄 | 회사명 |
| `COMPANY_PHONE` | 1688-2384 | 대표 전화 |
| `ADMIN_EMAIL` | - | 관리자 이메일 |
| `SMTP_HOST` | smtp.naver.com | SMTP 서버 |
| `SMTP_PORT` | 465 | SMTP 포트 |

## 포트

| 서비스 | 포트 | 용도 |
|--------|------|------|
| 웹 서버 | 80 | 메인 사이트 |
| MySQL | 3306 | 데이터베이스 |
| phpMyAdmin | 8080 | DB 관리 (선택) |

## 볼륨 (데이터 영구 저장)

| 볼륨 | 경로 | 용도 |
|------|------|------|
| `mysql_data` | /var/lib/mysql | DB 데이터 |
| `img_folder` | /var/www/html/ImgFolder | 이미지 파일 |
| `order_uploads` | /var/www/html/mlangorder_printauto/upload | 주문 파일 |

## 문제 해결

### 포트 충돌
```bash
# 80 포트 사용 중인 경우
docker-compose down
# docker-compose.yml에서 "80:80"을 "8000:80"으로 변경
docker-compose up -d
# http://localhost:8000/ 으로 접속
```

### 권한 문제
```bash
# 업로드 폴더 권한 재설정
docker exec -it duson_web chmod -R 777 /var/www/html/ImgFolder
docker exec -it duson_web chmod -R 777 /var/www/html/mlangorder_printauto/upload
```

### 데이터베이스 초기화
```bash
# 볼륨 삭제 후 재생성 (데이터 삭제됨!)
docker-compose down -v
docker-compose up -d
```

### 컨테이너 재빌드
```bash
# 이미지 재빌드 (코드 변경 시)
docker-compose build --no-cache
docker-compose up -d
```

## 프로덕션 배포

프로덕션 환경에서는 다음을 권장합니다:

1. **강력한 비밀번호 설정**
   ```bash
   DB_PASS=your_strong_password
   MYSQL_ROOT_PASSWORD=your_root_password
   ```

2. **HTTPS 적용** (nginx-proxy 또는 Traefik 사용)

3. **정기 백업 설정**
   ```bash
   # MySQL 백업
   docker exec duson_db mysqldump -u dsp1830 -p dsp1830 > backup.sql

   # 파일 백업
   docker cp duson_web:/var/www/html/ImgFolder ./backup/ImgFolder
   ```

4. **phpMyAdmin 비활성화** (보안)
   - `--profile admin` 없이 실행

## 시스템 요구사항

- Docker 20.10+
- Docker Compose 2.0+
- 최소 2GB RAM
- 최소 10GB 디스크 공간
