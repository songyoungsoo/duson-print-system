<?php
/**
 * 종이자석스티커 상세 설명 - 하단 설명방법
 * 색상 테마: #ff9800 (오렌지)
 */
?>

<style>
/* 종이자석스티커 전용 스타일 */
.msticker-detail-section {
    font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    color: #333;
}

.msticker-detail-section h2 {
    color: #ff9800;
    border-bottom: 3px solid #ff9800;
    padding-bottom: 8px;
    margin-bottom: 20px;
    font-size: 1.4rem;
    font-weight: 600;
}

.msticker-detail-section h3 {
    color: #ff9800;
    margin-top: 25px;
    margin-bottom: 15px;
    font-size: 1.2rem;
    font-weight: 600;
}

.msticker-detail-section h4 {
    color: #f57c00;
    margin-top: 20px;
    margin-bottom: 10px;
    font-size: 1.1rem;
    font-weight: 600;
}

/* 제품 소개 박스 */
.product-intro-box {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border: 2px solid #ff9800;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    position: relative;
}

.product-intro-box::before {
    content: "🧲";
    position: absolute;
    top: -15px;
    left: 20px;
    background: #fff;
    padding: 5px 10px;
    border-radius: 20px;
    border: 2px solid #ff9800;
    font-size: 1.2rem;
}

.product-intro-box h3 {
    color: #e65100;
    margin-top: 0;
    margin-bottom: 15px;
}

.product-intro-box p {
    margin-bottom: 10px;
    color: #333;
}

/* 사이즈 그리드 */
.size-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #ff9800;
}

.size-item {
    background: white;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    color: #ff9800;
    border: 1px solid #ffcc80;
    transition: all 0.3s ease;
}

.size-item:hover {
    background: #fff3e0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.2);
}

/* 작업 방식 구분 */
.work-type-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.work-type-box {
    background: white;
    border: 2px solid #ff9800;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.work-type-box h4 {
    color: #ff9800;
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.work-type-box p {
    margin: 0;
    color: #666;
    font-size: 0.95rem;
}

/* 제품 정보 테이블 */
.product-info-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.product-info-table th {
    background: #ff9800;
    color: white;
    padding: 15px;
    text-align: center;
    font-weight: 600;
}

.product-info-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    text-align: center;
}

.product-info-table tr:last-child td {
    border-bottom: none;
}

.product-info-table tr:nth-child(even) {
    background: #fff3e0;
}

/* 파일 형식 아이콘 */
.file-icons {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 20px 0;
    padding: 20px;
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border-radius: 12px;
    border: 1px solid #ff9800;
}

.file-icon {
    text-align: center;
    flex: 1;
}

.file-icon img {
    width: 50px;
    height: 50px;
    margin-bottom: 8px;
}

.file-icon span {
    display: block;
    font-size: 0.9rem;
    color: #ff9800;
    font-weight: 600;
}

/* 주의사항 박스 */
.warning-box {
    background: #fff3cd;
    border: 2px solid #ff9800;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
}

.warning-box h4 {
    color: #ff9800;
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1rem;
}

.warning-box p, .warning-box li {
    margin-bottom: 8px;
    color: #333;
    font-size: 0.95rem;
}

.warning-box ul {
    padding-left: 20px;
    margin: 10px 0;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .work-type-section {
        grid-template-columns: 1fr;
    }

    .size-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 8px;
        padding: 15px;
    }

    .file-icons {
        flex-wrap: wrap;
        gap: 15px;
    }

    .file-icon {
        flex: 0 0 calc(50% - 10px);
    }
}
</style>

<div class="msticker-detail-section">
    <!-- 제품 소개 -->
    <div class="product-intro-box">
        <h3>종이자석 스티커</h3>
        <p>자사의 종이자석 스티커는 후면에 조각자석을 붙이는 제품으로 홍보상품으로 유용합니다.</p>
        <p>일회성이 아닌 제품으로 실용적이며 기본모형 외에 원하는 모양의 제품 제작이 가능합니다.</p>
    </div>

    <!-- 제작 가능 사이즈 -->
    <h2>📏 제작 가능 사이즈</h2>
    <div class="size-grid">
        <div class="size-item">90mm × 70mm</div>
        <div class="size-item">90mm × 80mm</div>
        <div class="size-item">90mm × 90mm</div>
        <div class="size-item">90mm × 100mm</div>
        <div class="size-item">90mm × 110mm</div>
        <div class="size-item">90mm × 120mm</div>
        <div class="size-item">90mm × 130mm</div>
    </div>

    <!-- 작업 방식 구분 -->
    <h2>✂️ 작업 방식 구분</h2>
    <div class="work-type-section">
        <div class="work-type-box">
            <h4>🎯 원터치 (배경 없음)</h4>
            <p>재단사이즈와 편집사이즈가 동일</p>
        </div>
        <div class="work-type-box">
            <h4>🖼️ 투터치 (배경 있음)</h4>
            <p>재단사이즈에서 사방으로 3mm 여분 필요</p>
        </div>
    </div>

    <!-- 제품 정보 -->
    <h2>📋 제품 정보</h2>
    <table class="product-info-table">
        <thead>
            <tr>
                <th>구분</th>
                <th>재질</th>
                <th>특징</th>
                <th>출고일</th>
                <th>비고</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>종이 자석 스티커</td>
                <td>아트지 + 전체 자석 (합지)</td>
                <td>홍보상품으로 유용, 실용적</td>
                <td>접수 후 4~5일</td>
                <td>옵셋인쇄 가능</td>
            </tr>
        </tbody>
    </table>

    <!-- 출고 안내 -->
    <h2>🚚 접수 출고안내</h2>
    <p style="margin-bottom: 15px; color: #666;">제품별 출고일을 확인해 보세요.</p>
    <table class="product-info-table">
        <thead>
            <tr>
                <th>제품</th>
                <th>상세 제품</th>
                <th>상세 용지명</th>
                <th>출고일</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>종이 자석</td>
                <td>종이 자석</td>
                <td>스노우화이트 250g + CR코팅 조각자석 (부착)</td>
                <td>접수완료 후 4~5일</td>
            </tr>
            <tr>
                <td>종이 자석 (도무송)</td>
                <td>-</td>
                <td>-</td>
                <td>별도 문의</td>
            </tr>
        </tbody>
    </table>

    <!-- 접수 가능 파일 -->
    <h2>📁 접수 가능 파일</h2>
    <div class="file-icons">
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #ff9800;">🎨</div>
            <span>Adobe<br>Illustrator</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #ff9800;">📄</div>
            <span>Adobe<br>Photoshop</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #ff9800;">🖥️</div>
            <span>CorelDRAW</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #ff9800;">📷</div>
            <span>디지털 사진 및<br>디지털 그래픽툴 공용</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #ff9800;">🎭</div>
            <span>디지털 그래픽툴 공용</span>
        </div>
    </div>

    <!-- 작업 시 유의사항 -->
    <h2>⚠️ 작업 시 유의사항</h2>
    <div class="warning-box">
        <p><strong>반드시 작업 유의사항을 숙지하시고 주문해주시기 바랍니다.</strong> 당사가 편집, 수정 작업을 할 수 없으며 작업 유의사항에 맞지 않은 데이터의 오류는 사고처리가 불가하십니다.</p>
    </div>

    <h3>01. 접수가능한 사이즈 범위</h3>
    <div class="warning-box">
        <p>최소 90mm × 70mm ~ 최대 90mm × 130mm 입니다.</p>
    </div>

    <h3>02. 작업/재단사이즈 설정</h3>
    <div class="warning-box">
        <h4>작업 사이즈와 재단 사이즈의 색은 선색 없음 처리</h4>
    </div>

    <h3>03. 작업 주의사항</h3>
    <div class="warning-box">
        <ul>
            <li><strong>파일 업로드 시</strong> 돔보선은 넣지 마시고 안전선 / 재단선 / 작업선은 삭제가아닌 꼭 안 보이는 선색 없음으로 설정하셔야 합니다.</li>
            <li><strong>글씨나 배경 색상 작업 시</strong> CMYK가 섞인 먹색은 더블톤으로 나올 수 있으니 먹(K100)으로만 작업하셔야 선명하게 인쇄됩니다.</li>
            <li><strong>모든 작업물은 CMYK로 작업</strong>하셔야 하시고 모든 글꼴은 아웃라인(곡선화) 하셔야 합니다. (Shift+Ctrl+O / Type - Create Outlines / 윤곽선 만들기)</li>
            <li><strong>복잡한 개체나 특수한 효과</strong>를 사용한 것은 레스터화(비트맵) 하셔야 합니다.</li>
            <li><strong>잠금(Lock)이 된 부분</strong>은 인쇄 시 빠지거나 위치가 변동되실 수 있으니 반드시 잠금을 해지하셔야 합니다.</li>
            <li><strong>빠지는 개체 없이 그룹을 만들어</strong> 접수 부탁드립니다.</li>
            <li><strong>사용하시는 이미지는 반드시 CMYK모드 - 300dpi 해상도</strong>로 작업하시고 파일 내 사용한 이미지의 링크 여부 확인하신 후 이미지 포함(EMBEDED) 하여 저장하셔야 합니다.</li>
        </ul>
    </div>

    <h3>04. 재단</h3>
    <div class="warning-box">
        <ul>
            <li><strong>합판 시스템 특성상</strong> 한 장씩 재단하는 것이 아니라 200~500장의 많은 양을 한 번에 누르면서 재단하므로 안쪽이나 바깥쪽으로 재단 오차가 발생합니다.</li>
            <li><strong>테두리가 있거나 액자와 같은 형식의 디자인</strong>은 밀림현상으로 인하여 균등한 재단이 이루어지지 않으니 재단 사이즈에서 사방 3~4mm 여유 있는 작업을 하시면 육안상 많이 밀려 보이지 않으시니 작업 시 참고 부탁드립니다.</li>
            <li><strong>100mm 미만 사이즈는 재단 밀림</strong>으로 대각선으로 재단되실 수 있으므로 정밀한 재단을 원할 경우 도무송을 추천해드립니다.</li>
        </ul>
    </div>

    <h3>05. 납기 및 배송</h3>
    <div class="warning-box">
        <ul>
            <li><strong>합판 인쇄 시스템 특성상</strong> 인쇄 지연, 판 누락, 기기 고장, 연휴 기간, 데이터 이상으로 늦어질 수 있습니다.</li>
            <li><strong>접수완료 된 다음날 97% 이상 출고</strong>되지만 2~3%는 오류가 발생될 수 있으므로 해외출장, 행사 등으로 날짜 및 시간을 약속하는 제품은 주문을 사양 하며 늦어진 출고로 인한 책임은 질 수 없습니다. (별도의 독판 작업으로 문의 바랍니다.)</li>
            <li><strong>고객 여러분의 편의를 위하여</strong> 배송업무를 대행하기에 물건의 검수 작업 없이 배송 됩니다.</li>
            <li><strong>여러 가지 이유로 재작업 진행 후</strong> 납기지연으로 인한 배송, 퀵, 화물(착불) 등 요구시 발송은 가능하나 손해배상, 운송비는 부담하지 않습니다.</li>
            <li><strong>제품 보관 기간은 5일간</strong>입니다. 보관 기간 이후에 폐기하므로 별도 보관을 요청할 경우에는 출고실로 연락 주셔야 합니다.</li>
            <li><strong>모든 품목은 5~10% 미만의 수량 부족</strong>은 합판인쇄공정상 발생할 수 있으며, 후가공의 단계에 따라 더 발생할 수 있습니다. 이로 인한 환불이나 재작업은 불가합니다.</li>
        </ul>
    </div>
</div>