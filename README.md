# sf7/postgresql-messenger-redis — Symfony 7 + PostgreSQL + Messenger (Redis transport)

Infraestructura lista para producción: Symfony 7.4 LTS, Doctrine ORM, PostgreSQL 16, Symfony Messenger con transport `redis://` sobre Redis (broker dedicado, procesamiento asíncrono vía worker).
Sin código de dominio — punto de partida limpio para añadir tus propios Bounded Contexts.

Para ver un ejemplo completo con dominio User + Product y comunicación inter-BC, usa `sf7/postgresql-messenger-redis-example`.
Para la versión con Symfony 8, usa `sf8/postgresql-messenger-redis`.

---

## Stack

| Componente | Versión |
|---|---|
| PHP | 8.3 |
| Symfony | 7.4 LTS |
| Doctrine ORM | ^3.2 |
| Doctrine Migrations | ^3.4 |
| Symfony Messenger | ^7.4 |
| symfony/redis-messenger | ^7.4 |
| PostgreSQL | 16 |
| Redis | alpine |
| PHPUnit | 11 |

**Transport Messenger:** `redis://` (paquete `symfony/redis-messenger`, requiere la extensión PHP `redis`) — los mensajes se publican en un Redis Stream (XADD) y se procesan de forma asíncrona mediante un worker (`messenger:consume`). A diferencia del transport `doctrine://`, el broker es un servicio dedicado: no añade tablas a la base de datos. A diferencia del transport `amqp://`, no requiere un broker complejo — Redis es un proceso único, extremadamente rápido y con soporte nativo de streams desde Redis 5.

**Docker:**
- `nginx:alpine` — servidor web (puerto 8080)
- `php:8.3-fpm-alpine` — PHP-FPM (con extensión `redis`)
- `php:8.3-fpm-alpine` (modo CLI) — comandos, composer, tests, migraciones
- `postgres:16-alpine` — base de datos principal (puerto 5432)
- `postgres:16-alpine` — base de datos de test (puerto 5433)
- `redis:alpine` — broker de mensajería (puerto 6379)

---

## Prerrequisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) o Docker Engine + Docker Compose v2
- `make` (en macOS viene con Xcode CLI tools; en Linux: `sudo apt install make`)

No necesitas PHP, Composer ni PostgreSQL instalados localmente.

---

## Arrancar el proyecto por primera vez

```bash
# 1. Clona la rama
git clone -b sf7/postgresql-messenger-redis git@github.com:jousinho/base-project-with-claude.git mi-proyecto
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
make db            # Abre psql en la BD principal
make db-test       # Abre psql en la BD de test
```

---

## Tests

```bash
make test                # todos los tests
make test-unit           # suite Unit
make test-integration    # suite Integration (requiere BD de test)
make test-functional     # suite Functional (requiere BD de test)
```

La BD de test (`postgres_test`) está separada de la principal. PHPUnit apunta a ella
automáticamente mediante la variable `DATABASE_URL` definida en `phpunit.dist.xml`.

Los tests de integración usan `beginTransaction()` / `rollBack()` — nunca se limpia
la BD manualmente entre tests.

---

## Messenger — cómo usar el bus de eventos

El bus `event.bus` está disponible como `MessageBusInterface`. Para usarlo en un Application Service:

```php
use Symfony\Component\Messenger\MessageBusInterface;

final class CreateUserService
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private MessageBusInterface $eventBus,
    ) {}

    public function execute(CreateUserCommand $command): UserDTO
    {
        // ... crear y guardar user
        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
        return UserDTO::fromUser($user);
    }
}
```

Con `redis://`, el `dispatch()` no ejecuta el handler en el momento — serializa el mensaje y lo añade al Redis Stream (XADD). Un worker independiente lo recoge y lo procesa:

```bash
make console cmd="messenger:consume async -vv"
```

El worker lee del stream mediante XREADGROUP (consumer groups de Redis), ejecuta el handler y confirma el mensaje (XACK) al terminar — o lo rechaza/reencola según la política de reintentos si falla. Esto desacopla el tiempo de respuesta HTTP del procesamiento del evento — útil cuando el handler hace trabajo costoso (enviar emails, llamadas a APIs externas, generar reportes...).

Para inspeccionar el stream desde redis-cli:

```bash
# Ver mensajes pendientes en el stream
docker compose exec redis redis-cli XLEN messages

# Ver contenido del stream
docker compose exec redis redis-cli XRANGE messages - +
```

También desde la consola de Symfony:

```bash
make console cmd="messenger:setup-transports"
make console cmd="messenger:stats"
```

Ver `sf7/postgresql-messenger-redis-example` para la integración completa con User y Product, incluyendo cómo testear el flujo asíncrono (verificar el encolado y consumir el mensaje en el propio test).

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
    ├── messenger.yaml                   ← bus de eventos, transport redis
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

Ver `sf7/postgresql-messenger-redis-example` para un ejemplo completo con User + Product y comunicación inter-BC vía Domain Events.

---

## Variables de entorno

| Variable | Valor por defecto | Descripción |
|---|---|---|
| `APP_ENV` | `dev` | Entorno de Symfony |
| `APP_SECRET` | `change_me_please` | Clave secreta — cambiar en producción |
| `APP_PORT` | `8080` | Puerto local de nginx |
| `DATABASE_URL` | `postgresql://app:app@postgres:5432/app` | Conexión a la BD principal |
| `MESSENGER_TRANSPORT_DSN` | `redis://redis:6379` | Transporte de Messenger — stream publicado en Redis |

La BD de test se configura directamente en `phpunit.dist.xml` y apunta a `postgres_test:5432`.
