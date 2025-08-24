<?php 
/**
 * 스티커 주문 페이지 (공통 인클루드 사용 버전)
 * 경로: MlangPrintAuto/shop/view_modern_new.php
 */

session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 공통 인증 처리
include "../../includes/auth.php";

// 페이지 설정
$page_title = '🏷️ 두손기획인쇄 - 프리미엄 스티커 주문';
$current_page = 'sticker';

// 공통 헤더 포함
include "../../includes/header.php";

// 네비게이션 포함
include "../../includes/nav.php";
?>

<div class="container">
    <!-- 주문 폼 -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📝 스티커 주문 옵션 선택</h2>
            <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
        </div>
        
        <form id="orderForm" method="post">
            <input type="hidden" name="no" value="<?php echo htmlspecialchars($no ?? '', ENT_QUOTES, 'UTF-8')?>">
            <input type="hidden" name="action" value="calculate">
            
            <table class="order-form-table">
                <tbody>
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📄</span>
                                <span>1. 재질 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="jong" class="form-control-modern">
                                <option value="jil 아트유광">✨ 아트지유광 (90g)</option>
                                <option value="jil 아트무광코팅">🌟 아트지무광코팅 (90g)</option>
                                <option value="jil 아트비코팅">💫 아트지비코팅 (90g)</option>
                                <option value="cka 초강접아트유광">⚡ 초강접아트유광 (90g)</option>
                                <option value="cka 초강접아트비코팅">⚡ 초강접아트비코팅 (90g)</option>
                                <option value="jsp 유포지">📄 유포지 (80g)</option>
                                <option value="jsp 투명스티커">🔍 투명스티커</option>
                                <option value="jsp 홀로그램">🌈 홀로그램</option>
                                <option value="jsp 크라프트">🌿 크라프트지</option>
                            </select>
                            <small class="help-text">재질에 따라 스티커의 느낌과 내구성이 달라집니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📏</span>
                                <span>2. 크기 설정</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <div class="size-inputs">
                                <div class="size-input-inline">
                                    <label class="size-label">가로 (mm):</label>
                                    <input type="number" name="garo" class="form-control-inline" placeholder="예: 100" min="10" max="1000" required>
                                </div>
                                <span class="size-multiply">×</span>
                                <div class="size-input-inline">
                                    <label class="size-label">세로 (mm):</label>
                                    <input type="number" name="sero" class="form-control-inline" placeholder="예: 100" min="10" max="1000" required>
                                </div>
                            </div>
                            <small class="help-text">최소 10mm, 최대 1000mm까지 제작 가능합니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📦</span>
                                <span>3. 수량 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="mesu" class="form-control-modern">
                                <option value="500">500매</option>
                                <option value="1000" selected>1,000매 (추천)</option>
                                <option value="2000">2,000매</option>
                                <option value="3000">3,000매</option>
                                <option value="5000">5,000매</option>
                                <option value="10000">10,000매</option>
                                <option value="20000">20,000매</option>
                                <option value="30000">30,000매 (대량할인)</option>
                            </select>
                            <small class="help-text">수량이 많을수록 단가가 저렴해집니다</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">✏️</span>
                                <span>4. 편집비</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="uhyung" class="form-control-modern">
                                <option value="0">인쇄만 (파일 준비완료)</option>
                                <option value="10000">기본 편집 (+10,000원)</option>
                                <option value="30000">고급 편집 (+30,000원)</option>
                            </select>
                            <small class="help-text">디자인 파일이 없으시면 편집 서비스를 이용해주세요</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">🔲</span>
                                <span>5. 모양 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="domusong" class="form-control-modern">
                                <option value="00000 사각">⬜ 사각형 (기본)</option>
                                <option value="00001 원형">⭕ 원형</option>
                                <option value="00002 타원">🥚 타원형</option>
                                <option value="00003 별모양">⭐ 별모양</option>
                                <option value="00004 하트">❤️ 하트</option>
                                <option value="00005 다각형">🔷 다각형</option>
                            </select>
                            <small class="help-text">모양에 따라 추가 작업비가 발생할 수 있습니다</small>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div style="text-align: center; margin: 3rem 0;">
                <button type="button" onclick="calculatePrice()" class="btn-calculate">
                    💰 실시간 가격 계산하기
                </button>
            </div>
        </form>
    </div>
    
    <!-- 가격 계산 결과 -->
    <div id="priceSection" class="price-result">
        <h3>💎 견적 결과</h3>
        <div class="price-amount" id="priceAmount">0원</div>
        <div>부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700;">0원</span></div>
        
        <div class="action-buttons">
            <button onclick="addToBasket()" class="btn-action btn-primary">
                🛒 장바구니에 담기
            </button>
            <a href="cart.php" class="btn-action btn-secondary">
                👀 장바구니 보기
            </a>
        </div>
    </div>
    
    <!-- 최근 주문 내역 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📋 최근 스티커 주문 내역</h3>
            <p class="card-subtitle">현재 세션의 주문 내역입니다</p>
        </div>
        
        <table class="modern-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>재질</th>
                    <th>크기</th>
                    <th>수량</th>
                    <th>도무송</th>
                    <th>편집비</th>
                    <th>금액</th>
                    <th>VAT포함</th>
                    <th>삭제</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 스티커 주문 내역 조회
                $query = "SELECT * FROM shop_temp WHERE session_id='$session_id' AND product_type='sticker' ORDER BY no DESC LIMIT 5";  
                $result = mysqli_query($connect, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($data = mysqli_fetch_array($result)) {
                        // 도무송 이름 파싱
                        $domusong_parts = explode(' ', $data['domusong'], 2);
                        $domusong_name = isset($domusong_parts[1]) ? $domusong_parts[1] : $data['domusong'];
                        ?>
                        <tr>
                            <td><?php echo $data['no'] ?></td>
                            <td><?php echo substr($data['jong'], 4, 12); ?></td>
                            <td><?php echo $data['garo'] ?>×<?php echo $data['sero'] ?>mm</td>
                            <td><?php echo number_format($data['mesu']) ?>매</td>
                            <td><?php echo htmlspecialchars($domusong_name) ?></td>
                            <td><?php echo number_format($data['uhyung']) ?>원</td>
                            <td><strong><?php echo number_format($data['st_price']) ?>원</strong></td>
                            <td><strong><?php echo number_format($data['st_price_vat']) ?>원</strong></td>
                            <td><a href="del.php?no=<?php echo $data['no'] ?>" onclick="return confirm('정말 삭제할까요?');" class="btn-action btn-secondary" style="padding: 8px 15px; font-size: 0.9rem;">삭제</a></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            <div>
                                <h4>📭 주문 내역이 없습니다</h4>
                                <p>첫 번째 스티커 주문을 시작해보세요!</p>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// 가격 계산 함수
function calculatePrice() {
    const formData = new FormData(document.getElementById('orderForm'));
    
    fetch('calculate_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('priceAmount').textContent = data.price + '원';
            document.getElementById('priceVat').textContent = data.price_vat + '원';
            document.getElementById('priceSection').style.display = 'block';
        } else {
            alert('가격 계산 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('가격 계산 중 오류가 발생했습니다.');
    });
}

// 장바구니 추가 함수
function addToBasket() {
    const formData = new FormData(document.getElementById('orderForm'));
    formData.append('product_type', 'sticker');
    formData.append('action', 'add_to_basket');
    
    fetch('add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('장바구니에 추가되었습니다!');
            location.reload(); // 페이지 새로고침으로 최근 주문 내역 업데이트
        } else {
            alert('장바구니 추가 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('장바구니 추가 중 오류가 발생했습니다.');
    });
}
</script>

<?php
// 로그인 모달 포함
include "../../includes/login_modal.php";

// 공통 푸터 포함
include "../../includes/footer.php";
?>