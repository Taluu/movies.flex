# Movies API example implementation using Symfony Flex

## Requirements
- PHP 7.1
- MySQL 5.7 is preferred
- Composer

## How to run
- `composer install`
- `bin/console hautelook:fixtures:load --append`
- `make serve`

It is then accessible on `http://127.0.0.1:8000/v1/movies` (by default). You
can use the command `bin/console debug:router` if you want to do more stuff

## Tests
Two commands to launch :

- `vendor/bin/behat`
- `vendor/bin/phpunit`

## Fixtures
A set of 11 fixtures are available (1 is to be "soft deleted"). In order to
load them, you need to use the following command
`bin/command hautelook:fixtures:load --append`

Be careful with the `--append`, it is needed to behat tests.
