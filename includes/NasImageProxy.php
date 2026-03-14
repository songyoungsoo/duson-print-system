<?php
/**
 * NasImageProxy — NAS FTP에서 구서버 교정이미지를 프록시로 서빙
 *
 * 프로덕션 디스크에 파일을 저장하지 않고, FTP에서 tmpfile()로 다운로드 후
 * readfile()로 스트리밍. tmpfile()은 fclose() 시 자동 삭제 → 디스크 사용 0.
 *
 * NAS 아카이브 경로: archive_upload/{order_no}/파일명
 *
 * 사용법:
 *   // 파일 존재 확인
 *   $files = NasImageProxy::listFiles($orderNo);
 *
 *   // 파일 스트리밍 (HTTP 응답으로 직접 출력)
 *   NasImageProxy::streamFile($orderNo, $filename);
 *
 * PHP 7.4+ (프로덕션 8.2 호환)
 */
class NasImageProxy {

    // NAS FTP 설정 — 1차 NAS 우선, 실패 시 2차
    private static $NAS_SERVERS = [
        [
            'label'  => 'NAS1(dsp1830)',
            'host'   => 'dsp1830.ipdisk.co.kr',
            'user'   => 'admin',
            'pass'   => '1830',
            'base'   => '/HDD2/share/archive_upload/',
        ],
        [
            'label'  => 'NAS2(sknas205)',
            'host'   => 'sknas205.ipdisk.co.kr',
            'user'   => 'sknas205',
            'pass'   => 'sknas205204203',
            'base'   => '/HDD1/duson260118/archive_upload/',
        ],
    ];

    // FTP 연결 타임아웃 (초)
    const CONNECT_TIMEOUT = 10;


    /**
     * NAS에서 주문번호 폴더의 파일 목록 조회
     *
     * @param int $orderNo 주문번호
     * @return array ['files' => [...], 'nas_label' => 'NAS1(dsp1830)'] 또는 빈 배열
     */
    public static function listFiles($orderNo) {
        $orderNo = intval($orderNo);
        if ($orderNo <= 0) return [];

        foreach (self::$NAS_SERVERS as $nas) {
            $ftp = self::connect($nas);
            if (!$ftp) continue;

            $remotePath = $nas['base'] . $orderNo . '/';
            $list = @ftp_nlist($ftp, $remotePath);
            @ftp_close($ftp);

            if ($list === false || empty($list)) continue;

            $files = [];
            foreach ($list as $item) {
                $name = basename($item);
                if ($name === '.' || $name === '..') continue;
                $files[] = $name;
            }

            if (!empty($files)) {
                return [
                    'files' => $files,
                    'nas_label' => $nas['label'],
                ];
            }
        }

        return [];
    }

    /**
     * NAS에서 파일 존재 확인
     *
     * @param int $orderNo 주문번호
     * @param string $filename 파일명
     * @return bool
     */
    public static function fileExists($orderNo, $filename) {
        $orderNo = intval($orderNo);
        if ($orderNo <= 0 || empty($filename)) return false;

        foreach (self::$NAS_SERVERS as $nas) {
            $ftp = self::connect($nas);
            if (!$ftp) continue;

            $remotePath = $nas['base'] . $orderNo . '/' . $filename;
            $size = @ftp_size($ftp, $remotePath);
            @ftp_close($ftp);

            if ($size > 0) return true;
        }

        return false;
    }

    /**
     * NAS에서 파일을 다운로드하여 HTTP 스트리밍
     * 
     * tmpfile() → ftp_get() → readfile() → fclose()(자동삭제)
     * 프로덕션 디스크 사용량: 0
     *
     * @param int $orderNo 주문번호
     * @param string $filename 파일명
     * @return bool 성공 여부
     */
    public static function streamFile($orderNo, $filename) {
        $orderNo = intval($orderNo);
        if ($orderNo <= 0 || empty($filename)) return false;

        // 보안: 디렉토리 트래버설 방지
        $filename = basename($filename);

        foreach (self::$NAS_SERVERS as $nas) {
            $ftp = self::connect($nas);
            if (!$ftp) continue;

            $remotePath = $nas['base'] . $orderNo . '/' . $filename;

            // ftp_size로 존재+크기 확인
            $size = @ftp_size($ftp, $remotePath);
            if ($size <= 0) {
                @ftp_close($ftp);
                continue;
            }

            // tmpfile → ftp_get → readfile → fclose(자동삭제)
            $tmpFile = tmpfile();
            if (!$tmpFile) {
                @ftp_close($ftp);
                continue;
            }

            $tmpPath = stream_get_meta_data($tmpFile)['uri'];
            $ok = @ftp_get($ftp, $tmpPath, $remotePath, FTP_BINARY);
            @ftp_close($ftp);

            if (!$ok) {
                fclose($tmpFile); // 자동 삭제
                continue;
            }

            // MIME type 결정
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mime = self::getMimeType($ext);

            // HTTP 헤더 출력
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . $size);
            header('Cache-Control: public, max-age=86400');
            header('X-NAS-Source: ' . $nas['label']);

            // 파일 스트리밍
            rewind($tmpFile);
            fpassthru($tmpFile);
            fclose($tmpFile); // tmpfile 자동 삭제

            return true;
        }

        return false;
    }

    /**
     * FTP 연결 생성 (passive 모드)
     *
     * @param array $nas NAS 설정 배열
     * @return resource|false FTP 연결 또는 false
     */
    private static function connect($nas) {
        $ftp = @ftp_connect($nas['host'], 21, self::CONNECT_TIMEOUT);
        if (!$ftp) return false;

        if (!@ftp_login($ftp, $nas['user'], $nas['pass'])) {
            @ftp_close($ftp);
            return false;
        }

        @ftp_pasv($ftp, true);
        return $ftp;
    }

    /**
     * 확장자 → MIME type 매핑
     *
     * @param string $ext 소문자 확장자
     * @return string MIME type
     */
    private static function getMimeType($ext) {
        $map = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'pdf'  => 'application/pdf',
            'ai'   => 'application/postscript',
            'psd'  => 'application/octet-stream',
            'hwp'  => 'application/x-hwp',
            'hwpx' => 'application/x-hwpx',
            'tif'  => 'image/tiff',
            'tiff' => 'image/tiff',
            'bmp'  => 'image/bmp',
        ];

        return $map[$ext] ?? 'application/octet-stream';
    }
}
