Feature:
  In order to avoid having to look up previous invoices
  As a business owner
  I need the invoice number to be prepopulated when I create a new invoice

@api
Scenario Outline: Check incrementing of invoice numbers
  Given the most recent invoice has the number "<number1>"
  Then the next invoice number should be "<number2>"

  Examples:
  | number1  | number2  |
  | 2015-001 | 2015-002 |
  | AEX-099A | AEX-100A |
  | 000Z!<99 | 001Z!<00 |
  | G345*&32 | G345*&33 |
  | 0 1 9 99 | 0 2 0 00 |
  | INV99-9  | INV100-0 |

@api
Scenario: Check if an incremented invoice number is provided as the default
  Given I am logged in as a user with the "business owner" role
  And client:
    | name         | email               |
    | Dimmu Borgir | info@dimmuborgir.no |
  When I go to the add invoice form
  Then the "Invoice number" field should contain ""
  Given invoice:
  | client       | number   | date     |
  | Dimmu Borgir | SI15\029 | 20150505 |
  When I go to the add invoice form
  Then the "Invoice number" field should contain "SI15\030"
