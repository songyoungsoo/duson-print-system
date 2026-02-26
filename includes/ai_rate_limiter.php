<?php
/**
 * AI API Rate Limiter — Gemini API 호출 상한 관리
 * 
 * 파일 기반 카운터 (DB 불필요). 자정(PST) = 한국시간 오후 5시 기준 리셋.
 * 
 * 상한: 전체 300회/일, IP당 20회/일
 * 초과 시 Gemini 호출 차단 → 전화/상담위젯 fallback
 * 
 * @since 2026-02-26
 */

class AIRateLimiter
{
    // ── 설정 ──
    private const DAILY_GLOBAL_LIMIT = 300;   // 전체 하루 상한
    private const DAILY_IP_LIMIT = 20;        // IP당 하루 상한
    
    private string $dataDir;
    private string $dateKey;
    
    public function __construct()
    {
        // /tmp/ai_rate/ 디렉토리에 카운터 파일 저장
        $this->dataDir = sys_get_temp_dir() . '/ai_rate';
        if (!is_dir($this->dataDir)) {
            @mkdir($this->dataDir, 0755, true);
        }
        
        // 날짜 키: PST 자정 기준 (Gemini quota 리셋과 동기화)
        $this->dateKey = (new \DateTime('now', new \DateTimeZone('America/Los_Angeles')))->format('Y-m-d');
        
        // 어제 이전 파일 정리 (1일 1회)
        $this->cleanupOldFiles();
    }
    
    /**
     * API 호출 가능 여부 확인 + 카운터 증가
     * @return array ['allowed' => bool, 'reason' => string|null, 'global_count' => int, 'ip_count' => int]
     */
    public function checkAndIncrement(string $ip = ''): array
    {
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
        
        $globalCount = $this->getCount('global');
        $ipCount = $this->getCount('ip_' . md5($ip));
        
        // 전체 상한 체크
        if ($globalCount >= self::DAILY_GLOBAL_LIMIT) {
            return [
                'allowed' => false,
                'reason' => 'daily_global_limit',
                'global_count' => $globalCount,
                'ip_count' => $ipCount,
            ];
        }
        
        // IP 상한 체크
        if ($ipCount >= self::DAILY_IP_LIMIT) {
            return [
                'allowed' => false,
                'reason' => 'daily_ip_limit',
                'global_count' => $globalCount,
                'ip_count' => $ipCount,
            ];
        }
        
        // 허용 → 카운터 증가
        $this->increment('global');
        $this->increment('ip_' . md5($ip));
        
        return [
            'allowed' => true,
            'reason' => null,
            'global_count' => $globalCount + 1,
            'ip_count' => $ipCount + 1,
        ];
    }
    
    /**
     * 현재 상태 조회 (증가 없음)
     */
    public function getStatus(string $ip = ''): array
    {
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
        
        return [
            'global_count' => $this->getCount('global'),
            'global_limit' => self::DAILY_GLOBAL_LIMIT,
            'ip_count' => $this->getCount('ip_' . md5($ip)),
            'ip_limit' => self::DAILY_IP_LIMIT,
            'date_key' => $this->dateKey,
        ];
    }
    
    // ── 내부 메서드 ──
    
    private function getFilePath(string $key): string
    {
        return $this->dataDir . '/' . $this->dateKey . '_' . $key . '.count';
    }
    
    private function getCount(string $key): int
    {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) return 0;
        return (int)trim(file_get_contents($file));
    }
    
    private function increment(string $key): void
    {
        $file = $this->getFilePath($key);
        $count = $this->getCount($key) + 1;
        file_put_contents($file, (string)$count, LOCK_EX);
    }
    
    private function cleanupOldFiles(): void
    {
        // 하루에 1번만 실행 (플래그 파일)
        $flagFile = $this->dataDir . '/cleanup_' . $this->dateKey;
        if (file_exists($flagFile)) return;
        
        @touch($flagFile);
        
        $files = glob($this->dataDir . '/*.count');
        if (!$files) return;
        
        foreach ($files as $file) {
            $basename = basename($file);
            // 파일명이 오늘 날짜로 시작하지 않으면 삭제
            if (strpos($basename, $this->dateKey) !== 0) {
                @unlink($file);
            }
        }
    }
}

/**
 * Rate limit 초과 시 반환할 fallback 메시지
 */
function getAIRateLimitMessage(string $reason): string
{
    if ($reason === 'daily_ip_limit') {
        return "AI 상담 이용 횟수를 초과했습니다.\n" .
               "더 자세한 상담은 아래로 문의해주세요:\n" .
               "📱 상담위젯 (홈페이지 우측 하단, 영업시간 중)\n" .
               "📞 전화: 02-2632-1830\n" .
               "💬 카톡: http://pf.kakao.com/_pEGhj/chat";
    }
    
    // daily_global_limit
    return "현재 AI 상담이 일시적으로 제한되어 있습니다.\n" .
           "아래로 문의해주세요:\n" .
           "📱 상담위젯 (홈페이지 우측 하단, 영업시간 중)\n" .
           "📞 전화: 02-2632-1830\n" .
           "💬 카톡: http://pf.kakao.com/_pEGhj/chat";
}
