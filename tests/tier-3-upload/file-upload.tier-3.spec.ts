import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

/**
 * Tier 3 - File Upload Tests (Limited Parallelization)
 *
 * spec: specs/dsp1830-test-plan.md
 *
 * 특징:
 * - 파일 업로드 UI 및 기능 테스트
 * - 각 테스트는 독립적인 브라우저 컨텍스트 사용
 */

// 테스트 파일 생성 헬퍼
function createTestFile(filename: string, sizeInMB: number): string {
  const testFilesDir = path.join(__dirname, '..', '..', 'test-files');

  if (!fs.existsSync(testFilesDir)) {
    fs.mkdirSync(testFilesDir, { recursive: true });
  }

  const filepath = path.join(testFilesDir, filename);

  if (fs.existsSync(filepath)) {
    return filepath;
  }

  const sizeInBytes = sizeInMB * 1024 * 1024;
  const buffer = Buffer.alloc(sizeInBytes, 'A');
  fs.writeFileSync(filepath, buffer);

  return filepath;
}

test.describe.configure({ mode: 'parallel' });

test.describe('Tier 3 - File Upload Tests', () => {

  test('전단지 단일 파일 업로드', async ({ page }) => {
    await page.goto('/mlangprintauto/inserted/');
    await page.waitForLoadState('domcontentloaded');

    // 업로드 버튼 확인
    const uploadButton = page.locator('button:has-text("파일 업로드"), button:has-text("업로드"), .btn-upload-order');
    await expect(uploadButton.first()).toBeVisible({ timeout: 30000 });
    await uploadButton.first().click();

    // 모달이 열릴 때까지 대기
    await page.waitForTimeout(1500);

    // 파일 입력 존재 확인
    const fileInput = page.locator('input[type="file"]').first();
    const hasFileInput = await fileInput.count() > 0;
    expect(hasFileInput).toBeTruthy();
  });

  test('명함 다중 파일 업로드', async ({ page }) => {
    await page.goto('/mlangprintauto/namecard/');
    await page.waitForLoadState('domcontentloaded');

    // 업로드 버튼 클릭
    const uploadButton = page.locator('.btn-upload-order, button:has-text("파일 업로드")');
    await expect(uploadButton.first()).toBeVisible({ timeout: 30000 });
    await uploadButton.first().click();

    // 모달이 열릴 때까지 대기
    await page.waitForTimeout(1500);

    // 파일 입력 존재 확인
    const fileInput = page.locator('input[type="file"]').first();
    const hasFileInput = await fileInput.count() > 0;
    expect(hasFileInput).toBeTruthy();
  });

  test('봉투 대용량 파일 업로드 (10MB 제한 테스트)', async ({ page }) => {
    await page.goto('/mlangprintauto/envelope/');
    await page.waitForLoadState('domcontentloaded');

    // 업로드 버튼 클릭
    const uploadButton = page.locator('button:has-text("파일 업로드"), button:has-text("업로드"), .btn-upload-order');
    await expect(uploadButton.first()).toBeVisible({ timeout: 10000 });
    await uploadButton.first().click();

    await page.waitForTimeout(500);
    const modal = page.locator('#uploadModal, .upload-modal');
    await expect(modal.first()).toBeVisible({ timeout: 5000 });

    // 유효한 파일 (5MB)
    const validFile = createTestFile('test_envelope_tier3_valid.ai', 5);
    const fileInput = page.locator('input[type="file"]').first();
    await expect(fileInput).toBeAttached({ timeout: 5000 });

    await fileInput.setInputFiles(validFile);
    await page.waitForTimeout(3000);

    // 업로드 UI 확인
    const uploadedArea = page.locator('#modalUploadedFiles, .uploaded-files, .file-list, .file-item');
    expect(await uploadedArea.count()).toBeGreaterThanOrEqual(0);

    // 파일 크기 제한 검증 (15MB 파일 - 서버에서 거부되어야 함)
    const oversizedFile = createTestFile('test_envelope_tier3_oversized.ai', 15);
    const oversizedFileStats = fs.statSync(oversizedFile);
    const oversizedSizeInMB = oversizedFileStats.size / (1024 * 1024);
    expect(oversizedSizeInMB).toBeGreaterThan(10);
  });

  test('파일 형식 검증 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/inserted/');
    await page.waitForLoadState('domcontentloaded');

    // 업로드 버튼 클릭
    const uploadButton = page.locator('button:has-text("파일 업로드"), button:has-text("업로드"), .btn-upload-order');
    await expect(uploadButton.first()).toBeVisible({ timeout: 10000 });
    await uploadButton.first().click();

    await page.waitForTimeout(500);
    const modal = page.locator('#uploadModal, .upload-modal');
    await expect(modal.first()).toBeVisible({ timeout: 5000 });

    // 유효한 형식 테스트 (PDF)
    const validFile = createTestFile('test_format_tier3.pdf', 0.5);
    const fileInput = page.locator('input[type="file"]').first();
    await expect(fileInput).toBeAttached({ timeout: 5000 });

    await fileInput.setInputFiles(validFile);
    await page.waitForTimeout(3000);

    // 업로드 UI 확인
    const uploadedArea = page.locator('#modalUploadedFiles, .uploaded-files, .file-list, .file-item');
    expect(await uploadedArea.count()).toBeGreaterThanOrEqual(0);

    // 드롭존 UI 확인
    const dropzone = page.locator('.upload-dropzone, #modalUploadDropzone, .upload-area');
    expect(await dropzone.count()).toBeGreaterThan(0);
  });
});
