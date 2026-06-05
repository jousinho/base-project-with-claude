# sf8/main-example — Symfony 8 · Base mínima con ejemplo DDD

Misma infraestructura que `sf8/main` pero con un Bounded Context de ejemplo (`Health`)
que recorre las tres capas DDD: Domain → Application → Infrastructure.

Úsala como referencia para entender dónde va cada pieza antes de añadir BD, Messenger o JWT.
Para arrancar un proyecto real sin código de ejemplo, parte de `sf8/main`.

---

## Stack

| Componente | Versión |
|---|---|
| PHP | 8.4 |
| Symfony | 8.1 |
| PHPUnit | 11 |

**Paquetes Symfony incluidos:**
- `symfony/framework-bundle` — núcleo del framework
- `symfony/console` — comandos CLI (`bin/console`)
- `symfony/dotenv` — carga de variables de entorno desde `.env`
- `symfony/serializer` — serialización/deserialización
- `symfony/uid` — generación de UUIDs
- `symfony/yaml` — parseo de YAML

**Docker:**
- `nginx:alpine` — servidor web (puerto 8080)
- `php:8.4-fpm-alpine` — PHP-FPM (sirve las peticiones HTTP)
- `php:8.4-fpm-alpine` (modo CLI) — para ejecutar comandos, composer y tests

---

## Prerrequisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) o Docker Engine + Docker Compose v2
- `make` (en macOS viene con Xcode CLI tools; en Linux: `sudo apt install make`)

No necesitas PHP ni Composer instalados localmente — todo corre dentro de Docker.

---

## Arrancar el proyecto por primera vez

```bash
# 1. Clona la rama
git clone -b sf8/main-example git@github.com:jousinho/base-project-with-claude.git mi-proyecto
cd mi-proyecto

# 2. Copia el fichero de variables de entorno
cp .env.example .env

# 3. Construye las imágenes Docker (solo la primera vez, o cuando cambies el Dockerfile)
make build

# 4. Arranca los contenedores en segundo plano
make up

# 5. Instala las dependencias PHP dentro del contenedor
make composer cmd=install

# 6. Comprueba que todo funciona
curl http://localhost:8080/health
# Respuesta esperada: {"status":"ok","timestamp":"2026-06-05T10:00:00+00:00"}
```

O en un solo paso con el script de setup:

```bash
cp .env.example .env && bash scripts/setup.sh
```

---

## Comandos del día a día

```bash
make up            # Arranca los contenedores (docker compose up -d)
make down          # Para y elimina los contenedores
make build         # Reconstruye las imágenes (necesario al cambiar Dockerfile)
make logs          # Muestra los logs de todos los contenedores en tiempo real

make shell         # Abre una shell dentro del contenedor php-fpm
make cli           # Abre una shell dentro del contenedor php-cli

make console cmd=cache:clear          # Ejecuta bin/console dentro del contenedor
make composer cmd="require paquete"   # Ejecuta composer dentro del contenedor
```

---

## Tests

```bash
make test          # todos los tests
make test-unit     # solo la suite Unit
```

---

## Arquitectura DDD — cómo está organizado el código

Todas las ramas de este repositorio siguen **DDD + Arquitectura Hexagonal**.
El **Bounded Context (BC)** es la unidad de primer nivel: cada funcionalidad de negocio
vive en su propio BC, completamente aislada del resto.

### Regla de dependencia

```
Domain ← Application ← Infrastructure
```

- **Domain** no importa nada de fuera (ni Symfony, ni Doctrine, ni ningún framework).
- **Application** solo importa clases de su propio Domain.
- **Infrastructure** (controllers, repositorios, adaptadores) importa Application y Domain,
  y es la única capa que puede usar frameworks y librerías externas.

Un BC **nunca** importa clases de otro BC directamente.
La comunicación entre BCs va siempre por Domain Events despachados por Messenger
(presente en ramas con el sufijo `-messenger-*`).

### Estructura de carpetas

```
src/
└── {BoundedContext}/
    ├── Domain/
    │   ├── Entity/          # Aggregates (constructores privados + factory create())
    │   ├── ValueObject/     # Objetos inmutables con reglas de negocio
    │   ├── Repository/      # Interfaces de persistencia (nunca implementaciones)
    │   └── Event/           # Domain Events (emitidos por los Aggregates)
    ├── Application/
    │   └── Service/         # Application Services: orquestan el caso de uso
    └── Infrastructure/
        ├── Http/
        │   └── Controller/  # Controllers HTTP: reciben request, llaman Service, devuelven response
        └── Persistence/
            └── Doctrine/    # Implementaciones de los repositorios (presente en ramas con BD)
```

### El ejemplo de esta rama: BC `Health`

El BC `Health` no tiene persistencia ni reglas de negocio complejas, pero recorre
las tres capas para ilustrar el patrón:

```
GET /health
    │
    ▼
HealthController          (Infrastructure/Http/Controller)
    │  llama a
    ▼
GetHealthStatusService    (Application/Service)
    │  crea
    ▼
HealthStatus              (Domain/ValueObject)
```

**`HealthStatus`** — Value Object del dominio.
Constructor privado, se instancia con `HealthStatus::create()`.
Expone `status()` y `checkedAt()` sin prefijo `get`.

**`GetHealthStatusService`** — Application Service.
No tiene dependencias externas (sin repositorio porque no hay BD).
En BCs con persistencia, aquí se inyecta el `RepositoryInterface`.

**`HealthController`** — Adaptador HTTP.
Solo traduce: convierte el `HealthStatus` en un `JsonResponse`.
No contiene lógica de negocio.

---

## Estructura de archivos completa

```
src/
├── Health/
│   ├── Domain/
│   │   └── ValueObject/
│   │       └── HealthStatus.php
│   ├── Application/
│   │   └── Service/
│   │       └── GetHealthStatusService.php
│   └── Infrastructure/
│       └── Http/
│           └── Controller/
│               └── HealthController.php
└── Kernel.php

tests/
└── Unit/
    └── Health/
        ├── Domain/ValueObject/HealthStatusTest.php
        ├── Application/Service/GetHealthStatusServiceTest.php
        └── Infrastructure/Http/Controller/HealthControllerTest.php

config/
├── bundles.php
├── routes.yaml              # health: GET /health → HealthController
├── services.yaml            # GetHealthStatusService + HealthController registrados explícitamente
└── packages/
    ├── framework.yaml
    └── routing.yaml
```

---

## Endpoints

### `GET /health`

```bash
curl http://localhost:8080/health
```

```json
{
  "status": "ok",
  "timestamp": "2026-06-05T10:00:00+00:00"
}
```

---

## Variables de entorno

| Variable | Valor por defecto | Descripción |
|---|---|---|
| `APP_ENV` | `dev` | Entorno de Symfony (`dev`, `prod`, `test`) |
| `APP_SECRET` | `change_me_please` | Clave secreta de Symfony — **cambiar en producción** |
| `APP_PORT` | `8080` | Puerto local en el que nginx escucha |

---

## Decisiones de diseño

**Un solo Dockerfile para fpm y cli.** Ambos contenedores usan la misma imagen para garantizar
paridad de versiones y extensiones PHP. El contenedor `php-cli` sobreescribe el comando a
`tail -f /dev/null` en lugar de arrancar FPM.

**Sin auto-discovery de servicios.** `config/services.yaml` no usa `resource:` para cargar
servicios automáticamente. Cada servicio y controller se registra de forma explícita.
Esto hace que las dependencias sean visibles y evita registrar clases por error.

**Rutas en `routes.yaml`, no en atributos PHP.** Las rutas se declaran centralizadas en
`config/routes.yaml` para que toda la configuración de routing esté en un solo lugar,
sin tener que navegar por el código fuente para encontrar qué URL apunta a qué controller.

**Value Objects con constructor privado.** `HealthStatus` no puede instanciarse con `new`.
Solo se crea a través de `HealthStatus::create()`. Este patrón garantiza que cualquier
invariante del objeto (validaciones, valores por defecto) siempre pase por el factory method,
sin posibilidad de crear objetos en estado inválido.
