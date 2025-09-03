<?php
/**
 * Center-2-3 Carousel Gallery Component
 * Specialized carousel with 3-slide layout: center (2/3) + left/right peek (1/6 each)
 * Forward-only navigation with mathematical positioning
 */
?>

<div class="dsp-hero" 
     role="region" 
     aria-roledescription="carousel"
     aria-label="홈페이지 메인 갤러리"
     data-layout="center-2-3"
     data-autoplay="2000"
     data-gap="12"
     data-oneway="true">
     
    <div class="dsp-hero__track" dir="ltr" role="group" aria-live="polite">
        <!-- 실제 슬라이드 이미지들 -->
        <div class="dsp-hero__slide is-center is-active" role="tabpanel" aria-label="슬라이드 1 of 7">
            <img src="/slide/slide_inserted.gif" alt="두손기획인쇄 메인 서비스">
            <div class="dsp-hero__content">
                <h3>두손기획인쇄</h3>
                <p>전문 인쇄 서비스의 모든 것</p>
            </div>
        </div>
        
        <div class="dsp-hero__slide is-peek" role="tabpanel" aria-label="슬라이드 2 of 7">
            <img src="/slide/slide__Sticker.gif" alt="스티커 인쇄 서비스">
            <div class="dsp-hero__content">
                <h3>스티커 인쇄</h3>
                <p>고품질 스티커 제작 서비스</p>
            </div>
        </div>
        
        <div class="dsp-hero__slide" role="tabpanel" aria-label="슬라이드 3 of 7">
            <img src="/slide/slide__Sticker_2.gif" alt="라벨 스티커 서비스">
            <div class="dsp-hero__content">
                <h3>라벨 스티커</h3>
                <p>맞춤형 라벨 스티커 제작</p>
            </div>
        </div>
        
        <div class="dsp-hero__slide" role="tabpanel" aria-label="슬라이드 4 of 7">
            <img src="/slide/slide__Sticker_3.gif" alt="자석 스티커 서비스">
            <div class="dsp-hero__content">
                <h3>자석 스티커</h3>
                <p>강력한 자석 스티커 제작</p>
            </div>
        </div>
        
        <div class="dsp-hero__slide" role="tabpanel" aria-label="슬라이드 5 of 7">
            <img src="/slide/slide_cadarok.gif" alt="카다록 제작 서비스">
            <div class="dsp-hero__content">
                <h3>카다록 제작</h3>
                <p>전문적인 카다록 인쇄</p>
            </div>
        </div>
        
        <div class="dsp-hero__slide" role="tabpanel" aria-label="슬라이드 6 of 7">
            <img src="/slide/slide__poster.gif" alt="포스터 인쇄 서비스">
            <div class="dsp-hero__content">
                <h3>포스터 인쇄</h3>
                <p>대형 포스터 제작 서비스</p>
            </div>
        </div>
        
        <div class="dsp-hero__slide" role="tabpanel" aria-label="슬라이드 7 of 7">
            <img src="/slide/slide_Ncr.gif" alt="NCR 용지 인쇄 서비스">
            <div class="dsp-hero__content">
                <h3>NCR 용지</h3>
                <p>복사용지 및 양식지 제작</p>
            </div>
        </div>
    </div>
    
    <!-- 자동으로만 흐르는 캐러셀 - 버튼 없음 -->
</div>