const { test, expect } = require('@playwright/test')
const { routes } = require('./authenticated-surface')

function normalizedPath(url) {
  const parsed = new URL(url)

  return `${parsed.pathname}${parsed.search}`
}

test('Matches the authenticated navigation inventory', async ({ page }) => {
  await page.goto('/')

  for (const group of ['add', 'manage', 'import', 'admin']) {
    const actual = await page
      .locator(`ul[aria-labelledby="${group}-menu"] a`)
      .evaluateAll((links) => links.map((link) => link.href))
    const expected = routes
      .filter((route) => group === route.group)
      .map((route) => route.path)
      .sort()

    expect(actual.map(normalizedPath).sort()).toEqual(expected)
  }

  await page.goto('/settings/global')
  const actual = await page
    .locator('#settings-menu a')
    .evaluateAll((links) => links.map((link) => link.href))
  const expected = routes
    .filter((route) => 'settings' === route.group)
    .map((route) => route.path)
    .sort()

  expect(actual.map(normalizedPath).sort()).toEqual(expected)
})

for (const route of routes) {
  test(`[surface:${route.id}] loads ${route.path}`, async ({ page }) => {
    test.fixme(Boolean(route.fixme), route.fixme)
    const failedResponses = []

    page.on('response', (response) => {
      if (response.status() >= 400) {
        const url = new URL(response.url())

        failedResponses.push(`${response.status()} ${url.pathname}`)
      }
    })

    const response = await page.goto(route.path)

    expect(response?.status()).toBeLessThan(400)
    expect(new URL(page.url()).pathname).toBe(
      route.expectedPath || new URL(route.path, page.url()).pathname
    )
    await expect(page.locator('#main-column h1').first()).toBeVisible()
    await expect(
      page.locator('#main-column > .alert-danger:visible')
    ).toHaveCount(0)
    expect(failedResponses).toEqual([])
  })
}
