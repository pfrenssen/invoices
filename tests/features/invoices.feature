Feature: Invoices
  In order to manage my invoices
  As a business owner
  I want to be able to create new invoices

Background:
  Given I am logged in as a user with the 'business owner' role
  And client:
  | name    | email           |
  | Axemill | info@axemill.eu |

@api
Scenario: View the detail page of an invoice
  Given invoice:
  | client  | number    | date       |
  | Axemill | 2015/0001 | 2015-02-23 |
  When I visit the detail page for invoice '2015/0001'
  Then I see the text 'Axemill'
  And I see the text '2015/0001'
  And I see the text 'February 23, 2015'
