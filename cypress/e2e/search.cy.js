describe('Search', () => {
  beforeEach('Login', () => {
    cy.login()
  })

  beforeEach('Delete all descriptions', () => {
    cy.waitUntil(() =>
      cy.request('/informationobject/browse').its('body')
      .then(body => {
        let deletions = []
        Cypress.$(body).find('article.search-result a')
        .each((_, link) =>
          deletions.push(cy.deleteDescription(
            Cypress.$(link).attr('href').split('/').pop()
          ))
        )
        return Cypress.Promise.all(deletions)
      })
      .then(deleted => deleted.length == 0)
    )
  })

  it('Finds results using stop words and quotes', () => {
    cy.createDescription({title: 'department of medical imaging'})
    cy.createDescription({title: 'department medical imaging'})
    cy.createDescription({title: 'department of imaging'})
    cy.createDescription({title: 'medical imaging department'})
    cy.createDescription({title: 'department of medical'})

    cy.visit('/informationobject/browse')
    cy.get('.multiline-header').contains('Showing 5 results')

    cy.get('input[name=query]').clear()
    .type('department of imaging{enter}')
    cy.get('.multiline-header').contains('Showing 4 results')

    cy.get('input[name=query]').clear()
    .type('department of medical{enter}')
    cy.get('.multiline-header').contains('Showing 4 results')

    cy.get('input[name=query]').clear()
    .type('department of medical imaging{enter}')
    cy.get('.multiline-header').contains('Showing 3 results')

    cy.get('input[name=query]').clear()
    .type('"department of medical"{enter}')
    cy.get('.multiline-header').contains('Showing 2 result')

    cy.get('input[name=query]').clear()
    .type('"department of medical imaging"{enter}')
    cy.get('.multiline-header').contains('Showing 1 result')
  })

  it('Doesn\'t find "to be or not to be"', () => {
    cy.createDescription({title: 'to be or not to be'})

    cy.visit('/informationobject/browse')
    cy.get('.multiline-header').contains('Showing 1 result')

    cy.get('input[name=query]').clear()
    .type('to be or not to be{enter}')
    cy.get('.multiline-header').contains('No results found')

    cy.get('input[name=query]').clear()
    .type('"to be or not to be"{enter}')
    cy.get('.multiline-header').contains('No results found')
  })
})
