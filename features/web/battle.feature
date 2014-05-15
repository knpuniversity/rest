Feature: Battle
  In order to get a lot done and save the world
  As a user
  I need to be able to start battles against projects and view old battles

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
  Scenario: Start a battle
    Given I am on "/programmers/Kerry"
    When I press "Start Battle"
    And I wait for the dialog to appear
    And I click on a project
    And I wait for the dialog to disappear
    And I should see "Winner"

  Scenario: View past battles
    Given the following battles have been valiantly fought:
      | programmer | project |
      | Jerry | Tough Project |
      | Jerry | Medium Project |
      | Jerry | Tough Project |
      | Kerry | Easy Project |
    And I am on "/"
    When I click "Scores"
    Then I should see a table with 4 rows
    And I should see "Medium Project"
    But I should not see "Really Easy Project"
