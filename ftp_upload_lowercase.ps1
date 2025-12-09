# FTP 업로드 스크립트 (대소문자 문제 해결)
# 사용법: .\ftp_upload_lowercase.ps1

$ftpServer = "ftp.dsp1830.shop"
$ftpUser = "dsp1830"
$ftpPass = "dsp1830!@"

# 업로드할 파일 목록 (소문자 경로로 통일)
$files = @(
    @{Local="mlangorder_printauto\OrderFormOrderTree.php"; Remote="/public_html/mlangorder_printauto/OrderFormOrderTree.php"},
    @{Local="mlangorder_printauto\WindowSian.php"; Remote="/public_html/mlangorder_printauto/WindowSian.php"},
    @{Local="mlangorder_printauto\OrderResult_original.php"; Remote="/public_html/mlangorder_printauto/OrderResult_original.php"},
    @{Local="mlangorder_printauto\index.php"; Remote="/public_html/mlangorder_printauto/index.php"}
)

Write-Host "=== FTP 업로드 시작 (소문자 경로) ===" -ForegroundColor Green
Write-Host ""

foreach ($file in $files) {
    $localPath = $file.Local
    $remotePath = $file.Remote
    
    if (-not (Test-Path $localPath)) {
        Write-Host "[건너뜀] $localPath - 파일이 없습니다" -ForegroundColor Yellow
        continue
    }
    
    try {
        # FTP 요청 생성
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpServer$remotePath")
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.KeepAlive = $false
        
        # 파일 읽기 및 업로드
        $fileContent = [System.IO.File]::ReadAllBytes($localPath)
        $ftpRequest.ContentLength = $fileContent.Length
        
        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()
        
        $response = $ftpRequest.GetResponse()
        Write-Host "[성공] $localPath -> $remotePath" -ForegroundColor Green
        $response.Close()
    }
    catch {
        Write-Host "[실패] $localPath - $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== 업로드 완료 ===" -ForegroundColor Green
Write-Host ""
Write-Host "다음 단계:" -ForegroundColor Yellow
Write-Host "1. 서버에서 대소문자 혼용 경로 확인"
Write-Host "2. 필요시 서버에서 심볼릭 링크 생성 또는 경로 통일"
Write-Host ""
