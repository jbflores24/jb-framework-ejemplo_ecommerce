# apiEcommerce

API REST de ecommerce construida con JB Framework.

## Stack

- PHP 8.2+
- JB Framework (path repository `../../jb`)
- SQLite local por defecto (`storage/ecommerce.sqlite`)

## Dominio implementado

- Categorias (`/categories`)
- Productos (`/products`)
- Clientes (`/customers`)
- Pedidos (`/orders`)
- Detalle de pedido (`/order_items`)
- Checkout (`/checkout`)

## Endpoints principales

### Health

- `GET /api/health`

### Categorias

- `GET /api/categories`
- `GET /api/categories/{id}`
- `POST /api/categories`
- `PUT /api/categories/{id}`
- `DELETE /api/categories/{id}`

### Productos

- `GET /api/products?search=&category_id=&only_active=true`
- `GET /api/products/{id}`
- `POST /api/products`
- `PUT /api/products/{id}`
- `DELETE /api/products/{id}`

### Clientes

- `GET /api/customers`
- `GET /api/customers/{id}`
- `POST /api/customers`
- `PUT /api/customers/{id}`
- `DELETE /api/customers/{id}`

### Pedidos

- `GET /api/orders`
- `GET /api/orders/{id}` (incluye items)
- `POST /api/orders` (crea pedido via checkout)
- `PUT /api/orders/{id}` (actualiza status)
- `DELETE /api/orders/{id}`

### Checkout

- `POST /api/checkout`

Payload ejemplo:

```json
{
  "customer": {
    "nombre": "Ana Perez",
    "email": "ana@example.com",
    "telefono": "5511111111",
    "direccion": "CDMX"
  },
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 1 }
  ]
}
```

## Flujo de arranque

1. Instalar dependencias de runtime:

```bash
composer install --no-dev
```

2. Crear esquema:

```bash
php jb migrate:fresh
```

3. Cargar datos demo:

```bash
php jb seed ZEcommerceSeeder
php jb seed ZDemoOrdersSeeder
php jb seed ZBulkProductsSeeder
```

4. Generar OpenAPI basico:

```bash
php jb docs:generate
```

5. Servir API (opcion CLI):

```bash
php jb serve
```

En WAMP tambien puedes usar directamente:

- `http://localhost/ecommerce/apiEcommerce/public/api/health`

## Estructura clave

- `app/Controllers`: endpoints REST
- `app/Models`: acceso a datos
- `app/Services/CheckoutService.php`: logica transaccional de checkout
- `database/migrations`: esquema sqlite
- `database/seeders/ZEcommerceSeeder.php`: datos demo
- `routes/api.php`: contrato HTTP
- `docs/swagger.yaml`: salida de `php jb docs:generate`

## Consideraciones

- El checkout descuenta stock de forma atomica (update condicional por stock disponible).
- Todos los montos se manejan en centavos (`precio_centavos`, `total_centavos`) para evitar problemas de precision.
- Este proyecto esta pensado como base real pequena y extensible.

## Frontend asociado

El frontend en `../appEcommerce` incluye:

- vista `Tienda` con paginacion y ordenacion de productos,
- vista `Catalogos` con CRUD por tabla + paginacion + ordenacion,
- vista `Pedidos` con CRUD + filtros avanzados + paginacion + ordenacion.
