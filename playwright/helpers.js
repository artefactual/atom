const { expect } = require('@playwright/test')

async function login(page, email = 'demo@example.com', password = 'demo') {
  await page.goto('/user/login')
  const form = page.locator('#main-column form')

  await form.locator('#email').fill(email)
  await form.locator('#password').fill(password)
  await submitForm(form)
  await expect(page.locator('#user-menu')).not.toContainText('Log in')
}

async function loginViaRequest(
  request,
  email = 'demo@example.com',
  password = 'demo'
) {
  const response = await request.post('/user/login', {
    form: {
      email,
      password,
      _csrf_token: await getCsrfToken(request, '/user/login'),
    },
    maxRedirects: 0,
  })

  if (302 !== response.status()) {
    throw new Error(`Unable to log in: HTTP ${response.status()}`)
  }
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

async function createUser(page, user) {
  await page.goto('/user/add')
  await page.locator('#username').fill(user.username)
  await page.locator('#email').fill(user.email)
  await page.locator('#password').fill(user.password)
  await page.locator('#confirmPassword').fill(user.password)

  if (false === user.active) {
    await page.locator('#active').uncheck()
  }

  if (user.group) {
    await page.getByRole('button', { name: 'Access control' }).click()
    await page.locator('#groups').fill(user.group)
    await page
      .locator('.yui-ac-content li')
      .filter({ hasText: new RegExp(`^${user.group}$`) })
      .click()
  }

  await submitForm(page.locator('#main-column form'))
  await expect(page.locator('#main-column h1')).toContainText(
    `User ${user.username}`
  )

  return new URL(page.url()).pathname.split('/').filter(Boolean).pop()
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
  let link

  for (const filter of ['onlyActive', 'onlyInactive']) {
    await page.goto(
      `/user/list?filter=${filter}&subquery=${encodeURIComponent(username)}`
    )
    const candidate = page
      .locator('#main-column table a')
      .filter({ hasText: new RegExp(`^${username}$`) })
      .first()

    if (await candidate.count()) {
      link = candidate

      break
    }
  }

  if (!link) {
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

async function publishDescription(page, slug) {
  await page.goto(`/${slug}/informationobject/updatePublicationStatus`)
  await page.locator('#publicationStatus').selectOption('160')
  await submitForm(
    page.locator('[data-cy="update-publication-status-form"]')
  )
  await expect(page).toHaveURL(new RegExp(`/${slug}$`))
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
  createUser,
  deleteResource,
  deleteUser,
  login,
  loginViaRequest,
  publishDescription,
  submitForm,
}
