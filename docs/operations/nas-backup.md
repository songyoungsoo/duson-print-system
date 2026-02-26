## 💾 NAS 백업 서버 INFO (자동 동기화)

**⚠️ NAS FTP 구조 - Git 변경사항 자동 백업**

```
NAS 접속 정보 (dsp1830.ipdisk.co.kr):
├─ Host: dsp1830.ipdisk.co.kr
├─ User: admin
├─ Pass: 1830
├─ Port: 21
└─ Protocol: FTP (plain)

NAS 디렉토리 구조:
/HDD2/share/              ← NAS 백업 루트
├─ mlangprintauto/        ← 제품 페이지 백업
├─ payment/               ← 결제 시스템 백업
├─ includes/              ← 공통 컴포넌트 백업
├─ AGENTS.md              ← 시스템 문서 백업
└─ ...                    ← Git 추적 파일 전체

🎯 NAS 동기화 방법:
# 마지막 커밋 변경 파일만 동기화
./scripts/sync_to_nas.sh

# 특정 커밋 이후 변경사항 동기화
./scripts/sync_to_nas.sh HEAD~3

# 미리보기 (실제 업로드 없음)
./scripts/sync_to_nas.sh --dry-run

# 특정 파일만 업로드
./scripts/sync_to_nas.sh --file payment/inicis_return.php
```

**NAS 동기화 체크리스트:**
- [ ] Git 커밋 완료 후 실행하는가?
- [ ] 프로덕션 배포 전/후에 NAS 백업했는가?
- [ ] 동기화 로그에 실패한 파일이 없는가?

### dsp114.com → NAS 전체 파일 백업 (FTP→FTP 릴레이)

dsp114.com 폐쇄 대비, 모든 파일을 NAS로 FTP 릴레이 백업하는 스크립트.
HTTP API 대신 FTP 직접 전송으로 웹 트래픽 쿼터 회피.

| 항목 | 값 |
|------|-----|
| **스크립트** | `/system/migration/ftp_nas_backup.sh` |
| **소스 FTP** | `dsp114.com` (user: duson1830, 절대경로: `/home/neo_web2/duson1830/www/`) |
| **타겟 FTP** | `dsp1830.ipdisk.co.kr` (user: admin, 루트: `/HDD2/share/`) |
| **릴레이** | 소스 FTP → 로컬 `/tmp/ftp_nas_relay/` → NAS FTP |
| **도구** | `lftp` (mirror, resume, charset 변환) |

**3가지 파일 타입:**

| 타입 | 소스 경로 | NAS 경로 | 규모 |
|------|----------|----------|------|
| upload (교정파일) | `/www/MlangOrder_PrintAuto/upload/` | `/mlangorder_printauto/upload/` | ~18,000 폴더 |
| shop (원고-스티커) | `/www/shop/data/` | `/shop/data/` | ~240 파일 (EUC-KR) |
| imgfolder (원고-일반) | `/www/ImgFolder/_MlangPrintAuto_*/` | `/ImgFolder/_MlangPrintAuto_*/` | 10개 제품 디렉토리 |

**사용법:**
```bash
./system/migration/ftp_nas_backup.sh status              # 동기화 현황 확인
./system/migration/ftp_nas_backup.sh upload --batch=50   # 교정파일 50개씩 배치
./system/migration/ftp_nas_backup.sh shop                # 원고-스티커 전체
./system/migration/ftp_nas_backup.sh imgfolder           # 원고-일반 전체
./system/migration/ftp_nas_backup.sh all                 # 전체 동기화
./system/migration/ftp_nas_backup.sh upload --dry-run    # 미리보기
```

**⚠️ 경로 주의 (lftp vs curl):**
- lftp: 시스템 절대 경로 (`/home/neo_web2/duson1830/www/...`)
- curl: FTP 홈 상대 경로 (`/www/...`)

---

