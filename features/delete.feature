@http @api @rest
Feature: "Delete" a movie

Scenario: Soft delete a movie
  When I create a "DELETE" request to "/v1/movies/17ba0791499db908433b80f37c5fbc89b870084b"
    And I send the request
  Then the status code should be 204
