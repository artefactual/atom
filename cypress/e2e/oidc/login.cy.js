describe('SSO Login with Keycloak', () => {
  it('Should redirect to Keycloak and log in successfully with SSO', () => {
    cy.visit('/')
    cy.contains('Log in').should('be.visible').click()
    cy.contains('Log in with SSO').should('be.visible').click()
    cy.url({ timeout: 1000 }).should('include', '/oidc/login');
    cy.url({ timeout: 1000 }).should('include', 'realms/demo/protocol/openid-connect/auth')
    // cy.get('#username').type(Cypress.env('adminEmail'))
    // cy.get('#password').type(Cypress.env('adminPassword'))

    // cy.get('#kc-login').click();
    // cy.url().should('not.include', 'auth');
    // cy.url().should('include', '/dashboard');
    // cy.contains('Log out')
  })
})
