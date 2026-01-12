<?php
/**
 * DeliveryTrackingService
 *
 * 배송 추적 통합 서비스 - 스마트택배 API
 *
 * @package DusonPrint
 * @since 2026-01-13
 */

class DeliveryTrackingService
{
    private $db;
    private $config;
    private $lastError;

    const STATUS_READY = 'ready';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';

    const COURIER_CJ = 'cj';
    const COURIER_HANJIN = 'hanjin';
    const COURIER_LOTTE = 'lotte';
    const COURIER_LOGEN = 'logen';
    const COURIER_POST = 'post';

    public function __construct($db)
    {
        $this->db = $db;
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $this->config = [
            'smarttaekbae' => [
                'api_key' => getenv('SMARTTAEKBAE_API_KEY') ?: '',
                'api_url' => 'https://info.sweettracker.co.kr/api/v1'
            ],
            'couriers' => [
                self::COURIER_CJ => ['name' => 'CJ대한통운', 'code' => '04', 'tracking_url' => 'https://www.cjlogistics.com/ko/tool/parcel/tracking?gnbInvcNo='],
                self::COURIER_HANJIN => ['name' => '한진택배', 'code' => '05', 'tracking_url' => 'https://www.hanjin.com/kor/CMS/DeliveryMgr/WaybillResult.do?mession=&wblnumText2='],
                self::COURIER_LOTTE => ['name' => '롯데택배', 'code' => '08', 'tracking_url' => 'https://www.lotteglogis.com/home/reservation/tracking/linkView?InvNo='],
                self::COURIER_LOGEN => ['name' => '로젠택배', 'code' => '06', 'tracking_url' => 'https://www.ilogen.com/web/personal/trace/'],
                self::COURIER_POST => ['name' => '우체국택배', 'code' => '01', 'tracking_url' => 'https://service.epost.go.kr/trace.RetrieveDomRi498.postal?sid1=']
            ],
            'cache' => ['enabled' => true, 'ttl' => 600]
        ];
    }

    public function getLastError() { return $this->lastError; }

    public function registerShipping($orderId, $courierCode, $trackingNumber)
    {
        try {
            if (!isset($this->config['couriers'][$courierCode])) {
                throw new Exception('지원하지 않는 택배사입니다.');
            }
            $trackingNumber = preg_replace('/[^0-9]/', '', $trackingNumber);
            if (empty($trackingNumber)) {
                throw new Exception('유효하지 않은 운송장 번호입니다.');
            }

            $existing = $this->getShippingByOrder($orderId);
            if ($existing) {
                $query = "UPDATE shipping_info SET courier_code = ?, tracking_number = ?, status = ?, updated_at = NOW() WHERE order_id = ?";
                $stmt = mysqli_prepare($this->db, $query);
                $status = self::STATUS_READY;
                mysqli_stmt_bind_param($stmt, 'sssi', $courierCode, $trackingNumber, $status, $orderId);
            } else {
                $query = "INSERT INTO shipping_info (order_id, courier_code, tracking_number, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
                $stmt = mysqli_prepare($this->db, $query);
                $status = self::STATUS_READY;
                mysqli_stmt_bind_param($stmt, 'isss', $orderId, $courierCode, $trackingNumber, $status);
            }

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('배송 정보 저장 실패');
            }

            $this->updateOrderShippingInfo($orderId, $courierCode, $trackingNumber);

            return [
                'success' => true,
                'order_id' => $orderId,
                'courier' => $this->config['couriers'][$courierCode]['name'],
                'tracking_number' => $trackingNumber,
                'tracking_url' => $this->getTrackingUrl($courierCode, $trackingNumber)
            ];
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function trackShipment($courierCode, $trackingNumber)
    {
        try {
            $trackingNumber = preg_replace('/[^0-9]/', '', $trackingNumber);
            $cacheKey = "track_{$courierCode}_{$trackingNumber}";
            $cached = $this->getFromCache($cacheKey);
            if ($cached) return $cached;

            $result = $this->callSmartTaekbaeAPI($courierCode, $trackingNumber);
            if ($result) {
                $this->saveToCache($cacheKey, $result);
                return $result;
            }
            throw new Exception('배송 조회 실패');
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function trackOrderShipment($orderId)
    {
        $shipping = $this->getShippingByOrder($orderId);
        if (!$shipping) {
            $this->lastError = '배송 정보가 없습니다.';
            return false;
        }
        $result = $this->trackShipment($shipping['courier_code'], $shipping['tracking_number']);
        if ($result) {
            $this->updateShippingStatus($orderId, $result['status']);
        }
        return $result;
    }

    private function callSmartTaekbaeAPI($courierCode, $trackingNumber)
    {
        $config = $this->config['smarttaekbae'];
        if (empty($config['api_key'])) {
            return $this->getBasicTrackingInfo($courierCode, $trackingNumber);
        }

        $companyCode = $this->config['couriers'][$courierCode]['code'] ?? '';
        $url = $config['api_url'] . '/trackingInfo?' . http_build_query([
            't_key' => $config['api_key'],
            't_code' => $companyCode,
            't_invoice' => $trackingNumber
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            throw new Exception('API 호출 실패');
        }

        $data = json_decode($response, true);
        if (!$data || isset($data['code'])) {
            throw new Exception($data['msg'] ?? '배송 조회 실패');
        }

        return $this->parseSmartTaekbaeResponse($data, $courierCode, $trackingNumber);
    }

    private function parseSmartTaekbaeResponse($data, $courierCode, $trackingNumber)
    {
        $status = $this->mapDeliveryStatus($data['lastStateDetail']['kind'] ?? '');
        $trackingDetails = [];
        foreach ($data['trackingDetails'] ?? [] as $detail) {
            $trackingDetails[] = [
                'time' => $detail['timeString'] ?? '',
                'location' => $detail['where'] ?? '',
                'status' => $detail['kind'] ?? '',
                'description' => $detail['telno'] ?? ''
            ];
        }

        return [
            'success' => true,
            'courier' => $this->config['couriers'][$courierCode]['name'],
            'courier_code' => $courierCode,
            'tracking_number' => $trackingNumber,
            'tracking_url' => $this->getTrackingUrl($courierCode, $trackingNumber),
            'status' => $status,
            'status_text' => $this->getStatusText($status),
            'sender' => $data['senderName'] ?? '',
            'receiver' => $data['receiverName'] ?? '',
            'last_update' => $data['lastStateDetail']['timeString'] ?? '',
            'last_location' => $data['lastStateDetail']['where'] ?? '',
            'tracking_details' => $trackingDetails,
            'is_complete' => ($status === self::STATUS_DELIVERED)
        ];
    }

    private function getBasicTrackingInfo($courierCode, $trackingNumber)
    {
        return [
            'success' => true,
            'courier' => $this->config['couriers'][$courierCode]['name'] ?? '택배사',
            'courier_code' => $courierCode,
            'tracking_number' => $trackingNumber,
            'tracking_url' => $this->getTrackingUrl($courierCode, $trackingNumber),
            'status' => self::STATUS_IN_TRANSIT,
            'status_text' => '배송 중',
            'tracking_details' => [],
            'is_complete' => false,
            'message' => '상세 배송 조회는 택배사 사이트에서 확인해주세요.'
        ];
    }

    private function mapDeliveryStatus($kindCode)
    {
        $statusMap = [
            '01' => self::STATUS_PICKED_UP,
            '02' => self::STATUS_IN_TRANSIT,
            '03' => self::STATUS_IN_TRANSIT,
            '04' => self::STATUS_OUT_FOR_DELIVERY,
            '05' => self::STATUS_DELIVERED,
            '06' => self::STATUS_IN_TRANSIT
        ];
        return $statusMap[$kindCode] ?? self::STATUS_IN_TRANSIT;
    }

    private function getStatusText($status)
    {
        $texts = [
            self::STATUS_READY => '배송 준비중',
            self::STATUS_PICKED_UP => '집화 완료',
            self::STATUS_IN_TRANSIT => '배송 중',
            self::STATUS_OUT_FOR_DELIVERY => '배송 출발',
            self::STATUS_DELIVERED => '배송 완료',
            self::STATUS_FAILED => '배송 실패'
        ];
        return $texts[$status] ?? '알 수 없음';
    }

    public function getTrackingUrl($courierCode, $trackingNumber)
    {
        $courier = $this->config['couriers'][$courierCode] ?? null;
        if (!$courier) return 'https://tracker.delivery/#/' . $courierCode . '/' . $trackingNumber;
        return $courier['tracking_url'] . $trackingNumber;
    }

    public function getShippingByOrder($orderId)
    {
        $query = "SELECT * FROM shipping_info WHERE order_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    private function updateShippingStatus($orderId, $status)
    {
        $query = "UPDATE shipping_info SET status = ?, updated_at = NOW() WHERE order_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $orderId);
        mysqli_stmt_execute($stmt);

        if ($status === self::STATUS_DELIVERED) {
            $this->updateOrderDeliveryComplete($orderId);
        }
    }

    private function updateOrderShippingInfo($orderId, $courierCode, $trackingNumber)
    {
        $courierName = $this->config['couriers'][$courierCode]['name'] ?? $courierCode;
        $query = "UPDATE mlangorder_printauto SET courier = ?, tracking_number = ?, ship_status = 'shipped' WHERE no = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $courierName, $trackingNumber, $orderId);
        return mysqli_stmt_execute($stmt);
    }

    private function updateOrderDeliveryComplete($orderId)
    {
        $query = "UPDATE mlangorder_printauto SET ship_status = 'delivered', delivery_date = NOW() WHERE no = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        return mysqli_stmt_execute($stmt);
    }

    private function getFromCache($key)
    {
        if (!$this->config['cache']['enabled']) return null;
        $query = "SELECT data, created_at FROM tracking_cache WHERE cache_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        $stmt = mysqli_prepare($this->db, $query);
        $ttl = $this->config['cache']['ttl'];
        mysqli_stmt_bind_param($stmt, 'si', $key, $ttl);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        if ($row) return json_decode($row['data'], true);
        return null;
    }

    private function saveToCache($key, $data)
    {
        if (!$this->config['cache']['enabled']) return;
        $query = "REPLACE INTO tracking_cache (cache_key, data, created_at) VALUES (?, ?, NOW())";
        $stmt = mysqli_prepare($this->db, $query);
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);
        mysqli_stmt_bind_param($stmt, 'ss', $key, $dataJson);
        mysqli_stmt_execute($stmt);
    }

    public function getCourierList()
    {
        $list = [];
        foreach ($this->config['couriers'] as $code => $info) {
            $list[] = ['code' => $code, 'name' => $info['name']];
        }
        return $list;
    }

    public function batchUpdateTrackingStatus()
    {
        $query = "SELECT * FROM shipping_info WHERE status NOT IN (?, ?) ORDER BY created_at ASC";
        $stmt = mysqli_prepare($this->db, $query);
        $delivered = self::STATUS_DELIVERED;
        $failed = self::STATUS_FAILED;
        mysqli_stmt_bind_param($stmt, 'ss', $delivered, $failed);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $updated = 0;
        while ($shipment = mysqli_fetch_assoc($result)) {
            $trackResult = $this->trackShipment($shipment['courier_code'], $shipment['tracking_number']);
            if ($trackResult && $trackResult['status'] !== $shipment['status']) {
                $this->updateShippingStatus($shipment['order_id'], $trackResult['status']);
                $updated++;
            }
            usleep(300000);
        }
        return ['updated' => $updated];
    }

    public static function getCreateTableSQL()
    {
        return "
        CREATE TABLE IF NOT EXISTS `shipping_info` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `courier_code` VARCHAR(20) NOT NULL,
            `tracking_number` VARCHAR(50) NOT NULL,
            `status` VARCHAR(30) NOT NULL DEFAULT 'ready',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_order` (`order_id`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `tracking_cache` (
            `cache_key` VARCHAR(100) NOT NULL,
            `data` TEXT NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`cache_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }
}
