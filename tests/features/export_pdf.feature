Feature: Exporting invoices
  In order to print my invoices and share them with my clients
  As a business owner
  I want to be able to export invoices in PDF format

@api
Scenario: Verify content of exported invoice
  Given I am logged in as a user with the "business owner" role
  And client:
    | name  | email         |
    | Omnia | info@omnia.eu |
  And services:
    | description    | unit cost | time unit | quantity | tax   | discount |
    | Equipment hire | 124.95    | months    | 10.5     | 19.00 | 15       |
    | Truck rental   | 74.99     | days      | 120      | 19.00 | 15       |
    | Operator       | 250       | days      | 120      | 19.00 | 0        |
  And products:
    | description  | unit cost | quantity | tax   | discount |
    | Sievert Harp | 3305.00   | 1        | 19.00 | 20       |
    | Neumann U87  | 2599.00   | 2        | 19.00 | 20       |
  And invoice:
    | client | number  | date       | services                               | products                  |
    | Omnia  | 015-001 | 2015-04-08 | Equipment hire, Truck rental, Operator | Neumann U87, Sievert Harp |
  When I export the invoice with number "015-001"
  Then the exported invoice should have the following fields:
    | number          | 015-001                  |
    | date            | Wednesday, April 8, 2015 |
    | client          | Omnia                    |
    | client email    | info@omnia.eu            |
  And the exported invoice should have the following service data:
    | description    | unit cost | time unit | quantity | tax   | discount | total     |
    | Equipment hire | 124.95    | months    | 10.5     | 19.00 | 15.00%   | 1327.06€  |
    | Truck rental   | 74.99     | days      | 120      | 19.00 | 15.00%   | 9102.29€  |
    | Operator       | 250       | days      | 120      | 19.00 | 0.00%    | 35700.00€ |
  And the exported invoice should have the following product data:
    | description  | unit cost | quantity | tax   | discount | total    |
    | Sievert Harp | 3305.00   | 1        | 19.00 | 20.00%   | 3146.36€ |
    | Neumann U87  | 2599.00   | 2        | 19.00 | 20.00%   | 4948.50€ |
