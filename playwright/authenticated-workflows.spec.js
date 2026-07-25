const { test, expect } = require('@playwright/test')
const {
  createDescription,
  createUser,
  deleteResource,
  deleteUser,
  login,
  submitForm,
} = require('./helpers')

const actor = {
  slug: 'playwright-authenticated-authority',
  name: 'Playwright authenticated authority',
  updatedName: 'Playwright authenticated authority updated',
}
const repository = {
  slug: 'playwright-authenticated-repository',
  identifier: 'PW-AUTH-REPO',
  name: 'Playwright authenticated repository',
  updatedName: 'Playwright authenticated repository updated',
}
const accession = {
  slug: 'playwright-accession-001',
  identifier: 'PLAYWRIGHT-ACCESSION-001',
  source: 'Playwright authenticated transfer',
  location: 'Playwright shelf A',
  updatedLocation: 'Playwright shelf B',
}
const editor = {
  username: 'playwright-permissions-editor',
  email: 'playwright-permissions-editor@example.com',
  password: 'AtoM-Playwright-2026-Editor',
}
const globalReplacement = {
  identifier: 'PLAYWRIGHT-GLOBAL-REPLACE',
  title: 'Playwright global replacement before',
  updatedTitle: 'Playwright global replacement after',
}

test('[surface:workflow-logout] signs out', async ({ page }) => {
  await page.context().clearCookies()
  await login(page)
  await page.locator('#user-menu').click()
  await page.locator('a[href="/user/logout"]').click()

  await expect(page.locator('#admin-menu')).toHaveCount(0)
  await expect(page.locator('#user-menu')).toContainText('Log in')
})

test('[surface:workflow-admin-profile] opens the administrator profile', async ({
  page,
}) => {
  await page.goto('/')
  await page.locator('#user-menu').click()
  await page.getByRole('link', { name: 'My profile' }).click()
  await expect(page.locator('#main-column h1')).toContainText('User demo')
})

test('[surface:workflow-inline-translation] opens the translation editor', async ({
  page,
}) => {
  await page.goto('/?sf_culture=fr')

  const editor = page.locator('#l10n-client')

  await expect(editor).toBeAttached()
  await editor.locator('#l10n-client-show').click()
  const messages = editor.locator('.string-list li')

  await expect(messages.first()).toBeVisible()
  await messages.first().click()
  const source = editor.locator('textarea[name="source[]"]:visible')
  const target = editor.locator('textarea[name="target[]"]:visible')

  await expect(source).toHaveAttribute('readonly', 'readonly')
  await expect(source).not.toHaveValue('')
  await expect(target).toBeVisible()
})

test('[surface:workflow-authority-crud] manages an authority record', async ({
  page,
}) => {
  await deleteResource(page.request, actor.slug, 'actor')

  try {
    await page.goto('/actor/add')
    await page.getByRole('button', { name: 'Identity area' }).click()
    await page.locator('#entityType').selectOption({ label: 'Person' })
    await page.locator('#authorizedFormOfName').fill(actor.name)
    await submitForm(page.locator('#editForm'))

    expect(new URL(page.url()).pathname).toBe(`/${actor.slug}`)
    await expect(page.locator('#main-column')).toContainText(actor.name)

    await page
      .locator('.actions')
      .getByRole('link', { name: 'Edit', exact: true })
      .click()
    await page.getByRole('button', { name: 'Identity area' }).click()
    await page.locator('#authorizedFormOfName').fill(actor.updatedName)
    await submitForm(page.locator('#editForm'))
    await expect(page.locator('#main-column')).toContainText(
      actor.updatedName
    )

    await page.locator('.actions').getByRole('link', { name: 'Delete' }).click()
    await submitForm(page.locator('#main-column form'))
    expect((await page.request.get(`/${actor.slug}`)).status()).toBe(404)
  } finally {
    await deleteResource(page.request, actor.slug, 'actor')
  }
})

test('[surface:workflow-repository-crud] manages a repository', async ({
  page,
}) => {
  await deleteResource(page.request, repository.slug, 'repository')

  try {
    await page.goto('/repository/add')
    await page.getByRole('button', { name: 'Identity area' }).click()
    await page.locator('#identifier').fill(repository.identifier)
    await page.locator('#authorizedFormOfName').fill(repository.name)
    await submitForm(page.locator('#main-column form'))

    expect(new URL(page.url()).pathname).toBe(`/${repository.slug}`)
    await expect(page.locator('#main-column')).toContainText(repository.name)

    await page
      .locator('.actions')
      .getByRole('link', { name: 'Edit', exact: true })
      .click()
    await page.getByRole('button', { name: 'Identity area' }).click()
    await page
      .locator('#authorizedFormOfName')
      .fill(repository.updatedName)
    await submitForm(page.locator('#editForm'))
    await expect(page.locator('#main-column')).toContainText(
      repository.updatedName
    )

    await page.locator('.actions').getByRole('link', { name: 'Delete' }).click()
    await submitForm(page.locator('#main-column form'))
    expect(
      (await page.request.get(`/${repository.slug}`)).status()
    ).toBe(404)
  } finally {
    await deleteResource(page.request, repository.slug, 'repository')
  }
})

test('[surface:workflow-accession-crud] manages an accession', async ({
  page,
}) => {
  await deleteResource(page.request, accession.slug, 'accession')

  try {
    await page.goto('/accession/add')
    await page.getByRole('button', { name: 'Basic info' }).click()
    await page.locator('#identifier').fill(accession.identifier)
    await page.locator('#sourceOfAcquisition').fill(accession.source)
    await page.locator('#locationInformation').fill(accession.location)
    await submitForm(page.locator('#editForm'))

    expect(new URL(page.url()).pathname).toBe(`/${accession.slug}`)
    await expect(page.locator('#main-column')).toContainText(
      accession.identifier
    )

    await page.locator('.actions').getByRole('link', { name: 'Edit' }).click()
    await page.getByRole('button', { name: 'Basic info' }).click()
    await page.locator('#locationInformation').fill(accession.updatedLocation)
    await submitForm(page.locator('#editForm'))
    await expect(page.locator('#main-column')).toContainText(
      accession.updatedLocation
    )

    await page.locator('.actions').getByRole('link', { name: 'Delete' }).click()
    await submitForm(page.locator('#main-column form'))
    expect((await page.request.get(`/${accession.slug}`)).status()).toBe(404)
  } finally {
    await deleteResource(page.request, accession.slug, 'accession')
  }
})

test('[surface:workflow-global-setting] saves and restores a setting', async ({
  page,
}) => {
  await page.goto('/settings/global')
  await page.getByRole('button', { name: 'Search and browse' }).click()
  const input = page.locator('#global_settings_hits_per_page')
  const original = await input.inputValue()
  const updated = '25' === original ? '30' : '25'

  const save = async (value) => {
    await page.goto('/settings/global')
    await page.getByRole('button', { name: 'Search and browse' }).click()
    await page.locator('#global_settings_hits_per_page').fill(value)
    await submitForm(page.locator('#main-column form'))
    await expect(page.getByText('Global settings saved.')).toBeVisible()
  }

  try {
    await save(updated)
    await page.reload()
    await page.getByRole('button', { name: 'Search and browse' }).click()
    await expect(page.locator('#global_settings_hits_per_page')).toHaveValue(
      updated
    )
  } finally {
    await save(original)
  }
})

test('[surface:workflow-global-replace] replaces one description title', async ({
  page,
}) => {
  let slug

  try {
    slug = await createDescription(page.request, {
      identifier: globalReplacement.identifier,
      title: globalReplacement.title,
    })

    await page.goto('/search/globalReplace')
    await page.getByLabel('Search', { exact: true }).last().fill(
      globalReplacement.title
    )
    await page
      .getByLabel('Field', { exact: true })
      .last()
      .selectOption('title')
    await page
      .locator('#main-column')
      .getByRole('button', { name: 'Search', exact: true })
      .click()

    await expect(page.locator('#main-column')).toContainText(
      '1 descriptions match this search.'
    )

    await page.getByLabel('Replace', { exact: true }).fill('before')
    await page.getByLabel('With', { exact: true }).fill('after')
    await page
      .getByLabel('Field', { exact: true })
      .last()
      .selectOption('title')
    await page.getByRole('button', { name: 'Review replacement' }).click()

    await expect(page.getByText('This action cannot be undone!')).toBeVisible()
    await expect(page.locator('#main-column')).toContainText(
      'This will permanently modify 1 descriptions.'
    )
    await page.getByRole('button', { name: 'Replace', exact: true }).click()

    await page.goto(`/${slug}`)
    await expect(page.locator('#main-column h1')).toContainText(
      globalReplacement.updatedTitle
    )
  } finally {
    if (slug) {
      await deleteResource(page.request, slug, 'informationobject')
    }
  }
})

test('[surface:workflow-editor-boundary][surface:workflow-editor-crud] enforces editor permissions', async ({
  browser,
  page,
  baseURL,
}) => {
  test.setTimeout(60000)
  await deleteUser(page, editor.username)
  let descriptionSlug

  try {
    await createUser(page, { ...editor, group: 'editor' })

    const context = await browser.newContext({
      baseURL,
      storageState: {
        cookies: [],
        origins: [],
      },
    })
    const editorPage = await context.newPage()

    try {
      await login(editorPage, editor.email, editor.password)
      await editorPage.goto('/')
      await expect(editorPage.locator('#user-menu')).toContainText(
        editor.username
      )
      await expect(editorPage.locator('#add-menu')).toBeVisible()
      await expect(editorPage.locator('#manage-menu')).toBeVisible()
      await expect(editorPage.locator('#admin-menu')).toHaveCount(0)

      const denied = await editorPage.request.get('/user/list')

      expect(denied.status()).toBe(403)
      expect(await denied.text()).toContain(
        'Sorry, you do not have permission to access that page'
      )

      descriptionSlug = await createDescription(editorPage.request, {
        identifier: 'playwright-editor-permissions',
        title: 'Playwright editor permission description',
      })
      await editorPage.goto(`/${descriptionSlug}`)
      await expect(editorPage.locator('#main-column h1')).toContainText(
        'Playwright editor permission description'
      )

      await deleteResource(
        editorPage.request,
        descriptionSlug,
        'informationobject'
      )
      expect(
        (await editorPage.request.get(`/${descriptionSlug}`)).status()
      ).toBe(404)
      descriptionSlug = undefined
    } finally {
      await context.close()
    }
  } finally {
    if (descriptionSlug) {
      await deleteResource(
        page.request,
        descriptionSlug,
        'informationobject'
      )
    }
    await deleteUser(page, editor.username)
  }
})
