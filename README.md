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

### 1. API

```bash
cd apiEcommerce
composer install --no-dev
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

- El proyecto esta pensado para correr en Windows + WAMP.
- Los montos monetarios se manejan en centavos para evitar problemas de precision.
- El checkout descuenta stock de forma atomica.
