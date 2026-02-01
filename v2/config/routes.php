<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\OrderController;
use App\Controllers\AuthController;
use App\Controllers\CopywriterController;
use App\Controllers\ChatbotController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\Admin\ProofController as AdminProofController;

return function (Router $router) {
    
    $router->get('/', [HomeController::class, 'index']);
    
    $router->get('/product/{type}', [ProductController::class, 'show']);
    $router->post('/product/{type}/calculate', [ProductController::class, 'calculate']);
    $router->get('/product/{type}/options', [ProductController::class, 'options']);
    $router->get('/product/{type}/gallery', [ProductController::class, 'gallery']);
    
    $router->get('/cart', [CartController::class, 'index']);
    $router->post('/cart/add', [CartController::class, 'add']);
    $router->post('/cart/remove', [CartController::class, 'remove']);
    $router->post('/cart/update', [CartController::class, 'update']);
    $router->post('/cart/upload-files', [CartController::class, 'uploadFiles']);
    $router->post('/cart/update-files', [CartController::class, 'updateFiles']);
    $router->get('/cart/quote', [CartController::class, 'quote']);
    
    $router->get('/order', [OrderController::class, 'create']);
    $router->post('/order', [OrderController::class, 'store']);
    $router->get('/order/complete/{no}', [OrderController::class, 'complete']);
    
    $router->get('/login', [AuthController::class, 'loginForm']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/logout', [AuthController::class, 'logout']);
    $router->get('/register', [AuthController::class, 'registerForm']);
    $router->post('/register', [AuthController::class, 'register']);
    
    $router->get('/auth/kakao', [AuthController::class, 'kakao']);
    $router->get('/auth/kakao/callback', [AuthController::class, 'kakaoCallback']);
    $router->get('/auth/naver', [AuthController::class, 'naver']);
    $router->get('/auth/naver/callback', [AuthController::class, 'naverCallback']);
    $router->get('/auth/google', [AuthController::class, 'google']);
    $router->get('/auth/google/callback', [AuthController::class, 'googleCallback']);
    
    $router->get('/copywriter', [CopywriterController::class, 'index']);
    $router->post('/copywriter/generate', [CopywriterController::class, 'generate']);
    
    $router->get('/chatbot', [ChatbotController::class, 'index']);
    $router->post('/chatbot/chat', [ChatbotController::class, 'chat']);
    $router->post('/chatbot/tts', [ChatbotController::class, 'tts']);
    
    $router->group('/admin', function (Router $router) {
        $router->get('/orders', [AdminOrderController::class, 'index']);
        $router->get('/orders/{no}', [AdminOrderController::class, 'show']);
        $router->post('/orders/{no}/status', [AdminOrderController::class, 'updateStatus']);
        $router->get('/orders/{no}/print', [AdminOrderController::class, 'print']);
        
        $router->get('/products', [AdminProductController::class, 'index']);
        $router->get('/products/create', [AdminProductController::class, 'create']);
        $router->post('/products', [AdminProductController::class, 'store']);
        $router->get('/products/{id}/edit', [AdminProductController::class, 'edit']);
        $router->post('/products/{id}', [AdminProductController::class, 'update']);
        
        $router->get('/proofs/{no}', [AdminProofController::class, 'show']);
        $router->post('/proofs/{no}/upload', [AdminProofController::class, 'upload']);
        $router->post('/proofs/{no}/confirm', [AdminProofController::class, 'confirm']);
    });
};
