# Symfony Base Project

Repositorio de referencia con múltiples ramas que representan combinaciones incrementales
de PHP, Symfony y features de infraestructura.

**Cómo usarlo:** elige la rama que más se aproxima al stack que necesitas, clónala y
arranca desde ahí.

---

## Stacks base

| Base | PHP | Symfony |
|------|-----|---------|
| `sf7` | 8.3 | 7.4 LTS (soporte hasta Nov 2027) |
| `sf8` | 8.3 | 8.x (última estable) |

---

## Variantes de rama

Cada rama existe en dos variantes:

- **Clean** (`sf8/postgresql`) — solo infraestructura y configuración. Sin código de dominio. Punto de partida limpio para un proyecto real.
- **Example** (`sf8/postgresql-example`) — misma configuración más dominio completo de `User` y `Product` con tests. Sirve como referencia.

---

## Índice de ramas

### Base

| Rama | Descripción |
|------|-------------|
| `sf7/main` | Symfony 7 mínimo · framework + console + dotenv · healthcheck |
| `sf7/main-example` | + ejemplo de healthcheck documentado |
| `sf8/main` | Symfony 8 mínimo · framework + console + dotenv · healthcheck |
| `sf8/main-example` | + ejemplo de healthcheck documentado |

---

### Persistencia

| Rama | Descripción |
|------|-------------|
| `sf7/postgresql` | Symfony 7 + Doctrine ORM + PostgreSQL |
| `sf7/postgresql-example` | + dominio User & Product con CRUD y tests |
| `sf7/mysql` | Symfony 7 + Doctrine ORM + MySQL |
| `sf7/mysql-example` | + dominio User & Product con CRUD y tests |
| `sf8/postgresql` | Symfony 8 + Doctrine ORM + PostgreSQL |
| `sf8/postgresql-example` | + dominio User & Product con CRUD y tests |
| `sf8/mysql` | Symfony 8 + Doctrine ORM + MySQL |
| `sf8/mysql-example` | + dominio User & Product con CRUD y tests |

---

### Messenger (standalone, sin BD)

| Rama | Transport | Docker extra |
|------|-----------|-------------|
| `sf7/messenger-sync` | `sync://` | — |
| `sf7/messenger-sync-example` | `sync://` | — |
| `sf7/messenger-rabbitmq` | `amqp://` | RabbitMQ |
| `sf7/messenger-rabbitmq-example` | `amqp://` | RabbitMQ |
| `sf7/messenger-redis` | `redis://` | Redis |
| `sf7/messenger-redis-example` | `redis://` | Redis |
| `sf8/messenger-sync` | `sync://` | — |
| `sf8/messenger-sync-example` | `sync://` | — |
| `sf8/messenger-rabbitmq` | `amqp://` | RabbitMQ |
| `sf8/messenger-rabbitmq-example` | `amqp://` | RabbitMQ |
| `sf8/messenger-redis` | `redis://` | Redis |
| `sf8/messenger-redis-example` | `redis://` | Redis |

---

### JWT (standalone, sin BD)

| Rama | Descripción |
|------|-------------|
| `sf7/jwt` | Symfony 7 + LexikJWTAuthenticationBundle |
| `sf7/jwt-example` | + endpoints protegidos de ejemplo |
| `sf8/jwt` | Symfony 8 + LexikJWTAuthenticationBundle |
| `sf8/jwt-example` | + endpoints protegidos de ejemplo |

---

### PostgreSQL + Messenger

| Rama | Transport | Docker extra |
|------|-----------|-------------|
| `sf7/postgresql-messenger-sync` | `sync://` | — |
| `sf7/postgresql-messenger-sync-example` | `sync://` | — |
| `sf7/postgresql-messenger-doctrine` | `doctrine://` (BD como cola) | — |
| `sf7/postgresql-messenger-doctrine-example` | `doctrine://` | — |
| `sf7/postgresql-messenger-rabbitmq` | `amqp://` | RabbitMQ |
| `sf7/postgresql-messenger-rabbitmq-example` | `amqp://` | RabbitMQ |
| `sf7/postgresql-messenger-redis` | `redis://` | Redis |
| `sf7/postgresql-messenger-redis-example` | `redis://` | Redis |
| `sf8/postgresql-messenger-sync` | `sync://` | — |
| `sf8/postgresql-messenger-sync-example` | `sync://` | — |
| `sf8/postgresql-messenger-doctrine` | `doctrine://` | — |
| `sf8/postgresql-messenger-doctrine-example` | `doctrine://` | — |
| `sf8/postgresql-messenger-rabbitmq` | `amqp://` | RabbitMQ |
| `sf8/postgresql-messenger-rabbitmq-example` | `amqp://` | RabbitMQ |
| `sf8/postgresql-messenger-redis` | `redis://` | Redis |
| `sf8/postgresql-messenger-redis-example` | `redis://` | Redis |

---

### MySQL + Messenger

| Rama | Transport | Docker extra |
|------|-----------|-------------|
| `sf7/mysql-messenger-sync` | `sync://` | — |
| `sf7/mysql-messenger-sync-example` | `sync://` | — |
| `sf7/mysql-messenger-doctrine` | `doctrine://` | — |
| `sf7/mysql-messenger-doctrine-example` | `doctrine://` | — |
| `sf7/mysql-messenger-rabbitmq` | `amqp://` | RabbitMQ |
| `sf7/mysql-messenger-rabbitmq-example` | `amqp://` | RabbitMQ |
| `sf7/mysql-messenger-redis` | `redis://` | Redis |
| `sf7/mysql-messenger-redis-example` | `redis://` | Redis |
| `sf8/mysql-messenger-sync` | `sync://` | — |
| `sf8/mysql-messenger-sync-example` | `sync://` | — |
| `sf8/mysql-messenger-doctrine` | `doctrine://` | — |
| `sf8/mysql-messenger-doctrine-example` | `doctrine://` | — |
| `sf8/mysql-messenger-rabbitmq` | `amqp://` | RabbitMQ |
| `sf8/mysql-messenger-rabbitmq-example` | `amqp://` | RabbitMQ |
| `sf8/mysql-messenger-redis` | `redis://` | Redis |
| `sf8/mysql-messenger-redis-example` | `redis://` | Redis |

---

### PostgreSQL + JWT

| Rama | Descripción |
|------|-------------|
| `sf7/postgresql-jwt` | Symfony 7 + PostgreSQL + JWT Auth |
| `sf7/postgresql-jwt-example` | + User & Product con endpoints protegidos |
| `sf8/postgresql-jwt` | Symfony 8 + PostgreSQL + JWT Auth |
| `sf8/postgresql-jwt-example` | + User & Product con endpoints protegidos |

---

### MySQL + JWT

| Rama | Descripción |
|------|-------------|
| `sf7/mysql-jwt` | Symfony 7 + MySQL + JWT Auth |
| `sf7/mysql-jwt-example` | + User & Product con endpoints protegidos |
| `sf8/mysql-jwt` | Symfony 8 + MySQL + JWT Auth |
| `sf8/mysql-jwt-example` | + User & Product con endpoints protegidos |

---

### PostgreSQL + Messenger + JWT (stacks completos)

| Rama | Transport | Docker extra |
|------|-----------|-------------|
| `sf7/postgresql-messenger-sync-jwt` | `sync://` | — |
| `sf7/postgresql-messenger-sync-jwt-example` | `sync://` | — |
| `sf7/postgresql-messenger-doctrine-jwt` | `doctrine://` | — |
| `sf7/postgresql-messenger-doctrine-jwt-example` | `doctrine://` | — |
| `sf7/postgresql-messenger-rabbitmq-jwt` | `amqp://` | RabbitMQ |
| `sf7/postgresql-messenger-rabbitmq-jwt-example` | `amqp://` | RabbitMQ |
| `sf7/postgresql-messenger-redis-jwt` | `redis://` | Redis |
| `sf7/postgresql-messenger-redis-jwt-example` | `redis://` | Redis |
| `sf8/postgresql-messenger-sync-jwt` | `sync://` | — |
| `sf8/postgresql-messenger-sync-jwt-example` | `sync://` | — |
| `sf8/postgresql-messenger-doctrine-jwt` | `doctrine://` | — |
| `sf8/postgresql-messenger-doctrine-jwt-example` | `doctrine://` | — |
| `sf8/postgresql-messenger-rabbitmq-jwt` | `amqp://` | RabbitMQ |
| `sf8/postgresql-messenger-rabbitmq-jwt-example` | `amqp://` | RabbitMQ |
| `sf8/postgresql-messenger-redis-jwt` | `redis://` | Redis |
| `sf8/postgresql-messenger-redis-jwt-example` | `redis://` | Redis |

---

### MySQL + Messenger + JWT (stacks completos)

| Rama | Transport | Docker extra |
|------|-----------|-------------|
| `sf7/mysql-messenger-sync-jwt` | `sync://` | — |
| `sf7/mysql-messenger-sync-jwt-example` | `sync://` | — |
| `sf7/mysql-messenger-doctrine-jwt` | `doctrine://` | — |
| `sf7/mysql-messenger-doctrine-jwt-example` | `doctrine://` | — |
| `sf7/mysql-messenger-rabbitmq-jwt` | `amqp://` | RabbitMQ |
| `sf7/mysql-messenger-rabbitmq-jwt-example` | `amqp://` | RabbitMQ |
| `sf7/mysql-messenger-redis-jwt` | `redis://` | Redis |
| `sf7/mysql-messenger-redis-jwt-example` | `redis://` | Redis |
| `sf8/mysql-messenger-sync-jwt` | `sync://` | — |
| `sf8/mysql-messenger-sync-jwt-example` | `sync://` | — |
| `sf8/mysql-messenger-doctrine-jwt` | `doctrine://` | — |
| `sf8/mysql-messenger-doctrine-jwt-example` | `doctrine://` | — |
| `sf8/mysql-messenger-rabbitmq-jwt` | `amqp://` | RabbitMQ |
| `sf8/mysql-messenger-rabbitmq-jwt-example` | `amqp://` | RabbitMQ |
| `sf8/mysql-messenger-redis-jwt` | `redis://` | Redis |
| `sf8/mysql-messenger-redis-jwt-example` | `redis://` | Redis |

---

### CQRS con Elasticsearch

CQRS completo: escrituras en BD relacional, lecturas desde Elasticsearch (proyecciones).

| Rama | Descripción |
|------|-------------|
| `sf7/postgresql-cqrs` | Symfony 7 + PostgreSQL (write) + Elasticsearch (read) |
| `sf7/postgresql-cqrs-example` | + dominio User & Product con Commands, Queries y Projections |
| `sf7/mysql-cqrs` | Symfony 7 + MySQL (write) + Elasticsearch (read) |
| `sf7/mysql-cqrs-example` | + dominio User & Product con Commands, Queries y Projections |
| `sf8/postgresql-cqrs` | Symfony 8 + PostgreSQL (write) + Elasticsearch (read) |
| `sf8/postgresql-cqrs-example` | + dominio User & Product con Commands, Queries y Projections |
| `sf8/mysql-cqrs` | Symfony 8 + MySQL (write) + Elasticsearch (read) |
| `sf8/mysql-cqrs-example` | + dominio User & Product con Commands, Queries y Projections |

---

## Prerrequisitos

- Docker + Docker Compose
- Make

## Quick start

```bash
git clone <repo> -b sf8/postgresql-example
cd symfony-base
make setup   # copia .env.example, build, up, composer install, migraciones
make test    # ejecuta todos los tests
```

## Arquitectura

Todas las ramas siguen **DDD + Arquitectura Hexagonal** con el Bounded Context como
unidad de primer nivel:

```
src/
├── User/
│   ├── Domain/
│   ├── Application/
│   └── Infrastructure/
├── Product/
│   ├── Domain/
│   ├── Application/
│   └── Infrastructure/
└── Shared/
```

Un BC nunca importa clases de otro directamente. La comunicación inter-BC va por
Domain Events + Symfony Messenger.
