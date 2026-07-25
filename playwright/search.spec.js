const { test, expect } = require('@playwright/test')
const { createDescription, deleteResource } = require('./helpers')

const stopwordTitles = [
  'playwrightstopword department of medical imaging',
  'playwrightstopword department medical imaging',
  'playwrightstopword department of imaging',
  'playwrightstopword medical imaging department',
  'playwrightstopword department of medical',
]

function expectedSlug(title) {
  return title.replaceAll(' ', '-')
}

async function deleteDescriptions(request, titles) {
  for (const title of titles) {
    await deleteResource(
      request,
      expectedSlug(title),
      'informationobject'
    )
  }
}

async function search(page, query, expected) {
  const input = page.locator('#search-box-input')
  await input.fill(query)
  await input.press('Enter')
  await expect(page.locator('.multiline-header')).toContainText(expected)
}

test('[surface:workflow-search-stopwords] finds phrases containing stop words', async ({
  page,
}) => {
  test.setTimeout(60000)
  await deleteDescriptions(page.request, stopwordTitles)

  try {
    for (const title of stopwordTitles) {
      await createDescription(page.request, { title })
    }

    await page.goto(
      '/informationobject/browse?query=playwrightstopword&topLod=0'
    )
    await expect(page.locator('.multiline-header')).toContainText(
      'Showing 5 results'
    )

    await search(
      page,
      'playwrightstopword department of imaging',
      'Showing 4 results'
    )
    await search(
      page,
      'playwrightstopword department of medical',
      'Showing 4 results'
    )
    await search(
      page,
      'playwrightstopword department of medical imaging',
      'Showing 3 results'
    )
    await search(
      page,
      'playwrightstopword "department of medical"',
      'Showing 2 result'
    )
    await search(
      page,
      'playwrightstopword "department of medical imaging"',
      'Showing 1 result'
    )
  } finally {
    await deleteDescriptions(page.request, stopwordTitles)
  }
})

test('[surface:workflow-search-all-stopwords] ignores an all-stopword query', async ({
  page,
}) => {
  const title = 'to be or not to be'
  const slug = expectedSlug(title)
  await deleteResource(page.request, slug, 'informationobject')

  try {
    await createDescription(page.request, { title })
    await page.goto('/informationobject/browse?query=to+be+or+not+to+be&topLod=0')
    await expect(page.locator('.multiline-header')).toContainText(
      'No results found'
    )

    await search(page, '"to be or not to be"', 'No results found')
  } finally {
    await deleteResource(page.request, slug, 'informationobject')
  }
})
