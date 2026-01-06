<?php
/**
 * 제품별 동적 폼 생성 API
 *
 * GET /api/get_form.php?product=sticker
 *
 * 제품 코드에 맞는 폼 HTML을 생성하여 반환합니다.
 *
 * @author Claude Code
 * @version 2.0
 * @date 2026-01-06
 */

session_start();
require_once __DIR__ . '/../includes/CalculatorConfig.php';
require_once __DIR__ . '/../../../db.php';

header('Content-Type: text/html; charset=utf-8');

// GET 파라미터 검증
$productType = $_GET['product'] ?? '';

if (empty($productType)) {
    echo '<div class="error-state"><p>제품을 선택하세요.</p></div>';
    exit;
}

if (!CalculatorConfig::isValidProduct($productType)) {
    echo '<div class="error-state"><p>유효하지 않은 제품입니다.</p></div>';
    exit;
}

// 제품별 폼 HTML 생성
$productName = CalculatorConfig::getProductName($productType);
?>

<form id="productForm" data-product="<?= htmlspecialchars($productType) ?>">
    <h3 style="margin-bottom: 20px; color: #222;"><?= htmlspecialchars($productName) ?> 계산기</h3>

    <?php
    switch ($productType) {
        case 'sticker':
        case 'msticker':
            renderStickerForm($productType, $db);
            break;

        case 'namecard':
            renderNamecardForm($db);
            break;

        case 'inserted':
            renderInsertedForm($db);
            break;

        case 'envelope':
            renderEnvelopeForm($db);
            break;

        default:
            renderGenericForm($productType, $db);
            break;
    }
    ?>
</form>

<?php
/**
 * 스티커 폼
 */
function renderStickerForm($productType, $db) {
    $isMagnet = ($productType === 'msticker');
    ?>
    <div class="form-group">
        <label class="form-label">종류</label>
        <select name="jong" class="form-select" required>
            <option value="">선택하세요</option>
            <option value="jil유포지">유포지</option>
            <option value="jil아트지">아트지</option>
            <option value="jil크라프트지">크라프트지</option>
            <option value="jil투명">투명</option>
            <?php if ($isMagnet): ?>
            <option value="jil자석">자석</option>
            <?php else: ?>
            <option value="jil은무지">은무지</option>
            <option value="jil금무지">금무지</option>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">도무송</label>
        <select name="domusong" class="form-select" required>
            <option value="">선택하세요</option>
            <option value="0사각">사각</option>
            <option value="1원형">원형</option>
            <option value="2타원형">타원형</option>
            <option value="3귀도라지">귀도라지</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">가로 (mm)</label>
        <input type="number" name="garo" class="form-input"
               min="10" max="1000" step="1" placeholder="예: 90" required>
    </div>

    <div class="form-group">
        <label class="form-label">세로 (mm)</label>
        <input type="number" name="sero" class="form-input"
               min="10" max="1000" step="1" placeholder="예: 50" required>
    </div>

    <div class="form-group">
        <label class="form-label">수량 (매)</label>
        <select name="mesu" class="form-select" required>
            <option value="">선택하세요</option>
            <option value="100">100</option>
            <option value="200">200</option>
            <option value="300">300</option>
            <option value="500">500</option>
            <option value="700">700</option>
            <option value="1000" selected>1000</option>
            <option value="1500">1500</option>
            <option value="2000">2000</option>
            <option value="3000">3000</option>
            <option value="5000">5000</option>
            <option value="10000">10000</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">유형</label>
        <select name="uhyung" class="form-select" required>
            <option value="0">인쇄만</option>
            <option value="1">디자인+인쇄</option>
        </select>
    </div>
    <?php
}

/**
 * 명함 폼
 */
function renderNamecardForm($db) {
    // DB에서 옵션 조회
    $query = "SELECT DISTINCT MY_type FROM mlangprintauto_namecard ORDER BY MY_type";
    $styles = mysqli_query($db, $query);

    $query = "SELECT DISTINCT Section FROM mlangprintauto_namecard ORDER BY Section";
    $sections = mysqli_query($db, $query);

    $query = "SELECT DISTINCT POtype FROM mlangprintauto_namecard ORDER BY POtype";
    $potypes = mysqli_query($db, $query);
    ?>
    <div class="form-group">
        <label class="form-label">스타일</label>
        <select name="MY_type" class="form-select" required>
            <option value="">선택하세요</option>
            <?php while ($row = mysqli_fetch_assoc($styles)): ?>
                <option value="<?= htmlspecialchars($row['MY_type']) ?>">
                    <?= htmlspecialchars($row['MY_type']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">용지</label>
        <select name="Section" class="form-select" required>
            <option value="">스타일을 먼저 선택하세요</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">인쇄 색상</label>
        <select name="POtype" class="form-select" required>
            <option value="">용지를 먼저 선택하세요</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">수량 (매)</label>
        <select name="MY_amount" class="form-select" required>
            <option value="">인쇄 색상을 먼저 선택하세요</option>
        </select>
    </div>
    <?php
}

/**
 * 전단지 폼
 */
function renderInsertedForm($db) {
    $query = "SELECT DISTINCT MY_type FROM mlangprintauto_inserted ORDER BY MY_type";
    $styles = mysqli_query($db, $query);
    ?>
    <div class="form-group">
        <label class="form-label">스타일</label>
        <select name="MY_type" class="form-select" required>
            <option value="">선택하세요</option>
            <?php while ($row = mysqli_fetch_assoc($styles)): ?>
                <option value="<?= htmlspecialchars($row['MY_type']) ?>">
                    <?= htmlspecialchars($row['MY_type']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">규격</label>
        <select name="PN_type" class="form-select" required>
            <option value="">스타일을 먼저 선택하세요</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">용지</label>
        <select name="MY_Fsd" class="form-select" required>
            <option value="">규격을 먼저 선택하세요</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">수량 (연)</label>
        <select name="MY_amount" class="form-select" required>
            <option value="">용지를 먼저 선택하세요</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">인쇄 색상</label>
        <select name="POtype" class="form-select" required>
            <option value="">수량을 먼저 선택하세요</option>
        </select>
    </div>
    <?php
}

/**
 * 봉투 폼
 */
function renderEnvelopeForm($db) {
    $query = "SELECT DISTINCT MY_type FROM mlangprintauto_envelope ORDER BY MY_type";
    $styles = mysqli_query($db, $query);
    ?>
    <div class="form-group">
        <label class="form-label">종류</label>
        <select name="MY_type" class="form-select" required>
            <option value="">선택하세요</option>
            <?php while ($row = mysqli_fetch_assoc($styles)): ?>
                <option value="<?= htmlspecialchars($row['MY_type']) ?>">
                    <?= htmlspecialchars($row['MY_type']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">용지</label>
        <select name="Section" class="form-select" required>
            <option value="">종류를 먼저 선택하세요</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">인쇄 색상</label>
        <select name="POtype" class="form-select" required>
            <option value="">용지를 먼저 선택하세요</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">수량 (매)</label>
        <select name="MY_amount" class="form-select" required>
            <option value="">인쇄 색상을 먼저 선택하세요</option>
        </select>
    </div>
    <?php
}

/**
 * 일반 제품 폼 (기타)
 */
function renderGenericForm($productType, $db) {
    $tableName = CalculatorConfig::getDBTable($productType);
    if (!$tableName) {
        echo '<div class="error-state"><p>이 제품은 아직 지원하지 않습니다.</p></div>';
        return;
    }

    $query = "SELECT DISTINCT MY_type FROM $tableName ORDER BY MY_type LIMIT 50";
    $result = mysqli_query($db, $query);

    if (!$result) {
        echo '<div class="error-state"><p>옵션을 불러올 수 없습니다.</p></div>';
        return;
    }
    ?>
    <div class="form-group">
        <label class="form-label">종류</label>
        <select name="MY_type" class="form-select" required>
            <option value="">선택하세요</option>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <option value="<?= htmlspecialchars($row['MY_type']) ?>">
                    <?= htmlspecialchars($row['MY_type']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">수량</label>
        <select name="MY_amount" class="form-select" required>
            <option value="">종류를 먼저 선택하세요</option>
        </select>
    </div>

    <p style="color: #999; font-size: 13px; margin-top: 20px;">
        ℹ️ 이 제품은 기본 폼을 사용합니다. 추가 옵션은 선택 시 표시됩니다.
    </p>
    <?php
}

mysqli_close($db);
?>
