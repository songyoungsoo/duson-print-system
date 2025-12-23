# FTP 설정
$ftpServer = "ftp://dsp1830.shop"
$ftpUsername = "dsp1830"
$ftpPassword = "ds701018"

# 업로드할 파일 목록
$files = @(
    @{
        local = "mlangprintauto/inserted/index.php"
        remote = "/mlangprintauto/inserted/index.php"
    },
    @{
        local = "mlangprintauto/inserted/get_quantities.php"
        remote = "/mlangprintauto/inserted/get_quantities.php"
    }
)

Write-Host "📤 FTP 업로드 시작..." -ForegroundColor Cyan
Write-Host "   서버: $ftpServer" -ForegroundColor Gray

$successCount = 0
$failCount = 0

foreach ($file in $files) {
    $localFile = $file.local
    $remoteFile = $file.remote
    
    Write-Host "`n📄 파일: $localFile" -ForegroundColor Yellow
    
    try {
        # 파일 존재 확인
        if (-not (Test-Path $localFile)) {
            throw "로컬 파일을 찾을 수 없습니다"
        }

        # 파일 읽기
        $fileContent = [System.IO.File]::ReadAllBytes($localFile)
        $fileSize = $fileContent.Length
        Write-Host "   크기: $([math]::Round($fileSize/1KB, 2)) KB" -ForegroundColor Gray

        # FTP URI 생성
        $ftpUri = $ftpServer + $remoteFile
        
        # FTP 요청 생성
        $request = [System.Net.FtpWebRequest]::Create($ftpUri)
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUsername, $ftpPassword)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.UseBinary = $true
        $request.UsePassive = $true
        $request.KeepAlive = $false
        
        # 파일 업로드
        $requestStream = $request.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()
        
        # 응답 확인
        $response = $request.GetResponse()
        Write-Host "   ✅ 업로드 성공" -ForegroundColor Green
        $response.Close()
        
        $successCount++
        
    } catch {
        Write-Host "   ❌ 업로드 실패: $($_.Exception.Message)" -ForegroundColor Red
        $failCount++
    }
}

Write-Host "`n" -NoNewline
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host "=" -NoNewline -ForegroundColor Gray
Write-Host ""
Write-Host "📊 업로드 완료: 성공 $successCount / 실패 $failCount" -ForegroundColor Cyan
Write-Host "🔗 URL: http://dsp1830.shop/mlangprintauto/inserted/" -ForegroundColor Cyan

if ($failCount -gt 0) {
    exit 1
} else {
    exit 0
}
