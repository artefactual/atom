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
  cy.getHiddenInputs(url, form).then(inputs =>
    inputs.filter('#csrf_token').val()
  )
)

Cypress.Commands.add('login', () =>
  cy.getCsrfToken('/user/login', '#content form').then(token =>
    cy.request({
      method: 'POST',
      url: '/user/login',
      followRedirect: false,
      form: true,
      body: {
        email: Cypress.env('adminEmail'),
        password: Cypress.env('adminPassword'),
        _csrf_token: token,
      }
    })
  )
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
