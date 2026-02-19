<?php
/**
 * 공통 푸터 파일
 * 경로: includes/footer.php
 */
?>
        </div> <!-- main-content-wrapper 끝 -->

        <!-- 플로팅 우측 사이드바 -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- 푸터 -->
        <footer class="compact-footer">
            <div class="footer-compact-container">
                <!-- 상단 네비게이션 링크 -->
                <div class="footer-nav">
                    <a href="/" class="footer-logo">두손기획인쇄</a>
                    <span class="nav-divider">|</span>
                    <a href="/sub/company.php" class="footer-nav-link">회사소개</a>
                    <span class="nav-divider">|</span>
                    <a href="javascript:void(0);" onclick="openTermsModal()" class="footer-nav-link">이용약관</a>
                    <span class="nav-divider">|</span>
                    <a href="javascript:void(0);" onclick="openPrivacyModal()" class="footer-nav-link">개인정보처리방침</a>
                    <span class="nav-divider">|</span>
                    <a href="https://map.kakao.com/?q=%EC%84%9C%EC%9A%B8%EC%8B%9C+%EC%98%81%EB%93%B1%ED%8F%AC%EA%B5%AC+%EC%98%81%EB%93%B1%ED%8F%AC%EB%A1%9C+36%EA%B8%B8+9+%EC%86%A1%ED%98%B8%EB%B9%8C%EB%94%A9" target="_blank" rel="noopener noreferrer" class="footer-nav-link">오시는길</a>
                    <span class="nav-divider">|</span>
                    <a href="/sub/customer/notice.php" target="_blank" rel="noopener noreferrer" class="footer-nav-link">공지사항</a>
                    <span class="nav-divider">|</span>
                    <a href="/sub/customer/how_to_use.php" class="footer-nav-link">고객센터</a>
                </div>

                <!-- 회사 정보 및 인증마크 -->
                <div class="footer-info-section">
                    <div class="footer-info-left">
                        <!-- 회사 정보 -->
                        <div class="footer-info">
                            <div class="company-main">
                                <span class="company-name">두손기획인쇄</span>
                                <span>대표자 : 차경선</span>
                                <span class="info-divider">|</span>
                                <span>TEL 1688-2384 / 02-2632-1830</span>
                                <span class="info-divider">|</span>
                                <span>FAX 02-2362-1829</span>
                                <span class="info-divider">|</span>
                                <span>E-mail : dsp1830@naver.com</span>
                            </div>
                            <div class="company-detail">
                                <span>사업자등록번호 107-06-45106</span>
                                <span class="info-divider">|</span>
                                <span>통신판매업신고 영등포 제2008-0105</span>
                                <span class="info-divider">|</span>
                                <span>주소 : 서울시 영등포구 영등포로 36길 9 송호빌딩 1층</span>
                            </div>
                        </div>

                        <!-- 하단 저작권 -->
                        <div class="copyright-section">
                            <div class="copyright-text">
                                COPYRIGHT© 2004 두손기획인쇄 ALL RIGHTS RESERVED
                            </div>
                        </div>
                    </div>

                    <!-- 인증마크 (우측) -->
                    <div class="footer-logos-right">
                        <!-- 공정거래위원회 -->
                        <a href="https://www.ftc.go.kr/" target="_blank" rel="noopener noreferrer" title="공정거래위원회" class="cert-logo-link">
                            <img src="/images/logo-ftc.png" alt="공정거래위원회" class="cert-logo ftc-logo" onerror="this.outerHTML='<svg width=107 height=35 viewBox=%220 0 107 35%22 xmlns=%22http://www.w3.org/2000/svg%22><rect fill=%22%23fff%22 width=%22107%22 height=%2235%22 rx=%224%22/><text x=%2253.5%22 y=%2222%22 text-anchor=%22middle%22 font-size=%2212%22 font-family=%22Arial,sans-serif%22 fill=%22%23333%22>공정거래위원회</text></svg>';" />
                        </a>
                        <!-- 금융결제원 -->
                        <a href="https://www.kftc.or.kr/" target="_blank" rel="noopener noreferrer" title="금융결제원" class="cert-logo-link">
                            <img src="/images/logo-kftc.png" alt="금융결제원" class="cert-logo kftc-logo" onerror="this.outerHTML='<svg width=98 height=35 viewBox=%220 0 98 35%22 xmlns=%22http://www.w3.org/2000/svg%22><rect fill=%22%23fff%22 width=%2298%22 height=%2235%22 rx=%224%22/><text x=%2249%22 y=%2222%22 text-anchor=%22middle%22 font-size=%2212%22 font-family=%22Arial,sans-serif%22 fill=%22%23333%22>금융결제원</text></svg>';" />
                        </a>
                        <!-- KB 에스크로 -->
                        <a href="javascript:onPopKBAuthMark();" title="KB 에스크로 가입 사실 확인" class="cert-logo-link">
                            <img src="/images/escrowcmark.gif" alt="KB 에스크로 인증마크" class="cert-logo kb-logo" onerror="this.outerHTML='<svg width=60 height=60 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22><rect fill=%22%23fff%22 width=%2260%22 height=%2260%22 rx=%2260%22/><text x=%2230%22 y=%2237%22 text-anchor=%22middle%22 font-size=%2211%22 font-family=%22Arial,sans-serif%22 fill=%22%23333%22>KB</text></svg>';" />
                        </a>
                    </div>
                </div>

                <!-- KB에스크로 이체 인증마크 Form -->
                <form name="KB_AUTHMARK_FORM" method="get" style="display:none;">
                    <input type="hidden" name="page" value="C021590"/>
                    <input type="hidden" name="cc" value="b034066:b035526"/>
                    <input type="hidden" name="mHValue" value="ef04cec95f1a7298f1f686bfe3159ade"/>
                </form>

                <!-- 이용약관 모달 -->
                <div id="termsModal" class="legal-modal">
                    <div class="legal-modal-content">
                        <div class="legal-modal-header">
                            <span>두손기획인쇄 이용약관</span>
                            <button class="legal-modal-close" onclick="closeTermsModal()">&times;</button>
                        </div>
                        <div class="legal-modal-body" style="overflow-y: auto; padding: 30px; color: #333; line-height: 1.8;">
                            <h2 style="color: #2c3e50; font-size: 20px; font-weight: 700; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">제 1장 총칙</h2>

                            <p><strong>제 1조 (목적)</strong><br>
                            이 약관은 두손기획인쇄(이하 "회사"라 한다.)의 웹상에 제공하는 모든 컨텐츠 및 정보제공 서비스를 이용하는 조건 및 절차에 관한 기본적인 사항을 정함을 목적으로 합니다.</p>

                            <p><strong>제 2조 (약관의 효력 및 변경)</strong><br>
                            1. 이 약관은 이용고객에게 회원가입시 공지함으로써 효력을 발생합니다.<br>
                            2. 회사는 사정상 중요한 사유가 발생될 경우 사전 고지 없이 이 약관의 내용을 변경할 수 있으며, 변경된 약관은 제1항과 같은 방법으로 공지 또는 통지함으로써 효력이 발생됩니다.<br>
                            단, 요금 등 이용고객의 권리 또는 의무에 관한 중요한 규정의 변경은 최소한 30일전에 공시합니다.<br>
                            3. 회원은 변경된 약관에 동의하지 않을 경우 회원 탈퇴를 요청할 수 있으며, 변경된 약관의 효력 발생일 이후에도 서비스를 계속 사용할 경우 약관의 변경 사항에 동의한 것으로 간주됩니다.</p>

                            <p><strong>제 3조 (약관의 적용)</strong><br>
                            1. 이 약관의 공지 및 변경사항은 회사의 지정된 홈페이지에 게시하는 방법으로 공지합니다.<br>
                            2. 이 약관에 명시되지 않은 사항은 관계법령 및 이 약관의 취지에 따라 적용합니다.<br>
                            3. 회원 및 광고주회원은 ID, 비밀번호의 관리를 철저히 해야 하며 등록된 ID, 비밀번호가 잘못 이용될 경우 회원에게 책임이 있습니다.</p>

                            <p><strong>제 4조 (용어정의)</strong><br>
                            이 약관에서 사용하는 용어의 정의는 다음과 같습니다.<br>
                            1. 서비스 : 본 사이트에서 제공하는 모든 정보를 말합니다.<br>
                            2. 회원 : 서비스이용을 신청하고 ID/비밀번호를 발급 받은 자를 말합니다.<br>
                            3. 이용계약 : 사이트와 회원간의 정보사용에 관하여 사전 규정 정의하고, 이에 서로가 동의하는 것을 말합니다.<br>
                            4. 회원ID: 회원 식별과 서비스이용을 위하여 회원이 선정하고 회사가 인정한 문자와 숫자의 조합입니다.<br>
                            5. 비밀번호 : 회원ID와 일치된 자임을 확인하고, 회원의 비밀을 보호하기위해 회원이 선정한 문자와 숫자의 조합입니다.<br>
                            6. 회원정보 : 회원이 회사에 서비스 신청할 시 회사에 등록하는 회원ID, 이름, 주소, 전화번호 등의 신상정보를 말합니다.<br>
                            7. 해지(회원): 회사 또는 회원이 서비스개통후에 24시간안에 한하여 해지의사를 회사측으로 통보할 수 있습니다.</p>

                            <h2 style="color: #2c3e50; font-size: 20px; font-weight: 700; margin: 30px 0 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">제 2장 서비스 이용 신청 및 승낙</h2>

                            <p><strong>제 5조 이용계약의 성립및 단위</strong><br>
                            1. '이용약관에 동의하십니까?'에 '동의함'을 누르면 동의하는 것으로 간주합니다.<br>
                            2. 이용계약은 서비스 이용희망자의 이용신청에 대해 회사가 이용승낙을 함으로써 성립됩니다.<br>
                            3. 이용계약은 회원 ID 단위로 체결하며 계약단위는 1인 1개의 이용ID로 합니다.<br>
                            단, 이용자가 ID추가 요청시 제시한 사유를 회사가 인정한 경우에는 성립됩니다.</p>

                            <p><strong>제 6조 회원정의</strong><br>
                            회원은 다음과 같이 구별됩니다.<br>
                            1. 일반 이용자 회원<br>
                            2. 홈페이지 툴을 발급받은 회원</p>

                            <p><strong>제 7조 이용신청</strong><br>
                            1. 이용신청은 온라인으로 가입신청양식(회원가입 폼)에 기록하여 신청합니다.<br>
                            2. 이용회원은 반드시 자신의 본명 및 주민등록번호로 신청하여야 하며, 회사는 이용약관의 주요내용을 고지하고 회원의 동의를 받아 가입처리를 하여야 합니다.</p>

                            <p><strong>제 8조 이용신청의 승낙</strong><br>
                            1. 회원은 제6조에서 정한 모든 사항을 정확히 기재하여 이용신청을 하였을 때 승낙받습니다.<br>
                            2. 회사는 제6조의 규정에 의한 이용신청 고객에 대하여 업무 수행상 또는 기술상 지장이 없는 경우에는 원칙적으로 접수와 동시에 서비스 이용을 승낙합니다.<br>
                            3. 접수 후 이용신청을 승낙하는 때에는 이를 회원에게 서비스를 통하거나 제공받는 방법으로 진행합니다.</p>

                            <p><strong>제 9조 (회원 ID 부여 및 변경)</strong><br>
                            1. 회사는 회원이 신청한 ID를 자동으로 승인합니다.<br>
                            2. 회원은 멤버쉽을 통해 언제든지 본인의 개인정보를 열람하고 수정할 수 있습니다.<br>
                            3. 회원은 이용신청시 기재한 사항이 변경되었을 경우 온라인으로 수정을 해야 하며 회원정보의 미변경으로 인하여 발생되는 문제의 책임은 회원에게 있습니다.</p>

                            <h2 style="color: #2c3e50; font-size: 20px; font-weight: 700; margin: 30px 0 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">제 3장 서비스 이용</h2>

                            <p><strong>제 10조 서비스 이용 범위</strong><br>
                            회원은 dsp114.com을 통한 가입 시 회사 사이트 모든 정보를 이용할 수 있습니다.</p>

                            <p><strong>제 11조 (회사의 회원정보 사용에 대한 동의)</strong><br>
                            1. 회사에서 회원서비스의 양적 질적 증대를 목적으로 이용자의 개인 식별이 가능한 개인정보를 활용하여 제휴서비스(인터넷 쇼핑 서비스, 커뮤니케이션 서비스, 멤버쉽 카드 서비스, 보험, 은행, 신용카드 등)를 TM, DM, E-mail, 마케팅 서비스 등에 이용할 수 있습니다.<br>
                            2. 회원이 http://www.dsp114.com에 회원가입이 되면 본사와 전략제휴한 제휴사이트의 회원으로 공유되어 제휴사이트에서 별도의 가입을 하지 않아도 하나의 ID로 양쪽 사용이 가능합니다.<br>
                            3. 회사에서 제공하는 제휴 서비스나 제휴사 제공서비스는 사정에 따라 추가 또는 변경될 수 있습니다.</p>

                            <p><strong>제 12조 (요금 및 유료정보 등)</strong><br>
                            회사가 제공하는 서비스는 기본적으로 무료입니다.<br>
                            단, 회사가 명시한 일부 서비스에 대한 유료서비스 항목은 회원에게 미리 공지하여 회원으로 하여금 유료서비스를 선택 할 수 있게 한다.</p>

                            <p><strong>제 13조 (회원의 게시물)</strong><br>
                            회사는 회원이 게시하거나 등록하는 서비스내의 내용물이 각 호에 해당한다고 판단되는 경우 사전 통보 없이 삭제할 수 있습니다.<br>
                            - 다른 회원 또는 제3자를 비방하거나 중상모략으로 명예를 손상시키는 내용인 경우<br>
                            - 공공질서 및 미풍양속에 위반되는 내용인 경우<br>
                            - 범죄적 행위에 결부된다고 인정되는 내용일 경우<br>
                            - 제3자의 저작권등 기타권리를 침해하는 내용의 경우<br>
                            - 회사에서 규정한 게시시간을 초과한 경우<br>
                            - 기타 관계법령에 위반된다고 판단되는 경우<br>
                            - 회사의 사전동의 없는 상업적 목적의 게시물</p>

                            <p><strong>제 14조 (게시물의 저작권)</strong><br>
                            서비스에 게시된 자료에 대한 권리는 다음 각 호와 같습니다.<br>
                            1. 게시물에 대한 권리와 책임은 게시자에게 있으며 회사는 게시자의 동의 없이는 이를 영리적 목적으로 사용할 수 없습니다. 단 비영리적인 경우는 그러하지 아니하며 또한 서비스내의 게시권을 갖습니다.<br>
                            2. 회원은 서비스를 이용하여 얻은 정보를 가공, 판매하는 행위등 서비스에 게시된 자료를 상업적으로 사용할 수 없습니다. 단, 비영리적인 경우는 그러하지 아니하며 또한 서비스내의 게시권을 갖습니다.</p>

                            <p><strong>제 15조 (광고게재 및 광고주와의 거래)</strong><br>
                            1. 회사가 회원에게 서비스를 제공할 수 있는 서비스 투자기반의 일부는 광고게재를 통한 수익으로부터 나옵니다. 서비스를 이용하고자 하는 자는 서비스 이용시 노출되는 광고게재에 대해 동의하는 것으로 간주됩니다.<br>
                            2. 회사는 본 서비스상에 게재되어 있거나 본 서비스를 통한 광고주의 판촉활동에 회원이 참여하거나 교신 또는 거래의 결과로서 발생하는 모든 손실 또는 손해에 대해 책임을 지지 않습니다.<br>
                            3. 홈페이지에는 본사에서 진행하는 광고 마케팅에 의해 부분적인 형태로 배너가 개제될 수 있습니다.</p>

                            <p><strong>제 16조 (서비스 이용시간)</strong><br>
                            1. 서비스의 이용은 회사의 업무상 또는 기술상 특별한 지장이 없는 한 연중무휴 1일 24시간 원칙으로 합니다. 다만 정기점검 등의 필요로 회사가 정한 날이나 시간은 서비스 이용시간 제외대상에 속합니다.<br>
                            2. 회사는 서비스를 일정범위로 분할하여 각 범위별로 이용가능시간을 별도로 정할 수 있으며 이 경우 그 내용을 사전에 공지해야합니다.</p>

                            <p><strong>제 17조 (서비스 이용 책임)</strong><br>
                            회원은 회사에서 권한 있는 사원이 서명한 명시적인 서면에 구체적으로 허용한 경우를 제외하고는 서비스를 이용하여 상품을 판매하는 영업활동을 할 수 없으며 특히 해킹, 돈벌이 광고, 음란 사이트 등을 통한 상업행위, 상용S/W 불법배포 등을 할 수 없습니다.<br>
                            이를 어기고 발생한 영업활동의 결과 및 손실, 관계기관에 의한 구속 등 법적 조치등에 관해서는 회사가 책임을 지지 않습니다.</p>

                            <p><strong>제 18조 (서비스의 미 제공 및 제한)</strong><br>
                            1. 회사는 다음에 각호에 해당하는 이용신청에 대하여는 서비스를 제공하지 아니합니다.<br>
                            - 신청자가 미성년자인 경우<br>
                            - 다른 사람의 명의를 사용하여 신청하였을 때<br>
                            - 내용을 허위로 기재하였거나 허위서류를 첨부하여 신청하였을 때<br>
                            - 이용고객이 회사와 계약한 서비스 이외의 비정상적인 방법으로의 이용 및 상업적으로의 이용이 예상될 경우<br>
                            - 서비스의 정상적인 제공을 저해하거나 타 이용고객의 서비스 이용에 지장을 줄 것으로 예상될 경우<br>
                            - 사회의 안녕 질서 또는 미풍양속을 저해할 목적으로 신청하였을 때<br>
                            - 이용신청 요건이 미비 되었거나 본인의 확인이 불가능할 경우<br>
                            - 기타 이용신청자의 귀책 사유로 이용승낙이 곤란한 경우</p>

                            <p>2. 회사는 전항의 규정에 의하여 이용신청이 승낙되지 않거나 승낙을 제한하는 경우에는 이를 E-mail 또는 전화 등으로 이용신청회원에게 통지해야 합니다. 다만 이용신청자에게 책임이 있는 사유로 통지할 수 없을 때에는 그러하지 아니합니다.</p>

                            <p><strong>제 19조 (계약 해지)</strong><br>
                            이용자가 서비스 이용 계약을 해지하고자 할 때에는 이용자 본인이 직접 온라인, 전화, 팩스, 메일 등의 방법을 통해 회사에 해지 신청을 하여야 합니다.</p>

                            <p><strong>제 20조 (서비스 제공 개시일)</strong><br>
                            서비스 제공 개시일은 이용자가 제 6 조에서 정한 사항을 정확히 기재하여 이용 신청을 완료한 시점 또는 제 7 조 2 항의 신청 승낙 유보 사항이 해소된 시점으로부터 합니다.</p>

                            <h2 style="color: #2c3e50; font-size: 20px; font-weight: 700; margin: 30px 0 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">제 4장 회사 및 회원의 의무</h2>

                            <p><strong>제 21조 (회사의 의무)</strong><br>
                            1. 회사는 특별한 사정이 없는 한 회원이 서비스 이용을 신청한 날에 서비스를 이용할 수 있도록 합니다.<br>
                            2. 회사는 이 약관에서 정한 바에 따라 계속적이고 안정적인 서비스의 제공을 위하여 지속적으로 노력하며, 설비에 장애가 생기거나 멸실된 때에는 지체 없이 이를 수리 복구하여야 합니다. 다만, 천재지변, 비상사태 또는 그 밖에 부득이한 경우에는 그 서비스를 일시 중단하거나 중지할 수 있습니다.<br>
                            3. 회사는 이용계약의 체결, 계약사항의 변경 및 해지 등 이용고객과의 계약 관련 절차 및 내용 등에 있어 이용고객에게 편의를 제공하도록 노력합니다.<br>
                            4. 회사는 서비스 제공과 관련하여 취득한 회원의 정보를 본인의 사전 승낙 없이 타인에게 누설 또는 배포 할 수 없으며 상업적 목적으로 사용할 수 없습니다. 단, 아래의 경우는 예외로 합니다.<br>
                            - 관계 법령에 의거하여 수사기관이나 정보통신윤리위원회 및 신용정보기관이 요청하여 개인정보를 제공하는 경우<br>
                            - 특정개인을 식별할 수 없는 통계작성, 홍보자료, 학술연구 등의 목적일 경우<br>
                            - 동호회나 부가서비스 등 관리책임자가 별도로 있는 서비스에 가입 신청하면서 입력한 정보를 해당 서비스 제공자가 누설 및 배포한 경우<br>
                            - 전자우편 서비스 등 다양한 서비스 제공을 위하여 회원의 등록정보를 집단적인 형태로 사용하는 경우<br>
                            - 타인에게 피해를 주거나 미풍양속을 해치는 내용의 메일을 보내거나 글을 게재하는 경우</p>

                            <p><strong>제 22조 (회원의 의무)</strong><br>
                            1. 회원은 이 약관에서 규정하는 사항과 서비스 이용안내 또는 주의사항 등 회사가 공지 혹은 통지하는 사항을 준수하여야 하며, 기타 회사의 업무에 방해되는 행위를 하여서는 아니됩니다.<br>
                            2. 회원의 ID와 비밀번호에 관한 모든 관리책임은 회원에게 있습니다. 회원에게 부여된 ID와 비밀번호의 관리 소홀, 부정 사용에 의하여 발생하는 모든 결과에 대한 책임은 회원에게 있습니다.<br>
                            3. 회원은 자신의 ID나 비밀번호가 부정하게 사용되었다는 사실을 발견한 경우에는 즉시 회사에 신고하여야 하며, 신고를 하지 않아 발생하는 모든 결과에 대한 책임은 회원에게 있습니다.<br>
                            4. 회원은 내용별로 회사가 서비스 공지사항에 게시하거나 별도로 공지한 이용제한 사항을 준수하여야 합니다.<br>
                            5. 회원은 회사의 사전승낙 없이는 서비스를 이용하여 영업활동을 할 수 없으며, 그 영업활동의 결과와 회원이 약관에 위반한 영업활동을 하여 발생한 결과에 대하여 회사는 책임을 지지 않습니다. 회원은 이와 같은 영업활동으로 회사가 손해를 입은 경우 회원은 회사에 대하여 손해배상의무를 집니다.<br>
                            6. 회원은 회사의 명시적인 동의가 없는 한 서비스의 이용권한, 기타 이용계약상 지위를 타인에게 양도, 증여할 수 없으며, 이를 담보로 제공할 수 없습니다.<br>
                            7. 회원은 서비스 이용과 관련하여 다음 각 호의 1에 해당되는 행위를 하여서는 안됩니다.<br>
                            - 다른 회원의 ID와 비밀번호, 주민등록번호 등을 도용하는 행위<br>
                            - 본 서비스를 통하여 얻은 정보를 회사의 사전승낙 없이 회원의 이용 이외 목적으로 복제하거나 이를 출판 및 방송 등에 사용하거나 제3자에게 제공하는 행위<br>
                            - 타인의 특허, 상표, 영업비밀, 저작권 기타 지적재산권을 침해하는 내용을 게시, 전자메일 또는 기타의 방법으로 타인에게 유포하는 행위<br>
                            - 공공질서 및 미풍양속에 위반되는 저속, 음란한 내용의 정보, 문장, 도형 등을 전송, 게시, 전자메일 또는 기타의 방법으로 타인에게 유포하는 행위<br>
                            - 모욕적이거나 위협적이어서 타인의 프라이버시를 침해할 수 있는 내용을 전송, 게시, 전자메일 또는 기타의 방법으로 타인에게 유포하는 행위<br>
                            - 범죄와 결부된다고 객관적으로 판단되는 행위<br>
                            - 회사의 승인을 받지 않고 다른 사용자의 개인정보를 수집 또는 저장하는 행위<br>
                            - 기타 관계법령에 위배되는 행위<br>
                            * 이는 전기통신법 제53조와 전기통신사업법 시행령 16조(불온통신), 통신사업법 제53조3항에 의거합니다.</p>

                            <h2 style="color: #2c3e50; font-size: 20px; font-weight: 700; margin: 30px 0 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">제 5장 환불 처리</h2>

                            <p>1. 통신망의 이상으로 인하여 서비스를 제공할 수 없는 경우에는 서비스 제공 중지에 대한 책임이 면제됩니다.<br>
                            2. 회사는 이용고객의 귀책사유로 인한 서비스 이용의 장애에 대하여는 책임이 면제 됩니다.<br>
                            3. 회사는 회원이 서비스 제공으로부터 기대되는 이익을 얻지 못하였거나 서비스 자료에 대한 취사선택 또는 이용으로 인하여 발생하는 손해에 대해서는 배상하지 아니합니다.<br>
                            4. 회사는 서비스 설비의 장애, 서비스 이용의 폭주 등의 이유로 서비스 이용에 지장이 있는 경우 서비스의 일시적으로 중단할 수 있으며, 이로 인하여 회원 또는 제 3 자가 입은 손해에 대하여는 배상하지 아니합니다.<br>
                            5. 회사는 서비스 내용의 변경으로 인하여 회원이 입은 손해에 대하여는 배상하지 아니합니다.<br>
                            6. 회사는 회원이 게시 또는 게재한 정보나 자료의 신뢰성이나 정확성에 대해서는 책임을 지지 아니합니다.<br>
                            7. 회사는 제휴 사이트와 회원 사이에 행해진 거래에 대하여 책임을 지지 않으며 취급하는 상품 또는 용역에 대하여 보증 책임을 지지 않습니다.<br>
                            8. 회사는 회원 상호간 또는 회원과 제 3 자 상호간에 서비스를 매개로 하여 물품거래 등을 한 경우에는 책임을 지지 않습니다.<br>
                            9. 회사는 서비스 이용과 관련하여 가입자에게 발생한 손해 가운데 가입자의 고의, 과실에 의한 손해에 대하여 책임을 지지 않습니다.<br>
                            10. 약관의 적용은 이용고객에 한하며, 제3자로부터의 어떠한 배상, 클레임 등에 대하여 회사는 책임이 면제됩니다.</p>

                            <p><strong>제 26조 (해지)</strong><br>
                            1. 이용회원이 이용계약을 해지하고자 하는 때에는 본인이 직접 서비스를 통하거나 서면 또는 전화 등의 방법으로 회사에 신청하여야 합니다.<br>
                            2. 이용회원은 해지의 권리를 갖는 기간은 서비스 개시일부터 24시간 이전까지 갖을 수 있습니다.<br>
                            3. 회사는 제1항의 규정에 의하여 해지신청이 접수되면 익일부터 서비스의 이용을 제한합니다.<br>
                            4. 회사가 이용계약을 해지하고자 할 경우에는 해지조치 24시간 이전까지 이용회원에게 통지하고 의견진술의 기회를 주어야 합니다. 단 다음 각 호에 해당하는 경우에는 즉시 해지할 수 있습니다.<br>
                            - 이용회원이 이용제한규정을 위반하거나 그 이용 제한기간내에 제한사유를 해소하지 않는 경우<br>
                            - 이용회원이 정당한 사유없이 의견진술에 응하지 아니한 경우<br>
                            - 타인명의로 신청을 하였거나 신청서내용을 허위로 기재하여 이용계약을 체결한 경우<br>
                            5. 회사는 제3항의 규정에 의하여 해지된 이용회원에 대하여는 일정기간동안 가입을 제한할 수 있습니다.</p>

                            <p><strong>제 27조 (기타사항)</strong><br>
                            이 약관에 명시되지 아니한 사항에 관하여는 전기통신기본법, 전기통신사업법 등 기타 관계법령의 규정에 의합니다.</p>

                            <h2 style="color: #2c3e50; font-size: 20px; font-weight: 700; margin: 30px 0 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">부 칙</h2>

                            <p>(시행일) 이 약관은 공시한 날부터 시행합니다.<br>
                            (부칙) 이 약관은 2010년 3월 1일부터 시행합니다.</p>
                        </div>
                    </div>
                </div>

                <!-- 개인정보 취급방침 모달 -->
                <div id="privacyModal" class="legal-modal">
                    <div class="legal-modal-content">
                        <div class="legal-modal-header">
                            <span>개인정보 취급방침</span>
                            <button class="legal-modal-close" onclick="closePrivacyModal()">&times;</button>
                        </div>
                        <div class="legal-modal-body" style="overflow-y: auto; padding: 30px; color: #333; line-height: 1.8;">
                            <h1 style="color: #2c3e50; font-size: 24px; border-bottom: 3px solid #2c3e50; padding-bottom: 10px; margin-bottom: 30px;">[개인정보 취급방침]</h1>

                            <div style="background: #f8f9fa; padding: 20px; border-left: 4px solid #2c3e50; margin-bottom: 30px;">
                                <p><strong>두손기획인쇄는 고객의 개인정보보호를 매우 중요시합니다.</strong></p>
                                <p>본사의 서비스 이용시 온라인상에 제공한 정보가 보호 받을 수 있도록 최선을 다 합니다.</p>
                                <p>개인정보 취급방법의 변경이나 수정시에는 홈페이지를 통해 게시하고, 개정된 사항을 이용자들이 쉽게 알 수 있도록 개정일자을 부여합니다. 사이트 방문시 수시로 확인하여 주시기 바랍니다.</p>
                            </div>

                            <p>두손기획인쇄(이하 '두손'이라 함)은 대한민국 헌법에 보장된 '사생활의 비밀과 자유 및 통신의 비밀 보장을 위해 개인 정보 및 사생활에 대한 추적을 금지하고 있습니다.</p>

                            <p>그러나 일부 부도덕한 사람들의 불법행위로 인해 발생할지도 모르는 사생활 침해를 막고, 회원의 개인정보를 적극적으로 보호하며, 동시에 회원에게 보다 적절한 서비스를 제공하기 위해 두손은 다음과 같은 개인정보 보호 정책을 적용하고 있습니다.</p>

                            <p>본 개인정보 보호정책은 정부의 관련법률 및 지침의 변경과 두손의 정책 변화에 따라 변경될 수 있으므로 본 사이트를 방문 하실 때 수시로 확인하여 주시기를 당부드립니다.</p>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">1. 개인정보란?</h2>
                            <p>생존하는 개인에 관한 정보로서 당해 정보에 포함되어 있는 성명, 주민등록번호 등의 사항에 의하여 당해 개인을 식별할 수 있는 정보(당해 정보만으로는 특정 개인을 식별할 수 없더라도 다른 정보와 용이하게 결합하여 식별할 수 있는 것을 포함합니다)를 말합니다.</p>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">2. 개인정보의 수집목적 및 이용</h2>
                            <p>두손은 회원등록과 로그인(Log on) 자료 등을 통해 입수한 회원정보는 전체적인 통계자료로 이용되며, 이를 기반으로 광고주 또는 협력사와 제휴하여 회원들에게 양질의 서비스를 제공하는 데 활용됩니다.</p>
                            <p>두손의 홈페이지에 게재되는 광고는 기본적으로 회원에게 보다 풍부한 서비스를 무료로 제공하기 위한 것으로, 이 때 회원들의 통계화된 개인정보는 회원들에게 보다 적절하고 유용한 광고를 유치하기 위한 자료로 이용됩니다. 또한 회원 개개인의 기호에 보다 맞는 서비스를 제공하기 위하여, 회원들의 개인정보를 토대로 사용자의 관심도, 행동성향 등을 분석하여 각종 기업, 기관이나 단체, 개인 등과 제휴하는 데 활용하게 됩니다.</p>
                            <ul style="margin-left: 20px;">
                                <li>회원가입 및 이용 ID 발급</li>
                                <li>계약의 성립</li>
                                <li>개별회원에 대한 개인 맞춤서비스</li>
                                <li>인구통계학적 분석 (이용자의 연령별, 성별, 지역별 통계분석)</li>
                                <li>회원의 서비스 이용에 대한 통계를 수집하고, 이를 서비스 정책에 반영 (서비스 개편 및 확대)</li>
                                <li>상품 배송</li>
                                <li>요금 및 대금 지급</li>
                                <li>새로운 서비스, 이벤트 정보안내</li>
                            </ul>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">3. 개인정보의 수집항목</h2>
                            <p>두손은 먼저 회원으로 가입하실 때 필수적인 개인정보를 얻고 있습니다. 이 때 기입하시는 개인정보로는 성명, 주소, e-mail 주소가 있으며 경품이나 기념품 등의 발송과 양질의 서비스 등을 위한 목적으로 직업, 전화번호, 관심분야, 인지경로 등을 선택적으로 기입할 수 있도록 하고 있습니다. 또한, 추후에 회원의 상품구입 등으로 인해 두손은 부득이하게 회원의 주소, 우편번호, 전화번호 등의 개인정보를 요청하게 되며, 이 경우에도 두손에서는 해당 서비스의 제공이나 사전에 회원에게 밝힌 목적 이외의 어떤 다른 목적으로도 회원의 개인정보를 사용하지 않습니다.</p>

                            <p><strong>[필수사항]</strong></p>
                            <ul style="margin-left: 20px;">
                                <li>성명</li>
                                <li>휴대폰 번호</li>
                                <li>희망 ID</li>
                                <li>주소(우편번호)</li>
                                <li>직장(학교)명</li>
                                <li>전화번호</li>
                            </ul>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">4. 쿠키(cookie)의 의미와 이용</h2>
                            <p>사용자에게 개인화된 편리한 서비스를 제공하기 위한 방법으로 귀하에 대한 정보를 저장하고 "두손" 접속시 이용됩니다. "쿠키"는 사이트에서 사용자 브라우저에게 보내는 데이터로서 귀하의 컴퓨터 하드 디스크에 저장됩니다. 귀하가 접속한 모든 사이트 또는 개인화된 사이트를 이용하기 위해서는 귀하가 쿠키를 허용하여야 합니다. 두손은 쿠키나 서버 로그 파일을 기반으로 하여 회원의 관심사와 행동양식, 인구통계학적 분포등을 분석할 수 있습니다. 이러한 분석과 개인정보는 두손과 그 협력업체가 회원에게 보다 나은 서비스를 제공하기 위하여 사용됩니다. 이렇게 수집된 정보는 광고주 또는 협력사와 공유할 수 있습니다.</p>

                            <ul style="margin-left: 20px;">
                                <li>각 종 정보 서비스 이용</li>
                                <li>개인 맞춤 서비스 제공</li>
                                <li>유료서비스 이용 시 이용기간 안내</li>
                                <li>게시판 글 등록</li>
                            </ul>

                            <p>기타의 경우 전기통신사업법 등의 관계법령에 의하여 국가기관이 요구하는 경우를 제외한 경우에는 개인신상 정보를 본인의 승낙없이 타인에게 누설, 배포하지 않습니다.</p>

                            <p><strong>- 쿠키의 설치/운영 및 거부</strong></p>
                            <p>이용자는 쿠키 설치에 대한 선택권을 가지고 있습니다. 따라서 이용자는 웹브라우저에서 옵션을 설정함으로써 모든 쿠키를 허용하거나, 쿠키가 저장될 때마다 확인을 거치거나, 아니면 모든 쿠키의 저장을 거부할 수도 있습니다.</p>
                            <p>다만, 쿠키의 저장을 거부할 경우에는 로그인이 필요한 두손의 일부 서비스는 이용에 어려움이 있을 수 있습니다.</p>
                            <p>쿠키 설치 허용 여부를 지정하는 방법(Internet Explorer의 경우)은 다음과 같습니다.</p>
                            <ul style="margin-left: 20px;">
                                <li>① [도구] 메뉴에서 [인터넷 옵션]을 선택합니다.</li>
                                <li>② [개인정보 탭]을 클릭합니다.</li>
                                <li>③ [개인정보취급 수준]을 설정하시면 됩니다.</li>
                            </ul>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">5. 개인정보 열람, 수정, 삭제</h2>
                            <p>두손은 회원이 입력한 개인정보를 '회원수첩' 의 '회원정보수정'란 에서 언제든지 열람, 변경, 정정할 수 있도록 하고 있으며, 개인 정보 수집을 반대하는 회원에겐 개인정보 수집에 대한 동의를 철회할 수 있게 하고 있습니다.</p>
                            <p>회원탈퇴, 즉 아이디(ID)의 삭제를 원하시면 이메일(master@mspg.co.kr)로 신청 또는 지정된 절차를 통해 처리하시면 됩니다.</p>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">6. 개인정보의 제공 및 공유</h2>
                            <p>두손은 본인의 동의 없이 개별적인 신상 정보를 다른 개인이나 기업, 기간과 공유하지 않는 것을 원칙으로 하고 있습니다. 다만, 회원이 허락한 경우나 상거래 등의 이행을 위해 필요한 경우, 또는 이용 약관을 위반한 회원에게 법적인 제재를 주기 위한 경우에는 정보를 공개할 수 있습니다. 하지만, 이러한 경우라도 프라이버시에 대한 충분한 검토를 거친 후에 하게 됩니다.</p>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">7. 개인정보의 보유 및 폐기</h2>
                            <p>두손회원으로서 두손이 제공하는 서비스를 받는 동안 회원의 개인정보는 두손에서 계속 보유하며 서비스 제공을 위해 이용하게 됩니다. 다만,</p>
                            <ul style="margin-left: 20px;">
                                <li>회원가입 정보의 경우, 회원가입을 탈퇴하거나 회원에서 제명된 경우 ,ID(아이디) 삭제가 이루어진 경우</li>
                                <li>개인정보의 수집목적이 달성된 경우, 관련법규의 규정에 의한 보존의무가 없는 한 두손의 온라인 또는 오프라인상에 보존된 모든 개인정보는 완전폐기되어 사용할 수 없도록 하고 있습니다.</li>
                            </ul>
                            <p>(단, 상법 등 법령의 규정에 의하여 보존할 필요성이 있는 경우에는 예외로 합니다.)</p>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">8. 개인정보 보호</h2>
                            <p>두손은 회원 개인의 정보와 비밀을 안전하게 지킬 수 있도록 항상 모든 기술적 수단과 노력을 다하고 있습니다. 그러나 회원의 아이디(ID) 및 비밀번호의 보안은 기본적으로 회원 개개인의 책임하에 있습니다. 두손에서 개인정보에 접근할 수 있는 방법은 오직 회원의 아이디(ID) 및 비밀번호로 인한 로그인 (Log in)에 의한 방법이며, 두손은 e-mail이나 전화, 그 어떠한 방법을 통해서도 회원의 비밀번호를 묻는 경우는 없으므로(ID/비밀번호 분실로 인한 회원님의 요청시 제외), 회원 본인이 보안을 위해 비밀번호를 자주 바꾸어주시기 바랍니다. 두손을 이용하신 후에는 반드시 로그아웃(Log out)을 해 주시고, 컴퓨터를 공유하거나 공공장소에서 컴퓨터를 사용하는 경우에는 이용 후 반드시 웹 브라우저의 창을 닫아주는 등 개인정보 유출을 막기 위해 각별히 노력을 기울여주시기 바랍니다.</p>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">9. 아동의 정보 보호</h2>
                            <p>만 14세미만 (13세 이하)의 아동이 두손의 회원으로 가입하기 위해서는 먼저 부모님이나 보호자에게 허락을 받은 후 가입하여야 합니다.</p>
                            <p>두손은 관계법령에서 허용되어 있지 않은 만14세미만 (13세이하) 아동에 관한 개인정보를 판매하거나 다른 사람에게 제공하지 아니합니다.</p>

                            <h2 style="color: #34495e; font-size: 18px; margin-top: 30px; margin-bottom: 15px;">10. 개인정보 불법사용의 금지</h2>
                            <p>두손은 개인에 대한 스팸메일을 발송할 우려가 있는 제3자에게 개인정보의 판매와 유출을 하지 않습니다. 두손의 서비스를 이용하는 모든 개인은 수익을 목적으로 하는 상업 활동이나 기타 불법 목적으로 타인의 개인 정보를 사용할 수 없습니다.</p>
                            <p>만약 이로 인하여 타인의 명예를 손상시키거나 타인에게 불이익이 발생할 경우 두손 이용 약관을 위배한 것으로 간주하여 회원 가입을 해지 또는 일시 정지할 수 있습니다. 또한 불법행위에 대한 모든 책임은 행위자 본인에게 있으며 회사는 일체의 책임을 지지 아니합니다. 단, 관계 법령에 의한 국가 기관의 요청시 정보 제공을 할 수 있습니다.</p>

                            <div style="background: #e8f4f8; padding: 20px; border-radius: 8px; margin-top: 30px;">
                                <h2 style="color: #34495e; font-size: 18px; margin-top: 0; margin-bottom: 15px;">개인정보관리책임자</h2>
                                <p style="margin-bottom: 8px;"><strong>성명 : 송영수</strong></p>
                                <p style="margin-bottom: 8px;"><strong>전화번호 : 02-2671-1830</strong></p>
                                <p style="margin-bottom: 20px;"><strong>이메일 : dsp1830@naver.com</strong></p>

                                <p>귀하께서는 회사의 서비스를 이용하시며 발생하는 모든 개인정보보호 관련 민원을 개인정보관리책임자 혹은 담당부서로 신고하실 수 있습니다.</p>
                                <p>회사는 이용자들의 신고사항에 대해 신속하게 충분한 답변을 드릴 것입니다.</p>

                                <p style="margin-top: 20px;"><strong>기타 개인정보침해에 대한 신고나 상담이 필요하신 경우에는 아래 기관에 문의하시기 바랍니다.</strong></p>
                                <ul style="margin-left: 20px;">
                                    <li>개인분쟁조정위원회 (www.1336.or.kr/1336)</li>
                                    <li>정보보호마크인증위원회 (www.eprivacy.or.kr/02-580-0533~4)</li>
                                    <li>대검찰청 인터넷범죄수사센터 (http://icic.sppo.go.kr/02-3480-3600)</li>
                                    <li>경찰청 사이버테러대응센터 (www.ctrc.go.kr/02-392-0330)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <style>
        /* 컴팩트 푸터 스타일 */
        .compact-footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            margin-top: 40px;
            font-family: 'Noto Sans KR', sans-serif;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            padding: 20px 0 15px;
            max-width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .footer-compact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        /* 상단 네비게이션 링크 */
        .footer-nav {
            text-align: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 15px;
        }

        .footer-logo {
            color: #ffc107;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            margin-right: 5px;
        }

        .footer-logo:hover {
            color: #ffeb3b;
        }

        .footer-nav-link {
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s ease;
        }

        .footer-nav-link:hover {
            color: #ffc107;
            text-decoration: underline;
        }

        .nav-divider {
            color: rgba(255, 255, 255, 0.4);
            margin: 0 8px;
            font-size: 12px;
        }

        /* 회사 정보 및 인증마크 영역 */
        .footer-info-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 15px 0;
            gap: 20px;
        }

        .footer-info-left {
            flex: 1;
        }

        /* 회사 정보 */
        .footer-info {
            text-align: left;
            padding: 10px 0;
        }

        .company-main {
            margin-bottom: 8px;
            font-size: 13px;
            line-height: 1.6;
            color: #ffffff;
        }

        .company-name {
            color: #ffc107;
            font-weight: 700;
            margin-right: 5px;
        }

        .company-detail {
            font-size: 12px;
            color: #e0e0e0;
            line-height: 1.5;
        }

        .info-divider {
            color: rgba(255, 255, 255, 0.4);
            margin: 0 8px;
        }

        /* 저작권 섹션 */
        .copyright-section {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .copyright-text {
            font-size: 11px;
            color: #b0b0b0;
        }

        /* 인증마크 (우측) */
        .footer-logos-right {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }

        .cert-logo-item {
            display: flex;
            align-items: center;
        }

        .cert-logo-link {
            display: flex;
            align-items: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .cert-logo-link:hover {
            transform: translateY(-2px);
        }

        .cert-logo {
            display: block;
        }

        .ftc-logo {
            width: 107px;
            height: 35px;
        }

        .kftc-logo {
            width: 98px;
            height: 35px;
        }

        .kb-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }

        /* 법적 정보 모달 스타일 */
        .legal-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .legal-modal-content {
            background-color: #ffffff;
            margin: 3% auto;
            width: 90%;
            max-width: 900px;
            height: 85vh;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }

        .legal-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 1.5rem;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ffffff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .legal-modal-close {
            color: #ffffff;
            font-size: 1.8rem;
            font-weight: bold;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .legal-modal-close:hover {
            transform: scale(1.2);
            color: #ffc107;
        }

        .legal-modal-body {
            flex: 1;
            overflow: hidden;
            background: #ffffff;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .compact-footer {
                padding: 15px 10px;
            }

            .footer-compact-container {
                padding: 0 10px;
                max-width: 100%;
            }

            .footer-nav {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
            }

            .nav-divider {
                display: none;
            }

            .footer-nav-link {
                font-size: 11px;
            }

            .footer-info-section {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .footer-info-left {
                width: 100%;
            }

            .footer-info {
                text-align: center;
            }

            .company-main,
            .company-detail {
                font-size: 10px;
                word-break: keep-all;
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .info-divider {
                display: none;
            }

            .copyright-section {
                text-align: center;
            }

            .footer-logos-right {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
                gap: 10px;
            }

            .ftc-logo,
            .kftc-logo {
                width: 100px;
                height: 26px;
            }

            .kb-logo {
                width: 60px;
                height: 60px;
                border-radius: 50%;
            }

            .copyright-text {
                font-size: 9px;
                word-break: keep-all;
            }

            .legal-modal-content {
                width: 95%;
                height: 90vh;
                margin: 2% auto;
            }

            .legal-modal-header {
                padding: 1rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .company-main,
            .company-detail {
                font-size: 9px;
            }

            .ftc-logo,
            .kftc-logo {
                width: 90px;
                height: 23px;
            }

            .kb-logo {
                width: 60px;
                height: 60px;
                border-radius: 50%;
            }
        }
        </style>

        <script>
        // KB 에스크로 인증마크 팝업
        function onPopKBAuthMark() {
            window.open('', 'KB_AUTHMARK', 'height=604, width=648, status=yes, toolbar=no, menubar=no, location=no');
            document.KB_AUTHMARK_FORM.action = 'https://okbfex.kbstar.com/quics';
            document.KB_AUTHMARK_FORM.target = 'KB_AUTHMARK';
            document.KB_AUTHMARK_FORM.submit();
        }

        // 이용약관 모달 열기
        function openTermsModal() {
            document.getElementById('termsModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // 이용약관 모달 닫기
        function closeTermsModal() {
            document.getElementById('termsModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // 개인정보 취급방침 모달 열기
        function openPrivacyModal() {
            document.getElementById('privacyModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // 개인정보 취급방침 모달 닫기
        function closePrivacyModal() {
            document.getElementById('privacyModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // 모달 외부 클릭 시 닫기
        window.addEventListener('click', function(event) {
            var termsModal = document.getElementById('termsModal');
            var privacyModal = document.getElementById('privacyModal');

            if (event.target === termsModal) {
                closeTermsModal();
            }
            if (event.target === privacyModal) {
                closePrivacyModal();
            }
        });

        // ESC 키로 모달 닫기
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeTermsModal();
                closePrivacyModal();
            }
        });
        </script>
    </div> <!-- page-wrapper 끝 -->

    <?php
    // 로그인 모달 포함 (로그인하지 않은 사용자에게만 표시)
    if (!$is_logged_in) {
        include_once __DIR__ . '/login_modal.php';
    }
    ?>

    <?php
    // 직원 채팅 + AI 챗봇: 시간대별 전환 (겹침 방지)
    // 09:00~18:30 → 직원 채팅 | 18:30~09:00 → AI 챗봇
    include_once __DIR__ . '/chat_widget.php';
    include_once __DIR__ . '/ai_chatbot_widget.php';

    // 로딩 스피너 포함 (모든 페이지)
    include_once __DIR__ . '/loading-spinner.php';
    ?>

    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
    (function(){
        function isBusinessHours() {
            var now = new Date();
            var h = now.getHours(), m = now.getMinutes();
            if (h < 9) return false;
            if (h > 18) return false;
            if (h === 18 && m >= 30) return false;
            return true;
        }
        function toggleWidgets() {
            var biz = isBusinessHours();
            var staff = document.querySelector('.chat-widget');
            var ai = document.getElementById('ai-chatbot-widget');
            if (staff) staff.style.display = biz ? '' : 'none';
            if (ai) ai.style.display = biz ? 'none' : 'block';
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', toggleWidgets);
        } else {
            toggleWidgets();
        }
        setInterval(toggleWidgets, 60000);
    })();
    </script>

    <!-- PWA 설치 배너 (Android Chrome) -->
    <div id="pwa-install-banner" style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:99999; background:#fff; box-shadow:0 -2px 12px rgba(0,0,0,0.15); padding:12px 16px; font-family:'Pretendard',sans-serif;">
        <div style="max-width:600px; margin:0 auto; display:flex; align-items:center; gap:12px;">
            <img src="/ImgFolder/icon-192x192.png" alt="두손기획인쇄" style="width:44px; height:44px; border-radius:10px; flex-shrink:0;">
            <div style="flex:1; min-width:0;">
                <div style="font-weight:600; font-size:14px; color:#222;">두손기획인쇄</div>
                <div style="font-size:12px; color:#888; margin-top:2px;">홈 화면에 추가하고 빠르게 접속하세요</div>
            </div>
            <button id="pwa-install-btn" style="flex-shrink:0; background:#4CAF50; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">설치</button>
            <button id="pwa-install-close" style="flex-shrink:0; background:none; border:none; color:#aaa; font-size:20px; cursor:pointer; padding:0 4px;">✕</button>
        </div>
    </div>

    <!-- PWA 설치 안내 (iOS Safari) -->
    <div id="ios-install-banner" style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:99999; background:#fff; box-shadow:0 -4px 20px rgba(0,0,0,0.12); font-family:'Pretendard',sans-serif; border-top-left-radius:16px; border-top-right-radius:16px;">
        <div style="max-width:500px; margin:0 auto; padding:20px 20px 24px;">
            <!-- 상단 핸들 + 닫기 -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div style="width:36px; height:4px; background:#d1d5db; border-radius:2px; margin:0 auto;"></div>
                <button id="ios-install-close" style="position:absolute; right:16px; top:12px; background:none; border:none; color:#999; font-size:22px; cursor:pointer; padding:4px;">✕</button>
            </div>
            <!-- 앱 정보 -->
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:18px;">
                <img src="/ImgFolder/icon-192x192.png" alt="두손기획인쇄" style="width:52px; height:52px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <div>
                    <div style="font-weight:700; font-size:16px; color:#1e293b;">두손기획인쇄</div>
                    <div style="font-size:13px; color:#64748b; margin-top:2px;">dsp114.co.kr</div>
                </div>
            </div>
            <!-- 설치 단계 안내 -->
            <div style="background:#f8fafc; border-radius:12px; padding:16px; margin-bottom:16px;">
                <div style="font-weight:600; font-size:13px; color:#334155; margin-bottom:12px;">홈 화면에 추가하는 방법</div>
                <div style="display:flex; align-items:flex-start; gap:10px; margin-bottom:10px;">
                    <div style="flex-shrink:0; width:24px; height:24px; background:#007AFF; color:#fff; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700;">1</div>
                    <div style="font-size:13px; color:#475569; line-height:1.5; padding-top:2px;">하단의 <span style="display:inline-flex; align-items:center; gap:3px; background:#e2e8f0; padding:1px 6px; border-radius:4px; font-weight:600;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#007AFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg> 공유</span> 버튼을 탭하세요</div>
                </div>
                <div style="display:flex; align-items:flex-start; gap:10px;">
                    <div style="flex-shrink:0; width:24px; height:24px; background:#007AFF; color:#fff; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700;">2</div>
                    <div style="font-size:13px; color:#475569; line-height:1.5; padding-top:2px;"><span style="display:inline-flex; align-items:center; gap:3px; background:#e2e8f0; padding:1px 6px; border-radius:4px; font-weight:600;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg> 홈 화면에 추가</span>를 탭하세요</div>
                </div>
            </div>
            <div style="text-align:center; font-size:11px; color:#94a3b8;">앱처럼 바로 접속할 수 있습니다</div>
        </div>
    </div>

    <script>
    (function() {
        // === Android Chrome: beforeinstallprompt ===
        var deferredPrompt = null;
        var banner = document.getElementById('pwa-install-banner');
        var installBtn = document.getElementById('pwa-install-btn');
        var closeBtn = document.getElementById('pwa-install-close');

        if (!banner) return;
        if (localStorage.getItem('pwa-install-dismissed')) return;
        if (window.matchMedia('(display-mode: standalone)').matches) return;
        if (navigator.standalone === true) return; // iOS standalone

        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            banner.style.display = 'block';
        });

        installBtn.addEventListener('click', function() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function() {
                deferredPrompt = null;
                banner.style.display = 'none';
            });
        });

        closeBtn.addEventListener('click', function() {
            banner.style.display = 'none';
            localStorage.setItem('pwa-install-dismissed', '1');
        });

        window.addEventListener('appinstalled', function() {
            banner.style.display = 'none';
        });

        // === iOS Safari: 수동 안내 배너 ===
        var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        var isSafari = /Safari/.test(navigator.userAgent) && !/CriOS|FxiOS|OPiOS|EdgiOS/.test(navigator.userAgent);
        var iosBanner = document.getElementById('ios-install-banner');
        var iosCloseBtn = document.getElementById('ios-install-close');

        if (isIOS && isSafari && iosBanner && !navigator.standalone) {
            if (!localStorage.getItem('ios-install-dismissed')) {
                // 3초 후 표시 (페이지 로드 직후가 아닌 자연스러운 타이밍)
                setTimeout(function() {
                    iosBanner.style.display = 'block';
                    iosBanner.style.animation = 'iosSlideUp .35s ease';
                }, 3000);
            }
        }

        if (iosCloseBtn) {
            iosCloseBtn.addEventListener('click', function() {
                iosBanner.style.display = 'none';
                localStorage.setItem('ios-install-dismissed', '1');
            });
        }
    })();
    </script>
    <style>
    @keyframes iosSlideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
    </style>

</body>
</html>