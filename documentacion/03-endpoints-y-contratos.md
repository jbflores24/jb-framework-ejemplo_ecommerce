# Endpoints y Contratos

Base URL:

- `http://localhost/ecommerce/apiEcommerce/public/api`

## GET /health

Respuesta:

```json
{
  "status": "success",
  "message": "API disponible.",
  "data": {
    "service": "jb",
    "path": "/health"
  }
}
```

## Categories

- `GET /categories`
- `GET /categories/{id}`
- `POST /categories`
- `PUT /categories/{id}`
- `DELETE /categories/{id}`

Campos requeridos en `POST /categories`:

- `nombre`
- `slug`

Campos opcionales:

- `descripcion`
- `activo`

## Products

- `GET /products?search=&category_id=&only_active=true`
- `GET /products/{id}`
- `POST /products`
- `PUT /products/{id}`
- `DELETE /products/{id}`

Campos clave:

- `category_id`
- `sku`
- `nombre`
- `precio_centavos`
- `stock`

Campos requeridos en `POST /products`:

- `category_id`
- `sku`
- `nombre`
- `precio_centavos`
- `stock`

## Customers

- `GET /customers`
- `GET /customers/{id}`
- `POST /customers`
- `PUT /customers/{id}`
- `DELETE /customers/{id}`

Campo unico:

- `email`

Campos requeridos en `POST /customers`:

- `nombre`
- `email` (valido)

## Orders

- `GET /orders`
- `GET /orders/{id}`
- `POST /orders` (equivalente a checkout)
- `PUT /orders/{id}` (status)
- `DELETE /orders/{id}`

Estados permitidos en `PUT /orders/{id}`:

- `created`
- `paid`
- `shipped`
- `cancelled`

## Order items

- `GET /order_items`
- `GET /order_items?order_id={id}`
- `GET /order_items/{id}`
- `POST /order_items`
- `PUT /order_items/{id}`
- `DELETE /order_items/{id}`

## Checkout

- `POST /checkout`

Payload:

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

Errores esperados:

- `VALIDATION_ERROR` (422)
- `PRODUCT_NOT_AVAILABLE` (422)
- `INSUFFICIENT_STOCK` (422)
- `STOCK_RACE_CONDITION` (409)
