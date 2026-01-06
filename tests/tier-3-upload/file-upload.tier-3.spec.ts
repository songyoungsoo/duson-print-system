import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

/**
 * Tier 3 - File Upload Tests (Limited Parallelization)
 * 
 * spec: specs/dsp1830-test-plan.md
 * 
 * 특징:
 * - 파일 업로드 기능 테스트
 * - 각 테스트는 독립적인 브라우저 컨텍스트 사용 (세션 격리)
 * - 디스크 I/O 고려하여 최대 4개 워커로 제한
 * - DB 쓰기 없음 (세션 저장소만 사용)
 */

// 테스트 파일 생성 헬퍼
function createTestFile(filename: string, sizeInMB: number): string {
  const testFilesDir = path.join(__dirname, '..', '..', 'test-files');
  
  // 디렉토리 생성 (존재하지 않으면)
  if (!fs.existsSync(testFilesDir)) {
    fs.mkdirSync(testFilesDir, { recursive: true });
  }
  
  const filepath = path.join(testFilesDir, filename);
  
  // 파일이 이미 존재하면 재사용
  if (fs.existsSync(filepath)) {
    return filepath;
  }
  
  // 지정된 크기의 파일 생성 (더미 데이터)
  const sizeInBytes = sizeInMB * 1024 * 1024;
  const buffer = Buffer.alloc(sizeInBytes, 'A');
  fs.writeFileSync(filepath, buffer);
  
  return filepath;
}

// 병렬 실행 제한 설정 (디스크 I/O 고려)
test.describe.configure({ mode: 'parallel', workers: 4 });

test.describe('Tier 3 - File Upload Tests', () => {
  
  test('전단지 단일 파일 업로드', async ({ page }) => {
    // 1. Navigate to URL
    await page.goto('/mlangprintauto/inserted/');
    await page.waitForLoadState('domcontentloaded');

    // 테스트 파일 생성 (1MB PDF)
    const testFile = createTestFile('test_flyer_tier3_001.pdf', 1);

    // 2. Click "파일 업로드 및 주문하기" button to open upload modal
    const uploadButton = page.locator('button:has-text("파일 업로드 및 주문하기")');
    await expect(uploadButton).toBeVisible({ timeout: 10000 });
    await uploadButton.click();

    // 3. Wait for modal to appear
    await page.waitForSelector('.upload-modal, #uploadModal', { timeout: 5000 });

    // 4. Locate file upload input inside the modal
    const fileInput = page.locator('input[type="file"]').first();
    await expect(fileInput).toBeAttached({ timeout: 10000 });

    // 5. Wait for .file-list container to exist in modal
    const fileListContainer = page.locator('.file-list');
    await fileListContainer.waitFor({ state: 'attached', timeout: 10000 }).catch(() => {
      console.log('Warning: .file-list container not found, upload UI may not be initialized');
    });

    // 6. Upload test file
    await fileInput.setInputFiles(testFile);

    // 7. Wait for file item to appear (uploaded file creates .file-item element)
    // The UniversalFileUpload.js creates: <div class="file-item"> with <span class="file-name">
    await page.waitForSelector('.file-item', { timeout: 10000 });

    // 8. Wait for upload completion - look for success status "✅ 완료"
    const successStatus = page.locator('.file-status:has-text("완료")');
    await expect(successStatus.first()).toBeVisible({ timeout: 15000 });

    // 9. Verify filename displayed in file list
    const fileName = page.locator('.file-name');
    await expect(fileName.first()).toBeVisible();

    // 10. Verify file items count
    const fileItems = page.locator('.file-item');
    const fileItemCount = await fileItems.count();
    expect(fileItemCount).toBeGreaterThan(0);

    // 11. Verify file size < 15MB limit
    const fileStats = fs.statSync(testFile);
    const fileSizeInMB = fileStats.size / (1024 * 1024);
    expect(fileSizeInMB).toBeLessThan(15);
  });

  test('명함 다중 파일 업로드', async ({ page }) => {
    // 1. Navigate to URL
    await page.goto('/mlangprintauto/namecard/');
    await page.waitForLoadState('domcontentloaded');

    // 테스트 파일 2개 생성
    const testFile1 = createTestFile('test_namecard_tier3_001.jpg', 0.5);
    const testFile2 = createTestFile('test_namecard_tier3_002.jpg', 0.5);

    // 2. Click "파일 업로드 및 주문하기" button to open upload modal
    const uploadButton = page.locator('button:has-text("파일 업로드 및 주문하기")');
    await expect(uploadButton).toBeVisible({ timeout: 10000 });
    await uploadButton.click();

    // 3. Wait for modal to appear
    await page.waitForSelector('.upload-modal, #uploadModal', { timeout: 5000 });

    // 4. Wait for upload UI to be ready
    const fileInput = page.locator('input[type="file"]').first();
    await expect(fileInput).toBeAttached({ timeout: 10000 });

    const fileListContainer = page.locator('.file-list');
    await fileListContainer.waitFor({ state: 'attached', timeout: 10000 }).catch(() => {
      console.log('Warning: .file-list container not found');
    });

    // 5. Upload multiple files
    await fileInput.setInputFiles([testFile1, testFile2]);

    // 6. Wait for both file items to appear
    await page.waitForSelector('.file-item', { timeout: 10000 });

    // 7. Wait for file count to reach 2 (both files uploaded)
    await page.waitForFunction(
      () => document.querySelectorAll('.file-item').length >= 2,
      { timeout: 15000 }
    );

    // 8. Wait for success status on uploaded files
    const successItems = page.locator('.file-status:has-text("완료")');
    await expect(successItems.first()).toBeVisible({ timeout: 20000 });

    // 9. Verify both file items exist
    const fileItems = page.locator('.file-item');
    const fileItemCount = await fileItems.count();
    expect(fileItemCount).toBeGreaterThanOrEqual(2);

    // 10. Verify success count
    const successCount = await successItems.count();
    expect(successCount).toBeGreaterThan(0);

    // 11. Verify file names are visible (may be truncated)
    const fileNames = page.locator('.file-name');
    expect(await fileNames.count()).toBeGreaterThanOrEqual(2);
  });

  test('봉투 대용량 파일 업로드 (10MB 제한 테스트)', async ({ page }) => {
    // Setup console listener for error messages
    const errorMessages: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errorMessages.push(msg.text());
      }
    });

    // 1. Upload valid file (5MB - within default 10MB limit)
    await page.goto('/mlangprintauto/envelope/');
    await page.waitForLoadState('domcontentloaded');

    // 2. Click "파일 업로드 및 주문하기" button to open upload modal
    const uploadButton = page.locator('button:has-text("파일 업로드 및 주문하기")');
    await expect(uploadButton).toBeVisible({ timeout: 10000 });
    await uploadButton.click();

    // 3. Wait for modal to appear
    await page.waitForSelector('.upload-modal, #uploadModal', { timeout: 5000 });

    const validFile = createTestFile('test_envelope_tier3_valid.ai', 5);
    const fileInput = page.locator('input[type="file"]').first();
    await expect(fileInput).toBeAttached({ timeout: 10000 });

    const fileListContainer = page.locator('.file-list');
    await fileListContainer.waitFor({ state: 'attached', timeout: 10000 }).catch(() => {
      console.log('Warning: .file-list container not found');
    });

    await fileInput.setInputFiles(validFile);

    // 4. Wait for valid file to be uploaded successfully
    await page.waitForSelector('.file-item', { timeout: 10000 });
    const successStatus = page.locator('.file-status:has-text("완료")');
    await expect(successStatus.first()).toBeVisible({ timeout: 15000 });

    // 5. Verify valid file uploaded successfully
    const fileItems = page.locator('.file-item');
    const fileItemCount = await fileItems.count();
    expect(fileItemCount).toBeGreaterThan(0);

    // 페이지 새로고침하여 다음 테스트 준비
    await page.reload();
    await page.waitForLoadState('domcontentloaded');

    // 6. Open modal again for oversized file test
    const uploadButton2 = page.locator('button:has-text("파일 업로드 및 주문하기")');
    await expect(uploadButton2).toBeVisible({ timeout: 10000 });
    await uploadButton2.click();
    await page.waitForSelector('.upload-modal, #uploadModal', { timeout: 5000 });

    // 7. Attempt oversized file (15MB - exceeds default 10MB limit) - should be rejected by client-side validation
    const oversizedFile = createTestFile('test_envelope_tier3_oversized.ai', 15);
    const fileInput2 = page.locator('input[type="file"]').first();
    await expect(fileInput2).toBeAttached({ timeout: 10000 });

    await fileInput2.setInputFiles(oversizedFile);

    // 8. Wait a moment for potential validation message
    await page.waitForTimeout(2000);

    // 9. Verify oversized file was rejected - should NOT appear in file list
    const oversizedFileItems = page.locator('.file-item');
    const oversizedFileItemCount = await oversizedFileItems.count();

    // 10. Confirm 10MB limit enforced (default FileUploadComponent limit)
    const oversizedFileStats = fs.statSync(oversizedFile);
    const oversizedSizeInMB = oversizedFileStats.size / (1024 * 1024);
    expect(oversizedSizeInMB).toBeGreaterThan(10);

    // Assertion: Oversized file should be rejected (not added to file list)
    // The JavaScript validation prevents files > max_file_size (10MB default) from being added
    expect(oversizedFileItemCount).toBe(0);
  });

  test('파일 형식 검증 테스트', async ({ page }) => {
    // Setup console message listener to check for validation errors
    const consoleMessages: string[] = [];
    page.on('console', msg => {
      consoleMessages.push(msg.text());
    });

    // 1. Test valid formats: .pdf, .jpg, .png
    await page.goto('/mlangprintauto/inserted/');
    await page.waitForLoadState('domcontentloaded');

    const validFormats = [
      { ext: 'pdf', size: 0.5 },
      { ext: 'jpg', size: 0.5 },
      { ext: 'png', size: 0.5 }
    ];

    // 2. Verify each format uploads successfully
    for (const format of validFormats) {
      // Click upload button to open modal
      const uploadButton = page.locator('button:has-text("파일 업로드 및 주문하기")');
      await expect(uploadButton).toBeVisible({ timeout: 10000 });
      await uploadButton.click();
      await page.waitForSelector('.upload-modal, #uploadModal', { timeout: 5000 });

      const testFile = createTestFile(`test_format_tier3.${format.ext}`, format.size);
      const fileInput = page.locator('input[type="file"]').first();
      await expect(fileInput).toBeAttached({ timeout: 10000 });

      const fileListContainer = page.locator('.file-list');
      await fileListContainer.waitFor({ state: 'attached', timeout: 10000 }).catch(() => {
        console.log('Warning: .file-list container not found');
      });

      await fileInput.setInputFiles(testFile);

      // Wait for file item to appear
      await page.waitForSelector('.file-item', { timeout: 10000 });

      // Wait for success status
      const successStatus = page.locator('.file-status:has-text("완료")');
      await expect(successStatus.first()).toBeVisible({ timeout: 15000 });

      // Verify file was uploaded
      const fileItems = page.locator('.file-item');
      const fileItemCount = await fileItems.count();
      expect(fileItemCount).toBeGreaterThan(0);

      // 페이지 새로고침하여 다음 형식 테스트
      if (format !== validFormats[validFormats.length - 1]) {
        await page.reload();
        await page.waitForLoadState('domcontentloaded');
      }
    }

    // 페이지 새로고침
    await page.reload();
    await page.waitForLoadState('domcontentloaded');

    // 3. Open modal for invalid file test
    const uploadButton2 = page.locator('button:has-text("파일 업로드 및 주문하기")');
    await expect(uploadButton2).toBeVisible({ timeout: 10000 });
    await uploadButton2.click();
    await page.waitForSelector('.upload-modal, #uploadModal', { timeout: 5000 });

    // 4. Test invalid format: .exe (should be rejected by client-side validation)
    const invalidFile = createTestFile('test_invalid_tier3.exe', 0.1);
    const fileInput = page.locator('input[type="file"]').first();
    await expect(fileInput).toBeAttached({ timeout: 10000 });

    await fileInput.setInputFiles(invalidFile);
    await page.waitForTimeout(2000);

    // 5. Verify rejection - invalid file should NOT appear in file list
    const invalidFileItems = page.locator('.file-item');
    const invalidFileItemCount = await invalidFileItems.count();

    // Assertion: Invalid format file should be rejected (not added to file list)
    // The JavaScript validation checks allowed_types and rejects non-matching files
    expect(invalidFileItemCount).toBe(0);
  });
});
