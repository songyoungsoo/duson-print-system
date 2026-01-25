<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class HomeController
{
    public function index(Request $request, array $params): Response
    {
        $products = require V2_ROOT . '/config/products.php';
        
        $content = View::render('pages.home.index', [
            'title' => '두손기획인쇄 - 스티커 전단지 명함 인쇄 전문',
            'products' => $products,
        ]);
        
        $html = View::layout('main', $content, [
            'title' => '두손기획인쇄 - 스티커 전단지 명함 인쇄 전문',
        ]);
        
        return Response::html($html);
    }
}
