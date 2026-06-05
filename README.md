# sf7/mysql — Symfony 7 + MySQL

Infraestructura lista para producción: Symfony 7.4 LTS, Doctrine ORM, MySQL 8.4.
Sin código de dominio — punto de partida limpio para añadir tus propios Bounded Contexts.

Para ver un ejemplo completo con dominio User + Product y tests, usa `sf7/mysql-example`.
Para la versión con Symfony 8, usa `sf8/mysql`.

---

## Stack

| Componente | Versión |
|---|---|
| PHP | 8.3 |
| Symfony | 7.4 LTS |
| Doctrine ORM | ^3.2 |
| Doctrine Migrations | ^3.4 |
| MySQL | 8.4 |
| PHPUnit | 11 |

**Docker:**
- `nginx:alpine` — servidor web (puerto 8080)
- `php:8.3-fpm-alpine` — PHP-FPM
- `php:8.3-fpm-alpine` (modo CLI) — comandos, composer, tests, migraciones
- `mysql:8.4` — base de datos principal (puerto 3306)
- `mysql:8.4` — base de datos de test (puerto 3307)

---

## Prerrequisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) o Docker Engine + Docker Compose v2
- `make` (en macOS viene con Xcode CLI tools; en Linux: `sudo apt install make`)

No necesitas PHP, Composer ni MySQL instalados localmente.

---

## Arrancar el proyecto por primera vez

```bash
# 1. Clona la rama
git clone -b sf7/mysql git@github.com:jousinho/base-project-with-claude.git mi-proyecto
cd mi-proyecto

# 2. Copia el fichero de variables de entorno
cp .env.example .env

# 3. Construye las imágenes Docker
make build

# 4. Arranca los contenedores en segundo plano
make up

# 5. Instala las dependencias PHP
make composer cmd=install

# 6. Crea los directorios de cache y logs
docker compose exec php-cli mkdir -p var/cache var/log && docker compose exec php-cli chmod -R 777 var

# 7. Ejecuta las migraciones (vacías en esta rama, pero el comando debe funcionar)
make migrate

# 8. Comprueba que todo funciona
curl http://localhost:8080/health
# Respuesta esperada: {"status":"ok"}
```

O en un solo paso:

```bash
cp .env.example .env && bash scripts/setup.sh
```

---

## Comandos del día a día

```bash
make up            # Arranca los contenedores
make down          # Para y elimina los contenedores
make build         # Reconstruye las imágenes
make logs          # Logs en tiempo real

make shell         # Shell en php-fpm
make cli           # Shell en php-cli

make console cmd=cache:clear          # Ejecuta bin/console
make composer cmd="require paquete"   # Ejecuta composer

make migrate       # Aplica migraciones pendientes
make migration     # Genera una nueva migración a partir del diff de entidades
make db            # Abre mysql CLI en la BD principal
make db-test       # Abre mysql CLI en la BD de test
```

---

## Tests

```bash
make test                # todos los tests
make test-unit           # suite Unit
make test-integration    # suite Integration (requiere BD de test)
make test-functional     # suite Functional (requiere BD de test)
```

La BD de test (`mysql_test`) está separada de la principal. PHPUnit apunta a ella
automáticamente mediante la variable `DATABASE_URL` definida en `phpunit.dist.xml`.

Los tests de integración usan `beginTransaction()` / `rollBack()` — nunca se limpia
la BD manualmente entre tests.

---

## Estructura de carpetas

```
src/
├── Kernel.php
└── Shared/
    └── Infrastructure/
        ├── Http/
        │   └── Controller/
        │       └── HealthController.php
        └── Persistence/
            └── Doctrine/
                └── Migrations/          ← migraciones generadas aquí

config/
├── bundles.php
├── routes.yaml
├── services.yaml
└── packages/
    ├── doctrine.yaml                    ← DBAL + ORM
    ├── doctrine_migrations.yaml         ← ruta de migraciones
    ├── framework.yaml
    └── routing.yaml

tests/
├── Unit/
├── Integration/                         ← tests contra BD real
└── Functional/                          ← tests HTTP end-to-end
```

---

## Añadir un Bounded Context

Esta rama es el punto de partida. Para añadir un BC (`User`, `Product`, etc.):

1. Crea la estructura de carpetas en `src/{BC}/Domain/`, `Application/`, `Infrastructure/`
2. Define las entidades con XML mappings en `src/{BC}/Infrastructure/Persistence/Doctrine/`
3. Registra el mapping en `config/packages/doctrine.yaml`
4. Registra el repositorio y el controller en `config/services.yaml`
5. Declara las rutas en `config/routes.yaml`
6. Genera la migración: `make migration`
7. Aplica la migración: `make migrate`

Ver `sf7/mysql-example` para un ejemplo completo con User + Product.

---

## Variables de entorno

| Variable | Valor por defecto | Descripción |
|---|---|---|
| `APP_ENV` | `dev` | Entorno de Symfony |
| `APP_SECRET` | `change_me_please` | Clave secreta — cambiar en producción |
| `APP_PORT` | `8080` | Puerto local de nginx |
| `DATABASE_URL` | `mysql://app:app@mysql:3306/app` | Conexión a la BD principal |

La BD de test se configura directamente en `phpunit.dist.xml` y apunta a `mysql_test:3306`.
