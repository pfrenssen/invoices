Feature: Create client
  In order to manage my clients
  As a business owner
  I need to be able to add a new client to the database

Scenario Outline: Website field validation for correct entries
  Given a client with website "<website>"
  Then the client website should be valid

  Examples:
  | website                                                   |
  | http://localhost                                          |
  | http://localhost/                                         |
  | http://127.0.0.1/                                         |
  | https://twitter.com                                       |
  | https://twitter.com/                                      |
  | https://www.google.com                                    |
  | https://www.google.com/                                   |
  | https://www.google.be/q=i+pity+the+fool                   |
  | http://en.wikipedia.org/wiki/Uniform_resource_locator     |
  | https://jon:hunter2@mcp.net:8080/login.php?r=m%20d#Log_in |
  | 127.0.0.1/                                                |
  | twitter.com                                               |
  | twitter.com/                                              |
  | www.google.com                                            |
  | www.google.com/                                           |
  | www.google.be/q=i+pity+the+fool                           |
  | en.wikipedia.org/wiki/Uniform_resource_locator            |
  | jon:hunter2@mcp.net:8080/login.php?r=m%20d#Log_in         |

Scenario Outline: Website field validation for incorrect entries
  Given a client with website "<website>"
  Then the client website should be invalid

  Examples:
  | website                                  |
  | abc123                                   |
  | node/1                                   |
  | https:/www.google.com/                   |
  | https://www.goog%le.be/q=i+pity+the+fool |
