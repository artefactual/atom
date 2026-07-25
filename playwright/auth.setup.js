const fs = require('fs')
const path = require('path')
const { test, expect } = require('@playwright/test')
const { login } = require('./helpers')

const authState = 'output/playwright/admin.json'

test('[surface:workflow-login] signs in as an administrator', async ({
  page,
}) => {
  await login(page)
  await expect(page.locator('#admin-menu')).toBeVisible()
  fs.mkdirSync(path.dirname(authState), { recursive: true })
  await page.context().storageState({ path: authState })
})
