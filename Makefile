.PHONY: up down build shell cli console composer test test-unit logs

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

shell:
	docker compose exec php-fpm sh

cli:
	docker compose exec php-cli sh

console:
	docker compose exec php-cli php bin/console $(cmd)

composer:
	docker compose exec php-cli composer $(cmd)

test:
	docker compose exec php-cli php vendor/bin/phpunit

test-unit:
	docker compose exec php-cli php vendor/bin/phpunit --testsuite Unit

logs:
	docker compose logs -f
