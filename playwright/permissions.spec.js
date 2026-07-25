const { test, expect } = require('@playwright/test')
const {
  createDescription,
  createUser,
  deleteResource,
  deleteUser,
  login,
  submitForm,
} = require('./helpers')

const contributor = {
  username: 'playwright-permissions-contributor',
  email: 'playwright-permissions-contributor@example.com',
  password: 'AtoM-Playwright-2026-Contributor',
  group: 'contributor',
  active: false,
}

test('[surface:workflow-contributor-boundary] activates a contributor and denies deletion', async ({
  browser,
  page,
  baseURL,
}) => {
  test.setTimeout(60000)
  await deleteUser(page, contributor.username)
  let descriptionSlug
  let context

  try {
    const userSlug = await createUser(page, contributor)
    context = await browser.newContext({
      baseURL,
      storageState: {
        cookies: [],
        origins: [],
      },
    })
    let contributorPage = await context.newPage()

    await contributorPage.goto('/user/login')
    const loginForm = contributorPage.locator('#main-column form')
    await loginForm.locator('#email').fill(contributor.email)
    await loginForm.locator('#password').fill(contributor.password)
    await submitForm(loginForm)
    await expect(contributorPage.locator('#user-menu')).toContainText('Log in')

    await page.goto(`/${userSlug}/edit`)
    await page.locator('#active').check()
    await page.locator('#currentPassword').fill('demo')
    await submitForm(page.locator('#main-column form'))
    await expect(page.locator('#main-column h1')).toContainText(
      contributor.username
    )

    await context.close()
    context = await browser.newContext({
      baseURL,
      storageState: {
        cookies: [],
        origins: [],
      },
    })
    contributorPage = await context.newPage()
    await login(
      contributorPage,
      contributor.email,
      contributor.password
    )
    await contributorPage.goto('/')

    await expect(contributorPage.locator('#user-menu')).toContainText(
      contributor.username
    )
    await expect(contributorPage.locator('#add-menu')).toBeVisible()
    await expect(contributorPage.locator('#admin-menu')).toHaveCount(0)

    descriptionSlug = await createDescription(contributorPage.request, {
      identifier: 'playwright-contributor-permissions',
      title: 'Playwright contributor permission description',
    })

    expect(
      (
        await contributorPage.request.get(`/${descriptionSlug}/edit`)
      ).status()
    ).toBe(200)

    const denied = await contributorPage.request.get(
      `/${descriptionSlug}/informationobject/delete`
    )

    expect(denied.status()).toBe(403)
    expect(await denied.text()).toContain(
      'Sorry, you do not have permission to access that page'
    )
  } finally {
    if (context) {
      await context.close()
    }
    if (descriptionSlug) {
      await deleteResource(
        page.request,
        descriptionSlug,
        'informationobject'
      )
    }
    await deleteUser(page, contributor.username)
  }
})
