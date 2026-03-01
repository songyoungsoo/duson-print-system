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
                     data-menu-label="<?php echo htmlspecialchars($preset['menuLabel']); ?>">
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
            <input type="text" name="business_name" id="businessName" placeholder="예: 맛나분식" required>
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
                    <input type="tel" name="phone" id="phone" placeholder="02-1234-5678" required>
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
            <input type="text" name="address" id="address" placeholder="서울시 영등포구 당산로 123" required>
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

        <div class="btn-row">
            <button type="button" class="btn btn-prev" onclick="goStep(3)">← 이전</button>
            <button type="submit" class="btn btn-submit">전단지 만들기 (PDF 다운로드)</button>
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
</script>
</body>
</html>
