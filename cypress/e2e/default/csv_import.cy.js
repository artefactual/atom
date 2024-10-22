describe('CSV import', () => {
  it('Maintains siblings order', () => {
    cy.login()

    cy.visit('/object/importSelect?type=csv')
    cy.get('input[name=file]').attachFile('import_order.csv')
    cy.get('#wrapper form').submit()

    cy.contains('Import file initiated')

    cy.visit('/settings/treeview')
    cy.get('input[name=type][value=fullWidth]').click()
    cy.get('#wrapper form').submit()

    cy.waitUntil(() =>
      cy.visit('/informationobject/browse').then(() =>
        Cypress.$('a:contains("CSV import order fonds")').length > 0
      )
    )

    cy.contains('CSV import order fonds').click()
    cy.waitUntil(() => Cypress.$('li.jstree-node').length === 4)
    cy.get('li.jstree-closed > i')
      .should('be.visible') // Ensure the element is visible
      .click({ multiple: true });
    cy.waitUntil(() => Cypress.$('li.jstree-node').length === 34)

    const orderedTitles = [
      'CSV import order fonds',
      'Series A',
      'SA Item 1',
      'SA Item 2',
      'SA Item 3',
      'SA Item 4',
      'SA Item 5',
      'SA Item 6',
      'SA Item 7',
      'SA Item 8',
      'SA Item 9',
      'SA Item 10',
      'SA Item 11',
      'SA Item 12',
      'SA Item 13',
      'SA Item 14',
      'SA Item 15',
      'Series B',
      'SB Item 1',
      'SB Item 2',
      'SB Item 3',
      'SB Item 4',
      'SB Item 5',
      'SB Item 6',
      'SB Item 7',
      'SB Item 8',
      'SB Item 9',
      'SB Item 10',
      'Series C',
      'SC Item 1',
      'SC Item 2',
      'SC Item 3',
      'SC Item 4',
      'SC Item 5',
    ]

    cy.get('li.jstree-node').each(($li, index) =>
      cy.wrap($li).contains(orderedTitles[index])
    )
  })
})
