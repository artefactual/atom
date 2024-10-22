const { defineConfig } = require('cypress')

module.exports = defineConfig({
  env: {
    adminEmail: 'demo@example.com',
    adminPassword: 'demo',
  },
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config)
    },
    retries: {'runMode': 2},
    baseUrl: 'http://localhost:63001',
    specPattern: 'cypress/e2e/default/**/*.cy.js',
    supportFile: 'cypress/support/e2e.js'
  },
})
