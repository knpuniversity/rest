Feature: Token
  In order to access restricted information
  As an API client
  I can create tokens and use them to access information

  Scenario: Creating a token
    Given there is a user "weaverryan" with password "test"
    And I have the payload:
      """
      {
        "notes": "A testing token!"
      }
      """
    And I authenticate with user "weaverryan" and password "test"
    When I request "POST /api/tokens"
    Then the response status code should be 201
    # And the "Location" header should exist
    And the "token" property should be a string

  Scenario: Creating a token with a bad password
    Given there is a user "weaverryan" with password "test"
    And I have the payload:
      """
      {
        "notes": "A testing token!"
      }
      """
    And I authenticate with user "weaverryan" and password "WRONG"
    When I request "POST /api/tokens"
    Then the response status code should be 401

  Scenario: Creating a token without a note
    Given there is a user "weaverryan" with password "test"
    And I authenticate with user "weaverryan" and password "test"
    When I request "POST /api/tokens"
    Then the response status code should be 400
    And the "errors.notes" property should equal "Please add some notes about this token"
