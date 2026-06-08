# sf8/postgresql-messenger-rabbitmq-example — Symfony 8 + PostgreSQL + Messenger (RabbitMQ transport) + DDD completo

Ejemplo de referencia con dominio User y Product implementado siguiendo DDD + Arquitectura Hexagonal,
más comunicación inter-BC vía Domain Events sobre Symfony Messenger con transport `amqp://`
sobre RabbitMQ (broker dedicado, procesamiento asíncrono vía worker).
Incluye entidades, Value Objects, Domain Events, Application Services, repositorios Doctrine y tests en los tres niveles.

Para partir de cero sin código de dominio, usa `sf8/postgresql-messenger-rabbitmq`.
Para la versión con Symfony 7, usa `sf7/postgresql-messenger-rabbitmq-example`.

---

## Stack

| Componente | Versión |
|---|---|
| PHP | 8.4 |
| Symfony | 8.1 |
| Doctrine ORM | ^3.6 |
| Doctrine Migrations | ^4.0 |
| Symfony Messenger | ^8.1 |
| symfony/amqp-messenger | ^8.1 |
| PostgreSQL | 16 |
| RabbitMQ | 3 (management) |
| PHPUnit | 11 |

**Transport Messenger:** `amqp://` (paquete `symfony/amqp-messenger`, requiere la extensión PHP `amqp`) — los mensajes se publican en un exchange/cola de RabbitMQ y se procesan de forma asíncrona mediante un worker (`messenger:consume`). A diferencia del transport `doctrine://`, el broker es un servicio dedicado: no añade tablas a la base de datos y soporta mayor throughput y patrones de enrutado más ricos (exchanges, routing keys, colas con prioridad/TTL/dead-lettering).

**Docker:**
- `nginx:alpine` — servidor web (puerto 8080)
- `php:8.4-fpm-alpine` — PHP-FPM (con extensión `amqp`)
- `php:8.4-fpm-alpine` (modo CLI) — comandos, composer, tests, migraciones
- `postgres:16-alpine` — base de datos principal (puerto 5432)
- `postgres:16-alpine` — base de datos de test (puerto 5433)
- `rabbitmq:3-management-alpine` — broker de mensajería (puerto 5672, panel de gestión en 15672, usuario/clave `app`/`app`)

---

## Prerrequisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) o Docker Engine + Docker Compose v2
- `make` (en macOS viene con Xcode CLI tools; en Linux: `sudo apt install make`)

No necesitas PHP, Composer ni PostgreSQL instalados localmente.

---

## Arrancar el proyecto por primera vez

```bash
# 1. Clona la rama
git clone -b sf8/postgresql-messenger-rabbitmq-example git@github.com:jousinho/base-project-with-claude.git mi-proyecto
cd mi-proyecto

# 2. Copia las variables de entorno
cp .env.example .env

# 3. Construye las imágenes y arranca
make build
make up

# 4. Instala las dependencias
make composer cmd=install

# 5. Aplica las migraciones (crea las tablas users y products)
make migrate

# 6. Comprueba que funciona
curl http://localhost:8080/health
curl http://localhost:8080/api/users
curl http://localhost:8080/api/products
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
make build         # Reconstruye las imágenes (necesario al cambiar Dockerfile)
make logs          # Logs en tiempo real

make shell         # Shell en php-fpm
make cli           # Shell en php-cli

make console cmd=cache:clear        # Ejecuta bin/console
make composer cmd="require paquete" # Ejecuta composer

make migrate       # Aplica migraciones pendientes
make migration     # Genera migración a partir del diff de entidades
make db            # psql en la BD principal
make db-test       # psql en la BD de test
```

---

## Tests

```bash
make test                # los tres niveles
make test-unit           # solo Unit (sin BD)
make test-integration    # solo Integration (BD de test)
make test-functional     # solo Functional (BD de test)
```

La BD de test está separada de la principal. PHPUnit apunta a ella automáticamente via `DATABASE_URL` en `phpunit.dist.xml`. Los tests de integración usan `beginTransaction()` / `rollBack()` — la BD nunca se limpia manualmente.

---

## Messenger — comunicación inter-BC vía Domain Events

`CreateUserService` despacha los Domain Events del agregado `User` al bus `event.bus` tras persistir:

```php
foreach ($user->pullDomainEvents() as $event) {
    $this->eventBus->dispatch($event);
}
```

Con el transport `amqp://`, el `dispatch()` no ejecuta el handler en el momento — serializa el evento (`UserWasCreated`) y lo publica en una cola de RabbitMQ (Symfony crea automáticamente el exchange y la cola la primera vez que se publica o consume). Un worker independiente lo recoge y lo procesa:

```bash
make console cmd="messenger:consume async -vv"
```

`UserWasCreatedHandler` (Bounded Context **Product**) está suscrito a `UserWasCreated` y, al consumirlo, crea un producto de bienvenida ("Welcome product for {userName}", 100 EUR). Es un ejemplo deliberadamente simple de comunicación entre BCs sin acoplamiento directo: `User` no conoce `Product`, solo declara que "un usuario fue creado"; `Product` decide reaccionar.

Para inspeccionar las colas y mensajes en tránsito, el panel de gestión de RabbitMQ está disponible en `http://localhost:15672` (usuario/clave `app`/`app`).

### Cómo testear el flujo asíncrono

`tests/Functional/User/UserControllerTest.php` cubre las dos mitades del flujo, sin mocks y contra el broker real:

- `test_create_user_endpoint__should_queue_user_was_created_event` — comprueba que tras el `POST /api/users` queda 1 mensaje publicado en la cola de RabbitMQ (consultando el conteo real vía `Symfony\Component\Messenger\Bridge\Amqp\Transport\Connection::countMessagesInQueues()`, construida a partir del DSN configurado)
- `test_consuming_user_was_created_event__should_trigger_default_product_creation` — ejecuta `messenger:consume` programáticamente vía `CommandTester` para procesar el mensaje pendiente, y comprueba que el handler creó el producto de bienvenida

Como el broker es un servicio externo y persiste estado entre ejecuciones (a diferencia de la BD de test, que se limpia con transacciones), el test purga las colas en `setUp()` antes de cada caso para garantizar aislamiento.

---

## Arquitectura

**DDD + Arquitectura Hexagonal.** El Bounded Context es la unidad de primer nivel.

```
Domain ← Application ← Infrastructure
```

- **Domain**: entidades, Value Objects, Domain Events, interfaces de repositorio. Sin dependencias externas.
- **Application**: Application Services que orquestan casos de uso. Reciben Commands, devuelven DTOs.
- **Infrastructure**: controllers HTTP, repositorios Doctrine, mapeos XML. La única capa que conoce Symfony y Doctrine.

### Decisiones de diseño

**Entidades almacenan primitivos.** Las entidades guardan strings e ints internamente; los accessors devuelven Value Objects. Esto evita custom DBAL types y embeddables innecesarios. Doctrine hidrata via reflexión sin necesidad de configuración extra.

**Commands para toda entrada a Application.** Tanto escritura (`CreateUserCommand`) como lectura (`GetUserCommand`) pasan por un Command, aunque tenga un solo campo. Garantiza que nunca llegan primitivos sueltos a la capa de aplicación.

**DTOs para toda salida de Application.** Los services devuelven DTOs, nunca entidades de dominio. El controller serializa el DTO directamente, sin acceder al dominio.

**Rutas con `#[Route]` en los controllers.** `config/routes.yaml` solo contiene los scanners por BC (`type: attribute`).

**Mapeos Doctrine en XML** en `src/{BC}/Infrastructure/Persistence/Doctrine/Mapping/`. Sin annotations ni attributes en las entidades de dominio.

---

## Estructura de carpetas

```
src/
├── Shared/
│   ├── Domain/
│   │   ├── ValueObject/Uuid.php          # Base para todos los IDs
│   │   └── Event/DomainEvent.php         # Base para todos los Domain Events
│   └── Infrastructure/
│       ├── Http/Controller/HealthController.php
│       └── Persistence/Doctrine/Migrations/
├── User/
│   ├── Domain/
│   │   ├── Entity/User.php
│   │   ├── ValueObject/               # UserId, Email, UserName, UserStatus
│   │   ├── Event/UserWasCreated.php
│   │   ├── Repository/UserRepositoryInterface.php
│   │   └── Exception/
│   ├── Application/
│   │   ├── Command/                   # CreateUserCommand, GetUserCommand
│   │   ├── DTO/UserDTO.php
│   │   └── Service/                   # CreateUserService, GetUserService, ListUsersService
│   └── Infrastructure/
│       ├── Http/Controller/UserController.php
│       └── Persistence/Doctrine/
│           ├── DoctrineUserRepository.php
│           └── Mapping/               # User.orm.xml (XML, sin annotations)
└── Product/
    ├── Domain/
    │   ├── Entity/Product.php
    │   ├── ValueObject/               # ProductId, ProductName, Money, Currency
    │   ├── Event/ProductWasCreated.php
    │   ├── Repository/ProductRepositoryInterface.php
    │   └── Exception/
    ├── Application/
    │   ├── Command/                   # CreateProductCommand, GetProductCommand
    │   ├── DTO/ProductDTO.php
    │   └── Service/                   # CreateProductService, GetProductService, ListProductsService
    └── Infrastructure/
        ├── Http/Controller/ProductController.php
        └── Persistence/Doctrine/
            ├── DoctrineProductRepository.php
            └── Mapping/               # Product.orm.xml
```

---

## Endpoints

### Health

```bash
GET /health
# {"status":"ok"}
```

### Users

```bash
# Crear usuario
POST /api/users
Content-Type: application/json
{"email": "john@example.com", "name": "John"}
# 201 + Location: /api/users/{id}
# 422 si el email no es válido
# 409 si el email ya existe

# Obtener usuario
GET /api/users/{id}
# 200 {"id":"...","email":"...","name":"...","status":"active"}
# 404 si no existe

# Listar usuarios
GET /api/users
# 200 [{"id":"...","email":"...","name":"...","status":"active"}, ...]
```

### Products

```bash
# Crear producto
POST /api/products
Content-Type: application/json
{"name": "Widget", "price_amount": 999, "price_currency": "EUR"}
# 201 + Location: /api/products/{id}
# 422 si el nombre está vacío o el precio es negativo

# Obtener producto
GET /api/products/{id}
# 200 {"id":"...","name":"...","price_amount":999,"price_currency":"EUR"}
# 404 si no existe

# Listar productos
GET /api/products
# 200 [{"id":"...","name":"...","price_amount":...,"price_currency":"..."}, ...]
```

---

## Variables de entorno

| Variable | Valor por defecto | Descripción |
|---|---|---|
| `APP_ENV` | `dev` | Entorno de Symfony |
| `APP_SECRET` | `change_me_please` | Clave secreta — cambiar en producción |
| `APP_PORT` | `8080` | Puerto local de nginx |
| `DATABASE_URL` | `postgresql://app:app@postgres:5432/app` | BD principal |
| `MESSENGER_TRANSPORT_DSN` | `amqp://app:app@rabbitmq:5672/%2f/messages` | Transporte de Messenger — cola publicada en el broker RabbitMQ |

La BD de test apunta a `postgres_test:5432` y se configura en `phpunit.dist.xml`.
