<?php
// 상대 경로 설정
$M123 = isset($M123) ? $M123 : '.';

// 현재 스크립트의 디렉토리 경로 확인
$current_dir = dirname(__FILE__);

// 베이스 URL 설정
$base_url = '';
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'];
}

// 메뉴 구조 정의
$menu_items = [
    [
        'title' => 'HELP',
        'submenu' => [
            ['title' => '업데이트사항', 'url' => $base_url . '/HELP/SoftUpgrade.php', 'target' => '_blank'],
            ['title' => 'WEBSIL바로가기', 'url' => 'http://www.websil.net', 'target' => '_blank']
        ]
    ],
    [
        'title' => '관리자환경',
        'submenu' => [
            ['title' => '비밀번호 변경', 'url' => $base_url . '/admin/AdminConfig.php?mode=modify', 'target' => 'Mlang']
        ]
    ],
    [
        'title' => '게시판관리',
        'submenu' => [
            ['title' => '생성/관리/삭제', 'url' => $base_url . '/admin/bbs_admin.php?mode=list'],
            ['title' => '자료신고함', 'url' => $base_url . '/admin/BBSSinGo/index.php'],
            ['title' => '실적물 관리', 'url' => $base_url . '/admin/results/admin.php?mode=list']
        ]
    ],
    [
        'title' => '회원관리',
        'submenu' => [
            ['title' => 'LIST/검색/관리', 'url' => $base_url . '/admin/member/index.php'],
            [
                'title' => '메일관리',
                'submenu' => [
                    ['title' => '가입완료메일', 'url' => $base_url . '/admin/member/JoinAdmin.php'],
                    ['title' => '전체메일 관리', 'url' => $base_url . '/admin/member/MaillingJoinAdmin.php'],
                    [
                        'title' => '전체 메일발송',
                        'submenu' => [
                            ['title' => 'YES만 발송', 'url' => $base_url . '/admin/mailing/form.php?FFF=ok'],
                            ['title' => '전체다 발송', 'url' => $base_url . '/admin/mailing/form.php']
                        ]
                    ]
                ]
            ]
        ]
    ],
    [
        'title' => '인쇄업무프로그램',
        'submenu' => [
            [
                'title' => '견적안내프로그램',
                'submenu' => [
                    ['title' => '전단지 관리', 'url' => $base_url . '/admin/MlangPrintAuto/inserted_List.php'],
                    ['title' => '스티카 관리', 'url' => $base_url . '/admin/MlangPrintAuto/sticker_List.php'],
                    ['title' => '명함 관리', 'url' => $base_url . '/admin/MlangPrintAuto/NameCard_List.php'],
                    ['title' => '상품권 관리', 'url' => $base_url . '/admin/MlangPrintAuto/MerchandiseBond_List.php'],
                    ['title' => '봉투 관리', 'url' => $base_url . '/admin/MlangPrintAuto/envelope_List.php'],
                    ['title' => '양식지 관리', 'url' => $base_url . '/admin/MlangPrintAuto/NcrFlambeau_List.php'],
                    ['title' => '카다로그 관리', 'url' => $base_url . '/admin/MlangPrintAuto/cadarok_List.php'],
                    ['title' => '소량인쇄 관리', 'url' => $base_url . '/admin/MlangPrintAuto/LittlePrint_List.php'],
                    ['title' => '견적안내 주문', 'url' => $base_url . '/admin/MlangPrintAuto/OrderList.php'],
                    ['title' => '시안직접올리기', 'url' => $base_url . '/admin/MlangPrintAuto/admin.php?mode=AdminMlangOrdert', 'target' => 'Mlang'],
                    ['title' => '견적안내 통합관리', 'url' => $base_url . '/admin/MlangPrintAuto/admin.php?mode=BankForm&code=Text', 'target' => 'Mlang']
                ]
            ],
            [
                'title' => '수동견적프로그램',
                'submenu' => [
                    ['title' => '수동견적 주문', 'url' => $base_url . '/admin/MlangPrintAuto/OfferOrder.php']
                ]
            ],
            [
                'title' => '인쇄관련 업무',
                'submenu' => [
                    ['title' => '주문자 접수일보', 'url' => $base_url . '/admin/MlangPrintAuto/MemberOrderOfficeList.php']
                ]
            ]
        ]
    ]
];

// 메뉴 HTML 생성 함수
function generate_menu_html($items) {
    $html = '<ul class="modern-menu">';
    
    foreach ($items as $item) {
        $has_submenu = isset($item['submenu']) && !empty($item['submenu']);
        $class = $has_submenu ? 'menu-has-children' : '';
        
        $html .= '<li class="' . $class . '">';
        
        $target = isset($item['target']) ? ' target="' . $item['target'] . '"' : '';
        if (isset($item['url'])) {
            $html .= '<a href="' . $item['url'] . '"' . $target . '>' . $item['title'] . '</a>';
        } else {
            $html .= '<a href="#">' . $item['title'] . '</a>';
        }
        
        if ($has_submenu) {
            $html .= generate_submenu_html($item['submenu']);
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    return $html;
}

function generate_submenu_html($items) {
    $html = '<ul>';
    
    foreach ($items as $item) {
        $has_submenu = isset($item['submenu']) && !empty($item['submenu']);
        $class = $has_submenu ? 'submenu-has-children' : '';
        
        $html .= '<li class="' . $class . '">';
        
        $target = isset($item['target']) ? ' target="' . $item['target'] . '"' : '';
        if (isset($item['url'])) {
            $html .= '<a href="' . $item['url'] . '"' . $target . '>' . $item['title'] . '</a>';
        } else {
            $html .= '<a href="#">' . $item['title'] . '</a>';
        }
        
        if ($has_submenu) {
            $html .= generate_submenu_html($item['submenu']);
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    return $html;
}
?>

<!-- 모던 메뉴 컨테이너 -->
<div class="modern-menu-container">
    <button class="modern-menu-toggle">☰</button>
    <?php echo generate_menu_html($menu_items); ?>
</div>

<!-- CSS 및 JavaScript 포함 -->
<style>
/* 모던 메뉴 스타일 */
:root {
  --primary-color: #4a6da7;
  --primary-hover: #3a5a8c;
  --secondary-color: #f8f9fa;
  --text-color: #333;
  --text-light: #fff;
  --border-color: #ddd;
  --shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
  --transition: all 0.3s ease;
}

.modern-menu-container {
  width: 100%;
  background-color: var(--primary-color);
  box-shadow: var(--shadow);
  position: relative;
  z-index: 1000;
  font-family: 'Malgun Gothic', '맑은 고딕', sans-serif;
}

.modern-menu {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  max-width: 1200px;
  margin: 0 auto;
}

.modern-menu > li {
  position: relative;
}

.modern-menu > li > a {
  display: block;
  padding: 15px 20px;
  color: var(--text-light);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: var(--transition);
}

.modern-menu > li:hover > a {
  background-color: var(--primary-hover);
}

.modern-menu > li > ul {
  position: absolute;
  top: 100%;
  left: 0;
  background-color: var(--secondary-color);
  min-width: 200px;
  box-shadow: var(--shadow);
  opacity: 0;
  visibility: hidden;
  transform: translateY(10px);
  transition: var(--transition);
  list-style: none;
  padding: 0;
  z-index: 1001;
}

.modern-menu > li:hover > ul {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.modern-menu > li > ul > li {
  position: relative;
}

.modern-menu > li > ul > li > a {
  display: block;
  padding: 10px 15px;
  color: var(--text-color);
  text-decoration: none;
  font-size: 13px;
  transition: var(--transition);
  border-bottom: 1px solid var(--border-color);
}

.modern-menu > li > ul > li:hover > a {
  background-color: #eaeaea;
}

.modern-menu > li > ul > li > ul {
  position: absolute;
  top: 0;
  left: 100%;
  background-color: var(--secondary-color);
  min-width: 200px;
  box-shadow: var(--shadow);
  opacity: 0;
  visibility: hidden;
  transform: translateX(10px);
  transition: var(--transition);
  list-style: none;
  padding: 0;
  z-index: 1002;
}

.modern-menu > li > ul > li:hover > ul {
  opacity: 1;
  visibility: visible;
  transform: translateX(0);
}

.modern-menu > li > ul > li > ul > li > a {
  display: block;
  padding: 10px 15px;
  color: var(--text-color);
  text-decoration: none;
  font-size: 13px;
  transition: var(--transition);
  border-bottom: 1px solid var(--border-color);
}

.modern-menu > li > ul > li > ul > li:hover > a {
  background-color: #eaeaea;
}

.modern-menu-toggle {
  display: none;
  background: none;
  border: none;
  color: var(--text-light);
  font-size: 24px;
  padding: 10px;
  cursor: pointer;
}

.menu-has-children > a:after {
  content: '▼';
  font-size: 10px;
  margin-left: 5px;
}

.submenu-has-children > a:after {
  content: '▶';
  font-size: 10px;
  margin-left: 5px;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
  .modern-menu-toggle {
    display: block;
    position: absolute;
    right: 15px;
    top: 10px;
  }
  
  .modern-menu {
    display: none;
    flex-direction: column;
    width: 100%;
  }
  
  .modern-menu.active {
    display: flex;
  }
  
  .modern-menu > li > ul {
    position: static;
    opacity: 1;
    visibility: visible;
    transform: none;
    box-shadow: none;
    display: none;
    width: 100%;
    padding-left: 20px;
  }
  
  .modern-menu > li > ul > li > ul {
    position: static;
    opacity: 1;
    visibility: visible;
    transform: none;
    box-shadow: none;
    display: none;
    width: 100%;
    padding-left: 20px;
  }
  
  .modern-menu > li.active > ul,
  .modern-menu > li > ul > li.active > ul {
    display: block;
  }
  
  .menu-has-children > a:after,
  .submenu-has-children > a:after {
    content: '+';
  }
  
  .menu-has-children.active > a:after,
  .submenu-has-children.active > a:after {
    content: '-';
  }
}

/* 헤더 스타일 */
.modern-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 20px;
  background-color: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.modern-logo {
  font-size: 18px;
  font-weight: bold;
  color: var(--primary-color);
  text-decoration: none;
}
</style>

<script>
// 모던 메뉴 JavaScript
document.addEventListener('DOMContentLoaded', function() {
  // 모바일 메뉴 토글
  const menuToggle = document.querySelector('.modern-menu-toggle');
  const menu = document.querySelector('.modern-menu');
  
  if (menuToggle) {
    menuToggle.addEventListener('click', function() {
      menu.classList.toggle('active');
    });
  }
  
  // 서브메뉴 토글 (모바일)
  const menuItems = document.querySelectorAll('.menu-has-children > a, .submenu-has-children > a');
  
  menuItems.forEach(item => {
    item.addEventListener('click', function(e) {
      if (window.innerWidth <= 768) {
        e.preventDefault();
        const parent = this.parentElement;
        parent.classList.toggle('active');
      }
    });
  });
  
  // 창 크기 변경 시 모바일 메뉴 상태 초기화
  window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
      const activeItems = document.querySelectorAll('.modern-menu li.active');
      activeItems.forEach(item => {
        item.classList.remove('active');
      });
      
      if (menu.classList.contains('active')) {
        menu.classList.remove('active');
      }
    }
  });
});
</script>