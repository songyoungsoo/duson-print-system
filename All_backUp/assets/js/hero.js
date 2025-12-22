/**
 * DSP Hero Simple Center Carousel - 원본 구조 보존
 */

document.addEventListener('DOMContentLoaded', function() {
  console.log('Simple Center Carousel 초기화...');
  
  const hero = document.querySelector('.dsp-hero');
  if (!hero) return;
  
  const slides = hero.querySelectorAll('.dsp-hero__slide');
  if (slides.length === 0) return;
  
  let currentIndex = 0;
  
  // 초기 설정: 첫 번째만 활성화
  slides.forEach((slide, index) => {
    slide.classList.remove('is-center', 'is-active', 'is-peek');
    if (index === 0) {
      slide.classList.add('is-center', 'is-active');
      slide.style.display = 'block';
    } else {
      slide.style.display = 'none';
    }
  });
  
  function showNextSlide() {
    // 현재 슬라이드 숨기기
    slides[currentIndex].style.display = 'none';
    slides[currentIndex].classList.remove('is-center', 'is-active');
    
    // 다음 인덱스
    currentIndex = (currentIndex + 1) % slides.length;
    
    // 새 슬라이드 보이기
    slides[currentIndex].style.display = 'block';
    slides[currentIndex].classList.add('is-center', 'is-active');
  }
  
  // 3초마다 자동 전환
  setInterval(showNextSlide, 3000);
  
  console.log('Simple Center Carousel 초기화 완료:', slides.length, '개 슬라이드');
});