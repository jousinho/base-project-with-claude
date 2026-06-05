# sf8/postgresql-messenger-sync — Symfony 8 + PostgreSQL + Messenger (sync)

Infraestructura lista para producción: Symfony 8.1, Doctrine ORM, PostgreSQL 16, Symfony Messenger con transport `sync://`.
Sin código de dominio — punto de partida limpio para añadir tus propios Bounded Contexts.

Para ver un ejemplo completo con dominio User + Product y comunicación inter-BC, usa `sf8/postgresql-messenger-sync-example`.
Para la versión con Symfony 7, usa `sf7/postgresql-messenger-sync`.

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

**Transport Messenger:** `sync://` — los mensajes se procesan en el mismo proceso, sin cola externa.

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
git clone -b sf8/postgresql-messenger-sync git@github.com:jousinho/base-project-with-claude.git mi-proyecto
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

Con `sync://`, el handler se ejecuta inmediatamente en el mismo proceso.

Ver `sf8/postgresql-messenger-sync-example` para la integración completa con User y Product.

---

## Variables de entorno

| Variable | Valor por defecto | Descripción |
|---|---|---|
| `APP_ENV` | `dev` | Entorno de Symfony |
| `APP_SECRET` | `change_me_please` | Clave secreta — cambiar en producción |
| `APP_PORT` | `8080` | Puerto local de nginx |
| `DATABASE_URL` | `postgresql://app:app@postgres:5432/app` | BD principal |
