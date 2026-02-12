/**
 * 프리미엄 옵션 DB 로더
 * DB에서 옵션 데이터를 fetch하여 JS 클래스의 basePrices를 덮어씀
 * 실패 시 기존 하드코딩 값 그대로 사용 (fallback 보장)
 *
 * Usage:
 *   const dbPrices = await loadPremiumOptionsFromDB('namecard');
 *   if (dbPrices) manager.applyDBPrices(dbPrices);
 */
async function loadPremiumOptionsFromDB(productType) {
    try {
        const res = await fetch('/api/premium_options.php?product_type=' + encodeURIComponent(productType));
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (!data.success || !data.options) throw new Error('Invalid response');
        return data.options;
    } catch (e) {
        console.warn('[PremiumOptions] DB 로드 실패, 하드코딩 fallback 사용:', e.message);
        return null;
    }
}
