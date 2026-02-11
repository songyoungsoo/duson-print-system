<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>페이지를 찾을 수 없습니다 - 두손기획인쇄</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', sans-serif; background: #f8f9fa; color: #333; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { text-align: center; padding: 40px 20px; max-width: 500px; }
        .code { font-size: 120px; font-weight: 700; color: #1a3a5c; line-height: 1; }
        .message { font-size: 20px; color: #555; margin: 16px 0 8px; }
        .sub { font-size: 14px; color: #888; margin-bottom: 32px; }
        .links { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .links a { display: inline-block; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; transition: background 0.2s; }
        .btn-home { background: #1a3a5c; color: #fff; }
        .btn-home:hover { background: #0d2640; }
        .btn-products { background: #e9ecef; color: #333; }
        .btn-products:hover { background: #dee2e6; }
        .contact { margin-top: 32px; font-size: 13px; color: #999; }
        .contact a { color: #1a3a5c; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">404</div>
        <p class="message">페이지를 찾을 수 없습니다</p>
        <p class="sub">요청하신 페이지가 삭제되었거나 주소가 변경되었을 수 있습니다.</p>
        <div class="links">
            <a href="/" class="btn-home">메인으로 이동</a>
            <a href="/mlangprintauto/inserted/" class="btn-products">견적 바로가기</a>
        </div>
        <p class="contact">문의: <a href="tel:02-2632-1830">02-2632-1830</a></p>
    </div>
</body>
</html>
