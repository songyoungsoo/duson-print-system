<?php
/**
 * 명함 상세 설명 페이지
 * 하단 설명방법 적용
 */
?>

<div class="namecard-detail-content">
    <h2>💼 일반지 명함</h2>
    <p>자사의 일반명함은 일반용지부터 최고급 수입지 까지 30가지의 다양한 용지로<br>
    제작이 가능하며, 다양한 후가공으로 나만의 특별한 명함 제작이 가능합니다.</p>

    <div class="detail-section" id="paper-texture-section">
        <h3>📜 명함의 재질</h3>
        <p class="section-desc">다양한 명함 용지의 질감과 색상을 확인해보세요. 이미지를 클릭하면 크게 볼 수 있습니다.</p>
        <p class="section-desc texture-notice">
            <strong>📐 사이즈 안내:</strong> 아래 이미지에 표시된 <span class="old-size">편집: 91×52mm / 재단: 89×50mm</span>는 현재 <span class="new-size">편집: 92×52mm / 재단: 90×50mm</span>로 변경되었음을 알려드립니다.<br>
            <strong>⚠️ 참고:</strong> 실제 질감은 미묘한 차이로 인해 이미지로 보여드리는 것은 한계가 있음을 양지해주시기 바랍니다.
        </p>

        <div class="texture-gallery">
            <?php
            $textureDir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/paper_texture/명함재질/';
            $webPath = '/ImgFolder/paper_texture/명함재질/';

            if (is_dir($textureDir)) {
                $files = scandir($textureDir);
                $imageFiles = array_filter($files, function($file) use ($textureDir) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) && is_file($textureDir . $file);
                });

                // 정렬
                sort($imageFiles);

                foreach ($imageFiles as $file) {
                    // pathinfo()는 한글 파일명에서 문제 발생 → preg_replace 사용
                    $textureName = preg_replace('/\.[^.]+$/', '', $file);
                    $imagePath = $webPath . rawurlencode($file);
                    ?>
                    <div class="texture-item" onclick="openTextureModal('<?php echo htmlspecialchars($imagePath); ?>', '<?php echo htmlspecialchars($textureName); ?>')">
                        <div class="texture-image-wrapper">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                 alt="<?php echo htmlspecialchars($textureName); ?> 재질"
                                 loading="lazy">
                            <div class="texture-overlay">
                                <span class="zoom-message">🔍 클릭하면 확대이미지가 보입니다</span>
                            </div>
                        </div>
                        <div class="texture-name"><?php echo htmlspecialchars($textureName); ?></div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-textures">재질 이미지 폴더를 찾을 수 없습니다.</p>';
            }
            ?>
        </div>
    </div>

    <div class="detail-section">
        <h3>📏 제작 가능 사이즈</h3>
        <div class="size-info">
            <div class="size-item">
                <strong>86mm x 52mm</strong> (작업사이즈 88mm x 54mm)
            </div>
            <div class="size-item">
                <strong>90mm x 50mm</strong> (작업사이즈 92mm x 52mm)
            </div>
        </div>
        <p class="size-note">고객 입력 사이즈는 최소 <strong>50mm x 60mm</strong>부터 최대 <strong>500mm x 500mm</strong>입니다.<br>
        그 외의 사이즈는 초대장을 이용하시기 바랍니다.</p>
    </div>

    <div class="detail-section">
        <h3>📋 명함종류</h3>

        <div class="namecard-type">
            <h4>🟦 비코팅 명함</h4>
            <p>재질의 색상은 백색이며 재질 면은 아트지에 비해 촉감이 부드러우며,<br>
            광택이 없는 차분한 특징과 반사율이 적어 인쇄 후 은은한 광택을 연출하기도 합니다.</p>
            <div class="spec-info">
                <div class="spec-item"><strong>재질:</strong> 스노우화이트 250g</div>
                <div class="spec-item"><strong>출고:</strong> 매일 출고</div>
                <div class="spec-item"><strong>당일판 주문:</strong> 200매, 500매 접수 / 당일판 11시까지 접수 / 17시 전후 출고</div>
                <div class="spec-item"><strong>비고:</strong> 옵셋인쇄 / 디지털인쇄 / 당일판인쇄 가능</div>
            </div>
        </div>

        <div class="namecard-type">
            <h4>🟩 코팅 명함 (216g / 250g)</h4>
            <p>재질의 색상은 순백색이며, 구겨짐이 적고 잘 찢어지지 않는 특징이 있습니다.<br>
            인쇄 출력 시 배경색이 진할 경우에도 색 번짐이 없습니다.</p>
            <div class="spec-info">
                <div class="spec-item"><strong>재질:</strong> 스노우화이트 216g, 250g</div>
                <div class="spec-item"><strong>출고:</strong> 매일 출고</div>
                <div class="spec-item"><strong>당일판 주문:</strong> 200매, 500매 접수 / 당일판 11시까지 접수 / 17시 전후 출고</div>
                <div class="spec-item"><strong>비고:</strong> 옵셋인쇄 / 디지털인쇄 / 당일판인쇄 가능</div>
            </div>
        </div>

        <div class="namecard-type">
            <h4>🟨 고품격 코팅 명함 (300g / 400g)</h4>
            <p>일반 코팅 명함에 비하여 더 탄탄하고 두께감이 좋은 순백색의 스노우화이트 재질입니다.<br>
            고급 무광 라미네이팅 코팅으로 구겨짐이 적고 잘 찢어지지 않는 특징이 있습니다.</p>
            <div class="spec-info">
                <div class="spec-item"><strong>재질:</strong> 스노우화이트 300g, 400g, 유광코팅 300g, 고품격 코팅 250g</div>
                <div class="spec-item"><strong>출고:</strong> 250g, 300g 매일 / 400g 목 / 유광 300g 매일 출고</div>
                <div class="spec-item"><strong>당일판 주문:</strong> 200매, 500매 접수 / 당일판 11시까지 접수 / 17시 전후 출고<br>
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

    <!-- 재질 확대 모달 -->
    <div id="textureModal" class="texture-modal" onclick="closeTextureModal(event)">
        <div class="texture-modal-content">
            <span class="texture-modal-close" onclick="closeTextureModal(event)">&times;</span>
            <img id="textureModalImage" src="" alt="">
            <div id="textureModalCaption" class="texture-modal-caption"></div>
        </div>
    </div>

    <div class="detail-section">
        <h3>🚚 접수 출고안내</h3>
        <p class="section-desc">제품별 출고일을 확인해 보세요.</p>

        <div class="delivery-table">
            <div class="table-header">
                <div class="col-product">제품</div>
                <div class="col-paper">상세 용지</div>
                <div class="col-quantity">매수</div>
                <div class="col-schedule">출고 및 접수안내</div>
                <div class="col-method">배송가능방법</div>
            </div>

            <div class="table-row">
                <div class="col-product">일반명함</div>
                <div class="col-paper">
                    <div class="paper-type daily">코팅일반(216g)</div>
                    <div class="paper-type daily">코팅일반(250g)</div>
                    <div class="paper-type daily">비코팅일반</div>
                    <div class="paper-type schedule">코팅일반(고품격270g)</div>
                    <div class="paper-type schedule">코팅일반(고품격300g)</div>
                    <div class="paper-type schedule">코팅일반(고품격400g) <span class="schedule-day">월,수</span></div>
                </div>
                <div class="col-quantity">
                    <div class="quantity-item daily">200/500장</div>
                    <div class="quantity-note">당일판 진행안됨</div>
                </div>
                <div class="col-schedule">
                    <div class="schedule-item">
                        <span class="label">오전판 접수마감:</span> 오전 11시<br>
                        <span class="label">예상출고:</span> 당일 18시 전후 (97% 출고 예상)<br>
                        <span class="label">출고일:</span> 매일<br>
                        <span class="label">접수마감:</span> 오후 6시
                    </div>
                </div>
                <div class="col-method">
                    택배 / 방문출고 / 퀵
                </div>
            </div>
        </div>
    </div>

    <div class="detail-section">
        <h3>📁 접수가능 파일</h3>
        <p>Adobe Illustrator, Adobe Photoshop, 디지털 사진 및 디지털 그래픽툴 공용, pdf, 엑셀, 파워포인트, 워드, 한글<br>
        기타는 전화문의</p>
    </div>

    <div class="detail-section important">
        <h3>⚠️ 작업 시 유의사항</h3>
        <div class="warning-box">
            <p><strong>반드시 작업 유의사항을 숙지하시고 주문해주시기 바랍니다.</strong><br>
            당사가 편집, 수정 작업을 할 수 없으며 작업 유의사항에 맞지 않은 데이터의 오류는 사고처리가 불가하십니다.</p>
        </div>

        <div class="notice-item">
            <h4>01 접수가능한 사이즈 범위</h4>
            <p>최소 <strong>50mm x 60mm</strong>부터 최대 <strong>500mm x 500mm</strong>입니다.</p>
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
.namecard-detail-content {
    max-width: 100%;
    line-height: 1.6;
    color: #333;
    font-family: 'Noto Sans KR', sans-serif;
}

.namecard-detail-content h2 {
    color: #2c5aa0;
    font-size: 1.8rem;
    margin-bottom: 15px;
    border-bottom: 3px solid #2c5aa0;
    padding-bottom: 10px;
}

.namecard-detail-content h3 {
    color: #2c3e50;
    font-size: 1.3rem;
    margin: 25px 0 15px 0;
    padding-left: 10px;
    border-left: 4px solid #2c5aa0;
}

.namecard-detail-content h4 {
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

.size-info {
    margin: 15px 0;
}

.size-item {
    background: white;
    padding: 12px 15px;
    margin-bottom: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-weight: 500;
    color: #495057;
}

.size-note {
    margin-top: 15px;
    padding: 12px;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    color: #856404;
}

.namecard-type {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.namecard-type h4 {
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
    border-left: 3px solid #2c5aa0;
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
    grid-template-columns: 1fr 2fr 1fr 2fr 1.5fr;
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
    grid-template-columns: 1fr 2fr 1fr 2fr 1.5fr;
}

.table-row > div {
    padding: 15px 12px;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
}

.paper-type {
    margin-bottom: 5px;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.9rem;
}

.paper-type.daily {
    background: #d4edda;
    color: #155724;
}

.paper-type.schedule {
    background: #f8d7da;
    color: #721c24;
}

.schedule-day {
    font-weight: 600;
    color: #dc3545;
}

.quantity-item {
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 3px;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.quantity-note {
    color: #dc3545;
    font-weight: 500;
    font-size: 0.9rem;
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

    .coating-comparison {
        grid-template-columns: 1fr;
    }

    .file-types {
        grid-template-columns: 1fr;
    }

    .texture-gallery {
        grid-template-columns: 1fr !important;
    }
}

/* 명함 재질 갤러리 스타일 */
.texture-gallery {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin: 20px 0;
}

.texture-item {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

.texture-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.texture-image-wrapper {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.texture-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.texture-item:hover .texture-image-wrapper img {
    transform: scale(1.1);
}

.texture-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}

.texture-item:hover .texture-overlay {
    background: rgba(0, 0, 0, 0.3);
}

.zoom-message {
    font-size: 0.95rem;
    font-weight: 500;
    color: white;
    background: rgba(0, 0, 0, 0.75);
    padding: 8px 16px;
    border-radius: 20px;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
    white-space: nowrap;
}

.texture-item:hover .zoom-message {
    opacity: 1;
    transform: translateY(0);
}

.texture-name {
    padding: 15px;
    text-align: center;
    font-weight: 600;
    font-size: 1.1rem;
    color: #2c3e50;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid #dee2e6;
}

.no-textures {
    text-align: center;
    color: #6c757d;
    padding: 40px;
    font-style: italic;
}

.texture-notice {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 1px solid #ffc107;
    border-left: 4px solid #ff9800;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    line-height: 1.8;
}

.texture-notice .old-size {
    text-decoration: line-through;
    color: #dc3545;
    background: #f8d7da;
    padding: 2px 6px;
    border-radius: 4px;
}

.texture-notice .new-size {
    color: #155724;
    background: #d4edda;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
}

/* 재질 모달 스타일 */
.texture-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.texture-modal-content {
    position: relative;
    margin: auto;
    padding: 20px;
    max-width: 90%;
    max-height: 90%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.texture-modal-content img {
    max-width: 100%;
    max-height: 80vh;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    animation: zoomIn 0.3s ease;
}

@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.texture-modal-close {
    position: absolute;
    top: 20px;
    right: 35px;
    color: #fff;
    font-size: 45px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 10001;
}

.texture-modal-close:hover {
    color: #f1c40f;
    transform: scale(1.2);
}

.texture-modal-caption {
    margin-top: 15px;
    color: #fff;
    font-size: 1.3rem;
    font-weight: 600;
    text-align: center;
    padding: 10px 25px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 25px;
    backdrop-filter: blur(5px);
}
</style>

<script>
// 재질 모달 열기
function openTextureModal(imageSrc, textureName) {
    const modal = document.getElementById('textureModal');
    const modalImg = document.getElementById('textureModalImage');
    const modalCaption = document.getElementById('textureModalCaption');

    modal.style.display = 'block';
    modalImg.src = imageSrc;
    modalImg.alt = textureName + ' 재질';
    modalCaption.textContent = textureName;

    // 스크롤 방지
    document.body.style.overflow = 'hidden';
}

// 재질 모달 닫기
function closeTextureModal(event) {
    // 모달 콘텐츠 클릭 시에는 닫지 않음
    if (event.target.classList.contains('texture-modal') ||
        event.target.classList.contains('texture-modal-close')) {
        const modal = document.getElementById('textureModal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('textureModal');
        if (modal.style.display === 'block') {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
});
</script>