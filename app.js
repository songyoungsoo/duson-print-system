// ===== Language Data =====
const languages = [
    { code: 'ko', name: '한국어' },
    { code: 'en', name: 'English' },
    { code: 'ja', name: '日本語' },
    { code: 'zh-CN', name: '中文 (简体)' },
    { code: 'zh-TW', name: '中文 (繁體)' },
    { code: 'es', name: 'Español' },
    { code: 'fr', name: 'Français' },
    { code: 'de', name: 'Deutsch' },
    { code: 'it', name: 'Italiano' },
    { code: 'pt', name: 'Português' },
    { code: 'ru', name: 'Русский' },
    { code: 'ar', name: 'العربية' },
    { code: 'hi', name: 'हिन्दी' },
    { code: 'th', name: 'ไทย' },
    { code: 'vi', name: 'Tiếng Việt' },
    { code: 'id', name: 'Bahasa Indonesia' },
    { code: 'ms', name: 'Bahasa Melayu' },
    { code: 'tl', name: 'Filipino' },
    { code: 'nl', name: 'Nederlands' },
    { code: 'pl', name: 'Polski' },
    { code: 'tr', name: 'Türkçe' },
    { code: 'uk', name: 'Українська' },
    { code: 'cs', name: 'Čeština' },
    { code: 'sv', name: 'Svenska' },
    { code: 'da', name: 'Dansk' },
    { code: 'fi', name: 'Suomi' },
    { code: 'no', name: 'Norsk' },
    { code: 'el', name: 'Ελληνικά' },
    { code: 'he', name: 'עברית' },
    { code: 'hu', name: 'Magyar' },
    { code: 'ro', name: 'Română' },
    { code: 'sk', name: 'Slovenčina' },
    { code: 'bg', name: 'Български' },
    { code: 'hr', name: 'Hrvatski' },
    { code: 'sr', name: 'Српски' },
    { code: 'sl', name: 'Slovenščina' },
    { code: 'et', name: 'Eesti' },
    { code: 'lv', name: 'Latviešu' },
    { code: 'lt', name: 'Lietuvių' },
    { code: 'bn', name: 'বাংলা' },
    { code: 'ta', name: 'தமிழ்' },
    { code: 'te', name: 'తెలుగు' },
    { code: 'ml', name: 'മലയാളം' },
    { code: 'kn', name: 'ಕನ್ನಡ' },
    { code: 'mr', name: 'मराठी' },
    { code: 'gu', name: 'ગુજરાતી' },
    { code: 'pa', name: 'ਪੰਜਾਬੀ' },
    { code: 'ur', name: 'اردو' },
    { code: 'fa', name: 'فارسی' },
    { code: 'sw', name: 'Kiswahili' },
    { code: 'af', name: 'Afrikaans' },
    { code: 'ca', name: 'Català' },
    { code: 'gl', name: 'Galego' },
    { code: 'eu', name: 'Euskara' },
    { code: 'cy', name: 'Cymraeg' },
    { code: 'ga', name: 'Gaeilge' },
    { code: 'is', name: 'Íslenska' },
    { code: 'mt', name: 'Malti' },
    { code: 'sq', name: 'Shqip' },
    { code: 'mk', name: 'Македонски' },
    { code: 'bs', name: 'Bosanski' },
    { code: 'lb', name: 'Lëtzebuergesch' },
    { code: 'ka', name: 'ქართული' },
    { code: 'hy', name: 'Հայերdelays' },
    { code: 'az', name: 'Azərbaycan' },
    { code: 'kk', name: 'Қазақша' },
    { code: 'uz', name: 'Oʻzbekcha' },
    { code: 'mn', name: 'Монгол' },
    { code: 'ne', name: 'नेपाली' },
    { code: 'si', name: 'සිංහල' },
    { code: 'km', name: 'ភាសាខ្មែរ' },
    { code: 'lo', name: 'ລາວ' },
    { code: 'my', name: 'မြန်မာ' }
];

// ===== DOM Elements =====
const sourceLang = document.getElementById('source-lang');
const targetLang = document.getElementById('target-lang');
const sourceText = document.getElementById('source-text');
const targetText = document.getElementById('target-text');
const translateBtn = document.getElementById('translate-btn');
const swapBtn = document.getElementById('swap-btn');
const clearBtn = document.getElementById('clear-btn');
const copyBtn = document.getElementById('copy-btn');
const charCount = document.getElementById('char-count');
const detectedLang = document.getElementById('detected-lang');
const toast = document.getElementById('toast');

// ===== State =====
let isTranslating = false;
let lastTranslation = '';

// ===== Initialize =====
function init() {
    populateLanguageDropdowns();
    setupEventListeners();
    loadSavedPreferences();
}

function populateLanguageDropdowns() {
    // Source language dropdown (with auto-detect)
    languages.forEach(lang => {
        const option = document.createElement('option');
        option.value = lang.code;
        option.textContent = lang.name;
        sourceLang.appendChild(option);
    });

    // Target language dropdown (without auto-detect)
    languages.forEach(lang => {
        const option = document.createElement('option');
        option.value = lang.code;
        option.textContent = lang.name;
        targetLang.appendChild(option);
    });

    // Set default: Korean -> English
    sourceLang.value = 'auto';
    targetLang.value = 'en';
}

function setupEventListeners() {
    // Translate button
    translateBtn.addEventListener('click', handleTranslate);

    // Swap languages
    swapBtn.addEventListener('click', handleSwap);

    // Clear text
    clearBtn.addEventListener('click', handleClear);

    // Copy translation
    copyBtn.addEventListener('click', handleCopy);

    // Character count
    sourceText.addEventListener('input', updateCharCount);

    // Enter key to translate (Ctrl + Enter)
    sourceText.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'Enter') {
            handleTranslate();
        }
    });

    // Save preferences on change
    sourceLang.addEventListener('change', savePreferences);
    targetLang.addEventListener('change', savePreferences);
}

// ===== Translation =====
async function handleTranslate() {
    const text = sourceText.value.trim();

    if (!text) {
        showToast('번역할 텍스트를 입력해주세요', 'error');
        return;
    }

    if (isTranslating) return;

    isTranslating = true;
    translateBtn.classList.add('loading');
    targetText.innerHTML = '<span class="placeholder-text">번역 중...</span>';
    detectedLang.classList.remove('visible');

    try {
        const result = await translateText(text, sourceLang.value, targetLang.value);

        if (result.success) {
            lastTranslation = result.translatedText;
            targetText.textContent = result.translatedText;
            copyBtn.disabled = false;

            // Show detected language if auto-detect was used
            if (sourceLang.value === 'auto' && result.detectedLanguage) {
                const langName = getLanguageName(result.detectedLanguage);
                detectedLang.textContent = `감지됨: ${langName}`;
                detectedLang.classList.add('visible');
            }
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Translation error:', error);
        targetText.innerHTML = '<span class="placeholder-text">번역에 실패했습니다. 다시 시도해주세요.</span>';
        showToast('번역 중 오류가 발생했습니다', 'error');
    } finally {
        isTranslating = false;
        translateBtn.classList.remove('loading');
    }
}

async function translateText(text, sourceLangCode, targetLangCode) {
    // Use MyMemory Translation API
    // 자동 감지일 경우 한국어(ko)를 기본값으로 사용
    const source = sourceLangCode === 'auto' ? 'ko' : sourceLangCode;
    const langPair = `${source}|${targetLangCode}`;

    const url = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=${encodeURIComponent(langPair)}`;

    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const data = await response.json();

        console.log('API Response:', data); // 디버깅용

        if (data.responseStatus === 200 || data.responseStatus === '200') {
            let translatedText = data.responseData.translatedText;

            // MyMemory가 번역 실패 시 원문을 그대로 반환하는 경우 체크
            if (translatedText && translatedText.toUpperCase() !== text.toUpperCase()) {
                return {
                    success: true,
                    translatedText: translatedText,
                    detectedLanguage: source
                };
            } else {
                // 대체 번역 시도: 영어로 먼저 감지 시도
                if (sourceLangCode === 'auto' && source === 'ko') {
                    return await translateText(text, 'en', targetLangCode);
                }
                return {
                    success: true,
                    translatedText: translatedText || text,
                    detectedLanguage: source
                };
            }
        } else {
            return {
                success: false,
                error: data.responseDetails || `API Error: ${data.responseStatus}`
            };
        }
    } catch (error) {
        console.error('Fetch error:', error);
        return {
            success: false,
            error: error.message || 'Network error'
        };
    }
}

// ===== UI Handlers =====
function handleSwap() {
    // Can't swap if source is auto-detect
    if (sourceLang.value === 'auto') {
        showToast('자동 감지 모드에서는 언어를 교환할 수 없습니다', 'error');
        return;
    }

    // Swap language selections
    const tempLang = sourceLang.value;
    sourceLang.value = targetLang.value;
    targetLang.value = tempLang;

    // Swap text content
    const tempText = sourceText.value;
    sourceText.value = lastTranslation || '';

    if (tempText) {
        targetText.textContent = tempText;
        lastTranslation = tempText;
        copyBtn.disabled = false;
    } else {
        targetText.innerHTML = '<span class="placeholder-text">번역 결과가 여기에 표시됩니다</span>';
        copyBtn.disabled = true;
    }

    updateCharCount();
    savePreferences();

    // Visual feedback
    swapBtn.style.transform = 'scale(0.9)';
    setTimeout(() => {
        swapBtn.style.transform = '';
    }, 150);
}

function handleClear() {
    sourceText.value = '';
    targetText.innerHTML = '<span class="placeholder-text">번역 결과가 여기에 표시됩니다</span>';
    lastTranslation = '';
    copyBtn.disabled = true;
    detectedLang.classList.remove('visible');
    updateCharCount();
    sourceText.focus();
}

async function handleCopy() {
    if (!lastTranslation) return;

    try {
        await navigator.clipboard.writeText(lastTranslation);

        // Visual feedback
        copyBtn.classList.add('copied');
        setTimeout(() => {
            copyBtn.classList.remove('copied');
        }, 1500);

        showToast('클립보드에 복사되었습니다', 'success');
    } catch (error) {
        console.error('Copy failed:', error);
        showToast('복사에 실패했습니다', 'error');
    }
}

function updateCharCount() {
    const count = sourceText.value.length;
    const maxLength = 5000;
    charCount.textContent = `${count.toLocaleString()} / ${maxLength.toLocaleString()}`;

    if (count >= maxLength * 0.9) {
        charCount.style.color = '#ef4444';
    } else if (count >= maxLength * 0.7) {
        charCount.style.color = '#f59e0b';
    } else {
        charCount.style.color = '';
    }
}

// ===== Utilities =====
function getLanguageName(code) {
    const lang = languages.find(l => l.code.toLowerCase() === code.toLowerCase());
    return lang ? lang.name : code;
}

function showToast(message, type = '') {
    toast.textContent = message;
    toast.className = 'toast show';
    if (type) {
        toast.classList.add(type);
    }

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// ===== Local Storage =====
function savePreferences() {
    const prefs = {
        sourceLang: sourceLang.value,
        targetLang: targetLang.value
    };
    localStorage.setItem('translatorPrefs', JSON.stringify(prefs));
}

function loadSavedPreferences() {
    try {
        const saved = localStorage.getItem('translatorPrefs');
        if (saved) {
            const prefs = JSON.parse(saved);
            if (prefs.sourceLang) sourceLang.value = prefs.sourceLang;
            if (prefs.targetLang) targetLang.value = prefs.targetLang;
        }
    } catch (error) {
        console.error('Failed to load preferences:', error);
    }
}

// ===== Start Application =====
document.addEventListener('DOMContentLoaded', init);
