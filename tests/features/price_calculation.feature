Feature: Line item price calculation
  In order to get an overview of the cost of a line item
  As a business owner
  I want to be able to see the calculated price of a line item

Background:
  Given I am logged in as a user with the 'business owner' role

@api
Scenario Outline: The price calculation for a service should be correct
  Given service:
    | description   | unit cost   | time unit   | quantity   | tax   | discount   |
    | <description> | <unit cost> | <time unit> | <quantity> | <tax> | <discount> |
  Then the price calculation of the "<description>" line item should equal "<total>"

  Examples:
    | description | unit cost | time unit | quantity | tax   | discount | total   |
    | service 1   | 100       | hours     | 20       | 15    | 10       | 2070.00 |
    | service 2   | 14        | days      | 3        | 12.5  | 7        | 43.94   |
    | service 3   | 237.12    | weeks     | 21       | 33.33 | 40.05    | 3980.19 |
    | service 4   | 0         | months    | 0        | 0     | 0        | 0.00    |
    | service 5   | 199.95    | years     | 1        | 21    | 0        | 241.94  |

@api
Scenario: The price calculation for an invoice should be correct
  Given client:
    | name    | email           |
    | Axemill | info@axemill.eu |

  Given services:
    | description | unit cost | time unit | quantity | tax   | discount |
    | service 1   | 100       | hours     | 20       | 15    | 10       |
    | service 2   | 14        | days      | 3        | 12.5  | 7        |
    | service 3   | 237.12    | weeks     | 21       | 33.33 | 40.05    |
    | service 4   | 0         | months    | 0        | 0     | 0        |
    | service 5   | 199.95    | years     | 1        | 21    | 0        |

  Given products:
    | description | unit cost | quantity | tax   | discount |
    | product 1   | 580.48    | 37       | 0.05  | 31.68    |
    | product 2   | 0.97      | 11       | 37.86 | 7.21     |
    | product 3   | 51.91     | 76       | 0.38  | 14.32    |
    | product 4   | 464.00    | 26       | 9.07  | 77.59    |
    | product 5   | 56.01     | 59       | 16.95 | 42.41    |

  Given invoice:
    | client   | number | date     | discount | products                                              | services                                              |
    | Axemill  | 15/001 | 20150223 | 12.13    | product 1, product 2, product 3, product 4, product 5 | service 1, service 2, service 3, service 4, service 5 |
  Then the price excluding VAT of the invoice should equal "24335.25"
  And the price including VAT of the invoice should equal "26007.92"
