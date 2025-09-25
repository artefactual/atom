// OIDC authentication integration tests using Keycloak

// Increase default timeout to better tolerate CI/network delays
Cypress.config('defaultCommandTimeout', 30000);

const OIDC_USERNAME = Cypress.env('OIDC_USERNAME') || 'demo';
const OIDC_PASSWORD = Cypress.env('OIDC_PASSWORD') || 'demo';
const KEYCLOAK_ORIGIN = Cypress.env('KEYCLOAK_ORIGIN') || 'http://127.0.0.1:8080';

describe('OIDC SSO (Keycloak) - primary realm', () => {
  it('logs in via "Log in with SSO" and logs out', () => {
    cy.visit('/user/login');

    // Submit the main-column OIDC login form
    cy.get('form[action$="/oidc/login"]').last().within(() => {
      cy.get('button[type="submit"]')
        .invoke('text')
        .then((text) => cy.task('log', 'Clicking button: ' + text));
      cy.get('button[type="submit"]').click();
    });

    // Complete login inside Keycloak
    cy.origin(KEYCLOAK_ORIGIN, { args: { username: OIDC_USERNAME, password: OIDC_PASSWORD } }, ({ username, password }) => {
      cy.location('origin', { timeout: 30000 }).should('eq', 'http://127.0.0.1:8080');

      cy.task('log', `Using OIDC credentials: ${username}/*****`);
      cy.url().should('include', '/realms/demo/protocol/openid-connect/auth');
      cy.get('#kc-page-title', { timeout: 30000 }).should('exist');
      cy.get('#username').clear().type(username);
      cy.get('#password').clear().type(password);
      cy.get('#kc-login').click();
    });

    // Verify user menu shows username once redirected back to AtoM
    cy.get('#user-menu', { timeout: 30000 }).then(($menu) => {
      cy.task('log', 'User menu after login: ' + $menu.text());
    }).should('contain', OIDC_USERNAME);

    // Logout
    cy.get('#user-menu').click();
    cy.contains('a.dropdown-item', 'Logout', { matchCase: false }).click();

    // Verify login button is visible again
    cy.get('#user-menu', { timeout: 30000 }).then(($menu) => {
      cy.task('log', 'User menu after logout: ' + $menu.text());
    }).should('contain', 'Log in');
  });
});



// describe('OIDC SSO (Keycloak) - secondary realm', () => {
//   it('selects secondary provider via query param and logs in', () => {
//     // This uses provider_query_param_name=provider
//     cy.visit('/user/login?provider=secondary')
//     // Submit the first OIDC login form directly (handles duplicate forms)
//     // Click the second "Log in with SSO" button on the page
//     cy.contains('button', 'Log in with SSO').last().click();
//     cy.location('origin', { timeout: 30000 }).should('eq', KEYCLOAK_ORIGIN)

//     // Complete login on Keycloak (secondary realm)
//     cy.origin(KEYCLOAK_ORIGIN, () => {
//       const user = Cypress.env('OIDC_SECONDARY_USERNAME') || 'supportdefault'
//       const pass = Cypress.env('OIDC_SECONDARY_PASSWORD') || 'support'
//       cy.get('#kc-page-title', { timeout: 30000 }).should('exist')
//       cy.get('#username', { timeout: 30000 }).should('be.visible').clear().type(user)
//       cy.get('#password').clear().type(pass)
//       cy.get('#kc-login').click()
//     })

//     // Back on AtoM, user menu should show the username from secondary realm
//     const expectedUser = Cypress.env('OIDC_SECONDARY_USERNAME') || 'supportdefault'
//     cy.get('#user-menu', { timeout: 30000 }).should('be.visible').and('contain', expectedUser)
//   })
// })
