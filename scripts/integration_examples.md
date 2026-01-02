# PDF 견적서 생성 시스템 통합 가이드

## 📍 통합 가능 지점 4가지

### 1️⃣ 주문 완료 후 자동 생성 (추천 ⭐)

**파일**: `mlangorder_printauto/OrderComplete_universal.php`
**위치**: 주문 정보 조회 후, 화면 표시 전

```php
// 574번 줄 근처 - 주문 정보 조회 루프 끝에 추가
foreach ($order_numbers as $order_no) {
    $order_no = trim($order_no);
    if (!empty($order_no)) {
        $query = "SELECT * FROM mlangorder_printauto WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($connect, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($order = mysqli_fetch_assoc($result)) {
                $orders[] = $order;

                // 🆕 PDF 견적서 자동 생성
                generateQuotationPDF($order_no);
            }
        }
    }
}

// 🆕 PDF 생성 함수 추가 (파일 상단)
function generateQuotationPDF($order_no) {
    // 입력 검증 (보안)
    if (!preg_match('/^[0-9]+$/', $order_no)) {
        error_log("Invalid order_no format: " . $order_no);
        return false;
    }

    // 1. 주문 데이터 JSON 생성
    $php_script = "/var/www/html/scripts/get_order_data.php";
    $json_output = "/tmp/order_data_" . escapeshellarg($order_no) . ".json";

    // escapeshellcmd/escapeshellarg로 보안 강화
    $cmd = sprintf(
        "php %s > %s",
        escapeshellarg($php_script),
        escapeshellarg($json_output)
    );
    shell_exec($cmd);

    // 2. Python으로 PDF 생성
    $python_script = "/var/www/html/scripts/generate_quotation_from_db.py";
    $pdf_output = "/var/www/html/docs/quotation_" . escapeshellarg($order_no) . ".pdf";
    $venv_python = "/tmp/pdf_venv/bin/python3";

    $cmd = sprintf(
        "%s %s %s %s 2>&1",
        escapeshellarg($venv_python),
        escapeshellarg($python_script),
        escapeshellarg($json_output),
        escapeshellarg($pdf_output)
    );
    $output = shell_exec($cmd);

    // 3. 임시 파일 삭제
    if (file_exists($json_output)) {
        unlink($json_output);
    }

    return file_exists($pdf_output) ? $pdf_output : false;
}
```

**장점**:
- ✅ 주문 완료 시 자동으로 PDF 생성됨
- ✅ 별도 작업 없이 즉시 사용 가능
- ✅ 모든 주문에 대해 일관되게 생성

---

### 2️⃣ 관리자 페이지에서 수동 생성

**새 파일 생성**: `admin/mlangprintauto/generate_quotation.php`

```php
<?php
/**
 * 관리자용 견적서 PDF 생성 페이지
 */
session_start();
include "../../db.php";
include "../../includes/auth.php";

// 관리자 권한 확인
if (!isset($_SESSION['AdminId'])) {
    die("권한이 없습니다.");
}

$order_no = $_GET['order_no'] ?? '';

// 입력 검증 (보안)
if (!preg_match('/^[0-9]+$/', $order_no)) {
    die("잘못된 주문번호 형식입니다.");
}

// 주문 데이터 조회
$order_query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $order_query);
mysqli_stmt_bind_param($stmt, 's', $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("주문을 찾을 수 없습니다: " . htmlspecialchars($order_no));
}

// JSON 파일 생성 (안전한 경로)
$json_file = sys_get_temp_dir() . "/order_data_" . $order_no . ".json";
file_put_contents($json_file, json_encode($order, JSON_UNESCAPED_UNICODE));

// Python으로 PDF 생성 (escapeshellarg 사용)
$python_script = "/var/www/html/scripts/generate_quotation_from_db.py";
$pdf_output = "/var/www/html/docs/quotation_" . $order_no . ".pdf";
$venv_python = "/tmp/pdf_venv/bin/python3";

$cmd = sprintf(
    "%s %s %s %s 2>&1",
    escapeshellarg($venv_python),
    escapeshellarg($python_script),
    escapeshellarg($json_file),
    escapeshellarg($pdf_output)
);
exec($cmd, $output, $return_code);

// 임시 파일 삭제
if (file_exists($json_file)) {
    unlink($json_file);
}

// PDF 다운로드
if ($return_code === 0 && file_exists($pdf_output)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="quotation_' . $order_no . '.pdf"');
    header('Content-Length: ' . filesize($pdf_output));
    readfile($pdf_output);
    exit;
} else {
    echo "PDF 생성 실패:<br>";
    echo htmlspecialchars(implode("\n", $output));
}
?>
```

**관리자 페이지에 버튼 추가** (`admin/mlangprintauto/admin.php`):

```php
<!-- 주문 목록 테이블에 버튼 추가 -->
<td>
    <a href="generate_quotation.php?order_no=<?php echo htmlspecialchars($row['no']); ?>"
       class="btn btn-sm btn-primary"
       target="_blank">
        📄 견적서 생성
    </a>
</td>
```

**장점**:
- ✅ 관리자가 필요할 때만 생성
- ✅ 주문 조회 화면에서 바로 다운로드
- ✅ 서버 리소스 절약

---

### 3️⃣ 이메일 첨부용

**파일**: `mlangorder_printauto/send_order_email.php` (새로 생성)

```php
<?php
/**
 * 주문 확인 이메일 + 견적서 PDF 첨부
 */
require_once "../db.php";
require_once "PHPMailer/PHPMailerAutoload.php";

function sendOrderEmailWithQuotation($order_no) {
    global $db;

    // 입력 검증
    if (!preg_match('/^[0-9]+$/', $order_no)) {
        error_log("Invalid order_no: " . $order_no);
        return false;
    }

    // 1. 주문 정보 조회
    $query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 's', $order_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);

    if (!$order) return false;

    // 2. PDF 견적서 생성
    $json_file = sys_get_temp_dir() . "/order_data_" . $order_no . ".json";
    file_put_contents($json_file, json_encode($order, JSON_UNESCAPED_UNICODE));

    $python_script = "/var/www/html/scripts/generate_quotation_from_db.py";
    $pdf_output = sys_get_temp_dir() . "/quotation_" . $order_no . ".pdf";
    $venv_python = "/tmp/pdf_venv/bin/python3";

    $cmd = sprintf(
        "%s %s %s %s 2>&1",
        escapeshellarg($venv_python),
        escapeshellarg($python_script),
        escapeshellarg($json_file),
        escapeshellarg($pdf_output)
    );
    exec($cmd, $output, $return_code);

    if (file_exists($json_file)) {
        unlink($json_file);
    }

    if ($return_code !== 0 || !file_exists($pdf_output)) {
        error_log("PDF generation failed: " . implode("\n", $output));
        return false;
    }

    // 3. 이메일 발송
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'du1830@dsp1830.shop';
    $mail->Password = 'your_password';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('du1830@dsp1830.shop', '두손기획인쇄');
    $mail->addAddress($order['email'], $order['name']);

    $mail->Subject = '[두손기획인쇄] 주문확인 및 견적서 - 주문번호 #' . $order_no;
    $mail->Body = "
안녕하세요, {$order['name']}님

주문해주셔서 감사합니다.
주문번호: #{$order_no}
주문일시: {$order['date']}

첨부된 견적서를 확인해주세요.

감사합니다.
두손기획인쇄
02-2632-1830
    ";

    // 4. PDF 첨부
    $mail->addAttachment($pdf_output, 'quotation_' . $order_no . '.pdf');

    $result = $mail->send();

    // 5. 임시 PDF 삭제
    if (file_exists($pdf_output)) {
        unlink($pdf_output);
    }

    return $result;
}

// 사용 예시
if (isset($_GET['order_no'])) {
    $order_no = $_GET['order_no'];
    if (preg_match('/^[0-9]+$/', $order_no)) {
        $success = sendOrderEmailWithQuotation($order_no);
        echo $success ? "이메일 발송 완료" : "이메일 발송 실패";
    } else {
        echo "잘못된 주문번호 형식";
    }
}
?>
```

**ProcessOrder_unified.php에서 호출**:

```php
// 주문 처리 완료 후
if ($order_success) {
    require_once "send_order_email.php";
    sendOrderEmailWithQuotation($order_no);
}
```

**장점**:
- ✅ 고객에게 자동으로 견적서 전송
- ✅ 주문 확인 + 견적서 한번에 제공
- ✅ 고객 만족도 향상

---

### 4️⃣ API 엔드포인트 (독립 호출)

**새 파일**: `api/generate_quotation_api.php`

```php
<?php
/**
 * 견적서 PDF 생성 API
 *
 * 사용법:
 * GET /api/generate_quotation_api.php?order_no=83999&download=1
 *
 * 응답:
 * - download=1: PDF 파일 다운로드
 * - download=0: JSON 결과 반환
 */
header('Content-Type: application/json; charset=UTF-8');
require_once "../db.php";

$order_no = $_GET['order_no'] ?? '';
$download = isset($_GET['download']) && $_GET['download'] == '1';

// 입력 검증
if (!preg_match('/^[0-9]+$/', $order_no)) {
    echo json_encode([
        'success' => false,
        'error' => '잘못된 주문번호 형식입니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 주문 데이터 조회
$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 's', $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($stmt);

if (!$order) {
    echo json_encode([
        'success' => false,
        'error' => '주문을 찾을 수 없습니다: ' . $order_no
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// JSON 파일 생성
$json_file = sys_get_temp_dir() . "/order_data_" . $order_no . ".json";
file_put_contents($json_file, json_encode($order, JSON_UNESCAPED_UNICODE));

// Python으로 PDF 생성
$python_script = "/var/www/html/scripts/generate_quotation_from_db.py";
$pdf_output = sys_get_temp_dir() . "/quotation_" . $order_no . ".pdf";
$venv_python = "/tmp/pdf_venv/bin/python3";

$cmd = sprintf(
    "%s %s %s %s 2>&1",
    escapeshellarg($venv_python),
    escapeshellarg($python_script),
    escapeshellarg($json_file),
    escapeshellarg($pdf_output)
);
exec($cmd, $output, $return_code);

if (file_exists($json_file)) {
    unlink($json_file);
}

if ($return_code !== 0 || !file_exists($pdf_output)) {
    echo json_encode([
        'success' => false,
        'error' => 'PDF 생성 실패',
        'output' => $output
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 다운로드 모드
if ($download) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="quotation_' . $order_no . '.pdf"');
    header('Content-Length: ' . filesize($pdf_output));
    readfile($pdf_output);
    if (file_exists($pdf_output)) {
        unlink($pdf_output);
    }
    exit;
}

// JSON 응답 모드
$pdf_data = base64_encode(file_get_contents($pdf_output));
if (file_exists($pdf_output)) {
    unlink($pdf_output);
}

echo json_encode([
    'success' => true,
    'order_no' => $order_no,
    'pdf_file' => $pdf_output,
    'pdf_data_base64' => $pdf_data,
    'file_size' => strlen($pdf_data)
], JSON_UNESCAPED_UNICODE);
?>
```

**JavaScript에서 호출 예시**:

```javascript
// 견적서 다운로드
function downloadQuotation(orderNo) {
    // 숫자만 허용 (XSS 방지)
    if (!/^[0-9]+$/.test(orderNo)) {
        alert('잘못된 주문번호입니다.');
        return;
    }
    window.open(`/api/generate_quotation_api.php?order_no=${orderNo}&download=1`, '_blank');
}

// AJAX로 PDF 데이터 가져오기
async function getQuotationPDF(orderNo) {
    if (!/^[0-9]+$/.test(orderNo)) {
        alert('잘못된 주문번호입니다.');
        return;
    }

    const response = await fetch(`/api/generate_quotation_api.php?order_no=${orderNo}`);
    const data = await response.json();

    if (data.success) {
        // Base64 PDF 데이터 사용
        const pdfBlob = base64ToBlob(data.pdf_data_base64, 'application/pdf');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        window.open(pdfUrl);
    }
}
```

**장점**:
- ✅ 어디서든 호출 가능 (JavaScript, 다른 시스템)
- ✅ RESTful API 패턴
- ✅ 다운로드/데이터 모드 선택 가능

---

## 🔒 보안 고려사항

### 모든 예제에 적용된 보안 조치:

1. **입력 검증**: `preg_match('/^[0-9]+$/', $order_no)` - 숫자만 허용
2. **Command Injection 방지**: `escapeshellarg()` 사용
3. **SQL Injection 방지**: Prepared statements 사용
4. **XSS 방지**: `htmlspecialchars()` 출력 시 적용
5. **권한 확인**: 관리자 페이지는 `$_SESSION['AdminId']` 체크
6. **임시 파일 관리**: `sys_get_temp_dir()` 사용, 완료 후 즉시 삭제
7. **에러 로깅**: `error_log()` 사용, 사용자에게 상세 에러 노출 안 함

---

## 🚀 권장 통합 방법

### 단계별 구현 순서:

1. **1단계**: API 엔드포인트 먼저 구현 (4️⃣)
   - 독립적으로 테스트 가능
   - 다른 통합 방법의 기반이 됨

2. **2단계**: 관리자 페이지에 버튼 추가 (2️⃣)
   - 관리자가 수동으로 테스트
   - 생성 로직 검증

3. **3단계**: 주문 완료 시 자동 생성 (1️⃣)
   - 프로덕션 환경 적용
   - 모든 주문에 자동 적용

4. **4단계**: 이메일 첨부 기능 추가 (3️⃣)
   - 고객 서비스 개선
   - 자동화 완성

---

## 📁 파일 저장 위치

### 현재 구조:
```
/var/www/html/
├── scripts/
│   ├── get_order_data.php          # DB 조회
│   └── generate_quotation_from_db.py  # PDF 생성
├── docs/
│   └── quotation_XXXXX.pdf         # 생성된 PDF
└── api/
    └── generate_quotation_api.php  # API 엔드포인트
```

### 추천 구조 (리팩토링):
```
/var/www/html/
├── includes/
│   └── QuotationGenerator.php      # 통합 클래스
├── api/
│   └── quotation/
│       ├── generate.php            # PDF 생성 API
│       └── download.php            # PDF 다운로드 API
└── storage/
    └── quotations/
        └── 2025/
            └── 01/
                └── quotation_83999.pdf
```

---

## 🔧 테스트 방법

```bash
# 1. API 테스트
curl "http://localhost/api/generate_quotation_api.php?order_no=83999&download=1" -o test.pdf

# 2. 관리자 페이지 테스트
http://localhost/admin/mlangprintauto/generate_quotation.php?order_no=83999

# 3. 이메일 테스트
http://localhost/mlangorder_printauto/send_order_email.php?order_no=83999
```

---

## 💡 개선 아이디어

1. **PDF 캐싱**: 같은 주문번호는 재생성 안 하고 캐시 사용
2. **비동기 처리**: 큐 시스템으로 백그라운드 생성
3. **템플릿 시스템**: 여러 견적서 템플릿 선택 가능
4. **다국어 지원**: 영문/한글 견적서 선택
5. **전자서명**: PDF에 디지털 서명 추가
6. **로그 시스템**: PDF 생성 이력 추적

---

**작성일**: 2025-12-28
**문서 위치**: `/var/www/html/scripts/integration_examples.md`
**보안 업데이트**: Command injection 방지 강화
