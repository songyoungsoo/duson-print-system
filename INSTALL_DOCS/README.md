# Duson Print System (두손기획인쇄)

PHP 기반 인쇄 주문 관리 시스템

---

## 개요

두손기획인쇄 시스템은 다양한 인쇄물(전단지, 명함, 스티커, 봉투, 포스터, 카다록, NCR양식지, 상품권)의 온라인 견적 및 주문을 처리하는 웹 애플리케이션입니다.

### 주요 기능

- 9개 제품군 주문 처리
- 실시간 가격 계산
- 파일 업로드 관리
- 주문 상태 추적
- 관리자 대시보드

### 기술 스택

| 구성요소 | 버전 | 설명 |
|----------|------|------|
| PHP | 7.4+ | 서버 사이드 스크립트 |
| MySQL | 5.7+ | 데이터베이스 (utf8mb4) |
| Apache | 2.4+ | 웹 서버 |

---

## 빠른 시작

### 1. 요구사항 확인

```bash
# PHP 버전 확인
php -v
# 결과: PHP 7.4.x 이상 필요

# MySQL 버전 확인
mysql --version
# 결과: MySQL 5.7.x 이상 필요

# Apache 상태 확인
sudo service apache2 status
```

### 2. 서버 시작

#### Linux (WSL2/Ubuntu)

```bash
# Apache 시작
sudo service apache2 start

# MySQL 시작
sudo service mysql start

# 상태 확인
sudo service apache2 status
sudo service mysql status
```

#### Windows (XAMPP)

1. XAMPP Control Panel 실행
2. Apache **Start** 클릭
3. MySQL **Start** 클릭

### 3. 접속 확인

브라우저에서 다음 URL 접속:

```
http://localhost/
```

정상적으로 메인 페이지가 표시되면 설치 완료입니다.

---

## 문서 구조

| 문서 | 내용 |
|------|------|
| [INSTALL.md](./INSTALL.md) | 상세 설치 가이드 |
| [CONFIGURATION.md](./CONFIGURATION.md) | 설정 파일 가이드 |
| [PRODUCT_SETUP.md](./PRODUCT_SETUP.md) | 제품 설정 가이드 |

---

## 디렉토리 구조

```
/var/www/html/
├── db.php                          # DB 연결 (환경 자동 감지)
├── config.env.php                  # 환경 설정
├── includes/                       # 공통 모듈
│   ├── auth.php                    # 인증 처리
│   ├── QuantityFormatter.php       # 수량 포맷팅
│   └── ProductSpecFormatter.php    # 제품 사양 포맷팅
├── mlangprintauto/                 # 제품 페이지
│   ├── inserted/                   # 전단지
│   ├── namecard/                   # 명함
│   ├── sticker_new/                # 스티커
│   ├── msticker/                   # 자석스티커
│   ├── envelope/                   # 봉투
│   ├── littleprint/                # 포스터
│   ├── cadarok/                    # 카다록
│   ├── ncrflambeau/                # NCR양식지
│   └── merchandisebond/            # 상품권
├── mlangorder_printauto/           # 주문 처리
│   ├── ProcessOrder_unified.php    # 주문 저장
│   └── OrderComplete_universal.php # 주문 완료
└── upload/                         # 파일 업로드
```

---

## 제품 목록

시스템에서 지원하는 9개 제품:

| 번호 | 품목명 | 폴더명 | 단위 |
|------|--------|--------|------|
| 1 | 전단지 | `inserted` | 연 |
| 2 | 스티커 | `sticker_new` | 매 |
| 3 | 자석스티커 | `msticker` | 매 |
| 4 | 명함 | `namecard` | 매 |
| 5 | 봉투 | `envelope` | 매 |
| 6 | 포스터 | `littleprint` | 매 |
| 7 | 상품권 | `merchandisebond` | 매 |
| 8 | 카다록 | `cadarok` | 부 |
| 9 | NCR양식지 | `ncrflambeau` | 권 |

---

## 기본 계정

### 관리자 계정

| 항목 | 값 |
|------|-----|
| 아이디 | `duson1830` |
| 비밀번호 | `du1830` |

### 데이터베이스 계정

| 항목 | 값 |
|------|-----|
| 데이터베이스명 | `dsp1830` |
| 사용자명 | `dsp1830` |
| 비밀번호 | `ds701018` |

---

## 환경별 접속 정보

| 환경 | URL | 용도 |
|------|-----|------|
| 로컬 개발 | `http://localhost` | 개발 및 테스트 |
| 스테이징 | `http://dsp1830.shop` | 배포 전 검증 |
| 프로덕션 | `http://dsp1830.shop` | 실서비스 |

시스템은 접속 환경을 자동으로 감지하여 적절한 설정을 적용합니다.

---

## 지원

문제가 발생하면 다음 문서를 참조하세요:

1. [INSTALL.md](./INSTALL.md) - 설치 문제 해결
2. [CONFIGURATION.md](./CONFIGURATION.md) - 설정 문제 해결
3. [PRODUCT_SETUP.md](./PRODUCT_SETUP.md) - 제품 설정 문제 해결

---

*Version: 1.0*
*Last Updated: 2026-01-18*
