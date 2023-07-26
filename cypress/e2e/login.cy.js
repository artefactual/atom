describe('Login', () => {
  it('Logs in through the user menu', () => {
    cy.visit('/')
    cy.contains('Log in').click()
    cy.get('#csrf_token').should('exist')
    cy.get('input#email').type(Cypress.env('adminEmail'))
    cy.get('input#password').type(Cypress.env('adminPassword'))
    cy.get('#user-menu + .dropdown-menu form').submit()

    cy.get('#user-menu').click()
    cy.contains('Log out')
  })

  it('Logs in through the login page', () => {
    cy.visit('/user/login')
    cy.get('#csrf_token').should('exist')
    cy.get('#main-column input#email').type(Cypress.env('adminEmail'))
    cy.get('#main-column input#password').type(Cypress.env('adminPassword'))
    cy.get('#main-column form').submit()

    cy.get('#user-menu').click()
    cy.contains('Log out')
  })

  it('Fails log in with the same error for both login forms', () => {
    cy.visit('/')
    cy.contains('Log in').click()
    cy.get('#csrf_token').should('exist')
    cy.get('input#email').type('unknown@user.com')
    cy.get('input#password').type(Cypress.env('adminPassword'))
    cy.get('#user-menu + .dropdown-menu form').submit()

    cy.contains('Sorry, unrecognized email or password')

    cy.visit('/user/login')
    cy.get('#main-column input#email').type(Cypress.env('adminEmail'))
    cy.get('#main-column input#password').type('unknown_password')
    cy.get('#main-column form').submit()

    cy.contains('Sorry, unrecognized email or password')
  })

  it('Logs in without GUI', () => {
    cy.login()

    cy.visit('/')
    cy.get('#user-menu').click()
    cy.contains('Log out')
  })
})
