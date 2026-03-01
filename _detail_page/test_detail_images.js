const { chromium } = require('playwright');

async function testDetailImages() {
  const products = [
    { name: 'namecard', url: 'https://dsp114.com/mlangprintauto/namecard/index.php', expected: 13 },
    { name: 'sticker_new', url: 'https://dsp114.com/mlangprintauto/sticker_new/index.php', expected: 13 },
    { name: 'inserted', url: 'https://dsp114.com/mlangprintauto/inserted/index.php', expected: 13 },
    { name: 'envelope', url: 'https://dsp114.com/mlangprintauto/envelope/index.php', expected: 13 },
    { name: 'littleprint', url: 'https://dsp114.com/mlangprintauto/littleprint/index.php', expected: 12 },
    { name: 'merchandisebond', url: 'https://dsp114.com/mlangprintauto/merchandisebond/index.php', expected: 13 },
    { name: 'cadarok', url: 'https://dsp114.com/mlangprintauto/cadarok/index.php', expected: 13 },
    { name: 'ncrflambeau', url: 'https://dsp114.com/mlangprintauto/ncrflambeau/index.php', expected: 13 },
    { name: 'msticker', url: 'https://dsp114.com/mlangprintauto/msticker/index.php', expected: 13 }
  ];

  const browser = await chromium.launch();
  const page = await browser.newPage();
  const results = [];

  for (const product of products) {
    try {
      console.log(`Testing ${product.name}...`);
      await page.goto(product.url, { waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);

      const loadedCount = await page.evaluate(() => {
        const images = document.querySelectorAll('img[src*="/ImgFolder/detail_page/"]');
        let count = 0;
        images.forEach(img => {
          if (img.naturalWidth > 0) {
            count++;
          }
        });
        return count;
      });

      results.push({
        product: product.name,
        expected: product.expected,
        loaded: loadedCount,
        status: loadedCount === product.expected ? 'PASS' : 'FAIL'
      });
      console.log(`  ${loadedCount}/${product.expected} images loaded`);
    } catch (error) {
      results.push({
        product: product.name,
        expected: product.expected,
        loaded: 0,
        status: `ERROR: ${error.message}`
      });
      console.log(`  ERROR: ${error.message}`);
    }
  }

  // Take screenshot of sticker_new page
  try {
    console.log('\nTaking screenshot of sticker_new page...');
    await page.goto('https://dsp114.com/mlangprintauto/sticker_new/index.php', { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'sticker_detail_images.png', fullPage: true });
    console.log('Screenshot saved to sticker_detail_images.png');
  } catch (error) {
    console.log(`Screenshot error: ${error.message}`);
  }

  await browser.close();

  // Print results table
  console.log('\n=== DETAIL PAGE IMAGE LOADING REPORT ===\n');
  console.log('| Product | Expected | Loaded | Status |');
  console.log('|---------|----------|--------|--------|');
  results.forEach(r => {
    console.log(`| ${r.product.padEnd(20)} | ${String(r.expected).padEnd(8)} | ${String(r.loaded).padEnd(6)} | ${r.status} |`);
  });

  const passCount = results.filter(r => r.status === 'PASS').length;
  console.log(`\nSummary: ${passCount}/${results.length} products passed`);

  return results;
}

testDetailImages().catch(console.error);
