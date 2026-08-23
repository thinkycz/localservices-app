import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const browserHealth = new WeakMap();

test.beforeEach(async ({ page }) => {
    const state = { consoleErrors: [], badResponses: [] };
    browserHealth.set(page, state);
    page.on('console', (message) => {
        if (message.type() === 'error') state.consoleErrors.push(message.text());
    });
    page.on('response', (response) => {
        if (response.status() >= 400) state.badResponses.push(`${response.status()} ${response.url()}`);
    });
});

test.afterEach(async ({ page }) => {
    const state = browserHealth.get(page);
    expect(state?.consoleErrors ?? []).toEqual([]);
    expect(state?.badResponses ?? []).toEqual([]);
});

function nextWeekday(daysAhead = 2) {
    const date = new Date();
    date.setDate(date.getDate() + daysAhead);
    while ([0, 6].includes(date.getDay())) date.setDate(date.getDate() + 1);
    return localISODate(date);
}

function localISODate(date = new Date()) {
    const offset = date.getTimezoneOffset();
    return new Date(date.getTime() - offset * 60_000).toISOString().slice(0, 10);
}

async function expectHealthyPage(page) {
    const dimensions = await page.evaluate(() => ({
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
    }));
    expect(dimensions.scrollWidth).toBeLessThanOrEqual(dimensions.clientWidth + 1);
}

async function expectAccessible(page) {
    const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();

    expect(
        results.violations.map(({ id, impact, nodes }) => ({
            id,
            impact,
            targets: nodes.map((node) => node.target),
        })),
    ).toEqual([]);
}

test('guest discovers a real shop and completes a booking', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('heading', { name: /Najděte termín/ })).toBeVisible();
    await expectAccessible(page);
    await page.getByLabel('Služba nebo místo').fill('střih');
    await page.getByRole('button', { name: 'Hledat' }).click();
    await expect(page).toHaveURL(/q=st%C5%99ih/);
    await page.getByRole('link', { name: /Holičství U Tří křesel/ }).click();
    await expect(page.getByRole('heading', { name: 'Holičství U Tří křesel' })).toBeVisible();
    await expect(page.getByText('Marcus Thompson')).toHaveCount(0);
    await expectAccessible(page);

    await page.getByRole('button', { name: /Pánský střih/ }).click();
    await page.getByRole('button', { name: /Vybrat termín/ }).click();
    await expectAccessible(page);
    await page.getByLabel('Datum návštěvy').fill(nextWeekday());
    const timeSection = page.getByRole('region', { name: 'Vyberte čas' });
    await expect(timeSection.getByRole('button').first()).toBeVisible();
    await timeSection.getByRole('button').first().click();
    await page.getByLabel('Jméno a příjmení').fill('Alena Testovací');
    await page.getByLabel('E-mail', { exact: true }).fill('alena.e2e@example.cz');
    await page.getByLabel('Telefon').fill('+420 777 888 999');
    await page.getByRole('button', { name: 'Zkontrolovat rezervaci' }).click();
    await expect(page.getByRole('heading', { name: 'Zkontrolujte rezervaci' })).toBeVisible();
    await expectAccessible(page);
    await page.getByRole('button', { name: 'Potvrdit rezervaci' }).click();
    await expect(page).toHaveURL(/\/guest\/bookings\/\d+\/[A-Za-z0-9]+/);
    await expect(page.getByText('alena.e2e@example.cz')).toBeVisible();
    await expectAccessible(page);
    await page.getByRole('button', { name: 'Zrušit rezervaci' }).click();
    await page.getByRole('button', { name: 'Ano, zrušit' }).click();
    await expect(page.getByText('Tato rezervace už byla zrušena.')).toBeVisible();

    await page.goto('/contact');
    await expect(page.getByRole('heading', { name: 'Napište nám' })).toBeVisible();
    await expectAccessible(page);

    await expectHealthyPage(page);
});

test('customer can sign in, review booking history, profile, and sign out', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel(/E-mail|Email/).fill('customer@email.com');
    await page.getByLabel(/Heslo|Password/).fill('password');
    await page.getByRole('button', { name: /Přihlásit|Sign in|Log in/ }).click();
    await expect(page).toHaveURL('/');

    await page.goto('/bookings');
    await expect(page.getByRole('heading', { name: /Moje rezervace|My bookings/i })).toBeVisible();
    await expect(page.getByRole('heading', { name: /Nadcházející|Upcoming/i, exact: true })).toBeVisible();
    await expectAccessible(page);
    const rebook = page.getByRole('link', { name: /Rezervovat znovu|Book again/i }).first();
    await expect(rebook).toBeVisible();
    await rebook.click();
    await expect(page).toHaveURL(/\/shops\/[^/]+\/book/);
    await expectAccessible(page);
    await page.goto('/my-reviews');
    await expect(page.getByRole('heading', { name: /Moje recenze|My reviews/i })).toBeVisible();
    await expectAccessible(page);
    await page.goto('/profile');
    await expect(page.getByLabel(/Jméno a příjmení|Full name/)).toHaveValue('Eva Nováková');
    await expectAccessible(page);
    await expectHealthyPage(page);

    await page.getByRole('button', { name: /Open account menu|Otevřít nabídku účtu/i }).click();
    await page.getByRole('button', { name: /Odhlásit|Log out/i }).click();
    await expect(page).toHaveURL('/');
});

test('verified provider can use the responsive management core', async ({ page }, testInfo) => {
    await page.goto('/login');
    await page.getByLabel(/E-mail|Email/).fill('vendor@email.com');
    await page.getByLabel(/Heslo|Password/).fill('password');
    await page.getByRole('button', { name: /Přihlásit|Sign in|Log in/ }).click();
    await expect(page).toHaveURL(/\/vendor\/dashboard/);
    await expectAccessible(page);

    await page.goto('/vendor/calendar');
    if (testInfo.project.name === 'mobile') {
        await page.waitForURL(/view=day/);
        expect(new URL(page.url()).searchParams.get('start_date')).toBe(localISODate());
    }
    await expect(page.getByRole('button', { name: /Dnes|Today/i })).toBeVisible();
    await expectAccessible(page);
    await page.goto('/vendor/bookings');
    await expect(page.locator('#main-content').getByRole('heading', { name: 'Rezervace', exact: true })).toBeVisible();
    await expectAccessible(page);
    await page.goto('/vendor/customers');
    await expect(page.locator('#main-content').getByRole('heading', { name: 'Zákazníci', exact: true })).toBeVisible();
    await expectAccessible(page);
    await page.goto('/vendor/shops');
    await expect(page.getByRole('link', { name: 'Holičství U Tří křesel', exact: true }).filter({ visible: true })).toBeVisible();
    await expectAccessible(page);
    await expectHealthyPage(page);
});

test('public help, legal, and account-entry surfaces remain clear and accessible', async ({ page }) => {
    const paths = [
        ['/faq', /Časté dotazy|Frequently asked questions/i],
        ['/privacy', /Ochrana osobních údajů|Privacy Policy/i],
        ['/terms', /Podmínky používání|Terms of Service/i],
        ['/register', /Vytvořte si účet|Create your account/i],
        ['/forgot-password', /Zapomněli jste heslo|Forgot your password/i],
    ];

    for (const [path, heading] of paths) {
        await page.goto(path);
        await expect(page.getByRole('heading', { name: heading })).toBeVisible();
        await expectAccessible(page);
        await expectHealthyPage(page);
    }

    await page.goto('/');
    await page.goto('/language/en');
    await expect(page.getByRole('heading', { name: /Find a time/i })).toBeVisible();
    await page.goto('/language/cs');
    await expect(page.getByRole('heading', { name: /Najděte termín/i })).toBeVisible();
});
