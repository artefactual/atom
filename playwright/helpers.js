const { expect } = require('@playwright/test')

async function login(page, email = 'demo@example.com', password = 'demo') {
  await page.goto('/user/login')
  const form = page.locator('#main-column form')

  await form.locator('#email').fill(email)
  await form.locator('#password').fill(password)
  await submitForm(form)
  await expect(page.locator('#user-menu')).not.toContainText('Log in')
}

async function submitForm(form) {
  await form
    .locator(
      'button[type="submit"], input[type="submit"], button:not([type])'
    )
    .last()
    .click()
}

function csrfToken(html) {
  const input = html
    .match(/<input\b[^>]*>/gi)
    ?.find((tag) => /\bid=["']csrf_token["']/i.test(tag))
  const value = input?.match(/\bvalue=["']([^"']+)["']/i)?.[1]

  if (!value) {
    throw new Error('Unable to find the CSRF token')
  }

  return value
}

async function getCsrfToken(request, url) {
  const response = await request.get(url)

  if (!response.ok()) {
    throw new Error(`Unable to load CSRF form at ${url}`)
  }

  return csrfToken(await response.text())
}

async function createDescription(request, body) {
  const form = await request.get('/informationobject/add')

  if (!form.ok()) {
    throw new Error('Unable to load the description form')
  }

  const html = await form.text()
  const parent = hiddenInputValue(html, 'parent')
  const response = await request.post('/informationobject/add', {
    form: {
      ...body,
      _csrf_token: csrfToken(html),
      parent,
    },
    maxRedirects: 0,
  })

  if (302 !== response.status()) {
    throw new Error(`Unable to create description: HTTP ${response.status()}`)
  }

  const location = response.headers().location

  if (!location) {
    throw new Error('Description creation did not return a location')
  }

  return new URL(location, 'http://localhost').pathname
    .split('/')
    .filter(Boolean)
    .pop()
}

async function deleteResource(request, slug, module) {
  const resource = await request.get(`/${slug}`)

  if (404 === resource.status()) {
    return
  }

  const url = `/${slug}/${module}/delete`
  const token = await getCsrfToken(request, url)
  const response = await request.delete(url, {
    form: {
      _csrf_token: token,
    },
  })

  if (response.status() >= 400) {
    throw new Error(`Unable to delete ${module} ${slug}`)
  }
}

async function deleteUser(page, username) {
  await page.goto(`/user/list?subquery=${encodeURIComponent(username)}`)
  const link = page
    .locator('#main-column table a')
    .filter({ hasText: new RegExp(`^${username}$`) })
    .first()

  if (0 === (await link.count())) {
    return
  }

  const profile = await link.getAttribute('href')
  const url = `${new URL(profile, page.url()).pathname}/user/delete`
  const token = await getCsrfToken(page.request, url)
  const response = await page.request.delete(url, {
    form: {
      _csrf_token: token,
    },
  })

  if (response.status() >= 400) {
    throw new Error(`Unable to delete user ${username}`)
  }
}

function hiddenInputValue(html, name) {
  const input = html
    .match(/<input\b[^>]*>/gi)
    ?.find((tag) => new RegExp(`\\bname=["']${name}["']`, 'i').test(tag))
  const value = input?.match(/\bvalue=["']([^"']*)["']/i)?.[1]

  if (undefined === value) {
    throw new Error(`Unable to find hidden input ${name}`)
  }

  return value
}

module.exports = {
  createDescription,
  deleteResource,
  deleteUser,
  login,
  submitForm,
}
