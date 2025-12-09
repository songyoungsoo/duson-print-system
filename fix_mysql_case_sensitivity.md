# MySQL 대소문자 설정 변경 가이드

## 현재 상태
- 로컬: `lower_case_table_names = 0` (대소문자 구분)
- 운영: `lower_case_table_names = 1 또는 2` (대소문자 구분 안 함)

## 해결 방법

### 1. MySQL 설정 파일 수정

```bash
# MySQL 설정 파일 찾기
sudo find /etc -name "my.cnf" -o -name "mysqld.cnf"

# 또는
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

### 2. [mysqld] 섹션에 추가

```ini
[mysqld]
lower_case_table_names = 1
```

**설정 값 의미:**
- `0`: 대소문자 구분 (Linux 기본값)
- `1`: 테이블명을 소문자로 저장하고 비교 (Windows 기본값)
- `2`: 테이블명은 원본 유지하지만 비교는 소문자로 (macOS 기본값)

### 3. MySQL 재시작

```bash
sudo service mysql restart
```

### 4. 확인

```bash
mysql -u root -e "SHOW VARIABLES LIKE 'lower_case_table_names';"
```

## 주의사항

⚠️ **중요**: `lower_case_table_names` 설정을 변경하기 전에 기존 데이터베이스를 백업하세요!

MySQL 8.0 이상에서는 초기화 시에만 이 값을 설정할 수 있습니다. 이미 데이터가 있는 경우:

1. 데이터 백업
2. MySQL 데이터 디렉토리 삭제
3. 설정 변경
4. MySQL 재초기화
5. 데이터 복원

## 대안: 코드 수정

설정 변경이 어려운 경우, 모든 테이블명과 파일명을 소문자로 통일하는 것이 좋습니다.
