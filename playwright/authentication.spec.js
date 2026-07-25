const { test, expect } = require('@playwright/test')
const { loginViaRequest, submitForm } = require('./helpers')

test('[surface:workflow-login-menu] signs in through the user menu', async ({
  page,
}) => {
  await page.context().clearCookies()
  await page.goto('/')
  await page.locator('#user-menu').click()

  const form = page.locator('#user-menu + .dropdown-menu form')
  await expect(form.locator('#csrf_token')).toBeAttached()
  await form.locator('#email').fill('demo@example.com')
  await form.locator('#password').fill('demo')
  await submitForm(form)

  await page.locator('#user-menu').click()
  await expect(page.getByRole('link', { name: 'Log out' })).toBeVisible()
})

test('[surface:workflow-login-errors] rejects bad credentials consistently', async ({
  page,
}) => {
  await page.context().clearCookies()
  await page.goto('/')
  await page.locator('#user-menu').click()

  const menuForm = page.locator('#user-menu + .dropdown-menu form')
  await menuForm.locator('#email').fill('unknown@user.com')
  await menuForm.locator('#password').fill('demo')
  await submitForm(menuForm)
  await expect(
    page.getByText('Sorry, unrecognized email or password')
  ).toBeVisible()

  await page.goto('/user/login')
  const pageForm = page.locator('#main-column form')
  await pageForm.locator('#email').fill('demo@example.com')
  await pageForm.locator('#password').fill('unknown_password')
  await submitForm(pageForm)
  await expect(
    page.getByText('Sorry, unrecognized email or password')
  ).toBeVisible()
})

test('[surface:workflow-login-request] signs in without browser form interaction', async ({
  page,
}) => {
  await page.context().clearCookies()
  await loginViaRequest(page.request)
  await page.goto('/')
  await page.locator('#user-menu').click()
  await expect(page.getByRole('link', { name: 'Log out' })).toBeVisible()
})
