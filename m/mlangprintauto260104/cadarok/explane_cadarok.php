<?php
/**
 * 리플렛/팜플렛 상세 설명 - 하단 설명방법
 * 색상 테마: #4caf50 (그린)
 */
?>

<style>
/* 리플렛/팜플렛 전용 스타일 */
.cadarok-detail-section {
    font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    color: #333;
}

.cadarok-detail-section h2 {
    color: #4caf50;
    border-bottom: 3px solid #4caf50;
    padding-bottom: 8px;
    margin-bottom: 20px;
    font-size: 1.4rem;
    font-weight: 600;
}

.cadarok-detail-section h3 {
    color: #4caf50;
    margin-top: 25px;
    margin-bottom: 15px;
    font-size: 1.2rem;
    font-weight: 600;
}

.cadarok-detail-section h4 {
    color: #388e3c;
    margin-top: 20px;
    margin-bottom: 10px;
    font-size: 1.1rem;
    font-weight: 600;
}

/* 제품 소개 박스 */
.product-intro-box {
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    border: 2px solid #4caf50;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    position: relative;
}

.product-intro-box::before {
    content: "📃";
    position: absolute;
    top: -15px;
    left: 20px;
    background: #fff;
    padding: 5px 10px;
    border-radius: 20px;
    border: 2px solid #4caf50;
    font-size: 1.2rem;
}

.product-intro-box h3 {
    color: #2e7d32;
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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #4caf50;
}

.size-item {
    background: white;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    color: #4caf50;
    border: 1px solid #a5d6a7;
    transition: all 0.3s ease;
}

.size-item:hover {
    background: #e8f5e8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
}

/* 접지 정보 테이블 */
.fold-info-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.fold-info-table th {
    background: #4caf50;
    color: white;
    padding: 15px;
    text-align: center;
    font-weight: 600;
}

.fold-info-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}

.fold-info-table tr:last-child td {
    border-bottom: none;
}

.fold-info-table tr:nth-child(even) {
    background: #e8f5e8;
}

/* 접지 종류 리스트 */
.fold-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin: 15px 0;
    padding: 15px;
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    border-radius: 8px;
    border: 1px solid #4caf50;
}

.fold-type-item {
    background: white;
    padding: 8px 12px;
    border-radius: 6px;
    text-align: center;
    font-size: 0.9rem;
    color: #4caf50;
    border: 1px solid #a5d6a7;
    transition: all 0.3s ease;
}

.fold-type-item:hover {
    background: #4caf50;
    color: white;
    transform: translateY(-1px);
}

/* 재질 리스트 */
.material-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 8px;
    margin: 15px 0;
    padding: 15px;
    background: #f1f8e9;
    border-radius: 8px;
    border: 1px solid #4caf50;
}

.material-item {
    background: white;
    padding: 6px 10px;
    border-radius: 4px;
    text-align: center;
    font-size: 0.85rem;
    color: #4caf50;
    border: 1px solid #c8e6c9;
    font-weight: 500;
}

/* 접지별 페이지 이미지 섹션 */
.fold-pages-section {
    margin: 25px 0;
    padding: 20px;
    background: white;
    border-radius: 12px;
    border: 2px solid #4caf50;
}

.fold-pages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.fold-page-item {
    text-align: center;
    padding: 10px;
    background: #f1f8e9;
    border-radius: 8px;
    border: 1px solid #c8e6c9;
    transition: all 0.3s ease;
}

.fold-page-item:hover {
    background: #e8f5e8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
}

.fold-page-item h5 {
    color: #4caf50;
    margin: 0 0 8px 0;
    font-size: 0.9rem;
    font-weight: 600;
}

/* 템플릿 다운로드 섹션 */
.template-download-section {
    margin: 25px 0;
    padding: 20px;
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    border-radius: 12px;
    border: 2px solid #4caf50;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin: 15px 0;
}

.template-item {
    background: white;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #4caf50;
    transition: all 0.3s ease;
    cursor: pointer;
}

.template-item:hover {
    background: #4caf50;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

/* 파일 형식 아이콘 */
.file-icons {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 20px 0;
    padding: 20px;
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    border-radius: 12px;
    border: 1px solid #4caf50;
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
    color: #4caf50;
    font-weight: 600;
}

/* 주의사항 박스 */
.warning-box {
    background: #fff3cd;
    border: 2px solid #4caf50;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
}

.warning-box h4 {
    color: #4caf50;
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
    background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
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

/* 반응형 디자인 */
@media (max-width: 768px) {
    .size-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 8px;
        padding: 15px;
    }

    .fold-types {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 8px;
    }

    .material-list {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 6px;
    }

    .file-icons {
        flex-wrap: wrap;
        gap: 15px;
    }

    .file-icon {
        flex: 0 0 calc(50% - 10px);
    }

    .template-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }

    .fold-pages-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
    }
}

/* 테이블 스크롤 래퍼 */
.table-scroll-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 20px 0;
}

.table-scroll-wrapper .fold-info-table {
    margin: 0;
    min-width: 550px;
}

/* 한글 줄바꿈 방지 */
.cadarok-detail-section td,
.cadarok-detail-section th {
    word-break: keep-all;
}
</style>

<div class="cadarok-detail-section">
    <!-- 제품 소개 -->
    <div class="product-intro-box">
        <h3>접지리플렛</h3>
        <p>인쇄물 낱장을 접어서 면을 구분하여 페이지로 나누어 주는 방식으로 페이지 수가 많지 않아 제본 방식이 필요 없는 경우 용이합니다.</p>
    </div>

    <!-- 제작 가능 사이즈 -->
    <h2>📏 제작 가능 사이즈</h2>
    <div class="size-grid">
        <div class="size-item">A3[국4절]<br>(297 × 420mm)</div>
        <div class="size-item">A4[국8절]<br>(297 × 420mm)</div>
        <div class="size-item">A5[국16절]<br>(147 × 210mm)</div>
        <div class="size-item">8절<br>(257 × 367mm)</div>
        <div class="size-item">16절<br>(185 × 257mm)</div>
    </div>

    <div class="tip-box">
        <h4>TIP!</h4>
        <p><strong>작업사이즈:</strong> 재단사이즈에서 사방 1.5mm씩 여분<br>
        작업 템플릿을 다운 받아 사용하시면 더욱 더 정확하고 편리하게 작업하실 수 있습니다!</p>
    </div>

    <!-- 상세 정보 -->
    <h2>📋 상세 정보</h2>
    <div class="table-scroll-wrapper">
    <table class="fold-info-table">
        <thead>
            <tr>
                <th>구분</th>
                <th>특징</th>
                <th>재질</th>
                <th>출고</th>
                <th>인쇄유형</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>접지리플렛</td>
                <td>페이지 수가 많지 않아 제본 방식이 필요 없는 경우 용이</td>
                <td>아트지, 스노우화이트, 모조지 등</td>
                <td>접수 완료 후 3~4일</td>
                <td>옵셋 인쇄</td>
            </tr>
        </tbody>
    </table>
    </div>

    <!-- 접지 종류 -->
    <h3>✂️ 접지 종류</h3>
    <div class="fold-types">
        <div class="fold-type-item">2단접지</div>
        <div class="fold-type-item">3단접지</div>
        <div class="fold-type-item">N자접지</div>
        <div class="fold-type-item">병풍접지</div>
        <div class="fold-type-item">대문접지</div>
        <div class="fold-type-item">자사접지</div>
    </div>

    <!-- 재질 종류 -->
    <h3>📄 재질 종류</h3>
    <div class="material-list">
        <div class="material-item">아트지</div>
        <div class="material-item">스노우화이트</div>
        <div class="material-item">모조지</div>
        <div class="material-item">이매진</div>
        <div class="material-item">몽블랑</div>
        <div class="material-item">랑데뷰</div>
        <div class="material-item">르네상스</div>
        <div class="material-item">르느와르</div>
    </div>

    <!-- 접지별 페이지 안내 -->
    <h2>📖 접지별 페이지 안내</h2>
    <div class="fold-pages-section">
        <div class="fold-pages-grid">
            <div class="fold-page-item">
                <h5>2단 접지</h5>
                <p>4페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>3단 접지</h5>
                <p>6페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>3단 접지후 반접지</h5>
                <p>12페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>4단 접지</h5>
                <p>8페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>4단 접지: 병품접지</h5>
                <p>8페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>4단 접지: 두루마리 접지</h5>
                <p>8페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>4단 병풍 후 반접지</h5>
                <p>16페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>5단 병풍접지</h5>
                <p>10페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>6단 병풍접지</h5>
                <p>12페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>6단 병풍 후 반접지</h5>
                <p>24페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>7단 병풍 후 반접지</h5>
                <p>28페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>N접지</h5>
                <p>6페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>N접지 후 반접지</h5>
                <p>12페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>대문접지</h5>
                <p>8페이지</p>
            </div>
            <div class="fold-page-item">
                <h5>십자접지</h5>
                <p>8페이지</p>
            </div>
        </div>
    </div>

    <!-- 2단 접지 템플릿 다운로드 -->
    <h2>💾 2단 접지 템플릿 다운로드</h2>
    <div class="template-download-section">
        <div class="template-grid">
            <div class="template-item">B6_32절2단접지</div>
            <div class="template-item">B5_16절2단접지</div>
            <div class="template-item">B4_8절2단접지</div>
            <div class="template-item">B3_4절2단접지</div>
            <div class="template-item">A5_16절2단접지</div>
            <div class="template-item">A4_8절2단접지</div>
            <div class="template-item">A3_4절2단접지</div>
            <div class="template-item">A2_2절2단접지</div>
        </div>
    </div>

    <div class="warning-box">
        <ul>
            <li><strong>디자인하시는 시각에 따라 페이지가 달라질 수 있으므로</strong> 디자인 완료 후 바깥 면과 안쪽 면이 맞는지 접어서 확인하신 다음 접수해 주시기 바랍니다.</li>
            <li><strong>제작 사이즈에 따라 불가능한 접지 종류가 있을 수 있으며</strong> 접지 시 1~2mm 오차가 발생합니다.</li>
            <li><strong>일반적으로 종이 두께가 180g 이상의 종이로 제작하실 경우에는</strong> 접힌 뒷면에 터짐 현상으로 인하여 인쇄물이 손상 되어 보일 수 있습니다.</li>
        </ul>
    </div>

    <div class="tip-box">
        <h4>TIP!</h4>
        <p>작업 템플릿을 다운 받아 사용하시면 더욱 더 정확하고 편리하게 작업하실 수 있습니다!</p>
    </div>

    <!-- 접수 가능 파일 -->
    <h2>📁 접수 가능 파일</h2>
    <div class="file-icons">
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #4caf50;">🎨</div>
            <span>Adobe<br>Illustrator</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #4caf50;">📄</div>
            <span>Adobe<br>Photoshop</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #4caf50;">🖥️</div>
            <span>CorelDRAW</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #4caf50;">📷</div>
            <span>디지털 사진 및<br>디지털 그래픽툴 공용</span>
        </div>
        <div class="file-icon">
            <div style="font-size: 2.5rem; color: #4caf50;">🎭</div>
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
        <p><strong>A3[국4절] (297 × 420mm) / A4[국8절] (297 × 420mm) / A5[국16절] (147 × 210mm)</strong><br>
        <strong>8절 (257 × 367mm) / 16절 (185 × 257mm)</strong></p>
        <p><strong>작업사이즈:</strong> 재단사이즈에서 사방 1.5mm씩 여분<br>
        작업 템플릿을 다운 받아 사용하시면 더욱 더 정확하고 편리하게 작업하실 수 있습니다!</p>
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