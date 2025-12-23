# FTP 설정
$ftpServer = "ftp://dsp1830.shop"
$ftpUsername = "dsp1830"
$ftpPassword = "ds701018"
$localFile = "mlangprintauto/inserted/index.php"
$remoteFile = "/mlangprintauto/inserted/index.php"

Write-Host "📤 FTP 업로드 시작..." -ForegroundColor Cyan
Write-Host "   서버: $ftpServer" -ForegroundColor Gray
Write-Host "   로컬: $localFile" -ForegroundColor Gray
Write-Host "   원격: $remoteFile" -ForegroundColor Gray

try {
    # 파일 존재 확인
    if (-not (Test-Path $localFile)) {
        throw "로컬 파일을 찾을 수 없습니다: $localFile"
    }

    # 파일 읽기
    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
    $fileSize = $fileContent.Length
    Write-Host "   파일 크기: $([math]::Round($fileSize/1KB, 2)) KB" -ForegroundColor Gray

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
    Write-Host "`n⏳ 업로드 중..." -ForegroundColor Yellow
    $requestStream = $request.GetRequestStream()
    $requestStream.Write($fileContent, 0, $fileContent.Length)
    $requestStream.Close()
    
    # 응답 확인
    $response = $request.GetResponse()
    Write-Host "✅ 업로드 성공!" -ForegroundColor Green
    Write-Host "   상태: $($response.StatusDescription)" -ForegroundColor Green
    Write-Host "   URL: http://dsp1830.shop/mlangprintauto/inserted/index.php" -ForegroundColor Cyan
    $response.Close()
    
    exit 0
    
} catch {
    Write-Host "`n❌ 업로드 실패!" -ForegroundColor Red
    Write-Host "   오류: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.InnerException) {
        Write-Host "   상세: $($_.Exception.InnerException.Message)" -ForegroundColor Red
    }
    
    exit 1
}
