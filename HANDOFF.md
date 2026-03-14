# 핸드오프 문서 — 구서버 교정이미지 NAS 아카이브 프로젝트

> 마지막 업데이트: 2026-03-14 10:03 KST
> 이전 모델: claude-opus-4-6

---

## 현재 상태 요약

**프로덕션 코드 배포: ✅ 완료**
**NAS2(sknas205) 이미지 업로드: 🔄 자동 진행중 (PID 984568)**
**NAS1(dsp1830) 이미지 업로드: ⏳ 대기**

---

## 즉시 확인할 것 (세션 시작 시)

### 1. sknas205 업로드 완료 여부 확인
```bash
# 프로세스 살아있는지 확인
ps -p 984568 -o pid,stat,etime --no-headers 2>/dev/null

# 완료 여부 확인 (마지막 줄에 "Done" 있으면 완료)
tail -5 /var/www/html/scripts/direct_upload.log

# NAS에 올라간 폴더 수 확인 (25,553이면 완료)
curl -s --list-only "ftp://sknas205.ipdisk.co.kr/HDD1/duson260118/archive_upload/" --user "sknas205:sknas205204203" | wc -l

# 에러 확인
grep -c "ERROR" /var/www/html/scripts/direct_upload.log
```

### 2. 결과에 따른 다음 행동

**A. 업로드 완료 + 에러 0 → NAS1에도 동일하게 업로드**
```bash
# upload_direct_to_nas.sh 수정: NAS_HOST/USER/PASS를 NAS1으로 변경 후 실행
# NAS1: dsp1830.ipdisk.co.kr / admin / 1830 / /HDD2/share/archive_upload
```

**B. 업로드 완료 + 에러 있음 → 에러 파일만 재업로드**
```bash
grep "ERROR" /var/www/html/scripts/direct_upload.log
# 에러 파일 목록 확인 후 수동 재시도
```

**C. 프로세스 죽음 → 스크립트 다시 실행 (이미 올라간 건 덮어쓰기)**
```bash
nohup bash /var/www/html/scripts/upload_direct_to_nas.sh > /var/www/html/scripts/direct_upload_stdout.log 2>&1 &
```

---

## 전체 프로젝트 남은 작업 (3단계)

### Step 1: NAS2(sknas205) 업로드 완료 확인
- 자동 진행중 (PID 984568), 예상 소요 1.5~2시간
- 25,553 폴더 전부 올라가면 완료

### Step 2: NAS1(dsp1830)에도 동일하게 업로드
- 스크립트 `/var/www/html/scripts/upload_direct_to_nas.sh` 수정:
  ```
  NAS_HOST="dsp1830.ipdisk.co.kr"
  NAS_USER="admin"
  NAS_PASS="1830"
  NAS_BASE="/HDD2/share/archive_upload"
  ```
- 다시 실행하면 NAS1에도 업로드됨

### Step 3: 프로덕션 테스트
- 프로덕션 코드는 이미 배포 완료 (4개 파일)
- 구서버 주문번호로 교정보기 열어서 NAS 이미지 로드 확인
- 테스트 주문번호 예시: #1399, #29417, #52243, #50000
- 테스트 방법: 관리자 페이지에서 해당 주문 클릭 → 교정보기(WindowSian) → 이미지 표시 확인

### Step 3b: NAS tar.gz 파일 정리
- NAS2에 사장님이 올린 `archive_upload_01~10.tar.gz` 삭제 필요 (압축 풀기 대신 직접 업로드로 변경됨)
```bash
for i in $(seq -w 1 10); do
  curl -s -Q "DELE /HDD1/duson260118/archive_upload/archive_upload_${i}.tar.gz" \
    "ftp://sknas205.ipdisk.co.kr/" --user "sknas205:sknas205204203"
done
```

### Step 4: 로컬 임시파일 정리 (모든 검증 완료 후)
```bash
rm -rf /var/www/html/scripts/old_proofs_raw
rm -rf /var/www/html/scripts/old_proofs_organized
rm -f /var/www/html/scripts/archive_upload_*.tar.gz
rm -f /var/www/html/HANDOFF.md
```

---

## 배포 완료된 파일 (프로덕션)

| 로컬 경로 | 프로덕션 경로 | 상태 |
|-----------|-------------|------|
| `includes/NasImageProxy.php` | `httpdocs/includes/NasImageProxy.php` | ✅ 배포됨 |
| `includes/ImagePathResolver.php` | `httpdocs/includes/ImagePathResolver.php` | ✅ 배포됨 |
| `mlangorder_printauto/view_proof.php` | `httpdocs/mlangorder_printauto/view_proof.php` | ✅ 배포됨 |
| `mlangorder_printauto/WindowSian.php` | `httpdocs/mlangorder_printauto/WindowSian.php` | ✅ 배포됨 |

### 배포 검증 결과
- 메인 사이트 (dsp114.com): ✅ 200 OK
- 최근 주문 교정보기 (#84836): ✅ 200 OK (로컬 이미지 정상)
- 4개 파일 FTP 존재 확인: ✅

---

## 아키텍처 (NAS 프록시 흐름)

```
관리자 주문 클릭 → WindowSian.php → ImagePathResolver::getFilesFromRow($row)
    → B 섹션: 로컬 upload/{no}/ 스캔
    → B-2 섹션: 로컬에 없으면 NasImageProxy::listFiles($orderNo)
        → NAS FTP로 archive_upload/{no}/ 파일 목록 조회
    → WindowSian.php: nas_source=true인 파일에 src=nas URL 생성
    → view_proof.php: src=nas → NasImageProxy::streamFile() 호출
        → NAS FTP → tmpfile() → readfile() → fclose()(자동삭제)
```

---

## 접속 정보

| 서버 | Host | User | Pass |
|------|------|------|------|
| 프로덕션 FTP | dsp114.com | dsp1830 | cH*j@yzj093BeTtc |
| NAS1 FTP | dsp1830.ipdisk.co.kr | admin | 1830 |
| NAS2 FTP | sknas205.ipdisk.co.kr | sknas205 | sknas205204203 |

---

## 주의사항

1. **NAS 경로 정확히**: `archive_upload/{order_no}/` (플랫 구조)
2. **PHP 버전**: 프로덕션 8.2 / 로컬 7.4 — `NasImageProxy.php`는 둘 다 호환
3. **tar.gz 파일 NAS에 남아있음**: 사장님이 올린 10개 파일, 직접 업로드로 대체했으므로 삭제 필요
4. **로컬 11GB 데이터**: `old_proofs_organized/` — 모든 NAS 업로드+테스트 완료 후 삭제
