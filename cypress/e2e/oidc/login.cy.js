describe('Login', () => {
  it('Logs in through the user menu', () => {
    cy.visit('/')
    cy.contains('Log in').click()
    // cy.get('#csrf_token').should('exist')
    // cy.get('input#email').type(Cypress.env('adminEmail'))
    // cy.get('input#password').type(Cypress.env('adminPassword'))
    // cy.get('#user-menu + .dropdown-menu form').submit()

    // cy.get('#user-menu').click()
    // cy.contains('Log out')
  })
})
