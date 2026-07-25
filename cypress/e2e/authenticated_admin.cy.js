describe('Authenticated administrator workflows', () => {
  const actor = {
    slug: 'cypress-authenticated-authority',
    name: 'Cypress authenticated authority',
    updatedName: 'Cypress authenticated authority updated',
  }
  const repository = {
    slug: 'cypress-authenticated-repository',
    identifier: 'CY-AUTH-REPO',
    name: 'Cypress authenticated repository',
    updatedName: 'Cypress authenticated repository updated',
  }
  const accession = {
    slug: 'cypress-accession-001',
    identifier: 'CYPRESS-ACCESSION-001',
    source: 'Cypress authenticated transfer',
    location: 'Cypress shelf A',
    updatedLocation: 'Cypress shelf B',
  }

  const adminPages = [
    '/user/list',
    '/aclGroup/list',
    '/staticpage/list',
    '/menu/list',
    '/sfPluginAdminPlugin/plugins',
    '/sfPluginAdminPlugin/themes',
    '/settings/global',
    '/search/descriptionUpdates',
    '/settings/visibleElements',
  ]
  const managementPages = [
    '/accession/browse',
    '/donor/browse',
    '/jobs/browse',
    '/physicalobject/browse',
    '/rightsholder/browse',
    {
      path: '/taxonomy/browse',
      expectedPath: '/taxonomy/list',
    },
  ]
  const settingsPages = [
    '/settings/clipboard',
    '/settings/csvValidator',
    '/settings/pageElements',
    '/settings/template',
    '/settings/diacritics',
    '/settings/digitalObjectDerivatives',
    '/settings/dipUpload',
    '/settings/findingAid',
    '/settings/global',
    '/settings/header',
    '/settings/language',
    '/settings/identifier',
    '/settings/inventory',
    '/settings/markdown',
    '/settings/permissions',
    '/settings/privacyNotification',
    '/settings/security',
    '/settings/siteInformation',
    '/settings/treeview',
    '/settings/uploads',
    '/settings/interfaceLabel',
    '/settings/analytics',
  ]

  let actorSlug
  let repositorySlug
  let accessionSlug
  let originalHitsPerPage

  const loginAsAdmin = () => {
    cy.clearCookies()
    cy.login()
  }

  const assertPagesLoad = pages => {
    pages.forEach(page => {
      const path = 'string' === typeof page ? page : page.path
      const expectedPath =
        'string' === typeof page ? page : page.expectedPath

      cy.log(`Opening ${path}`)
      cy.visit(path)
      cy.location('pathname').should('equal', expectedPath)
      cy.get('#main-column h1').should('be.visible').and('not.be.empty')
      cy.get('#main-column .alert-danger').should('not.exist')
    })
  }

  const saveHitsPerPage = value => {
    cy.visit('/settings/global')
    cy.contains('button', 'Search and browse').click()
    cy.get('#global_settings_hits_per_page').clear().type(value)
    cy.get('#main-column form').submit()
    cy.contains('Global settings saved.')

    return cy
      .get('#global_settings_hits_per_page')
      .should('have.value', value)
  }

  const removeTestRecords = () => {
    loginAsAdmin()
    cy.deleteResource(actorSlug || actor.slug, 'actor')
    cy.deleteResource(repositorySlug || repository.slug, 'repository')
    cy.deleteResource(accessionSlug || accession.slug, 'accession')
    actorSlug = undefined
    repositorySlug = undefined
    accessionSlug = undefined

    if (originalHitsPerPage) {
      saveHitsPerPage(originalHitsPerPage).then(() => {
        originalHitsPerPage = undefined
      })
    }
  }

  before(removeTestRecords)
  beforeEach(loginAsAdmin)
  afterEach(removeTestRecords)

  it('Loads the administrator menu destinations', () => {
    assertPagesLoad(adminPages)
  })

  it('Loads the management menu destinations', () => {
    assertPagesLoad(managementPages)
  })

  it('Loads the settings destinations', () => {
    assertPagesLoad(settingsPages)
  })

  it('Creates, edits, and deletes an authority record', () => {
    cy.visit('/actor/add')
    cy.contains('button', 'Identity area').click()
    cy.get('#entityType').select('Person')
    cy.get('#authorizedFormOfName').type(actor.name, {force: true})
    cy.get('#editForm').submit()

    cy.location('pathname').then(path => {
      actorSlug = path.split('/').filter(Boolean).pop()
      expect(actorSlug).to.equal(actor.slug)
    })
    cy.get('#main-column').should('contain', actor.name)

    cy.get('.actions').contains('a', 'Edit').click()
    cy.contains('button', 'Identity area').click()
    cy.get('#authorizedFormOfName')
      .clear({force: true})
      .type(actor.updatedName, {force: true})
    cy.get('#editForm').submit()
    cy.get('#main-column').should('contain', actor.updatedName)

    cy.get('.actions').contains('a', 'Delete').click()
    cy.get('#main-column form').submit()
    cy.request({url: `/${actor.slug}`, failOnStatusCode: false})
      .its('status')
      .should('equal', 404)
    actorSlug = undefined
  })

  it('Creates, edits, and deletes an archival institution', () => {
    cy.visit('/repository/add')
    cy.contains('button', 'Identity area').click()
    cy.get('#identifier').type(repository.identifier)
    cy.get('#authorizedFormOfName').type(repository.name, {force: true})
    cy.get('#main-column form').submit()

    cy.location('pathname').then(path => {
      repositorySlug = path.split('/').filter(Boolean).pop()
      expect(repositorySlug).to.equal(repository.slug)
    })
    cy.get('#main-column').should('contain', repository.name)

    cy.get('.actions').contains('a', 'Edit').click()
    cy.contains('button', 'Identity area').click()
    cy.get('#authorizedFormOfName')
      .clear({force: true})
      .type(repository.updatedName, {force: true})
    cy.get('#editForm').submit()
    cy.get('#main-column').should('contain', repository.updatedName)

    cy.get('.actions').contains('a', 'Delete').click()
    cy.get('#main-column form').submit()
    cy.request({url: `/${repository.slug}`, failOnStatusCode: false})
      .its('status')
      .should('equal', 404)
    repositorySlug = undefined
  })

  it('Creates, edits, and deletes an accession', () => {
    cy.visit('/accession/add')
    cy.contains('button', 'Basic info').click()
    cy.get('#identifier').clear().type(accession.identifier)
    cy.get('#sourceOfAcquisition').type(accession.source)
    cy.get('#locationInformation').type(accession.location)
    cy.get('#editForm').submit()

    cy.location('pathname').then(path => {
      accessionSlug = path.split('/').filter(Boolean).pop()
      expect(accessionSlug).to.equal(accession.slug)
    })
    cy.get('#main-column').should('contain', accession.identifier)

    cy.get('.actions').contains('a', 'Edit').click()
    cy.contains('button', 'Basic info').click()
    cy.get('#locationInformation')
      .clear()
      .type(accession.updatedLocation)
    cy.get('#editForm').submit()
    cy.get('#main-column').should('contain', accession.updatedLocation)

    cy.get('.actions').contains('a', 'Delete').click()
    cy.get('#main-column form').submit()
    cy.request({url: `/${accession.slug}`, failOnStatusCode: false})
      .its('status')
      .should('equal', 404)
    accessionSlug = undefined
  })

  it('Saves and restores a global setting', () => {
    cy.visit('/settings/global')
    cy.get('#global_settings_hits_per_page')
      .invoke('val')
      .then(value => {
        originalHitsPerPage = value
        const updatedValue = '25' === value ? '30' : '25'

        saveHitsPerPage(updatedValue)
        cy.reload()
        cy.get('#global_settings_hits_per_page')
          .should('have.value', updatedValue)
        saveHitsPerPage(originalHitsPerPage).then(() => {
          originalHitsPerPage = undefined
        })
      })
  })
})
