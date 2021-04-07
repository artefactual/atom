describe('Login', () => {
  it('Logs in through the user menu', () => {
    cy.visit('/')
    cy.contains('Log in').click()
    cy.get('input#email').type(Cypress.env('adminEmail'))
    cy.get('input#password').type(Cypress.env('adminPassword'))
    cy.get('#user-menu form').submit()

    cy.get('#user-menu').click()
    cy.contains('Log out')
  })

  it('Logs in through the login page', () => {
    cy.visit('/user/login')
    cy.get('#content input#email').type(Cypress.env('adminEmail'))
    cy.get('#content input#password').type(Cypress.env('adminPassword'))
    cy.get('#content form').submit()

    cy.get('#user-menu').click()
    cy.contains('Log out')
  })

  it('Fails loging in with the same error', () => {
    cy.visit('/')
    cy.contains('Log in').click()
    cy.get('input#email').type('unknown@user.com')
    cy.get('input#password').type(Cypress.env('adminPassword'))
    cy.get('#user-menu form').submit()

    cy.contains('Sorry, unrecognized email or password')

    cy.visit('/user/login')
    cy.get('#content input#email').type(Cypress.env('adminEmail'))
    cy.get('#content input#password').type('unknown_password')
    cy.get('#content form').submit()

    cy.contains('Sorry, unrecognized email or password')
  })

  it('Logs in without GUI', () => {
    cy.login()

    cy.visit('/')
    cy.get('#user-menu').click()
    cy.contains('Log out')
  })
})
