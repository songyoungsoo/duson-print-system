<?php
/**
 * 티켓(상품권) 상세 설명 페이지
 * 하단 설명방법 적용
 */
?>

<div class="ticket-detail-content">
    <h2>🎫 일반지 티켓</h2>
    <p>일반 용지부터 최고급 수입지까지 다양한 용지로 티켓을 제작하실 수 있습니다.<br>
    원하는 후가공을 선택해 이벤트에 맞는 티켓을 제작하세요.</p>

    <div class="detail-section">
        <h3>📏 제작 가능 사이즈</h3>
        <div class="size-list">
            <div class="size-item">158 x 72mm</div>
            <div class="size-item">168 x 72mm</div>
            <div class="size-item">172 x 72mm</div>
            <div class="size-item">148 x 68mm (신권)</div>
            <div class="size-item">160 x 73mm (구권)</div>
        </div>
        <p class="size-note">고객 입력 사이즈는 최소 <strong>50mm x 60mm</strong> 부터 최대 <strong>500mm x 500mm</strong> 입니다.<br>
        그 외의 사이즈는 초대장을 이용하시기 바랍니다.</p>
    </div>

    <div class="detail-section">
        <h3>📋 용지 종류</h3>

        <div class="ticket-type">
            <h4>🟦 비코팅 티켓</h4>
            <p>재질의 색상은 백색이며 재질 면은 아트지에 비해 촉감이 부드러우며,<br>
            광택이 없는 차분한 특징과 반사율이 적어 인쇄 후 은은한 광택을 연출하기도 합니다.</p>
            <div class="spec-info">
                <div class="spec-item"><strong>재질:</strong> 스노우화이트 250g</div>
                <div class="spec-item"><strong>출고:</strong> 매일 출고</div>
                <div class="spec-item"><strong>당일판 주문:</strong> 200매, 500매 접수 / 당일판 11시30분까지 접수 / 17시 전후 출고</div>
                <div class="spec-item"><strong>비고:</strong> 옵셋인쇄 / 디지털인쇄 / 당일판인쇄 가능</div>
            </div>
        </div>

        <div class="ticket-type">
            <h4>🟩 코팅 티켓</h4>
            <p>재질의 색상은 순백색이며, 구겨짐이 적고 잘 찢어지지 않는 특징이 있습니다.<br>
            인쇄 출력 시 배경색이 진할 경우에도 색 번짐이 없습니다.</p>
            <div class="spec-info">
                <div class="spec-item"><strong>재질:</strong> 스노우화이트 216g, 250g</div>
                <div class="spec-item"><strong>출고:</strong> 매일 출고</div>
                <div class="spec-item"><strong>당일판 주문:</strong> 200매, 500매 접수 / 당일판 11시30분까지 접수 / 17시 전후 출고</div>
                <div class="spec-item"><strong>비고:</strong> 옵셋인쇄 / 디지털인쇄 / 당일판인쇄 가능</div>
            </div>
        </div>

        <div class="ticket-type">
            <h4>🟨 고품격 코팅 티켓</h4>
            <p>일반 코팅 명함에 비하여 더 탄탄하고 두께감이 좋은 순백색의 스노우화이트 재질입니다.<br>
            고급 무광 라미네이팅 코팅으로 구겨짐이 적고 잘 찢어지지 않는 특징이 있습니다.</p>
            <div class="spec-info">
                <div class="spec-item"><strong>재질:</strong> 스노우화이트 300g, 400g, 유광코팅 300g, 고품격 코팅 250g</div>
                <div class="spec-item"><strong>출고:</strong> 250g, 300g 매일 / 400g 목 / 유광 300g 매일 출고</div>
                <div class="spec-item"><strong>당일판 주문:</strong> 200매, 500매 접수 / 당일판 11시30분까지 접수 / 17시 전후 출고<br>
                * 고품격 코팅 250g은 당일판 접수 가능</div>
                <div class="spec-item"><strong>비고:</strong> 옵셋인쇄 / 디지털인쇄 / 당일판인쇄 가능</div>
            </div>
        </div>
    </div>

    <div class="detail-section">
        <h3>🛡️ 코팅 종류 - 찢김의 정도</h3>
        <p class="section-desc">코팅의 종류에 따라 찢어지기도 하며 물기에 약하기도 합니다.<br>원하시는 용도에 맞게 선택하세요.</p>

        <div class="coating-comparison">
            <div class="coating-item tear">
                <div class="coating-title">비코팅명함</div>
                <div class="coating-status tear-yes">찢김</div>
            </div>
            <div class="coating-item no-tear">
                <div class="coating-title">코팅명함</div>
                <div class="coating-status tear-no">잘 찢어지지 않음</div>
            </div>
            <div class="coating-item no-tear">
                <div class="coating-title">고품격코팅명함</div>
                <div class="coating-status tear-no">잘 찢어지지 않음</div>
            </div>
        </div>
    </div>

    <div class="detail-section">
        <h3>🚚 접수 출고안내</h3>
        <p class="section-desc">제품별 출고일을 확인해 보세요.</p>

        <div class="delivery-table">
            <div class="table-header">
                <div class="col-product">제품</div>
                <div class="col-detail">상세 제품</div>
                <div class="col-schedule">출고일</div>
            </div>

            <div class="table-row">
                <div class="col-product">일반</div>
                <div class="col-detail">
                    <div class="product-type daily">코팅일반(216g)</div>
                    <div class="product-type daily">코팅일반(250g)</div>
                    <div class="product-type daily">코팅일반(고품격 270g)</div>
                    <div class="product-type daily">코팅일반(고품격 300g)</div>
                    <div class="product-type daily">유광코팅(고품격 300g)</div>
                    <div class="product-type daily">비코팅일반</div>
                    <div class="product-type schedule">코팅일반(고품격 400g)</div>
                </div>
                <div class="col-schedule">
                    <div class="schedule-daily">매일</div>
                    <div class="schedule-weekly">목 (400g만)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="detail-section">
        <h3>📁 접수가능 파일</h3>
        <div class="file-types">
            <div class="file-type">
                <img src="../../images/adobe-ai.png" alt="Adobe Illustrator" class="file-icon">
                <span>Adobe Illustrator</span>
            </div>
            <div class="file-type">
                <img src="../../images/adobe-ps.png" alt="Adobe Photoshop" class="file-icon">
                <span>Adobe Photoshop</span>
            </div>
            <div class="file-type">
                <img src="../../images/corel.png" alt="CorelDRAW" class="file-icon">
                <span>CorelDRAW</span>
            </div>
            <div class="file-type">
                <img src="../../images/photo.png" alt="디지털 사진" class="file-icon">
                <span>디지털 사진 및<br>디지털 그래픽툴 공용</span>
            </div>
            <div class="file-type">
                <img src="../../images/graphic.png" alt="그래픽툴" class="file-icon">
                <span>디지털 그래픽툴 공용</span>
            </div>
        </div>
    </div>

    <div class="detail-section important">
        <h3>⚠️ 작업 시 유의사항</h3>
        <div class="warning-box">
            <p><strong>반드시 작업 유의사항을 숙지하시고 주문해주시기 바랍니다.</strong><br>
            당사가 편집, 수정 작업을 할 수 없으며 작업 유의사항에 맞지 않은 데이터의 오류는 사고처리가 불가하십니다.</p>
        </div>

        <div class="notice-item">
            <h4>01 접수가능한 사이즈 범위</h4>
            <p><strong>158 x 72mm / 168 x 72mm / 172 x 72mm<br>
            148 x 68mm(신권) / 160 x 73mm(구권)</strong></p>
        </div>

        <div class="notice-item">
            <h4>02 작업/재단사이즈 설정</h4>
            <p>작업 사이즈와 재단 사이즈의 색은 <strong>선색 없음</strong> 처리</p>
        </div>

        <div class="notice-item">
            <h4>03 작업 주의사항</h4>
            <ul>
                <li>파일 업로드 시 돔보선은 넣지 마시고 안전선 / 재단선 / 작업선은 삭제가아닌 꼭 <strong>안 보이는 선색 없음</strong>으로 설정하셔야 합니다.</li>
                <li>글씨나 배경 색상 작업 시, CMYK가 섞인 먹색은 더블톤으로 나올 수 있으니 <strong>먹(K100)으로만</strong> 작업하셔야 선명하게 인쇄됩니다.</li>
                <li>모든 작업물은 <strong>CMYK로 작업</strong>하셔야 하시고 모든 글꼴은 <strong>아웃라인(곡선화)</strong> 하셔야 합니다. (Shift+Ctrl+O / Type - Create Outlines / 윤곽선 만들기)</li>
                <li>복잡한 개체나 특수한 효과를 사용한 것은 <strong>레스터화(비트맵)</strong> 하셔야 합니다.</li>
                <li>잠금(Lock)이 된 부분은 인쇄 시 빠지거나 위치가 변동되실 수 있으니 반드시 <strong>잠금을 해지</strong>하셔야 합니다.</li>
                <li>빠지는 개체 없이 <strong>그룹을 만들어</strong> 접수 부탁드립니다.</li>
                <li>사용하시는 이미지는 반드시 <strong>CMYK모드 - 300dpi 해상도</strong>로 작업하시고 파일 내 사용한 이미지의 링크 여부 확인하신 후 <strong>이미지 포함(EMBEDED)</strong> 하여 저장하셔야 합니다.</li>
            </ul>
        </div>

        <div class="notice-item">
            <h4>04 재단</h4>
            <ul>
                <li>합판 시스템 특성상 한 장씩 재단하는 것이 아니라 200~500장의 많은 양을 한 번에 누르면서 재단하므로 안쪽이나 바깥쪽으로 <strong>재단 오차</strong>가 발생합니다.</li>
                <li>테두리가 있거나 액자와 같은 형식의 디자인은 밀림현상으로 인하여 균등한 재단이 이루어지지 않으니 재단 사이즈에서 <strong>사방 3~4mm 여유</strong> 있는 작업을 하시면 육안상 많이 밀려 보이지 않으시니 작업 시 참고 부탁드립니다.</li>
                <li><strong>100mm 미만 사이즈</strong>는 재단 밀림으로 대각선으로 재단되실 수 있으므로 정밀한 재단을 원할 경우 도무송을 추천해드립니다.</li>
            </ul>
        </div>

        <div class="notice-item">
            <h4>05 납기 및 배송</h4>
            <ul>
                <li>합판 인쇄 시스템 특성상 인쇄 지연, 판 누락, 기기 고장, 연휴 기간, 데이터 이상으로 늦어질 수 있습니다.</li>
                <li>접수완료 된 다음날 <strong>97% 이상 출고</strong>되지만 2~3%는 오류가 발생될 수 있으므로 해외출장, 행사 등으로 날짜 및 시간을 약속하는 제품은 주문을 사양 하며 늦어진 출고로 인한 책임은 질 수 없습니다.</li>
                <li>고객 여러분의 편의를 위하여 배송업무를 대행하기에 <strong>물건의 검수 작업 없이 배송</strong> 됩니다.</li>
                <li>여러 가지 이유로 재작업 진행 후 납기지연으로 인한 배송, 퀵, 화물(착불) 등 요구시 발송은 가능하나 <strong>손해배상, 운송비는 부담하지 않습니다</strong>.</li>
                <li>제품 보관 기간은 <strong>5일간</strong>입니다. 보관 기간 이후에 폐기하므로 별도 보관을 요청할 경우에는 출고실로 연락 주셔야 합니다.</li>
                <li>모든 품목은 <strong>5~10% 미만의 수량 부족</strong>은 합판인쇄공정상 발생할 수 있으며, 후가공의 단계에 따라 더 발생할 수 있습니다. 이로 인한 환불이나 재작업은 불가합니다.</li>
            </ul>
        </div>
    </div>
</div>

<style>
.ticket-detail-content {
    max-width: 100%;
    line-height: 1.6;
    color: #333;
    font-family: 'Noto Sans KR', sans-serif;
}

.ticket-detail-content h2 {
    color: #9c27b0;
    font-size: 1.8rem;
    margin-bottom: 15px;
    border-bottom: 3px solid #9c27b0;
    padding-bottom: 10px;
}

.ticket-detail-content h3 {
    color: #2c3e50;
    font-size: 1.3rem;
    margin: 25px 0 15px 0;
    padding-left: 10px;
    border-left: 4px solid #9c27b0;
}

.ticket-detail-content h4 {
    color: #34495e;
    font-size: 1.1rem;
    margin: 15px 0 10px 0;
    font-weight: 600;
}

.detail-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.size-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
    margin: 15px 0;
}

.size-item {
    background: white;
    padding: 12px 15px;
    border: 2px solid #9c27b0;
    border-radius: 8px;
    font-weight: 600;
    color: #9c27b0;
    text-align: center;
}

.size-note {
    margin-top: 15px;
    padding: 12px;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    color: #856404;
}

.ticket-type {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.ticket-type h4 {
    margin-top: 0;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid #e9ecef;
}

.spec-info {
    margin-top: 15px;
}

.spec-item {
    padding: 8px 12px;
    margin-bottom: 5px;
    background: #f8f9fa;
    border-left: 3px solid #9c27b0;
    border-radius: 3px;
    font-size: 0.9rem;
}

.coating-comparison {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.coating-item {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.coating-item.tear {
    border-color: #dc3545;
}

.coating-item.no-tear {
    border-color: #28a745;
}

.coating-title {
    font-weight: 600;
    margin-bottom: 10px;
    color: #495057;
}

.coating-status {
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.9rem;
}

.tear-yes {
    background: #f8d7da;
    color: #721c24;
}

.tear-no {
    background: #d4edda;
    color: #155724;
}

.delivery-table {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
    margin: 15px 0;
}

.table-header {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
    background: #343a40;
    color: white;
    font-weight: 600;
}

.table-header > div {
    padding: 12px;
    border-right: 1px solid #495057;
    text-align: center;
}

.table-row {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
}

.table-row > div {
    padding: 15px 12px;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.product-type {
    margin-bottom: 4px;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.9rem;
    text-align: center;
}

.product-type.daily {
    background: #d4edda;
    color: #155724;
}

.product-type.schedule {
    background: #f8d7da;
    color: #721c24;
}

.schedule-daily {
    background: #d4edda;
    color: #155724;
    padding: 8px 12px;
    border-radius: 4px;
    font-weight: 500;
    margin-bottom: 5px;
}

.schedule-weekly {
    background: #f8d7da;
    color: #721c24;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.85rem;
}

.file-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.file-type {
    background: white;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-weight: 500;
}

.file-icon {
    width: 40px;
    height: 40px;
    margin-bottom: 8px;
    display: block;
    margin: 0 auto 8px auto;
}

.important {
    border-left: 5px solid #dc3545;
}

.warning-box {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.notice-item {
    margin-bottom: 25px;
    padding: 15px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.notice-item ul {
    margin: 10px 0;
    padding-left: 20px;
}

.notice-item li {
    margin-bottom: 8px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .table-header, .table-row {
        grid-template-columns: 1fr;
    }

    .table-header > div, .table-row > div {
        border-right: none;
        border-bottom: 1px solid #dee2e6;
    }

    .size-list {
        grid-template-columns: 1fr;
    }

    .coating-comparison {
        grid-template-columns: 1fr;
    }

    .file-types {
        grid-template-columns: 1fr;
    }
}
</style>