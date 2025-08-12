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