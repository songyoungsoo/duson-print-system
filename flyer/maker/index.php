<?php
require_once __DIR__ . '/templates/presets.php';
$grouped = getPresetsGrouped();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>전단지 만들기 - 두손기획인쇄</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Noto Sans KR',sans-serif;background:#f1f3f5;color:#333;min-height:100vh}
a{color:inherit;text-decoration:none}

/* Header */
.top-bar{background:#002B5B;color:#fff;padding:12px 24px;display:flex;align-items:center;justify-content:space-between}
.top-bar .brand{font-size:14px;font-weight:600;opacity:.85}
.top-bar .title{font-size:15px;font-weight:700}

/* Container */
.container{max-width:720px;margin:32px auto;padding:0 16px}

/* Hero */
.hero{text-align:center;margin-bottom:28px}
.hero h1{font-size:28px;font-weight:800;color:#002B5B;margin-bottom:6px}
.hero p{font-size:15px;color:#666}

/* Progress */
.progress{display:flex;justify-content:center;gap:8px;margin-bottom:32px}
.progress .step{display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;background:#e9ecef;color:#868e96;transition:all .3s}
.progress .step.active{background:#002B5B;color:#fff}
.progress .step.done{background:#20c997;color:#fff}
.progress .step .num{width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.25);display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700}

/* Card */
.card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);padding:32px;margin-bottom:20px}
.card h2{font-size:20px;font-weight:700;color:#002B5B;margin-bottom:4px}
.card .subtitle{font-size:13px;color:#868e96;margin-bottom:24px}

/* Form */
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:13px;font-weight:600;color:#495057;margin-bottom:6px}
.form-group label .req{color:#e03131;margin-left:2px}
.form-group input[type=text],
.form-group input[type=tel],
.form-group input[type=url],
.form-group textarea,
.form-group select{width:100%;padding:10px 14px;border:1.5px solid #dee2e6;border-radius:10px;font-size:14px;font-family:inherit;transition:border-color .2s;outline:none;background:#fff}
.form-group input:focus,.form-group textarea:focus,.form-group select:focus{border-color:#002B5B}
.form-group textarea{resize:vertical;min-height:70px}
.form-group .hint{font-size:11px;color:#adb5bd;margin-top:4px}
.form-group.error input,.form-group.error select,.form-group.error textarea{border-color:#e03131}
.form-group.error label{color:#e03131}

/* Two columns */
.row{display:flex;gap:14px}
.row .col{flex:1}

/* Industry selector */
.industry-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-top:8px}
.industry-card{border:2px solid #e9ecef;border-radius:12px;padding:14px 10px;text-align:center;cursor:pointer;transition:all .2s}
.industry-card:hover{border-color:#002B5B;background:#f8f9ff}
.industry-card.selected{border-color:#002B5B;background:#edf2ff;box-shadow:0 0 0 3px rgba(0,43,91,.15)}
.industry-card .icon{font-size:28px;margin-bottom:4px}
.industry-card .name{font-size:13px;font-weight:600;color:#333}

/* Category tabs */
.cat-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.cat-tab{padding:7px 16px;border-radius:20px;font-size:13px;font-weight:600;background:#f1f3f5;color:#666;cursor:pointer;border:none;transition:all .2s}
.cat-tab:hover{background:#e9ecef}
.cat-tab.active{background:#002B5B;color:#fff}

/* Menu items */
.menu-list{margin-top:8px}
.menu-row{display:flex;gap:8px;align-items:center;margin-bottom:8px}
.menu-row input[type=text]{flex:1;padding:9px 12px;border:1.5px solid #dee2e6;border-radius:8px;font-size:13px;font-family:inherit;outline:none}
.menu-row input[type=text]:focus{border-color:#002B5B}
.menu-row .price-input{width:120px;text-align:right}
.menu-row .unit{font-size:12px;color:#868e96;margin-left:-4px}
.menu-row .btn-del{width:32px;height:32px;border:none;background:#fee2e2;color:#e03131;border-radius:8px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center}
.menu-row .btn-del:hover{background:#fca5a5}
.btn-add-menu{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1.5px dashed #adb5bd;border-radius:10px;background:none;color:#495057;font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;margin-top:4px}
.btn-add-menu:hover{border-color:#002B5B;color:#002B5B}

/* Feature inputs */
.feature-row{display:flex;gap:8px;align-items:center;margin-bottom:8px}
.feature-row .badge{width:26px;height:26px;border-radius:50%;background:#002B5B;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
.feature-row input{flex:1;padding:9px 12px;border:1.5px solid #dee2e6;border-radius:8px;font-size:13px;font-family:inherit;outline:none}
.feature-row input:focus{border-color:#002B5B}

/* File upload */
.file-zone{border:2px dashed #dee2e6;border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:all .2s;position:relative;overflow:hidden}
.file-zone:hover{border-color:#002B5B;background:#f8f9ff}
.file-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer}
.file-zone .fz-icon{font-size:28px;margin-bottom:4px;color:#adb5bd}
.file-zone .fz-text{font-size:13px;color:#868e96}
.file-zone .fz-text strong{color:#002B5B}

/* Preview thumbnails */
.previews{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.previews .thumb{width:80px;height:80px;border-radius:8px;object-fit:cover;border:2px solid #e9ecef}

/* Buttons */
.btn-row{display:flex;justify-content:space-between;margin-top:28px}
.btn{padding:12px 32px;border-radius:12px;font-size:15px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:all .2s}
.btn-prev{background:#e9ecef;color:#495057}
.btn-prev:hover{background:#dee2e6}
.btn-next{background:#002B5B;color:#fff}
.btn-next:hover{background:#001d3d}
.btn-submit{background:#20c997;color:#fff;font-size:16px;padding:14px 40px}
.btn-submit:hover{background:#12b886}

/* === Generation Options === */
.generation-options{margin:24px 0;text-align:center}
.generation-options p{font-size:14px;color:#666;margin-bottom:15px}
.generation-options .gen-buttons{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.btn-generate-quick,.btn-generate-premium{padding:16px 28px;border-radius:14px;font-size:15px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:all .3s;min-width:200px}
.btn-generate-quick{background:#20c997;color:#fff;box-shadow:0 4px 14px rgba(32,201,151,.3)}
.btn-generate-quick:hover{background:#12b886;transform:translateY(-1px);box-shadow:0 6px 20px rgba(32,201,151,.4)}
.btn-generate-premium{background:linear-gradient(135deg,#6c5ce7 0%,#a855f7 100%);color:#fff;box-shadow:0 4px 14px rgba(168,85,247,.3)}
.btn-generate-premium:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(168,85,247,.4)}
.btn-generate-quick:disabled,.btn-generate-premium:disabled{opacity:.6;cursor:not-allowed;transform:none;box-shadow:none}
.btn-generate-quick .sub,.btn-generate-premium .sub{display:block;font-size:12px;font-weight:400;opacity:.85;margin-top:4px}

/* === Premium Progress Overlay === */
.premium-progress-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center}
.premium-progress-overlay.visible{display:flex}
.premium-progress-card{background:#fff;border-radius:20px;padding:40px 36px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3);animation:slideUp .4s ease}
@keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
.premium-progress-card .progress-icon{font-size:48px;margin-bottom:12px;animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}
.premium-progress-card h3{font-size:18px;font-weight:700;color:#002B5B;margin-bottom:8px}
.premium-progress-card .progress-msg{font-size:14px;color:#666;margin-bottom:20px;min-height:20px}
.progress-bar-container{width:100%;height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;margin-bottom:8px}
.progress-bar-container .progress-bar-fill{height:100%;background:linear-gradient(90deg,#6c5ce7,#a855f7);border-radius:4px;transition:width .5s ease;width:0}
.premium-progress-card .progress-pct{font-size:13px;font-weight:600;color:#6c5ce7}
.premium-progress-card .progress-hint{font-size:12px;color:#999;margin-top:15px}
.premium-progress-card .btn-cancel-premium{margin-top:16px;padding:8px 20px;border:1.5px solid #dee2e6;border-radius:8px;background:none;color:#868e96;font-size:13px;cursor:pointer;font-family:inherit}
.premium-progress-card .btn-cancel-premium:hover{border-color:#e03131;color:#e03131}

/* === Premium Result === */
.premium-result{display:none;margin:24px 0;text-align:center}
.premium-result.visible{display:block}
.premium-result-card{background:linear-gradient(135deg,#f0f0ff 0%,#f8f0ff 100%);border:1.5px solid #d4c5f9;border-radius:16px;padding:28px;text-align:center}
.premium-result-card .result-icon{font-size:48px;margin-bottom:8px}
.premium-result-card h3{font-size:18px;font-weight:700;color:#002B5B;margin-bottom:8px}
.premium-result-card p{font-size:14px;color:#666;margin-bottom:16px}
.premium-result-card .result-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.btn-download{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;border-radius:12px;font-size:15px;font-weight:700;border:none;cursor:pointer;font-family:inherit;background:linear-gradient(135deg,#6c5ce7 0%,#a855f7 100%);color:#fff;box-shadow:0 4px 14px rgba(168,85,247,.3);text-decoration:none;transition:all .3s}
.btn-download:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(168,85,247,.4)}
.btn-retry{display:inline-flex;align-items:center;gap:6px;padding:12px 24px;border-radius:12px;font-size:14px;font-weight:600;border:1.5px solid #dee2e6;background:#fff;color:#495057;cursor:pointer;font-family:inherit;transition:all .2s}
.btn-retry:hover{border-color:#002B5B;color:#002B5B}

@media(max-width:600px){
  .generation-options .gen-buttons{flex-direction:column;align-items:center}
  .btn-generate-quick,.btn-generate-premium{min-width:0;width:100%;max-width:280px}
  .premium-progress-card{padding:28px 20px}
  .premium-result-card .result-actions{flex-direction:column;align-items:center}
}

/* === AI 버튼 === */
.ai-section{background:linear-gradient(135deg,#f0f4ff 0%,#e8f0fe 100%);border:1.5px solid #c8d6e5;border-radius:14px;padding:20px;margin-bottom:20px;text-align:center}
.ai-section h3{font-size:15px;font-weight:700;color:#002B5B;margin-bottom:4px}
.ai-section p{font-size:12px;color:#868e96;margin-bottom:14px}
.ai-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:all .3s;background:linear-gradient(135deg,#002B5B 0%,#1a4a7a 100%);color:#fff;box-shadow:0 4px 14px rgba(0,43,91,.3)}
.ai-btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,43,91,.4)}
.ai-btn:disabled{opacity:.6;cursor:not-allowed;transform:none;box-shadow:none}
.ai-btn .spinner{display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
.ai-btn.loading .spinner{display:inline-block}
.ai-btn.loading .btn-text{display:none}
@keyframes spin{to{transform:rotate(360deg)}}
.ai-notice{margin-top:10px;padding:10px 14px;border-radius:8px;font-size:12px;display:none}
.ai-notice.success{display:block;background:#d3f9d8;color:#2b8a3e;border:1px solid #b2f2bb}
.ai-notice.error{display:block;background:#ffe0e0;color:#c92a2a;border:1px solid #ffa8a8}
.ai-image-wrap{margin-top:12px;display:none}
.ai-image-wrap img{max-width:100%;max-height:300px;border-radius:12px;border:2px solid #dee2e6;object-fit:cover}
.ai-image-wrap .ai-img-actions{margin-top:8px;display:flex;gap:8px;justify-content:center}
.ai-image-wrap .ai-img-actions button{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid #dee2e6;background:#fff;color:#495057;font-family:inherit}
.ai-image-wrap .ai-img-actions button:hover{border-color:#002B5B;color:#002B5B}

/* Responsive */
@media(max-width:600px){
  .container{padding:0 10px;margin:16px auto}
  .card{padding:20px 16px}
  .row{flex-direction:column;gap:0}
  .industry-grid{grid-template-columns:repeat(2,1fr)}
  .hero h1{font-size:22px}
  .progress .step{padding:6px 10px;font-size:11px}
}
</style>
</head>
<body>

<div class="top-bar">
    <span class="brand">두손기획인쇄</span>
    <span class="title">전단지 만들기</span>
</div>

<div class="container">
    <div class="hero">
        <h1>내 사업체 전단지 만들기</h1>
        <p>업종에 맞는 디자인으로 A4 홍보 전단지를 바로 만들어보세요</p>
    </div>

    <!-- Progress -->
    <div class="progress">
        <div class="step active" data-step="1"><span class="num">1</span> 업종</div>
        <div class="step" data-step="2"><span class="num">2</span> 기본정보</div>
        <div class="step" data-step="3"><span class="num">3</span> 콘텐츠</div>
        <div class="step" data-step="4"><span class="num">4</span> 이미지</div>
    </div>

    <form id="flyerForm" method="POST" action="generate.php" enctype="multipart/form-data">

    <!-- STEP 1: 업종 선택 -->
    <div class="card step-panel" id="step1">
        <h2>업종을 선택하세요</h2>
        <p class="subtitle">업종에 맞는 색상과 레이아웃이 자동 적용됩니다</p>
        <input type="hidden" name="industry_key" id="industryKey" value="">
        <input type="hidden" name="subtitle" id="subtitle" value="">
        <input type="hidden" name="industry_label" id="industryLabel" value="">

        <div class="cat-tabs" id="catTabs">
            <?php $first = true; foreach ($grouped as $cat => $items): ?>
            <button type="button" class="cat-tab <?php echo $first ? 'active' : ''; ?>" data-cat="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></button>
            <?php $first = false; endforeach; ?>
        </div>

        <div id="industryCards">
        <?php foreach ($grouped as $cat => $items): ?>
            <div class="industry-grid cat-group" data-cat="<?php echo htmlspecialchars($cat); ?>">
            <?php foreach ($items as $key => $preset): ?>
                <div class="industry-card" data-key="<?php echo htmlspecialchars($key); ?>"
                     data-tagline="<?php echo htmlspecialchars($preset['defaultTagline']); ?>"
                     data-hints="<?php echo htmlspecialchars(implode('|', $preset['featureHints'])); ?>"
                     data-menu-label="<?php echo htmlspecialchars($preset['menuLabel']); ?>"
                     data-label="<?php echo htmlspecialchars($preset['label']); ?>">
                    <div class="icon"><?php echo $preset['icon']; ?></div>
                    <div class="name"><?php echo htmlspecialchars($preset['label']); ?></div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="btn-row">
            <div></div>
            <button type="button" class="btn btn-next" onclick="goStep(2)">다음 →</button>
        </div>
    </div>

    <!-- STEP 2: 기본정보 -->
    <div class="card step-panel" id="step2" style="display:none">
        <h2>사업체 기본 정보</h2>
        <p class="subtitle">전단지에 표시될 기본 정보를 입력하세요</p>

        <div class="form-group">
            <label>상호명 <span class="req">*</span></label>
            <input type="text" name="business_name" id="businessName" placeholder="예: 맛나분식">
        </div>

        <!-- AI 카피라이터 -->
        <div class="ai-section" id="aiCopySection">
            <h3>✨ AI 카피라이터</h3>
            <p>상호명만 입력하면 AI가 전단지 문구를 자동 생성합니다 (캐치프레이즈, 특장점, 메뉴, 프로모션, 영업시간)</p>
            <button type="button" class="ai-btn" id="aiCopyBtn" onclick="generateAIContent()">
                <span class="btn-text">✨ AI로 전단지 문구 자동 생성</span>
                <span class="spinner"></span>
            </button>
            <div class="ai-notice" id="aiCopyNotice"></div>
        </div>

        <div class="form-group">
            <label>캐치프레이즈</label>
            <input type="text" name="tagline" id="tagline" placeholder="예: 정성을 담은 한 상">
            <div class="hint">비워두면 업종 기본 문구가 적용됩니다</div>
        </div>

        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>전화번호 <span class="req">*</span></label>
                    <input type="tel" name="phone" id="phone" placeholder="02-1234-5678">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>영업시간</label>
                    <input type="text" name="hours" placeholder="매일 11:00~22:00">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>주소 <span class="req">*</span></label>
            <input type="text" name="address" id="address" placeholder="서울시 영등포구 당산로 123">
        </div>

        <div class="form-group">
            <label>웹사이트 / SNS 주소</label>
            <input type="url" name="website_url" placeholder="https://instagram.com/my_store">
            <div class="hint">입력하면 QR코드가 자동 생성됩니다</div>
        </div>

        <div class="btn-row">
            <button type="button" class="btn btn-prev" onclick="goStep(1)">← 이전</button>
            <button type="button" class="btn btn-next" onclick="goStep(3)">다음 →</button>
        </div>
    </div>

    <!-- STEP 3: 콘텐츠 -->
    <div class="card step-panel" id="step3" style="display:none">
        <h2>콘텐츠 입력</h2>
        <p class="subtitle">전단지에 들어갈 내용을 채워주세요</p>

        <!-- 특장점 -->
        <div class="form-group">
            <label>특장점 / 강점 <span class="req">*</span> (1~3개)</label>
            <div class="feature-row"><span class="badge">1</span><input type="text" name="features[]" id="feat1" placeholder="예: 30년 전통 비법 양념"></div>
            <div class="feature-row"><span class="badge">2</span><input type="text" name="features[]" id="feat2" placeholder="예: 국내산 재료만 사용"></div>
            <div class="feature-row"><span class="badge">3</span><input type="text" name="features[]" id="feat3" placeholder="예: 넉넉한 인심"></div>
        </div>

        <!-- 메뉴/서비스 -->
        <div class="form-group">
            <label id="menuLabel">메뉴 / 서비스 목록</label>
            <div class="menu-list" id="menuList">
                <div class="menu-row">
                    <input type="text" name="menu_name[]" placeholder="항목 이름">
                    <input type="text" name="menu_price[]" class="price-input" placeholder="가격">
                    <span class="unit">원</span>
                    <button type="button" class="btn-del" onclick="removeMenu(this)" title="삭제">&times;</button>
                </div>
            </div>
            <button type="button" class="btn-add-menu" onclick="addMenu()">+ 항목 추가</button>
            <div class="hint">최대 12개까지 앞면에 표시됩니다. 초과 시 뒷면에 이어집니다.</div>
        </div>

        <!-- 프로모션 -->
        <div class="form-group">
            <label>프로모션 / 할인 정보</label>
            <textarea name="promotion" placeholder="예: 오픈 기념 전 메뉴 20% 할인! (3월 한정)"></textarea>
        </div>

        <div class="btn-row">
            <button type="button" class="btn btn-prev" onclick="goStep(2)">← 이전</button>
            <button type="button" class="btn btn-next" onclick="goStep(4)">다음 →</button>
        </div>
    </div>

    <!-- STEP 4: 이미지 -->
    <div class="card step-panel" id="step4" style="display:none">
        <h2>이미지 업로드</h2>
        <p class="subtitle">로고나 사진이 있으면 전단지가 더 풍성해집니다 (선택사항)</p>

        <!-- AI 이미지 생성 -->
        <div class="ai-section" id="aiImageSection">
            <h3>🎨 AI 이미지 생성</h3>
            <p>업종에 맞는 홍보용 이미지를 AI가 자동 생성합니다 (Nano Banana Pro)</p>
            <button type="button" class="ai-btn" id="aiImageBtn" onclick="generateAIImage()">
                <span class="btn-text">🎨 AI 이미지 생성하기</span>
                <span class="spinner"></span>
            </button>
            <div class="ai-notice" id="aiImageNotice"></div>
            <div class="ai-image-wrap" id="aiImageWrap">
                <img id="aiImagePreview" src="" alt="AI 생성 이미지">
                <input type="hidden" name="ai_image" id="aiImagePath" value="">
                <div class="ai-img-actions">
                    <button type="button" onclick="generateAIImage()">🔄 다시 생성</button>
                    <button type="button" onclick="removeAIImage()">❌ 제거</button>
                </div>
            </div>
        </div>

        <!-- 로고 -->
        <div class="form-group">
            <label>로고 이미지</label>
            <div class="file-zone" id="logoZone">
                <input type="file" name="logo" accept="image/jpeg,image/png,image/gif" onchange="previewFile(this,'logoPreview')">
                <div class="fz-icon">🏢</div>
                <div class="fz-text">클릭하여 <strong>로고</strong>를 업로드하세요 (JPG/PNG, 5MB 이하)</div>
            </div>
            <div class="previews" id="logoPreview"></div>
        </div>

        <!-- 사진 -->
        <div class="form-group">
            <label>사업체 사진 (최대 4장)</label>
            <div class="file-zone" id="photoZone">
                <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/gif" onchange="previewFiles(this,'photoPreview')">
                <div class="fz-icon">📷</div>
                <div class="fz-text">클릭하여 <strong>사진</strong>을 업로드하세요 (최대 4장)</div>
            </div>
            <div class="previews" id="photoPreview"></div>
            <div class="hint">매장, 음식, 시공사례 등 홍보에 도움되는 사진</div>
        </div>

        <!-- 약도 -->
        <div class="form-group">
            <label>약도 / 지도 이미지</label>
            <div class="file-zone" id="mapZone">
                <input type="file" name="map_image" accept="image/jpeg,image/png,image/gif" onchange="previewFile(this,'mapPreview')">
                <div class="fz-icon">🗺️</div>
                <div class="fz-text">클릭하여 <strong>약도</strong>를 업로드하세요</div>
            </div>
            <div class="previews" id="mapPreview"></div>
            <div class="hint">네이버/카카오맵 캡처 또는 직접 그린 약도</div>
        </div>

        <div class="btn-row" style="margin-bottom:0">
            <button type="button" class="btn btn-prev" onclick="goStep(3)">← 이전</button>
            <div></div>
        </div>

        <!-- Generation Options -->
        <div class="generation-options" id="generationOptions">
            <p>생성 방식을 선택하세요</p>
            <!-- 단면/양면 선택 -->
            <div id="sidesOptionWrap" style="text-align:center; margin-bottom:15px; display:none;">
                <label style="display:inline-block; margin:0 10px; cursor:pointer; padding:8px 16px; border:2px solid #4A90D9; border-radius:8px; background-color:#f0f7ff;">
                    <input type="radio" name="page_sides" value="single" checked
                           onchange="updateSidesOption(this.value)"
                           style="margin-right:5px;">
                    단면 (앞면만)
                    <span style="display:block; font-size:11px; color:#888;">빠른 생성 ~1분</span>
                </label>
                <label style="display:inline-block; margin:0 10px; cursor:pointer; padding:8px 16px; border:2px solid #ddd; border-radius:8px;">
                    <input type="radio" name="page_sides" value="double"
                           onchange="updateSidesOption(this.value)"
                           style="margin-right:5px;">
                    양면 (앞+뒤)
                    <span style="display:block; font-size:11px; color:#888;">~2분 소요</span>
                </label>
            </div>
            <div class="gen-buttons">
                <button type="button" id="btn-generate-quick" class="btn-generate-quick" onclick="generateFlyer('quick')">
                    ⚡ 빠른 생성
                    <span class="sub">텍스트 위주 (~30초)</span>
                </button>
                <button type="button" id="btn-generate-premium" class="btn-generate-premium" onclick="generateFlyer('premium')">
                    🎨 프리미엄 생성
                    <span class="sub" id="premium-time-hint">AI 이미지 포함 (~1분)</span>
                </button>
            </div>
        </div>

        <!-- Premium Result (hidden) -->
        <div class="premium-result" id="premiumResult">
            <div class="premium-result-card">
                <div class="result-icon">✅</div>
                <h3>프리미엄 전단지 완성!</h3>
                <p>AI가 생성한 이미지가 포함된 고품질 전단지입니다</p>
                <div class="result-actions">
                    <a href="#" id="premiumDownloadLink" class="btn-download" target="_blank">📥 PDF 다운로드</a>
                    <button type="button" class="btn-retry" onclick="resetPremiumResult()">🔄 다시 만들기</button>
                </div>
            </div>
        </div>

    </div>

    </form>
</div>

<script>
let currentStep = 1;
const totalSteps = 4;

function goStep(n) {
    // Validate before advancing
    if (n > currentStep && !validateStep(currentStep)) return;

    document.getElementById('step' + currentStep).style.display = 'none';
    document.getElementById('step' + n).style.display = 'block';

    document.querySelectorAll('.progress .step').forEach(el => {
        const s = parseInt(el.dataset.step);
        el.classList.remove('active', 'done');
        if (s < n) el.classList.add('done');
        else if (s === n) el.classList.add('active');
    });

    currentStep = n;
    // Show sides option on step 4 (generation)
    if (n === 4) showSidesOption();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
    let valid = true;
    if (step === 1) {
        if (!document.getElementById('industryKey').value) {
            alert('업종을 선택해주세요.');
            return false;
        }
    }
    if (step === 2) {
        ['businessName', 'phone', 'address'].forEach(id => {
            const el = document.getElementById(id);
            const fg = el.closest('.form-group');
            if (!el.value.trim()) {
                fg.classList.add('error');
                valid = false;
            } else {
                fg.classList.remove('error');
            }
        });
        if (!valid) alert('필수 항목을 입력해주세요.');
    }
    if (step === 3) {
        const f1 = document.getElementById('feat1');
        const fg = f1.closest('.form-group');
        if (!f1.value.trim()) {
            fg.classList.add('error');
            alert('특장점을 최소 1개 입력해주세요.');
            valid = false;
        } else {
            fg.classList.remove('error');
        }
    }
    return valid;
}

// Industry selection
document.querySelectorAll('.cat-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.cat;
        document.querySelectorAll('.cat-group').forEach(g => {
            g.style.display = g.dataset.cat === cat ? 'grid' : 'none';
        });
    });
});
// Show first category only
document.querySelectorAll('.cat-group').forEach((g, i) => {
    if (i > 0) g.style.display = 'none';
});

document.querySelectorAll('.industry-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.industry-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('industryKey').value = this.dataset.key;
        document.getElementById('industryLabel').value = this.dataset.label || '';

        // Pre-fill tagline
        const tl = document.getElementById('tagline');
        if (!tl.value.trim() && this.dataset.tagline) {
            tl.value = this.dataset.tagline;
        }

        // Pre-fill feature hints
        const hints = this.dataset.hints ? this.dataset.hints.split('|') : [];
        for (let i = 0; i < 3; i++) {
            const el = document.getElementById('feat' + (i + 1));
            if (el && !el.value.trim() && hints[i]) {
                el.placeholder = '예: ' + hints[i];
            }
        }

        // Update menu label
        if (this.dataset.menuLabel) {
            document.getElementById('menuLabel').textContent = this.dataset.menuLabel + ' 목록';
        }
    });
});

// Menu items
function addMenu() {
    const list = document.getElementById('menuList');
    if (list.children.length >= 20) { alert('최대 20개까지 추가할 수 있습니다.'); return; }
    const row = document.createElement('div');
    row.className = 'menu-row';
    row.innerHTML = '<input type="text" name="menu_name[]" placeholder="항목 이름">' +
        '<input type="text" name="menu_price[]" class="price-input" placeholder="가격">' +
        '<span class="unit">원</span>' +
        '<button type="button" class="btn-del" onclick="removeMenu(this)" title="삭제">&times;</button>';
    list.appendChild(row);
}

function removeMenu(btn) {
    const list = document.getElementById('menuList');
    if (list.children.length <= 1) return;
    btn.closest('.menu-row').remove();
}

// File previews
function previewFile(input, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'thumb';
            container.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewFiles(input, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    if (!input.files) return;
    const max = 4;
    for (let i = 0; i < Math.min(input.files.length, max); i++) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'thumb';
            container.appendChild(img);
        };
        reader.readAsDataURL(input.files[i]);
    }
    if (input.files.length > max) {
        alert('최대 ' + max + '장까지 업로드 가능합니다. 처음 ' + max + '장만 사용됩니다.');
    }
}

// Error from generate.php redirect
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('error')) {
    alert('오류: ' + decodeURIComponent(urlParams.get('error')));
}

// ═══════════════════════════════════════════════
//  AI 카피라이터 — 텍스트 생성
// ═══════════════════════════════════════════════
function generateAIContent() {
    const industryKey = document.getElementById('industryKey').value;
    const industryLabel = document.getElementById('industryLabel').value;
    const businessName = document.getElementById('businessName').value.trim();
    const btn = document.getElementById('aiCopyBtn');
    const notice = document.getElementById('aiCopyNotice');

    if (!industryKey) { alert('업종을 먼저 선택해주세요 (1단계).'); return; }
    if (!businessName) { alert('상호명을 입력해주세요.'); document.getElementById('businessName').focus(); return; }

    btn.classList.add('loading');
    btn.disabled = true;
    notice.className = 'ai-notice';
    notice.style.display = 'none';

    const formData = new FormData();
    formData.append('industry_key', industryKey);
    formData.append('industry_label', industryLabel);
    formData.append('business_name', businessName);

    fetch('api/generate_content.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.classList.remove('loading');
            btn.disabled = false;

            if (data.error) {
                notice.className = 'ai-notice error';
                notice.textContent = '❌ ' + data.error;
                return;
            }

            if (data.success && data.data) {
                applyAIContent(data.data);
                notice.className = 'ai-notice success';
                notice.textContent = '✅ AI가 전단지 문구를 생성했습니다! 다음 단계에서 확인하세요.';
            }
        })
        .catch(err => {
            btn.classList.remove('loading');
            btn.disabled = false;
            notice.className = 'ai-notice error';
            notice.textContent = '❌ 네트워크 오류가 발생했습니다. 다시 시도해주세요.';
        });
}

function applyAIContent(data) {
    // 캐치프레이즈
    if (data.tagline) {
        document.getElementById('tagline').value = data.tagline;
    }

    // 부제목/소개 문구
    if (data.subtitle) {
        document.getElementById('subtitle').value = data.subtitle;
    }

    // 영업시간
    if (data.hours) {
        const hoursInput = document.querySelector('input[name="hours"]');
        if (hoursInput) hoursInput.value = data.hours;
    }

    // 특장점
    if (data.features && Array.isArray(data.features)) {
        for (let i = 0; i < Math.min(data.features.length, 3); i++) {
            const el = document.getElementById('feat' + (i + 1));
            if (el) el.value = data.features[i];
        }
    }

    // 메뉴
    if (data.menu && Array.isArray(data.menu)) {
        const menuList = document.getElementById('menuList');
        menuList.innerHTML = '';
        data.menu.forEach(item => {
            const row = document.createElement('div');
            row.className = 'menu-row';
            row.innerHTML = '<input type="text" name="menu_name[]" value="' + escapeHtml(item.name || '') + '" placeholder="항목 이름">' +
                '<input type="text" name="menu_price[]" class="price-input" value="' + escapeHtml(item.price || '') + '" placeholder="가격">' +
                '<span class="unit">원</span>' +
                '<button type="button" class="btn-del" onclick="removeMenu(this)" title="삭제">&times;</button>';
            menuList.appendChild(row);
        });
    }

    // 프로모션
    if (data.promotion) {
        const promoEl = document.querySelector('textarea[name="promotion"]');
        if (promoEl) promoEl.value = data.promotion;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ═══════════════════════════════════════════════
//  AI 이미지 생성
// ═══════════════════════════════════════════════
function generateAIImage() {
    const industryKey = document.getElementById('industryKey').value;
    const industryLabel = document.getElementById('industryLabel').value;
    const businessName = document.getElementById('businessName').value.trim();
    const btn = document.getElementById('aiImageBtn');
    const notice = document.getElementById('aiImageNotice');
    const wrap = document.getElementById('aiImageWrap');

    if (!industryKey) { alert('업종을 먼저 선택해주세요 (1단계).'); return; }
    if (!businessName) { alert('상호명을 먼저 입력해주세요 (2단계).'); return; }

    btn.classList.add('loading');
    btn.disabled = true;
    notice.className = 'ai-notice';
    notice.style.display = 'none';

    const formData = new FormData();
    formData.append('industry_key', industryKey);
    formData.append('industry_label', industryLabel);
    formData.append('business_name', businessName);
    formData.append('image_type', 'hero');

    fetch('api/generate_image.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.classList.remove('loading');
            btn.disabled = false;

            if (data.error) {
                notice.className = 'ai-notice error';
                notice.textContent = '❌ ' + data.error;
                return;
            }

            if (data.success && data.url) {
                document.getElementById('aiImagePreview').src = data.url;
                document.getElementById('aiImagePath').value = data.filename;
                wrap.style.display = 'block';
                notice.className = 'ai-notice success';
                notice.textContent = '✅ AI 이미지가 생성되었습니다!';
            }
        })
        .catch(err => {
            btn.classList.remove('loading');
            btn.disabled = false;
            notice.className = 'ai-notice error';
            notice.textContent = '❌ 네트워크 오류가 발생했습니다. 다시 시도해주세요.';
        });
}

function removeAIImage() {
    document.getElementById('aiImageWrap').style.display = 'none';
    document.getElementById('aiImagePreview').src = '';
    document.getElementById('aiImagePath').value = '';
    document.getElementById('aiImageNotice').className = 'ai-notice';
    document.getElementById('aiImageNotice').style.display = 'none';
}

// ═══════════════════════════════════════════════
//  Premium Progress Overlay (injected after form)
// ═══════════════════════════════════════════════
(function() {
    var overlay = document.createElement('div');
    overlay.id = 'premium-progress-overlay';
    overlay.className = 'premium-progress-overlay';
    overlay.innerHTML =
        '<div class="premium-progress-card">' +
            '<div class="progress-icon" id="progress-icon">🎨</div>' +
            '<h3 id="progress-title">프리미엄 전단지 생성 중</h3>' +
            '<p class="progress-msg" id="progress-message">준비 중...</p>' +
            '<div class="progress-bar-container">' +
                '<div class="progress-bar-fill" id="progress-bar"></div>' +
            '</div>' +
            '<p class="progress-pct" id="progress-percent">0%</p>' +
            '<p class="progress-hint" id="progress-hint">AI가 이미지를 생성하고 있습니다. 잠시만 기다려주세요.</p>' +
            '<button type="button" class="btn-cancel-premium" id="btn-cancel-premium" onclick="cancelPremiumGeneration()">취소</button>' +
        '</div>';
    document.body.appendChild(overlay);
})();

var premiumAbortController = null;

// ═══════════════════════════════════════════════
//  Sides Option (단면/양면)
// ═══════════════════════════════════════════════
function updateSidesOption(value) {
    var radios = document.querySelectorAll('input[name="page_sides"]');
    for (var i = 0; i < radios.length; i++) {
        var parentLabel = radios[i].closest('label');
        if (radios[i].value === value) {
            parentLabel.style.borderColor = '#4A90D9';
            parentLabel.style.backgroundColor = '#f0f7ff';
        } else {
            parentLabel.style.borderColor = '#ddd';
            parentLabel.style.backgroundColor = '';
        }
    }
    // Update premium button time hint
    var hintEl = document.getElementById('premium-time-hint');
    if (hintEl) {
        hintEl.textContent = value === 'double' ? 'AI 이미지 포함 (~2분)' : 'AI 이미지 포함 (~1분)';
    }
}

function showSidesOption() {
    var wrap = document.getElementById('sidesOptionWrap');
    if (wrap) wrap.style.display = '';
}
function hideSidesOption() {
    var wrap = document.getElementById('sidesOptionWrap');
    if (wrap) wrap.style.display = 'none';
}


// ═══════════════════════════════════════════════
//  Generation Router
// ═══════════════════════════════════════════════
function generateFlyer(mode) {
    if (mode === 'quick') {
        hideSidesOption();
        generateFlyerQuick();
        return;
    }
    generateFlyerPremium();
}

// ═══════════════════════════════════════════════
//  Quick Generation (existing form submit)
// ═══════════════════════════════════════════════
function generateFlyerQuick() {
    // Validate all steps
    if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
        return;
    }
    // Submit the form as before
    document.getElementById('flyerForm').submit();
}

// ═══════════════════════════════════════════════
//  Premium Generation (SSE via fetch)
// ═══════════════════════════════════════════════
function generateFlyerPremium() {
    // Validate all steps
    if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
        return;
    }

    var formData = collectFormData();
    showPremiumProgress();

    premiumAbortController = new AbortController();

    fetch('api/generate_premium.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(formData),
        signal: premiumAbortController.signal
    }).then(function(response) {
        if (!response.ok) {
            throw new Error('서버 오류 (HTTP ' + response.status + ')');
        }
        var reader = response.body.getReader();
        var decoder = new TextDecoder();
        var buffer = '';

        function read() {
            return reader.read().then(function(result) {
                if (result.done) {
                    // Process any remaining buffer
                    if (buffer.trim()) {
                        processSSEBuffer(buffer);
                    }
                    return;
                }
                buffer += decoder.decode(result.value, {stream: true});

                // Process complete SSE events (separated by double newline)
                var parts = buffer.split('\n\n');
                // Keep the last incomplete part in buffer
                buffer = parts.pop() || '';

                for (var i = 0; i < parts.length; i++) {
                    processSSEBuffer(parts[i]);
                }

                return read();
            });
        }
        return read();
    }).catch(function(error) {
        if (error.name === 'AbortError') {
            // User cancelled
            hidePremiumProgress();
            return;
        }
        hidePremiumProgress();
        alert('생성 중 오류가 발생했습니다: ' + error.message);
    });
}

function processSSEBuffer(text) {
    var lines = text.split('\n');
    for (var i = 0; i < lines.length; i++) {
        var line = lines[i].trim();
        if (line.indexOf('data: ') === 0) {
            try {
                var data = JSON.parse(line.substring(6));
                handleSSEEvent(data);
            } catch (e) {
                // Skip invalid JSON
            }
        }
    }
}

function handleSSEEvent(data) {
    if (data.type === 'progress') {
        updateProgress(data.message, data.progress, data.stage);
    } else if (data.type === 'complete') {
        hidePremiumProgress();
        if (data.success && data.pdf_url) {
            showPremiumResult(data.pdf_url, data.preview_images);
        } else {
            alert(data.error || '생성에 실패했습니다.');
        }
    } else if (data.type === 'error') {
        hidePremiumProgress();
        alert(data.message || '생성 중 오류가 발생했습니다.');
    }
}

// ═══════════════════════════════════════════════
//  Progress UI
// ═══════════════════════════════════════════════
function showPremiumProgress() {
    var overlay = document.getElementById('premium-progress-overlay');
    overlay.classList.add('visible');
    // Update hint based on sides selection
    var sidesEl = document.querySelector('input[name="page_sides"]:checked');
    var isDouble = sidesEl && sidesEl.value === 'double';
    var hintEl = document.getElementById('progress-hint');
    if (hintEl) {
        hintEl.textContent = isDouble
            ? 'AI가 앞면+뒷면 이미지를 생성합니다. 약 2분 소요됩니다.'
            : 'AI가 앞면 이미지를 생성합니다. 약 1분 소요됩니다.';
    }
    updateProgress('준비 중...', 0, 'init');
    // Disable buttons
    var btnQ = document.getElementById('btn-generate-quick');
    var btnP = document.getElementById('btn-generate-premium');
    if (btnQ) btnQ.disabled = true;
    if (btnP) btnP.disabled = true;
}

function hidePremiumProgress() {
    var overlay = document.getElementById('premium-progress-overlay');
    overlay.classList.remove('visible');
    // Re-enable buttons
    var btnQ = document.getElementById('btn-generate-quick');
    var btnP = document.getElementById('btn-generate-premium');
    if (btnQ) btnQ.disabled = false;
    if (btnP) btnP.disabled = false;
}

function updateProgress(message, percent, stage) {
    var msgEl = document.getElementById('progress-message');
    var barEl = document.getElementById('progress-bar');
    var pctEl = document.getElementById('progress-percent');
    var iconEl = document.getElementById('progress-icon');

    if (msgEl) msgEl.textContent = message;
    if (barEl) barEl.style.width = Math.min(100, Math.max(0, percent)) + '%';
    if (pctEl) pctEl.textContent = Math.round(percent) + '%';

    var icons = {
        'init': '🚀',
        'collector': '📋',
        'copywriter': '✍️',
        'designer': '🎨',
        'prompter': '🖼️',
        'image_generation': '🖼️',
        'assembler': '📄',
        'complete': '✅'
    };
    if (iconEl && icons[stage]) {
        iconEl.textContent = icons[stage];
    }
}

function cancelPremiumGeneration() {
    if (premiumAbortController) {
        premiumAbortController.abort();
        premiumAbortController = null;
    }
    hidePremiumProgress();
}

// ═══════════════════════════════════════════════
//  Premium Result
// ═══════════════════════════════════════════════
function showPremiumResult(pdfUrl, previewImages) {
    // Hide generation options
    var genOpts = document.getElementById('generationOptions');
    if (genOpts) genOpts.style.display = 'none';

    // Show result card
    var resultDiv = document.getElementById('premiumResult');
    resultDiv.classList.add('visible');

    var downloadLink = document.getElementById('premiumDownloadLink');
    downloadLink.href = pdfUrl;
}

function resetPremiumResult() {
    // Show generation options
    var genOpts = document.getElementById('generationOptions');
    if (genOpts) genOpts.style.display = '';

    // Hide result card
    var resultDiv = document.getElementById('premiumResult');
    resultDiv.classList.remove('visible');
}

// ═══════════════════════════════════════════════
//  Collect Form Data for Premium API
// ═══════════════════════════════════════════════
function collectFormData() {
    // Basic info
    var data = {
        business_name: (document.getElementById('businessName') || {}).value || '',
        industry_key: (document.getElementById('industryKey') || {}).value || '',
        industry_label: (document.getElementById('industryLabel') || {}).value || '',
        tagline: (document.getElementById('tagline') || {}).value || '',
        subtitle: (document.getElementById('subtitle') || {}).value || '',
        phone: (document.getElementById('phone') || {}).value || '',
        address: (document.getElementById('address') || {}).value || '',
        hours: '',
        website_url: '',
        promotion: '',
        features: [],
        menu_items: [],
        ai_image: (document.getElementById('aiImagePath') || {}).value || ''
    };

    // Single/double-sided option
    var sidesEl = document.querySelector('input[name="page_sides"]:checked');
    data.double_sided = sidesEl ? (sidesEl.value === 'double') : false;

    // Hours
    var hoursEl = document.querySelector('input[name="hours"]');
    if (hoursEl) data.hours = hoursEl.value || '';

    // Website
    var webEl = document.querySelector('input[name="website_url"]');
    if (webEl) data.website_url = webEl.value || '';

    // Promotion
    var promoEl = document.querySelector('textarea[name="promotion"]');
    if (promoEl) data.promotion = promoEl.value || '';

    // Features (up to 3)
    var featEls = document.querySelectorAll('input[name="features[]"]');
    for (var i = 0; i < featEls.length; i++) {
        var v = featEls[i].value.trim();
        if (v) data.features.push(v);
    }

    // Menu items
    var nameEls = document.querySelectorAll('input[name="menu_name[]"]');
    var priceEls = document.querySelectorAll('input[name="menu_price[]"]');
    for (var j = 0; j < nameEls.length; j++) {
        var mName = nameEls[j].value.trim();
        var mPrice = (priceEls[j] || {}).value || '';
        if (mName) {
            data.menu_items.push({name: mName, price: mPrice.trim()});
        }
    }

    return data;
}


</script>
</body>
</html>
