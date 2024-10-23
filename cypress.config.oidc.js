const { defineConfig } = require('cypress')

module.exports = defineConfig({
  env: {
    adminEmail: 'demo@example.com',
    adminPassword: 'demo',
  },
  e2e: {
    retries: {'runMode': 2},
    baseUrl: 'http://localhost:63001',
    specPattern: 'cypress/e2e/oidc/**/*.cy.js',
    supportFile: 'cypress/support/e2e.js',
    chromeWebSecurity: false,
  },
})
