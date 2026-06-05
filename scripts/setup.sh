#!/usr/bin/env bash
set -euo pipefail

cp .env.example .env
docker compose build
docker compose up -d
docker compose exec php-cli composer install
docker compose exec php-cli mkdir -p var/cache var/log
docker compose exec php-cli chmod -R 777 var
echo "Setup complete. Visit http://localhost:8080/health"
