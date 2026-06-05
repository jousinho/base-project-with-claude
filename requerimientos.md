# Requerimientos — Symfony Base Project

## Visión general

Repositorio de referencia con múltiples ramas que representan combinaciones incrementales
de PHP, Symfony y features de infraestructura. Sirve como punto de partida para nuevos
proyectos: se parte de la rama que más se aproxima al stack deseado y se trabaja desde ahí.

---

## Stacks base

Dos líneas de soporte paralelas, diferenciadas por versión de Symfony:

| Base | PHP | Symfony | Soporte Symfony |
|------|-----|---------|-----------------|
| **sf7** | 8.3 | 7.4 LTS | Hasta Nov 2027 |
| **sf8** | 8.3 | 8.1 (última estable) | Estándar |

PHP 8.3 para ambas: estabilidad sin sacrificar características modernas (readonly classes,
typed constants, `#[\Override]`).

---

## Estructura de ramas

### Convención de nomenclatura

```
<base>/<feature>[-<feature2>...][-example]
```

Cada rama existe en dos variantes:
- **Sin sufijo** (`*/postgresql`) — solo infraestructura y configuración. Sin código de dominio.
  Punto de partida limpio para un proyecto real.
- **Con `-example`** (`*/postgresql-example`) — misma configuración más el dominio completo
  de User y Product con tests. Sirve como referencia y documentación viva.

### Ramas de cada base (×2 — una por sf7 y otra por sf8)

```
# ─── Base ─────────────────────────────────────────────────────────────────
sf7/main
sf7/main-example

# ─── Persistencia ──────────────────────────────────────────────────────────
sf7/postgresql
sf7/postgresql-example
sf7/mysql
sf7/mysql-example

# ─── [DESCARTADO] Messenger standalone (sin BD) ────────────────────────────
# Decisión: sin persistencia el ejemplo no tiene sentido real (no hay dominio
# que despachar ni efectos observables). Messenger solo existe en combinación
# con postgresql o mysql.
# sf7/messenger-sync             sf7/messenger-sync-example
# sf7/messenger-rabbitmq         sf7/messenger-rabbitmq-example
# sf7/messenger-redis            sf7/messenger-redis-example

# ─── [DESCARTADO] JWT standalone (sin BD) ───────────────────────────────────
# Decisión: sin BD no hay usuarios contra los que validar credenciales.
# JWT solo existe en combinación con postgresql o mysql.
# sf7/jwt                        sf7/jwt-example

# ─── PostgreSQL + Messenger ────────────────────────────────────────────────
sf7/postgresql-messenger-sync
sf7/postgresql-messenger-sync-example
sf7/postgresql-messenger-doctrine        # BD como cola de mensajes
sf7/postgresql-messenger-doctrine-example
sf7/postgresql-messenger-rabbitmq
sf7/postgresql-messenger-rabbitmq-example
sf7/postgresql-messenger-redis
sf7/postgresql-messenger-redis-example

# ─── MySQL + Messenger ─────────────────────────────────────────────────────
sf7/mysql-messenger-sync
sf7/mysql-messenger-sync-example
sf7/mysql-messenger-doctrine
sf7/mysql-messenger-doctrine-example
sf7/mysql-messenger-rabbitmq
sf7/mysql-messenger-rabbitmq-example
sf7/mysql-messenger-redis
sf7/mysql-messenger-redis-example

# ─── PostgreSQL + JWT ──────────────────────────────────────────────────────
sf7/postgresql-jwt
sf7/postgresql-jwt-example

# ─── MySQL + JWT ───────────────────────────────────────────────────────────
sf7/mysql-jwt
sf7/mysql-jwt-example

# ─── Stacks completos: PostgreSQL + Messenger + JWT ────────────────────────
sf7/postgresql-messenger-sync-jwt
sf7/postgresql-messenger-sync-jwt-example
sf7/postgresql-messenger-doctrine-jwt
sf7/postgresql-messenger-doctrine-jwt-example
sf7/postgresql-messenger-rabbitmq-jwt
sf7/postgresql-messenger-rabbitmq-jwt-example
sf7/postgresql-messenger-redis-jwt
sf7/postgresql-messenger-redis-jwt-example

# ─── Stacks completos: MySQL + Messenger + JWT ─────────────────────────────
sf7/mysql-messenger-sync-jwt
sf7/mysql-messenger-sync-jwt-example
sf7/mysql-messenger-doctrine-jwt
sf7/mysql-messenger-doctrine-jwt-example
sf7/mysql-messenger-rabbitmq-jwt
sf7/mysql-messenger-rabbitmq-jwt-example
sf7/mysql-messenger-redis-jwt
sf7/mysql-messenger-redis-jwt-example

# ─── CQRS (solo con ejemplo, incluye Elasticsearch) ────────────────────────
sf7/postgresql-cqrs
sf7/postgresql-cqrs-example
sf7/mysql-cqrs
sf7/mysql-cqrs-example

# Mismas 48 ramas duplicadas con prefijo sf8/
```

**Total: 96 ramas** (48 por cada base de Symfony).

**Notas:**
- Messenger y JWT **siempre** van en combinación con `postgresql` o `mysql`. Las variantes standalone fueron descartadas (ver secciones marcadas como `[DESCARTADO]` arriba).
- `messenger-doctrine` necesita una BD para persistir la cola — por eso solo existe en combinación con `postgresql` o `mysql`.
- Las ramas CQRS incluyen Elasticsearch como read model. Solo existen para PostgreSQL y MySQL.

### Rama por defecto del repositorio

`sf8/postgresql-messenger-rabbitmq-jwt-example` — stack completo con ejemplo funcional.

---

## Arquitectura del código

**DDD + Arquitectura Hexagonal (Ports & Adapters)** en todas las ramas, sin excepción.

### Estructura de carpetas

El Bounded Context es la unidad de primer nivel. Cada BC contiene sus propias capas,
lo que lo hace autónomo y extraíble a un microservicio sin refactor mayor.

```
src/
├── User/                           # Bounded Context: User
│   ├── Domain/
│   │   ├── Entity/                 # User (Aggregate Root)
│   │   ├── ValueObject/            # UserId, Email, UserName, UserStatus
│   │   ├── Repository/             # UserRepositoryInterface
│   │   └── Event/                  # UserWasCreated, UserStatusChanged
│   ├── Application/
│   │   ├── Command/                # CreateUserCommand, GetUserCommand (toda entrada al service)
│   │   ├── DTO/                    # UserDTO (toda salida del service hacia infra)
│   │   └── Service/                # CreateUserService, GetUserService, ListUsersService
│   └── Infrastructure/
│       ├── Http/Controller/        # UserController
│       └── Persistence/Doctrine/   # DoctrineUserRepository + mappings XML
├── Product/                        # Bounded Context: Product
│   ├── Domain/
│   │   ├── Entity/                 # Product (Aggregate Root)
│   │   ├── ValueObject/            # ProductId, ProductName, Money, Currency
│   │   ├── Repository/             # ProductRepositoryInterface
│   │   └── Event/                  # ProductWasCreated, ProductPriceChanged
│   ├── Application/
│   │   ├── Command/                # CreateProductCommand, GetProductCommand
│   │   ├── DTO/                    # ProductDTO
│   │   └── Service/                # CreateProductService, GetProductService, ListProductsService
│   └── Infrastructure/
│       ├── Http/Controller/        # ProductController
│       └── Persistence/Doctrine/   # DoctrineProductRepository + mappings XML
└── Shared/                         # Shared Kernel — solo lo verdaderamente compartido
    ├── Domain/
    │   └── ValueObject/            # Uuid (base), DomainEvent (base)
    └── Infrastructure/
        └── Persistence/Doctrine/
            └── Migrations/
```

### Reglas de dependencia

```
BC/Domain ← BC/Application ← BC/Infrastructure
```

- Un BC **nunca** importa clases de otro BC directamente.
- La comunicación inter-BC va siempre a través de Domain Events despachados por Messenger.
- `Shared/` solo contiene abstracciones base (interfaces, clases abstractas, Value Objects
  genéricos como UUID). Nunca lógica de negocio de ningún BC.
- El dominio no importa ningún framework, ORM ni broker.
- Controllers solo orquestan: reciben request, construyen Command, llaman Application Service, serializan DTO de respuesta.
- Application Services reciben **Commands** (lectura y escritura) y devuelven **DTOs** — nunca entidades de dominio.
- Sin lógica de negocio fuera del dominio.

---

## Dominio de ejemplo

### Contexto User

| Elemento | Detalle |
|----------|---------|
| `UserId` | Value Object — UUID v4 |
| `Email` | Value Object — validación formato |
| `UserName` | Value Object — no vacío, máx 100 chars |
| `UserStatus` | Value Object — enum backed: `active`, `inactive` |
| `User` | Aggregate Root — factory `create(UserId, Email, UserName): self` |
| `UserWasCreated` | Domain Event — emitido en `User::create()` |
| `UserRepositoryInterface` | Puerto — `save(User)`, `findById(UserId)`, `findByEmail(Email)` |

**Endpoints REST expuestos:**

```
POST   /api/users          # Crear usuario
GET    /api/users/{id}     # Obtener usuario por ID
GET    /api/users          # Listar usuarios (paginado)
```

### Contexto Product

| Elemento | Detalle |
|----------|---------|
| `ProductId` | Value Object — UUID v4 |
| `ProductName` | Value Object — no vacío, máx 200 chars |
| `Money` | Value Object — amount (int centavos) + Currency |
| `Currency` | Value Object — enum backed: `EUR`, `USD` |
| `Product` | Aggregate Root — factory `create(ProductId, ProductName, Money): self` |
| `ProductWasCreated` | Domain Event — emitido en `Product::create()` |
| `ProductRepositoryInterface` | Puerto — `save(Product)`, `findById(ProductId)` |

**Endpoints REST expuestos:**

```
POST   /api/products       # Crear producto
GET    /api/products/{id}  # Obtener producto por ID
GET    /api/products       # Listar productos (paginado)
```

---

## Convenciones PHP / Symfony

- Constructor **privado** + factory method estático `create(...): self`
- Getters **sin prefijo `get`**: `email()`, `status()`, nunca `getEmail()`
- `match` nunca como nombre de clase o variable (palabra reservada PHP 8)
- Sin comentarios salvo que la lógica no sea evidente
- Sin docblocks en código no modificado
- Domain Events con campo `occurredOn: DateTimeImmutable`
- `config/services.yaml`: bindings explícitos interface → implementación Doctrine
- Rutas declaradas con `#[Route]` directamente en los controllers
- `config/routes.yaml`: solo scanners por BC (`type: attribute`), sin rutas individuales
- Controllers registrados explícitamente en `services.yaml`
- Migraciones en `src/Shared/Infrastructure/Persistence/Doctrine/Migrations/`

### `final` en entidades — diferencia entre sf7 y sf8

Las entidades (`User`, `Product`) son **`final` en ramas `sf8`** pero **no en ramas `sf7`**.

**Por qué:** PHP 8.4 introdujo lazy ghost objects nativos, que Doctrine usa para lazy
loading sin necesidad de herencia. Esto hace que `final` sea compatible con Doctrine ORM 3.x.
PHP 8.3 no tiene lazy ghost objects, por lo que Doctrine genera proxies extendiendo la
clase — incompatible con `final`.

**Consecuencia práctica:** en ramas `sf7` las entidades son extensibles por diseño de
plataforma, no por elección arquitectónica. El resto de clases (`final` en Value Objects,
Services, Controllers, etc.) no se ven afectadas porque Doctrine nunca genera proxies
para ellas.

---

## Features por rama

### `*/main` — Base Symfony

Paquetes incluidos:
- `symfony/framework-bundle`
- `symfony/console`
- `symfony/dotenv`
- `symfony/yaml`
- `symfony/serializer`
- `symfony/uid`

Sin base de datos. Endpoint de healthcheck: `GET /health` → `{"status":"ok"}`.

---

### `*/postgresql` y `*/mysql` — Persistencia

Paquetes adicionales:
- `doctrine/orm`
- `doctrine/doctrine-bundle`
- `doctrine/doctrine-migrations-bundle`

Versiones según base:

| Base | doctrine/orm | doctrine/doctrine-bundle | doctrine/doctrine-migrations-bundle |
|------|-------------|--------------------------|--------------------------------------|
| `sf8` | `^3.6` | `^3.2` | `^4.0` |
| `sf7` | `^3.2` | `^2.13` | `^3.4` |

Las versiones `^3.x` de `doctrine-bundle` y `^4.0` de `doctrine-migrations-bundle`
requieren PHP `^8.4` — incompatibles con las ramas `sf7` (PHP 8.3).

Ambos contextos (User + Product) completamente implementados con CRUD.

Mapeos Doctrine via XML (no annotations) en `{BC}/Infrastructure/Persistence/Doctrine/`.

---

### `*/messenger-*` — Mensajería (cuatro variantes de transport)

Paquete base común: `symfony/messenger`.

Messenger **siempre** aparece en combinación con `postgresql` o `mysql` — no existe como
rama standalone. Al crear un User, el `UserWasCreated` Domain Event se despacha como
mensaje; el Product BC tiene un handler que crea un producto por defecto vinculado al
usuario, demostrando comunicación real inter-BC.

| Rama | Transport | Paquete extra | Docker extra |
|------|-----------|---------------|-------------|
| `*-messenger-sync` | `sync://` | — | Ninguno |
| `*-messenger-doctrine` | `doctrine://` | — | Ninguno (usa la BD del BC) |
| `*-messenger-rabbitmq` | `amqp://` | `symfony/amqp-messenger` | RabbitMQ (5672 + UI 15672) |
| `*-messenger-redis` | `redis://` | `symfony/redis-messenger` | Redis (6379) |

`messenger-doctrine` usa la propia BD del proyecto para persistir la cola de mensajes.

---

### `*/postgresql-cqrs` y `*/mysql-cqrs` — CQRS con Elasticsearch

CQRS completo con modelos de lectura y escritura separados:

```
Command → CommandHandler → BD relacional → Domain Event
                                               ↓
                                    ProjectionHandler → Elasticsearch
Query  → QueryHandler   → Elasticsearch → Response DTO
```

**Modelo de escritura:** Doctrine sobre PostgreSQL o MySQL (igual que las ramas de persistencia).

**Modelo de lectura:** Elasticsearch — índice por BC, desnormalizado, listo para servir.

**Estructura de Application** (cambia respecto al resto de ramas):

```
User/
├── Application/
│   ├── Command/
│   │   └── CreateUser/
│   │       ├── CreateUserCommand.php           # datos de entrada (readonly)
│   │       └── CreateUserCommandHandler.php    # escribe en BD, despacha evento
│   └── Query/
│       └── GetUser/
│           ├── GetUserQuery.php
│           ├── GetUserQueryHandler.php          # lee de Elasticsearch
│           └── UserResponse.php                # DTO de salida
└── Infrastructure/
    ├── Persistence/Doctrine/                   # write: DoctrineUserRepository
    ├── Persistence/Elasticsearch/              # read: ElasticsearchUserFinder
    └── Projection/                             # UserProjectionHandler (evento → Elastic)
```

**Dos puertos en Domain/Repository/:**
- `UserRepositoryInterface` — escritura (save, findById para writes)
- `UserFinderInterface` — lectura (findById, findAll para queries, devuelve arrays/DTOs)

**Buses en messenger.yaml:**
```yaml
framework:
    messenger:
        buses:
            command.bus: ~
            query.bus:   ~
            event.bus:
                default_middleware:
                    allow_no_handlers: true
```

**Paquetes adicionales:**
- `symfony/messenger`
- `elasticsearch/elasticsearch` (cliente oficial)

**Docker añade:** `elasticsearch:8-alpine` (puerto 9200) + `kibana` opcional (5601).

---

### `*/jwt` — Autenticación JWT

JWT **siempre** aparece en combinación con `postgresql` o `mysql` — no existe como rama
standalone (sin BD no hay usuarios contra los que validar credenciales).

Paquetes adicionales:
- `lexik/jwt-authentication-bundle`
- `symfony/security-bundle`

Endpoints de autenticación:
```
POST /api/auth/login     # Devuelve JWT token
```

Rutas `/api/users` y `/api/products` protegidas con `IS_AUTHENTICATED_FULLY`.

---

## Docker

### Servicios base (todas las ramas)

```yaml
nginx       # Alpine, puerto 8080→80
php-fpm     # PHP 8.3-fpm-alpine
php-cli     # Misma imagen, tail -f /dev/null (comandos Symfony, composer, tests)
```

### Servicios adicionales por feature

| Feature | Servicio añadido |
|---------|-----------------|
| postgresql | `postgres:16-alpine` (puerto 5432) + `postgres_test:16-alpine` (5433) |
| mysql | `mysql:8.4` (puerto 3306) + `mysql_test:8.4` (3307) |
| messenger-rabbitmq | `rabbitmq:3-management-alpine` (5672 + management UI 15672) |
| messenger-redis | `redis:7-alpine` (puerto 6379) |
| cqrs | `elasticsearch:8` (puerto 9200) |

### Convenciones Docker

- Healthchecks en todos los servicios con dependencias
- `.env.example` junto a cada `.env`
- `php-fpm` y `php-cli` con la **misma imagen** (un solo Dockerfile)
- `php-cli` sin entrypoint de FPM: solo `tail -f /dev/null`
- Volúmenes nombrados para datos persistentes
- Red bridge interna `app`

### Dockerfile PHP

```dockerfile
FROM php:8.3-fpm-alpine

# Extensions base (todas las ramas):
# pdo, intl, opcache, zip, bcmath, sodium

# Extensions adicionales por feature:
# postgresql: pdo_pgsql, pgsql
# mysql: pdo_mysql
# messenger-rabbitmq: amqp, sockets

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/symfony.ini
WORKDIR /var/www/html
```

---

## Testing

Tres niveles en todas las ramas con persistencia (postgresql/mysql y combinaciones).
Ramas sin DB solo tienen tests unitarios.

### Nomenclatura obligatoria

```
test_{acción}_{contexto}__should_{resultado_esperado}
test_{acción}_{contexto}__when_{condición}__should_{resultado_esperado}
```

### Unit (`tests/Unit/`)

Prueban dominio puro: Entity, Value Objects, Domain Services. Todo externo mockeado.

Ejemplos de tests a incluir:
- `test_create_user__should_emit_domain_event`
- `test_create_user__when_invalid_email__should_throw_exception`
- `test_create_product__when_negative_price__should_throw_exception`
- `test_money_value_object__when_different_currencies__should_throw_on_addition`

### Integration (`tests/Integration/`)

Prueban repositorios Doctrine y Application Services contra BD real.
Usar `beginTransaction()` / `rollBack()` — nunca limpiar la BD manualmente.

Ejemplos:
- `test_save_user__should_persist_and_retrieve_by_id`
- `test_find_user_by_email__when_not_exists__should_return_null`
- `test_create_user_service__should_save_user`

### Functional (`tests/Functional/`)

Prueban controllers HTTP end-to-end. Sin mocks.

Ejemplos:
- `test_create_user_endpoint__should_return_201_with_location_header`
- `test_create_user_endpoint__when_duplicate_email__should_return_409`
- `test_get_user_endpoint__when_not_found__should_return_404`

### Configuración PHPUnit

- `phpunit.dist.xml` con tres test suites: Unit, Integration, Functional
- Variable de entorno `DATABASE_URL` en `phpunit.dist.xml` apuntando a BD de test
- BD de test separada del contenedor principal

---

## Makefile

Targets estándar en todas las ramas:

```makefile
up              # docker compose up -d
down            # docker compose down
build           # docker compose build
shell           # exec php-fpm sh
cli             # exec php-cli sh
console         # exec php-cli php bin/console $(cmd)
composer        # exec php-cli composer $(cmd)
test            # exec php-cli php bin/phpunit
test-unit       # exec php-cli php bin/phpunit --testsuite Unit
test-integration  # exec php-cli php bin/phpunit --testsuite Integration
test-functional   # exec php-cli php bin/phpunit --testsuite Functional
migrate         # exec php-cli php bin/console doctrine:migrations:migrate --no-interaction
migration       # exec php-cli php bin/console doctrine:migrations:diff
db              # exec postgres/mysql en prod
db-test         # exec postgres/mysql en test
logs            # docker compose logs -f
```

---

## Scripts de utilidad

`scripts/` en la raíz del proyecto:

| Script | Descripción |
|--------|-------------|
| `setup.sh` | Primera configuración: copia `.env.example` → `.env`, build, up, instala deps, migra |
| `test-all.sh` | Ejecuta los tres niveles de test con separación visual |
| `make-migration.sh` | Wrapper de `doctrine:migrations:diff` |

---

## Plan de implementación por fases

El trabajo se organiza en iteraciones. Cada iteración produce ramas funcionales con
tests pasando. **No se avanza a la siguiente hasta que la anterior está verde.**

En cada fase, el orden es siempre: rama clean → rama example → equivalente en sf7.

### Fase 1 — Base

1. `sf8/main` + `sf8/main-example`: Symfony mínimo, Docker, healthcheck, Makefile
2. `sf7/main` + `sf7/main-example`: misma config con Symfony 7.4

### Fase 2 — Persistencia

3. `sf8/postgresql` + `sf8/postgresql-example`
4. `sf8/mysql` + `sf8/mysql-example`
5. Equivalentes en sf7

### ~~Fase 3 — Messenger standalone~~ *(descartada)*

> Messenger standalone eliminado. Messenger siempre va en combinación con BD.
> Ver sección "Estructura de ramas" para el razonamiento.

### Fase 3 — PostgreSQL + Messenger

6. Las cuatro variantes de transport (`sync`, `doctrine`, `rabbitmq`, `redis`) + sus `-example`
7. Equivalentes en sf7

### Fase 4 — MySQL + Messenger

8. Las cuatro variantes + sus `-example`
9. Equivalentes en sf7

### Fase 5 — JWT

> JWT standalone eliminado. JWT siempre va en combinación con BD.

10. `sf8/postgresql-jwt` + example, `sf8/mysql-jwt` + example
11. Equivalentes en sf7

### Fase 6 — Stacks completos (Messenger + JWT)

12. Las ocho combinaciones postgresql + messenger-* + jwt + sus `-example`
13. Las ocho combinaciones mysql + messenger-* + jwt + sus `-example`
14. Equivalentes en sf7

### Fase 7 — CQRS

15. `sf8/postgresql-cqrs` + `sf8/postgresql-cqrs-example`
16. `sf8/mysql-cqrs` + `sf8/mysql-cqrs-example`
17. Equivalentes en sf7
18. Revisión general: README en cada rama documenta stack, endpoints y cómo arrancarlo

---

## README por rama

Cada rama tendrá un `README.md` que incluye:

1. Stack exacto (PHP version, Symfony version, paquetes adicionales)
2. Prerrequisitos (Docker, Make)
3. Quick start: `git clone`, `make setup` o comandos manuales, `make up`
4. Cómo ejecutar tests
5. Estructura de carpetas relevante para esa rama
6. Endpoints disponibles con ejemplos `curl`

---

## Definition of Done por rama

Antes de considerar una rama terminada:

- [ ] `make up` arranca sin errores
- [ ] Healthcheck responde en `GET /health`
- [ ] Tests pasan (`make test`) sin warnings
- [ ] Nomenclatura de tests correcta
- [ ] Estructura DDD respetada
- [ ] Value Objects para todos los datos con reglas de negocio
- [ ] Sin lógica de negocio en controllers ni infraestructura
- [ ] Sin comentarios innecesarios ni docblocks en código no modificado
- [ ] `.env.example` presente y actualizado
- [ ] README de la rama actualizado
