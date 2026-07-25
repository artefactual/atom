describe('Administrator user and permission management', () => {
  const editor = {
    username: 'cypress-permissions-editor',
    email: 'cypress-permissions-editor@example.com',
    password: 'AtoM-Cypress-2026-Editor',
    group: 'editor',
  }
  const contributor = {
    username: 'cypress-permissions-contributor',
    email: 'cypress-permissions-contributor@example.com',
    password: 'AtoM-Cypress-2026-Contributor',
    group: 'contributor',
    active: false,
  }

  let descriptionSlug

  const loginAsAdmin = () => {
    cy.clearCookies()
    cy.login()
  }

  const removeTestRecords = () => {
    loginAsAdmin()

    if (descriptionSlug) {
      cy.deleteDescription(descriptionSlug)
      descriptionSlug = undefined
    }

    cy.deleteUser(editor.username)
    cy.deleteUser(contributor.username)
  }

  before(removeTestRecords)
  afterEach(removeTestRecords)

  it('Creates an editor and enforces the admin boundary', () => {
    loginAsAdmin()
    cy.createUser(editor).then((userSlug) => {
      cy.get('#main-column').within(() => {
        cy.contains(editor.username)
        cy.contains(editor.email)
        cy.contains('editor')
      })

      cy.clearCookies()
      cy.login(editor.email, editor.password)
      cy.visit('/')

      cy.get('#user-menu').should('contain', editor.username)
      cy.get('#add-menu').should('exist')
      cy.get('#manage-menu').should('exist')
      cy.get('#admin-menu').should('not.exist')

      cy.request({ url: '/user/list', failOnStatusCode: false }).then(
        (response) => {
          expect(response.status).to.equal(403)
          expect(response.body).to.contain(
            'Sorry, you do not have permission to access that page'
          )
        }
      )

      cy.createDescription({
        identifier: 'cypress-editor-permissions',
        title: 'Cypress editor permission description',
      }).then((slug) => {
        descriptionSlug = slug
        cy.visit(`/${descriptionSlug}`)
        cy.get('#main-column > h1').should(
          'contain',
          'Cypress editor permission description'
        )

        cy.deleteDescription(descriptionSlug)
        descriptionSlug = undefined

        cy.request({
          url: `/${slug}`,
          failOnStatusCode: false,
        })
          .its('status')
          .should('equal', 404)
      })

      cy.wrap(userSlug).should('not.be.empty')
    })
  })

  it('Activates a contributor and denies delete permission', () => {
    loginAsAdmin()
    cy.createUser(contributor).then((userSlug) => {
      cy.clearCookies()
      cy.login(contributor.email, contributor.password)
      cy.visit('/')
      cy.get('#user-menu').should('contain', 'Log in')

      loginAsAdmin()
      cy.visit(`/${userSlug}/edit`)
      cy.get('#active').check()
      cy.get('#currentPassword').type(Cypress.env('adminPassword'))
      cy.get('#main-column form').submit()
      cy.get('#main-column h1').should('contain', contributor.username)

      cy.clearCookies()
      cy.login(contributor.email, contributor.password)
      cy.visit('/')

      cy.get('#user-menu').should('contain', contributor.username)
      cy.get('#add-menu').should('exist')
      cy.get('#admin-menu').should('not.exist')

      cy.createDescription({
        identifier: 'cypress-contributor-permissions',
        title: 'Cypress contributor permission description',
      }).then((slug) => {
        descriptionSlug = slug

        cy.request(`/${descriptionSlug}/edit`)
          .its('status')
          .should('equal', 200)

        cy.request({
          url: `/${descriptionSlug}/informationobject/delete`,
          failOnStatusCode: false,
        }).then((response) => {
          expect(response.status).to.equal(403)
          expect(response.body).to.contain(
            'Sorry, you do not have permission to access that page'
          )
        })
      })
    })
  })
})
