<?php
  $orderNo = isset($_GET['no']) ? intval($_GET['no']) : 0;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>두손기획인쇄 교정보기</title>

  <!-- Noto Sans KR 불러오기 -->
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap" rel="stylesheet">

  <style>
    /* 기본 리셋 */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }

    /* 배경과 중앙 정렬 */
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #0d1b2a;
      font-family: 'Noto Sans KR', sans-serif;
    }

    /* 컨테이너 */
    .container {
      width: 90%;
      max-width: 360px;
      background: #ffffff;
      border-radius: 8px;
      padding: 24px 16px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* 제목 */
    .title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 24px;
      color: #212121;
    }

    /* 입력폼 */
    .inputs {
      margin-bottom: 24px;
    }
    .inputs input {
      width: 100%;
      padding: 12px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 12px;
    }

    /* 공유 버튼 */
    #share-btn {
      width: 100%;
      padding: 12px;
      font-size: 1rem;
      font-weight: 600;
      color: #ffffff;
      background-color: #ffa726;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-bottom: 24px;
    }

    /* 액션 버튼 그룹 */
    .buttons {
      display: flex;
      gap: 12px;
      margin-bottom: 24px;
    }
    .buttons .btn {
      flex: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 0;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 4px;
      text-decoration: none;
      cursor: pointer;
    }
    .btn-view {
      background-color: #212121;
      color: #ffffff;
    }
    .btn-view i {
      margin-right: 6px;
    }
    .btn-kakao {
      background-color: #FEE500;
      color: #3A1D1D;
    }
    .btn-kakao i {
      margin-right: 6px;
    }

    /* 하단 링크 버튼 */
    .footer-link {
      display: block;
      width: 100%;
      padding: 12px 0;
      font-size: 0.9rem;
      color: #ffffff;
      background-color: #1a73e8;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 500;
    }
  </style>
</head>
<body>

  <div class="container">
    <!-- 1) 제목 -->
    <h1 class="title">두손기획인쇄</h1>



    <!-- 3) 교정시안보기 / 카톡문의 버튼 -->
    <div class="buttons">
      <a href="MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=<?php echo  $orderNo ?>"
         class="btn btn-view">
        <i class="fas fa-eye"></i> 교정시안보기
      </a>
      <a href="https://pf.kakao.com/_pEGhj/chat"
         class="btn btn-kakao" target="_blank" rel="noopener">
        <i class="fas fa-comment"></i> 카카오톡
      </a>
    </div>

        <!-- 2) 입력폼 -->
    <div class="inputs">
      <input type="text"    id="name"    placeholder="이름 / 상호 입력">
      <input type="tel"     id="phone"   placeholder="휴대폰 번호 입력">
      <button id="share-btn">카톡으로 정보 전송하기</button>
    </div>

    <!-- 4) 푸터 링크 버튼 -->
    <a href="http://dsp114.com" class="footer-link" target="_blank" rel="noopener">
      두손기획인쇄 홈페이지 바로가기
    </a>
  </div>

  <!-- 공유 스크립트 -->
  <script>
    document.getElementById('share-btn').addEventListener('click', () => {
      const orderNo = <?php echo  $orderNo ?>;
      const name    = document.getElementById('name').value.trim();
      const phone   = document.getElementById('phone').value.trim();

      if (!name || !phone) {
        return alert('이름과 연락처를 입력해 주세요.');
      }

      const text = 
        `두손기획인쇄 교정요청\n` +
        `주문번호: ${orderNo}\n` +
        `이름: ${name}\n` +
        `연락처: ${phone}\n` +
        `(수정할 이미지를 카톡으로 보내주세요)`;

      if (navigator.share) {
        navigator.share({ title: '두손기획인쇄 문의', text })
          .catch(err => console.error(err));
      } else {
        alert('공유를 지원하지 않는 환경입니다. 카톡 채팅방에서 직접 연락처를 공유해주세요.');
      }
    });
  </script>

  <!-- FontAwesome -->
  <script src="https://kit.fontawesome.com/a6737bf84e.js" crossorigin="anonymous"></script>
</body>
</html>
