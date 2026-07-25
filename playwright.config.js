const { defineConfig, devices } = require('@playwright/test')

const authState = 'output/playwright/admin.json'

module.exports = defineConfig({
  testDir: './playwright',
  outputDir: 'output/playwright/test-results',
  fullyParallel: false,
  workers: 1,
  retries: 1,
  timeout: 30000,
  expect: {
    timeout: 10000,
  },
  reporter: [
    ['list'],
    ['./playwright/coverage-reporter.js'],
  ],
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:63001',
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'setup',
      testMatch: /.*\.setup\.js/,
      use: {
        ...devices['Desktop Chrome'],
        channel: 'chrome',
      },
    },
    {
      name: 'chrome',
      testIgnore: /.*\.setup\.js/,
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        channel: 'chrome',
        storageState: authState,
      },
    },
  ],
})
