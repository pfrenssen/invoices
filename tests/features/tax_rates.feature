Feature: Tax rates
  In order to supply tax rates on line items
  As a business owner
  I want to be able to manage tax rates

@api
Scenario: A created tax rate appears in the overview
  Given I am logged in as a user with the 'business owner' role
  And tax rate:
  | name | rate  |
  | VAT  | 21.00 |
  When I visit the tax rate overview
  Then I should see 1 tax rate in the overview
