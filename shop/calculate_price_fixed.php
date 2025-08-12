<?php
// 수정된 안전한 버전의 가격 계산

// 출력 버퍼링 시작
ob_start();

// 오류 출력 제어
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

try {
    // POST 요청 확인
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST 요청만 허용됩니다.');
    }
    
    if (!isset($_POST['action']) || $_POST['action'] !== 'calculate') {
        throw new Exception('잘못된 요청입니다.');
    }
    
    // 입력값 받기 및 검증
    $jong = trim($_POST['jong'] ?? '');
    $garo = (int)($_POST['garo'] ?? 0);
    $sero = (int)($_POST['sero'] ?? 0);
    $mesu = (int)($_POST['mesu'] ?? 0);
    $uhyung = (int)($_POST['uhyung'] ?? 0);
    $domusong = trim($_POST['domusong'] ?? '');
    
    // 필수값 검증
    if (empty($jong)) throw new Exception('재질을 선택하세요');
    if ($garo <= 0) throw new Exception('가로사이즈를 입력하세요');
    if ($sero <= 0) throw new Exception('세로사이즈를 입력하세요');
    if ($mesu <= 0) throw new Exception('수량을 입력하세요');
    
    // 범위 검증
    if ($garo > 590) throw new Exception('가로사이즈를 590mm이하만 입력할 수 있습니다');
    if ($sero > 590) throw new Exception('세로사이즈를 590mm이하만 입력할 수 있습니다');
    if (($garo * $sero) > 250000 && $mesu > 5000) {
        throw new Exception('500mm이상 대형사이즈를 5000매이상 주문은 전화요청바랍니다');
    }
    if ($mesu > 10000) throw new Exception('1만매 이상은 할인가 적용-전화주시기바랍니다');
    
    // 도무송 강제 선택 검증
    if (($garo < 50 || $sero < 60) && ($garo < 60 || $sero < 50) && $domusong == '00000 사각') {
        throw new Exception('가로,세로사이즈가 50mmx60mm 미만일 경우, 도무송을 선택해야 합니다.');
    }
    
    // 재질별 제한 검증
    $j = substr($jong, 4, 10);
    if ($j == '금지스티커') throw new Exception('금지스티커는 전화 또는 메일로 견적 문의하세요');
    if ($j == '금박스티커') throw new Exception('금박스티커는 전화 또는 메일로 견적 문의하세요');
    if ($j == '롤형스티커') throw new Exception('롤스티커는 전화 또는 메일로 견적 문의하세요');
    
    // 데이터베이스 연결
    include "../lib/func.php";
    $connect = dbconn();
    
    if (!$connect) {
        throw new Exception('데이터베이스 연결에 실패했습니다.');
    }
    
    // 가격 계산
    $result = calculatePrice($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect);
    
    // 세션에 주문 정보 저장
    $_SESSION['temp_order'] = [
        'jong' => $jong,
        'garo' => $garo,
        'sero' => $sero,
        'mesu' => $mesu,
        'uhyung' => $uhyung,
        'domusong' => $domusong,
        'price' => $result['st_price'],
        'price_vat' => $result['st_price_vat'],
        'created_at' => time()
    ];
    
    // 성공 응답
    $response = [
        'success' => true,
        'price' => number_format($result['st_price']),
        'price_vat' => number_format($result['st_price_vat'])
    ];
    
} catch (Exception $e) {
    // 오류 응답
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// 데이터베이스 연결 종료
if (isset($connect) && $connect) {
    mysqli_close($connect);
}

// 출력 버퍼 정리 후 JSON 출력
ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
ob_end_flush();

// 가격 계산 함수
function calculatePrice($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect) {
    // 재질 코드 추출
    $j1 = substr($jong, 0, 3);
    $j = substr($jong, 4, 10);
    
    // 도무송 정보 추출
    $d1 = (int)substr($domusong, 0, 5);
    
    // 기본값 설정
    $yoyo = 0.15; // 기본 요율
    $mg = 7000;   // 기본 비용
    $ts = 9;      // 기본 톰슨비용
    
    // 재질별 데이터베이스 조회
    $table_map = [
        'jil' => 'shop_d1',
        'jka' => 'shop_d2', 
        'jsp' => 'shop_d3',
        'cka' => 'shop_d4'
    ];
    
    if (isset($table_map[$j1])) {
        $query = "SELECT * FROM {$table_map[$j1]} LIMIT 1";
        $result = mysqli_query($connect, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_array($result);
            
            // 수량별 요율 및 기본비용 설정
            if ($mesu <= 1000) {
                $yoyo = $data[0] ?? 0.15;
                $mg = 7000;
            } else if ($mesu <= 4000) {
                $yoyo = $data[1] ?? 0.14;
                $mg = 6500;
            } else if ($mesu <= 5000) {
                $yoyo = $data[2] ?? 0.13;
                $mg = 6500;
            } else if ($mesu <= 9000) {
                $yoyo = $data[3] ?? 0.12;
                $mg = 6000;
            } else if ($mesu <= 10000) {
                $yoyo = $data[4] ?? 0.11;
                $mg = 5500;
            } else if ($mesu <= 50000) {
                $yoyo = $data[5] ?? 0.10;
                $mg = 5000;
            } else {
                $yoyo = $data[6] ?? 0.09;
                $mg = 5000;
            }
        }
    }
    
    // 재질별 톰슨비용
    if ($j1 == 'jsp' || $j1 == 'jka' || $j1 == 'cka') {
        $ts = 14;
    }
    
    // 도무송칼 크기 계산
    $d2 = max($garo, $sero);
    
    // 사이즈별 마진비율
    $gase = ($garo * $sero <= 18000) ? 1 : 1.25;
    
    // 도무송 비용 계산
    $d1_cost = 0;
    if ($d1 > 0) {
        if ($mesu == 500) {
            $d1_cost = (($d1 + ($d2 * 20)) * 900 / 1000) + (900 * $ts);
        } else if ($mesu == 1000) {
            $d1_cost = (($d1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * $ts);
        } else if ($mesu > 1000) {
            $d1_cost = (($d1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * ($ts / 9));
        }
    }
    
    // 특수용지 비용
    $jsp = 0;
    $jka = 0;
    $cka = 0;
    
    if ($j1 == 'jsp') {
        if ($mesu == 500) {
            $jsp = 10000 * ($mesu + 400) / 1000;
        } else if ($mesu > 500) {
            $jsp = 10000 * $mesu / 1000;
        }
    }
    
    if ($j1 == 'jka') {
        if ($mesu == 500) {
            $jka = 4000 * ($mesu + 400) / 1000;
        } else if ($mesu > 500) {
            $jka = 10000 * $mesu / 1000;
        }
    }
    
    if ($j1 == 'cka') {
        if ($mesu == 500) {
            $cka = 4000 * ($mesu + 400) / 1000;
        } else if ($mesu > 500) {
            $cka = 10000 * $mesu / 1000;
        }
    }
    
    // 최종 가격 계산
    if ($mesu == 500) {
        $s_price = (($garo + 4) * ($sero + 4) * ($mesu + 400)) * $yoyo + $jsp + $jka + $cka + $d1_cost;
        $st_price = round($s_price * $gase, -3) + $uhyung + ($mg * ($mesu + 400) / 1000);
    } else {
        $s_price = (($garo + 4) * ($sero + 4) * $mesu) * $yoyo + $jsp + $jka + $cka + $d1_cost;
        $st_price = round($s_price * $gase, -3) + $uhyung + ($mg * $mesu / 1000);
    }
    
    $st_price_vat = $st_price * 1.1;
    
    return [
        'st_price' => $st_price,
        'st_price_vat' => $st_price_vat
    ];
}
?>