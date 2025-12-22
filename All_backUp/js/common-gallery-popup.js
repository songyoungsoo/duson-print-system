/**
 * 공통 갤러리 팝업 함수
 * 모든 품목에서 통일된 방식으로 갤러리 팝업을 열기 위한 공통 함수
 */

/**
 * 갤러리 팝업 열기
 * @param {string} category - 카테고리명 (명함, 스티커, 전단지 등)
 */
function openGalleryPopup(category) {
    if (!category) {
        console.error('카테고리가 지정되지 않았습니다.');
        return;
    }

    const width = 1200;
    const height = 800;
    const left = Math.floor((screen.width - width) / 2);
    const top = Math.floor((screen.height - height) / 2);

    const popup = window.open(
        '/popup/proof_gallery.php?cate=' + encodeURIComponent(category),
        'proof_popup_' + category,
        `width=${width},height=${height},scrollbars=yes,resizable=yes,top=${top},left=${left}`
    );

    if (popup) {
        popup.focus();
    } else {
        alert('팝업 차단이 감지되었습니다. 팝업 차단을 해제해주세요.');
    }
}

// 전역 스코프에 함수 등록
window.openGalleryPopup = openGalleryPopup;

// DOM 로드 후 모든 gallery-more-thumb 버튼에 이벤트 자동 바인딩
document.addEventListener('DOMContentLoaded', function() {
    // 카테고리 영문 -> 한글 매핑
    const categoryMap = {
        'namecard': '명함',
        'sticker': '스티커',
        'sticker_new': '스티커',
        'envelope': '봉투',
        'inserted': '전단지',
        'leaflet': '전단지',  // 리플렛도 전단지 카테고리 사용
        'littleprint': '포스터',
        'cadarok': '카탈로그',
        'merchandisebond': '상품권',
        'msticker': '자석스티커',
        'ncrflambeau': '양식지'
    };

    // 모든 gallery-more-thumb 버튼 찾기
    const moreButtons = document.querySelectorAll('.gallery-more-thumb');

    moreButtons.forEach(function(button) {
        // 이미 onclick이 있는지 확인
        if (!button.onclick) {
            const product = button.getAttribute('data-product');
            if (product) {
                const category = categoryMap[product] || product;
                button.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openGalleryPopup(category);
                };
                console.log(`✅ 갤러리 팝업 자동 바인딩: ${product} → ${category}`);
            }
        }
    });
});
