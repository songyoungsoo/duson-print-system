<?php
/**
 * EmailTypoFixer - 이메일 도메인 오타 자동 감지 및 수정
 * 
 * 알려진 도메인 목록 + 레벤슈타인 거리(편집 거리 1~2)로 유사 도메인 매칭.
 * CLI 스크립트와 대시보드 API에서 공통 사용.
 */
class EmailTypoFixer
{
    /** 한국에서 흔한 이메일 도메인 목록 (정상) */
    private static $knownDomains = [
        'naver.com',
        'gmail.com',
        'daum.net',
        'hanmail.net',
        'nate.com',
        'hotmail.com',
        'yahoo.com',
        'yahoo.co.kr',
        'outlook.com',
        'kakao.com',
        'korea.com',
        'paran.com',
        'dreamwiz.com',
        'empal.com',
        'lycos.co.kr',
        'hanmir.com',
        'freechal.com',
        'chol.com',
    ];

    /** 자주 발생하는 오타 → 정답 직접 매핑 (레벤슈타인보다 정확) */
    private static $directMap = [
        // naver 변형
        'naver.vom'   => 'naver.com',
        'naver.coml'  => 'naver.com',
        'naver.co.kr' => 'naver.com',
        'naver.cim'   => 'naver.com',
        'naver.con'   => 'naver.com',
        'naver.cm'    => 'naver.com',
        'naver.xom'   => 'naver.com',
        'nave.com'    => 'naver.com',
        'navr.com'    => 'naver.com',
        'naer.com'    => 'naver.com',
        'naver.comm'  => 'naver.com',
        'never.com'   => 'naver.com',
        // gmail 변형
        'gmail.co'    => 'gmail.com',
        'gamil.com'   => 'gmail.com',
        'gmaill.com'  => 'gmail.com',
        'gmial.com'   => 'gmail.com',
        'gmal.com'    => 'gmail.com',
        'gmail.con'   => 'gmail.com',
        'gmail.comm'  => 'gmail.com',
        'gmail.cim'   => 'gmail.com',
        // daum 변형
        'daum.ent'    => 'daum.net',
        'daum.ne'     => 'daum.net',
        'daum.bet'    => 'daum.net',
        'daum.met'    => 'daum.net',
        'duam.net'    => 'daum.net',
        // hanmail 변형
        'hanmail.ent' => 'hanmail.net',
        'hanmail.ne'  => 'hanmail.net',
        'hanmail.bet' => 'hanmail.net',
        'hammail.net' => 'hanmail.net',
        'hanmal.net'  => 'hanmail.net',
        // nate 변형
        'nate.ocm'    => 'nate.com',
        'nate.con'    => 'nate.com',
        'nate.cm'     => 'nate.com',
        'nate.vom'    => 'nate.com',
        // hotmail 변형
        'hotmail.con' => 'hotmail.com',
        'hotmail.co'  => 'hotmail.com',
        'hotmal.com'  => 'hotmail.com',
        'hotmial.com' => 'hotmail.com',
    ];

    /**
     * 단일 이메일 오타 검사
     * 
     * @param string $email 검사할 이메일
     * @return array|null ['original' => 원본, 'suggested' => 수정안, 'method' => 감지방법] 또는 null(정상)
     */
    public static function check(string $email): ?array
    {
        $email = trim(strtolower($email));
        if (empty($email) || strpos($email, '@') === false) {
            return null;
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return null;
        }

        $local = $parts[0];
        $domain = $parts[1];

        // 1. 이미 정상 도메인이면 null
        if (in_array($domain, self::$knownDomains, true)) {
            return null;
        }

        // 2. 직접 매핑 테이블에서 찾기 (가장 정확)
        if (isset(self::$directMap[$domain])) {
            return [
                'original'  => $email,
                'suggested' => $local . '@' . self::$directMap[$domain],
                'domain_from' => $domain,
                'domain_to'   => self::$directMap[$domain],
                'method'    => 'direct_map',
            ];
        }

        // 3. 레벤슈타인 거리로 유사 도메인 찾기 (편집 거리 1~2)
        $bestMatch = null;
        $bestDist = 999;

        foreach (self::$knownDomains as $known) {
            $dist = levenshtein($domain, $known);
            if ($dist > 0 && $dist <= 2 && $dist < $bestDist) {
                $bestDist = $dist;
                $bestMatch = $known;
            }
        }

        if ($bestMatch !== null) {
            return [
                'original'  => $email,
                'suggested' => $local . '@' . $bestMatch,
                'domain_from' => $domain,
                'domain_to'   => $bestMatch,
                'method'    => 'levenshtein_' . $bestDist,
            ];
        }

        return null;
    }

    /**
     * DB에서 전체 회원 이메일 검사
     * 
     * @param mysqli $db DB 연결
     * @return array ['typos' => [...], 'total_checked' => N]
     */
    public static function scanAll(mysqli $db): array
    {
        $query = "SELECT id, username, name, email FROM users WHERE email IS NOT NULL AND email != '' ORDER BY id";
        $result = mysqli_query($db, $query);

        $typos = [];
        $total = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $total++;
            $check = self::check($row['email']);
            if ($check !== null) {
                $check['user_id'] = $row['id'];
                $check['username'] = $row['username'];
                $check['name'] = $row['name'];
                $typos[] = $check;
            }
        }

        return [
            'typos' => $typos,
            'total_checked' => $total,
        ];
    }

    /**
     * 단일 회원 이메일 수정
     * 
     * @param mysqli $db DB 연결
     * @param int $userId 회원 ID
     * @param string $newEmail 수정할 이메일
     * @return bool 성공 여부
     */
    public static function fix(mysqli $db, int $userId, string $newEmail): bool
    {
        $query = "UPDATE users SET email = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        // 2 placeholders: s, i
        $placeholder_count = substr_count($query, '?'); // 2
        $type_string = 'si';
        $type_count = strlen($type_string); // 2
        $var_count = 2;
        mysqli_stmt_bind_param($stmt, $type_string, $newEmail, $userId);
        return mysqli_stmt_execute($stmt);
    }

    /**
     * 감지된 오타 일괄 수정
     * 
     * @param mysqli $db DB 연결
     * @param array $typos scanAll() 결과의 typos 배열
     * @return array ['fixed' => N, 'failed' => N, 'details' => [...]]
     */
    public static function fixAll(mysqli $db, array $typos): array
    {
        $fixed = 0;
        $failed = 0;
        $details = [];

        foreach ($typos as $typo) {
            $success = self::fix($db, $typo['user_id'], $typo['suggested']);
            if ($success) {
                $fixed++;
                $details[] = [
                    'user_id' => $typo['user_id'],
                    'from' => $typo['original'],
                    'to' => $typo['suggested'],
                    'status' => 'fixed',
                ];
            } else {
                $failed++;
                $details[] = [
                    'user_id' => $typo['user_id'],
                    'from' => $typo['original'],
                    'to' => $typo['suggested'],
                    'status' => 'failed',
                    'error' => mysqli_error($db),
                ];
            }
        }

        return ['fixed' => $fixed, 'failed' => $failed, 'details' => $details];
    }
}
