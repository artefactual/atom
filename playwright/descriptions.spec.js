const { test, expect } = require('@playwright/test')
const {
  createDescription,
  deleteResource,
  submitForm,
} = require('./helpers')

const records = {
  createGui: {
    slug: 'playwright-description-gui',
    identifier: 'PW-DESCRIPTION-GUI',
    title: 'Playwright description GUI',
  },
  createRequest: {
    slug: 'playwright-description-request',
    identifier: 'PW-DESCRIPTION-REQUEST',
    title: 'Playwright description request',
  },
  deleteGui: {
    identifier: 'PW-DESCRIPTION-DELETE-GUI',
    title: 'Playwright description delete GUI',
  },
  deleteRequest: {
    identifier: 'PW-DESCRIPTION-DELETE-REQUEST',
    title: 'Playwright description delete request',
  },
}

test('[surface:workflow-description-create-gui] creates a description through the form', async ({
  page,
}) => {
  const record = records.createGui
  await deleteResource(page.request, record.slug, 'informationobject')

  try {
    await page.goto('/informationobject/add')
    await page.getByRole('button', { name: 'Identity area' }).click()
    await page.locator('#identifier').fill(record.identifier)
    await page.locator('#title').fill(record.title)
    await submitForm(page.locator('#main-column form'))

    expect(new URL(page.url()).pathname).toBe(`/${record.slug}`)
    await expect(page.locator('#main-column h1')).toContainText(
      `${record.identifier} - ${record.title}`
    )
  } finally {
    await deleteResource(page.request, record.slug, 'informationobject')
  }
})

test('[surface:workflow-description-create-request] creates a description through HTTP', async ({
  page,
}) => {
  const record = records.createRequest
  await deleteResource(page.request, record.slug, 'informationobject')

  try {
    const slug = await createDescription(page.request, {
      identifier: record.identifier,
      title: record.title,
    })

    expect(slug).toBe(record.slug)
    await page.goto(`/${slug}`)
    await expect(page.locator('#main-column h1')).toContainText(
      `${record.identifier} - ${record.title}`
    )
  } finally {
    await deleteResource(page.request, record.slug, 'informationobject')
  }
})

test('[surface:workflow-description-delete-gui] deletes a description through the form', async ({
  page,
}) => {
  let slug

  try {
    slug = await createDescription(page.request, records.deleteGui)
    await page.goto(`/${slug}`)
    await page.locator('.actions').getByRole('link', { name: 'Delete' }).click()
    await submitForm(page.locator('#main-column form'))

    const response = await page.request.get(`/${slug}`)

    expect(response.status()).toBe(404)
    expect(await response.text()).toContain('Sorry, page not found')
    slug = undefined
  } finally {
    if (slug) {
      await deleteResource(page.request, slug, 'informationobject')
    }
  }
})

test('[surface:workflow-description-delete-request] deletes a description through HTTP', async ({
  page,
}) => {
  const slug = await createDescription(
    page.request,
    records.deleteRequest
  )

  await deleteResource(page.request, slug, 'informationobject')
  const response = await page.request.get(`/${slug}`)

  expect(response.status()).toBe(404)
  expect(await response.text()).toContain('Sorry, page not found')
})
