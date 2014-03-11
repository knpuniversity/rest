Feature: Programmer
  In order to defeat evil projects
  As a user
  I need to be able to create programmers and battle them against evil projects

  Background:
    Given I am logged in

  @javascript
  Scenario: Create a programmer
    When I go to "/"
    And I click "Create Programmer"
    And I fill in "Nickname" with "SuperNiceGuy"
    And I select an avatar
    And I press "Compile"
    Then I should see "SuperNiceGuy has been compiled and is ready for battle!"
