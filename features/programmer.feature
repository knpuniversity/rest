Feature: Programmer
  In order to battle projects
  As a user
  I need to be able to create programmers and power them up

  Background:
    Given I am logged in
    And I created the following programmers
      | nickname |
      | Jerry    |
      | Kerry    |
      | Bob      |
    And the following projects exist
      | name                |
      | Tough Project       |
      | Medium Project      |
      | Easy Project        |
      | Really Easy Project |

  @javascript
  Scenario: Create a programmer
    When I go to "/"
    And I click "Create Programmer"
    And I fill in "Nickname" with "SuperNiceGuy"
    And I select an avatar
    And I press "Compile"
    Then I should see "SuperNiceGuy has been compiled and is ready for battle!"

  Scenario: I only see my programmers
    Given someone else created a programmer named "Outsider"
    When I go to "/"
    And I click "Start Battle"
    Then I should see 3 programmers in the list

  Scenario: Choose a programmer to battle with
    When I go to "/"
    And I click "Start Battle"
    And I click "Kerry"
    Then I should be on "/programmers/Kerry"

  @javascript
  Scenario: See 3 choices of battles
    Given I am on "/programmers/Kerry"
    When I press "Start Battle"
    And I wait for the dialog to appear
    Then I should see 3 projects in the list

  Scenario: Cannot fight with someone else's programmers
    Given someone else created a programmer named "Outsider"
    When I go to "/programmers/Outsider"
    Then I should not see "Start Battle"

  Scenario: Power up
    Given I am on "/programmers/Kerry"
    When I press "Power Up"
    Then I should see a flash message containing "energy"
