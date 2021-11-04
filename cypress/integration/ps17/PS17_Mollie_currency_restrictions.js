/// <reference types="Cypress" />
context('PS17 Currencly Restrictions check', () => {
    beforeEach(() => {
          // likely want to do this in a support file
      // so it's applied to all spec files
      // cypress/support/index.js

      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
  })
it('DE / 100 EUR / 3 Klarna Payments should be visible', () => {
      cy.viewport(1920,1080)
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/fr/accueil/20-testproduct1.html')
      cy.get('a > .material-icons').click()
      cy.get('[class="form-control"]').type((Cypress.env('FO_username')), {log: false})
      cy.get('[class="form-control js-child-focus js-visible-password"]').type((Cypress.env('FO_password')), {log: false})
      cy.get('[class="btn btn-primary"]').click()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/fr/accueil/20-testproduct1.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('GERMANY').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.get('[class="payment-options "]').contains('Payer en 3 fois sans frais')
      cy.get('[class="payment-options "]').contains('Slice it.')
      cy.get('[class="payment-options "]').contains('SOFORT Banking')
})
it('DE / <45 EUR / Klarna Slice It. should not be visible', () => {
      cy.viewport(1920,1080)
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/art/4-16-the-adventure-begins-framed-poster.html#')
      cy.get('a > .material-icons').click()
      cy.get('[class="form-control"]').type((Cypress.env('FO_username')), {log: false})
      cy.get('[class="form-control js-child-focus js-visible-password"]').type((Cypress.env('FO_password')), {log: false})
      cy.get('[class="btn btn-primary"]').click()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/art/4-16-the-adventure-begins-framed-poster.html#')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('GERMANY').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.get('[class="payment-options "]').contains('Slice it.').should('not.exist')
})
it('DE / >10000 EUR / Klarna Slice It, Klarna Pay Later, iDEAL, Bancontact, Belfius Direct Net, KBC/CBC, Credit card, SOFORT Banking, SOFORT Banking, sps, paysafecard, Przelewy 24, Gift cards should not be visible', () => {
      cy.viewport(1920,1080)
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/fr/accueil/20-testproduct1.html')
      cy.get('a > .material-icons').click()
      cy.get('[class="form-control"]').type((Cypress.env('FO_username')), {log: false})
      cy.get('[class="form-control js-child-focus js-visible-password"]').type((Cypress.env('FO_password')), {log: false})
      cy.get('[class="btn btn-primary"]').click()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/fr/accueil/20-testproduct1.html')
      cy.get('[id="quantity_wanted"]').clear().type('501')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('GERMANY').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.get('[class="payment-options "]').contains('Slice it.').should('not.exist')
      cy.get('[class="payment-options "]').contains('iDEAL').should('not.exist')
      cy.get('[class="payment-options "]').contains('Carte de crédit').should('not.exist')
      cy.get('[class="payment-options "]').contains('Payer en 3 fois sans frais').should('not.exist')
      cy.get('[class="payment-options "]').contains('Slice it.').should('not.exist')
      cy.get('[class="payment-options "]').contains('SOFORT').should('not.exist')
      cy.get('[class="payment-options "]').contains('Bancontact').should('not.exist')
      cy.get('[class="payment-options "]').contains('Przelewy24').should('not.exist')
      cy.get('[class="payment-options "]').contains('eps').should('not.exist')
      cy.get('[class="payment-options "]').contains('KBC/CBC').should('not.exist')
      cy.get('[class="payment-options "]').contains('Belfius').should('not.exist')
      cy.get('[class="payment-options "]').contains('Giropay').should('not.exist')
      cy.get('[class="payment-options "]').contains('Gift').should('not.exist')
})
it('JPN / >1000000 JPY / PayPal should not be visible', () => {
      cy.viewport(1920,1080)
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/accueil/20-testproduct1.html')
      cy.get('[class="expand-more _gray-darker"]').click()
      cy.contains('JPY').click()
      cy.get('a > .material-icons').click()
      cy.get('[class="form-control"]').type((Cypress.env('FO_username')), {log: false})
      cy.get('[class="form-control js-child-focus js-visible-password"]').type((Cypress.env('FO_password')), {log: false})
      cy.get('[class="btn btn-primary"]').click()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/fr/accueil/20-testproduct1.html')
      cy.get('[id="quantity_wanted"]').clear().type('1000')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('JAPAN').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.get('[class="payment-options "]').contains('PayPal').should('not.exist')
})
it('FR / <35 EUR / Klarna Pay Later should not be visible', () => {
      cy.viewport(1920,1080)
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home-accessories/8-mug-today-is-a-good-day.html')
      cy.get('a > .material-icons').click()
      cy.get('[class="form-control"]').type((Cypress.env('FO_username')), {log: false})
      cy.get('[class="form-control js-child-focus js-visible-password"]').type((Cypress.env('FO_password')), {log: false})
      cy.get('[class="btn btn-primary"]').click()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home-accessories/8-mug-today-is-a-good-day.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('FRANCE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.get('[class="payment-options "]').contains('Pay later.').should('not.exist')
})
it('FR / >1000 EUR / Klarna Pay Later should not be visible', () => {
      cy.viewport(1920,1080)
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/accueil/20-testproduct1.html')
      cy.get('a > .material-icons').click()
      cy.get('[class="form-control"]').type((Cypress.env('FO_username')), {log: false})
      cy.get('[class="form-control js-child-focus js-visible-password"]').type((Cypress.env('FO_password')), {log: false})
      cy.get('[class="btn btn-primary"]').click()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/accueil/20-testproduct1.html')
      cy.get('[id="quantity_wanted"]').clear().type('11')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('FRANCE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.get('[class="payment-options "]').contains('Pay later.').should('not.exist')
})
})