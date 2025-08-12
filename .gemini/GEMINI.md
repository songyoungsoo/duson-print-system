<!------------------------------------------------------------------------------------
 데이터베이스 구조 관계도
1. 카테고리 관리 테이블 (mlangprintauto_transactioncate)

역할: 인쇄물 카테고리의 계층 구조를 관리
구조: 트리 형태의 계층적 분류

BigNo = '0': 최상위 카테고리
BigNo가 다른 번호: 하위 카테고리 (부모-자식 관계)



카테고리 분류:

inserted: 칼라인쇄 관련
NameCard: 명함 관련
LittlePrint: 소량포스터
envelope: 봉투
cadarok: 카다록/리플렛
MerchandiseBond: 상품권
msticker: 자석스티커
NcrFlambeau: 양식/NCR

2. 품목별 세부 테이블들
각 카테고리(Ttable)별로 독립된 테이블이 존재:
공통 필드 구조:

style: 상위 카테고리 번호 (transactioncate의 no와 연결)
Section: 세부 옵션 번호 (transactioncate의 no와 연결)
quantity: 수량
money: 가격
TreeSelect: 추가 옵션 (용지/재질 등)
DesignMoney: 디자인 비용
POtype: 주문 유형

3.드롭다운 될 때 품목별 순서
mlangprintauto_inserted //전단지 (인쇄색상,종이종류,종이규격,인쇄면(단면,양면), 수량(매수), 편집비용)
mlangprintauto_sticker //스티커 (재질, 가로X세로,  매수, 편집, 모양)
mlangprintauto_msticker //종이자석,전체자석 (종류, 규격, 수량,편집비)
mlangprintauto_namecard //명함 (명항종류,명함재질, 인쇄면(단면,양면), 수량, 편집디자인)
mlangprintauto_merchandisebond //쿠폰 (종류,수량, 인쇄면,후가공(인쇄만), 편집비용)
mlangprintauto_envelope //봉투 (구분(대,소봉투),종류, 인쇄색상, 수량, 디자인편집)
mlangprintauto_ncrflambeau //양식지 (구분, 규격, 색상, 수량, 편집디자인)
mlangprintauto_cadarok //카다록 (구분, 규격, 종이종류, 수량,주문방법-인쇄만)
mlangprintauto_littleprint //포스터 (구분, 종이종류,종이규격, 인쇄면, 수량, 디자인편집)

4. 주문 저장 테이블 (MlangOrder_PrintAuto)

역할: 실제 주문 정보 통합 저장
고객 정보, 주문 상세, 배송 정보 등을 포함

관계 흐름
카테고리 관리 (transactioncate)
         ↓
품목별 세부 테이블 (inserted, namecard, envelope 등)
         ↓
주문 통합 테이블 (MlangOrder_PrintAuto)
데이터 연결 관계:

transactioncate.no ↔ 품목테이블.style/Section (카테고리-옵션 연결)
transactioncate.no ↔ 품목테이블.TreeSelect (추가옵션 연결)
품목테이블의 설정 → MlangOrder_PrintAuto (주문 생성)

이 구조는 인쇄물의 복잡한 옵션 체계(크기, 용지, 수량, 후가공 등)를 계층적으로 관리하면서, 각 품목별 특성에 맞는 세부 설정을 지원하는 유연한 시스템입니다.
-------------------------------------------------------------------------------------> 