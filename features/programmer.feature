Feature: Programmer
  In order to defeat evil projects
  As a user
  I need to be able to create programmers and battle them against evil projects

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
    Then I should be on "/programmer/Kerry"

  Scenario: See 3 choices of battles
    Given I am on "/programmer/Kerry"
    When I click "Start Battle"
    Then I should see 3 projects in the list

  Scenario: Start a battle
    Given I am on "/programmer/Kerry"
    When I click "Start Battle"
    And I click on a project
    Then I should see "Battle Commencing"
