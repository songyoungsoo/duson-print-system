<?php
/**
 * 파일 다운로드 UI 컴포넌트
 * 관리자 페이지에서 주문 파일을 표시하고 다운로드할 수 있는 통합 컴포넌트
 */

class FileDownloadComponent {
    
    /**
     * 주문 파일 다운로드 버튼 렌더링
     *
     * @param int $orderNo 주문 번호
     * @param string $filename 파일명 (선택사항)
     * @param string $imgFolder ImgFolder 값 (선택사항)
     * @param string $buttonClass 버튼 CSS 클래스
     * @return string HTML
     */
    public static function renderDownloadButton($orderNo, $filename = '', $imgFolder = '', $buttonClass = 'btn btn-sm btn-primary') {
        if (!$orderNo) {
            return '<span class="text-muted">-</span>';
        }

        // 파일이 있는 경우
        if ($filename && $imgFolder) {
            $downloadUrl = 'download_files.php?action=single&order_no=' . $orderNo . '&filename=' . urlencode($filename);
            $previewUrl = 'download_files.php?action=preview&order_no=' . $orderNo . '&filename=' . urlencode($filename);
            
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
            $isImage = in_array($extension, $imageExtensions);

            $html = '<div class="file-download-group">';
            
            // 미리보기 버튼 (이미지만)
            if ($isImage) {
                $html .= '<button type="button" class="' . $buttonClass . '" onclick="previewFile(\'' . $previewUrl . '\', \'' . htmlspecialchars($filename) . '\')">';
                $html .= '👁️ 미리보기</button> ';
            }
            
            // 다운로드 버튼
            $html .= '<a href="' . $downloadUrl . '" class="' . $buttonClass . '" download>';
            $html .= '📥 다운로드</a>';
            
            $html .= '</div>';
            return $html;
        }

        // 파일 없음
        return '<span class="text-muted">파일 없음</span>';
    }

    /**
     * 주문의 모든 파일 ZIP 다운로드 버튼 렌더링
     *
     * @param int $orderNo 주문 번호
     * @param int $fileCount 파일 개수
     * @param string $buttonClass 버튼 CSS 클래스
     * @return string HTML
     */
    public static function renderZipDownloadButton($orderNo, $fileCount = 0, $buttonClass = 'btn btn-sm btn-success') {
        if (!$orderNo || $fileCount == 0) {
            return '';
        }

        $zipUrl = 'download_files.php?action=zip&order_no=' . $orderNo;
        
        $html = '<a href="' . $zipUrl . '" class="' . $buttonClass . '">';
        $html .= '📦 전체 다운로드 (ZIP)</a>';
        
        return $html;
    }

    /**
     * 파일 정보 테이블 렌더링
     *
     * @param mysqli $db 데이터베이스 연결
     * @param int $orderNo 주문 번호
     * @return string HTML
     */
    public static function renderFileList($db, $orderNo) {
        require_once __DIR__ . '/../../../includes/UploadPathHelper.php';
        
        $files = UploadPathHelper::getOrderFiles($db, $orderNo);
        
        if (empty($files)) {
            return '<p class="text-muted">업로드된 파일이 없습니다.</p>';
        }

        $html = '<div class="file-list-container">';
        $html .= '<h4>업로드 파일 목록 (' . count($files) . '개)</h4>';
        $html .= '<table class="table table-sm table-bordered">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th style="width: 50px">#</th>';
        $html .= '<th>파일명</th>';
        $html .= '<th style="width: 100px">품목</th>';
        $html .= '<th style="width: 80px">상태</th>';
        $html .= '<th style="width: 200px">작업</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($files as $index => $file) {
            $html .= '<tr>';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td><code>' . htmlspecialchars($file['filename']) . '</code></td>';
            $html .= '<td>' . htmlspecialchars($file['product_type']) . '</td>';
            
            // 파일 존재 상태
            if ($file['exists']) {
                $html .= '<td><span class="badge badge-success">존재</span></td>';
            } else {
                $html .= '<td><span class="badge badge-danger">없음</span></td>';
            }
            
            // 작업 버튼
            $html .= '<td>';
            if ($file['exists']) {
                $html .= self::renderDownloadButton(
                    $file['order_no'],
                    $file['filename'],
                    dirname($file['web_path']),
                    'btn btn-xs btn-primary'
                );
            } else {
                $html .= '<span class="text-danger">파일을 찾을 수 없음</span>';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        
        // ZIP 다운로드 버튼 (파일이 2개 이상일 때)
        if (count($files) >= 1) {
            $html .= '<div class="mt-2">';
            $html .= self::renderZipDownloadButton($orderNo, count($files));
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * JavaScript 코드 렌더링 (페이지 하단에 포함)
     *
     * @return string JavaScript
     */
    public static function renderJavaScript() {
        return <<<'JAVASCRIPT'
<script>
function previewFile(url, filename) {
    const modal = window.open('', 'FilePreview', 'width=800,height=600,resizable=yes,scrollbars=yes');
    
    modal.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>미리보기: ${filename}</title>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    background: #f0f0f0;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }
                img {
                    max-width: 100%;
                    height: auto;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    background: white;
                    padding: 10px;
                }
                .header {
                    background: white;
                    padding: 15px;
                    width: 100%;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .filename {
                    font-weight: bold;
                    font-size: 16px;
                }
                .close-btn {
                    padding: 8px 16px;
                    background: #dc3545;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                .close-btn:hover {
                    background: #c82333;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <span class="filename">${filename}</span>
                <button class="close-btn" onclick="window.close()">닫기</button>
            </div>
            <img src="${url}" alt="${filename}" />
        </body>
        </html>
    `);
}
</script>
JAVASCRIPT;
    }

    /**
     * CSS 스타일 렌더링 (페이지 헤더에 포함)
     *
     * @return string CSS
     */
    public static function renderCSS() {
        return <<<'CSS'
<style>
.file-download-group {
    display: inline-flex;
    gap: 5px;
}

.file-list-container {
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.file-list-container h4 {
    margin-bottom: 15px;
    color: #333;
}

.file-list-container table {
    background: white;
}

.file-list-container .badge {
    padding: 4px 8px;
    font-size: 12px;
}

.file-list-container .badge-success {
    background-color: #28a745;
    color: white;
}

.file-list-container .badge-danger {
    background-color: #dc3545;
    color: white;
}

.btn-xs {
    padding: 2px 8px;
    font-size: 12px;
}
</style>
CSS;
    }
}
