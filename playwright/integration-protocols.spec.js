const { test, expect } = require('@playwright/test')
const {
  createDescription,
  deleteResource,
  publishDescription,
} = require('./helpers')

const requiredPlugins = ['arOaiPlugin', 'arRestApiPlugin', 'qtSwordPlugin']
const description = {
  slug: 'playwright-protocol-compatibility',
  identifier: 'PW-PROTOCOL-COMPATIBILITY',
  title: 'Playwright protocol compatibility',
}

async function pluginSelection(page) {
  await page.goto('/sfPluginAdminPlugin/plugins')

  return page
    .locator('input[name="enabled[]"]:not(:disabled)')
    .evaluateAll((inputs) =>
      inputs.filter((input) => input.checked).map((input) => input.value)
    )
}

async function savePlugins(page, enabled) {
  await page.goto('/sfPluginAdminPlugin/plugins')
  const inputs = page.locator('input[name="enabled[]"]:not(:disabled)')

  for (const input of await inputs.all()) {
    await input.setChecked(enabled.includes(await input.inputValue()))
  }

  await page.locator('#main-column form').evaluate((form) => form.submit())
  await expect(page).toHaveURL(/\/sfPluginAdminPlugin\/plugins$/)

  await page.goto('/sfPluginAdminPlugin/plugins')
  for (const input of await inputs.all()) {
    expect(await input.isChecked()).toBe(
      enabled.includes(await input.inputValue())
    )
  }
}

async function xmlValues(page, xml) {
  return page.evaluate((source) => {
    const document = new DOMParser().parseFromString(
      source,
      'application/xml'
    )
    const value = (name) =>
      document.getElementsByTagNameNS('*', name)[0]?.textContent || null

    return {
      parserError: Boolean(document.querySelector('parsererror')),
      protocolVersion: value('protocolVersion'),
      repositoryName: value('repositoryName'),
      errorCode:
        document.getElementsByTagNameNS('*', 'error')[0]?.getAttribute(
          'code'
        ) || null,
      listRecords: document.getElementsByTagNameNS('*', 'ListRecords')
        .length,
      swordVersion: document.getElementsByTagNameNS(
        'http://purl.org/net/sword/',
        'version'
      )[0]?.textContent,
      swordService: document.getElementsByTagNameNS(
        'http://www.w3.org/2007/app',
        'service'
      ).length,
    }
  }, xml)
}

function basicAuth() {
  return {
    Authorization: `Basic ${Buffer.from(
      'demo@example.com:demo'
    ).toString('base64')}`,
  }
}

test('[surface:workflow-oai-protocol][surface:workflow-rest-protocol][surface:workflow-sword-protocol] preserves integration protocols', async ({
  baseURL,
  page,
  playwright,
}) => {
  test.setTimeout(90000)
  const originalPlugins = await pluginSelection(page)
  const anonymous = await playwright.request.newContext({
    baseURL,
    storageState: {
      cookies: [],
      origins: [],
    },
  })
  let slug

  try {
    await savePlugins(page, [
      ...new Set([...originalPlugins, ...requiredPlugins]),
    ])

    await page.goto('/informationobject/add')
    const fonds = await page
      .locator('#levelOfDescription option')
      .filter({ hasText: /^Fonds$/ })
      .getAttribute('value')

    slug = await createDescription(page.request, {
      identifier: description.identifier,
      title: description.title,
      levelOfDescription: fonds,
      extentAndMedium: '1 digital record',
    })
    await publishDescription(page, slug)

    const identify = await page.request.get('/;oai?verb=Identify')

    expect(identify.status()).toBe(200)
    expect(identify.headers()['content-type']).toContain('text/xml')
    const identifyXml = await xmlValues(page, await identify.text())
    expect(identifyXml.parserError).toBe(false)
    expect(identifyXml.protocolVersion).toBe('2.0')
    expect(identifyXml.repositoryName).toBeTruthy()

    await expect
      .poll(
        async () => {
          const response = await page.request.get(
            '/;oai?verb=ListRecords&metadataPrefix=oai_dc'
          )

          return response.ok() &&
            (await response.text()).includes(description.title)
        },
        { timeout: 30000 }
      )
      .toBe(true)

    const list = await page.request.get(
      '/;oai?verb=ListRecords&metadataPrefix=oai_dc'
    )
    const listXml = await xmlValues(page, await list.text())
    expect(listXml.parserError).toBe(false)
    expect(listXml.listRecords).toBe(1)

    const badVerb = await page.request.get('/;oai?verb=Unsupported')
    const badVerbXml = await xmlValues(page, await badVerb.text())
    expect(badVerbXml.parserError).toBe(false)
    expect(badVerbXml.errorCode).toBe('badVerb')

    const unauthorizedApi = await anonymous.get('/api')
    const unauthorizedApiBody = await unauthorizedApi.text()

    expect(
      unauthorizedApi.status(),
      unauthorizedApiBody
    ).toBe(401)
    expect(JSON.parse(unauthorizedApiBody)).toEqual({
      id: 'not-authorized',
      message: 'Not authorized',
    })

    const api = await anonymous.get('/api', {
      headers: basicAuth(),
    })

    expect(api.status()).toBe(200)
    expect(api.headers()['content-type']).toContain('application/json')
    expect((await api.json()).version).toMatch(/^\d+\.\d+\.\d+$/)

    const apiDescription = await anonymous.get(
      `/api/informationobjects/${slug}`,
      { headers: basicAuth() }
    )
    const apiRecord = await apiDescription.json()

    expect(apiDescription.status()).toBe(200)
    expect(apiRecord.title).toBe(description.title)
    expect(apiRecord.publication_status).toBe('Published')

    const unauthorizedSword = await anonymous.get(
      '/sword/servicedocument'
    )

    expect(unauthorizedSword.status()).toBe(401)
    expect(unauthorizedSword.headers()['www-authenticate']).toBe(
      'Basic realm="Secure area"'
    )

    const sword = await anonymous.get('/sword/servicedocument', {
      headers: basicAuth(),
    })

    expect(sword.status()).toBe(200)
    expect(sword.headers()['content-type']).toContain('text/xml')
    const swordXml = await xmlValues(page, await sword.text())
    expect(swordXml.parserError).toBe(false)
    expect(swordXml.swordVersion).toBe('1.3')
    expect(swordXml.swordService).toBe(1)
  } finally {
    if (slug) {
      await deleteResource(page.request, slug, 'informationobject')
    } else {
      await deleteResource(
        page.request,
        description.slug,
        'informationobject'
      )
    }
    await anonymous.dispose()
    await savePlugins(page, originalPlugins)
  }
})
