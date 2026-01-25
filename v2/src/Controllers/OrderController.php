<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\Validator;

class OrderController
{
    private array $products;
    
    public function __construct()
    {
        $this->products = require V2_ROOT . '/config/products.php';
    }
    
    public function create(Request $request): Response
    {
        $sessionId = Session::id();
        $items = $this->getCartItems($sessionId);
        
        if (empty($items)) {
            Session::flash('error', '장바구니가 비어있습니다.');
            return Response::redirect('/v2/public/cart');
        }
        
        $totals = $this->calculateTotals($items);
        $user = $this->getCurrentUser();
        
        $content = View::render('pages.order.create', [
            'items' => $items,
            'totals' => $totals,
            'products' => $this->products,
            'user' => $user,
        ]);
        
        $html = View::layout('main', $content, [
            'title' => '주문하기 - 두손기획인쇄',
        ]);
        
        return Response::html($html);
    }
    
    public function store(Request $request): Response
    {
        if (!CSRF::validate($request->post('_token', ''))) {
            return Response::json(['success' => false, 'message' => 'CSRF 토큰 오류'], 403);
        }
        
        $sessionId = Session::id();
        $items = $this->getCartItems($sessionId);
        
        if (empty($items)) {
            return Response::json(['success' => false, 'message' => '장바구니가 비어있습니다.'], 400);
        }
        
        $validator = new Validator($request->all());
        $validator->required(['name', 'phone', 'zip', 'address1']);
        
        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'message' => '필수 정보를 입력해주세요.',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            $orderNo = $this->generateOrderNo($db);
            $this->createOrderFolder($orderNo);
            $totals = $this->calculateTotals($items);
            
            $orderData = [
                'no' => $orderNo,
                'Type' => $this->getOrderType($items),
                'Type_1' => $this->formatOrderSpec($items),
                'money_1' => $totals['supply_total'],
                'money_2' => $totals['grand_total'],
                'money_3' => 0,
                'money_4' => $totals['vat_total'],
                'money_5' => 0,
                'name' => $request->post('name'),
                'email' => $request->post('email', ''),
                'zip' => $request->post('zip'),
                'zip1' => $request->post('address1'),
                'zip2' => $request->post('address2', ''),
                'phone' => $request->post('phone'),
                'Hendphone' => $request->post('mobile', ''),
                'delivery' => $request->post('delivery_memo', ''),
                'bizname' => $request->post('company', ''),
                'bank' => $request->post('payment_method', ''),
                'cont' => $request->post('memo', ''),
                'date' => date('Y-m-d H:i:s'),
                'OrderStyle' => 'v2_order',
                'ThingCate' => $items[0]['product_type'] ?? '',
                'ImgFolder' => $this->getImgFolders($items),
                'pass' => $request->post('password', ''),
                'Gensu' => '1',
            ];
            
            $sql = "INSERT INTO mlangorder_printauto (" . implode(', ', array_keys($orderData)) . ") 
                    VALUES (:" . implode(', :', array_keys($orderData)) . ")";
            $db->execute($sql, $orderData);
            
            $this->moveFilesToOrder($items, $orderNo);
            $this->clearCart($sessionId);
            
            $db->commit();
            
            $this->sendOrderNotification($orderNo, $orderData, $items);
            
            return Response::json([
                'success' => true,
                'message' => '주문이 완료되었습니다.',
                'order_no' => $orderNo,
                'redirect' => "/v2/public/order/complete/{$orderNo}",
            ]);
            
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Order store error: ' . $e->getMessage());
            return Response::json(['success' => false, 'message' => '주문 처리 중 오류가 발생했습니다.'], 500);
        }
    }
    
    public function complete(Request $request, array $params): Response
    {
        $orderNo = (int) ($params['no'] ?? 0);
        
        if ($orderNo <= 0) {
            return Response::notFound('주문을 찾을 수 없습니다.');
        }
        
        $db = Database::getInstance();
        $order = $db->queryOne(
            "SELECT * FROM mlangorder_printauto WHERE no = :no",
            ['no' => $orderNo]
        );
        
        if (!$order) {
            return Response::notFound('주문을 찾을 수 없습니다.');
        }
        
        $content = View::render('pages.order.complete', [
            'order' => $order,
            'products' => $this->products,
        ]);
        
        $html = View::layout('main', $content, [
            'title' => '주문 완료 - 두손기획인쇄',
        ]);
        
        return Response::html($html);
    }
    
    private function getCartItems(string $sessionId): array
    {
        $db = Database::getInstance();
        return $db->query(
            "SELECT * FROM shop_temp WHERE session_id = :session_id ORDER BY no DESC",
            ['session_id' => $sessionId]
        );
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
    
    private function getCurrentUser(): ?array
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return null;
        }
        
        $db = Database::getInstance();
        return $db->queryOne(
            "SELECT * FROM mlangmember WHERE no = :no",
            ['no' => $userId]
        );
    }
    
    private function generateOrderNo(Database $db): int
    {
        $result = $db->queryOne("SELECT MAX(no) as max_no FROM mlangorder_printauto");
        return ($result['max_no'] ?? 0) + 1;
    }
    
    private function createOrderFolder(int $orderNo): void
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/mlangorder_printauto/upload/{$orderNo}";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            chmod($dir, 0777);
        }
    }
    
    private function getOrderType(array $items): string
    {
        $types = [];
        foreach ($items as $item) {
            $productType = $item['product_type'] ?? '';
            if (isset($this->products[$productType])) {
                $types[] = $this->products[$productType]['name'];
            }
        }
        return implode(', ', array_unique($types)) ?: '인쇄물';
    }
    
    private function formatOrderSpec(array $items): string
    {
        $specs = [];
        foreach ($items as $index => $item) {
            $itemSpec = [];
            $itemSpec[] = "[ " . ($index + 1) . " ] " . ($this->products[$item['product_type']]['name'] ?? $item['product_type']);
            
            if (!empty($item['spec_type'])) $itemSpec[] = "종류: " . $item['spec_type'];
            if (!empty($item['spec_material'])) $itemSpec[] = "재질: " . $item['spec_material'];
            if (!empty($item['spec_size'])) $itemSpec[] = "사이즈: " . $item['spec_size'];
            if (!empty($item['spec_sides'])) $itemSpec[] = "인쇄: " . $item['spec_sides'];
            if (!empty($item['quantity_display'])) $itemSpec[] = "수량: " . $item['quantity_display'];
            
            if (empty($item['spec_type']) && !empty($item['MY_type_name'])) {
                $itemSpec[] = "종류: " . $item['MY_type_name'];
            }
            if (empty($item['spec_material']) && !empty($item['Section_name'])) {
                $itemSpec[] = "재질: " . $item['Section_name'];
            }
            if (empty($item['quantity_display']) && !empty($item['MY_amount'])) {
                $itemSpec[] = "수량: " . $item['MY_amount'];
            }
            
            $specs[] = implode("\n", $itemSpec);
        }
        return implode("\n\n", $specs);
    }
    
    private function getImgFolders(array $items): string
    {
        $folders = [];
        foreach ($items as $item) {
            if (!empty($item['ImgFolder'])) {
                $folders[] = $item['ImgFolder'];
            }
        }
        return implode('|', $folders);
    }
    
    private function moveFilesToOrder(array $items, int $orderNo): void
    {
        $destDir = $_SERVER['DOCUMENT_ROOT'] . "/mlangorder_printauto/upload/{$orderNo}/";
        
        foreach ($items as $item) {
            if (empty($item['uploaded_files'])) continue;
            
            $decoded = json_decode($item['uploaded_files'], true);
            if (!is_array($decoded)) continue;
            
            $files = $decoded['files'] ?? (isset($decoded[0]) ? $decoded : []);
            
            foreach ($files as $file) {
                if (isset($file['path'])) {
                    $srcPath = $_SERVER['DOCUMENT_ROOT'] . $file['path'];
                    if (file_exists($srcPath)) {
                        $destPath = $destDir . ($file['name'] ?? basename($file['path']));
                        copy($srcPath, $destPath);
                    }
                }
            }
        }
    }
    
    private function clearCart(string $sessionId): void
    {
        $db = Database::getInstance();
        $db->delete('shop_temp', 'session_id = :session_id', ['session_id' => $sessionId]);
    }
    
    private function sendOrderNotification(int $orderNo, array $orderData, array $items): void
    {
        $adminEmail = 'dsp1830@naver.com';
        $subject = "[두손기획인쇄] 새 주문 #{$orderNo}";
        
        $body = "새로운 주문이 접수되었습니다.\n\n";
        $body .= "주문번호: {$orderNo}\n";
        $body .= "주문자: {$orderData['name']}\n";
        $body .= "연락처: {$orderData['phone']}\n";
        $body .= "이메일: {$orderData['email']}\n";
        $body .= "주소: {$orderData['zip1']} {$orderData['zip2']}\n";
        $body .= "결제금액: " . number_format($orderData['money_2']) . "원\n\n";
        $body .= "주문 상세:\n";
        $body .= $orderData['Type_1'];
        
        $headers = "From: noreply@dsp1830.shop\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        @mail($adminEmail, $subject, $body, $headers);
    }
}
