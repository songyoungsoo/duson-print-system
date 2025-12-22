/**
 * Playwright 설정 파일
 * 경로: playwright.config.js
 */

const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',

  // 테스트 타임아웃
  timeout: 30000,

  // 실패 시 재시도
  retries: 1,

  // 병렬 실행 워커 수
  workers: 1,

  // 리포터 설정
  reporter: [
    ['html', { outputFolder: 'tests/playwright-report' }],
    ['list']
  ],

  use: {
    // 기본 URL
    baseURL: 'http://localhost',

    // 헤드리스 모드 (GUI 없이 실행)
    headless: true,

    // 스크린샷 설정
    screenshot: 'only-on-failure',

    // 비디오 녹화
    video: 'retain-on-failure',

    // 브라우저 컨텍스트 옵션
    viewport: { width: 1280, height: 720 },

    // 네트워크 대기
    actionTimeout: 10000,
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
