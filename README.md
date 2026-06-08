# sf8/postgresql-messenger-rabbitmq — Symfony 8 + PostgreSQL + Messenger (RabbitMQ transport)

Infraestructura lista para producción: Symfony 8.1, Doctrine ORM, PostgreSQL 16, Symfony Messenger con transport `amqp://` sobre RabbitMQ (broker dedicado, procesamiento asíncrono vía worker).
Sin código de dominio — punto de partida limpio para añadir tus propios Bounded Contexts.

Para ver un ejemplo completo con dominio User + Product y comunicación inter-BC, usa `sf8/postgresql-messenger-rabbitmq-example`.
Para la versión con Symfony 7, usa `sf7/postgresql-messenger-rabbitmq`.

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
git clone -b sf8/postgresql-messenger-rabbitmq git@github.com:jousinho/base-project-with-claude.git mi-proyecto
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

Con `amqp://`, el `dispatch()` no ejecuta el handler en el momento — serializa el mensaje y lo publica en una cola de RabbitMQ (Symfony crea automáticamente el exchange y la cola la primera vez que se publica o consume). Un worker independiente lo recoge y lo procesa:

```bash
make console cmd="messenger:consume async -vv"
```

El worker abre una conexión persistente con el broker, consume mensajes de la cola, ejecuta el handler y los confirma (`ack`) al terminar — o los rechaza/reencola según la política de reintentos si falla. Esto desacopla el tiempo de respuesta HTTP del procesamiento del evento — útil cuando el handler hace trabajo costoso (enviar emails, llamadas a APIs externas, generar reportes...).

Para inspeccionar las colas y mensajes en tránsito, el panel de gestión de RabbitMQ está disponible en `http://localhost:15672` (usuario/clave `app`/`app`). También puedes comprobar el estado del transporte desde la consola:

```bash
make console cmd="messenger:setup-transports"
make console cmd="messenger:stats"
```

Ver `sf8/postgresql-messenger-rabbitmq-example` para la integración completa con User y Product, incluyendo cómo testear el flujo asíncrono (verificar el encolado y consumir el mensaje en el propio test).

---

## Variables de entorno

| Variable | Valor por defecto | Descripción |
|---|---|---|
| `APP_ENV` | `dev` | Entorno de Symfony |
| `APP_SECRET` | `change_me_please` | Clave secreta — cambiar en producción |
| `APP_PORT` | `8080` | Puerto local de nginx |
| `DATABASE_URL` | `postgresql://app:app@postgres:5432/app` | BD principal |
| `MESSENGER_TRANSPORT_DSN` | `amqp://app:app@rabbitmq:5672/%2f/messages` | Transporte de Messenger — cola publicada en el broker RabbitMQ |
