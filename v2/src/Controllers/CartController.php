<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;

class CartController
{
    private array $products;
    
    public function __construct()
    {
        $this->products = require V2_ROOT . '/config/products.php';
    }
    
    public function index(Request $request): Response
    {
        $sessionId = Session::id();
        $items = $this->getCartItems($sessionId);
        $totals = $this->calculateTotals($items);
        
        $content = View::render('pages.cart.index', [
            'items' => $items,
            'totals' => $totals,
            'products' => $this->products,
        ]);
        
        $html = View::layout('main', $content, [
            'title' => '장바구니 - 두손기획인쇄',
        ]);
        
        return Response::html($html);
    }
    
    public function add(Request $request): Response
    {
        if (!CSRF::verify($request->post('_token', ''))) {
            return Response::json(['success' => false, 'message' => 'CSRF 토큰 오류'], 403);
        }
        
        $sessionId = Session::id();
        $productType = $request->post('product_type', '');
        
        if (!isset($this->products[$productType])) {
            return Response::json(['success' => false, 'message' => '잘못된 상품 유형'], 400);
        }
        
        $db = Database::getInstance();
        
        $data = [
            'session_id' => $sessionId,
            'product_type' => $productType,
            'MY_type' => $request->post('MY_type', ''),
            'MY_Fsd' => $request->post('MY_Fsd', ''),
            'Section' => $request->post('Section', ''),
            'POtype' => $request->post('POtype', ''),
            'MY_amount' => $request->post('MY_amount', ''),
            'ordertype' => $request->post('ordertype', ''),
            'st_price' => (float) $request->post('price', 0),
            'st_price_vat' => (float) $request->post('vat_price', 0),
            'work_memo' => $request->post('work_memo', ''),
            'upload_method' => $request->post('upload_method', 'upload'),
            'spec_type' => $request->post('spec_type', ''),
            'spec_material' => $request->post('spec_material', ''),
            'spec_size' => $request->post('spec_size', ''),
            'spec_sides' => $request->post('spec_sides', ''),
            'spec_design' => $request->post('spec_design', ''),
            'quantity_value' => (float) $request->post('quantity_value', 0),
            'quantity_unit' => $request->post('quantity_unit', ''),
            'quantity_sheets' => (int) $request->post('quantity_sheets', 0),
            'quantity_display' => $request->post('quantity_display', ''),
            'price_supply' => (int) $request->post('price_supply', 0),
            'price_vat' => (int) $request->post('price_vat', 0),
            'price_vat_amount' => (int) $request->post('price_vat_amount', 0),
            'premium_options' => $request->post('premium_options', ''),
            'data_version' => 2,
        ];
        
        $uploadedFiles = $this->handleFileUpload($productType);
        if ($uploadedFiles) {
            $data['uploaded_files'] = json_encode($uploadedFiles, JSON_UNESCAPED_UNICODE);
            $data['ImgFolder'] = $uploadedFiles['folder'] ?? '';
            $data['ThingCate'] = $uploadedFiles['thing_cate'] ?? '';
        }
        
        $optionNames = $this->getOptionNames($productType, $data);
        $data = array_merge($data, $optionNames);
        
        try {
            $basketId = $db->insert('shop_temp', $data);
            $cartCount = $this->getCartCount($sessionId);
            
            return Response::json([
                'success' => true,
                'message' => '장바구니에 추가되었습니다.',
                'basket_id' => $basketId,
                'cart_count' => $cartCount,
            ]);
        } catch (\Exception $e) {
            error_log('Cart add error: ' . $e->getMessage());
            return Response::json(['success' => false, 'message' => '장바구니 추가 실패'], 500);
        }
    }
    
    public function remove(Request $request): Response
    {
        if (!CSRF::verify($request->post('_token', ''))) {
            return Response::json(['success' => false, 'message' => 'CSRF 토큰 오류'], 403);
        }
        
        $sessionId = Session::id();
        $itemId = (int) $request->post('item_id', 0);
        
        if ($itemId <= 0) {
            return Response::json(['success' => false, 'message' => '잘못된 아이템 ID'], 400);
        }
        
        $db = Database::getInstance();
        
        try {
            $affected = $db->delete(
                'shop_temp',
                'no = :id AND session_id = :session_id',
                ['id' => $itemId, 'session_id' => $sessionId]
            );
            
            if ($affected === 0) {
                return Response::json(['success' => false, 'message' => '아이템을 찾을 수 없습니다.'], 404);
            }
            
            $cartCount = $this->getCartCount($sessionId);
            $totals = $this->calculateTotals($this->getCartItems($sessionId));
            
            return Response::json([
                'success' => true,
                'message' => '삭제되었습니다.',
                'cart_count' => $cartCount,
                'totals' => $totals,
            ]);
        } catch (\Exception $e) {
            error_log('Cart remove error: ' . $e->getMessage());
            return Response::json(['success' => false, 'message' => '삭제 실패'], 500);
        }
    }
    
    public function update(Request $request): Response
    {
        if (!CSRF::verify($request->post('_token', ''))) {
            return Response::json(['success' => false, 'message' => 'CSRF 토큰 오류'], 403);
        }
        
        $sessionId = Session::id();
        $itemId = (int) $request->post('item_id', 0);
        $quantity = (int) $request->post('quantity', 0);
        
        if ($itemId <= 0 || $quantity <= 0) {
            return Response::json(['success' => false, 'message' => '잘못된 요청'], 400);
        }
        
        return Response::json([
            'success' => true,
            'message' => '장바구니 수량 변경은 현재 지원하지 않습니다. 삭제 후 다시 추가해 주세요.',
        ]);
    }
    
    public function uploadFiles(Request $request): Response
    {
        if (!CSRF::verify($request->post('_token', ''))) {
            return Response::json(['success' => false, 'message' => 'CSRF 토큰 오류'], 403);
        }
        
        $sessionId = Session::id();
        $itemId = (int) $request->post('item_id', 0);
        
        if ($itemId <= 0) {
            return Response::json(['success' => false, 'message' => '잘못된 아이템 ID'], 400);
        }
        
        $db = Database::getInstance();
        
        $item = $db->queryOne(
            "SELECT * FROM shop_temp WHERE no = :id AND session_id = :session_id",
            ['id' => $itemId, 'session_id' => $sessionId]
        );
        
        if (!$item) {
            return Response::json(['success' => false, 'message' => '아이템을 찾을 수 없습니다.'], 404);
        }
        
        $productType = $item['product_type'] ?? 'default';
        $existingFiles = [];
        if (!empty($item['uploaded_files'])) {
            $decoded = json_decode($item['uploaded_files'], true);
            if (isset($decoded['files'])) {
                $existingFiles = $decoded['files'];
            } elseif (is_array($decoded)) {
                $existingFiles = $decoded;
            }
        }
        
        $newUpload = $this->handleFileUpload($productType);
        
        if ($newUpload && !empty($newUpload['files'])) {
            $existingFiles = array_merge($existingFiles, $newUpload['files']);
        }
        
        $uploadData = [
            'files' => $existingFiles,
            'folder' => $newUpload['folder'] ?? ('/ImgFolder/' . $productType),
            'thing_cate' => $productType,
        ];
        
        try {
            $db->update(
                'shop_temp',
                [
                    'uploaded_files' => json_encode($uploadData, JSON_UNESCAPED_UNICODE),
                    'ImgFolder' => $uploadData['folder'],
                    'ThingCate' => $productType,
                    'upload_method' => 'upload',
                ],
                'no = :id',
                ['id' => $itemId]
            );
            
            return Response::json([
                'success' => true,
                'message' => '파일이 업로드되었습니다.',
                'files' => $existingFiles,
            ]);
        } catch (\Exception $e) {
            error_log('File upload error: ' . $e->getMessage());
            return Response::json(['success' => false, 'message' => '파일 저장 실패'], 500);
        }
    }
    
    public function updateFiles(Request $request): Response
    {
        if (!CSRF::verify($request->post('_token', ''))) {
            return Response::json(['success' => false, 'message' => 'CSRF 토큰 오류'], 403);
        }
        
        $sessionId = Session::id();
        $itemId = (int) $request->post('item_id', 0);
        $files = $request->post('files', []);
        
        if ($itemId <= 0) {
            return Response::json(['success' => false, 'message' => '잘못된 아이템 ID'], 400);
        }
        
        $db = Database::getInstance();
        
        $item = $db->queryOne(
            "SELECT * FROM shop_temp WHERE no = :id AND session_id = :session_id",
            ['id' => $itemId, 'session_id' => $sessionId]
        );
        
        if (!$item) {
            return Response::json(['success' => false, 'message' => '아이템을 찾을 수 없습니다.'], 404);
        }
        
        $productType = $item['product_type'] ?? 'default';
        
        $uploadData = [
            'files' => is_array($files) ? $files : [],
            'folder' => '/ImgFolder/' . $productType,
            'thing_cate' => $productType,
        ];
        
        try {
            $db->update(
                'shop_temp',
                [
                    'uploaded_files' => json_encode($uploadData, JSON_UNESCAPED_UNICODE),
                    'upload_method' => empty($files) ? 'later' : 'upload',
                ],
                'no = :id',
                ['id' => $itemId]
            );
            
            return Response::json([
                'success' => true,
                'message' => '파일 목록이 업데이트되었습니다.',
            ]);
        } catch (\Exception $e) {
            error_log('Update files error: ' . $e->getMessage());
            return Response::json(['success' => false, 'message' => '파일 목록 저장 실패'], 500);
        }
    }
    
    public function quote(Request $request): Response
    {
        $sessionId = Session::id();
        $items = $this->getCartItems($sessionId);
        
        if (empty($items)) {
            return Response::json(['success' => false, 'message' => '장바구니가 비어있습니다.'], 400);
        }
        
        $totals = $this->calculateTotals($items);
        
        $quoteData = [
            'date' => date('Y-m-d H:i:s'),
            'quote_no' => 'Q' . date('YmdHis'),
            'items' => array_map(function($item) {
                return [
                    'product_name' => $this->products[$item['product_type']]['name'] ?? $item['product_type'],
                    'spec' => $this->formatSpec($item),
                    'quantity' => $item['quantity_display'] ?? '',
                    'price' => $item['st_price_vat'] ?? 0,
                ];
            }, $items),
            'totals' => $totals,
        ];
        
        return Response::json([
            'success' => true,
            'quote' => $quoteData,
        ]);
    }
    
    private function getCartItems(string $sessionId): array
    {
        $db = Database::getInstance();
        
        return $db->query(
            "SELECT * FROM shop_temp WHERE session_id = :session_id ORDER BY no DESC",
            ['session_id' => $sessionId]
        );
    }
    
    private function getCartCount(string $sessionId): int
    {
        $db = Database::getInstance();
        
        $result = $db->queryOne(
            "SELECT COUNT(*) as cnt FROM shop_temp WHERE session_id = :session_id",
            ['session_id' => $sessionId]
        );
        
        return (int) ($result['cnt'] ?? 0);
    }
    
    private function calculateTotals(array $items): array
    {
        $supplyTotal = 0;
        $vatTotal = 0;
        $grandTotal = 0;
        
        foreach ($items as $item) {
            if (!empty($item['price_supply'])) {
                $supplyTotal += (int) $item['price_supply'];
                $vatTotal += (int) ($item['price_vat_amount'] ?? 0);
                $grandTotal += (int) $item['price_vat'];
            } else {
                $supplyTotal += (float) ($item['st_price'] ?? 0);
                $grandTotal += (float) ($item['st_price_vat'] ?? $item['st_price'] ?? 0);
                $vatTotal = $grandTotal - $supplyTotal;
            }
        }
        
        return [
            'supply_total' => $supplyTotal,
            'vat_total' => $vatTotal,
            'grand_total' => $grandTotal,
            'item_count' => count($items),
        ];
    }
    
    private function getOptionNames(string $productType, array $data): array
    {
        $db = Database::getInstance();
        $names = [];
        
        if (!empty($data['MY_type'])) {
            $tableName = $this->getTransactionTable($productType);
            if ($tableName) {
                $result = $db->queryOne(
                    "SELECT title FROM {$tableName} WHERE no = :no",
                    ['no' => $data['MY_type']]
                );
                $names['MY_type_name'] = $result['title'] ?? '';
            }
        }
        
        if (!empty($data['Section'])) {
            $tableName = $this->getTransactionTable($productType);
            if ($tableName) {
                $result = $db->queryOne(
                    "SELECT title FROM {$tableName} WHERE no = :no",
                    ['no' => $data['Section']]
                );
                $names['Section_name'] = $result['title'] ?? '';
            }
        }
        
        if (!empty($data['POtype'])) {
            $potypeNames = [
                '1' => '단면칼라',
                '2' => '양면칼라',
            ];
            $names['POtype_name'] = $potypeNames[$data['POtype']] ?? '';
        }
        
        return $names;
    }
    
    private function getTransactionTable(string $productType): string
    {
        $tables = [
            'namecard' => 'mlangprintauto_transactioncate',
            'sticker_new' => 'mlangprintauto_sticker_new',
            'inserted' => 'mlangprintauto_inserted',
            'envelope' => 'mlangprintauto_envelope',
            'cadarok' => 'mlangprintauto_cadarok',
            'littleprint' => 'mlangprintauto_littleprint',
            'merchandisebond' => 'mlangprintauto_merchandisebond',
            'ncrflambeau' => 'mlangprintauto_ncrflambeau',
            'msticker' => 'mlangprintauto_msticker',
        ];
        
        return $tables[$productType] ?? '';
    }
    
    private function handleFileUpload(string $productType): ?array
    {
        if (empty($_FILES) || !isset($_FILES['file'])) {
            return null;
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $productType . '/';
        $webPath = '/ImgFolder/' . $productType;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $files = $_FILES['file'];
        $uploadedFiles = [];
        
        if (is_array($files['name'])) {
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $filename = $this->generateFileName($files['name'][$i]);
                    $destination = $uploadDir . $filename;
                    
                    if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                        $uploadedFiles[] = [
                            'name' => $filename,
                            'original_name' => $files['name'][$i],
                            'path' => $webPath . '/' . $filename,
                            'size' => $files['size'][$i],
                        ];
                    }
                }
            }
        } else {
            if ($files['error'] === UPLOAD_ERR_OK) {
                $filename = $this->generateFileName($files['name']);
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($files['tmp_name'], $destination)) {
                    $uploadedFiles[] = [
                        'name' => $filename,
                        'original_name' => $files['name'],
                        'path' => $webPath . '/' . $filename,
                        'size' => $files['size'],
                    ];
                }
            }
        }
        
        if (empty($uploadedFiles)) {
            return null;
        }
        
        return [
            'files' => $uploadedFiles,
            'folder' => $webPath,
            'thing_cate' => $productType,
        ];
    }
    
    private function generateFileName(string $originalName): string
    {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        return date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    }
    
    private function formatSpec(array $item): string
    {
        $parts = [];
        
        if (!empty($item['spec_type'])) {
            $parts[] = $item['spec_type'];
        }
        if (!empty($item['spec_material'])) {
            $parts[] = $item['spec_material'];
        }
        if (!empty($item['spec_size'])) {
            $parts[] = $item['spec_size'];
        }
        if (!empty($item['spec_sides'])) {
            $parts[] = $item['spec_sides'];
        }
        
        if (empty($parts)) {
            if (!empty($item['MY_type_name'])) {
                $parts[] = $item['MY_type_name'];
            }
            if (!empty($item['Section_name'])) {
                $parts[] = $item['Section_name'];
            }
            if (!empty($item['POtype_name'])) {
                $parts[] = $item['POtype_name'];
            }
        }
        
        return implode(' / ', $parts);
    }
}
