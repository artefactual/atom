// OIDC authentication integration tests using Keycloak

// Increase default timeout to better tolerate CI/network delays
Cypress.config('defaultCommandTimeout', 30000)

const OIDC_USERNAME = Cypress.env('OIDC_USERNAME') || 'demo'
const OIDC_PASSWORD = Cypress.env('OIDC_PASSWORD') || 'demo'
const KEYCLOAK_ORIGIN = Cypress.env('KEYCLOAK_ORIGIN') || 'http://127.0.0.1:8080'

describe('OIDC SSO (Keycloak) - primary realm', () => {
  it('logs in via "Log in with SSO" and logs out', () => {
    // Go to AtoM login page and trigger OIDC login

    cy.intercept('POST', '/oidc/login').as('oidcLogin');

    cy.visit('/user/login')
    // Submit the first OIDC login form directly (handles duplicate forms)
    // Click the second "Log in with SSO" button on the page
    // cy.contains('button', 'Log in with SSO').last().click();
    cy.contains('button', 'Log in with SSO').last().click();

    // Wait for the POST to happen
    cy.wait('@oidcLogin').then((interception) => {
      // Print out request details
      cy.log('Intercepted OIDC login POST');

      // Print to GitHub Actions log
      // requestBody will contain form fields like "next"
      // response?.statusCode will show 200/302 etc.
      console.log('OIDC POST request body:', interception.request?.body);
      console.log('OIDC POST response status:', interception.response?.statusCode);

      // Assert form contained the "next" parameter
      expect(interception.request?.body).to.have.property('next');

      // Assert redirect response
      expect(interception.response?.statusCode).to.be.oneOf([200, 302]);
    });

    // Wait for top-level navigation to Keycloak before running cross-origin commands
    cy.location('origin', { timeout: 30000 }).should('eq', KEYCLOAK_ORIGIN)

    // Complete login on Keycloak (demo realm)
    cy.origin(KEYCLOAK_ORIGIN, () => {
      // Ensure we are on the Keycloak page
      cy.get('#kc-page-title', { timeout: 30000 }).should('exist')
      cy.get('#username', { timeout: 30000 }).should('be.visible').clear().type(Cypress.env('OIDC_USERNAME') || 'demo')
      cy.get('#password').clear().type(Cypress.env('OIDC_PASSWORD') || 'demo')
      cy.get('#kc-login').click()
    })

    // Back on AtoM, user menu should show the username
    cy.get('#user-menu', { timeout: 30000 }).should('be.visible').and('contain', OIDC_USERNAME)

    // Logout via user menu -> OIDC logout
    cy.get('#user-menu').click()
    cy.contains('a.dropdown-item', 'Logout', { matchCase: false }).click()

    // After logout, the login button should be visible again
    cy.get('#user-menu', { timeout: 30000 }).should('contain', 'Log in')
  })
})

describe('OIDC SSO (Keycloak) - secondary realm', () => {
  it('selects secondary provider via query param and logs in', () => {
    // This uses provider_query_param_name=provider
    cy.visit('/user/login?provider=secondary')
    // Submit the first OIDC login form directly (handles duplicate forms)
    // Click the second "Log in with SSO" button on the page
    cy.contains('button', 'Log in with SSO').last().click();
    cy.location('origin', { timeout: 30000 }).should('eq', KEYCLOAK_ORIGIN)

    // Complete login on Keycloak (secondary realm)
    cy.origin(KEYCLOAK_ORIGIN, () => {
      const user = Cypress.env('OIDC_SECONDARY_USERNAME') || 'supportdefault'
      const pass = Cypress.env('OIDC_SECONDARY_PASSWORD') || 'support'
      cy.get('#kc-page-title', { timeout: 30000 }).should('exist')
      cy.get('#username', { timeout: 30000 }).should('be.visible').clear().type(user)
      cy.get('#password').clear().type(pass)
      cy.get('#kc-login').click()
    })

    // Back on AtoM, user menu should show the username from secondary realm
    const expectedUser = Cypress.env('OIDC_SECONDARY_USERNAME') || 'supportdefault'
    cy.get('#user-menu', { timeout: 30000 }).should('be.visible').and('contain', expectedUser)
  })
})
