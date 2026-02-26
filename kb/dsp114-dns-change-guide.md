## DSP114 도메인 DNS 변경 가이드

### 현재 상황

| 도메인 | 등록 기관 | 호스팅 NS | IP 주소 | 상태 |
|--------|----------|----------|---------|------|
| dsp114.com | Tucows | NS1.GABIWEB.CO.KR, NS2.GABIWEB.CO.KR | 175.119.156.249 | 활성화 |
| dsp114.co.kr | bizwon.com | ns1.dsp114.co.kr, ns2.dsp114.co.kr | 175.119.156.249 | 활성화 |

**핵심:** 두 도메인이 동일한 IP 사용, dsp114.com만 GABIWEB.CO.KR의 NS 사용 중

---

### 변경 작업 (참조용)

**변경할 것:**
- dsp114.com: 호스팅 NS를 ns1.dsp114.co.kr, ns2.dsp114.co.kr로 변경

---

### 변경 방법 3가지

#### 방법 1: Tucows에서 직접 변경 (가장 간단)

1. https://www.tucows.com 접속
2. "My Domains" 로그인
3. dsp114.com 선택
4. DNS 설정 (또는 "Name Servers") 클릭
5. 변경:
   - Name Server 1: ns1.dsp114.co.kr
   - Name Server 2: ns2.dsp114.co.kr
6. 변경 저장
7. 대기 2~3시간 (DNS 전파)

#### 방법 2: Plesk에서 변경

1. Plesk에 로그인 (https://cmshom.co.kr:8443/login_up.php)
2. 왼쪽 메뉴 → "웹사이트" 클릭
3. 웹사이트 목록 → dsp114.com 클릭
4. "DNS 설정" 탭 → "스타일"에서 "간소화" → "상세"로 변경
5. "호스팅 DNS" 또는 "네임서버" 찾기
6. 변경:
   - 현재: NS1.GABIWEB.CO.KR, NS2.GABIWEB.CO.KR
   - 변경: ns1.dsp114.co.kr, ns2.dsp114.co.kr
7. 변경 저장
8. 대기 2~3시간

#### 방법 3: Plesk 지원에 문의

**이메일:** gabiweb02@gmail.com
**제목:** [DNS 변경] dsp114.com 호스팅 NS 변경 요청

**내용:**
```
현재 dsp114.com의 호스팅 NS를 dsp114.co.kr의 자체 NS로 변경하고 싶습니다.

현재 설정: NS1.GABIWEB.CO.KR, NS2.GABIWEB.CO.KR
변경 후 설정: ns1.dsp114.co.kr, ns2.dsp114.co.kr
```

---

### 변경 후 확인

1. 2~3시간 후 DNS 전파 확인
2. Whois 검색 (https://www.whois.com/whois/dsp114.com)
   - Name Server: ns1.dsp114.co.kr, ns2.dsp114.co.kr (업데이트 확인)
3. 웹사이트 접속 확인
   - https://dsp114.com → 잘 작동하는지 확인
4. 검색엔진 동기화 확인
   - 네이버, 구글 등에서 dsp114.com으로 접속 시 정상 작동 확인

---

### ⚠️ 중요 시나리오

**변경 후 상황:**
- ✅ dsp114.com → 메인 홈페이지 (ns1.dsp114.co.kr, ns2.dsp114.co.kr)
- ✅ dsp114.co.kr → 메인 홈페이지 (ns1.dsp114.co.kr, ns2.dsp114.co.kr)

**즉, 두 도메인이 완전하게 동일하게 설정됨!**

**대안:** dsp114.com을 유지하고 dsp114.co.kr을 없앨 경우
- dsp114.com → 메인 홈페이지 유지
- dsp114.co.kr → 301 리다이렉트 → dsp114.com

---

### 💡 추가 참고 정보

1. **DNS 변경 시간:** 2~3시간 (최대 48시간)
2. **Whois 업데이트 시간:** DNS 변경 후 24~48시간 이내
3. **검색엔진 반영 시간:** DNS 변경 후 24시간 이내
4. **변경 전 백업 필수:** 필요 시 파일 백업
5. **DNS 재배포 대기:** 전 세계 DNS 서버 동기화 대기

---

### 📞 필요시 연락처

- **Plesk 호스팅 지원:** gabiweb02@gmail.com
- **전화:** +82.07070141101
- **Plesk 로그인:** https://cmshom.co.kr:8443/login_up.php
  아이디: 두손기획
  비밀번호: h%42D9u2m

---

**작성일:** 2026-02-23
**마지막 업데이트:** 2026-02-23
