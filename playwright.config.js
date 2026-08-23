import { defineConfig } from '@playwright/test';
import { resolve } from 'node:path';

const database = resolve('storage/framework/testing/domluveno-e2e.sqlite');
process.env.APP_ENV = 'testing';
process.env.APP_URL = 'http://127.0.0.1:8173';
process.env.APP_LOCALE = 'cs';
process.env.APP_FALLBACK_LOCALE = 'en';
process.env.DB_CONNECTION = 'sqlite';
process.env.DB_DATABASE = database;
process.env.CACHE_STORE = 'array';
process.env.MAIL_MAILER = 'array';
process.env.QUEUE_CONNECTION = 'sync';
process.env.SESSION_DRIVER = 'file';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [['list'], ['html', { open: 'never', outputFolder: 'storage/framework/testing/playwright-report' }]],
    globalTeardown: './tests/e2e/global-teardown.js',
    use: {
        baseURL: process.env.APP_URL,
        browserName: 'chromium',
        channel: 'chrome',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    expect: { timeout: 10_000 },
    webServer: {
        command: 'node tests/e2e/serve.js',
        url: process.env.APP_URL,
        env: { ...process.env },
        reuseExistingServer: false,
        timeout: 120_000,
        stdout: 'ignore',
        stderr: 'pipe',
    },
    projects: [
        {
            name: 'mobile',
            use: {
                browserName: 'chromium',
                viewport: { width: 390, height: 844 },
                isMobile: true,
                hasTouch: true,
            },
        },
        { name: 'tablet', use: { viewport: { width: 768, height: 1024 } } },
        { name: 'desktop', use: { viewport: { width: 1440, height: 900 } } },
    ],
});
