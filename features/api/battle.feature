Feature:
  In order to prove my programmers' worth against projects
  As an API client
  I need to be able to create and view battles

  Background:
    Given the user "weaverryan" exists
    And "weaverryan" has an authentication token "ABCD123"
    And I set the "Authorization" header to be "token ABCD123"

  Scenario: Create a battle
    Given there is a project called "my_project"
    And there is a programmer called "Fred"
    And I have the payload:
      """
      {
        "programmerId": "%programmers.Fred.id%",
        "projectId": "%projects.my_project.id%"
      }
      """
    When I request "POST /api/battles"
    Then the response status code should be 201
    And the "Location" header should exist
    And the "didProgrammerWin" property should exist

  Scenario: GET one battle
    Given there is a project called "projectA"
    And there is a programmer called "Fred"
    And there has been a battle between "Fred" and "projectA"
    When I request "GET /api/battles/%battles.last.id%"
    Then the response status code should be 200
    And the "notes" property should exist
    And the "didProgrammerWin" property should exist
