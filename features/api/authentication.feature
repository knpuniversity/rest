Feature: Authentication
  In order to access protected resource
  As an API client
  I need to be able to authenticate

  Scenario: Create a programmer without authentication
    When I request "POST /api/programmers"
    Then the response status code should be 401
    And the "detail" property should equal "Authentication Required"
