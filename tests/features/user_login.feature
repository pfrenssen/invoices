Feature: Logging in
  In order to prevent unauthorized access
  As a business owner
  I want to ensure that my account can only be accessed with proper credentials

@api
Scenario: Resend activation mail
  Given I am an anonymous user
  When I visit "user"
  And I click "Create new account"
  And I fill in the following:
  | E-mail           | pasajero@iberia.es |
  | Password         | Desperdicios12@    |
  | Confirm password | Desperdicios12@    |
  | Company name     | Iberia             |
  | First name       | Mariano            |
  | Last name        | Campillo Mazo      |
  And I press "Create new account"
  Then I should see the success message "A welcome message with further instructions has been sent to your e-mail address."
  When I fill in the following:
  | E-mail           | pasajero@iberia.es |
  | Password         | Incorrect password |
  And I press "Log in"
  Then I should see the error message "Sorry, unrecognized username or password. Have you forgotten your password?"
  And I should not see the error message "Your account is not yet activated. Please follow the link in your activation e-mail. If you have not received the mail, please check your spam folder, or resend the activation e-mail."
  When I fill in the following:
    | E-mail           | pasajero@iberia.es |
    | Password         | Desperdicios12@    |
  And I press "Log in"
  Then I should see the error message "Your account is not yet activated. Please follow the link in your activation e-mail. If you have not received the mail, please check your spam folder, or resend the activation e-mail."
  And I should not see the error message "Sorry, unrecognized username or password. Have you forgotten your password?"
  When I click "resend the activation e-mail"
  Then I should see the success message "The activation e-mail has been sent to pasajero@iberia.es."
