import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',

  // 병렬 실행 설정
  fullyParallel: true,
  workers: process.env.CI ? 5 : 10, // 로컬: 10개 워커, CI: 5개 워커

  // 재시도 설정
  retries: process.env.CI ? 2 : 0,
  forbidOnly: !!process.env.CI,

  // 타임아웃
  timeout: 60 * 1000, // 60초
  expect: {
    timeout: 10 * 1000, // 10초
  },

  // 리포터
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['json', { outputFile: 'playwright-report/results.json' }],
    ['list'],
  ],

  use: {
    baseURL: 'http://dsp1830.shop',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',

    // 브라우저 설정
    viewport: { width: 1280, height: 720 },
    ignoreHTTPSErrors: true,
  },

  projects: [
    // 🟢 Group A: 읽기 전용 테스트 (최대 병렬)
    {
      name: 'group-a-readonly',
      testMatch: /.*\.group-a\.spec\.ts/,
      fullyParallel: true,
      workers: 11,
      use: { ...devices['Desktop Chrome'] },
    },

    // 🟡 Group B: 가격 계산 테스트 (최대 병렬)
    {
      name: 'group-b-calculation',
      testMatch: /.*\.group-b\.spec\.ts/,
      fullyParallel: true,
      workers: 11,
      use: { ...devices['Desktop Chrome'] },
    },

    // 🟠 Group C: 단일 기능 테스트 (제한 병렬)
    {
      name: 'group-c-features',
      testMatch: /.*\.group-c\.spec\.ts/,
      fullyParallel: true,
      workers: 5,
      use: {
        ...devices['Desktop Chrome'],
        // 각 테스트마다 독립 브라우저 컨텍스트
        contextOptions: {
          ignoreHTTPSErrors: true,
        },
      },
    },

    // 🔴 Group D: E2E 플로우 (제한 병렬)
    {
      name: 'group-d-e2e',
      testMatch: /.*\.group-d\.spec\.ts/,
      fullyParallel: true,
      workers: 3, // 리소스 고려
      use: {
        ...devices['Desktop Chrome'],
        // E2E는 느린 네트워크 시뮬레이션
        contextOptions: {
          ignoreHTTPSErrors: true,
        },
      },
      timeout: 60 * 1000, // E2E는 60초 타임아웃
    },

    // 🔵 Group E: 관리자 기능 (순차 실행)
    {
      name: 'group-e-admin',
      testMatch: /.*\.group-e\.spec\.ts/,
      fullyParallel: false,
      workers: 1,
      use: { ...devices['Desktop Chrome'] },
    },

    // 🟢 Tier 1: 읽기 전용 테스트 (최대 병렬)
    {
      name: 'tier-1-readonly',
      testMatch: /.*\.tier-1\.spec\.ts/,
      fullyParallel: true,
      workers: 11,
      use: { ...devices['Desktop Chrome'] },
    },

    // 🟢 Tier 2: 가격 계산 테스트 (최대 병렬)
    {
      name: 'tier-2-calculation',
      testMatch: /.*\.tier-2\.spec\.ts/,
      fullyParallel: true,
      workers: 11,
      use: { ...devices['Desktop Chrome'] },
    },

    // 🟡 Tier 3: 파일 업로드 테스트 (제한 병렬)
    {
      name: 'tier-3-upload',
      testMatch: /.*\.tier-3\.spec\.ts/,
      fullyParallel: true,
      workers: 4,
      use: {
        ...devices['Desktop Chrome'],
        contextOptions: {
          ignoreHTTPSErrors: true,
        },
      },
    },

    // 🔴 Tier 4: E2E 플로우 (순차 실행)
    {
      name: 'tier-4-e2e',
      testMatch: /.*\.tier-4\.spec\.ts/,
      fullyParallel: false,
      workers: 1,
      use: {
        ...devices['Desktop Chrome'],
        contextOptions: {
          ignoreHTTPSErrors: true,
        },
      },
      timeout: 90 * 1000, // E2E는 90초 타임아웃
    },

    // 🟢 Dashboard: Admin dashboard tests
    {
      name: 'dashboard',
      testMatch: /tests\/dashboard\/.*\.spec\.ts/,
      fullyParallel: true,
      workers: 5,
      use: {
        ...devices['Desktop Chrome'],
        baseURL: 'http://localhost',
      },
      timeout: 60 * 1000,
    },
  ],

  webServer: {
    command: 'echo "Using external server at http://dsp1830.shop"',
    url: 'http://dsp1830.shop',
    reuseExistingServer: true,
    timeout: 5 * 1000,
  },
});
