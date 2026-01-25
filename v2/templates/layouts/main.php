<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? '두손기획인쇄' ?></title>
    
    <meta name="description" content="<?= $description ?? '두손기획인쇄 - 스티커, 전단지, 명함, 봉투, 카다록, 포스터, 상품권, 양식지, 자석스티커 인쇄 전문' ?>">
    
    <?= \App\Core\CSRF::meta() ?>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Pretendard', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        brand: {
                            navy: '#1e3a5f',
                            gold: '#c9a962',
                        }
                    }
                }
            }
        }
    </script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="<?= \App\Core\View::asset('css/app.css') ?>">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans bg-gray-50 text-gray-900">
    
    <?php include __DIR__ . '/../components/header.php'; ?>
    
    <main class="min-h-screen">
        <?= $content ?>
    </main>
    
    <?php include __DIR__ . '/../components/footer.php'; ?>
    
    <?php include __DIR__ . '/../components/chat-widget.php'; ?>
    
    <script src="<?= \App\Core\View::asset('js/app.js') ?>"></script>
</body>
</html>
