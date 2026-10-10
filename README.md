# Ecommerce Demo

Proyecto completo de demostracion construido sobre JB Framework, con backend REST, frontend web y documentacion tecnica separada por modulo.

## Estructura

- `apiEcommerce/`: API REST del ecommerce.
- `appEcommerce/`: frontend web que consume la API.
- `documentacion/`: guia de ejecucion, contratos y explicacion tecnica.

## Que incluye

- catalogo de categorias, productos y clientes,
- pedidos con detalle y checkout transaccional,
- frontend con vista de tienda, catalogos y pedidos,
- paginacion, ordenacion y filtros avanzados en la interfaz,
- datos demo y seeders adicionales para pruebas.

## Inicio rapido

### 0. Obtener el proyecto

Clona el repositorio **con el nombre `ecommerce`** dentro de la carpeta web de tu servidor local (`htdocs` en XAMPP, `www` en WAMP). Las URLs por defecto de la API y del frontend asumen ese nombre:

```bash
git clone https://github.com/jbflores24/jb-framework-ejemplo_ecommerce.git ecommerce
```

Si usas otro nombre de carpeta, ajusta `APP_URL` y `APP_BASE_ROUTE` en `apiEcommerce/.env` y el campo `API Base` del frontend.

### 1. API

```bash
cd apiEcommerce
composer install --no-dev
cp .env.example .env   # en Windows (cmd): copy .env.example .env
php jb migrate:fresh
php jb seed ZEcommerceSeeder
php jb seed ZDemoOrdersSeeder
php jb seed ZBulkProductsSeeder
php jb docs:generate
```

Healthcheck:

- `http://localhost/ecommerce/apiEcommerce/public/api/health`

### 2. Frontend

Abre:

- `appEcommerce/index.html`

Por defecto consume:

- `http://localhost/ecommerce/apiEcommerce/public/api`

## Documentacion

- [Documentacion del proyecto](documentacion/README.md)
- [README de la API](apiEcommerce/README.md)
- [README del frontend](appEcommerce/README.md)

## Notas

- Probado en Windows con WAMP y con XAMPP (PHP 8.2+, extension `pdo_sqlite`).
- JB Framework se instala desde su repositorio publico con Composer: no hace falta tenerlo clonado aparte.
- Los montos monetarios se manejan en centavos para evitar problemas de precision.
- El checkout descuenta stock de forma atomica.
