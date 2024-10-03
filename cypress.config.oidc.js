const { defineConfig } = require('cypress')

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost:63001',
    specPattern: 'cypress/e2e/oidc/**/*.cy.js',
    supportFile: 'cypress/support/e2e.js'
  },
})
