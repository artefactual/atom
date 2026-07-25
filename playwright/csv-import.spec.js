const path = require('path')
const { test, expect } = require('@playwright/test')
const { deleteResource, submitForm } = require('./helpers')

const fondsSlug = 'csv-import-order-fonds'
const orderedTitles = [
  'CSV import order fonds',
  'Series A',
  'SA Item 1',
  'SA Item 2',
  'SA Item 3',
  'SA Item 4',
  'SA Item 5',
  'SA Item 6',
  'SA Item 7',
  'SA Item 8',
  'SA Item 9',
  'SA Item 10',
  'SA Item 11',
  'SA Item 12',
  'SA Item 13',
  'SA Item 14',
  'SA Item 15',
  'Series B',
  'SB Item 1',
  'SB Item 2',
  'SB Item 3',
  'SB Item 4',
  'SB Item 5',
  'SB Item 6',
  'SB Item 7',
  'SB Item 8',
  'SB Item 9',
  'SB Item 10',
  'Series C',
  'SC Item 1',
  'SC Item 2',
  'SC Item 3',
  'SC Item 4',
  'SC Item 5',
]

async function saveTreeviewType(page, type) {
  await page.goto('/settings/treeview')
  await page.locator(`input[name="type"][value="${type}"]`).check()
  await submitForm(page.locator('#main-column form'))
}

async function waitForImport(request) {
  const unexpectedStatuses = []

  await expect
    .poll(
      async () => {
        const status = (await request.get(`/${fondsSlug}`)).status()
        if (![200, 404].includes(status)) {
          unexpectedStatuses.push(status)
        }

        return status
      },
      { timeout: 45000 }
    )
    .toBe(200)

  expect(unexpectedStatuses).toEqual([])
}

test('[surface:workflow-csv-import-order] preserves sibling order through the worker', async ({
  page,
}) => {
  test.setTimeout(90000)
  await deleteResource(page.request, fondsSlug, 'informationobject')
  await page.goto('/settings/treeview')
  const originalType = await page
    .locator('input[name="type"]:checked')
    .inputValue()
  let importInitiated = false

  try {
    await page.goto('/object/importSelect?type=csv')
    await page.locator('input[name="file"]').setInputFiles(
      path.join(__dirname, 'fixtures/import_order.csv')
    )
    await submitForm(page.locator('#main-column form'))
    await expect(page.getByText('Import file initiated')).toBeVisible()
    importInitiated = true

    await saveTreeviewType(page, 'fullWidth')
    await waitForImport(page.request)

    await page.goto(`/${fondsSlug}`)
    const nodes = page.locator('li.jstree-node')
    await expect(nodes).toHaveCount(4, { timeout: 30000 })

    for (const expectedCount of [19, 29, orderedTitles.length]) {
      await page.locator('li.jstree-closed > i').first().click()
      await expect(nodes).toHaveCount(expectedCount, { timeout: 30000 })
    }

    const actualTitles = await page
      .locator('li.jstree-node > a.jstree-anchor')
      .allTextContents()

    for (const [index, title] of orderedTitles.entries()) {
      expect(actualTitles[index]).toContain(title)
    }
  } finally {
    if (importInitiated) {
      await waitForImport(page.request)
    }
    await deleteResource(page.request, fondsSlug, 'informationobject')
    await saveTreeviewType(page, originalType)
  }
})
