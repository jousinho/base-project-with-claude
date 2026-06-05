#!/usr/bin/env bash
set -euo pipefail

cp .env.example .env
docker compose build
docker compose up -d
docker compose exec php-cli composer install
docker compose exec php-cli mkdir -p var/cache var/log
docker compose exec php-cli chmod -R 777 var
docker compose exec php-cli php bin/console doctrine:migrations:migrate --no-interaction || true
docker compose exec php-cli env DATABASE_URL="postgresql://app:app@postgres_test:5432/app_test?serverVersion=16&charset=utf8" php bin/console doctrine:migrations:migrate --no-interaction || true
echo "Setup complete. Visit http://localhost:8080/health"
