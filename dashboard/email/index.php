<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

        <!-- Tabs -->
        <div class="flex items-center gap-2 mb-4 border-b border-gray-200">
            <button onclick="switchTab('compose')" id="tab-compose"
                class="px-4 py-2 text-sm font-medium border-b-2 border-brand text-brand -mb-px">새 이메일 작성</button>
            <button onclick="switchTab('history')" id="tab-history"
                class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px">발송 이력</button>
            <button onclick="switchTab('templates')" id="tab-templates"
                class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px">템플릿 관리</button>
        </div>

        <!-- ===== TAB 1: Compose ===== -->
        <div id="panel-compose">
            <div class="flex items-center gap-3 mb-3">
                <h1 class="text-2xl font-bold text-gray-900">이메일 발송</h1>
                <span class="text-sm text-gray-500">회원에게 일괄 이메일을 발송합니다</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                <!-- LEFT: Form (3/5) -->
                <div class="lg:col-span-3 space-y-3">

                    <!-- Recipients -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">수신 대상</label>
                        <div class="flex flex-wrap gap-3 mb-2">
                            <label class="flex items-center gap-1 text-xs cursor-pointer">
                                <input type="radio" name="recipient_type" value="all" checked onchange="onRecipientTypeChange()">
                                <span>전체 회원</span>
                                <span id="badge-all" class="ml-0.5 px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-medium">-</span>
                            </label>
                            <label class="flex items-center gap-1 text-xs cursor-pointer">
                                <input type="radio" name="recipient_type" value="filtered" onchange="onRecipientTypeChange()">
                                <span>조건 필터</span>
                            </label>
                            <label class="flex items-center gap-1 text-xs cursor-pointer">
                                <input type="radio" name="recipient_type" value="manual" onchange="onRecipientTypeChange()">
                                <span>직접 입력</span>
                            </label>
                        </div>

                        <!-- Filter options -->
                        <div id="filter-options" class="hidden space-y-1.5 p-2 bg-gray-50 rounded border border-gray-200">
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-gray-600 w-28 flex-shrink-0">최근 로그인</label>
                                <select id="filter-login-months" class="text-xs border border-gray-300 rounded px-2 py-1" onchange="updateRecipientCount()">
                                    <option value="">전체</option>
                                    <option value="1">1개월 이내</option>
                                    <option value="3">3개월 이내</option>
                                    <option value="6">6개월 이내</option>
                                    <option value="12">12개월 이내</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-gray-600 w-28 flex-shrink-0">이메일 도메인</label>
                                <select id="filter-domain" class="text-xs border border-gray-300 rounded px-2 py-1" onchange="updateRecipientCount()">
                                    <option value="">전체</option>
                                    <option value="naver.com">naver.com</option>
                                    <option value="gmail.com">gmail.com</option>
                                    <option value="daum.net">daum.net</option>
                                    <option value="hanmail.net">hanmail.net</option>
                                </select>
                            </div>
                            <div class="text-[10px] text-gray-400">필터 조건에 맞는 회원: <span id="filtered-count" class="font-medium text-gray-600">-</span>명</div>
                        </div>

                        <!-- Manual input -->
                        <div id="manual-input" class="hidden">
                            <textarea id="manual-emails" rows="3" placeholder="이메일을 쉼표(,)로 구분하여 입력&#10;예: user1@email.com, user2@email.com"
                                class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 font-mono focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                oninput="updateManualCount()"></textarea>
                            <div class="text-[10px] text-gray-400 mt-0.5">입력된 이메일: <span id="manual-count" class="font-medium text-gray-600">0</span>개</div>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-sm font-semibold text-gray-700">제목</label>
                            <span class="text-[10px] text-gray-400"><span id="subject-length">0</span>/200</span>
                        </div>
                        <input type="text" id="email-subject" maxlength="200" placeholder="이메일 제목을 입력하세요"
                            class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            oninput="document.getElementById('subject-length').textContent = this.value.length; updateChecklist();">
                    </div>

                    <!-- Body -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-sm font-semibold text-gray-700">본문</label>
                            <div class="inline-flex bg-gray-100 rounded-lg p-0.5">
                                <button onclick="switchEditorMode('wysiwyg')" id="btn-mode-wysiwyg" class="text-xs px-3 py-1 rounded-md bg-white text-brand shadow-sm font-medium">편집기</button>
                                <button onclick="switchEditorMode('html')" id="btn-mode-html" class="text-xs px-3 py-1 rounded-md text-gray-600 hover:text-gray-900">HTML</button>
                                <button onclick="switchEditorMode('preview')" id="btn-mode-preview" class="text-xs px-3 py-1 rounded-md text-gray-600 hover:text-gray-900">미리보기</button>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mb-2">
                            <select id="template-select" class="text-xs border border-gray-300 rounded px-2 py-1 max-w-[200px]" onchange="onTemplateSelect(this.value)">
                                <option value="">-- 템플릿 선택 --</option>
                            </select>
                            <button onclick="loadTemplate()" id="btn-load-template" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">불러오기</button>
                        </div>

                        <!-- Toolbar (WYSIWYG mode only) -->
                        <div id="toolbar-wrap" class="flex flex-wrap items-center gap-1 p-2 bg-gray-50 border border-gray-300 border-b-0 rounded-t">
                            <button type="button" onclick="execCmd('bold')" class="px-2 py-1 text-xs font-bold border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="굵게">B</button>
                            <button type="button" onclick="execCmd('italic')" class="px-2 py-1 text-xs italic border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="기울임">I</button>
                            <button type="button" onclick="execCmd('underline')" class="px-2 py-1 text-xs underline border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="밑줄">U</button>
                            <span class="w-px h-5 bg-gray-300 mx-0.5"></span>
                            <button type="button" onclick="execCmd('formatBlock','<h1>')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="제목1">H1</button>
                            <button type="button" onclick="execCmd('formatBlock','<h2>')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="제목2">H2</button>
                            <button type="button" onclick="execCmd('formatBlock','<p>')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="본문">P</button>
                            <span class="w-px h-5 bg-gray-300 mx-0.5"></span>
                            <button type="button" onclick="insertLink()" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="링크 삽입">🔗 링크</button>
                            <button type="button" onclick="insertImage()" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="이미지 업로드">📷 이미지</button>
                            <span class="w-px h-5 bg-gray-300 mx-0.5"></span>
                            <button type="button" onclick="execCmd('insertUnorderedList')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="글머리 기호">• 목록</button>
                            <button type="button" onclick="execCmd('insertOrderedList')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="번호 목록">1. 목록</button>
                            <button type="button" onclick="execCmd('insertHorizontalRule')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="구분선">─</button>
                            <span class="w-px h-5 bg-gray-300 mx-0.5"></span>
                            <label class="flex items-center gap-1 px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="글자색">
                                <span class="text-xs">색</span>
                                <input type="color" id="text-color-picker" value="#000000" onchange="changeColor()" class="w-4 h-4 p-0 border-0 cursor-pointer">
                            </label>
                            <button type="button" onclick="execCmd('removeFormat')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="서식 제거">✕ 서식제거</button>
                        </div>

                        <!-- WYSIWYG Editor -->
                        <div id="wysiwyg-editor" contenteditable="true"
                            class="w-full min-h-[380px] max-h-[600px] overflow-y-auto text-sm border border-gray-300 rounded-b px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            style="line-height:1.6" oninput="updateChecklist()"></div>

                        <!-- HTML Textarea (hidden by default) -->
                        <div id="editor-wrap" class="hidden">
                            <textarea id="email-body" rows="18" placeholder="HTML 본문을 입력하세요. {{name}} 을 사용하면 수신자 이름으로 치환됩니다."
                                class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 font-mono focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                oninput="updateChecklist()"></textarea>
                        </div>

                        <!-- Preview (hidden by default) -->
                        <div id="preview-wrap" class="hidden border border-gray-300 rounded p-3 min-h-[200px] text-sm overflow-auto bg-white"></div>

                        <!-- Hidden file input for image upload -->
                        <input type="file" id="image-upload-input" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="uploadImage(this)">

                        <div class="text-xs text-gray-400 mt-1.5">
                            <code class="bg-gray-100 px-1 rounded">{{name}}</code> &rarr; 수신자 이름으로 자동 치환됩니다 &nbsp;|&nbsp;
                            📷 이미지: 5MB 이하 JPG/PNG/GIF/WebP
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Summary (2/5) -->
                <div class="lg:col-span-2 space-y-3">

                    <!-- Readiness Checklist -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">발송 준비 상태</h3>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm" id="check-recipient">
                                <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0" id="check-recipient-dot"></span>
                                <span class="text-gray-500">수신자 설정</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm" id="check-subject">
                                <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0" id="check-subject-dot"></span>
                                <span class="text-gray-500">제목 입력</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm" id="check-body">
                                <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0" id="check-body-dot"></span>
                                <span class="text-gray-500">본문 작성</span>
                            </div>
                        </div>
                    </div>

                    <!-- Send Info Summary -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">발송 정보 요약</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">발신자</dt>
                                <dd class="text-gray-900 font-medium">두손기획인쇄</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">발신 이메일</dt>
                                <dd class="text-gray-700 text-xs">dsp1830@naver.com</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-500">수신자</dt>
                                <dd class="font-bold text-lg text-brand" id="summary-recipients">0명</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">예상 소요</dt>
                                <dd class="text-gray-700" id="summary-time">~0분</dd>
                            </div>
                            <div class="flex justify-between items-start">
                                <dt class="text-gray-500">Gmail 수신자</dt>
                                <dd class="text-right">
                                    <span id="summary-gmail" class="text-gray-700">0명</span>
                                    <div class="text-xs text-orange-500 mt-0.5" id="gmail-warning" style="display:none">스팸 분류 가능성 있음</div>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        <button onclick="startSend()" id="btn-send"
                            class="w-full py-2.5 text-sm font-bold text-white rounded-lg shadow bg-[#1E4E79] hover:bg-[#153A5A] transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <span id="send-btn-text">이메일 발송</span>
                        </button>
                        <div class="grid grid-cols-2 gap-2">
                            <button onclick="sendTest()" class="py-2 text-sm border border-gray-300 rounded hover:bg-gray-50 text-gray-700 transition-colors">
                                미리보기 발송
                            </button>
                            <button onclick="saveDraft()" class="py-2 text-sm border border-gray-300 rounded hover:bg-gray-50 text-gray-700 transition-colors">
                                임시저장
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 text-center">미리보기 발송: dsp1830@naver.com 으로 테스트</p>
                    </div>

                    <!-- Naver SMTP Limits (collapsible) -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg overflow-hidden">
                        <button onclick="toggleSmtpInfo()" class="w-full flex items-center justify-between p-3 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition-colors">
                            <span>⚠️ 네이버 SMTP 제한</span>
                            <svg id="smtp-chevron" class="w-4 h-4 transform rotate-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="smtp-detail" class="hidden px-3 pb-3">
                            <ul class="text-xs text-amber-700 space-y-1">
                                <li>• 1회 최대 100명 발송</li>
                                <li>• 일일 ~500통 안전 한도</li>
                                <li>• 배치 간 5초 딜레이 적용</li>
                                <li>• Gmail 수신 시 스팸 분류 가능</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Area (hidden by default) -->
            <div id="progress-area" class="hidden mt-3 bg-white rounded-lg shadow p-3">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-gray-700">발송 진행</span>
                    <div class="flex items-center gap-2">
                        <span id="progress-text" class="text-xs text-gray-600">대기 중...</span>
                        <button onclick="cancelSend()" id="btn-cancel-send" class="text-[10px] px-2 py-0.5 bg-red-500 text-white rounded hover:bg-red-600">중지</button>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div id="progress-bar" class="h-full rounded-full transition-all duration-300" style="width:0%;background:#1E4E79"></div>
                </div>
                <div class="flex justify-between mt-1 text-[10px] text-gray-400">
                    <span>성공: <span id="progress-success" class="text-green-600 font-medium">0</span> / 실패: <span id="progress-fail" class="text-red-500 font-medium">0</span></span>
                    <span id="progress-pct">0%</span>
                </div>
            </div>
        </div>

        <!-- ===== TAB 2: History ===== -->
        <div id="panel-history" class="hidden">
            <div class="flex items-center gap-3 mb-3">
                <h1 class="text-2xl font-bold text-gray-900">발송 이력</h1>
                <span class="text-sm text-gray-500">캠페인 발송 기록을 확인합니다</span>
                <button onclick="loadCampaigns()" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 ml-auto">새로고침</button>
            </div>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500">ID</th>
                                <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500">제목</th>
                                <th class="px-3 py-2.5 text-center text-xs font-medium text-gray-500">수신자</th>
                                <th class="px-3 py-2.5 text-center text-xs font-medium text-gray-500">성공/실패</th>
                                <th class="px-3 py-2.5 text-center text-xs font-medium text-gray-500">상태</th>
                                <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500">발송일</th>
                            </tr>
                        </thead>
                        <tbody id="campaigns-tbody" class="bg-white divide-y divide-gray-200">
                            <tr><td colspan="6" class="px-2 py-4 text-center text-gray-400 text-xs">로딩 중...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="campaigns-pagination" class="px-3 py-1.5 border-t border-gray-200 flex items-center justify-between text-xs">
                    <span class="text-gray-500">총 <span id="campaigns-total">0</span>건</span>
                    <div id="campaigns-page-btns" class="flex items-center gap-1"></div>
                </div>
            </div>
        </div>

        <!-- ===== TAB 3: Templates ===== -->
        <div id="panel-templates" class="hidden">
            <div class="flex items-center gap-3 mb-3">
                <h1 class="text-2xl font-bold text-gray-900">템플릿 관리</h1>
                <span class="text-sm text-gray-500">자주 사용하는 이메일 양식을 관리합니다</span>
                <div class="ml-auto flex items-center gap-2">
                    <button onclick="saveCurrentAsTemplate()" class="text-xs px-3 py-1.5 border border-gray-300 rounded hover:bg-gray-50 text-gray-700 transition-colors">현재 작성 내용 저장</button>
                    <button onclick="createNewTemplate()" class="text-xs px-3 py-1.5 text-white rounded bg-[#1E4E79] hover:bg-[#153A5A] transition-colors">+ 새 템플릿 만들기</button>
                </div>
            </div>
            <div id="templates-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                <div class="text-xs text-gray-400 p-4">로딩 중...</div>
            </div>
        </div>

    </div>

    <!-- Mobile Sticky Send Bar -->
    <div id="mobile-send-bar" class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg p-3 z-40">
        <div class="flex items-center gap-3">
            <div class="flex-1 text-sm text-gray-600">
                수신자: <span id="mobile-recipient-count" class="font-bold text-brand">0명</span>
            </div>
            <button onclick="startSend()" id="mobile-btn-send"
                class="px-6 py-2.5 text-sm font-bold text-white rounded-lg bg-[#1E4E79] hover:bg-[#153A5A] transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                발송
            </button>
        </div>
    </div>
</main>

<!-- Campaign Detail Modal -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeDetailModal()"></div>
    <div class="absolute inset-4 md:inset-x-auto md:inset-y-8 md:max-w-4xl md:mx-auto bg-white rounded-lg shadow-xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200">
            <h3 class="text-sm font-bold text-gray-900">발송 상세</h3>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>
        <div id="modal-detail-body" class="flex-1 overflow-y-auto p-4 text-xs"></div>
    </div>
</div>

<!-- Template Preview Modal -->
<div id="modal-preview" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closePreviewModal()"></div>
    <div class="absolute inset-4 md:inset-x-auto md:inset-y-6 md:max-w-3xl md:mx-auto bg-white rounded-lg shadow-xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div>
                <h3 class="text-sm font-bold text-gray-900">템플릿 미리보기</h3>
                <p id="preview-template-name" class="text-xs text-gray-500 mt-0.5"></p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="usePreviewedTemplate()" class="text-xs px-3 py-1.5 text-white rounded bg-[#1E4E79] hover:bg-[#153A5A] transition-colors">이 템플릿 사용</button>
                <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>
        </div>
        <div class="px-4 py-2 border-b border-gray-100 bg-white">
            <span class="text-xs text-gray-500">제목:</span>
            <span id="preview-template-subject" class="text-sm font-medium text-gray-900 ml-1"></span>
        </div>
        <div id="preview-template-body" class="flex-1 overflow-y-auto p-4 text-sm"></div>
    </div>
</div>

<!-- Template Edit Modal (WYSIWYG) -->
<div id="modal-template" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeTemplateModal()"></div>
    <div class="absolute inset-4 md:inset-x-auto md:inset-y-4 md:max-w-4xl md:mx-auto bg-white rounded-lg shadow-xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-bold text-gray-900" id="template-modal-title">새 템플릿 만들기</h3>
            <button onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <input type="hidden" id="tpl-edit-id" value="">

            <!-- Name + Subject row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-700">템플릿 이름</label>
                    <input type="text" id="tpl-edit-name" class="w-full text-sm border border-gray-300 rounded px-3 py-2 mt-1 focus:ring-1 focus:ring-blue-500" placeholder="예: 추석 인사, 여름휴가 안내">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700">이메일 제목</label>
                    <input type="text" id="tpl-edit-subject" class="w-full text-sm border border-gray-300 rounded px-3 py-2 mt-1 focus:ring-1 focus:ring-blue-500" placeholder="수신자에게 보일 이메일 제목">
                </div>
            </div>

            <!-- Body editor -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-semibold text-gray-700">본문</label>
                    <div class="inline-flex bg-gray-100 rounded-lg p-0.5">
                        <button onclick="switchTplEditorMode('wysiwyg')" id="tpl-btn-mode-wysiwyg" class="text-xs px-3 py-1 rounded-md bg-white text-brand shadow-sm font-medium">편집기</button>
                        <button onclick="switchTplEditorMode('html')" id="tpl-btn-mode-html" class="text-xs px-3 py-1 rounded-md text-gray-600 hover:text-gray-900">HTML</button>
                        <button onclick="switchTplEditorMode('preview')" id="tpl-btn-mode-preview" class="text-xs px-3 py-1 rounded-md text-gray-600 hover:text-gray-900">미리보기</button>
                    </div>
                </div>

                <!-- Toolbar -->
                <div id="tpl-toolbar-wrap" class="flex flex-wrap items-center gap-1 p-2 bg-gray-50 border border-gray-300 border-b-0 rounded-t">
                    <button type="button" onclick="tplExecCmd('bold')" class="px-2 py-1 text-xs font-bold border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="굵게">B</button>
                    <button type="button" onclick="tplExecCmd('italic')" class="px-2 py-1 text-xs italic border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="기울임">I</button>
                    <button type="button" onclick="tplExecCmd('underline')" class="px-2 py-1 text-xs underline border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer" title="밑줄">U</button>
                    <span class="w-px h-5 bg-gray-300 mx-0.5"></span>
                    <button type="button" onclick="tplExecCmd('formatBlock','<h1>')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">H1</button>
                    <button type="button" onclick="tplExecCmd('formatBlock','<h2>')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">H2</button>
                    <button type="button" onclick="tplExecCmd('formatBlock','<p>')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">P</button>
                    <span class="w-px h-5 bg-gray-300 mx-0.5"></span>
                    <button type="button" onclick="tplInsertLink()" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">🔗 링크</button>
                    <button type="button" onclick="tplInsertImage()" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">📷 이미지</button>
                    <span class="w-px h-5 bg-gray-300 mx-0.5"></span>
                    <button type="button" onclick="tplExecCmd('insertUnorderedList')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">• 목록</button>
                    <button type="button" onclick="tplExecCmd('insertOrderedList')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">1. 목록</button>
                    <button type="button" onclick="tplExecCmd('insertHorizontalRule')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">─</button>
                    <span class="w-px h-5 bg-gray-300 mx-0.5"></span>
                    <label class="flex items-center gap-1 px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">
                        <span class="text-xs">색</span>
                        <input type="color" id="tpl-text-color-picker" value="#000000" onchange="tplChangeColor()" class="w-4 h-4 p-0 border-0 cursor-pointer">
                    </label>
                    <button type="button" onclick="tplExecCmd('removeFormat')" class="px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-200 bg-white cursor-pointer">✕ 서식제거</button>
                </div>

                <!-- WYSIWYG Editor -->
                <div id="tpl-wysiwyg-editor" contenteditable="true"
                    class="w-full min-h-[350px] max-h-[500px] overflow-y-auto text-sm border border-gray-300 rounded-b px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                    style="line-height:1.6"></div>

                <!-- HTML Textarea (hidden) -->
                <div id="tpl-html-wrap" class="hidden">
                    <textarea id="tpl-edit-body" rows="16" placeholder="HTML 본문을 직접 입력하세요. {{name}} → 수신자 이름 치환"
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 font-mono focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <!-- Preview (hidden) -->
                <div id="tpl-preview-wrap" class="hidden border border-gray-300 rounded p-4 min-h-[200px] text-sm overflow-auto bg-white"></div>

                <!-- Hidden file input for image upload -->
                <input type="file" id="tpl-image-upload-input" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="tplUploadImage(this)">

                <div class="text-xs text-gray-400 mt-1">
                    <code class="bg-gray-100 px-1 rounded">{{name}}</code> → 수신자 이름 자동 치환 &nbsp;|&nbsp; 📷 이미지: 5MB 이하
                </div>
            </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
            <span class="text-xs text-gray-400" id="tpl-save-hint"></span>
            <div class="flex gap-2">
                <button onclick="closeTemplateModal()" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">취소</button>
                <button onclick="submitTemplate()" class="px-4 py-2 text-sm text-white rounded bg-[#1E4E79] hover:bg-[#153A5A] transition-colors">저장</button>
            </div>
        </div>
    </div>
</div>

<script>
var API = '/dashboard/api/email.php';
var sendCancelled = false;
var currentCampaignId = null;
var recipientCache = { all: null, filtered: null };
var campaignPage = 1;

// ==================== TAB SWITCHING ====================
function switchTab(tab) {
    var tabs = ['compose', 'history', 'templates'];
    tabs.forEach(function(t) {
        document.getElementById('panel-' + t).classList.toggle('hidden', t !== tab);
        var tabBtn = document.getElementById('tab-' + t);
        if (t === tab) {
            tabBtn.classList.add('border-brand', 'text-brand');
            tabBtn.classList.remove('border-transparent', 'text-gray-500');
        } else {
            tabBtn.classList.remove('border-brand', 'text-brand');
            tabBtn.classList.add('border-transparent', 'text-gray-500');
        }
    });
    if (tab === 'history') loadCampaigns();
    if (tab === 'templates') loadTemplatesList();
    var mobileBar = document.getElementById('mobile-send-bar');
    if (mobileBar) mobileBar.style.display = (tab === 'compose') ? '' : 'none';
}

// ==================== RECIPIENTS ====================
function getRecipientType() {
    var radios = document.querySelectorAll('input[name="recipient_type"]');
    for (var i = 0; i < radios.length; i++) { if (radios[i].checked) return radios[i].value; }
    return 'all';
}

function onRecipientTypeChange() {
    var type = getRecipientType();
    document.getElementById('filter-options').classList.toggle('hidden', type !== 'filtered');
    document.getElementById('manual-input').classList.toggle('hidden', type !== 'manual');
    updateRecipientCount();
}

function updateRecipientCount() {
    var type = getRecipientType();
    if (type === 'manual') { updateManualCount(); return; }

    var params = new URLSearchParams({ action: 'get_recipients', type: type });
    if (type === 'filtered') {
        var months = document.getElementById('filter-login-months').value;
        var domain = document.getElementById('filter-domain').value;
        if (months) params.set('login_months', months);
        if (domain) params.set('domain', domain);
    }

    fetch(API + '?' + params).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) return;
        var count = res.data.count;
        var gmailCount = res.data.gmail_count || 0;
        document.getElementById('badge-all').textContent = count + '명';
        if (type === 'filtered') document.getElementById('filtered-count').textContent = count;
        updateSummary(count, gmailCount);
        recipientCache[type] = res.data;
    });
}

function updateManualCount() {
    var raw = document.getElementById('manual-emails').value.trim();
    if (!raw) { updateSummary(0, 0); document.getElementById('manual-count').textContent = '0'; return; }
    var emails = raw.split(/[,\n]+/).map(function(e) { return e.trim(); }).filter(function(e) { return e && e.indexOf('@') > 0; });
    var gmail = emails.filter(function(e) { return e.toLowerCase().indexOf('@gmail.com') > -1; }).length;
    document.getElementById('manual-count').textContent = emails.length;
    updateSummary(emails.length, gmail);
}

function updateSummary(count, gmail) {
    document.getElementById('summary-recipients').textContent = count + '명';
    var minutes = Math.max(1, Math.ceil(count / 100 * 5 / 60));
    document.getElementById('summary-time').textContent = '~' + minutes + '분';
    document.getElementById('summary-gmail').textContent = gmail + '명';
    document.getElementById('gmail-warning').style.display = gmail > 0 ? '' : 'none';
    var mobileCount = document.getElementById('mobile-recipient-count');
    if (mobileCount) mobileCount.textContent = count + '명';
    updateChecklist();
}

// ==================== EDITOR MODES ====================
var currentEditorMode = 'wysiwyg'; // 'wysiwyg' | 'html' | 'preview'

function switchEditorMode(mode) {
    var prevMode = currentEditorMode;
    currentEditorMode = mode;

    // Sync content between modes
    if (prevMode === 'wysiwyg' && mode !== 'wysiwyg') {
        document.getElementById('email-body').value = document.getElementById('wysiwyg-editor').innerHTML;
    }
    if (prevMode === 'html' && mode !== 'html') {
        document.getElementById('wysiwyg-editor').innerHTML = document.getElementById('email-body').value;
    }

    // Toggle visibility
    document.getElementById('toolbar-wrap').classList.toggle('hidden', mode !== 'wysiwyg');
    document.getElementById('wysiwyg-editor').classList.toggle('hidden', mode !== 'wysiwyg');
    document.getElementById('editor-wrap').classList.toggle('hidden', mode !== 'html');
    document.getElementById('preview-wrap').classList.toggle('hidden', mode !== 'preview');

    if (mode === 'preview') {
        var html = prevMode === 'wysiwyg'
            ? document.getElementById('wysiwyg-editor').innerHTML
            : document.getElementById('email-body').value;
        document.getElementById('preview-wrap').innerHTML = html;
    }

    // Update pill toggle styles
    ['wysiwyg', 'html', 'preview'].forEach(function(m) {
        var btn = document.getElementById('btn-mode-' + m);
        if (m === mode) {
            btn.classList.add('bg-white', 'text-brand', 'shadow-sm', 'font-medium');
            btn.classList.remove('text-gray-600', 'hover:text-gray-900');
        } else {
            btn.classList.remove('bg-white', 'text-brand', 'shadow-sm', 'font-medium');
            btn.classList.add('text-gray-600', 'hover:text-gray-900');
        }
    });
}

// ==================== TOOLBAR COMMANDS ====================
function execCmd(command, value) {
    document.getElementById('wysiwyg-editor').focus();
    document.execCommand(command, false, value || null);
}

function insertLink() {
    var url = prompt('URL을 입력하세요:', 'https://');
    if (url && url !== 'https://') execCmd('createLink', url);
}

function insertImage() {
    document.getElementById('image-upload-input').click();
}

function uploadImage(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('5MB 이하 이미지만 가능합니다', 'warning'); input.value = ''; return; }

    var formData = new FormData();
    formData.append('action', 'upload_image');
    formData.append('image', file);

    showToast('이미지 업로드 중...', 'info');

    fetch(API, {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); input.value = ''; return; }

        var imgTag = '<img src="' + res.data.url + '" alt="uploaded image" style="max-width:100%;height:auto;">';
        if (currentEditorMode === 'wysiwyg') {
            document.getElementById('wysiwyg-editor').focus();
            document.execCommand('insertHTML', false, imgTag);
        } else {
            var ta = document.getElementById('email-body');
            var pos = ta.selectionStart || ta.value.length;
            ta.value = ta.value.substring(0, pos) + imgTag + ta.value.substring(pos);
        }
        showToast('이미지가 삽입되었습니다', 'success');
        input.value = '';
    }).catch(function() { showToast('업로드 실패', 'error'); input.value = ''; });
}

function changeColor() {
    var color = document.getElementById('text-color-picker').value;
    execCmd('foreColor', color);
}

// Helper: get email body from whichever mode is active
function getEmailBody() {
    if (currentEditorMode === 'wysiwyg') {
        document.getElementById('email-body').value = document.getElementById('wysiwyg-editor').innerHTML;
    }
    return document.getElementById('email-body').value.trim();
}

// ==================== TEMPLATES (dropdown) ====================
function loadTemplateOptions() {
    fetch(API + '?action=templates').then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) return;
        var sel = document.getElementById('template-select');
        sel.innerHTML = '<option value="">-- 템플릿 선택 --</option>';
        res.data.forEach(function(t) {
            var opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            sel.appendChild(opt);
        });
    });
}

function onTemplateSelect(id) {
    if (!id) return;
    fetch(API + '?action=load_template&id=' + id).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); return; }
        document.getElementById('email-subject').value = res.data.subject;
        document.getElementById('email-body').value = res.data.body_html;
        document.getElementById('wysiwyg-editor').innerHTML = res.data.body_html;
        document.getElementById('subject-length').textContent = res.data.subject.length;
        showToast('템플릿을 불러왔습니다', 'success');
    });
}

function loadTemplate() {
    var sel = document.getElementById('template-select');
    if (sel.value) onTemplateSelect(sel.value);
    else showToast('템플릿을 선택해주세요', 'warning');
}

// ==================== SEND TEST ====================
function sendTest() {
    var subject = document.getElementById('email-subject').value.trim();
    var body = getEmailBody();
    if (!subject || !body) { showToast('제목과 본문을 입력해주세요', 'warning'); return; }
    if (!confirm('dsp1830@naver.com 으로 테스트 메일을 발송합니다.')) return;

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'send_test', subject: subject, body_html: body })
    }).then(function(r) { return r.json(); }).then(function(res) {
        showToast(res.message, res.success ? 'success' : 'error');
    }).catch(function() { showToast('네트워크 오류', 'error'); });
}

// ==================== SAVE DRAFT ====================
function saveDraft() {
    var subject = document.getElementById('email-subject').value.trim();
    var body = getEmailBody();
    if (!subject) { showToast('제목을 입력해주세요', 'warning'); return; }

    var type = getRecipientType();
    var params = {
        action: 'save_draft',
        subject: subject,
        body_html: body,
        recipient_type: type
    };
    if (type === 'filtered') {
        var months = document.getElementById('filter-login-months').value;
        var domain = document.getElementById('filter-domain').value;
        if (months) params.login_months = months;
        if (domain) params.domain = domain;
    }
    if (type === 'manual') params.recipient_emails = document.getElementById('manual-emails').value.trim();

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(params)
    }).then(function(r) { return r.json(); }).then(function(res) {
        showToast(res.message, res.success ? 'success' : 'error');
    }).catch(function() { showToast('네트워크 오류', 'error'); });
}

// ==================== SEND CAMPAIGN ====================
function startSend() {
    var subject = document.getElementById('email-subject').value.trim();
    var body = getEmailBody();
    if (!subject || !body) { showToast('제목과 본문을 입력해주세요', 'warning'); return; }

    var recipientText = document.getElementById('summary-recipients').textContent;
    var count = parseInt(recipientText) || 0;
    if (count === 0) { showToast('수신자가 없습니다', 'warning'); return; }

    if (!confirm(count + '명에게 이메일을 발송합니다. 계속하시겠습니까?')) return;

    sendCancelled = false;
    var type = getRecipientType();
    var params = {
        action: 'send',
        subject: subject,
        body_html: body,
        recipient_type: type
    };
    if (type === 'filtered') {
        var months = document.getElementById('filter-login-months').value;
        var domain = document.getElementById('filter-domain').value;
        if (months) params.login_months = months;
        if (domain) params.domain = domain;
    }
    if (type === 'manual') params.recipient_emails = document.getElementById('manual-emails').value.trim();

    document.getElementById('btn-send').disabled = true;
    document.getElementById('send-btn-text').textContent = '발송 준비 중...';
    document.getElementById('progress-area').classList.remove('hidden');
    updateProgress(0, 0, 0, count);

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(params)
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); resetSendUI(); return; }
        currentCampaignId = res.data.campaign_id;
        var total = res.data.total_recipients;
        sendNextBatch(0, total, 0, 0);
    }).catch(function(e) { showToast('발송 시작 실패: ' + e.message, 'error'); resetSendUI(); });
}

function sendNextBatch(offset, total, successCount, failCount) {
    if (sendCancelled) {
        showToast('발송이 중지되었습니다', 'warning');
        resetSendUI();
        return;
    }
    if (offset >= total) {
        updateProgress(successCount, failCount, total, total);
        document.getElementById('progress-text').textContent = '발송 완료!';
        showToast('발송 완료! 성공: ' + successCount + ', 실패: ' + failCount, 'success');
        resetSendUI();
        return;
    }

    document.getElementById('send-btn-text').textContent = '발송 중...';
    var params = new URLSearchParams({
        action: 'send_batch',
        campaign_id: currentCampaignId,
        offset: offset
    });

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); resetSendUI(); return; }
        var newSuccess = successCount + (res.data.sent_count || 0);
        var newFail = failCount + (res.data.fail_count || 0);
        var newOffset = offset + (res.data.batch_size || 100);
        updateProgress(newSuccess, newFail, newOffset, total);

        if (res.data.is_last_batch) {
            document.getElementById('progress-text').textContent = '발송 완료!';
            showToast('발송 완료! 성공: ' + newSuccess + ', 실패: ' + newFail, 'success');
            resetSendUI();
        } else {
            document.getElementById('progress-text').textContent = '다음 배치 준비 중... (3초 대기)';
            setTimeout(function() { sendNextBatch(newOffset, total, newSuccess, newFail); }, 3000);
        }
    }).catch(function(e) {
        showToast('배치 발송 실패: ' + e.message, 'error');
        resetSendUI();
    });
}

function updateProgress(success, fail, sent, total) {
    var pct = total > 0 ? Math.round(sent / total * 100) : 0;
    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('progress-pct').textContent = pct + '%';
    document.getElementById('progress-text').textContent = '발송 중... ' + sent + '/' + total + ' (' + pct + '%)';
    document.getElementById('progress-success').textContent = success;
    document.getElementById('progress-fail').textContent = fail;
}

function cancelSend() {
    if (confirm('발송을 중지하시겠습니까? 이미 발송된 메일은 취소되지 않습니다.')) {
        sendCancelled = true;
    }
}

function resetSendUI() {
    document.getElementById('send-btn-text').textContent = '이메일 발송';
    currentCampaignId = null;
    updateChecklist();
}

// ==================== CAMPAIGNS (History) ====================
function loadCampaigns(page) {
    campaignPage = page || 1;
    fetch(API + '?action=campaigns&page=' + campaignPage).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) return;
        var tbody = document.getElementById('campaigns-tbody');
        tbody.innerHTML = '';
        var campaigns = res.data.data;
        document.getElementById('campaigns-total').textContent = res.data.pagination.total_items;

        if (campaigns.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-2 py-4 text-center text-gray-400 text-xs">발송 이력이 없습니다</td></tr>';
            return;
        }

        campaigns.forEach(function(c, idx) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 cursor-pointer' + (idx % 2 === 1 ? ' bg-blue-50/30' : '');
            tr.onclick = function() { showCampaignDetail(c.id); };

            var statusMap = {
                draft: '<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">임시저장</span>',
                sending: '<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs animate-pulse">발송중</span>',
                completed: '<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">완료</span>',
                failed: '<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs">실패</span>',
                cancelled: '<span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-xs">중지</span>'
            };

            tr.innerHTML = '<td class="px-3 py-2.5 text-xs text-gray-500">' + c.id + '</td>' +
                '<td class="px-3 py-2.5 text-xs text-gray-900 font-medium max-w-[200px] truncate">' + escHtml(c.subject) + '</td>' +
                '<td class="px-3 py-2.5 text-xs text-center text-gray-600">' + c.total_recipients + '명</td>' +
                '<td class="px-3 py-2.5 text-xs text-center"><span class="text-green-600">' + c.sent_count + '</span>/<span class="text-red-500">' + c.fail_count + '</span></td>' +
                '<td class="px-3 py-2.5 text-center">' + (statusMap[c.status] || c.status) + '</td>' +
                '<td class="px-3 py-2.5 text-xs text-gray-500">' + (c.started_at || c.created_at || '-') + '</td>';
            tbody.appendChild(tr);
        });

        renderCampaignPagination(res.data.pagination);
    });
}

function renderCampaignPagination(pg) {
    var container = document.getElementById('campaigns-page-btns');
    container.innerHTML = '';
    if (pg.total_pages <= 1) return;
    for (var i = 1; i <= pg.total_pages; i++) {
        var btn = document.createElement('button');
        btn.textContent = i;
        btn.className = i === pg.current_page
            ? 'px-2 py-0.5 text-xs rounded border border-blue-600 bg-blue-600 text-white'
            : 'px-2 py-0.5 text-xs rounded border border-gray-300 text-gray-700 hover:bg-gray-50';
        btn.onclick = (function(p) { return function() { loadCampaigns(p); }; })(i);
        container.appendChild(btn);
    }
}

function showCampaignDetail(id) {
    fetch(API + '?action=campaign_detail&id=' + id).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); return; }
        var c = res.data.campaign;
        var logs = res.data.logs;

        var html = '<div class="space-y-3">';
        html += '<div class="grid grid-cols-2 gap-2">';
        html += '<div><span class="text-gray-500">제목:</span> <span class="font-medium">' + escHtml(c.subject) + '</span></div>';
        html += '<div><span class="text-gray-500">상태:</span> ' + c.status + '</div>';
        html += '<div><span class="text-gray-500">수신자:</span> ' + c.total_recipients + '명</div>';
        html += '<div><span class="text-gray-500">성공/실패:</span> <span class="text-green-600">' + c.sent_count + '</span>/<span class="text-red-500">' + c.fail_count + '</span></div>';
        html += '<div><span class="text-gray-500">시작:</span> ' + (c.started_at || '-') + '</div>';
        html += '<div><span class="text-gray-500">완료:</span> ' + (c.completed_at || '-') + '</div>';
        html += '</div>';

        html += '<div class="border-t border-gray-200 pt-2"><span class="font-semibold">발송 로그</span> (' + logs.length + '건)</div>';
        html += '<table class="min-w-full text-xs"><thead class="bg-gray-50"><tr><th class="px-2 py-1 text-left">이메일</th><th class="px-2 py-1 text-left">이름</th><th class="px-2 py-1 text-center">상태</th><th class="px-2 py-1 text-left">발송시각</th></tr></thead><tbody>';
        logs.forEach(function(l) {
            var sc = l.status === 'sent' ? 'text-green-600' : (l.status === 'failed' ? 'text-red-500' : 'text-gray-400');
            var label = l.status === 'sent' ? '성공' : (l.status === 'failed' ? '실패' : '대기');
            html += '<tr class="border-t border-gray-100"><td class="px-2 py-1">' + escHtml(l.recipient_email) + '</td><td class="px-2 py-1">' + escHtml(l.recipient_name || '-') + '</td><td class="px-2 py-1 text-center ' + sc + '">' + label + '</td><td class="px-2 py-1 text-gray-500">' + (l.sent_at || '-') + '</td></tr>';
        });
        html += '</tbody></table></div>';

        document.getElementById('modal-detail-body').innerHTML = html;
        document.getElementById('modal-detail').classList.remove('hidden');
    });
}

function closeDetailModal() { document.getElementById('modal-detail').classList.add('hidden'); }

// ==================== TEMPLATES (Tab 3) ====================
function loadTemplatesList() {
    fetch(API + '?action=templates').then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) return;
        var grid = document.getElementById('templates-grid');
        grid.innerHTML = '';

        if (res.data.length === 0) {
            grid.innerHTML = '<div class="text-xs text-gray-400 p-4 col-span-3">저장된 템플릿이 없습니다</div>';
            return;
        }

        res.data.forEach(function(t) {
            var card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow p-4 space-y-2 hover:shadow-md transition-shadow';
            card.innerHTML =
                '<div class="flex items-start justify-between">' +
                    '<h4 class="text-sm font-bold text-gray-900 truncate flex-1">' + escHtml(t.name) + '</h4>' +
                    '<span class="text-xs text-gray-400 ml-2 flex-shrink-0">#' + t.id + '</span>' +
                '</div>' +
                '<p class="text-xs text-gray-600 truncate">' + escHtml(t.subject) + '</p>' +
                '<div class="text-xs text-gray-400 line-clamp-2 h-8 overflow-hidden">' + stripHtml(t.body_html || '') + '</div>' +
                '<div class="text-xs text-gray-400">' + (t.updated_at || t.created_at) + '</div>' +
                '<div class="flex gap-1.5 pt-2 border-t border-gray-100">' +
                    '<button onclick="previewTemplate(' + t.id + ')" class="flex-1 text-xs py-1.5 border border-blue-300 text-blue-600 rounded hover:bg-blue-50 transition-colors">미리보기</button>' +
                    '<button onclick="useTemplate(' + t.id + ')" class="flex-1 text-xs py-1.5 text-white rounded bg-[#1E4E79] hover:bg-[#153A5A] transition-colors">사용하기</button>' +
                    '<button onclick="editTemplate(' + t.id + ')" class="flex-1 text-xs py-1.5 border border-gray-300 rounded hover:bg-gray-50">수정</button>' +
                    '<button onclick="deleteTemplate(' + t.id + ')" class="text-xs py-1.5 px-2 text-red-500 border border-red-200 rounded hover:bg-red-50">삭제</button>' +
                '</div>';
            grid.appendChild(card);
        });
    });
}

function useTemplate(id) {
    fetch(API + '?action=load_template&id=' + id).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); return; }
        document.getElementById('email-subject').value = res.data.subject;
        document.getElementById('email-body').value = res.data.body_html;
        document.getElementById('wysiwyg-editor').innerHTML = res.data.body_html;
        document.getElementById('subject-length').textContent = res.data.subject.length;
        switchTab('compose');
        showToast('템플릿을 불러왔습니다', 'success');
    });
}

function editTemplate(id) {
    fetch(API + '?action=load_template&id=' + id).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); return; }
        document.getElementById('tpl-edit-id').value = id;
        document.getElementById('tpl-edit-name').value = res.data.name;
        document.getElementById('tpl-edit-subject').value = res.data.subject;
        document.getElementById('tpl-edit-body').value = res.data.body_html;
        document.getElementById('tpl-wysiwyg-editor').innerHTML = res.data.body_html;
        document.getElementById('template-modal-title').textContent = '템플릿 수정';
        document.getElementById('tpl-save-hint').textContent = '#' + id + ' 수정 중';
        switchTplEditorMode('wysiwyg');
        document.getElementById('modal-template').classList.remove('hidden');
    });
}

function deleteTemplate(id) {
    if (!confirm('이 템플릿을 삭제하시겠습니까?')) return;
    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'delete_template', id: id })
    }).then(function(r) { return r.json(); }).then(function(res) {
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) { loadTemplatesList(); loadTemplateOptions(); }
    });
}

var previewingTemplateId = null;

function previewTemplate(id) {
    fetch(API + '?action=load_template&id=' + id).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); return; }
        previewingTemplateId = id;
        document.getElementById('preview-template-name').textContent = res.data.name;
        document.getElementById('preview-template-subject').textContent = res.data.subject;
        document.getElementById('preview-template-body').innerHTML = res.data.body_html;
        document.getElementById('modal-preview').classList.remove('hidden');
    });
}

function closePreviewModal() {
    document.getElementById('modal-preview').classList.add('hidden');
    previewingTemplateId = null;
}

function usePreviewedTemplate() {
    if (previewingTemplateId) {
        closePreviewModal();
        useTemplate(previewingTemplateId);
    }
}

function createNewTemplate() {
    document.getElementById('tpl-edit-id').value = '';
    document.getElementById('tpl-edit-name').value = '';
    document.getElementById('tpl-edit-subject').value = '';
    document.getElementById('tpl-edit-body').value = '';
    document.getElementById('tpl-wysiwyg-editor').innerHTML = '';
    document.getElementById('template-modal-title').textContent = '새 템플릿 만들기';
    document.getElementById('tpl-save-hint').textContent = '';
    switchTplEditorMode('wysiwyg');
    document.getElementById('modal-template').classList.remove('hidden');
}

function saveCurrentAsTemplate() {
    var subject = document.getElementById('email-subject').value.trim();
    var body = getEmailBody();
    document.getElementById('tpl-edit-id').value = '';
    document.getElementById('tpl-edit-name').value = '';
    document.getElementById('tpl-edit-subject').value = subject;
    document.getElementById('tpl-edit-body').value = body;
    document.getElementById('tpl-wysiwyg-editor').innerHTML = body;
    document.getElementById('template-modal-title').textContent = '현재 내용을 템플릿으로 저장';
    document.getElementById('tpl-save-hint').textContent = '작성 탭의 내용이 불러와졌습니다';
    switchTplEditorMode('wysiwyg');
    document.getElementById('modal-template').classList.remove('hidden');
}

function getTplBody() {
    if (tplEditorMode === 'wysiwyg') {
        document.getElementById('tpl-edit-body').value = document.getElementById('tpl-wysiwyg-editor').innerHTML;
    }
    return document.getElementById('tpl-edit-body').value.trim();
}

function submitTemplate() {
    var id = document.getElementById('tpl-edit-id').value;
    var name = document.getElementById('tpl-edit-name').value.trim();
    var subject = document.getElementById('tpl-edit-subject').value.trim();
    var body = getTplBody();
    if (!name || !subject) { showToast('이름과 제목을 입력해주세요', 'warning'); return; }
    if (!body) { showToast('본문을 입력해주세요', 'warning'); return; }

    var params = { action: 'save_template', name: name, subject: subject, body_html: body };
    if (id) params.id = id;

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(params)
    }).then(function(r) { return r.json(); }).then(function(res) {
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) { closeTemplateModal(); loadTemplatesList(); loadTemplateOptions(); }
    });
}

function closeTemplateModal() {
    document.getElementById('modal-template').classList.add('hidden');
}

// ==================== TEMPLATE EDITOR ====================
var tplEditorMode = 'wysiwyg';

function switchTplEditorMode(mode) {
    var prev = tplEditorMode;
    tplEditorMode = mode;

    // Sync content between modes
    if (prev === 'wysiwyg' && mode !== 'wysiwyg') {
        document.getElementById('tpl-edit-body').value = document.getElementById('tpl-wysiwyg-editor').innerHTML;
    }
    if (prev === 'html' && mode !== 'html') {
        document.getElementById('tpl-wysiwyg-editor').innerHTML = document.getElementById('tpl-edit-body').value;
    }

    // Toggle visibility
    document.getElementById('tpl-toolbar-wrap').classList.toggle('hidden', mode !== 'wysiwyg');
    document.getElementById('tpl-wysiwyg-editor').classList.toggle('hidden', mode !== 'wysiwyg');
    document.getElementById('tpl-html-wrap').classList.toggle('hidden', mode !== 'html');
    document.getElementById('tpl-preview-wrap').classList.toggle('hidden', mode !== 'preview');

    if (mode === 'preview') {
        var html = prev === 'wysiwyg'
            ? document.getElementById('tpl-wysiwyg-editor').innerHTML
            : document.getElementById('tpl-edit-body').value;
        document.getElementById('tpl-preview-wrap').innerHTML = html;
    }

    // Update pill toggle styles
    ['wysiwyg', 'html', 'preview'].forEach(function(m) {
        var btn = document.getElementById('tpl-btn-mode-' + m);
        if (m === mode) {
            btn.classList.add('bg-white', 'text-brand', 'shadow-sm', 'font-medium');
            btn.classList.remove('text-gray-600', 'hover:text-gray-900');
        } else {
            btn.classList.remove('bg-white', 'text-brand', 'shadow-sm', 'font-medium');
            btn.classList.add('text-gray-600', 'hover:text-gray-900');
        }
    });
}

function tplExecCmd(command, value) {
    document.getElementById('tpl-wysiwyg-editor').focus();
    document.execCommand(command, false, value || null);
}

function tplInsertLink() {
    var url = prompt('URL을 입력하세요:', 'https://');
    if (url && url !== 'https://') tplExecCmd('createLink', url);
}

function tplInsertImage() {
    document.getElementById('tpl-image-upload-input').click();
}

function tplUploadImage(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('5MB 이하 이미지만 가능합니다', 'warning'); input.value = ''; return; }

    var formData = new FormData();
    formData.append('action', 'upload_image');
    formData.append('image', file);

    showToast('이미지 업로드 중...', 'info');

    fetch(API, {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); input.value = ''; return; }
        var imgTag = '<img src="' + res.data.url + '" alt="uploaded image" style="max-width:100%;height:auto;">';
        if (tplEditorMode === 'wysiwyg') {
            document.getElementById('tpl-wysiwyg-editor').focus();
            document.execCommand('insertHTML', false, imgTag);
        } else {
            var ta = document.getElementById('tpl-edit-body');
            var pos = ta.selectionStart || ta.value.length;
            ta.value = ta.value.substring(0, pos) + imgTag + ta.value.substring(pos);
        }
        showToast('이미지가 삽입되었습니다', 'success');
        input.value = '';
    }).catch(function() { showToast('업로드 실패', 'error'); input.value = ''; });
}

function tplChangeColor() {
    var color = document.getElementById('tpl-text-color-picker').value;
    tplExecCmd('foreColor', color);
}

// ==================== UTILS ====================
function escHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ==================== CHECKLIST ====================
function updateChecklist() {
    var recipientText = document.getElementById('summary-recipients').textContent;
    var recipientOk = parseInt(recipientText) > 0;
    var subjectOk = document.getElementById('email-subject').value.trim().length > 0;
    var bodyOk = currentEditorMode === 'wysiwyg'
        ? document.getElementById('wysiwyg-editor').textContent.trim().length > 0
        : document.getElementById('email-body').value.trim().length > 0;

    setCheckState('check-recipient-dot', recipientOk);
    setCheckState('check-subject-dot', subjectOk);
    setCheckState('check-body-dot', bodyOk);

    var allReady = recipientOk && subjectOk && bodyOk;
    var sendBtn = document.getElementById('btn-send');
    var mobileSendBtn = document.getElementById('mobile-btn-send');
    if (sendBtn) sendBtn.disabled = !allReady;
    if (mobileSendBtn) mobileSendBtn.disabled = !allReady;
}

function setCheckState(dotId, ok) {
    var dot = document.getElementById(dotId);
    if (!dot) return;
    dot.classList.toggle('bg-green-500', ok);
    dot.classList.toggle('bg-gray-300', !ok);
    var label = dot.nextElementSibling;
    if (label) {
        label.classList.toggle('text-gray-900', ok);
        label.classList.toggle('text-gray-500', !ok);
    }
}

function toggleSmtpInfo() {
    var detail = document.getElementById('smtp-detail');
    var chevron = document.getElementById('smtp-chevron');
    detail.classList.toggle('hidden');
    chevron.classList.toggle('rotate-180');
}

function stripHtml(html) {
    var tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}

// ==================== INIT ====================
updateRecipientCount();
loadTemplateOptions();
updateChecklist();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
