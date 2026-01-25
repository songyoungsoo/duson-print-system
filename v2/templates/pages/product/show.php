<div class="product-page">
    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <nav class="breadcrumb">
            <a href="<?= \App\Core\View::url('/') ?>" class="breadcrumb-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>홈</span>
            </a>
            <svg class="breadcrumb-separator" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="breadcrumb-current"><?= $product['name'] ?></span>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="product-layout">
            <!-- Gallery Section -->
            <div class="product-gallery" x-data="galleryComponent(<?= htmlspecialchars(json_encode($gallery)) ?>)">
                <div class="gallery-container">
                    <!-- Main Image -->
                    <div class="gallery-main-wrapper">
                        <div class="gallery-main">
                            <img :src="mainImage" :alt="mainImageName" class="gallery-main-img">
                            <div class="gallery-zoom-hint">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thumbnails -->
                    <div class="gallery-thumbs">
                        <template x-for="(img, index) in images.slice(0, 4)" :key="index">
                            <button @click="setMain(index)" 
                                    :class="{'gallery-thumb-active': currentIndex === index}"
                                    class="gallery-thumb">
                                <img :src="img.thumb" :alt="img.name">
                            </button>
                        </template>
                        
                        <button @click="openModal()" class="gallery-thumb gallery-thumb-more">
                            <span class="gallery-more-text">+더보기</span>
                        </button>
                    </div>
                </div>
                
                <!-- Product Info Card (Gallery 하단) -->
                <div class="product-info-card">
                    <div class="info-card-header">
                        <span class="info-card-badge"><?= $product['unit_name'] ?>단위</span>
                        <?php if ($product['has_template'] ?? false): ?>
                        <span class="info-card-badge info-card-badge-gold">템플릿 제공</span>
                        <?php endif; ?>
                    </div>
                    <ul class="info-card-list">
                        <li>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>고품질 인쇄 보장</span>
                        </li>
                        <li>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>빠른 제작 및 배송</span>
                        </li>
                        <li>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>다양한 옵션 선택 가능</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Gallery Modal -->
                <div x-show="modalOpen" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     @keydown.escape.window="modalOpen = false">
                    <div class="modal-backdrop" @click="modalOpen = false"></div>
                    <div class="modal-content w-full max-w-4xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">샘플 이미지</h3>
                            <button @click="modalOpen = false" class="modal-close-btn">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-4 gap-4 max-h-96 overflow-y-auto">
                            <template x-for="(img, index) in allImages" :key="index">
                                <div @click="setMain(index); modalOpen = false" 
                                     class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-brand-navy transition-all">
                                    <img :src="img.thumb" :alt="img.name" class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculator Section -->
            <?php
            $jsConfig = [
                'productType' => $product['type'],
                'uiType' => $uiType,
                'uiConfig' => $uiConfig,
                'initialDropdowns' => $initialDropdowns,
                'premiumOptions' => $premiumOptions,
            ];
            ?>
            <div class="product-calculator" x-data="productCalculator(<?= htmlspecialchars(json_encode($jsConfig)) ?>)">
                <!-- Header -->
                <div class="calculator-header">
                    <h1 class="calculator-title"><?= $product['name'] ?></h1>
                    <p class="calculator-subtitle">원하시는 옵션을 선택하고 견적을 확인하세요.</p>
                </div>

                <?php if ($uiType === 'formula_input'): ?>
                <!-- 스티커: 직접입력 방식 -->
                <div class="option-section">
                    <h3 class="option-section-title">
                        <span class="option-section-number">1</span>
                        기본 옵션
                    </h3>
                    
                    <div class="option-group">
                        <label class="option-label">재질 선택 <span class="required">*</span></label>
                        <select x-model="formData.material" @change="calculate()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="mat in dropdowns.material" :key="mat.id">
                                <option :value="mat.id" x-text="mat.title"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="option-row">
                        <div class="option-group">
                            <label class="option-label">가로 (mm) <span class="required">*</span></label>
                            <input type="number" x-model="formData.width" @input="calculate()" 
                                   min="10" max="1000" placeholder="가로 크기"
                                   class="form-input">
                        </div>
                        <div class="option-group">
                            <label class="option-label">세로 (mm) <span class="required">*</span></label>
                            <input type="number" x-model="formData.height" @input="calculate()"
                                   min="10" max="1000" placeholder="세로 크기"
                                   class="form-input">
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <label class="option-label">수량 <span class="required">*</span></label>
                        <input type="number" x-model="formData.quantity" @input="calculate()"
                               min="1" placeholder="수량 입력"
                               class="form-input">
                    </div>
                    
                    <div class="option-group">
                        <label class="option-label">재단/도무송</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="cutting" value="none" x-model="formData.cutting" @change="calculate()">
                                <span class="radio-label">재단</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="cutting" value="domusong" x-model="formData.cutting" @change="calculate()">
                                <span class="radio-label">도무송 <span class="option-price-hint">+별도</span></span>
                            </label>
                        </div>
                    </div>
                </div>

                <?php elseif ($uiType === 'dropdown_4level'): ?>
                <!-- 전단지: 4단계 드롭다운 -->
                <div class="option-section">
                    <h3 class="option-section-title">
                        <span class="option-section-number">1</span>
                        기본 옵션
                    </h3>
                    
                    <div class="option-group">
                        <label class="option-label"><?= $uiConfig['levels'][0]['label'] ?? '인쇄도수' ?> <span class="required">*</span></label>
                        <select x-model="formData.style" @change="onLevel1Change()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.style" :key="opt.id">
                                <option :value="opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="option-group" x-show="dropdowns.TreeSelect.length > 0" x-transition>
                        <label class="option-label"><?= $uiConfig['levels'][1]['label'] ?? '용지' ?> <span class="required">*</span></label>
                        <select x-model="formData.TreeSelect" @change="onLevel2Change()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.TreeSelect" :key="opt.id">
                                <option :value="opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="option-group" x-show="dropdowns.Section.length > 0" x-transition>
                        <label class="option-label"><?= $uiConfig['levels'][2]['label'] ?? '규격' ?> <span class="required">*</span></label>
                        <select x-model="formData.Section" @change="onLevel3Change()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.Section" :key="opt.id">
                                <option :value="opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="option-group" x-show="dropdowns.quantity.length > 0" x-transition>
                        <label class="option-label"><?= $uiConfig['levels'][3]['label'] ?? '수량' ?> <span class="required">*</span></label>
                        <select x-model="formData.quantity" @change="calculate()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.quantity" :key="opt.id">
                                <option :value="opt.value || opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <?php elseif ($uiType === 'dropdown_2level'): ?>
                <!-- 2단계: style → Section → quantity (명함 등) -->
                <div class="option-section">
                    <h3 class="option-section-title">
                        <span class="option-section-number">1</span>
                        기본 옵션
                    </h3>
                    
                    <div class="option-group">
                        <label class="option-label"><?= $uiConfig['levels'][0]['label'] ?? '종류' ?> <span class="required">*</span></label>
                        <select x-model="formData.style" @change="onLevel1Change2Level()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.style" :key="opt.id">
                                <option :value="opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="option-group" x-show="dropdowns.Section.length > 0" x-transition>
                        <label class="option-label"><?= $uiConfig['levels'][1]['label'] ?? '용지' ?> <span class="required">*</span></label>
                        <select x-model="formData.Section" @change="onLevel2Change2Level()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.Section" :key="opt.id">
                                <option :value="opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="option-group" x-show="dropdowns.quantity.length > 0" x-transition>
                        <label class="option-label"><?= $uiConfig['levels'][2]['label'] ?? '수량' ?> <span class="required">*</span></label>
                        <select x-model="formData.quantity" @change="calculate()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.quantity" :key="opt.id">
                                <option :value="opt.value || opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <?php else: ?>
                <!-- 기본: 3단계 드롭다운 (봉투 등) -->
                <div class="option-section">
                    <h3 class="option-section-title">
                        <span class="option-section-number">1</span>
                        기본 옵션
                    </h3>
                    
                    <div class="option-group">
                        <label class="option-label"><?= $uiConfig['levels'][0]['label'] ?? '인쇄도수' ?> <span class="required">*</span></label>
                        <select x-model="formData.style" @change="onLevel1Change()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.style" :key="opt.id">
                                <option :value="opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="option-group" x-show="dropdowns.TreeSelect.length > 0" x-transition>
                        <label class="option-label"><?= $uiConfig['levels'][1]['label'] ?? '용지' ?> <span class="required">*</span></label>
                        <select x-model="formData.TreeSelect" @change="onLevel2Change()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.TreeSelect" :key="opt.id">
                                <option :value="opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="option-group" x-show="dropdowns.quantity.length > 0" x-transition>
                        <label class="option-label"><?= $uiConfig['levels'][2]['label'] ?? '수량' ?> <span class="required">*</span></label>
                        <select x-model="formData.quantity" @change="calculate()" class="form-select">
                            <option value="">선택하세요</option>
                            <template x-for="opt in dropdowns.quantity" :key="opt.id">
                                <option :value="opt.value || opt.id" x-text="opt.title"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 인쇄 옵션 섹션 -->
                <div class="option-section">
                    <h3 class="option-section-title">
                        <span class="option-section-number">2</span>
                        인쇄 옵션
                    </h3>
                    
                    <?php if ($uiType !== 'formula_input'): ?>
                    <div class="option-group">
                        <label class="option-label">인쇄면 선택</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="POtype" value="1" x-model="formData.POtype" @change="calculate()">
                                <span class="radio-label">단면</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="POtype" value="2" x-model="formData.POtype" @change="calculate()">
                                <span class="radio-label">양면</span>
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="option-group">
                        <label class="option-label">디자인 의뢰</label>
                        <div class="radio-group radio-group-vertical">
                            <label class="radio-option-card">
                                <input type="radio" name="ordertype" value="1" x-model="formData.ordertype" @change="calculate()">
                                <div class="radio-card-content">
                                    <span class="radio-card-title">직접 시안 보유</span>
                                    <span class="radio-card-desc">디자인 파일을 직접 업로드합니다</span>
                                </div>
                            </label>
                            <label class="radio-option-card">
                                <input type="radio" name="ordertype" value="2" x-model="formData.ordertype" @change="calculate()">
                                <div class="radio-card-content">
                                    <span class="radio-card-title">디자인 의뢰</span>
                                    <span class="radio-card-desc">전문 디자이너가 제작해 드립니다 <span class="option-price-hint">+별도</span></span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <?php if (!empty($premiumOptions)): ?>
                <!-- 프리미엄 옵션 섹션 -->
                <div class="option-section">
                    <h3 class="option-section-title">
                        <span class="option-section-number">3</span>
                        추가 옵션
                        <span class="option-section-badge">선택</span>
                    </h3>
                    
                    <div class="premium-options-grid">
                        <?php foreach ($premiumOptions as $key => $opt): ?>
                        <?php $optName = $opt['name'] ?? $key; ?>
                        <?php if (($opt['type'] ?? 'checkbox') === 'select'): ?>
                        <!-- Select 타입 옵션 -->
                        <div class="premium-option-card" :class="{'premium-option-active': formData.premiumOptions.<?= $optName ?>.enabled}">
                            <div class="premium-option-header">
                                <label class="premium-option-toggle">
                                    <input type="checkbox"
                                           x-model="formData.premiumOptions.<?= $optName ?>.enabled"
                                           @change="calculate()">
                                    <span class="premium-option-name"><?= htmlspecialchars($opt['label'] ?? $optName) ?></span>
                                </label>
                                <?php if (isset($opt['note'])): ?>
                                <span class="premium-option-note"><?= htmlspecialchars($opt['note']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div x-show="formData.premiumOptions.<?= $optName ?>.enabled" x-transition class="premium-option-body">
                                <select x-model="formData.premiumOptions.<?= $optName ?>.type"
                                        @change="calculate()"
                                        class="form-select form-select-sm">
                                    <option value="">종류 선택</option>
                                    <?php foreach ($opt['options'] ?? [] as $subOpt): ?>
                                    <option value="<?= htmlspecialchars($subOpt['value']) ?>"><?= htmlspecialchars($subOpt['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="premium-option-price">
                                <?= number_format($opt['base_qty'] ?? 500) ?>매 기준 <?= number_format($opt['base_price'] ?? 0) ?>원~
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Checkbox 타입 옵션 -->
                        <label class="premium-option-card premium-option-simple" :class="{'premium-option-active': formData.premiumOptions.<?= $optName ?>.enabled}">
                            <div class="premium-option-header">
                                <div class="premium-option-toggle">
                                    <input type="checkbox"
                                           x-model="formData.premiumOptions.<?= $optName ?>.enabled"
                                           @change="calculate()">
                                    <span class="premium-option-name"><?= htmlspecialchars($opt['label'] ?? $optName) ?></span>
                                </div>
                            </div>
                            <div class="premium-option-price">
                                <?php if (isset($opt['fixed_price'])): ?>
                                +<?= number_format($opt['fixed_price']) ?>원
                                <?php else: ?>
                                <?= number_format($opt['base_qty'] ?? 500) ?>매 기준 <?= number_format($opt['base_price'] ?? 0) ?>원~
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 파일 업로드 섹션 -->
                <div class="option-section">
                    <h3 class="option-section-title">
                        <span class="option-section-number"><?= !empty($premiumOptions) ? '4' : '3' ?></span>
                        디자인 파일 업로드
                        <span class="option-section-badge">선택</span>
                    </h3>
                    
                    <div class="file-upload-area" 
                         x-data="fileUploader()"
                         @dragover.prevent="dragover = true"
                         @dragleave.prevent="dragover = false"
                         @drop.prevent="handleDrop($event)">
                        
                        <!-- 드래그앤드롭 영역 -->
                        <div class="file-dropzone" 
                             :class="{'file-dropzone-active': dragover}"
                             @click="$refs.fileInput.click()">
                            <input type="file" 
                                   x-ref="fileInput"
                                   multiple
                                   accept=".ai,.psd,.pdf,.jpg,.jpeg,.png,.gif,.tif,.tiff,.eps,.cdr"
                                   @change="handleFiles($event)"
                                   class="hidden">
                            
                            <div class="file-dropzone-content">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="file-dropzone-text">파일을 드래그하거나 클릭하여 업로드</p>
                                <p class="file-dropzone-hint">AI, PSD, PDF, JPG, PNG, GIF, TIF, EPS, CDR (최대 50MB)</p>
                            </div>
                        </div>
                        
                        <!-- 업로드된 파일 목록 -->
                        <template x-if="files.length > 0">
                            <div class="uploaded-files-list">
                                <template x-for="(file, index) in files" :key="index">
                                    <div class="uploaded-file-item">
                                        <div class="uploaded-file-icon" :class="getFileIconClass(file.name)">
                                            <span x-text="getFileExtension(file.name)"></span>
                                        </div>
                                        <div class="uploaded-file-info">
                                            <span class="uploaded-file-name" x-text="file.name"></span>
                                            <span class="uploaded-file-size" x-text="formatFileSize(file.size)"></span>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="uploaded-file-remove">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <!-- 파일 업로드 안내 -->
                        <div class="file-upload-notice">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p>파일은 장바구니에 담을 때 함께 업로드됩니다.</p>
                                <p class="text-gray-500 text-xs mt-1">나중에 장바구니/주문 페이지에서도 추가 업로드 가능합니다.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($product['has_template'] ?? false): ?>
                <!-- 템플릿 다운로드 -->
                <div class="template-download-card">
                    <div class="template-download-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="template-download-content">
                        <span class="template-download-title">작업 템플릿</span>
                        <a href="/templates/<?= $product['type'] ?>_template.ai" class="template-download-link">
                            일러스트레이터 템플릿 다운로드
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 가격 계산 결과 -->
                <div class="price-calculator-box">
                    <div class="price-box-header">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>견적 계산</span>
                    </div>
                    
                    <div class="price-box-content">
                        <!-- Loading -->
                        <div x-show="loading" class="price-loading">
                            <div class="price-loading-spinner"></div>
                            <span>계산 중...</span>
                        </div>
                        
                        <!-- Result -->
                        <template x-if="!loading && result.success">
                            <div class="price-result">
                                <div class="price-detail-row">
                                    <span>인쇄비</span>
                                    <span x-text="result.formatted?.base || '0원'"></span>
                                </div>
                                <div x-show="result.design_price > 0" class="price-detail-row">
                                    <span>디자인비</span>
                                    <span x-text="result.formatted?.design || '0원'"></span>
                                </div>
                                <div x-show="result.options_price > 0" class="price-detail-row">
                                    <span>추가 옵션</span>
                                    <span class="text-brand-navy" x-text="result.formatted?.options || '0원'"></span>
                                </div>
                                <div class="price-divider"></div>
                                <div class="price-detail-row">
                                    <span>공급가액</span>
                                    <span x-text="result.formatted?.supply || '0원'"></span>
                                </div>
                                <div class="price-detail-row price-vat">
                                    <span>부가세 (10%)</span>
                                    <span x-text="result.formatted?.vat || '0원'"></span>
                                </div>
                                <div class="price-total-row">
                                    <span>총 금액</span>
                                    <span x-text="result.formatted?.total || '0원'"></span>
                                </div>
                                <div x-show="result.quantity_display" class="price-quantity-display">
                                    주문 수량: <strong x-text="result.quantity_display"></strong>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Error -->
                        <template x-if="!loading && result.error">
                            <div class="price-error">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p x-text="result.error"></p>
                            </div>
                        </template>
                        
                        <!-- Placeholder -->
                        <template x-if="!loading && !result.success && !result.error">
                            <div class="price-placeholder">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <p>옵션을 선택하면<br>가격이 계산됩니다</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button @click="addToCart()" 
                            :disabled="!canOrder"
                            class="action-btn action-btn-secondary"
                            :class="{'action-btn-disabled': !canOrder}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>장바구니</span>
                    </button>
                    <button @click="orderNow()" 
                            :disabled="!canOrder"
                            class="action-btn action-btn-primary"
                            :class="{'action-btn-disabled': !canOrder}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        <span>바로 주문하기</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// File Uploader Component
function fileUploader() {
    return {
        files: [],
        dragover: false,
        
        init() {
            window.addEventListener('cart-files-clear', () => {
                this.files = [];
            });
        },
        
        handleFiles(event) {
            const newFiles = Array.from(event.target.files);
            this.addFiles(newFiles);
            event.target.value = ''; // Reset input
        },
        
        handleDrop(event) {
            this.dragover = false;
            const newFiles = Array.from(event.dataTransfer.files);
            this.addFiles(newFiles);
        },
        
        addFiles(newFiles) {
            const validExtensions = ['ai', 'psd', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'eps', 'cdr'];
            const maxSize = 50 * 1024 * 1024; // 50MB
            
            for (const file of newFiles) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!validExtensions.includes(ext)) {
                    DusonApp.showToast(`지원하지 않는 파일 형식입니다: ${file.name}`, 'error');
                    continue;
                }
                if (file.size > maxSize) {
                    DusonApp.showToast(`파일 크기 초과 (50MB): ${file.name}`, 'error');
                    continue;
                }
                // Avoid duplicates
                if (!this.files.some(f => f.name === file.name && f.size === file.size)) {
                    this.files.push(file);
                }
            }
            
            // Dispatch event to parent component
            window.dispatchEvent(new CustomEvent('files-updated', { detail: this.files }));
        },
        
        removeFile(index) {
            this.files.splice(index, 1);
            window.dispatchEvent(new CustomEvent('files-updated', { detail: this.files }));
        },
        
        getFileExtension(filename) {
            return filename.split('.').pop().toUpperCase();
        },
        
        getFileIconClass(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const iconClasses = {
                'ai': 'file-icon-ai',
                'psd': 'file-icon-psd',
                'pdf': 'file-icon-pdf',
                'jpg': 'file-icon-image',
                'jpeg': 'file-icon-image',
                'png': 'file-icon-image',
                'gif': 'file-icon-image',
                'tif': 'file-icon-image',
                'tiff': 'file-icon-image',
                'eps': 'file-icon-eps',
                'cdr': 'file-icon-cdr',
            };
            return iconClasses[ext] || 'file-icon-default';
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }
    };
}

function galleryComponent(initialImages) {
    return {
        images: initialImages || [],
        allImages: initialImages || [],
        currentIndex: 0,
        modalOpen: false,
        
        get mainImage() {
            return this.images[this.currentIndex]?.url || '/v2/public/assets/images/placeholder.png';
        },
        
        get mainImageName() {
            return this.images[this.currentIndex]?.name || '';
        },
        
        setMain(index) {
            this.currentIndex = index;
        },
        
        openModal() {
            this.modalOpen = true;
        }
    };
}

function productCalculator(config) {
    // premiumOptions 초기 상태 생성
    const initPremiumOptions = {};
    if (config.premiumOptions) {
        for (const [key, opt] of Object.entries(config.premiumOptions)) {
            const name = opt.name || key;
            initPremiumOptions[name] = {
                enabled: false,
                type: ''
            };
        }
    }
    
    return {
        productType: config.productType,
        uiType: config.uiType,
        uiConfig: config.uiConfig,
        premiumOptionsConfig: config.premiumOptions || {},
        
        dropdowns: {
            style: config.initialDropdowns?.style || [],
            TreeSelect: [],
            Section: [],
            quantity: [],
            material: config.initialDropdowns?.material || [],
        },
        
        formData: {
            style: '',
            TreeSelect: '',
            Section: '',
            quantity: '',
            POtype: '1',
            ordertype: '1',
            premiumOptions: initPremiumOptions,
            material: '',
            width: '',
            height: '',
            cutting: 'none',
        },
        
        uploadedFiles: [],  // 업로드할 파일들
        isSubmitting: false,
        
        result: {},
        loading: false,
        calcTimeout: null,
        
        init() {
            // 파일 업로드 이벤트 리스너
            window.addEventListener('files-updated', (e) => {
                this.uploadedFiles = e.detail;
            });
        },
        
        get canOrder() {
            if (this.uiType === 'formula_input') {
                return this.formData.material && this.formData.width && this.formData.height && 
                       this.formData.quantity && this.result.success && this.result.total_price > 0;
            }
            return this.formData.quantity && this.result.success && this.result.total_price > 0;
        },
        
        async onLevel1Change() {
            this.formData.TreeSelect = '';
            this.formData.Section = '';
            this.formData.quantity = '';
            this.dropdowns.TreeSelect = [];
            this.dropdowns.Section = [];
            this.dropdowns.quantity = [];
            this.result = {};
            
            if (!this.formData.style) return;
            
            const options = await this.loadOptions('TreeSelect', { style: this.formData.style });
            this.dropdowns.TreeSelect = options;
            
            if (this.uiType === 'dropdown_3level') {
                const qtyOptions = await this.loadOptions('quantity', { style: this.formData.style });
                this.dropdowns.quantity = qtyOptions;
            }
        },
        
        // 2-level dropdown: style → Section → quantity
        async onLevel1Change2Level() {
            this.formData.Section = '';
            this.formData.quantity = '';
            this.dropdowns.Section = [];
            this.dropdowns.quantity = [];
            this.result = {};
            
            if (!this.formData.style) return;
            
            // Load Section options (uses BigNo = style)
            const options = await this.loadOptions('Section', { style: this.formData.style });
            this.dropdowns.Section = options;
        },
        
        async onLevel2Change2Level() {
            this.formData.quantity = '';
            this.dropdowns.quantity = [];
            this.result = {};
            
            if (!this.formData.Section) return;
            
            // Load quantity options
            const qtyOptions = await this.loadOptions('quantity', { 
                style: this.formData.style,
                Section: this.formData.Section
            });
            this.dropdowns.quantity = qtyOptions;
        },
        
        async onLevel2Change() {
            this.formData.Section = '';
            this.formData.quantity = '';
            this.dropdowns.Section = [];
            this.dropdowns.quantity = [];
            this.result = {};
            
            if (!this.formData.TreeSelect) return;
            
            if (this.uiType === 'dropdown_4level') {
                const options = await this.loadOptions('Section', { 
                    style: this.formData.style,
                    TreeSelect: this.formData.TreeSelect 
                });
                this.dropdowns.Section = options;
            } else {
                const qtyOptions = await this.loadOptions('quantity', { 
                    style: this.formData.style,
                    TreeSelect: this.formData.TreeSelect 
                });
                this.dropdowns.quantity = qtyOptions;
            }
        },
        
        async onLevel3Change() {
            this.formData.quantity = '';
            this.dropdowns.quantity = [];
            this.result = {};
            
            if (!this.formData.Section) return;
            
            const qtyOptions = await this.loadOptions('quantity', { 
                style: this.formData.style,
                TreeSelect: this.formData.TreeSelect,
                Section: this.formData.Section
            });
            this.dropdowns.quantity = qtyOptions;
        },
        
        async loadOptions(level, params) {
            try {
                const queryString = new URLSearchParams({ level, ...params }).toString();
                const response = await fetch(`/v2/public/product/${this.productType}/options?${queryString}`);
                const data = await response.json();
                return data.success ? data.options : [];
            } catch (error) {
                console.error('옵션 로드 오류:', error);
                return [];
            }
        },
        
        calculate() {
            if (this.calcTimeout) clearTimeout(this.calcTimeout);
            this.calcTimeout = setTimeout(() => this.doCalculate(), 300);
        },
        
        async doCalculate() {
            const hasRequiredFields = this.uiType === 'formula_input'
                ? (this.formData.material && this.formData.width && this.formData.height && this.formData.quantity)
                : this.formData.quantity;
            
            if (!hasRequiredFields) {
                this.result = {};
                return;
            }
            
            this.loading = true;
            
            try {
                const body = this.uiType === 'formula_input'
                    ? {
                        jong: this.formData.material,
                        garo: this.formData.width,
                        sero: this.formData.height,
                        mesu: this.formData.quantity,
                        domusong: this.formData.cutting === 'domusong' ? 1 : 0,
                        ordertype: this.formData.ordertype,
                        premium_options: JSON.stringify(this.formData.premiumOptions),
                    }
                    : {
                        MY_type: this.formData.style,
                        MY_Fsd: this.formData.TreeSelect,
                        PN_type: this.formData.Section,
                        MY_amount: this.formData.quantity,
                        POtype: this.formData.POtype,
                        ordertype: this.formData.ordertype,
                        premium_options: JSON.stringify(this.formData.premiumOptions),
                    };
                
                const response = await DusonApp.fetchAPI(`/v2/public/product/${this.productType}/calculate`, {
                    method: 'POST',
                    body: JSON.stringify(body),
                });
                
                this.result = response;
            } catch (error) {
                console.error('계산 오류:', error);
                this.result = { success: false, error: '가격 계산 중 오류가 발생했습니다.' };
            } finally {
                this.loading = false;
            }
        },
        
        async addToCart() {
            if (!this.canOrder || this.isSubmitting) return;
            
            this.isSubmitting = true;
            
            try {
                // FormData 생성 (파일 업로드 지원)
                const formData = new FormData();
                formData.append('_token', '<?= \App\Core\CSRF::token() ?>');
                formData.append('product_type', this.productType);
                
                // 기본 옵션
                if (this.uiType === 'formula_input') {
                    formData.append('MY_type', this.formData.material);
                    formData.append('spec_size', `${this.formData.width}x${this.formData.height}mm`);
                    formData.append('MY_amount', this.formData.quantity);
                } else {
                    formData.append('MY_type', this.formData.style);
                    formData.append('MY_Fsd', this.formData.TreeSelect);
                    formData.append('Section', this.formData.Section);
                    formData.append('MY_amount', this.formData.quantity);
                }
                
                // 인쇄 옵션
                formData.append('POtype', this.formData.POtype);
                formData.append('ordertype', this.formData.ordertype);
                
                // 가격 정보
                formData.append('price', this.result.supply_price || this.result.base_price || 0);
                formData.append('vat_price', this.result.total_price || 0);
                formData.append('price_supply', this.result.supply_price || 0);
                formData.append('price_vat', this.result.total_price || 0);
                formData.append('price_vat_amount', this.result.vat_amount || 0);
                
                // 수량 정보
                formData.append('quantity_display', this.result.quantity_display || '');
                formData.append('quantity_value', this.result.quantity_value || this.formData.quantity);
                formData.append('quantity_unit', this.result.unit || '');
                formData.append('quantity_sheets', this.result.total_sheets || 0);
                
                // 스펙 정보 (표시용)
                formData.append('spec_type', this.getSelectedOptionLabel('style'));
                formData.append('spec_material', this.getSelectedOptionLabel('TreeSelect') || this.getSelectedOptionLabel('Section'));
                formData.append('spec_sides', this.formData.POtype === '2' ? '양면' : '단면');
                formData.append('spec_design', this.formData.ordertype === '2' ? '디자인의뢰' : '직접시안');
                
                // 프리미엄 옵션 (선택된 것만)
                const selectedPremiumOptions = [];
                for (const [key, opt] of Object.entries(this.formData.premiumOptions)) {
                    if (opt.enabled) {
                        selectedPremiumOptions.push({
                            name: key,
                            type: opt.type || null,
                            label: this.premiumOptionsConfig[key]?.label || key,
                        });
                    }
                }
                formData.append('premium_options', JSON.stringify(selectedPremiumOptions));
                
                // 파일 업로드
                if (this.uploadedFiles.length > 0) {
                    for (const file of this.uploadedFiles) {
                        formData.append('file[]', file);
                    }
                    formData.append('upload_method', 'upload');
                } else {
                    formData.append('upload_method', 'later'); // 나중에 업로드
                }
                
                // API 호출
                const response = await fetch('/v2/public/cart/add', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // 업로드 파일 초기화
                    this.uploadedFiles = [];
                    window.dispatchEvent(new CustomEvent('cart-files-clear'));
                    
                    if (typeof DusonApp !== 'undefined') {
                        DusonApp.updateCartBadge(data.cart_count || 1);
                        DusonApp.showToast('장바구니에 추가되었습니다.', 'success');
                    }
                } else {
                    throw new Error(data.message || '장바구니 추가 실패');
                }
            } catch (error) {
                console.error('장바구니 추가 오류:', error);
                DusonApp.showToast(error.message || '장바구니 추가 중 오류가 발생했습니다.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },
        
        getSelectedOptionLabel(fieldName) {
            const value = this.formData[fieldName];
            if (!value) return '';
            
            const options = this.dropdowns[fieldName] || [];
            const selected = options.find(opt => String(opt.id) === String(value));
            return selected?.title || '';
        },
        
        async orderNow() {
            await this.addToCart();
            if (!this.isSubmitting) {  // 성공 시에만 이동
                window.location.href = '/v2/public/cart';
            }
        }
    };
}
</script>
