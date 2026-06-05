.PHONY: up down build shell cli console composer test test-unit test-integration test-functional migrate migration db db-test logs

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

test-integration:
	docker compose exec php-cli php vendor/bin/phpunit --testsuite Integration

test-functional:
	docker compose exec php-cli php vendor/bin/phpunit --testsuite Functional

migrate:
	docker compose exec php-cli php bin/console doctrine:migrations:migrate --no-interaction

migration:
	docker compose exec php-cli php bin/console doctrine:migrations:diff

db:
	docker compose exec mysql mysql -u app -papp app

db-test:
	docker compose exec mysql_test mysql -u app -papp app_test

logs:
	docker compose logs -f
