Feature: Creating invoices
  In order to manage my invoices
  As a business owner
  I want to be able to create invoices

@api
Scenario Outline: Check labels for inline entities
  Given I am logged in as a user with the "business owner" role
  When I go to the add invoice form
  Then I should see the fieldset with the legend "<legend>"
  When I press the "<add new>" button
  Then I should see the fieldset with the legend "<add new>"
  And I should see the button "<create>"
  When I press the 'Cancel' button
  And I press the "<add existing>" button
  Then I should see the fieldset with the legend "<add existing>"
  And I should see the button "<add>"

  Examples:
  | legend   | add new         | create         | add existing         | add         |
  | Client   | Add new client  | Create client  | Add existing client  | Add client  |
  | Services | Add new service | Create service | Add existing service | Add service |
  | Products | Add new product | Create product | Add existing product | Add product |

@api
Scenario: Inline editing of a line item after adding it
  Given I am logged in as a user with the 'business owner' role
  When I go to the add invoice form
  And I press "Add new service" in the "Services" fieldset
  And I enter the following:
  | Description | A useful service |
  | Unit cost   | 99.99            |
  | Quantity    | 12               |
  | Tax rate    | 19.50            |
  | Discount    | 5                |
  And I select the radio button "months"
  And I press "Create service" in the "Add new service" fieldset
  And I press "Edit" in the "Services" fieldset
  Then I should see the following field values:
  | Description | A useful service |
  | Unit cost   | 99.99            |
  | Quantity    | 12               |
  | Tax rate    | 19.50            |
  | Discount    | 5                |
  And the radio button option "months" should be selected

@api
Scenario Outline: Tax rates should be entered using an HTML5 number field
  Given I am logged in as a user with the 'business owner' role
  When I go to the add invoice form
  And I press the "<button>" button
  Then I should see that the 'Tax rate' field is a number field

  Examples:
  | button          |
  | Add new service |
  | Add new product |

@api
Scenario: Create an invoice using only the required fields
  Given I am logged in as a user with the "business owner" role
  When I go to the add invoice form
  And I fill in "2015001" for "Invoice number"
  And I press the "Add new client" button
  And I fill in the following:
  | Name          | Martin "Pops" Holdgate          |
  | Email address | pops@holdgate-enterprises.co.uk |
  And I press the "Save" button
  Then I should see the success message "New invoice has been added."
  But I should not see the following error messages:
  | error messages                 |
  | Address 1 field is required.   |
  | Postal code field is required. |
  | City field is required.        |
  And I should have 1 client
  And I should have 1 invoice
