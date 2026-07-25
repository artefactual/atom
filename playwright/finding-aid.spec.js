const { test, expect } = require('@playwright/test')
const {
  createDescription,
  deleteResource,
  publishDescription,
  submitForm,
} = require('./helpers')

const description = {
  slug: 'playwright-finding-aid-description',
  identifier: 'PW-FINDING-AID',
  title: 'Playwright finding aid description',
}

async function saveFindingAidSetting(page, enabled) {
  await page.goto('/settings/findingAid')
  await page
    .locator(`#finding_aid_finding_aids_enabled_${enabled ? '1' : '0'}`)
    .check()
  await submitForm(
    page.locator('[data-cy="settings-finding-aid-form"]')
  )
  await expect(page.getByText('Finding aid settings saved.')).toBeVisible()
}

async function assertFindingAidState(page, slug, enabled) {
  await page.goto('/informationobject/browse')
  await page.locator('[data-cy="advanced-search-toggle"]').click()

  const searchFields = page.locator('select[name="sf0"]')
  await expect(
    searchFields.locator('option[value="findingAidTranscript"]')
  ).toHaveCount(enabled ? 1 : 0)
  await expect(
    searchFields.locator(
      'option[value="allExceptFindingAidTranscript"]'
    )
  ).toHaveCount(enabled ? 1 : 0)
  await expect(page.locator('#findingAidStatus')).toHaveCount(enabled ? 1 : 0)

  await page.goto(`/${slug}`)
  await expect(
    page.locator('[data-cy="generate-finding-aid"]')
  ).toHaveCount(enabled ? 1 : 0)
  await expect(
    page.locator('[data-cy="upload-finding-aid"]')
  ).toHaveCount(enabled ? 1 : 0)
}

test('[surface:workflow-finding-aid-setting] applies and restores finding aid availability', async ({
  page,
}) => {
  test.setTimeout(60000)
  await deleteResource(
    page.request,
    description.slug,
    'informationobject'
  )
  let original
  let slug

  try {
    await page.goto('/settings/findingAid')
    original = await page
      .locator('#finding_aid_finding_aids_enabled_1')
      .isChecked()

    slug = await createDescription(page.request, {
      identifier: description.identifier,
      title: description.title,
    })
    await publishDescription(page, slug)

    await saveFindingAidSetting(page, false)
    await assertFindingAidState(page, slug, false)

    await saveFindingAidSetting(page, true)
    await assertFindingAidState(page, slug, true)
  } finally {
    if (undefined !== original) {
      await saveFindingAidSetting(page, original)
    }
    if (slug) {
      await deleteResource(page.request, slug, 'informationobject')
    }
  }
})
