Feature: User Management
  In order to use the site as me and save data
  As a user
  I need to be able to register and login

  Scenario: Registration
    When I go to "/login"
    And I click "Register"
    And I fill in "Email" with "ryan@knpuniversity.com"
    And I fill in "Username" with "weaverryan"
    And I fill in "Password" with "foo"
    And I press "Battle"
    Then I should see "Logout"

  Scenario: Logging in
    Given there is a user "coolguy@baz.com" with password "bar"
    When I go to "/login"
    And I fill in "Email" with "coolguy@baz.com"
    And I fill in "Password" with "bar"
    And I press "Login"
    And I go to "/"
    Then I should see "Logout"
