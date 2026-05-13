# Guia de Ejecucion

## Pre-requisitos

- PHP 8.2+
- Composer
- WAMP/Apache o servidor local

## Backend (apiEcommerce)

Ruta:

- `c:/wamp64/www/ecommerce/apiEcommerce`

Pasos:

```bash
composer install --no-dev
php jb migrate:fresh
php jb seed ZEcommerceSeeder
php jb seed ZDemoOrdersSeeder
php jb seed ZBulkProductsSeeder
php jb docs:generate
```

Healthcheck:

- `http://localhost/ecommerce/apiEcommerce/public/api/health`

## Frontend (appEcommerce)

Ruta:

- `c:/wamp64/www/ecommerce/appEcommerce`

Abre `index.html` en navegador.

Por defecto consume:

- `http://localhost/ecommerce/apiEcommerce/public/api`

Se puede ajustar desde el campo `API Base` en pantalla.

Capacidades actuales del frontend:

- Menu con 3 vistas: `Tienda`, `Catalogos`, `Pedidos`.
- `Tienda`: filtros, ordenacion y paginacion de cards.
- `Catalogos`: CRUD por tabla + paginacion configurable + ordenacion.
- `Pedidos`: CRUD + filtros avanzados + paginacion + ordenacion.

## Flujo recomendado para demo

1. Abrir frontend.
2. Filtrar/buscar productos.
3. Agregar productos al carrito.
4. Completar checkout.
5. Verificar respuesta de pedido creado.
6. Validar que el stock disminuyo al recargar catalogo.
7. Entrar a `Catalogos` y validar CRUD de al menos `categories`.
8. Entrar a `Pedidos` y probar filtros por estado, cliente y rango de fecha.
