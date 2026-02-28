<?php
/**
 * 팝업 템플릿 엔진
 * 6종 명절/휴무 인사말 템플릿 + 간지(干支) 자동 계산
 *
 * 사용법:
 *   require_once 'includes/popup_templates.php';
 *   $html = renderPopupTemplate('new_year', ['year' => 2026, ...]);
 */

/**
 * 간지(干支/천간지지) 계산
 * @param int $year 양력 연도
 * @return array ['name'=>'병오', 'stem'=>'병', 'branch'=>'오', 'animal'=>'말', 'emoji'=>'🐴']
 */
function getGanji($year) {
    $stems    = ['갑','을','병','정','무','기','경','신','임','계'];
    $branches = ['자','축','인','묘','진','사','오','미','신','유','술','해'];
    $animals  = ['쥐','소','호랑이','토끼','용','뱀','말','양','원숭이','닭','개','돼지'];
    $emojis   = ['🐀','🐂','🐅','🐇','🐉','🐍','🐴','🐏','🐒','🐓','🐕','🐷'];

    $idx = $year - 4;
    $si = (($idx % 10) + 10) % 10;
    $bi = (($idx % 12) + 12) % 12;

    return [
        'name'   => $stems[$si] . $branches[$bi],
        'stem'   => $stems[$si],
        'branch' => $branches[$bi],
        'animal' => $animals[$bi],
        'emoji'  => $emojis[$bi]
    ];
}

/**
 * 사용 가능한 템플릿 목록
 */
function getTemplateList() {
    return [
        'new_year' => [
            'name' => '신년 인사',
            'icon' => '🎍',
            'desc' => '양력 새해 인사',
            'has_period' => false,
            'color' => '#DC2626'
        ],
        'lunar_new_year' => [
            'name' => '설날 인사',
            'icon' => '🧧',
            'desc' => '음력 설날 인사 + 휴무 안내',
            'has_period' => true,
            'color' => '#B91C1C'
        ],
        'chuseok' => [
            'name' => '추석 인사',
            'icon' => '🌕',
            'desc' => '한가위 인사 + 휴무 안내',
            'has_period' => true,
            'color' => '#C2410C'
        ],
        'summer_vacation' => [
            'name' => '여름휴가 안내',
            'icon' => '🏖️',
            'desc' => '하계 휴무 안내',
            'has_period' => true,
            'color' => '#0369A1'
        ],
        'year_end' => [
            'name' => '연말 인사',
            'icon' => '🎄',
            'desc' => '한 해 감사 인사',
            'has_period' => false,
            'color' => '#1E3A5F'
        ],
        'general_notice' => [
            'name' => '일반 휴무 공지',
            'icon' => '📢',
            'desc' => '자유 입력 공지',
            'has_period' => true,
            'color' => '#374151'
        ]
    ];
}

/**
 * 날짜를 한국어 형식으로 포맷
 * @param string $date 'Y-m-d' 형식
 * @return string 'M. D (요일)' 형식
 */
function formatDateKr($date) {
    if (empty($date)) return '';
    $dayNames = ['일','월','화','수','목','금','토'];
    $ts = strtotime($date);
    if ($ts === false) return $date;
    $m = (int)date('n', $ts);
    $d = (int)date('j', $ts);
    $w = $dayNames[(int)date('w', $ts)];
    return "{$m}. {$d} ({$w})";
}

/**
 * 템플릿 렌더링
 * @param string $type 템플릿 타입 (new_year, lunar_new_year, chuseok, summer_vacation, year_end, general_notice)
 * @param array $data 템플릿 데이터
 * @return string 렌더링된 HTML
 */
function renderPopupTemplate($type, $data = []) {
    // 기본값 설정
    $defaults = [
        'year'         => (int)date('Y'),
        'start_date'   => '',
        'end_date'     => '',
        'resume_date'  => '',
        'phone'        => '02-2632-1830',
        'company'      => '두손기획인쇄',
        'greeting'     => '',
    ];
    $d = array_merge($defaults, $data);

    // 간지 계산
    $ganji = getGanji($d['year']);
    $ganjiName = $ganji['name'];
    $ganjiAnimal = $ganji['animal'];
    $ganjiEmoji = $ganji['emoji'];

    // 날짜 포맷
    $startFmt  = formatDateKr($d['start_date']);
    $endFmt    = formatDateKr($d['end_date']);
    $resumeFmt = formatDateKr($d['resume_date']);
    $yearStr   = $d['year'] . '년';

    // HTML escape helper
    $e = function($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };

    switch ($type) {
        case 'new_year':
            return _tplNewYear($d, $ganji, $e);
        case 'lunar_new_year':
            return _tplLunarNewYear($d, $ganji, $startFmt, $endFmt, $resumeFmt, $e);
        case 'chuseok':
            return _tplChuseok($d, $ganji, $startFmt, $endFmt, $resumeFmt, $e);
        case 'summer_vacation':
            return _tplSummerVacation($d, $startFmt, $endFmt, $resumeFmt, $e);
        case 'year_end':
            return _tplYearEnd($d, $ganji, $e);
        case 'general_notice':
            return _tplGeneralNotice($d, $startFmt, $endFmt, $resumeFmt, $e);
        default:
            return '<div style="padding:40px;text-align:center;color:#999;">알 수 없는 템플릿</div>';
    }
}


// ============================================================
// 개별 템플릿 렌더링 함수
// ============================================================

/** 🎍 신년 인사 */
function _tplNewYear($d, $ganji, $e) {
    $year = $d['year'];
    $greeting = !empty($d['greeting'])
        ? nl2br($e($d['greeting']))
        : "{$e($ganji['name'])}년 새해<br>복 많이 받으세요";

    return <<<HTML
<div style="font-family:'Malgun Gothic','맑은 고딕',sans-serif;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#DC2626 0%,#991B1B 100%);padding:40px 30px 30px;text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-20px;left:-20px;font-size:120px;opacity:0.08;transform:rotate(-15deg);">🎍</div>
    <div style="position:absolute;bottom:-10px;right:-10px;font-size:80px;opacity:0.08;">🎍</div>
    <div style="font-size:14px;color:rgba(255,255,255,0.7);letter-spacing:3px;margin-bottom:8px;">HAPPY NEW YEAR</div>
    <div style="font-size:48px;margin-bottom:12px;">{$e($ganji['emoji'])}</div>
    <div style="font-size:28px;font-weight:800;color:#fff;line-height:1.4;margin-bottom:10px;">{$greeting}</div>
    <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:20px;padding:6px 18px;">
      <span style="font-size:14px;color:rgba(255,255,255,0.9);">{$year}년 · {$e($ganji['name'])}년 ({$e($ganji['animal'])}띠)</span>
    </div>
  </div>
  <div style="background:#FEF2F2;padding:20px 30px;text-align:center;">
    <div style="font-size:13px;color:#991B1B;font-weight:600;">{$e($d['company'])}</div>
    <div style="font-size:12px;color:#B91C1C;margin-top:2px;">☎ {$e($d['phone'])}</div>
  </div>
</div>
HTML;
}

/** 🧧 설날 인사 */
function _tplLunarNewYear($d, $ganji, $startFmt, $endFmt, $resumeFmt, $e) {
    $year = $d['year'];
    $greeting = !empty($d['greeting'])
        ? nl2br($e($d['greeting']))
        : "{$e($ganji['name'])}년 설날을 맞이하여<br>풍성한 명절 보내시기 바랍니다";

    $periodHtml = '';
    if (!empty($startFmt) && !empty($endFmt)) {
        $periodHtml = <<<PERIOD
    <div style="background:rgba(255,255,255,0.85);border-radius:10px;padding:14px 20px;margin-top:20px;border:1px solid rgba(185,28,28,0.15);">
      <div style="font-size:11px;color:#991B1B;font-weight:700;letter-spacing:1px;margin-bottom:6px;">휴무기간</div>
      <div style="font-size:16px;font-weight:700;color:#7F1D1D;">{$startFmt} ~ {$endFmt}</div>
PERIOD;
        if (!empty($resumeFmt)) {
            $periodHtml .= "<div style=\"font-size:12px;color:#991B1B;margin-top:6px;\">{$resumeFmt} 부터 정상 운영</div>";
        }
        $periodHtml .= '</div>';
    }

    return <<<HTML
<div style="font-family:'Malgun Gothic','맑은 고딕',sans-serif;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#B91C1C 0%,#7F1D1D 100%);padding:35px 30px 20px;text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;top:10px;left:15px;font-size:60px;opacity:0.1;">🧧</div>
    <div style="position:absolute;top:10px;right:15px;font-size:60px;opacity:0.1;">🏮</div>
    <div style="font-size:12px;color:rgba(255,255,255,0.6);letter-spacing:4px;margin-bottom:6px;">설날 인사</div>
    <div style="font-size:42px;margin-bottom:8px;">{$e($ganji['emoji'])}</div>
    <div style="font-size:22px;font-weight:800;color:#fff;line-height:1.5;">{$greeting}</div>
    <div style="margin-top:10px;font-size:13px;color:rgba(255,255,255,0.7);">{$year}년 {$e($ganji['name'])}년 ({$e($ganji['animal'])}띠)</div>
  </div>
  <div style="background:#FEF2F2;padding:16px 30px 20px;text-align:center;">
    {$periodHtml}
    <div style="margin-top:14px;font-size:13px;color:#991B1B;font-weight:600;">{$e($d['company'])}</div>
    <div style="font-size:12px;color:#B91C1C;margin-top:2px;">☎ {$e($d['phone'])}</div>
  </div>
</div>
HTML;
}

/** 🌕 추석 인사 */
function _tplChuseok($d, $ganji, $startFmt, $endFmt, $resumeFmt, $e) {
    $year = $d['year'];
    $greeting = !empty($d['greeting'])
        ? nl2br($e($d['greeting']))
        : "풍요로운 한가위<br>보내세요";

    $periodHtml = '';
    if (!empty($startFmt) && !empty($endFmt)) {
        $periodHtml = <<<PERIOD
    <div style="background:rgba(255,255,255,0.85);border-radius:10px;padding:14px 20px;margin-top:20px;border:1px solid rgba(194,65,12,0.15);">
      <div style="font-size:11px;color:#9A3412;font-weight:700;letter-spacing:1px;margin-bottom:6px;">휴무기간</div>
      <div style="font-size:16px;font-weight:700;color:#7C2D12;">{$startFmt} ~ {$endFmt}</div>
PERIOD;
        if (!empty($resumeFmt)) {
            $periodHtml .= "<div style=\"font-size:12px;color:#9A3412;margin-top:6px;\">{$resumeFmt} 부터 정상 운영</div>";
        }
        $periodHtml .= '</div>';
    }

    return <<<HTML
<div style="font-family:'Malgun Gothic','맑은 고딕',sans-serif;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#EA580C 0%,#9A3412 100%);padding:35px 30px 20px;text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-10px;right:20px;font-size:90px;opacity:0.12;">🌕</div>
    <div style="position:absolute;bottom:0;left:10px;font-size:50px;opacity:0.1;">🍂</div>
    <div style="font-size:12px;color:rgba(255,255,255,0.6);letter-spacing:4px;margin-bottom:6px;">추석 인사</div>
    <div style="font-size:56px;margin-bottom:8px;">🌕</div>
    <div style="font-size:26px;font-weight:800;color:#fff;line-height:1.4;">{$greeting}</div>
    <div style="margin-top:8px;font-size:13px;color:rgba(255,255,255,0.7);">{$year}년 한가위</div>
  </div>
  <div style="background:#FFF7ED;padding:16px 30px 20px;text-align:center;">
    {$periodHtml}
    <div style="margin-top:14px;font-size:13px;color:#9A3412;font-weight:600;">{$e($d['company'])}</div>
    <div style="font-size:12px;color:#C2410C;margin-top:2px;">☎ {$e($d['phone'])}</div>
  </div>
</div>
HTML;
}

/** 🏖️ 여름휴가 안내 */
function _tplSummerVacation($d, $startFmt, $endFmt, $resumeFmt, $e) {
    $greeting = !empty($d['greeting'])
        ? nl2br($e($d['greeting']))
        : "여름 휴가 기간 동안<br><b>주문 접수 및 배송이 중단</b>됩니다";

    $periodHtml = '';
    if (!empty($startFmt) && !empty($endFmt)) {
        $periodHtml = <<<PERIOD
    <div style="background:rgba(255,255,255,0.85);border-radius:10px;padding:14px 20px;margin-top:20px;border:1px solid rgba(3,105,161,0.15);">
      <div style="font-size:11px;color:#075985;font-weight:700;letter-spacing:1px;margin-bottom:6px;">휴무기간</div>
      <div style="font-size:16px;font-weight:700;color:#0C4A6E;">{$startFmt} ~ {$endFmt}</div>
PERIOD;
        if (!empty($resumeFmt)) {
            $periodHtml .= "<div style=\"font-size:12px;color:#075985;margin-top:6px;\">{$resumeFmt} 부터 정상 운영</div>";
        }
        $periodHtml .= '</div>';
    }

    return <<<HTML
<div style="font-family:'Malgun Gothic','맑은 고딕',sans-serif;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#0284C7 0%,#0369A1 50%,#075985 100%);padding:35px 30px 20px;text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;top:5px;right:15px;font-size:70px;opacity:0.1;">☀️</div>
    <div style="position:absolute;bottom:-5px;left:0;right:0;font-size:40px;opacity:0.1;">🌊🌊🌊🌊🌊</div>
    <div style="font-size:12px;color:rgba(255,255,255,0.6);letter-spacing:4px;margin-bottom:6px;">SUMMER VACATION</div>
    <div style="font-size:48px;margin-bottom:8px;">🏖️</div>
    <div style="font-size:20px;font-weight:700;color:#fff;line-height:1.5;">{$greeting}</div>
    <div style="margin-top:6px;font-size:12px;color:rgba(255,255,255,0.7);">고객 여러분의 양해 부탁드립니다</div>
  </div>
  <div style="background:#F0F9FF;padding:16px 30px 20px;text-align:center;">
    {$periodHtml}
    <div style="margin-top:14px;font-size:13px;color:#075985;font-weight:600;">{$e($d['company'])}</div>
    <div style="font-size:12px;color:#0369A1;margin-top:2px;">☎ {$e($d['phone'])}</div>
  </div>
</div>
HTML;
}

/** 🎄 연말 인사 */
function _tplYearEnd($d, $ganji, $e) {
    $year = $d['year'];
    $nextYear = $year + 1;
    $nextGanji = getGanji($nextYear);
    $greeting = !empty($d['greeting'])
        ? nl2br($e($d['greeting']))
        : "한 해 동안 감사했습니다<br>{$nextYear}년에도 잘 부탁드립니다";

    return <<<HTML
<div style="font-family:'Malgun Gothic','맑은 고딕',sans-serif;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#1E3A5F 0%,#0F172A 100%);padding:40px 30px 30px;text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;top:5px;left:20px;font-size:18px;opacity:0.3;">✦</div>
    <div style="position:absolute;top:30px;right:30px;font-size:12px;opacity:0.25;">✦</div>
    <div style="position:absolute;top:15px;right:60px;font-size:8px;opacity:0.2;">✦</div>
    <div style="position:absolute;bottom:20px;left:40px;font-size:10px;opacity:0.2;">✦</div>
    <div style="font-size:12px;color:rgba(255,255,255,0.5);letter-spacing:4px;margin-bottom:6px;">HAPPY HOLIDAYS</div>
    <div style="font-size:48px;margin-bottom:10px;">🎄</div>
    <div style="font-size:22px;font-weight:800;color:#fff;line-height:1.5;">{$greeting}</div>
    <div style="display:inline-block;background:rgba(255,255,255,0.1);border-radius:20px;padding:6px 18px;margin-top:12px;">
      <span style="font-size:13px;color:rgba(255,255,255,0.7);">{$year} → {$nextYear} {$e($nextGanji['name'])}년 {$e($nextGanji['emoji'])}</span>
    </div>
  </div>
  <div style="background:#F1F5F9;padding:20px 30px;text-align:center;">
    <div style="font-size:13px;color:#1E3A5F;font-weight:600;">{$e($d['company'])}</div>
    <div style="font-size:12px;color:#475569;margin-top:2px;">☎ {$e($d['phone'])}</div>
  </div>
</div>
HTML;
}

/** 📢 일반 휴무 공지 */
function _tplGeneralNotice($d, $startFmt, $endFmt, $resumeFmt, $e) {
    $title = !empty($d['greeting']) ? nl2br($e($d['greeting'])) : '임시 휴무 안내';

    $periodHtml = '';
    if (!empty($startFmt) && !empty($endFmt)) {
        $periodHtml = <<<PERIOD
    <div style="background:#fff;border-radius:10px;padding:14px 20px;margin-top:18px;border:1px solid #E5E7EB;">
      <div style="font-size:11px;color:#6B7280;font-weight:700;letter-spacing:1px;margin-bottom:6px;">휴무기간</div>
      <div style="font-size:16px;font-weight:700;color:#1F2937;">{$startFmt} ~ {$endFmt}</div>
PERIOD;
        if (!empty($resumeFmt)) {
            $periodHtml .= "<div style=\"font-size:12px;color:#6B7280;margin-top:6px;\">{$resumeFmt} 부터 정상 운영</div>";
        }
        $periodHtml .= '</div>';
    }

    return <<<HTML
<div style="font-family:'Malgun Gothic','맑은 고딕',sans-serif;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#374151 0%,#1F2937 100%);padding:35px 30px 20px;text-align:center;position:relative;overflow:hidden;">
    <div style="font-size:12px;color:rgba(255,255,255,0.5);letter-spacing:4px;margin-bottom:6px;">NOTICE</div>
    <div style="font-size:42px;margin-bottom:10px;">📢</div>
    <div style="font-size:22px;font-weight:800;color:#fff;line-height:1.4;">{$title}</div>
  </div>
  <div style="background:#F9FAFB;padding:16px 30px 20px;text-align:center;">
    {$periodHtml}
    <div style="margin-top:14px;font-size:13px;color:#374151;font-weight:600;">{$e($d['company'])}</div>
    <div style="font-size:12px;color:#6B7280;margin-top:2px;">☎ {$e($d['phone'])}</div>
  </div>
</div>
HTML;
}
