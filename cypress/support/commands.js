// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })

import 'cypress-file-upload';
import 'cypress-wait-until';

Cypress.Commands.add('getHiddenInputs', (url, form) =>
  cy.request(url).its('body').then(body =>
    Cypress.$(body).find(form + ' input[type=hidden]')
  )
)

Cypress.Commands.add('getCsrfToken', (url, form) =>
  cy.getHiddenInputs(url, form).then(inputs => {
    const token = inputs.filter('#csrf_token')

    if (0 === token.length) {
      throw 'CSRF seems to be disabled (no #csrf_token hidden input found).' +
      ' See: https://symfony.com/legacy/doc/reference/1_4/en/04-Settings#chapter_04_sub_csrf_secret'
    }

    return token.val()
  })
)

Cypress.Commands.add('login', (
  email = Cypress.env('adminEmail'),
  password = Cypress.env('adminPassword')
) =>
  cy.getCsrfToken('/user/login', '#main-column form').then(token =>
    cy.request({
      method: 'POST',
      url: '/user/login',
      followRedirect: false,
      form: true,
      body: {
        email,
        password,
        _csrf_token: token,
      }
    })
  )
)

Cypress.Commands.add('createUser', user => {
  cy.visit('/user/add')
  cy.get('#username').type(user.username)
  cy.get('#email').type(user.email)
  cy.get('#password').type(user.password)
  cy.get('#confirmPassword').type(user.password)

  if (false === user.active) {
    cy.get('#active').uncheck()
  }

  if (user.group) {
    cy.contains('button', 'Access control').click()
    cy.get('#groups').type(user.group)
    cy.contains(
      '.yui-ac-content li',
      new RegExp(`^${Cypress._.escapeRegExp(user.group)}$`)
    ).click()
    cy.get('input[name="groups[]"]').should('exist')
  }

  cy.get('#main-column form').submit()
  cy.get('#main-column h1').should('contain', `User ${user.username}`)

  return cy.location('pathname').then(path =>
    path.split('/').filter(Boolean).pop()
  )
})

Cypress.Commands.add('deleteUser', username =>
  cy.request(`/user/list?subquery=${encodeURIComponent(username)}`)
  .its('body')
  .then(body => {
    const link = Cypress.$(body)
      .find('#main-column table a')
      .filter((_, element) =>
        Cypress.$(element).text().trim() === username
      )
      .first()
      .attr('href')

    if (!link) {
      return
    }

    return cy.getCsrfToken(`${link}/user/delete`, '#main-column form')
    .then(token =>
      cy.request({
        method: 'DELETE',
        url: `${link}/user/delete`,
        followRedirect: false,
        form: true,
        body: {_csrf_token: token},
      })
    )
  })
)

Cypress.Commands.add('createDescription', body =>
  cy.getHiddenInputs('/informationobject/add', '#main-column form')
  .then(inputs => {
    body._csrf_token = inputs.filter('#csrf_token').val()
    if (!body.parent) {
      body.parent = inputs.filter('#parent').val()
    }
    cy.request({
      method: 'POST',
      url: '/informationobject/add',
      followRedirect: false,
      form: true,
      body: body,
    })
  })
  .its('redirectedToUrl').then(url => url.split('/').pop())
)

Cypress.Commands.add('deleteDescription', slug =>
  cy.getCsrfToken('/' + slug + '/informationobject/delete', 'form')
  .then(token =>
    cy.request({
      method: 'DELETE',
      url: '/' + slug + '/informationobject/delete',
      followRedirect: false,
      form: true,
      body: {_csrf_token: token}
    })
  )
)
