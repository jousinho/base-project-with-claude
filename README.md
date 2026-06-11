# sf8/main — Symfony 8 · Base mínima

Punto de partida limpio para proyectos Symfony 8. No incluye base de datos ni autenticación —
solo el framework, Docker listo para arrancar y un endpoint de healthcheck funcional.

Si necesitas persistencia, autenticación o mensajería, parte de una rama más completa
(`sf8/postgresql`, `sf8/postgresql-messenger-rabbitmq-jwt`, etc.).

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
- `php:8.3-fpm-alpine` — PHP-FPM (sirve las peticiones HTTP)
- `php:8.3-fpm-alpine` (modo CLI) — para ejecutar comandos, composer y tests

---

## Prerrequisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) o Docker Engine + Docker Compose v2
- `make` (en macOS viene con Xcode CLI tools; en Linux: `sudo apt install make`)

No necesitas PHP ni Composer instalados localmente — todo corre dentro de Docker.

---

## Arrancar el proyecto por primera vez

```bash
# 1. Clona la rama
git clone -b sf8/main git@github.com:jousinho/base-project-with-claude.git mi-proyecto
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
# Respuesta esperada: {"status":"ok"}
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

Esta rama solo tiene tests unitarios (no hay base de datos que integrar).

```bash
# Ejecuta todos los tests
make test

# Ejecuta solo la suite Unit
make test-unit
```

Los tests están en `tests/Unit/` y siguen la nomenclatura:

```
test_{acción}_{contexto}__should_{resultado_esperado}
test_{acción}_{contexto}__when_{condición}__should_{resultado_esperado}
```

---

## Estructura de carpetas

```
├── bin/
│   └── console                  # CLI de Symfony
├── config/
│   ├── bundles.php              # Bundles registrados (solo FrameworkBundle)
│   ├── routes.yaml              # Rutas declaradas explícitamente
│   ├── services.yaml            # Servicios y controllers registrados explícitamente
│   └── packages/
│       ├── framework.yaml       # Config del framework (secret, error handling)
│       └── routing.yaml         # Router con UTF-8 habilitado
├── docker/
│   ├── nginx/default.conf       # Config de nginx (reescritura a index.php)
│   └── php/symfony.ini          # Config de PHP (opcache, timezone, memory_limit)
├── public/
│   └── index.php                # Front controller de Symfony
├── scripts/
│   └── setup.sh                 # Primera configuración: build + up + composer install
├── src/
│   ├── Kernel.php
│   └── Shared/
│       └── Infrastructure/
│           └── Http/
│               └── Controller/
│                   └── HealthController.php
├── tests/
│   └── Unit/
│       └── Shared/
│           └── Infrastructure/
│               └── Http/
│                   └── Controller/
│                       └── HealthControllerTest.php
├── .env.example                 # Variables de entorno de ejemplo (sí se commitea)
├── docker-compose.yaml          # Definición de servicios Docker
├── Dockerfile                   # Imagen PHP 8.3-fpm-alpine compartida por fpm y cli
├── Makefile                     # Atajos para comandos Docker/Symfony
└── phpunit.dist.xml             # Configuración de PHPUnit (suite Unit)
```

---

## Endpoints

### `GET /health`

Comprueba que la aplicación está viva. No tiene dependencias externas (sin BD, sin caché externa).

```bash
curl http://localhost:8080/health
```

```json
{"status":"ok"}
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
paridad de versiones y extensiones PHP. El contenedor `php-cli` simplemente sobreescribe el
comando a `tail -f /dev/null` en lugar de arrancar FPM.

**Sin auto-discovery de servicios.** `config/services.yaml` no usa `resource:` para cargar
servicios automáticamente. Cada controlador se registra de forma explícita. Esto hace
que las dependencias sean visibles y evita registrar clases por error.

**Rutas en `routes.yaml`, no en atributos.** Las rutas se declaran en `config/routes.yaml`
para mantener toda la configuración de routing centralizada y visible sin tener que
navegar por el código.
