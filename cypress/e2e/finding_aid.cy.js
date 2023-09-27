describe(
  'Test setting to enabled/disable finding aid upload and generation', () => {
    const testUrl = '/cypress-test-finding-aid-description-1'

    before('Ensure test description exists', () => {
      cy.login()

      cy.request({
        url: testUrl,
        failOnStatusCode: false,
      }).then((resp) => {
        // Create a test description, if one doesn't exist already
        if ('404' == resp.status) {
          cy.createDescription({
            identifier: 'cy-finding-aid-test-1',
            title: 'Cypress test finding aid description 1'
          }).then(slug => {
            // Publish it
            cy.visit('/' + slug + '/informationobject/updatePublicationStatus')
            cy.get('#publicationStatus').select('160')
            cy.get('[data-cy=update-publication-status-form]').submit()
          })
        }
      })
    })


    beforeEach(() => {
      // Use cy.session to preserve all cookies and validate them
      cy.session('unique_identifier', cy.login, {
        validate () {
          cy.getCookies().should('exist')
          },
        })
    });

    it('Disables finding aids', () => {
      cy.visit('settings/findingAid')

      cy.get('#finding_aid_finding_aids_enabled_1').should('exist')
      cy.get('#finding_aid_finding_aids_enabled_0').should('exist')

      // Disable finding aids
      cy.get('#finding_aid_finding_aids_enabled_0').check()
      cy.get('[data-cy=settings-finding-aid-form]').submit()

      cy.contains('Finding aid settings saved.')
      cy.get('#finding_aid_finding_aids_enabled_0').should('be.checked')

      // Test that finding aid search options are absent
      cy.visit('informationobject/browse')
      cy.get('[data-cy=advanced-search-toggle]').click()
      cy.get('select[name=sf0]').should(($sel) => {
        expect($sel).not.descendants('option[value=findingAidTranscript]')
        expect($sel).not.descendants('option[value=allExceptFindingAidTranscript]')
      })
      cy.get('#findingAidStatus').should('not.exist')

      // Test that description finding aid links are absent
      cy.visit(testUrl)
      cy.get('[data-cy=generate-finding-aid]').should('not.exist')
      cy.get('[data-cy=upload-finding-aid]').should('not.exist')
    })

    it('Enables finding aids', () => {
      cy.visit('settings/findingAid')

      cy.get('#finding_aid_finding_aids_enabled_1').should('exist')
      cy.get('#finding_aid_finding_aids_enabled_0').should('exist')

      // Enable finding aids
      cy.get('#finding_aid_finding_aids_enabled_1').check()
      cy.get('[data-cy=settings-finding-aid-form]').submit()

      cy.contains('Finding aid settings saved.')
      cy.get('#finding_aid_finding_aids_enabled_1').should('be.checked')

      // Test that finding aid search options are present
      cy.visit('informationobject/browse')
      cy.get('[data-cy=advanced-search-toggle]').click()
      cy.get('select[name=sf0]').should(($sel) => {
        expect($sel).descendants('option[value=findingAidTranscript]')
        expect($sel).descendants('option[value=allExceptFindingAidTranscript]')
      })
      cy.get('#findingAidStatus').should('exist')

      // Test that description finding aid links are present
      cy.visit(testUrl)
      cy.get('[data-cy=generate-finding-aid]').should('exist')
      cy.get('[data-cy=upload-finding-aid]').should('exist')
    })
  }
)
