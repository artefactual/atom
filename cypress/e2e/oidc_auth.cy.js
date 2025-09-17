// OIDC authentication integration tests using Keycloak

// Increase default timeout to better tolerate CI/network delays
Cypress.config('defaultCommandTimeout', 60000)

const OIDC_USERNAME = Cypress.env('OIDC_USERNAME') || 'demo'
const OIDC_PASSWORD = Cypress.env('OIDC_PASSWORD') || 'demo'

describe('OIDC SSO (Keycloak) - primary realm', () => {
  it('logs in via "Log in with SSO" and logs out', () => {
    // Go to AtoM login page and trigger OIDC login
    cy.visit('/user/login')
    cy.contains('button', 'Log in with SSO', { timeout: 60000 }).click()

    // Complete login on Keycloak (demo realm)
    cy.origin('http://127.0.0.1:8080', () => {
      // Ensure we are on the Keycloak page
      cy.get('#kc-page-title', { timeout: 60000 }).should('exist')
      cy.get('#username', { timeout: 60000 }).should('be.visible').clear().type(Cypress.env('OIDC_USERNAME') || 'demo')
      cy.get('#password').clear().type(Cypress.env('OIDC_PASSWORD') || 'demo')
      cy.get('#kc-login').click()
    })

    // Back on AtoM, user menu should show the username
    cy.get('#user-menu', { timeout: 60000 }).should('be.visible').and('contain', OIDC_USERNAME)

    // Logout via user menu -> OIDC logout
    cy.get('#user-menu').click()
    cy.contains('a.dropdown-item', 'Logout', { matchCase: false }).click()

    // After logout, the login button should be visible again
    cy.get('#user-menu', { timeout: 60000 }).should('contain', 'Log in')
  })
})

describe('OIDC SSO (Keycloak) - secondary realm', () => {
  it('selects secondary provider via query param and logs in', () => {
    // This uses provider_query_param_name=provider
    cy.visit('/user/login?provider=secondary')
    cy.contains('button', 'Log in with SSO', { timeout: 60000 }).click()

    // Complete login on Keycloak (secondary realm)
    cy.origin('http://127.0.0.1:8080', () => {
      const user = Cypress.env('OIDC_SECONDARY_USERNAME') || 'supportdefault'
      const pass = Cypress.env('OIDC_SECONDARY_PASSWORD') || 'support'
      cy.get('#kc-page-title', { timeout: 60000 }).should('exist')
      cy.get('#username', { timeout: 60000 }).should('be.visible').clear().type(user)
      cy.get('#password').clear().type(pass)
      cy.get('#kc-login').click()
    })

    // Back on AtoM, user menu should show the username from secondary realm
    const expectedUser = Cypress.env('OIDC_SECONDARY_USERNAME') || 'supportdefault'
    cy.get('#user-menu', { timeout: 60000 }).should('be.visible').and('contain', expectedUser)
  })
})
