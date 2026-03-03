<?php
/**
 * 홈페이지 리뉴얼 공지 팝업을 site_popups DB에 등록
 * 실행: php scripts/insert_welcome_popup.php
 */
require_once __DIR__ . '/../db.php';

// 이미 등록된 동일 팝업 체크
$check = mysqli_query($db, "SELECT id FROM site_popups WHERE title = '홈페이지 리뉴얼 안내'");
if ($check && mysqli_num_rows($check) > 0) {
    $row = mysqli_fetch_assoc($check);
    echo "⚠️  이미 등록되어 있습니다 (ID: {$row['id']}). 삭제 후 재등록합니다.\n";
    mysqli_query($db, "DELETE FROM site_popups WHERE title = '홈페이지 리뉴얼 안내'");
}

$htmlContent = <<<'HTML'
<div style="font-family:'Malgun Gothic','맑은 고딕',sans-serif;overflow:hidden;">
  <!-- 헤더 -->
  <div style="background:linear-gradient(135deg,#1a3a5c,#2d5a8e);color:#fff;padding:28px 28px 20px;text-align:center;">
    <h2 style="font-size:1.35rem;font-weight:700;margin:0 0 6px;line-height:1.4;color:#fff;">두손기획인쇄 홈페이지가<br>새 단장 했습니다!</h2>
    <p style="font-size:0.85rem;opacity:0.85;margin:0;color:#fff;">더 편리한 서비스를 위해 홈페이지를 새롭게 개편하였습니다</p>
  </div>

  <!-- 본문 -->
  <div style="padding:24px 28px;">
    <!-- 기존 회원 안내 -->
    <div style="margin-bottom:18px;">
      <div style="font-size:0.9rem;font-weight:700;color:#1a3a5c;margin:0 0 8px;display:flex;align-items:center;gap:6px;">🔑 기존 회원님께</div>
      <ul style="list-style:none;padding:0;margin:0;">
        <li style="font-size:0.85rem;color:#444;line-height:1.6;padding-left:18px;position:relative;">
          <span style="position:absolute;left:0;color:#10b981;font-weight:700;">✓</span>
          기존 아이디와 비밀번호 <strong>그대로 사용</strong> 가능합니다
        </li>
        <li style="font-size:0.85rem;color:#444;line-height:1.6;padding-left:18px;position:relative;">
          <span style="position:absolute;left:0;color:#10b981;font-weight:700;">✓</span>
          주문 내역도 모두 보존되어 있습니다
        </li>
      </ul>
    </div>

    <!-- 교정 확인 안내 (하이라이트) -->
    <div style="background:#fff8e1;border-left:3px solid #f59e0b;border-radius:0 8px 8px 0;padding:12px 14px;">
      <div style="font-size:0.9rem;font-weight:700;color:#b45309;margin:0 0 8px;display:flex;align-items:center;gap:6px;">교정 확인 방법 안내</div>
      <ul style="list-style:none;padding:0;margin:0;">
        <li style="font-size:0.85rem;color:#444;line-height:1.6;padding-left:22px;position:relative;">
          <span style="position:absolute;left:0;font-size:0.75rem;">🔒</span>
          교정(시안) 확인 시 <strong>본인 확인 절차</strong>가 추가되었습니다
        </li>
        <li style="font-size:0.85rem;color:#444;line-height:1.6;padding-left:22px;position:relative;">
          <span style="position:absolute;left:0;font-size:0.75rem;">🔒</span>
          주문 시 등록하신 <strong>전화번호 뒷자리 4자리</strong>를 입력하면 확인 가능합니다
        </li>
      </ul>
    </div>
  </div>
</div>
HTML;

$title = '홈페이지 리뉴얼 안내';
$contentType = 'template';
$templateType = null;
$templateData = null;
$imagePath = '';
$linkUrl = '';
$linkTarget = '_blank';
$startDate = date('Y-m-d');
$endDate = '2026-12-31';
$hideOption = 'month';
$sortOrder = 0;

$stmt = mysqli_prepare($db,
    "INSERT INTO site_popups (title, content_type, template_type, html_content, template_data, image_path, link_url, link_target, start_date, end_date, is_active, hide_option, sort_order)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "sssssssssssi",
    $title, $contentType, $templateType, $htmlContent, $templateData,
    $imagePath, $linkUrl, $linkTarget, $startDate, $endDate, $hideOption, $sortOrder
);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($db);
    echo "✅ 팝업 등록 완료! (ID: {$newId})\n";
    echo "   제목: {$title}\n";
    echo "   표시기간: {$startDate} ~ {$endDate}\n";
    echo "   안보기: 30일간\n";
    echo "\n";
    echo "📋 관리: /dashboard/popups/ 에서 수정/삭제 가능\n";
    echo "🌐 확인: http://localhost/ 에서 팝업 표시 확인\n";
} else {
    echo "❌ 등록 실패: " . mysqli_error($db) . "\n";
}

mysqli_stmt_close($stmt);
if (isset($db) && $db) { mysqli_close($db); }
