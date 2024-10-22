describe('Descriptions', () => {
  beforeEach('Login', () => {
    cy.login()
  })

  it('Creates a basic description', () => {
    cy.visit('/informationobject/add')
    cy.contains('Identity area').click()
    cy.get('input#identifier').type('123')
    cy.get('input#title').type('Test description A', {force: true})
    cy.get('#main-column form').submit()

    cy.get('#main-column > h1').contains('123 - Test description A')
  })

  it('Creates a basic description without GUI', () => {
    cy.createDescription({identifier: '456', title: 'Test description B'})
    .then(slug => {
      cy.visit('/' + slug)
      cy.get('#main-column > h1').contains('456 - Test description B')
    })
  })

  it('Deletes a basic description', () => {
    cy.createDescription({identifier: '789', title: 'Test description C'})
    .then(slug => {
      cy.visit('/' + slug)
      cy.get('.actions').contains('Delete').click()
      cy.get('.actions').contains('Delete').click()

      cy.request({url: '/' + slug, failOnStatusCode: false})
      .then(response => {
        expect(response.status).to.equal(404)
        expect(response.body).to.contain('Sorry, page not found')
      })
    })
  })

  it('Deletes a basic description without GUI', () => {
    cy.createDescription({identifier: '789', title: 'Test description C'})
    .then(slug => cy.deleteDescription(slug).then(() =>
      cy.request({url: '/' + slug, failOnStatusCode: false})
      .then(response => {
        expect(response.status).to.equal(404)
        expect(response.body).to.contain('Sorry, page not found')
      })
    ))
  })
})
