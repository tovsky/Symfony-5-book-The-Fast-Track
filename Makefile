SHELL := /bin/bash

tests:
    docker-compose exec fpm bin/console doctrine:fixtures:load -n
    docker-compose exec fpm bin/phpunit
.PHONY: tests