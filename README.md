# sf8/postgresql-messenger-doctrine — Symfony 8 + PostgreSQL + Messenger (Doctrine transport)

Infraestructura lista para producción: Symfony 8.1, Doctrine ORM, PostgreSQL 16, Symfony Messenger con transport `doctrine://` (cola persistida en BD, procesamiento asíncrono vía worker).
Sin código de dominio — punto de partida limpio para añadir tus propios Bounded Contexts.

Para ver un ejemplo completo con dominio User + Product y comunicación inter-BC, usa `sf8/postgresql-messenger-doctrine-example`.
Para la versión con Symfony 7, usa `sf7/postgresql-messenger-doctrine`.

---

## Stack

| Componente | Versión |
|---|---|
| PHP | 8.4 |
| Symfony | 8.1 |
| Doctrine ORM | ^3.6 |
| Doctrine Migrations | ^4.0 |
| Symfony Messenger | ^8.1 |
| PostgreSQL | 16 |
| PHPUnit | 11 |

**Transport Messenger:** `doctrine://` — los mensajes se persisten en la tabla `messenger_messages` y se procesan de forma asíncrona mediante un worker (`messenger:consume`), sin depender de un broker externo.

**Docker:**
- `nginx:alpine` — servidor web (puerto 8080)
- `php:8.4-fpm-alpine` — PHP-FPM
- `php:8.4-fpm-alpine` (modo CLI) — comandos, composer, tests, migraciones
- `postgres:16-alpine` — base de datos principal (puerto 5432)
- `postgres:16-alpine` — base de datos de test (puerto 5433)

---

## Prerrequisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) o Docker Engine + Docker Compose v2
- `make` (en macOS viene con Xcode CLI tools; en Linux: `sudo apt install make`)

No necesitas PHP, Composer ni PostgreSQL instalados localmente.

---

## Arrancar el proyecto por primera vez

```bash
git clone -b sf8/postgresql-messenger-doctrine git@github.com:jousinho/base-project-with-claude.git mi-proyecto
cd mi-proyecto
cp .env.example .env
make build
make up
make composer cmd=install
make migrate
curl http://localhost:8080/health
```

---

## Comandos del día a día

```bash
make up / down / build / logs
make shell / cli
make console cmd=cache:clear
make composer cmd="require paquete"
make migrate / migration
make db / db-test
make test / test-unit / test-integration / test-functional
```

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

Con `doctrine://`, el `dispatch()` no ejecuta el handler en el momento — serializa el mensaje y lo guarda como una fila en `messenger_messages`. Un worker independiente lo recoge y lo procesa:

```bash
make console cmd="messenger:consume async -vv"
```

El worker consulta la tabla, bloquea el siguiente mensaje disponible, ejecuta el handler y borra la fila al terminar (o la reprograma si falla, según la política de reintentos). Esto desacopla el tiempo de respuesta HTTP del procesamiento del evento — útil cuando el handler hace trabajo costoso (enviar emails, llamadas a APIs externas, generar reportes...).

Ver `sf8/postgresql-messenger-doctrine-example` para la integración completa con User y Product, incluyendo cómo testear el flujo asíncrono (verificar el encolado y consumir el mensaje en el propio test).

---

## Variables de entorno

| Variable | Valor por defecto | Descripción |
|---|---|---|
| `APP_ENV` | `dev` | Entorno de Symfony |
| `APP_SECRET` | `change_me_please` | Clave secreta — cambiar en producción |
| `APP_PORT` | `8080` | Puerto local de nginx |
| `DATABASE_URL` | `postgresql://app:app@postgres:5432/app` | BD principal |
| `MESSENGER_TRANSPORT_DSN` | `doctrine://default?queue_name=async` | Transporte de Messenger — cola persistida en la tabla `messenger_messages` |
