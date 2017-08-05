@http @api @rest
Feature: Fetch an element of a collection of Movies

Scenario: Fetch a single movie
  When I create a "GET" request to "/v1/movies/356a192b7913b04c54574d18c28d46e6395428ab"
    And I send the request
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "count" should be equal to 1
    And in the json, "total" should be equal to 1
    And in the json, "data.id" should match "{^[0-9a-z]{40}$}"

Scenario: Get a 404 when fetching an inexistent movie
  When I create a "GET" request to "/v1/movies/fooooooooooooooooooooooooooooooooooooooo"
    And I send the request
  Then the status code should be 404
    And the response should be a valid json response
    And in the json, "error" should be equal to "Movie fooooooooooooooooooooooooooooooooooooooo was not found"
    And in the json, "http" should be equal to 404
