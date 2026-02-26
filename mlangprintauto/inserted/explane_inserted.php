<?php
/**
 * 합판 전단지 상세 설명 - 하단 설명방법
 * 색상 테마: #2196f3 (블루)
 */
?>

<style>
/* 합판 전단지 전용 스타일 */
.inserted-detail-section {
    font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    color: #333;
}

.inserted-detail-section h2 {
    color: #2196f3;
    border-bottom: 3px solid #2196f3;
    padding-bottom: 8px;
    margin-bottom: 20px;
    font-size: 1.4rem;
    font-weight: 600;
}

.inserted-detail-section h3 {
    color: #2196f3;
    margin-top: 25px;
    margin-bottom: 15px;
    font-size: 1.2rem;
    font-weight: 600;
}

.inserted-detail-section h4 {
    color: #1976d2;
    margin-top: 20px;
    margin-bottom: 10px;
    font-size: 1.1rem;
    font-weight: 600;
}

/* 제품 소개 박스 */
.product-intro-box {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 2px solid #2196f3;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    position: relative;
}

.product-intro-box::before {
    content: "📄";
    position: absolute;
    top: -15px;
    left: 20px;
    background: #fff;
    padding: 5px 10px;
    border-radius: 20px;
    border: 2px solid #2196f3;
    font-size: 1.2rem;
}

.product-intro-box h3 {
    color: #1565c0;
    margin-top: 0;
    margin-bottom: 15px;
}

.product-intro-box p {
    margin-bottom: 10px;
    color: #333;
}

/* 제품 타입 비교 섹션 */
.product-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 25px 0;
}

.product-type-box {
    background: white;
    border: 2px solid #2196f3;
    border-radius: 12px;
    padding: 20px;
    position: relative;
}

.product-type-box.gang-plate {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.product-type-box.exclusive-plate {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border-color: #ff9800;
}

.product-type-box h4 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.product-type-box.gang-plate h4 {
    color: #1565c0;
}

.product-type-box.exclusive-plate h4 {
    color: #ef6c00;
}

/* 사이즈 그리드 */
.size-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #2196f3;
}

.size-item {
    background: white;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    color: #2196f3;
    border: 1px solid #90caf9;
    transition: all 0.3s ease;
}

.size-item:hover {
    background: #e3f2fd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
}

/* 상세 정보 테이블 */
.detail-info-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.detail-info-table th {
    background: #2196f3;
    color: white;
    padding: 15px;
    text-align: center;
    font-weight: 600;
}

.detail-info-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}

.detail-info-table tr:last-child td {
    border-bottom: none;
}

.detail-info-table tr:nth-child(even) {
    background: #e3f2fd;
}

/* 출고 안내 테이블 */
.delivery-schedule-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.delivery-schedule-table th {
    background: #2196f3;
    color: white;
    padding: 12px 10px;
    text-align: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.delivery-schedule-table td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: center;
    font-size: 0.9rem;
}

.delivery-schedule-table .product-name {
    text-align: left;
    font-weight: 600;
    color: #2196f3;
}

.delivery-schedule-table .paper-type {
    color: #666;
}

.delivery-schedule-table .time-info {
    color: #d32f2f;
    font-weight: 600;
}

.delivery-schedule-table .delivery-method {
    color: #388e3c;
    font-size: 0.85rem;
}

/* 파일 형식 아이콘 */
.file-icons {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 20px 0;
    padding: 20px;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-radius: 12px;
    border: 1px solid #2196f3;
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
    color: #2196f3;
    font-weight: 600;
}

/* 주의사항 박스 */
.warning-box {
    background: #fff3cd;
    border: 2px solid #2196f3;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
}

.warning-box h4 {
    color: #2196f3;
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

/* TIP 박스 */
.tip-box {
    background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
    color: white;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    position: relative;
}

.tip-box::before {
    content: "💡";
    position: absolute;
    top: -10px;
    left: 15px;
    background: white;
    padding: 5px;
    border-radius: 50%;
    font-size: 1.1rem;
}

.tip-box h4 {
    color: white;
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1rem;
}

.tip-box p {
    margin: 0;
    color: white;
    font-size: 0.95rem;
}

/* 특징 박스들 */
.feature-boxes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.feature-box {
    background: white;
    border: 2px solid #2196f3;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.feature-box:hover {
    background: #e3f2fd;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(33, 150, 243, 0.2);
}

.feature-box h4 {
    color: #2196f3;
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.feature-box p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* 테이블 스크롤 래퍼 */
.table-scroll-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 20px 0;
}

.table-scroll-wrapper .detail-info-table,
.table-scroll-wrapper .delivery-schedule-table {
    margin: 0;
    min-width: 600px;
}

/* 한글 줄바꿈 방지 */
.inserted-detail-section td,
.inserted-detail-section th {
    word-break: keep-all;
}

.knowledge-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin: 20px 0;
}
.knowledge-item {
    background: white;
    border: 2px solid #2196f3;
    border-radius: 12px;
    padding: 15px;
}
.knowledge-item strong {
    color: #1565c0;
    display: block;
    margin-bottom: 5px;
    font-size: 1rem;
}
.knowledge-item p {
    margin: 0;
    font-size: 0.9rem;
    color: #555;
    line-height: 1.5;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .knowledge-grid {
        grid-template-columns: 1fr;
    }
    .product-comparison {
        grid-template-columns: 1fr;
    }

    .size-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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

    .feature-boxes {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .delivery-schedule-table {
        font-size: 0.8rem;
    }

    .delivery-schedule-table th,
    .delivery-schedule-table td {
        padding: 8px 6px;
    }
}
</style>

<div class="inserted-detail-section">
    <!-- 제품 소개 -->
    <div class="product-intro-box">
        <h3>합판 전단지</h3>
        <p>일정량의 고객 인쇄물을 한판에 모아서 인쇄 제작하는 상품으로 저렴한 가격과 빠른 제작시간이 특징인 상품입니다.</p>
        <p>일반 길거리 대량 배포용 전단지를 제작하실 때 선택하시면 됩니다.</p>
    </div>

    <!-- 제품 타입 비교 -->
    <h2>📊 제품 타입 비교</h2>
    <div class="product-comparison">
        <div class="product-type-box gang-plate">
            <h4>💰 합판전단지</h4>
            <p><strong>특징:</strong> 일정량의 고객 인쇄물을 한판에 모아서 인쇄 제작</p>
            <p><strong>장점:</strong> 저렴한 가격과 빠른 제작시간</p>
            <p><strong>용도:</strong> 일반 길거리 대량 배포용 전단지</p>
            <p><strong>제작방식:</strong> 제작비용을 나눠서 부담</p>
        </div>
        <div class="product-type-box exclusive-plate">
            <h4>⭐ 독판전단지</h4>
            <p><strong>특징:</strong> 나만의 인쇄물을 단독으로 인쇄</p>
            <p><strong>장점:</strong> 고급 인쇄물 제작, 다양한 용지 선택</p>
            <p><strong>용도:</strong> 고급 인쇄물 제작을 원할 때</p>
            <p><strong>후가공:</strong> 각종 박, 형압, 엠보, 타공, 접지, 코팅 등</p>
        </div>
    </div>

    <!-- 합판전단지 제작 가능 사이즈 -->
    <h2>📏 합판전단지 제작 가능 사이즈</h2>
    <div class="size-grid">
        <div class="size-item">A2<br>(420 × 594mm)</div>
        <div class="size-item">A3<br>(297 × 420mm)</div>
        <div class="size-item">A4<br>(210 × 297mm)</div>
        <div class="size-item">4절<br>(367 × 517mm)</div>
        <div class="size-item">8절<br>(257 × 367mm)</div>
        <div class="size-item">16절<br>(182 × 257mm)</div>
    </div>

    <div class="tip-box">
        <h4>TIP!</h4>
        <p><strong>작업사이즈:</strong> 재단사이즈에서 사방 1.5mm씩 여분<br>
        작업 템플릿을 다운 받아 사용하시면 더욱 더 정확하고 편리하게 작업하실 수 있습니다!</p>
    </div>

    <!-- 독판전단지 제작 가능 사이즈 -->
    <h2>📏 독판전단지 제작 가능 사이즈</h2>
    <div class="size-grid">
        <div class="size-item">A1[국전]<br>(594 × 841mm)</div>
        <div class="size-item">A3[국2절]<br>(420 × 594mm)</div>
        <div class="size-item">A3[국4절]<br>(297 × 420mm)</div>
        <div class="size-item">A4[국8절]<br>(297 × 420mm)</div>
        <div class="size-item">A5[국16절]<br>(147 × 210mm)</div>
        <div class="size-item">2절<br>(512 × 737mm)</div>
        <div class="size-item">4절<br>(367 × 517mm)</div>
        <div class="size-item">8절<br>(257 × 367mm)</div>
        <div class="size-item">16절<br>(185 × 257mm)</div>
    </div>

    <!-- 상세 정보 -->
    <h2>📋 상세 정보</h2>
    <div class="table-scroll-wrapper">
    <table class="detail-info-table">
        <thead>
            <tr>
                <th>구분</th>
                <th>작업사이즈</th>
                <th>인쇄유형</th>
                <th>출고</th>
                <th>후가공</th>
                <th>재질</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>독판전단지</strong></td>
                <td>재단사이즈에서 사방 1.5mm씩 여분</td>
                <td>옵셋인쇄</td>
                <td>매일 출고</td>
                <td>각종 박, 형압, 엠보, 타공, 접지, 코팅, 도무송, 접착, 오시, 미싱, 넘버링</td>
                <td>아트지, 스노우화이트, 모조지 등</td>
            </tr>
        </tbody>
    </table>
    </div>

    <!-- 용지별 특징 안내 -->
    <h2>📖 용지별 특징 안내</h2>
    <p style="margin-bottom: 15px; color: #666;">전단지 용지에 따라 인쇄 품질과 느낌이 달라집니다.</p>
    <div class="knowledge-grid">
        <div class="knowledge-item">
            <strong>아트지 (90g)</strong>
            <p>표면에 광택 코팅이 되어 색상이 선명합니다. 전단지, 홍보물에 가장 많이 사용되는 대표적인 용지입니다.</p>
        </div>
        <div class="knowledge-item">
            <strong>모조지 (80g)</strong>
            <p>코팅 없는 자연스러운 종이 질감입니다. 필기가 가능하고 가격이 경제적이나, 아트지에 비해 색감이 다소 연합니다.</p>
        </div>
        <div class="knowledge-item">
            <strong>스노우화이트</strong>
            <p>무광 코팅지로 아트지보다 차분하고 고급스러운 느낌입니다. 독판 인쇄 시 선택 가능합니다.</p>
        </div>
    </div>

    <div class="tip-box">
        <h4>합판인쇄 vs 독판인쇄</h4>
        <p><strong>합판인쇄:</strong> 여러 고객의 인쇄물을 한 판에 모아 인쇄합니다. 비용을 나누므로 <strong>가격이 저렴</strong>하고 소량 인쇄에 적합합니다.<br>
        <strong>독판인쇄:</strong> 한 고객의 인쇄물만 단독으로 인쇄합니다. <strong>색상 정밀도가 높고</strong> 다양한 용지·후가공 선택이 가능하지만 비용이 높습니다.</p>
    </div>

    <!-- 접수 출고안내 -->
    <h2>🚚 접수 출고안내</h2>
    <p style="margin-bottom: 15px; color: #666;">제품별 출고일을 확인해 보세요.</p>
    <div class="table-scroll-wrapper">
    <table class="delivery-schedule-table">
        <thead>
            <tr>
                <th rowspan="2">제품</th>
                <th rowspan="2">상세 용지</th>
                <th colspan="3">출고 및 접수안내</th>
                <th rowspan="2">배송가능방법</th>
            </tr>
            <tr>
                <th>접수마감</th>
                <th>예상출고</th>
                <th>출고일</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="3" class="product-name">합판전단지<br><small>(당일판 일반접수)</small></td>
                <td class="paper-type">아트지 (90g)</td>
                <td class="time-info">오전 11시</td>
                <td class="time-info">당일 18시 전후 (97% 출고 예상)</td>
                <td class="time-info">매일 오후 6시</td>
                <td class="delivery-method">택배 / 방문출고 / 퀵</td>
            </tr>
            <tr>
                <td class="paper-type">모조지 (80g)</td>
                <td colspan="3" style="text-align: center; color: #d32f2f; font-weight: bold;">당일판 진행안됨</td>
                <td class="delivery-method">일반 출고 일정</td>
            </tr>
        </tbody>
    </table>
    </div>

    <!-- 특징 박스들 -->
    <h2>✨ 주요 특징</h2>
    <div class="feature-boxes">
        <div class="feature-box">
            <h4>💰 경제적 가격</h4>
            <p>합판 시스템으로 제작비용을 분담하여 저렴한 가격 제공</p>
        </div>
        <div class="feature-box">
            <h4>⚡ 빠른 제작</h4>
            <p>당일 접수 당일 출고로 빠른 제작시간 보장</p>
        </div>
        <div class="feature-box">
            <h4>📦 다양한 배송</h4>
            <p>택배, 방문출고, 퀵 지원</p>
        </div>
        <div class="feature-box">
            <h4>🎯 대량 배포용</h4>
            <p>일반 길거리 대량 배포용 전단지에 최적화</p>
        </div>
    </div>

    <!-- 접수 가능 파일 -->
    <h2>📁 접수 가능 파일</h2>
    <div class="file-icons">
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #2196f3;">🎨</div>
            <span>Adobe<br>Illustrator</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #2196f3;">📄</div>
            <span>Adobe<br>Photoshop</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #2196f3;">🖥️</div>
            <span>CorelDRAW</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #2196f3;">📷</div>
            <span>디지털 사진 및<br>디지털 그래픽툴 공용</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #2196f3;">🎭</div>
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
        <p><strong>A2 (420 × 594mm) / A3 (297 × 420mm) / A4 (210 × 297mm)</strong><br>
        <strong>4절 (367 × 517mm) / 8절 (257 × 367mm) / 16절 (182 × 257mm)</strong></p>
        <p><strong>작업사이즈:</strong> 재단사이즈에서 사방 1.5mm씩 여분</p>
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