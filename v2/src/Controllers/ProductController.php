<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Database;
use App\Services\Product\PriceCalculator;

class ProductController
{
    private array $products;
    
    public function __construct()
    {
        $this->products = require V2_ROOT . '/config/products.php';
    }
    
    public function show(Request $request, array $params): Response
    {
        $type = $params['type'] ?? '';
        
        if (!isset($this->products[$type])) {
            return Response::notFound('상품을 찾을 수 없습니다.');
        }
        
        $product = $this->products[$type];
        $product['type'] = $type;
        
        $calculator = new PriceCalculator();
        $initialDropdowns = $calculator->getInitialDropdowns($type);
        
        $uiType = $product['ui_type'] ?? 'dropdown_3level';
        $uiConfig = $product['ui_config'] ?? [];
        
        $premiumOptions = $this->getProductOptions($type);
        $gallery = $this->getProductGallery($type);
        
        $content = View::render('pages.product.show', [
            'product' => $product,
            'uiType' => $uiType,
            'uiConfig' => $uiConfig,
            'initialDropdowns' => $initialDropdowns,
            'premiumOptions' => $premiumOptions,
            'gallery' => $gallery,
        ]);
        
        $html = View::layout('main', $content, [
            'title' => $product['name'] . ' - 두손기획인쇄',
        ]);
        
        return Response::html($html);
    }
    
    public function calculate(Request $request, array $params): Response
    {
        $type = $params['type'] ?? '';
        
        if (!isset($this->products[$type])) {
            return Response::json(['success' => false, 'error' => '상품을 찾을 수 없습니다.'], 404);
        }
        
        $calculator = new PriceCalculator();
        
        // Start with all request params (includes mesu, jong, garo, sero for sticker)
        $calcParams = $request->all();
        
        // Add legacy param mappings only if values exist
        $legacyMappings = [
            'MY_type' => $request->post('MY_type') ?: $request->post('category'),
            'PN_type' => $request->post('PN_type') ?: $request->post('section'),
            'MY_Fsd' => $request->post('MY_Fsd') ?: $request->post('paper'),
            'MY_amount' => $request->post('MY_amount') ?: $request->post('quantity'),
            'POtype' => $request->post('POtype') ?: $request->post('sides') ?: '1',
            'ordertype' => $request->post('ordertype') ?: $request->post('design') ?: '1',
        ];
        
        foreach ($legacyMappings as $key => $value) {
            if ($value !== null && $value !== '') {
                $calcParams[$key] = $value;
            }
        }
        
        $result = $calculator->calculate($type, $calcParams);
        
        return Response::json($result);
    }
    
    public function options(Request $request, array $params): Response
    {
        $type = $params['type'] ?? '';
        $level = $request->get('level', '');
        
        if (!isset($this->products[$type])) {
            return Response::json(['success' => false, 'error' => '상품을 찾을 수 없습니다.'], 404);
        }
        
        if (empty($level)) {
            return Response::json(['success' => false, 'error' => 'level 파라미터가 필요합니다.'], 400);
        }
        
        $parentValues = [
            'style' => $request->get('style', $request->get('MY_type', '')),
            'TreeSelect' => $request->get('TreeSelect', $request->get('MY_Fsd', '')),
            'Section' => $request->get('Section', $request->get('PN_type', '')),
        ];
        
        $calculator = new PriceCalculator();
        $options = $calculator->getDropdownOptions($type, $level, $parentValues);
        
        return Response::json([
            'success' => true,
            'options' => $options,
        ]);
    }
    
    public function gallery(Request $request, array $params): Response
    {
        $type = $params['type'] ?? '';
        $gallery = $this->getProductGallery($type, true);
        
        return Response::json(['success' => true, 'images' => $gallery]);
    }
    
    /**
     * 제품별 프리미엄 옵션 반환 (config/products.php 기반)
     * key-value 형태 그대로 반환 (템플릿 PHP와 JS에서 둘 다 사용)
     */
    private function getProductOptions(string $type): array
    {
        $product = $this->products[$type] ?? [];
        return $product['premium_options'] ?? [];
    }
    
    private function getProductGallery(string $type, bool $all = false): array
    {
        $basePath = $_SERVER['DOCUMENT_ROOT'] . "/ImgFolder/{$type}";
        $webPath = "/ImgFolder/{$type}";
        
        if (!is_dir($basePath)) {
            return [];
        }
        
        $images = [];
        $files = glob($basePath . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        
        if ($files) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $limit = $all ? 50 : 5;
            $files = array_slice($files, 0, $limit);
            
            foreach ($files as $file) {
                $images[] = [
                    'url' => $webPath . '/' . basename($file),
                    'thumb' => $webPath . '/' . basename($file),
                    'name' => basename($file),
                ];
            }
        }
        
        return $images;
    }
}
