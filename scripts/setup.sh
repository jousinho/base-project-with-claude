#!/usr/bin/env bash
set -euo pipefail

cp .env.example .env
docker compose build
docker compose up -d
docker compose exec php-cli composer install
echo "Setup complete. Visit http://localhost:8080/health"
