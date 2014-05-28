Feature:
  In order to prove my programmers' worth against projects
  As an API client
  I need to be able to create and view battles

  Background:
    Given the user "weaverryan" exists
    And "weaverryan" has an authentication token "ABCD123"
    And I set the "Authorization" header to be "token ABCD123"

  Scenario: Create a programmer
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

  Scenario: GET all battles
    Given there is a project called "projectA"
    And there is a programmer called "Fred"
    And there is a programmer called "Barney"
    And there has been a battle between "Fred" and "projectA"
    And there has been a battle between "Barney" and "projectA"
    When I request "GET /api/battles"
    Then the response status code should be 200
    And the "battles" property should be an array
    And the "battles" property should contain 2 items
    And the "battles.0.programmerUri" property should equal "/api/programmers/Fred"
