# MlangPrintAuto - msticker 품목 파일 업로드 시스템 통합 보고서

## 📅 작성일: 2025년 8월 5일

## 🎯 목적
`msticker` 품목 페이지의 파일 업로드 시스템을 기존 자체 구현 방식에서 `공통 파일 업로드 시스템 설계서`에 명시된 공통 컴포넌트(`includes/file_upload_component.php`) 방식으로 통합하고, 업로드된 파일 정보가 장바구니(`shop_temp`) 테이블에 체계적으로 저장되도록 개선합니다. 최종적으로 주문 시스템(`MlangOrder_PrintAuto`)과 연동을 위한 기반을 마련합니다.

## 🔍 변경 전 시스템 분석

### `MlangPrintAuto/msticker/index.php`
*   파일 업로드를 위한 자체적인 HTML(`id="fileDropZone"`, `id="fileInput"`, `id="uploadedFilesList"`) 및 JavaScript(`selectedFiles`, `handleFiles`, `updateFileList` 등) 로직을 포함하고 있었습니다.
*   동시에 `../../includes/file_upload_component.php`를 `include`하고 `renderFileUploadComponent()`를 호출하여 공통 컴포넌트도 사용하려 시도하고 있었습니다.
*   이러한 중복된 구현으로 인해 파일 목록이 제대로 표시되지 않는 등의 충돌 문제가 발생할 수 있었습니다.

### `MlangPrintAuto/msticker/add_to_basket.php`
*   장바구니 추가 시 `$_FILES` 슈퍼글로벌을 통해 파일을 직접 업로드 처리하는 로직을 포함하고 있었습니다.
*   `shop_temp` 테이블에 `MY_comment`를 비롯한 일부 파일 관련 컬럼이 동적으로 추가되지 않거나, `index.php`에서 전달되는 `uploaded_files_info` JSON 데이터를 처리할 준비가 되어 있지 않았습니다.

## 🛠️ 변경된 시스템 설계 및 구현 내용

### 1. `MlangPrintAuto/msticker/index.php` 수정
*   **자체 구현 코드 제거:** 625행부터 641행까지의 자체 파일 업로드 섹션 HTML (`<div class="file-upload-section">`)과 822행부터 913행까지의 자체 JavaScript 파일 업로드 관련 로직을 완전히 제거했습니다.
*   **공통 컴포넌트 통합:**
    *   `<?php include "../../includes/file_upload_component.php"; ?>` 및 `<?php renderFileUploadComponent(...) ?>` 호출을 그대로 유지하여, 공통 컴포넌트가 `msticker` 페이지의 파일 업로드 UI/UX를 담당하도록 했습니다.
    *   `renderFileUploadComponent` 함수 호출 시 `title`, `description`, `max_files`, `allowed_types` 등의 옵션을 명시적으로 전달하여 페이지 특성에 맞는 설정을 적용했습니다.
*   **`addToBasket()` 함수 수정:**
    *   기존 `selectedFiles` 배열 대신 공통 컴포넌트에서 관리하는 `window.uploadedFiles` 전역 변수를 참조하도록 변경했습니다.
    *   `window.uploadedFiles`에 담긴 각 파일의 메타데이터(원본 이름, 저장된 이름, 업로드 경로, 크기, 타입 등)를 추출하여 JSON 문자열(`uploaded_files_info`)로 변환 후 `FormData`에 담아 `add_to_basket.php`로 전송하도록 수정했습니다.
    *   장바구니 추가 성공 시, 사용자에게 메시지를 알리고 `/MlangPrintAuto/cart.php`로 리디렉션하도록 로직을 개선했습니다.

### 2. `MlangPrintAuto/msticker/add_to_basket.php` 수정
*   **`shop_temp` 테이블 컬럼 동적 추가:**
    *   `session_id`, `product_type` 등 기본 컬럼 외에, 파일 업로드 및 기타 사항 저장을 위해 `img` (TEXT), `file_path` (VARCHAR(500)), `file_info` (TEXT), `upload_log` (TEXT), `MY_comment` (TEXT) 컬럼이 `shop_temp` 테이블에 존재하지 않을 경우 자동으로 추가하는 로직을 삽입했습니다.
*   **`$_FILES` 처리 로직 제거:** 기존의 `$_FILES` 슈퍼글로벌을 직접 처리하던 파일 업로드 로직(대략 74행부터 122행까지)을 제거했습니다. 파일 업로드는 이제 공통 컴포넌트의 `file_upload_handler.php`에서 독립적으로 처리됩니다.
*   **`uploaded_files_info` 처리:**
    *   `$_POST['uploaded_files_info']`로 전송된 JSON 문자열을 `json_decode()` 함수를 사용하여 PHP 배열로 파싱합니다.
    *   파싱된 파일 정보를 기반으로 `img` (원본 파일명들을 콤마로 구분), `file_path` (첫 번째 파일의 업로드 경로), `file_info` (전체 파일 상세 정보를 JSON 문자열로), `upload_log` (현재는 빈 JSON) 변수를 구성합니다.
    *   `MY_comment` 값은 `$_POST['comment']`에서 직접 받아 사용하도록 했습니다.
*   **`shop_temp` 삽입 쿼리 수정:** `INSERT INTO shop_temp` 쿼리에 `img`, `file_path`, `file_info`, `upload_log`, `MY_comment` 컬럼 및 해당 값들을 추가하여, 장바구니에 아이템과 함께 파일 메타데이터 및 기타사항이 저장되도록 했습니다.

## 📈 개선 효과
*   **코드 중복 제거:** `msticker/index.php`에서 중복된 파일 업로드 관련 코드를 제거하여 코드의 간결성과 유지보수성을 높였습니다.
*   **시스템 일관성:** `공통 파일 업로드 시스템 설계서`의 지침을 따라 공통 컴포넌트를 사용하여, 향후 다른 품목 페이지에도 동일한 업로드 경험과 관리 로직을 적용할 수 있는 기반을 마련했습니다.
*   **파일 정보 저장 자동화:** 업로드된 파일의 상세 정보가 `shop_temp` 테이블에 체계적으로 저장되므로, 장바구니 아이템과 파일 간의 연관성이 명확해졌습니다.
*   **`MY_comment` 오류 해결:** `MY_comment` 컬럼 존재 여부 확인 및 저장 로직을 추가하여 이전의 "Undefined index" 및 "Unknown column" 오류를 해결했습니다.

## ➡️ 다음 단계
1.  **기능 테스트:** `msticker` 페이지에서 파일 업로드 및 장바구니 추가 기능이 정상적으로 작동하는지, 그리고 `phpMyAdmin` 등의 도구를 통해 `shop_temp` 테이블에 파일 관련 정보가 올바르게 저장되는지 면밀히 확인해야 합니다.
2.  **`MlangOrder_PrintAuto` 연동:** `shop_temp` 테이블에 저장된 파일 정보를 기반으로, 주문 완료 시 `MlangOrder_PrintAuto` 테이블에 해당 파일 메타데이터가 정상적으로 이전되고 저장되도록 주문 처리 로직을 검토하고 필요시 수정해야 합니다.