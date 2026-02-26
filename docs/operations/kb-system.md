## 📂 Knowledge Vault (KB) 시스템

### 시스템 개요

AI 대화 결과를 저장하고 검색하는 개인 지식 관리 시스템. 토큰 소모 없이 기존 정보 재활용.

| 항목 | 값 |
|------|-----|
| **경로** | `/kb/` (로컬: `http://localhost/kb/`, 프로덕션: `https://dsp114.co.kr/kb/`) |
| **인증** | localhost 자동 우회, 프로덕션 비밀번호: `duson2026!kb` |
| **DB 테이블** | `knowledge_base` (FULLTEXT INDEX on title, content, tags) |
| **파일** | `kb_auth.php`, `api.php`, `index.php`, `article.php` |

### 파일 구조

```
/kb/
├── kb_auth.php   ← 인증 모듈 (localhost 자동 우회 + 세션 비밀번호)
├── api.php       ← CRUD + FULLTEXT 검색 API
├── index.php     ← 메인 검색/목록 페이지 (AJAX 실시간 검색)
└── article.php   ← 문서 상세/수정 페이지 (마크다운 렌더링 + 코드 하이라이팅)
```

### API 엔드포인트 (`/kb/api.php`)

| action | Method | 용도 |
|--------|--------|------|
| `search` | GET | FULLTEXT 검색 (q, category, page 파라미터) |
| `get` | GET | 단일 문서 조회 (id 파라미터) |
| `create` | POST | 새 문서 생성 (title, content, tags, category) |
| `update` | POST | 문서 수정 (id, title, content, tags, category) |
| `delete` | POST | 문서 삭제 (id) |

### 카테고리 (7종)

| 코드 | 이름 |
|------|------|
| `general` | 일반 |
| `setup` | 설치가이드 |
| `config` | 설정 |
| `troubleshoot` | 트러블슈팅 |
| `code` | 코드/스니펫 |
| `reference` | 참조 |
| `workflow` | 워크플로우 |

### 기능

- FULLTEXT 검색 (Boolean Mode, 한국어+영문 지원)
- 250ms 디바운스 실시간 검색
- 카테고리 필터 탭
- 마크다운 렌더링 + highlight.js 코드 하이라이팅
- 코드 블록 복사 버튼
- 인라인 문서 편집
- 페이지네이션 (20건/페이지)


