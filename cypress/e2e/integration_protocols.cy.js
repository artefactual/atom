describe('Integration protocols', () => {
  const requiredPlugins = ['arOaiPlugin', 'arRestApiPlugin', 'qtSwordPlugin']
  let enabledPlugins
  let descriptionSlug

  const savePlugins = (plugins) => {
    cy.visit('/sfPluginAdminPlugin/plugins')
    cy.get('input[name="enabled[]"]:not(:disabled)').each(($input) => {
      const action = plugins.includes($input.val()) ? 'check' : 'uncheck'

      cy.wrap($input)[action]()
    })
    cy.get('#main-column form').submit()
    cy.location('pathname').should('equal', '/sfPluginAdminPlugin/plugins')
    cy.visit('/sfPluginAdminPlugin/plugins')
    cy.get('input[name="enabled[]"]:not(:disabled)').each(($input) => {
      expect($input.prop('checked')).to.equal(plugins.includes($input.val()))
    })
  }

  const parseXml = (response) => {
    expect(response.status).to.equal(200)
    expect(response.headers['content-type']).to.contain('text/xml')

    const document = new DOMParser().parseFromString(
      response.body,
      'application/xml'
    )

    expect(document.querySelector('parsererror')).to.not.exist

    return document
  }

  before(() => {
    cy.clearCookies()
    cy.login()
    cy.visit('/sfPluginAdminPlugin/plugins')
    cy.get('input[name="enabled[]"]:not(:disabled)').then(($inputs) => {
      enabledPlugins = [...$inputs]
        .filter((input) => input.checked)
        .map((input) => input.value)

      savePlugins([...new Set([...enabledPlugins, ...requiredPlugins])])
    })

    cy.request('/informationobject/add').then((response) => {
      const levelOfDescription = [
        ...Cypress.$(response.body).find('#levelOfDescription option'),
      ].find((option) => option.textContent.trim() === 'Fonds').value

      cy.createDescription({
        identifier: 'cypress-protocol-compatibility',
        title: 'Cypress protocol compatibility',
        levelOfDescription,
        extentAndMedium: '1 digital record',
      }).then((slug) => {
        descriptionSlug = slug
        cy.visit(`/${slug}/informationobject/updatePublicationStatus`)
        cy.get('#publicationStatus').select('160')
        cy.get('[data-cy=update-publication-status-form]').submit()
        cy.location('pathname').should('equal', `/${slug}`)
        cy.get('.alert-danger').should('not.exist')
      })
    })
  })

  after(() => {
    cy.clearCookies()
    cy.login()

    if (descriptionSlug) {
      cy.deleteDescription(descriptionSlug)
    }

    if (enabledPlugins) {
      savePlugins(enabledPlugins)
    }
  })

  it('Serves valid OAI-PMH responses', () => {
    cy.request({
      url: '/;oai',
      qs: { verb: 'Identify' },
    }).then((response) => {
      const document = parseXml(response)

      expect(
        document.getElementsByTagNameNS('*', 'protocolVersion')[0].textContent
      ).to.equal('2.0')
      expect(
        document.getElementsByTagNameNS('*', 'repositoryName')[0].textContent
      ).to.not.be.empty
    })

    cy.request({
      url: '/;oai',
      qs: {
        verb: 'ListRecords',
        metadataPrefix: 'oai_dc',
      },
    }).then((response) => {
      const document = parseXml(response)

      expect(
        document.getElementsByTagNameNS('*', 'ListRecords')
      ).to.have.length(1)
      expect(response.body).to.contain('Cypress protocol compatibility')
    })

    cy.request({
      url: '/;oai',
      qs: { verb: 'Unsupported' },
    }).then((response) => {
      const document = parseXml(response)
      const error = document.getElementsByTagNameNS('*', 'error')[0]

      expect(error.getAttribute('code')).to.equal('badVerb')
    })
  })

  it('Authenticates and serves the REST API', () => {
    cy.request({
      url: '/api',
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status).to.equal(401)
      expect(response.body).to.deep.equal({
        id: 'not-authorized',
        message: 'Not authorized',
      })
    })

    const request = {
      auth: {
        username: Cypress.env('adminEmail'),
        password: Cypress.env('adminPassword'),
      },
    }

    cy.request({ ...request, url: '/api' }).then((response) => {
      expect(response.status).to.equal(200)
      expect(response.headers['content-type']).to.contain('application/json')
      expect(response.body.version).to.match(/^\d+\.\d+\.\d+$/)
    })

    cy.request({
      ...request,
      url: `/api/informationobjects/${descriptionSlug}`,
    }).then((response) => {
      expect(response.status).to.equal(200)
      expect(response.body.title).to.equal('Cypress protocol compatibility')
      expect(response.body.publication_status).to.equal('Published')
    })
  })

  it('Authenticates and serves the SWORD document', () => {
    cy.request({
      url: '/sword/servicedocument',
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status).to.equal(401)
      expect(response.headers['www-authenticate']).to.equal(
        'Basic realm="Secure area"'
      )
    })

    cy.request({
      url: '/sword/servicedocument',
      auth: {
        username: Cypress.env('adminEmail'),
        password: Cypress.env('adminPassword'),
      },
    }).then((response) => {
      const document = parseXml(response)

      expect(
        document.getElementsByTagNameNS(
          'http://purl.org/net/sword/',
          'version'
        )[0].textContent
      ).to.equal('1.3')
      expect(
        document.getElementsByTagNameNS('http://www.w3.org/2007/app', 'service')
      ).to.have.length(1)
    })
  })
})
