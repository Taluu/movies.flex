@http @api @rest
Feature: Fetch collection of Movies

Scenario: Fetch a list of undeleted movies
  When I create a "GET" request to "/v1/movies"
    And I send the request
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "count" should be greater than or equal to 10
    And in the json, "total" should be greater than or equal to 10
    And in the json, each "id" in "data" should match "{^[0-9a-z]{40}$}"

Scenario: Fetch a paginated list of undeleted movies
  When I create a "GET" request to "/v1/movies?limit=5"
    And I send the request
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "count" should be equal to 5
    And in the json, "total" should be greater than or equal to 10

Scenario: Fetch a paginated list of undeleted movies with a offset
  When I create a "GET" request to "/v1/movies?limit=5&start=1"
    And I send the request
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "count" should be equal to 5
    And in the json, "total" should be greater than or equal to 10

Scenario: Fetch a list of undeleted movies sorted by name
  When I create a "GET" request to "/v1/movies?order=name&direction=asc"
    And I send the request
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, each element in "data" should be sorted by "name" asc

Scenario: Fetch a list of undeleted movies sorted by name, inverted
  When I create a "GET" request to "/v1/movies?order=name&direction=desc"
    And I send the request
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, each element in "data" should be sorted by "name" desc

Scenario: Fetch a list of undeleted movies sorted by an invalid field
  When I create a "GET" request to "/v1/movies?order=foo&direction=asc"
    And I send the request
  Then the status code should be 400
    And the response should be a valid json response
    And in the json, "error" should be:
      """
      Expected "name" or no value for order, had "foo"
      """
    And in the json, "http" should be equal to 400

Scenario: Fetch a list of undeleted movies sorted by name but in invalid direction
    When I create a "GET" request to "/v1/movies?order=name&direction=foo"
    And I send the request
  Then the status code should be 400
    And the response should be a valid json response
    And in the json, "error" should be:
      """
      Expected "asc" or "desc" for order direction, had "foo"
      """
    And in the json, "http" should be equal to 400

