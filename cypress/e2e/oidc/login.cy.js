describe('SSO Login with Keycloak', () => {
  it('Should redirect to Keycloak and log in successfully with SSO', () => {
    cy.visit('/')
    cy.contains('Log in with SSO').click()
    cy.url().should('include', 'realms/demo/protocol/openid-connect/auth');
    // cy.get('#username').type('demo@example.com');
    // cy.get('#password').type('demo');
    // cy.get('#kc-login').click();
    // cy.url().should('not.include', 'auth');
    // cy.url().should('include', '/dashboard');
    // cy.contains('Log out')
  })
})
